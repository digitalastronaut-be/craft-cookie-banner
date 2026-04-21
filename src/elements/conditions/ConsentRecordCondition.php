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

namespace digitalastronaut\craftcookiebanner\elements\conditions;

use craft\elements\conditions\ElementCondition;

/**
 * Class ConsentRecordCondition
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 */
class ConsentRecordCondition extends ElementCondition {
    /**
     * @return array
     */
    protected function selectableConditionRules(): array {
        return array_merge(parent::selectableConditionRules(), [
            // ...
        ]);
    }
}
