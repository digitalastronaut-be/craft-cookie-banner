<?php

namespace digitalastronaut\craftcookiebanner\records;

use craft\db\ActiveRecord;
use craft\records\Site;

use yii\db\ActiveQueryInterface;

use digitalastronaut\craftcookiebanner\helpers\Table;

class Content extends ActiveRecord {
    public static function tableName(): string {
        return Table::COOKIE_BANNER_CONTENT;
    }

    public function getSite(): ActiveQueryInterface {
        return $this->hasOne(Site::class, ['id' => 'siteId']);
    }

    public function fields() {
        $fields = [
            'title',
            'text',

            'privacyPolicyLinkLabel',
            'privacyPolicyLinkURL',
            'cookiePolicyLinkLabel',
            'cookiePolicyLinkURL',

            'essentialCookiesTitle',
            'essentialCookiesLabel',
            'essentialCookiesDefinition',

            'functionalCookiesTitle',
            'functionalCookiesLabel',
            'functionalCookiesDefinition',

            'analyticalCookiesTitle',
            'analyticalCookiesLabel',
            'analyticalCookiesDefinition',

            'advertisementCookiesTitle',
            'advertisementCookiesLabel',
            'advertisementCookiesDefinition',

            'personalizationCookiesTitle',
            'personalizationCookiesLabel',
            'personalizationCookiesDefinition',

            'uncategorizedCookiesTitle',
            'uncategorizedCookiesLabel',
            'uncategorizedCookiesDefinition',

            'acceptAllButtonLabel',
            'acceptSelectedButtonLabel',
            'refuseAllButtonLabel',
            'DeterminePreferencesButtonLabel',
            'DetailedPreferencesButtonLabel',
            
            'cookieGroups',
            'essentialCookies',
            'functionalCookies',
            'analyticalCookies',
            'advertisementCookies',
            'personalizationCookies',
            'uncategorizedCookies',
        ];

        return array_merge($fields, parent::fields());
    }

    public function rules(): array {
        return [
            [
                [
                    'title', 
                    'text', 
                    'privacyPolicyLinkLabel',
                    'privacyPolicyLinkURL',
                    'cookiePolicyLinkLabel',
                    'cookiePolicyLinkURL',
                    'essentialCookiesTitle', 
                    'essentialCookiesLabel',
                    'essentialCookiesDefinition', 
                    'functionalCookiesTitle', 
                    'functionalCookiesLabel',
                    'functionalCookiesDefinition', 
                    'analyticalCookiesTitle', 
                    'analyticalCookiesLabel',
                    'analyticalCookiesDefinition', 
                    'advertisementCookiesTitle', 
                    'advertisementCookiesLabel',
                    'advertisementCookiesDefinition', 
                    'personalizationCookiesTitle', 
                    'personalizationCookiesLabel',
                    'personalizationCookiesDefinition', 
                    'uncategorizedCookiesTitle',
                    'uncategorizedCookiesLabel',
                    'uncategorizedCookiesDefinition',
                    'acceptAllButtonLabel', 
                    'acceptSelectedButtonLabel', 
                    'refuseAllButtonLabel', 
                    'determinePreferencesButtonLabel', 
                    'detailedPreferencesButtonLabel', 
                    'cookieGroups', 
                    'essentialCookies', 
                    'functionalCookies', 
                    'analyticalCookies', 
                    'advertisementCookies', 
                    'personalizationCookies',
                    'uncategorizedCookies',
                ], 
                'safe'
            ],
        ];
    }
}