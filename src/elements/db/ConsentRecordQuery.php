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

namespace digitalastronaut\craftcookiebanner\elements\db;

use Craft;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

use yii\db\Expression;

/**
 * Class ConsentRecordQuery
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 * 
 * @method self getCategorizedStats()
 * @method self ipAddressHash(string $value)
 */
class ConsentRecordQuery extends ElementQuery {
    public array|string|null $status = null;
    public string|array|null $ipAddressHash = null;
    public string|array|null $sessionId = null;
    public string|array|null $userAgent = null;
    public string|array|null $language = null;
    public string|array|null $consentTimestamp = null;
    public string|array|null $consentAction = null;
    public string|array|null $consentMethod = null;
    public string|array|null $bannerVersion = null;
    public string|array|null $privacyPolicyVersion = null;
    public string|array|null $cookiePolicyVersion = null;

    public ?bool $necessaryCookies = null;
    public ?bool $preferenceCookies = null;
    public ?bool $analyticalCookies = null;
    public ?bool $marketingCookies = null;
    public ?bool $uncategorizedCookies = null;
 
    public ?string $consentTimestampFrom = null;
    public ?string $consentTimestampTo = null;

    /**
     * @param string $value
     * @return ConsentRecordQuery
     */
    public function ipAddressHash(string $value): static {
        $this->ipAddressHash = $value;
        return $this;
    }

    /**
     * @param string $from
     * @param string $to
     * @return ConsentRecordQuery
     */
    public function consentTimestampBetween(string $from, string $to): static {
        $this->consentTimestampFrom = $from;
        $this->consentTimestampTo = $to;
        return $this;
    }

    /**
     * @param string $status
     * @return mixed
     */
    protected function statusCondition(string $status): mixed {
        return match ($status) {
            'valid' => true,
            default => parent::statusCondition($status),
        };
    }

    /**
     * @return array[]
     */
    public function groupedByMonth(): array {
        $db = Craft::$app->getDb();

        $query = clone $this;
        $query->prepare($db->getQueryBuilder());

        $table = 'cookie_banner_consent_records';

        $yearExpression = new Expression("YEAR([[{$table}.consentTimestamp]])");
        $monthExpression = new Expression("MONTH([[{$table}.consentTimestamp]])");

        $rows = (clone $query->subQuery)
            ->select([
                'year' => $yearExpression,
                'month' => $monthExpression,
                'count' => new Expression('COUNT(*)'),
            ])
            ->groupBy([$yearExpression, $monthExpression])
            ->orderBy([
                'year' => SORT_DESC,
                'month' => SORT_DESC,
            ])
            ->all();

        return $rows;
    }

    /**
     * @return array[]
     */
    public function countAndAcceptancePerDay(): array {
        $db = Craft::$app->getDb();

        $query = clone $this;
        $query->prepare($db->getQueryBuilder());

        $table = 'cookie_banner_consent_records';

        $dateExpression =  
            new Expression('DATE([[cookie_banner_consent_records.consentTimestamp]])');

        $acceptedExpression = 
            new Expression("SUM(CASE 
                WHEN [[{$table}.necessaryCookies]] = 1
                AND [[{$table}.preferenceCookies]] = 1
                AND [[{$table}.analyticalCookies]] = 1
                AND [[{$table}.marketingCookies]] = 1
                AND [[{$table}.uncategorizedCookies]] = 1
                THEN 1 ELSE 0
            END)");

        $rows = (clone $query->subQuery)
            ->select([
                'date' => $dateExpression,
                'count' => new Expression('COUNT(*)'),
                'accepted' => $acceptedExpression,
            ])
            ->groupBy($dateExpression)
            ->orderBy(['date' => SORT_ASC])
            ->all();

        return $rows;
    }

    /**
     * @return array
     */
    public function categoryAcceptancePercentages(): array {
        $db = Craft::$app->getDb();

        $query = clone $this;
        $query->prepare($db->getQueryBuilder());

        $categories = [
            'acceptedNecessaryCookiesPercentage'    => 'necessaryCookies',
            'acceptedPreferenceCookiesPercentage'   => 'preferenceCookies',
            'acceptedAnalyticalCookiesPercentage'   => 'analyticalCookies',
            'acceptedMarketingCookiesPercentage'    => 'marketingCookies',
            'acceptedUncategorizedCookiesPercentage' => 'uncategorizedCookies',
        ];

        $selects = [
            new Expression('COUNT(*) AS [[total]]'),
        ];

        foreach ($categories as $alias => $column) {
            $selects[] = new Expression(
                'SUM(CASE WHEN {{cookie_banner_consent_records}}.[[' . $column . ']] = 1 THEN 1 ELSE 0 END) AS [[' . $alias . ']]'
            );
        }

        $result = (clone $query->subQuery)
            ->select($selects)
            ->one();

        if (!$result || (int) $result['total'] === 0) {
            return array_fill_keys(array_keys($categories), 0.0);
        }

        $total = (int) $result['total'];
        $percentages = [];

        foreach (array_keys($categories) as $alias) {
            $count = (int) ($result[$alias] ?? 0);
            $percentages[$alias] = round(($count / $total) * 100, 1);
        }

        return $percentages;
    }

