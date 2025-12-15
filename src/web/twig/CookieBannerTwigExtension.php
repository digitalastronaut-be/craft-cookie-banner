<?php

namespace digitalastronaut\craftcookiebanner\web\twig;

use Craft;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\records\Content;

class CookieBannerTwigExtension extends AbstractExtension {
    public function getFunctions() {
        return [
            new TwigFunction('renderCookiesListHtml', [$this, 'renderCookiesListHtml']),
            new TwigFunction('getCookieDetectionOverviewData', [$this, 'getCookieDetectionOverviewData']),
            new TwigFunction('checkCookiesDefenitionForLanguages', [$this, 'checkCookiesDefenitionForLanguages']),
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

    public function checkCookiesDefenitionForLanguages($cookieName) {
        $cookieBannerContentAllLanguages = Content::find()->all();

        $result = [];
        
        foreach ($cookieBannerContentAllLanguages as $content) {
            $site = Craft::$app->getSites()->getSiteById($content->siteId);
            $siteKey = $site->handle . " (" . $site->language . ")";
            
            $allCookies = array_merge(
                is_array($content["essentialCookies"]) ? $content["essentialCookies"] : [],
                is_array($content["functionalCookies"]) ? $content["functionalCookies"] : [],
                is_array($content["analyticalCookies"]) ? $content["analyticalCookies"] : [],
                is_array($content["advertisementCookies"]) ? $content["advertisementCookies"] : [],
                is_array($content["personalizationCookies"]) ? $content["personalizationCookies"] : [],
                is_array($content["uncategorizedCookies"]) ? $content["uncategorizedCookies"] : []
            );

            $matchedCookie = null;
            foreach ($allCookies as $cookie) {
                if (isset($cookie['name']) && $this->cookieNameMatches($cookieName, $cookie['name'])) {
                    $matchedCookie = $cookie;
                    break;
                }
            }

            if ($matchedCookie === null) {
                $result[$siteKey] = "not-defined";
            } else {
                $hasPurpose = !empty($matchedCookie['purpose']);
                $hasExpiration = !empty($matchedCookie['expiration']);
                
                if ($hasPurpose && $hasExpiration) $result[$siteKey] = "defined";
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
