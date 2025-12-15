<?php

namespace digitalastronaut\craftcookiebanner\services;

use Craft;
use craft\base\Component;
use digitalastronaut\craftcookiebanner\CookieBanner;

class CookieDetectionService extends Component {
    public array $controlPanelCookies = [
        "Craft-*",
        "*_identity",
        "*_username",
    ];

    public function getCookiesOverview(): array {
        $cookieDatabase = json_decode(file_get_contents(CookieBanner::getInstance()->getBasePath() . '/static/cookies/en.json'), true);

        $result = [];

        foreach ($_COOKIE as $cookieName => $cookieValue) {
            $matchedCookie = null;
            $isControlPanelCookie = $this->isControlPanelCookie($cookieName);
            
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
                    'isAutomated' => false
                ];
            }
        }

        usort($result, function($a, $b) {
            if ($a['isControlPanelCookie'] === $b['isControlPanelCookie']) return 0;
            return $a['isControlPanelCookie'] ? 1 : -1;
        });

        return $result;
    }

    public function getCookieDataFromDatabase(string $cookieName, string $language) {
        $databaseFile = file_get_contents(CookieBanner::getInstance()->getBasePath() . "/static/cookies/{$language}.json");

        if (!$databaseFile) return null;

        $cookieDatabase = json_decode($databaseFile, true);

        foreach ($cookieDatabase as $cookie) {
            if ($this->cookieNameMatches($cookieName, $cookie['name'])) return $cookie;
        }
    }

    public function getBannerCookies($content) {
        return array_merge(
            is_array($content["essentialCookies"]) ? $content["essentialCookies"] : [],
            is_array($content["functionalCookies"]) ? $content["functionalCookies"] : [],
            is_array($content["analyticalCookies"]) ? $content["analyticalCookies"] : [],
            is_array($content["advertisementCookies"]) ? $content["advertisementCookies"] : [],
            is_array($content["personalizationCookies"]) ? $content["personalizationCookies"] : [],
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
}