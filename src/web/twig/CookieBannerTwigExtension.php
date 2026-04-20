<?php

namespace digitalastronaut\craftcookiebanner\web\twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use digitalastronaut\craftcookiebanner\CookieBanner;

class CookieBannerTwigExtension extends AbstractExtension {
    public function getFunctions() {
        return [
            new TwigFunction('renderCookiesListHtml', [$this, 'renderCookiesListHtml'], ['is_safe' => ['html']]),
            new TwigFunction('getCookieDetectionOverviewData', [$this, 'getCookieDetectionOverviewData']),
            new TwigFunction('checkCookieDefinitionForEachSite', [$this, 'checkCookieDefinitionForEachSite']),
            new TwigFunction('checkVendorDefinitionForEachSite', [$this, 'checkVendorDefinitionForEachSite']),
            new TwigFunction('getVendorOverview', [$this, 'getVendorOverview']),
            new TwigFunction('getVendorSelectFieldOptions', [$this, 'getVendorSelectFieldOptions']),
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
        return CookieBanner::getInstance()->getCookiesAndVendors()->getCookiesListHtml();
    }

    public function getVendorSelectFieldOptions(): array {
        return CookieBanner::getInstance()->getCookiesAndVendors()->getVendorSelectFieldOptions();
    }
}
