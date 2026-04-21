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

namespace digitalastronaut\craftcookiebanner\models;

use craft\base\Model;

/**
 * Class Settings
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 */
class Settings extends Model {
    public bool $cookieBannerEnabled = true;
    public string $bannerVersion = 'v1.0.0';
    public string $privacyPolicyVersion = 'v1.0.0';
    public string $cookiePolicyVersion = 'v1.0.0';
    public string $consentRecordRetention = 'sixMonths';

    public array $gettingStartedProgress = [
        "legalPagesStepCompleted" => false,
        "deferScriptsStepCompleted" => false,
        "contentStepCompleted" => false,
        "appearanceStepCompleted" => false,
        "finalSettingsStepCompleted" => false,
    ];

    public array $blacklistedCookies = [];
    public array $blacklistedVendors = [];

    protected function defineRules(): array {
        return [
            [['cookieBannerEnabled'], 'boolean'],

            [['bannerVersion', 'privacyPolicyVersion', 'cookiePolicyVersion'], 'string'],
            [['bannerVersion', 'privacyPolicyVersion', 'cookiePolicyVersion'], 'match', 'pattern' => '/^v\d+\.\d+\.\d+$/'],

            [['consentRecordRetention'], 'string'],
            [['consentRecordRetention'], 'in', 'range' => [
                'oneWeek',
                'oneMonth',
                'sixMonths',
                'oneYear',
                'fiveYears',
            ]],

            [['gettingStartedProgress', 'blacklistedCookies', 'blacklistedVendors'], 'safe'],

            ['gettingStartedProgress', function ($attribute) {
                $requiredKeys = [
                    "legalPagesStepCompleted",
                    "deferScriptsStepCompleted",
                    "contentStepCompleted",
                    "appearanceStepCompleted",
                    "finalSettingsStepCompleted",
                ];

                foreach ($requiredKeys as $key) {
                    if (!array_key_exists($key, $this->$attribute) || !is_bool($this->$attribute[$key])) {
                        $this->addError($attribute, "Invalid gettingStartedProgress structure.");
                        return;
                    }
                }
            }],
        ];
    }
}
