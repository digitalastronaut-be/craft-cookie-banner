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

            'vendors',

            'necessaryCookiesTitle',
            'necessaryCookiesLabel',
            'necessaryCookiesDefinition',
            'necessaryCookies',

            'preferenceCookiesTitle',
            'preferenceCookiesLabel',
            'preferenceCookiesDefinition',
            'preferenceCookies',

            'analyticalCookiesTitle',
            'analyticalCookiesLabel',
            'analyticalCookiesDefinition',
            'analyticalCookies',

            'marketingCookiesTitle',
            'marketingCookiesLabel',
            'marketingCookiesDefinition',
            'marketingCookies',

            'uncategorizedCookiesTitle',
            'uncategorizedCookiesLabel',
            'uncategorizedCookiesDefinition',
            'uncategorizedCookies',

            'acceptAllButtonLabel',
            'acceptSelectedButtonLabel',
            'refuseAllButtonLabel',
            'DeterminePreferencesButtonLabel',
            'DetailedPreferencesButtonLabel',
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
                    'vendors',
                    'necessaryCookiesTitle',
                    'necessaryCookiesLabel',
                    'necessaryCookiesDefinition',
                    'necessaryCookies',
                    'preferencelCookiesTitle',
                    'preferencelCookiesLabel',
                    'preferencelCookiesDefinition',
                    'preferencelCookies',
                    'analyticalCookiesTitle',
                    'analyticalCookiesLabel',
                    'analyticalCookiesDefinition',
                    'analyticalCookies',
                    'marketingCookiesTitle',
                    'marketingCookiesLabel',
                    'marketingCookiesDefinition',
                    'marketingCookies',
                    'uncategorizedCookiesTitle',
                    'uncategorizedCookiesLabel',
                    'uncategorizedCookiesDefinition',
                    'uncategorizedCookies',
                    'acceptAllButtonLabel',
                    'acceptSelectedButtonLabel',
                    'refuseAllButtonLabel',
                    'DeterminePreferencesButtonLabel',
                    'DetailedPreferencesButtonLabel',
                ], 
                'safe'
            ],
        ];
    }
}