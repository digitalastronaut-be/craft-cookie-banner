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
            'essentialCookiesDefinition',

            'functionalCookiesTitle',
            'functionalCookiesDefinition',

            'analyticalCookiesTitle',
            'analyticalCookiesDefinition',

            'advertisementCookiesTitle',
            'advertisementCookiesDefinition',

            'personalizationCookiesTitle',
            'personalizationCookiesDefinition',

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
                    'essentialCookiesDefinition', 
                    'functionalCookiesTitle', 
                    'functionalCookiesDefinition', 
                    'analyticalCookiesTitle', 
                    'analyticalCookiesDefinition', 
                    'advertisementCookiesTitle', 
                    'advertisementCookiesDefinition', 
                    'personalizationCookiesTitle', 
                    'personalizationCookiesDefinition', 
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
                    'personalizationCookies'
                ], 
                'safe'
            ],
        ];
    }
}