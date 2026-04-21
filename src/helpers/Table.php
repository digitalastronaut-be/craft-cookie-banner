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

use craft\db\Table as CraftTable;

/**
 * Summary of Table
 */
abstract class Table extends CraftTable {
    public const COOKIE_BANNER_CONSENT_RECORDS = '{{%cookie_banner_consent_records}}';
    public const COOKIE_BANNER_CONTENT = '{{%cookie_banner_content}}';
    public const COOKIE_BANNER_APPEARANCE = '{{%cookie_banner_appearance}}';
}