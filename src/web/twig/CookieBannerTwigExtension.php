<?php

namespace digitalastronaut\craftcookiebanner\web\twig;

use Craft;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use yii\helpers\Inflector;

use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\records\Content;

class CookieBannerTwigExtension extends AbstractExtension {
    public function getFunctions() {
        return [
            new TwigFunction('renderCookiesListHtml', [$this, 'renderCookiesListHtml']),
            new TwigFunction('getCookieDetectionOverviewData', [$this, 'getCookieDetectionOverviewData']),
            new TwigFunction('checkCookiesDefenitionForLanguages', [$this, 'checkCookiesDefenitionForLanguages']),
            new TwigFunction('checkVendorDefenitionForLanguages', [$this, 'checkVendorDefenitionForLanguages']),
            new TwigFunction('getVendorOverview', [$this, 'getVendorOverview']),
            new TwigFunction('getVendorOptions', [$this, 'getVendorOptions']),
        ];
    }

    public function renderCookiesListHtml() {
        $currentSiteId = Craft::$app->getSites()->getCurrentSite()->id;
        $content = Content::find()->where(['siteId' => $currentSiteId])->one();
        $settings = CookieBanner::getInstance()->getSettings();

        $cookiesListHtml = Craft::$app->getView()->renderTemplate('cookie-banner/components/_cookiesList.twig', [
            'settings' => $settings,
            'banner' => $content
        ]);

        echo $cookiesListHtml;
    }

    public function getCookieDetectionOverviewData() {
        return CookieBanner::getInstance()->getCookieDetection()->getCookiesOverview();
    }

    public function getVendorOverview() {
        return CookieBanner::getInstance()->getCookieDetection()->getVendorsOverview();
    }

    public function getVendorOptions(): array {
        $content = Content::find()->one()->cookieGroups;

        $options[0] = [
            "label" => "Default",
            "value" => "default"
        ];

        foreach ($content as $vendor) {
            $options[] = [
                "label" => $vendor['name'],
                "value" => Inflector::slug($vendor['name']),
            ];
        }

        return $options;
    }

    public function checkCookiesDefenitionForLanguages($cookieName) {
        $cookieBannerContentAllLanguages = Content::find()->all();

        $result = [];
        
        foreach ($cookieBannerContentAllLanguages as $content) {
            $site = Craft::$app->getSites()->getSiteById($content->siteId);
            $siteKey = $site->name . " (" . $site->language . ")";

            $allCookies = CookieBanner::getInstance()->getCookieDetection()->getBannerCookies($content);
            
            $matchedCookie = null;
            foreach ($allCookies as $cookie) {
                if (isset($cookie['name']) && $this->cookieNameMatches($cookieName, $cookie['name'])) {
                    $matchedCookie = $cookie;
                    break;
                }
            }

            if ($matchedCookie === null) {
                $result[$siteKey] = "not-defined";
            } elseif (!$matchedCookie['enabled']) {
                $result[$siteKey] = "disabled";
            } else {
                $hasPurpose = !empty($matchedCookie['purpose']);
                $hasExpiration = !empty($matchedCookie['expiration']);
                
                if ($hasPurpose && $hasExpiration) $result[$siteKey] = "defined";
                else $result[$siteKey] = "defined-incomplete";
            }
        }

        return $result;
    }

    public function checkVendorDefenitionForLanguages($vendorName) {
        $cookieBannerContentAllLanguages = Content::find()->all();

        $result = [];

        foreach ($cookieBannerContentAllLanguages as $content) {
            $site = Craft::$app->getSites()->getSiteById($content->siteId);
            $siteKey = $site->name . ' (' . $site->language . ')';

            $vendors = $content['cookieGroups'];

            $matchedVendor = null;
            foreach ($vendors as $vendor) {
                if (isset($vendor['name']) && $vendor['name'] === $vendorName) {
                    $matchedVendor = $vendor;
                    break;
                }
            }

            if ($matchedVendor === null) {
                $result[$siteKey] = "not-defined";
            } elseif (empty($matchedVendor['enabled']) || $matchedVendor['enabled'] === "0") {
                $result[$siteKey] = "disabled";
            } else {
                $hasUrl = !empty($matchedVendor['url']);
                $hasDescription = !empty($matchedVendor['description']);

                if ($hasUrl && $hasDescription) $result[$siteKey] = "defined";
                else $result[$siteKey] = "defined-incomplete";
            }
        }

        return $result;
    }

    private function cookieNameMatches(string $cookieName, string $pattern): bool {
        $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/';

        return (bool) preg_match($regex, $cookieName);
    }
}
