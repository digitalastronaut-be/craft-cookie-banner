<?php

namespace digitalastronaut\craftcookiebanner\migrations;

use craft\db\Migration;
use craft\records\Site;
use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\helpers\Table;

class Install extends Migration {
    public function safeUp(): bool {
        $this->createTables();
        $this->createForeignKeys();
        $this->createIndexes();

        $this->seedCookieBannerContentTable();

        return true;
    }

    public function safeDown(): bool {
        $this->dropForeignKeys();
        $this->dropTables();

        return true;
    }

    public function createTables(): void {
        $this->createTable(Table::COOKIE_BANNER_CONSENT_RECORDS, [
            'id' => $this->primaryKey(),

            'ipAddressHash' => $this->string(255)->null(),
            'sessionId' => $this->string(255)->null(),
            'userAgent' => $this->text()->null(),
            'language' => $this->string(10)->null(),

            'consentTimestamp' => $this->dateTime()->null(),
            'consentExpiry' => $this->dateTime()->null(),
            'consentAction' => $this->enum('consentAction', ['Accept all', 'Accept selected', 'Refuse all'])->null(),
            'consentMethod' => $this->string(255)->notNull(),
            
            'essentialCookies' => $this->boolean()->null(),
            'functionalCookies' => $this->boolean()->null(),
            'analyticalCookies' => $this->boolean()->null(),
            'advertisementCookies' => $this->boolean()->null(),
            'personalizationCookies' => $this->boolean()->null(),

            'bannerVersion' => $this->string(50)->null(),
            'privacyPolicyVersion' => $this->string(50)->null(),
            'cookiePolicyVersion' => $this->string(50)->null(),

            'uid' => $this->uid(),
        ]);

        $this->createTable(Table::COOKIE_BANNER_CONTENT, [
            'id' => $this->primaryKey(),
            'siteId' => $this->integer()->notNull(),
            'title' => $this->string(255)->null(),
            'text' => $this->text()->null(),

            'cookieGroups' => $this->json()->null(),

            'privacyPolicyLinkLabel' => $this->string(255)->null(),
            'privacyPolicyLinkURL' => $this->string(255)->null(),
            'cookiePolicyLinkLabel' => $this->string(255)->null(),
            'cookiePolicyLinkURL' => $this->string(255)->null(),

            'necessaryCookiesTitle' => $this->string(255)->null(),
            'necessaryCookiesLabel' => $this->string(255)->null(),
            'necessaryCookiesDefinition' => $this->text()->null(),
            'necessaryCookies' => $this->json()->null(),

            'preferenceCookiesTitle' => $this->string(255)->null(),
            'preferenceCookiesLabel' => $this->string(255)->null(),
            'preferenceCookiesDefinition' => $this->text()->null(),
            'preferenceCookies' => $this->json()->null(),

            'analyticalCookiesTitle' => $this->string(255)->null(),
            'analyticalCookiesLabel' => $this->string(255)->null(),
            'analyticalCookiesDefinition' => $this->text()->null(),
            'analyticalCookies' => $this->json()->null(),

            'marketingCookiesTitle' => $this->string(255)->null(),
            'marketingCookiesLabel' => $this->string(255)->null(),
            'marketingCookiesDefinition' => $this->text()->null(),
            'marketingCookies' => $this->json()->null(),

            'uncategorizedCookiesTitle' => $this->string(255)->null(),
            'uncategorizedCookiesLabel' => $this->string(255)->null(),
            'uncategorizedCookiesDefinition' => $this->text()->null(),
            'uncategorizedCookies' => $this->json()->null(),

            'acceptAllButtonLabel' => $this->string(255)->null(),
            'acceptSelectedButtonLabel' => $this->string(255)->null(),
            'refuseAllButtonLabel' => $this->string(255)->null(),
            'determinePreferencesButtonLabel' => $this->string(255)->null(),
            'detailedPreferencesButtonLabel' => $this->string(255)->null(),
        ]); 
    }

    public function createForeignKeys(): void {
        $this->addForeignKey(null, Table::COOKIE_BANNER_CONSENT_RECORDS, 'id', Table::ELEMENTS, 'id', 'CASCADE', null);
        $this->addForeignKey(null, Table::COOKIE_BANNER_CONTENT, 'siteId', Table::SITES, 'id', 'CASCADE', null);
    }

    public function createIndexes(): void {
        $this->createIndex(null, Table::COOKIE_BANNER_CONTENT, 'siteId', true);
    }

    public function dropTables(): void {
        $this->dropTableIfExists(Table::COOKIE_BANNER_CONSENT_RECORDS);
        $this->dropTableIfExists(Table::COOKIE_BANNER_CONTENT);
    }

    public function dropForeignKeys(): void {
        $this->dropAllForeignKeysToTable(Table::COOKIE_BANNER_CONSENT_RECORDS);
        $this->dropAllForeignKeysToTable(Table::COOKIE_BANNER_CONTENT);
    }

    public function seedCookieBannerContentTable() {
        $sites = Site::find()->all();
        $basePath = CookieBanner::getInstance()->getBasePath() . '/static/content';

        foreach ($sites as $site) {
            $languageCode = strtolower(explode('-', $site->language)[0]);
            $filePath = "{$basePath}/{$languageCode}.json";

            if (!file_exists($filePath)) $filePath = "{$basePath}/default.json";

            $this->insert(Table::COOKIE_BANNER_CONTENT, array_merge(
                ['siteId' => $site->id],
                json_decode(file_get_contents($filePath), true)
            ));
        }
    }
}
