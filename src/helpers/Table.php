<?php

namespace digitalastronaut\craftcookiebanner\helpers;

use craft\db\Table as CraftTable;

abstract class Table extends CraftTable {
    public const COOKIE_BANNER_CONSENT_RECORDS = '{{%cookie_banner_consent_records}}';
    public const COOKIE_BANNER_CONTENT = '{{%cookie_banner_content}}';
    public const COOKIE_BANNER_APPEARANCE = '{{%cookie_banner_appearance}}';
}