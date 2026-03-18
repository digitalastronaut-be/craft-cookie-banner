<?php

namespace digitalastronaut\craftcookiebanner\elements;

use Craft;
use craft\base\Element;
use craft\elements\User;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\db\ElementQueryInterface;
use craft\enums\Color;
use craft\helpers\Db;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use yii\web\Response;

use digitalastronaut\craftcookiebanner\elements\conditions\ConsentRecordCondition;
use digitalastronaut\craftcookiebanner\elements\db\ConsentRecordQuery;
use digitalastronaut\craftcookiebanner\helpers\Table;

use DateTime;

class ConsentRecord extends Element {
    private ?FieldLayout $fieldLayout = null;
    public ?string $ipAddressHash = null;
    public ?string $sessionId = null;
    public ?string $userAgent = null;
    public ?string $language = null;
    public ?DateTime $consentTimestamp = null;
    public ?DateTime $consentExpiry = null;
    public ?string $consentAction = null;
    public ?bool $essentialCookies = null;
    public ?bool $functionalCookies = null;
    public ?bool $analyticalCookies = null;
    public ?bool $advertisementCookies = null;
    public ?bool $personalizationCookies = null;
    public ?string $consentMethod = null;
    public ?string $bannerVersion = null;
    public ?string $privacyPolicyVersion = null;
    public ?string $cookiePolicyVersion = null;

    public static function displayName(): string { return Craft::t('cookie-banner', 'Consent record'); }
    public static function lowerDisplayName(): string { return Craft::t('cookie-banner', 'consent record'); }
    public static function pluralDisplayName(): string { return Craft::t('cookie-banner', 'Consent records'); }
    public static function pluralLowerDisplayName(): string { return Craft::t('cookie-banner', 'consent records'); }
    public static function refHandle(): ?string { return 'consentrecord'; }

    public static function trackChanges(): bool { return true; }
    public static function hasTitles(): bool { return true; }
    public static function hasUris(): bool { return false; }
    public static function isLocalized(): bool { return false; }
    public static function hasStatuses(): bool { return true; }

    public static function statuses(): array {
        return [
            'valid' => ['label' => 'Valid', 'color' => Color::Teal],
            'expired' => ['label' => 'Expired', 'color' => Color::Red],
        ];
    }

    public function getStatus(): ?string {
        if (new DateTime() < $this->consentExpiry) return 'valid';

        return 'expired';
    }

    public static function find(): ElementQueryInterface {
        return Craft::createObject(ConsentRecordQuery::class, [static::class]);
    }

    public static function createCondition(): ElementConditionInterface {
        return Craft::createObject(ConsentRecordCondition::class, [static::class]);
    }

    protected static function defineSearchableAttributes(): array {
        return ['ipAddressHash', 'userAgent'];
    }

    protected static function defineSources(string $context): array {
        // $years = [];
        // $yearSources = [];

        // foreach (ConsentRecord::find()->all() as $consentRecord) {
        //     $years[] = $consentRecord->consentTimestamp->format('Y');
        // }

        // $years = array_unique($years);
        // rsort($years, SORT_NUMERIC);

        // foreach ($years as $year) {
        //     $nextYear = $year + 1;

        //     $yearSources[] = [
        //         'key' => "year:{$year}",
        //         'label' => $year,
        //         'criteria' => [
        //             'consentTimestampFrom' => "{$year}-01-01",
        //             'consentTimestampTo' => "{$nextYear}-01-01",
        //         ],
        //     ];
        // }

        $sources = [
            [
                'key' => 'all',
                'label' => Craft::t('cookie-banner', 'All consent records'),
                // 'nested' => $yearSources,
            ],
            // [
            //     'key' => 'statusValid',
            //     'label' => Craft::t('cookie-banner', 'Active records'),
            //     'criteria' => ['isExpired' => false],
            //     'status' => 'teal',
            // ],
            // [
            //     'key' => 'statusExpired',
            //     'label' => Craft::t('cookie-banner', 'Expired records'),
            //     'criteria' => ['isExpired' => true],
            //     'status' => 'red',
            // ],
        ];

        return $sources;
    }

    protected static function defineActions(string $source): array {
        $actions = parent::defineActions($source);

        return $actions;
    }

    protected static function defineSortOptions(): array {
        return ['title' => Craft::t('app', 'Title')];
    }

