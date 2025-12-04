<?php

namespace digitalastronaut\craftcookiebanner\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

/**
 * @method self ipAddressHash(string $value) Filter by IP address hash
 * @method self isExpired(bool|null $value)  Filter expired (true) or valid (false) records
 * @method self active()
 * @method self expired()
 */
class ConsentRecordQuery extends ElementQuery {
    public mixed $ipAddressHash = null;
    public mixed $sessionId = null;
    public mixed $userAgent = null;
    public mixed $language = null;
    public mixed $consentTimestamp = null;
    public mixed $consentExpiry = null;
    public mixed $consentAction = null;
    public mixed $consentMethod = null;
    public ?bool $essentialCookies = null;
    public ?bool $functionalCookies = null;
    public ?bool $analyticalCookies = null;
    public ?bool $advertisementCookies = null;
    public ?bool $personalizationCookies = null;
    public mixed $bannerVersion = null;
    public mixed $privacyPolicyVersion = null;
    public mixed $cookiePolicyVersion = null;

    public array|string|null $status = null;
    public ?bool $isExpired = null;
    public ?string $consentTimestampFrom = null;
    public ?string $consentTimestampTo = null;

    public function ipAddressHash(string $value): static {
        $this->ipAddressHash = $value;
        return $this;
    }

    public function isExpired(?bool $value): static {
        $this->isExpired = $value;
        return $this;
    }

    public function valid(): static {
        $this->isExpired = false;
        return $this;
    }

    public function expired(): static {
        $this->isExpired = true;
        return $this;
    }

    public function consentTimestampBetween(string $from, string $to): static {
        $this->consentTimestampFrom = $from;
        $this->consentTimestampTo = $to;
        return $this;
    }

    protected function statusCondition(string $status): mixed {
        switch ($status) {
            case 'valid':
                return ['>=', 'cookie_banner_consent_records.consentExpiry', Db::prepareDateForDb(new \DateTime())];
            case 'expired':
                return ['<', 'cookie_banner_consent_records.consentExpiry', Db::prepareDateForDb(new \DateTime())];
            default:
                return parent::statusCondition($status);
        }
    }

    protected function beforePrepare(): bool {
        $this->joinElementTable('cookie_banner_consent_records');

        $this->query->select([
            'cookie_banner_consent_records.id',
            'cookie_banner_consent_records.ipAddressHash',
            'cookie_banner_consent_records.sessionId',
            'cookie_banner_consent_records.userAgent',
            'cookie_banner_consent_records.language',
            'cookie_banner_consent_records.consentTimestamp',
            'cookie_banner_consent_records.consentExpiry',
            'cookie_banner_consent_records.consentAction',
            'cookie_banner_consent_records.essentialCookies',
            'cookie_banner_consent_records.functionalCookies',
            'cookie_banner_consent_records.analyticalCookies',
            'cookie_banner_consent_records.advertisementCookies',
            'cookie_banner_consent_records.personalizationCookies',
            'cookie_banner_consent_records.consentMethod',
            'cookie_banner_consent_records.bannerVersion',
            'cookie_banner_consent_records.privacyPolicyVersion',
            'cookie_banner_consent_records.cookiePolicyVersion',
        ]);

        if ($this->ipAddressHash !== null) {
            $this->subQuery->andWhere(Db::parseParam('cookie_banner_consent_records.ipAddressHash', $this->ipAddressHash));
        }

        if ($this->isExpired !== null) {
            if ($this->isExpired) $this->subQuery->andWhere(['<', 'cookie_banner_consent_records.consentExpiry', Db::prepareDateForDb(new \DateTime())]);
            if (!$this->isExpired) $this->subQuery->andWhere(['>=', 'cookie_banner_consent_records.consentExpiry', Db::prepareDateForDb(new \DateTime())]);
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