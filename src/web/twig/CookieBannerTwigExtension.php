<?php
/**
 * Cookie banner plugin for Craft CMS
 *
 * Provides a fully configurable GDPR-compliant cookie banner for
 * Craft CMS. Supports cookie detection/suggestion, consent records, vendor
 * management, and customizable appearance & content — all from within the
 * Craft control panel.
 *
 * @link      https://digitalastronaut.be
 * @copyright Copyright (c) 2026 Digitalastronaut
 */

namespace digitalastronaut\craftcookiebanner\web\twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use digitalastronaut\craftcookiebanner\CookieBanner;

/**
 * Class CookieBannerTwigExtension
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 */
class CookieBannerTwigExtension extends AbstractExtension {
    /**
     * @return TwigFunction[]
     */
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

    /**
     * @return array
     */
    public function getCookieDetectionOverviewData(): array {
        return CookieBanner::getInstance()->getCookieDetection()->getCookiesOverview();
    }

    /**
     * @return array
     */
    public function getVendorOverview(): array {
        return CookieBanner::getInstance()->getCookieDetection()->getVendorsOverview();
    }

    /**
     * @param string $searchTerm
     * @return array
     */
    public function searchCookieDatabase(string $searchTerm): array {
        return CookieBanner::getInstance()->getCookieDetection()->searchCookieDatabase($searchTerm);
    }

    /**
     * @param string $searchTerm
     * @return array
     */
    public function searchVendorDatabase(string $searchTerm): array {
        return CookieBanner::getInstance()->getCookieDetection()->searchVendorDatabase($searchTerm);
    }

    /**
     * @param string $cookieName
     * @return array
     */
    public function checkCookieDefinitionForEachSite(string $cookieName): array {
        return CookieBanner::getInstance()->getCookiesAndVendors()->checkCookieDefinitionForEachSite($cookieName);
    }

    /**
     * @param string $vendorName
     * @return array
     */
    public function checkVendorDefinitionForEachSite(string $vendorName): array {
        return CookieBanner::getInstance()->getCookiesAndVendors()->checkVendorDefinitionForEachSite($vendorName);
    }

    /**
     * @return string
     */
    public function renderCookiesListHtml(): string {
        return CookieBanner::getInstance()->getCookiesAndVendors()->getCookiesListHtml();
    }

    /**
     * @return array
     */
    public function getVendorSelectFieldOptions(): array {
        return CookieBanner::getInstance()->getCookiesAndVendors()->getVendorSelectFieldOptions();
    }
}
