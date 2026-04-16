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
    public ?string $consentAction = null;
    public ?bool $necessaryCookies = null;
    public ?bool $preferenceCookies = null;
    public ?bool $analyticalCookies = null;
    public ?bool $marketingCookies = null;
    public ?bool $uncategorizedCookies = null;
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
        ];
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
        $grouped = [];

        foreach (ConsentRecord::find()->all() as $consentRecord) {
            $date = $consentRecord->consentTimestamp;

            $year = (int)$date->format('Y');
            $month = (int)$date->format('m');
            $monthLabel = $date->format('F');

            if (!isset($grouped[$year])) {
                $grouped[$year] = [];
            }

            $grouped[$year][$month] = $monthLabel;
        }

        krsort($grouped);

        $sources = [
            [
                'key' => 'all',
                'label' => Craft::t('cookie-banner', 'All consent records'),
            ],
        ];

        foreach ($grouped as $year => $months) {
            krsort($months);

            $monthSources = [];

            foreach ($months as $monthNumber => $monthLabel) {
                $from = sprintf('%04d-%02d-01', $year, $monthNumber);
                $to = date('Y-m-d', strtotime("$from +1 month"));

                $monthSources[] = [
                    'key' => "month:{$year}-{$monthNumber}",
                    'label' => $monthLabel,
                    'criteria' => [
                        'consentTimestampFrom' => $from,
                        'consentTimestampTo' => $to,
                    ],
                ];
            }

            $sources[] = [
                'key' => "year:{$year}",
                'label' => (string)$year,
                'criteria' => [
                    'consentTimestampFrom' => "{$year}-01-01",
                    'consentTimestampTo' => ($year + 1) . "-01-01",
                ],
                'nested' => $monthSources,
            ];
        }

        return $sources;
    }

    protected static function defineActions(string $source): array {
        $actions = parent::defineActions($source);

        return $actions;
    }

    protected static function defineSortOptions(): array {
        return [
            'title' => Craft::t('app', 'Title'),
            'consentTimestamp' => Craft::t('app', 'Title')
        ];
    }

    protected static function defineTableAttributes(): array {
        return [
            'status' => ['label' => 'Status'],
            'ipAddressHash' => ['label' => 'IP address Hash'],
            'sessionId' => ['label' => 'Session ID'],
            'userAgent' => ['label' => 'User Agent'],
            'language' => ['label' => 'Language'],
            'consentTimestamp' => ['label' => 'Timestamp'],
            'consentAction' => ['label' => 'Action'],
            'consentMethod' => ['label' => 'Method'],
            'necessaryCookies' => ['label' => 'Necessary'],
            'preferenceCookies' => ['label' => 'Preference'],
            'analyticalCookies' => ['label' => 'Analytical'],
            'marketingCookies' => ['label' => 'Marketing'],
            'uncategorizedCookies' => ['label' => 'Uncategorized'],
            'bannerVersion' => ['label' => 'Banner Version'],
            'privacyPolicyVersion' => ['label' => 'Privacy Policy Version'],
            'cookiePolicyVersion' => ['label' => 'Cookie Policy Version'],
        ];
    }

    protected static function defineDefaultTableAttributes(string $source): array {
        return [
            'consentTimestamp', 
            'necessaryCookies', 
            'preferenceCookies', 
            'analyticalCookies', 
            'marketingCookies', 
            'uncategorizedCookies'
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
                    'necessaryCookies', 
                    'preferenceCookies', 
                    'analyticalCookies', 
                    'marketingCookies', 
                    'uncategorizedCookies'
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
                'consentAction' => $this->consentAction,
                'consentMethod' => $this->consentMethod,
                'necessaryCookies' => $this->necessaryCookies,
                'preferenceCookies' => $this->preferenceCookies,
                'analyticalCookies' => $this->analyticalCookies,
                'marketingCookies' => $this->marketingCookies,
                'uncategorizedCookies' => $this->uncategorizedCookies,
                'bannerVersion' => $this->bannerVersion,
                'privacyPolicyVersion' => $this->privacyPolicyVersion,
                'cookiePolicyVersion' => $this->cookiePolicyVersion,
            ], [
                'ipAddressHash' => $this->ipAddressHash,
                'sessionId' => $this->sessionId,
                'userAgent' => $this->userAgent,
                'language' => $this->language,
                'consentTimestamp' => Db::prepareDateForDb($this->consentTimestamp),
                'consentAction' => $this->consentAction,
                'necessaryCookies' => $this->necessaryCookies,
                'preferenceCookies' => $this->preferenceCookies,
                'analyticalCookies' => $this->analyticalCookies,
                'marketingCookies' => $this->marketingCookies,
                'uncategorizedCookies' => $this->uncategorizedCookies,
                'consentMethod' => $this->consentMethod,
                'bannerVersion' => $this->bannerVersion,
                'privacyPolicyVersion' => $this->privacyPolicyVersion,
                'cookiePolicyVersion' => $this->cookiePolicyVersion,
            ]);
        }

        parent::afterSave($isNew);
    }
}
