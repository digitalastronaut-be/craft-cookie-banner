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

        $this->seedContentTable();
        $this->seedAppearanceTable();

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

            'vendors' => $this->json()->null(),

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

        $this->createTable(Table::COOKIE_BANNER_APPEARANCE, [
            'id' => $this->primaryKey(),
            'siteId' => $this->integer()->notNull(),

            'cookieBannerPosition' => $this->string(50)->defaultValue('bottom-left'),
            'preferencesAction' => $this->string(50)->defaultValue('button'),
            'showCookieCategoriesPreview' => $this->boolean()->defaultValue(false),
            'showCookieTables' => $this->boolean()->defaultValue(true),

            'buttonSize' => $this->string(20)->defaultValue('small'),
            'bannerStyle' => $this->string(20)->defaultValue('square'),
            'cookieListStyle' => $this->string(20)->defaultValue('ungrouped'),

            'titleFont' => $this->string(255)->defaultValue('"system-ui", sans-serif'),
            'textFont' => $this->string(255)->defaultValue('"system-ui", sans-serif'),

            'bannerBackground' => $this->string(20)->defaultValue('ffffff'),
            'bannerBorderColor' => $this->string(20)->defaultValue('ffffff'),
            'titleColor' => $this->string(20)->defaultValue('3F4d5a'),
            'textColor' => $this->string(20)->defaultValue('596673'),
            'linkColor' => $this->string(20)->defaultValue('2563eb'),
            'linkHoverColor' => $this->string(20)->defaultValue('123a97'),
            
            'bannerBorderThickness' => $this->integer()->defaultValue(0),
            'bannerOverlayOpacity' => $this->integer()->defaultValue(0),

            'buttonBackground' => $this->string(20)->defaultValue('d9e0e8'),
            'buttonColor' => $this->string(20)->defaultValue('3F4d5a'),
            'buttonBorderColor' => $this->string(20)->defaultValue('d9e0e8'),
            'buttonHoverBackground' => $this->string(20)->defaultValue('3F4d5a'),
            'buttonHoverColor' => $this->string(20)->defaultValue('ffffff'),
            'buttonHoverBorderColor' => $this->string(20)->defaultValue('404d5a'),
            'buttonBorderThickness' => $this->integer()->defaultValue(0),

            'toggleBackgroundOff' => $this->string(20)->defaultValue('e5e7eb'),
            'toggleBackgroundOn' => $this->string(20)->defaultValue('009c8c'),
            'toggleColor' => $this->string(20)->defaultValue('ffffff'),

            'cookieGroupTitleColor' => $this->string(20)->defaultValue('3F4d5a'),
            'cookieGroupTextColor' => $this->string(20)->defaultValue('596673'),
            'cookieGroupLinkColor' => $this->string(20)->defaultValue('2663eb'),
            'cookieGroupLinkHoverColor' => $this->string(20)->defaultValue('143a97'),
            'cookieGroupBackground' => $this->string(20)->defaultValue('f3f7fb'),
            'cookieGroupBorderColor' => $this->string(20)->defaultValue('e5e7eb'),
            'cookieGroupHoverBackground' => $this->string(20)->defaultValue('f3f7fb'),
            'cookieGroupHoverBorderColor' => $this->string(20)->defaultValue('afb6bf'),
            'cookieGroupBorderThickness' => $this->integer()->defaultValue(1),

            'cookieTableTitleColor' => $this->string(20)->defaultValue('3F4d5a'),
            'cookieTableTextColor' => $this->string(20)->defaultValue('596673'),
            'cookieTableBackground' => $this->string(20)->defaultValue('ffffff'),
            'cookieTableBorderColor' => $this->string(20)->defaultValue('e5e7eb'),
            'cookieTableBorderThickness' => $this->integer()->defaultValue(1),

            'css' => $this->text()->null(),

            'uid' => $this->uid(),
        ]);
    }

    public function createForeignKeys(): void {
        $this->addForeignKey(null, Table::COOKIE_BANNER_CONSENT_RECORDS, 'id', Table::ELEMENTS, 'id', 'CASCADE', null);
        $this->addForeignKey(null, Table::COOKIE_BANNER_CONTENT, 'siteId', Table::SITES, 'id', 'CASCADE', null);
        $this->addForeignKey(null, Table::COOKIE_BANNER_APPEARANCE, 'siteId', Table::SITES, 'id', 'CASCADE', null);
    }

    public function createIndexes(): void {
        $this->createIndex(null, Table::COOKIE_BANNER_CONTENT, 'siteId', true);
        $this->createIndex(null, Table::COOKIE_BANNER_APPEARANCE, 'siteId', true);
    }

    public function dropTables(): void {
        $this->dropTableIfExists(Table::COOKIE_BANNER_CONSENT_RECORDS);
        $this->dropTableIfExists(Table::COOKIE_BANNER_CONTENT);
        $this->dropTableIfExists(Table::COOKIE_BANNER_APPEARANCE);
    }

    public function dropForeignKeys(): void {
        $this->dropAllForeignKeysToTable(Table::COOKIE_BANNER_CONSENT_RECORDS);
        $this->dropAllForeignKeysToTable(Table::COOKIE_BANNER_CONTENT);
        $this->dropAllForeignKeysToTable(Table::COOKIE_BANNER_APPEARANCE);
    }

    public function seedContentTable() {
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

    public function seedAppearanceTable() {
        $sites = Site::find()->all();

        foreach ($sites as $site) {
            $this->insert(Table::COOKIE_BANNER_APPEARANCE, [
                'siteId' => $site->id,
            ]);
        }
    }
}
