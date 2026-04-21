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

namespace digitalastronaut\craftcookiebanner\services;

/**
 * Class CookieBanner
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 */
trait ServicesTrait {
    /**
     * @return array
     */
    public static function config(): array {
        return [
            'components' => [
                'cookieDetection' => CookieDetectionService::class,
                'consentRecords' => ConsentRecordsService::class,
                'cookiesAndVendors' => CookiesAndVendorsService::class,
            ],
        ];
    }

    /**
     * @return CookieDetectionService|null
     */
    public function getCookieDetection(): CookieDetectionService {
        return $this->get('cookieDetection');
    }

    /**
     * @return ConsentRecordsService|null
     */
    public function getConsentRecords(): ConsentRecordsService {
        return $this->get('consentRecords');
    }

    /**
     * @return CookiesAndVendorsService|null
     */
    public function getCookiesAndVendors(): CookiesAndVendorsService {
        return $this->get('cookiesAndVendors');
    }
}