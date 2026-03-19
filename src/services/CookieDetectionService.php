<?php

namespace digitalastronaut\craftcookiebanner\services;

use craft\base\Component;

use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\helpers\CookieBanner as CookieBannerHelper;
use digitalastronaut\craftcookiebanner\records\Content;

use Fuse\Fuse;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions\F;

class CookieDetectionService extends Component {
    /**
     * @return int
     */
    public function getIssues(): int {
        $issues = 0;

        $cookiesOverview = $this->getCookiesOverview();

        foreach ($cookiesOverview as $cookie) {
            if ($this->isControlPanelCookie($cookie['name'])) continue;

            $definitions = CookieBanner::getInstance()
                ->getCookiesAndVendors()
                ->checkCookieDefinitionForEachSite($cookie['name']);

            $definitionCounts = array_count_values($definitions);

            if (
                ($definitionCounts['not-defined'] ?? 0) > 0 ||
                ($definitionCounts['defined-incomplete'] ?? 0) > 0
            ) $issues++;
        }

        $vendorsOverview = $this->getVendorsOverview();
        
        foreach ($vendorsOverview as $vendor) {
            $definitions = CookieBanner::getInstance()
                ->getCookiesAndVendors()
                ->checkVendorDefinitionForEachSite($vendor['name']);

            $definitionCounts = array_count_values($definitions);

            if (
                ($definitionCounts['not-defined'] ?? 0) > 0 ||
                ($definitionCounts['defined-incomplete'] ?? 0) > 0
            ) $issues++;
        }

        return $issues;
    }

    /**
     * @return array
     */
    public function getCookiesOverview(): array {
        $result = [];

        $existingCookies = CookieBanner::getInstance()->getCookiesAndVendors()->getAllCookies();

        foreach($existingCookies as $cookie) {
            $isAutomated = $this->getCookieDataFromDatabase($cookie['name'], 'en') ? true : false;
            $isControlPanelCookie = $this->isControlPanelCookie($cookie['name']);

            $result[$cookie['name']] = [
                'name' => $cookie['name'],
                'currentValue' => '',
                'isAutomated' => $isAutomated,
                'isControlPanelCookie' => $isControlPanelCookie,
                'onlyInBrowserCookies' => false,
            ];
        }

        $browserCookies = $_COOKIE;

        foreach ($browserCookies as $cookieName => $cookieValue) {
            if (isset($result[$cookieName])) continue;

            $isControlPanelCookie = $this->isControlPanelCookie($cookieName);
            $databaseCookie = $this->getCookieDataFromDatabase($cookieName, 'en');
            $isAutomated = $databaseCookie ? true : false;
            // Cookies in the database use the * wildcard for generated strings so we show this instead of the full name
            $wildCardName = $databaseCookie ? $databaseCookie['cookie']['name'] : $cookieName;

            if ($this->isCookieBlacklisted($cookieName)) continue;

            $result[$wildCardName] = [
                'name' => $wildCardName,
                'currentValue' => $cookieValue,
                'isAutomated' => $isAutomated,
                'isControlPanelCookie' => $isControlPanelCookie,
                'onlyInBrowserCookies' => true,
            ];
        }

        uasort($result, function ($a, $b) {
            if ($a['isControlPanelCookie'] !== $b['isControlPanelCookie']) {
                return $a['isControlPanelCookie'] ? 1 : -1;
            }

            return $b['onlyInBrowserCookies'] <=> $a['onlyInBrowserCookies'];
        });
            
        return $result;
    }

    /**
     * @return array
     */
    public function getVendorsOverview() {
        $result = [];
        $existingVendorNames = [];

        $existingVendors = Content::find()->one()->cookieGroups;

        foreach ($existingVendors as $vendor) {
            $vendorMatch = CookieBanner::getInstance()
                ->getCookieDetection()
                ->getVendorDataFromDatabase($vendor['name'], "en");
            
            if (!in_array($vendor['name'], $existingVendorNames)) {
                $result[] = [
                    'name' => $vendor['name'],
                    'url' => $vendor['url'],
                    'isAutomated' => $vendorMatch ? true : false,
                    'isSuggestion' => false,
                ];

                $existingVendorNames[] = $vendor['name'];
            };
        }

        $blacklistedVendors = CookieBanner::getInstance()->getSettings()->blacklistedVendors;
        $alreadyDefinedCookies = CookieBanner::getInstance()
            ->getCookiesAndVendors()
            ->getAllCookies();

        foreach ($alreadyDefinedCookies as $cookie) {
            $cookieMatch = CookieBanner::getInstance()
                ->getCookieDetection()
                ->getCookieDataFromDatabase($cookie['name'], "en");
            
            if (!$cookieMatch) continue;

            $vendorMatch = CookieBanner::getInstance()
                ->getCookieDetection()
                ->getVendorDataFromDatabase($cookieMatch['cookie']['vendor'], "en");

            if (
                $vendorMatch &&
                !in_array($vendorMatch['vendor']['name'], $existingVendorNames) &&
                !in_array($vendorMatch['vendor']['name'], array_column($blacklistedVendors, null, 'name'))
            ) {
                $result[] = [
                    'name' => $vendorMatch['vendor']['name'],
                    'url' => $vendorMatch['vendor']['homePage'],
                    'isAutomated' => true,
                    'isSuggestion' => true,
                ];

                $existingVendorNames[] = $vendorMatch['vendor']['name'];
            }
        }

        return $result;
    }

