<?php

namespace digitalastronaut\craftcookiebanner\elements\conditions;

use Craft;
use craft\elements\conditions\ElementCondition;

/**
 * Consent Record condition
 */
class ConsentRecordCondition extends ElementCondition
{
    protected function selectableConditionRules(): array
    {
        return array_merge(parent::selectableConditionRules(), [
            // ...
        ]);
    }
}