    /**
     * @return bool
     */
    protected function beforePrepare(): bool {
        $this->joinElementTable('cookie_banner_consent_records');

        $this->query->addSelect([
            'cookie_banner_consent_records.ipAddressHash',
            'cookie_banner_consent_records.sessionId',
            'cookie_banner_consent_records.userAgent',
            'cookie_banner_consent_records.language',
            'cookie_banner_consent_records.consentTimestamp',
            'cookie_banner_consent_records.consentAction',
            'cookie_banner_consent_records.necessaryCookies',
            'cookie_banner_consent_records.preferenceCookies',
            'cookie_banner_consent_records.analyticalCookies',
            'cookie_banner_consent_records.marketingCookies',
            'cookie_banner_consent_records.uncategorizedCookies',
            'cookie_banner_consent_records.consentMethod',
            'cookie_banner_consent_records.bannerVersion',
            'cookie_banner_consent_records.privacyPolicyVersion',
            'cookie_banner_consent_records.cookiePolicyVersion',
        ]);

        if ($this->ipAddressHash !== null) {
            $this->subQuery->andWhere(Db::parseParam('cookie_banner_consent_records.ipAddressHash', $this->ipAddressHash));
        }

        if ($this->sessionId !== null) {
            $this->subQuery->andWhere(Db::parseParam('cookie_banner_consent_records.sessionId', $this->sessionId));
        }

        if ($this->userAgent !== null) {
            $this->subQuery->andWhere(Db::parseParam('cookie_banner_consent_records.userAgent', $this->userAgent));
        }

        if ($this->language !== null) {
            $this->subQuery->andWhere(Db::parseParam('cookie_banner_consent_records.language', $this->language));
        }

        if ($this->consentAction !== null) {
            $this->subQuery->andWhere(Db::parseParam('cookie_banner_consent_records.consentAction', $this->consentAction));
        }

        if ($this->consentMethod !== null) {
            $this->subQuery->andWhere(Db::parseParam('cookie_banner_consent_records.consentMethod', $this->consentMethod));
        }

        if ($this->bannerVersion !== null) {
            $this->subQuery->andWhere(Db::parseParam('cookie_banner_consent_records.bannerVersion', $this->bannerVersion));
        }

        if ($this->privacyPolicyVersion !== null) {
            $this->subQuery->andWhere(Db::parseParam('cookie_banner_consent_records.privacyPolicyVersion', $this->privacyPolicyVersion));
        }

        if ($this->cookiePolicyVersion !== null) {
            $this->subQuery->andWhere(Db::parseParam('cookie_banner_consent_records.cookiePolicyVersion', $this->cookiePolicyVersion));
        }

        if ($this->necessaryCookies !== null) {
            $this->subQuery->andWhere(['cookie_banner_consent_records.necessaryCookies' => $this->necessaryCookies]);
        }

        if ($this->preferenceCookies !== null) {
            $this->subQuery->andWhere(['cookie_banner_consent_records.preferenceCookies' => $this->preferenceCookies]);
        }

        if ($this->analyticalCookies !== null) {
            $this->subQuery->andWhere(['cookie_banner_consent_records.analyticalCookies' => $this->analyticalCookies]);
        }

        if ($this->marketingCookies !== null) {
            $this->subQuery->andWhere(['cookie_banner_consent_records.marketingCookies' => $this->marketingCookies]);
        }

        if ($this->uncategorizedCookies !== null) {
            $this->subQuery->andWhere(['cookie_banner_consent_records.uncategorizedCookies' => $this->uncategorizedCookies]);
        }

        if ($this->consentTimestamp !== null) {
            $this->subQuery->andWhere(Db::parseDateParam('cookie_banner_consent_records.consentTimestamp', $this->consentTimestamp));
        }

        if ($this->consentTimestampFrom !== null && $this->consentTimestampTo !== null) {
            $this->subQuery->andWhere([
                'between',
                'cookie_banner_consent_records.consentTimestamp',
                Db::prepareDateForDb($this->consentTimestampFrom),
                Db::prepareDateForDb($this->consentTimestampTo),
            ]);
        }

        return parent::beforePrepare();
    }
}