    /**
     * @param string|null $searchTerm
     *
     * @return array
     */
    public function searchCookieDatabase(string|null $searchTerm) {
        if (!$searchTerm) return [];
    
        $cookieDatabase = $this->getDatabaseFile("cookies", "en");

        $fuzzySearchIndex = new Fuse($cookieDatabase['data'], ['keys' => ['name', 'vendor']]);

        $results = $fuzzySearchIndex->search($searchTerm);

        return array_slice($results, 0, 100);
    }

    /**
     * @param string|null $searchTerm
     *
     * @return array
     */
    public function searchVendorDatabase(string|null $searchTerm) {
        if (!$searchTerm) return [];
    
        $vendorDatabase = $this->getDatabaseFile("vendors", "en");

        $fuzzySearchIndex = new Fuse($vendorDatabase['data'], ['keys' => ['name']]);

        $results = $fuzzySearchIndex->search($searchTerm);

        return array_slice($results, 0, 100);
    }

    /**
     * @param string $cookieName
     * @param string $language
     *
     * @return array|null
     */
    public function getCookieDataFromDatabase(string $cookieName, string $language): ?array {
        $cookieDatabase = $this->getDatabaseFile("cookies", $language);

        foreach ($cookieDatabase['data'] as $cookie) {
            if ($this->cookieNameMatches($cookieName, $cookie['name'])) {
                return [
                    "cookie" => $cookie,
                    "languageMatch" => $cookieDatabase['languageMatch'],
                ];
            };
        }

        return null;
    }

    /**
     * @param string $vendorName
     * @param string $language
     *
     * @return array|void
     */
    public function getVendorDataFromDatabase(string $vendorName, string $language): ?array {
        $vendorDatabase = $this->getDatabaseFile("vendors", $language);

        foreach ($vendorDatabase['data'] as $vendor) {
            if ($vendorName === $vendor['name']) {
                return [
                    "vendor" => $vendor,
                    "languageMatch" => $vendorDatabase['languageMatch'],
                ];
            }
        }

        return null;
    }

    /**
     * @param string $handle
     * @param string $language
     *
     * @return array|null
     */
    public function getDatabaseFile(string $handle, string $language): ?array {
        $languageMatch = true;
        $path = CookieBanner::getInstance()->getBasePath() . "/static/{$handle}/{$language}.json";

        if (!is_file($path)) {
            $path = CookieBanner::getInstance()->getBasePath() . "/static/{$handle}/en.json";
            $languageMatch = false;
        }

        $fileContents = file_get_contents($path);
        if (!$fileContents) return null;

        $data = json_decode($fileContents, true);
        if (!is_array($data)) return null;

        return [
            'data' => $data,
            'languageMatch' => $languageMatch,
        ];
    }

    /**
     * @param string $cookieName
     * @param string $pattern
     *
     * @return bool
     */
    public function cookieNameMatches(string $cookieName, string $pattern): bool {
        $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/';

        return (bool) preg_match($regex, $cookieName);
    }

    /**
     * @param string $cookieName
     *
     * @return bool
     */
    private function isControlPanelCookie(string $cookieName): bool {
        foreach (CookieBannerHelper::CONTROL_PANEL_COOKIES as $pattern) {
            if ($this->cookieNameMatches($cookieName, $pattern)) return true;
        }
        
        return false;
    }

    /**
     * @param string|null $cookieName
     *
     * @return bool
     */
    public function isCookieBlacklisted(string|null $cookieName): bool {
        $blacklistCookies = array_column(CookieBanner::getInstance()->getSettings()->blacklistedCookies, 'name');

        return in_array($cookieName, $blacklistCookies);
    }

    /**
     * @param string|null $vendorName
     *
     * @return bool
     */
    public function isVendorBlacklisted(string|null $vendorName): bool {
        $blacklistVendors = array_column(CookieBanner::getInstance()->getSettings()->blacklistedVendors, 'name');

        dd($blacklistVendors);

        return in_array($vendorName, $blacklistVendors);
    }
}