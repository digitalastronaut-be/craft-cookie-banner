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

namespace digitalastronaut\craftcookiebanner\helpers;

/**
 * Class EventHandlers
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 */
abstract class CookieBanner {
    public const COOKIE_CATEGORIES = [
        'necessaryCookies',
        'preferenceCookies',
        'analyticalCookies',
        'marketingCookies',
        'uncategorizedCookies'
    ];

    public const CONTROL_PANEL_COOKIES = [
        "Craft-*",
        "*_identity",
        "*_username",
    ];

    public const EXAMPLE_USER_AGENTS = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
        'Mozilla/5.0 (X11; Linux x86_64)',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X)',
        'Mozilla/5.0 (Android 11; Mobile; rv:90.0)',
    ];
}