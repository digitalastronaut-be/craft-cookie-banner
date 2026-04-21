<?php

namespace digitalastronaut\craftcookiebanner\records;

use craft\db\ActiveRecord;
use craft\records\Site;

use yii\db\ActiveQueryInterface;

use digitalastronaut\craftcookiebanner\helpers\Table;

/**
 * Class Appearance
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 * 
 * @property int $id
 * @property int $siteId
 * @property string|null $title
 * @property string|null $text
 * @property string|null $privacyPolicyLinkLabel
 * @property string|null $privacyPolicyLinkURL
 * @property string|null $cookiePolicyLinkLabel
 * @property string|null $cookiePolicyLinkURL
 * 
 * @property array|null $vendors
 * 
 * @property string|null $necessaryCookiesTitle
 * @property string|null $necessaryCookiesLabel
 * @property string|null $necessaryCookiesDefinition
 * @property array|null $necessaryCookies
 * 
 * @property string|null $preferenceCookiesTitle
 * @property string|null $preferenceCookiesLabel
 * @property string|null $preferenceCookiesDefinition
 * @property array|null $preferenceCookies
 * 
 * @property string|null $analyticalCookiesTitle
 * @property string|null $analyticalCookiesLabel
 * @property string|null $analyticalCookiesDefinition
 * @property array|null $analyticalCookies
 * 
 * @property string|null $marketingCookiesTitle
 * @property string|null $marketingCookiesLabel
 * @property string|null $marketingCookiesDefinition
 * @property array|null $marketingCookies
 * 
 * @property string|null $uncategorizedCookiesTitle
 * @property string|null $uncategorizedCookiesLabel
 * @property string|null $uncategorizedCookiesDefinition
 * @property array|null $uncategorizedCookies
 * 
 * @property string|null $acceptAllButtonLabel
 * @property string|null $acceptSelectedButtonLabel
 * @property string|null $refuseAllButtonLabel
 * @property string|null $determinePreferencesButtonLabel
 * @property string|null $detailedPreferencesButtonLabel
 */
class Content extends ActiveRecord {
    /**
     * @return string
     */
    public static function tableName(): string {
        return Table::COOKIE_BANNER_CONTENT;
    }

    /**
     * @return ActiveQueryInterface
     */
    public function getSite(): ActiveQueryInterface {
        return $this->hasOne(Site::class, ['id' => 'siteId']);
    }

    /**
     * @return string[]
     */
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
            'determinePreferencesButtonLabel',
            'detailedPreferencesButtonLabel',
        ];

        return array_merge($fields, parent::fields());
    }

    public function rules(): array {
        return [
            [['siteId'], 'required'],
            [['siteId'], 'integer'],

            [
                [
                    'title',
                    'privacyPolicyLinkLabel',
                    'privacyPolicyLinkURL',
                    'cookiePolicyLinkLabel',
                    'cookiePolicyLinkURL',
                    'necessaryCookiesTitle',
                    'necessaryCookiesLabel',
                    'necessaryCookiesDefinition',
                    'preferenceCookiesTitle',
                    'preferenceCookiesLabel',
                    'preferenceCookiesDefinition',
                    'analyticalCookiesTitle',
                    'analyticalCookiesLabel',
                    'analyticalCookiesDefinition',
                    'marketingCookiesTitle',
                    'marketingCookiesLabel',
                    'marketingCookiesDefinition',
                    'uncategorizedCookiesTitle',
                    'uncategorizedCookiesLabel',
                    'uncategorizedCookiesDefinition',
                    'acceptAllButtonLabel',
                    'acceptSelectedButtonLabel',
                    'refuseAllButtonLabel',
                    'determinePreferencesButtonLabel',
                    'detailedPreferencesButtonLabel',
                    'privacyPolicyLinkURL',
                    'cookiePolicyLinkURL',
                ],
                'string',
                'max' => 255,
            ],

            [['text'], 'string', 'max' => 65535],

            [
                [
                    'vendors',
                    'necessaryCookies',
                    'preferenceCookies',
                    'analyticalCookies',
                    'marketingCookies',
                    'uncategorizedCookies',
                ],
                'safe',
            ],
        ];
    }
}