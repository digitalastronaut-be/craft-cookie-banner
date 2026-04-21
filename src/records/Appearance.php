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
 */
class Appearance extends ActiveRecord {
    public static function tableName(): string {
        return Table::COOKIE_BANNER_APPEARANCE;
    }

    public function getSite(): ActiveQueryInterface {
        return $this->hasOne(Site::class, ['id' => 'siteId']);
    }

    public function fields() {
        $fields = [
            'cookieBannerPosition',
            'preferencesAction',
            'showCookieCategoriesPreview',
            'showCookieTables',

            'buttonSize',
            'bannerStyle',
            'cookieListStyle',

            'titleFont',
            'textFont',

            'bannerBackground',
            'bannerBorderColor',
            'titleColor',
            'textColor',
            'linkColor',
            'linkHoverColor',

            'bannerBorderThickness',
            'bannerOverlayOpacity',

            'buttonBackground',
            'buttonColor',
            'buttonBorderColor',
            'buttonHoverBackground',
            'buttonHoverColor',
            'buttonHoverBorderColor',
            'buttonBorderThickness',

            'toggleBackgroundOff',
            'toggleBackgroundOn',
            'toggleColor',

            'cookieGroupTitleColor',
            'cookieGroupTextColor',
            'cookieGroupLinkColor',
            'cookieGroupLinkHoverColor',
            'cookieGroupBackground',
            'cookieGroupBorderColor',
            'cookieGroupHoverBackground',
            'cookieGroupHoverBorderColor',
            'cookieGroupBorderThickness',

            'cookieTableTitleColor',
            'cookieTableTextColor',
            'cookieTableBackground',
            'cookieTableBorderColor',
            'cookieTableBorderThickness',

            'css',
        ];

        return array_merge($fields, parent::fields());
    }

    public function rules(): array {
        return [
            [
                [
                    'cookieBannerPosition',
                    'preferencesAction',
                    'showCookieCategoriesPreview',
                    'showCookieTables',

                    'buttonSize',
                    'bannerStyle',
                    'cookieListStyle',

                    'titleFont',
                    'textFont',

                    'bannerBackground',
                    'bannerBorderColor',
                    'titleColor',
                    'textColor',
                    'linkColor',
                    'linkHoverColor',

                    'bannerBorderThickness',
                    'bannerOverlayOpacity',

                    'buttonBackground',
                    'buttonColor',
                    'buttonBorderColor',
                    'buttonHoverBackground',
                    'buttonHoverColor',
                    'buttonHoverBorderColor',
                    'buttonBorderThickness',

                    'toggleBackgroundOff',
                    'toggleBackgroundOn',
                    'toggleColor',

                    'cookieGroupTitleColor',
                    'cookieGroupTextColor',
                    'cookieGroupLinkColor',
                    'cookieGroupLinkHoverColor',
                    'cookieGroupBackground',
                    'cookieGroupBorderColor',
                    'cookieGroupHoverBackground',
                    'cookieGroupHoverBorderColor',
                    'cookieGroupBorderThickness',

                    'cookieTableTitleColor',
                    'cookieTableTextColor',
                    'cookieTableBackground',
                    'cookieTableBorderColor',
                    'cookieTableBorderThickness',

                    'css',
                ],
                'safe'
            ],
        ];
    }
}