    protected static function defineTableAttributes(): array {
        return [
            'status' => ['label' => 'Status'],
            'ipAddressHash' => ['label' => 'IP address Hash'],
            'sessionId' => ['label' => 'Session ID'],
            'userAgent' => ['label' => 'User Agent'],
            'language' => ['label' => 'Language'],
            'consentTimestamp' => ['label' => 'Timestamp'],
            'consentExpiry' => ['label' => 'Expiry'],
            'consentAction' => ['label' => 'Action'],
            'consentMethod' => ['label' => 'Method'],
            'essentialCookies' => ['label' => 'Necessary'],
            'functionalCookies' => ['label' => 'Preference'],
            'analyticalCookies' => ['label' => 'Analytical'],
            'advertisementCookies' => ['label' => 'Marketing'],
            'personalizationCookies' => ['label' => 'Uncategorized'],
            'bannerVersion' => ['label' => 'Banner Version'],
            'privacyPolicyVersion' => ['label' => 'Privacy Policy Version'],
            'cookiePolicyVersion' => ['label' => 'Cookie Policy Version'],
        ];
    }

    protected static function defineDefaultTableAttributes(string $source): array {
        return [
            'status',
            'consentTimestamp', 
            'essentialCookies', 
            'functionalCookies', 
            'analyticalCookies', 
            'advertisementCookies', 
            'personalizationCookies'
        ];
    }

    protected function defineRules(): array {
        return array_merge(parent::defineRules(), [
            [
                ['consentAction'],
                'required'
            ],
            [
                [
                    'ipAddressHash', 
                    'sessionId', 
                    'userAgent', 
                    'consentAction', 
                    'consentMethod', 
                    'bannerVersion', 
                    'privacyPolicyVersion', 
                    'cookiePolicyVersion'
                ], 
                'string'
            ],
            [
                [
                    'essentialCookies', 
                    'functionalCookies', 
                    'analyticalCookies', 
                    'advertisementCookies', 
                    'personalizationCookies'
                ], 
                'boolean'
            ],
            [
                ['sessionId'], 
                'string', 'max' => 255
            ],
            [
                ['consentAction'], 
                'in',
                'range' => [
                    'Accept all', 
                    'Accept selected', 
                    'Refuse all'
                ]
            ],
        ]);
    }

    public function cpEditUrl(): string {
        return "cookie-banner/consent-records/{$this->id}";
    }

    protected function route(): array|string|null {
        return ['templates/render', [
            'template' => 'site/template/path',
            'variables' => ['consentRecord' => $this],
        ]];
    }

    public function getFieldLayout(): ?FieldLayout
    {
        if ($this->fieldLayout !== null) {
            return $this->fieldLayout;
        }

        $this->fieldLayout = Craft::$app->getFields()->getLayoutByType(self::class);

        return $this->fieldLayout;
    }

    public function canView(User $user): bool { return true; }
    public function canSave(User $user): bool { return false; }
    public function canDuplicate(User $user): bool {return false; }
    public function canDelete(User $user): bool { return true; }
    public function canCreateDrafts(User $user): bool { return false; }

    public function prepareEditScreen(Response $response, string $containerId): void {
        $response->crumbs([
            [
                'label' => self::pluralDisplayName(),
                'url' => UrlHelper::cpUrl('cookie-banner/consent-records'),
            ],
        ]);
    }

    public function afterSave(bool $isNew): void {
        if (!$this->propagating) {
            Db::upsert(Table::COOKIE_BANNER_CONSENT_RECORDS, [
                'id' => $this->id,
                'ipAddressHash' => $this->ipAddressHash,
                'sessionId' => $this->sessionId,
                'userAgent' => $this->userAgent,
                'language' => $this->language,
                'consentTimestamp' => Db::prepareDateForDb($this->consentTimestamp),
                'consentExpiry' => Db::prepareDateForDb($this->consentExpiry),
                'consentAction' => $this->consentAction,
                'consentMethod' => $this->consentMethod,
                'essentialCookies' => $this->essentialCookies,
                'functionalCookies' => $this->functionalCookies,
                'analyticalCookies' => $this->analyticalCookies,
                'advertisementCookies' => $this->advertisementCookies,
                'personalizationCookies' => $this->personalizationCookies,
                'bannerVersion' => $this->bannerVersion,
                'privacyPolicyVersion' => $this->privacyPolicyVersion,
                'cookiePolicyVersion' => $this->cookiePolicyVersion,
            ], [
                'ipAddressHash' => $this->ipAddressHash,
                'sessionId' => $this->sessionId,
                'userAgent' => $this->userAgent,
                'language' => $this->language,
                'consentTimestamp' => Db::prepareDateForDb($this->consentTimestamp),
                'consentExpiry' => Db::prepareDateForDb($this->consentExpiry),
                'consentAction' => $this->consentAction,
                'essentialCookies' => $this->essentialCookies,
                'functionalCookies' => $this->functionalCookies,
                'analyticalCookies' => $this->analyticalCookies,
                'advertisementCookies' => $this->advertisementCookies,
                'personalizationCookies' => $this->personalizationCookies,
                'consentMethod' => $this->consentMethod,
                'bannerVersion' => $this->bannerVersion,
                'privacyPolicyVersion' => $this->privacyPolicyVersion,
                'cookiePolicyVersion' => $this->cookiePolicyVersion,
            ]);
        }

        parent::afterSave($isNew);
    }
}
