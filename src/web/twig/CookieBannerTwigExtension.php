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
            new TwigFunction('checkCookieDefinitionForEachSite', [$this, 'checkCookieDefinitionForEachSite']),
            new TwigFunction('checkVendorDefinitionForEachSite', [$this, 'checkVendorDefinitionForEachSite']),
            new TwigFunction('getVendorOverview', [$this, 'getVendorOverview']),
            new TwigFunction('getVendorOptions', [$this, 'getVendorOptions']),
            new TwigFunction('searchCookieDatabase', [$this, 'searchCookieDatabase']),
            new TwigFunction('searchVendorDatabase', [$this, 'searchVendorDatabase']),
        ];
    }

    public function getCookieDetectionOverviewData() {
        return CookieBanner::getInstance()->getCookieDetection()->getCookiesOverview();
    }

    public function getVendorOverview() {
        return CookieBanner::getInstance()->getCookieDetection()->getVendorsOverview();
    }

    public function searchCookieDatabase($searchTerm) {
        return CookieBanner::getInstance()->getCookieDetection()->searchCookieDatabase($searchTerm);
    }

    public function searchVendorDatabase($searchTerm) {
        return CookieBanner::getInstance()->getCookieDetection()->searchVendorDatabase($searchTerm);
    }

    public function checkCookieDefinitionForEachSite($cookieName) {
        return CookieBanner::getInstance()->getCookiesAndVendors()->checkCookieDefinitionForEachSite($cookieName);
    }

    public function checkVendorDefinitionForEachSite($vendorName) {
        return CookieBanner::getInstance()->getCookiesAndVendors()->checkVendorDefinitionForEachSite($vendorName);
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

    public function getVendorOptions(): array {
        $content = Content::find()->one()->cookieGroups;

        $options[0] = [
            "label" => Craft::t("cookie-banner", "Default"),
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
}
