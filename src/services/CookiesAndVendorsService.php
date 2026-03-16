<?php

namespace digitalastronaut\craftcookiebanner\services;

use Craft;
use craft\base\Component;
use digitalastronaut\craftcookiebanner\helpers\CookieBanner;
use digitalastronaut\craftcookiebanner\records\Content;

class CookiesAndVendorsService extends Component {
    public function getAllCookies(bool $categorized = false) {
        $content = Content::find()->one();
        $categorizedCookies = $content->getAttributes(CookieBanner::COOKIE_GROUPS);

        if (!$content) return [];

        $uncategorizedCookies = [];

        foreach ($categorizedCookies as $category => $cookies) {
            if (!is_array($cookies)) continue;

            foreach ($cookies as $cookie) {
                $cookie['category'] = $category;
                $uncategorizedCookies[] = $cookie;
            }
        }

        return $categorized ? $categorizedCookies : $uncategorizedCookies;
    }
}