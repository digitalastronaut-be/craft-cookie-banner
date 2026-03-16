<?php

namespace digitalastronaut\craftcookiebanner\services;

use craft\base\Component;
use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\records\Content;

class CookieDetectionService extends Component {
    public array $controlPanelCookies = [
        "Craft-*",
        "*_identity",
        "*_username",
    ];

    public function getCookiesOverview(): array {
        $blacklistedCookies = CookieBanner::getInstance()->getSettings()->blacklistedCookies;
        $cookieDatabase = json_decode(file_get_contents(CookieBanner::getInstance()->getBasePath() . '/static/cookies/en.json'), true);

        $result = [];

        foreach ($_COOKIE as $cookieName => $cookieValue) {
            $matchedCookie = null;
            $isControlPanelCookie = $this->isControlPanelCookie($cookieName);
            
            foreach ($blacklistedCookies as $blacklistedCookie) {
                if (!empty($blacklistedCookie['name']) &&
                    $this->cookieNameMatches($cookieName, $blacklistedCookie['name'])) {
                    continue 2;
                }
            }

            foreach ($cookieDatabase as $cookie) {
                if ($this->cookieNameMatches($cookieName, $cookie['name'])) {
                    $matchedCookie = $cookie;
                    break;
                }
            }

            if ($matchedCookie) {
                $result[] = [
                    'category'    => $matchedCookie['category'],
                    'name'        => $matchedCookie['name'],
                    'description' => $matchedCookie['description'],
                    'retention'  => $matchedCookie['retention'],
                    'vendor'      => $matchedCookie['vendor'],
                    'currentValue' => $cookieValue,
                    'isControlPanelCookie' => $isControlPanelCookie,
                    'onlyInBrowserCookies' => true,
                    'isAutomated' => true
                ];
            } else {
                $result[] = [
                    'category'    => 'Uncategorized',
                    'name'        => $cookieName,
                    'description' => '',
                    'retention'  => '',
                    'vendor'      => '',
                    'currentValue' => $cookieValue,
                    'isControlPanelCookie' => $isControlPanelCookie,
                    'onlyInBrowserCookies' => true,
                    'isAutomated' => false
                ];
            }
        }

        $alreadyDefinedCookies = CookieBanner::getInstance()
            ->getCookiesAndVendors()
            ->getAllCookies();

        foreach ($alreadyDefinedCookies as $index => $alreadyDefinedCookie) {
            $matchedCookie = null;
            $isControlPanelCookie = $this->isControlPanelCookie($alreadyDefinedCookie['name']);
            
            // Check if the already defined cookie is in the detected cookies array if so skip it
            $existingKey = array_search($alreadyDefinedCookie['name'], array_column($result, 'name'));
            if ($existingKey !== false) {
                $result[$existingKey]['onlyInBrowserCookies'] = false;
                continue;
            }

            foreach ($cookieDatabase as $cookie) {
                if ($this->cookieNameMatches($alreadyDefinedCookie['name'], $cookie['name'])) {
                    $matchedCookie = $cookie;
                    break;
                }
            }

            $result[] = [
                'category'    => $alreadyDefinedCookie['category'],
                'name'        => $alreadyDefinedCookie['name'],
                'description' => $alreadyDefinedCookie['purpose'],
                'retention'  => $alreadyDefinedCookie['expiration'],
                'vendor'      => $alreadyDefinedCookie['group'],
                'currentValue' => "",
                'isControlPanelCookie' => $isControlPanelCookie,
                'isAutomated' => $matchedCookie ? true : false,
                'onlyInBrowserCookies' => false,
            ];
        }

        usort($result, function($a, $b) {
            if ($a['isControlPanelCookie'] === $b['isControlPanelCookie']) return 0;
            return $a['isControlPanelCookie'] ? 1 : -1;
        });

        return $result;
    }

    public function getCookieDataFromDatabase(string $cookieName, string $language) {
        $languageMatch = true;
        $path = CookieBanner::getInstance()->getBasePath() . "/static/cookies/{$language}.json";
        
        if (!is_file($path)) {
            $path = CookieBanner::getInstance()->getBasePath() . "/static/cookies/en.json";
            $languageMatch = false;
        }

        $databaseFile = file_get_contents($path);

        if (!$databaseFile) return null;

        $cookieDatabase = json_decode($databaseFile, true);

        foreach ($cookieDatabase as $cookie) {
            if ($this->cookieNameMatches($cookieName, $cookie['name'])) {
                return [
                    "cookie" => $cookie,
                    "languageMatch" => $languageMatch,
                ];
            };
        }

        return null;
    }

    public function getVendorDataFromDatabase(string $vendorName, string $language) {
        $languageMatch = true;
        $path = CookieBanner::getInstance()->getBasePath() . "/static/vendors/{$language}.json";
        
        if (!is_file($path)) {
            $path = CookieBanner::getInstance()->getBasePath() . "/static/vendors/en.json";
            $languageMatch = false;
        }

        $databaseFile = file_get_contents($path);

        if (!$databaseFile) return null;

        $vendorDatabase = json_decode($databaseFile, true);

        foreach ($vendorDatabase as $vendor) {
            if ($vendorName === $vendor['name']) {
                return [
                    "vendor" => $vendor,
                    "languageMatch" => $languageMatch,
                ];
            }
        }
    }

    public function getBannerCookies($content) {
        return array_merge(
            is_array($content["necessaryCookies"]) ? $content["necessaryCookies"] : [],
            is_array($content["preferenceCookies"]) ? $content["preferenceCookies"] : [],
            is_array($content["analyticalCookies"]) ? $content["analyticalCookies"] : [],
            is_array($content["marketingCookies"]) ? $content["marketingCookies"] : [],
            is_array($content["uncategorizedCookies"]) ? $content["uncategorizedCookies"] : []
        );
    }

    public function cookieNameMatches(string $cookieName, string $pattern): bool {
        $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/';

        return (bool) preg_match($regex, $cookieName);
    }

    private function isControlPanelCookie(string $cookieName): bool {
        foreach ($this->controlPanelCookies as $pattern) {
            if ($this->cookieNameMatches($cookieName, $pattern)) return true;
        }
        
        return false;
    }

    public function getVendorsOverview() {
        $result = [];
        $existingVendorNames = [];

        $existingVendors = Content::find()->one()->cookieGroups;

        foreach ($existingVendors as $vendor) {
            $vendorMatch = CookieBanner::getInstance()
                ->getCookieDetection()
                ->getVendorDataFromDatabase($vendor['name'], "en");
            
            if (
                $vendorMatch &&
                !in_array($vendor['name'], $existingVendorNames)
            ) {
                $result[] = [
                    'name' => $vendor['name'],
                    'url' => $vendor['url'],
                    'isAutomated' => $vendorMatch ? true : false,
                    'isSuggestion' => false,
                ];

                $existingVendorNames[] = $vendor['name'];
            };
        }

        $alreadyDefinedCookies = CookieBanner::getInstance()
            ->getCookiesAndVendors()
            ->getAllCookies();

        foreach ($alreadyDefinedCookies as $cookie) {
            $cookieMatch = CookieBanner::getInstance()
                ->getCookieDetection()
                ->getCookieDataFromDatabase($cookie['name'], "en");
            
            $vendorMatch = CookieBanner::getInstance()
                ->getCookieDetection()
                ->getVendorDataFromDatabase($cookieMatch['cookie']['vendor'], "en");

            if (
                $vendorMatch &&
                !in_array($vendorMatch['vendor']['name'], $existingVendorNames)
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
}