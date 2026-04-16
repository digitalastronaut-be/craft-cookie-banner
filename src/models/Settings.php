<?php

namespace digitalastronaut\craftcookiebanner\models;

use Craft;
use craft\base\Model;

/**
 * Cookie banner settings
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

            [['cookieBannerVersion', 'privacyPolicyVersion', 'cookiePolicyVersion'], 'string'],
            [['cookieBannerVersion', 'privacyPolicyVersion', 'cookiePolicyVersion'], 'match', 'pattern' => '/^v\d+\.\d+\.\d+$/'],

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
