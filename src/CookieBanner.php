<?php

namespace digitalastronaut\craftcookiebanner;

use Craft;
use craft\base\Model;
use craft\base\Plugin;

use craft\helpers\UrlHelper;

use digitalastronaut\craftcookiebanner\models\Settings;
use digitalastronaut\craftcookiebanner\services\ServicesTrait;

// TODO: make the appearance settings database and multisite capable so multisites with different styling can match the cookiebanners
// TODO: fix blacklisted vendors showing up in the list
// TODO: Enabled switches row laten disabelen als ze uit staan CSS bestaat al en heeft gewerkt weet niet waarom nu niet meer
// TODO: add a language swicher to the cookie banner as an option (EXTRA)
// TODO: Figure out hoe we consent records kunnen deleten zonder elke site/cp request cleanup te triggeren zonder persee een cron job... (BASE)
// TODO: Styling en templates mooier opsplitsen (BASE) 
// TODO: Cascade for content table fixen. (BASE)
// TODO: Loading animation when saving consent so it's more clear to the user. (BASE)
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
        $currentUser = Craft::$app->getUser();

        $nav = parent::getCpNavItem();

        $nav['icon'] = "@digitalastronaut/craftcookiebanner/web/icons/shield.svg";
        $nav['url'] = 'cookie-banner';
        $nav['badgeCount'] = CookieBanner::getInstance()->getCookieDetection()->getIssues();

        if ($currentUser->checkPermission("cookie-banner:access-cookies-and-vendors")) {
            $nav['subnav']['gettingStarted'] = [
                'label' => Craft::t('cookie-banner', 'Getting started'), 
                'url' => 'cookie-banner/getting-started',
            ];

            $nav['subnav']['cookiesAndVendors'] = [
                'label' => Craft::t('cookie-banner', 'Cookies and vendors'), 
                'url' => 'cookie-banner/cookies-and-vendors',
            ];
        }

        if ($currentUser->checkPermission("cookie-banner:access-consent-records")) {
            $nav['subnav']['consentRecords'] = [
                'label' => Craft::t('cookie-banner', 'Consent records'), 
                'url' => 'cookie-banner/consent-records'
            ];
        }
                
        if ($currentUser->checkPermission("cookie-banner:access-content")) {
            $nav['subnav']['content'] = [
                'label' => Craft::t('cookie-banner', 'Content'), 
                'url' => 'cookie-banner/content'
            ];
        }

        if ($currentUser->checkPermission("cookie-banner:access-appearance")) {
            $nav['subnav']['appearance'] = [
                'label' => Craft::t('cookie-banner', 'Appearance'), 
                'url' => 'cookie-banner/appearance'
            ];
        }
        
        if ($currentUser->checkPermission("cookie-banner:access-appearance")) {
            $nav['subnav']['settings'] = [
                'label' => Craft::t('cookie-banner', 'Settings'), 
                'url' => 'cookie-banner/settings'
            ];
        }

        return $nav;
    }
}
