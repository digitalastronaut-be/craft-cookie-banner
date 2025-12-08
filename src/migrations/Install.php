<?php

namespace digitalastronaut\craftcookiebanner\migrations;

use craft\db\Migration;
use craft\records\Site;
use digitalastronaut\craftcookiebanner\helpers\CookieBanner;
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

            'privacyPolicyLinkLabel' => $this->string(255)->null(),
            'privacyPolicyLinkURL' => $this->string(255)->null(),
            'cookiePolicyLinkLabel' => $this->string(255)->null(),
            'cookiePolicyLinkURL' => $this->string(255)->null(),

            'essentialCookiesTitle' => $this->string(255)->null(),
            'essentialCookiesLabel' => $this->string(255)->null(),
            'essentialCookiesDefinition' => $this->string(255)->null(),

            'functionalCookiesTitle' => $this->string(255)->null(),
            'functionalCookiesLabel' => $this->string(255)->null(),
            'functionalCookiesDefinition' => $this->string(255)->null(),

            'analyticalCookiesTitle' => $this->string(255)->null(),
            'analyticalCookiesLabel' => $this->string(255)->null(),
            'analyticalCookiesDefinition' => $this->string(255)->null(),

            'advertisementCookiesTitle' => $this->string(255)->null(),
            'advertisementCookiesLabel' => $this->string(255)->null(),
            'advertisementCookiesDefinition' => $this->string(255)->null(),

            'personalizationCookiesTitle' => $this->string(255)->null(),
            'personalizationCookiesLabel' => $this->string(255)->null(),
            'personalizationCookiesDefinition' => $this->string(255)->null(),

            'acceptAllButtonLabel' => $this->string(255)->null(),
            'acceptSelectedButtonLabel' => $this->string(255)->null(),
            'refuseAllButtonLabel' => $this->string(255)->null(),
            'determinePreferencesButtonLabel' => $this->string(255)->null(),
            'detailedPreferencesButtonLabel' => $this->string(255)->null(),

            'cookieGroups' => $this->json()->null(),
            'essentialCookies' => $this->json()->null(),
            'functionalCookies' => $this->json()->null(),
            'analyticalCookies' => $this->json()->null(),
            'advertisementCookies' => $this->json()->null(),
            'personalizationCookies' => $this->json()->null(),
        ]); 

        $this->createTable(Table::COOKIE_BANNER_DETECTED_COOKIES, [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->null(),
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

        foreach ($sites as $site) {
            $this->insert(Table::COOKIE_BANNER_CONTENT, array_merge(
                ['siteId' => $site->id],
                CookieBanner::BASE_CONTENT
            ));
        }
    }
}
