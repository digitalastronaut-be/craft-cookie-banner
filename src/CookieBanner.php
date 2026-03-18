<?php

namespace digitalastronaut\craftcookiebanner;

use Craft;
use craft\base\Model;
use craft\base\Plugin;

use craft\helpers\UrlHelper;

use digitalastronaut\craftcookiebanner\models\Settings;
use digitalastronaut\craftcookiebanner\services\ServicesTrait;

// TODO: implement a blacklist so unnessecary cookies detected from browser storage don't clutter the table
// TODO: remove adding or removing rows from content page and centralize cookies and vendors on their own page (BASE)
// TODO: add a language swicher to the cookie banner as an option (EXTRA)
// TODO: rename create cookie to add cookie in the controllers and logic
// TODO: Figure out hoe we consent records kunnen deleten zonder elke site/cp request cleanup te triggeren zonder persee een cron job... (BASE)
// TODO: Permissions overal toepassen (BASE)
// TODO: Badge met aantal cookies die nog niet in orde zijn tonen in sidenav (BASE)
// TODO: Error handling in controllers verbetern (BASE)
// TODO: All business logic veranderen naar services (BASE)
// TODO: Styling en templates mooier opsplitsen (BASE) 
// TODO: Cascade for content table fixen. (BASE)
// TODO: Loading animation when saving consent so it's more clear to the user. (BASE)
// TODO: Provide a way to insert the detected cookies in to all languages. (BASE)
// TODO: rename consent records controller action view -> index and  edit -> view (BASE)
// TODO: test creating sites and deleting sites since event refactoring couldn't at the time (BASE)
// TODO: rename cookieGroups to vendors

/**
 * Cookie banner plugin
 *
 * @method static CookieBanner getInstance()
 * @method Settings getSettings()
 * @author digitalastronaut <bram@digitalastronaut.be>
 * @copyright digitalastronaut
 * @license https://craftcms.github.io/license/ Craft License
 */
class CookieBanner extends Plugin {
    use ServicesTrait;

    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;
    public bool $hasCpSection = true;

    public function init(): void {
        parent::init();

        EventHandlers::register();

        Craft::info(Craft::t('cookie-banner', '{name} plugin loaded', ['name' => $this->name]));
    }

    protected function createSettingsModel(): ?Model {
        return Craft::createObject(Settings::class);
    }

    public function getSettingsResponse(): mixed {
		return Craft::$app->controller->redirect(UrlHelper::cpUrl('cookie-banner/settings'));
	}

    public function getCpNavItem(): ?array {
        $item = parent::getCpNavItem();

        $item['icon'] = "@digitalastronaut/craftcookiebanner/web/icons/shield.svg";
        $item['url'] = 'cookie-banner';
        $item['badgeCount'] = 0;
        $item['subnav'] = [
            'gettingStarted' => [
                'label' => Craft::t('cookie-banner', 'Getting started'), 
                'url' => 'cookie-banner/getting-started'
            ],
            'cookiesAndVendors' => [
                'label' => Craft::t('cookie-banner', 'Cookies and vendors'), 
                'url' => 'cookie-banner/cookies-and-vendors'
            ],
            'consentRecords' => [
                'label' => Craft::t('cookie-banner', 'Consent records'), 
                'url' => 'cookie-banner/consent-records'
            ],
            'content' => [
                'label' => Craft::t('cookie-banner', 'Content'), 
                'url' => 'cookie-banner/content'
            ],
            'appearance' => [
                'label' => Craft::t('cookie-banner', 'Appearance'), 
                'url' => 'cookie-banner/appearance'
            ],
            'settings' => [
                'label' => Craft::t('cookie-banner', 'Settings'), 
                'url' => 'cookie-banner/settings'
            ],
        ];

        return $item;
    }
}
