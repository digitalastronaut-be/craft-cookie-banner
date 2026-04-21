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

/**
 * Class ConsentRecord
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 * 
 * @method static ConsentRecordQuery find()
 */
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

    public static function trackChanges(): bool { return false; }
    public static function hasTitles(): bool { return true; }
    public static function hasUris(): bool { return false; }
    public static function isLocalized(): bool { return false; }
    public static function hasStatuses(): bool { return true; }

    public function canView(User $user): bool { return true; }
    public function canSave(User $user): bool { return false; }
    public function canDuplicate(User $user): bool {return false; }
    public function canDelete(User $user): bool { return true; }
    public function canCreateDrafts(User $user): bool { return false; }

    public static function statuses(): array {
        return [
            'valid' => ['label' => Craft::t('cookie-banner', 'Valid'), 'color' => Color::Teal],
        ];
    }

    /**
     * @return ConsentRecordQuery
     */
    public static function find(): ElementQueryInterface {
        return Craft::createObject(ConsentRecordQuery::class, [static::class]);
    }

    /**
     * @return ConsentRecordCondition
     */
    public static function createCondition(): ElementConditionInterface {
        return Craft::createObject(ConsentRecordCondition::class, [static::class]);
    }

    /**
     * @return string[]
     */
    protected static function defineSearchableAttributes(): array {
        return ['ipAddressHash', 'userAgent'];
    }

    /**
     * @param string $context
     * @return array[]
     */
    protected static function defineSources(string $context): array {
        $rows = ConsentRecord::find()->groupedByMonth();

        $grouped = [];

        foreach ($rows as $row) {
            $year = (int)$row['year'];
            $month = (int)$row['month'];

            if (!isset($grouped[$year])) $grouped[$year] = [];

            $monthLabel = Craft::$app->getFormatter()->asDate("{$year}-{$month}-01", 'MMMM');
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

    /**
     * @param string $source
     * @return array
     */
    protected static function defineActions(string $source): array {
        $actions = parent::defineActions($source);

        return $actions;
    }

    /**
     * @return array
     */
    protected static function defineSortOptions(): array {
        return [
            'title' => Craft::t('app', 'Title'),
            'consentTimestamp' => Craft::t('app', 'Consent timestamp')
        ];
    }

    /**
     * @return array
     */
    protected static function defineTableAttributes(): array {
        return [
            'status' => ['label' => Craft::t('cookie-banner', 'Status')],
            'ipAddressHash' => ['label' => Craft::t('cookie-banner', 'IP address Hash')],
            'sessionId' => ['label' => Craft::t('cookie-banner', 'Session ID')],
            'userAgent' => ['label' => Craft::t('cookie-banner', 'User Agent')],
            'language' => ['label' => Craft::t('cookie-banner', 'Language')],
            'consentTimestamp' => ['label' => Craft::t('cookie-banner', 'Timestamp')],
            'consentAction' => ['label' => Craft::t('cookie-banner', 'Action')],
            'consentMethod' => ['label' => Craft::t('cookie-banner', 'Method')],
            'necessaryCookies' => ['label' => Craft::t('cookie-banner', 'Necessary')],
            'preferenceCookies' => ['label' => Craft::t('cookie-banner', 'Preference')],
            'analyticalCookies' => ['label' => Craft::t('cookie-banner', 'Analytical')],
            'marketingCookies' => ['label' => Craft::t('cookie-banner', 'Marketing')],
            'uncategorizedCookies' => ['label' => Craft::t('cookie-banner', 'Uncategorized')],
            'bannerVersion' => ['label' => Craft::t('cookie-banner', 'Banner Version')],
            'privacyPolicyVersion' => ['label' => Craft::t('cookie-banner', 'Privacy Policy Version')],
            'cookiePolicyVersion' => ['label' => Craft::t('cookie-banner', 'Cookie Policy Version')],
        ];
    }

    /**
     * @param string $source
     * @return string[]
     */
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

    /**
     * @return array
     */
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

    /**
     * @return string
     */
    public function cpEditUrl(): string {
        return UrlHelper::cpUrl("cookie-banner/consent-records/{$this->id}");
    }

    /**
     * @return array|string|null
     */
    protected function route(): array|string|null {
        return null;
    }

    /**
     * @return FieldLayout|null
     */
    public function getFieldLayout(): ?FieldLayout {
        if ($this->fieldLayout !== null) {
            return $this->fieldLayout;
        }

        $this->fieldLayout = Craft::$app->getFields()->getLayoutByType(self::class);

        return $this->fieldLayout;
    }

    /**
     * @param Response $response
     * @param string $containerId
     * @return void
     */
    public function prepareEditScreen(Response $response, string $containerId): void {
        $response->crumbs([
            [
                'label' => self::pluralDisplayName(),
                'url' => UrlHelper::cpUrl('cookie-banner/consent-records'),
            ],
        ]);
    }

    /**
     * @param bool $isNew
     * @return void
     */
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
