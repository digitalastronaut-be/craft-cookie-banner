<?php

namespace digitalastronaut\craftcookiebanner;

use Craft;
use craft\base\Model;
use craft\base\Plugin;

use craft\helpers\UrlHelper;
use digitalastronaut\craftcookiebanner\models\Settings;
use digitalastronaut\craftcookiebanner\services\ServicesTrait;

// TODO: Enabled switches row laten disabelen als ze uit staan CSS bestaat al en heeft gewerkt weet niet waarom nu niet meer
// TODO: Figure out hoe we consent records kunnen deleten zonder elke site/cp request cleanup te triggeren zonder persee een cron job... (BASE)
// TODO: Styling en templates mooier opsplitsen (BASE) 
// TODO: JS translatinos for chip component
// TODO: improve rules for active records
// TODO: kijken om de category ook autoFillen bij manual create
// TODO: Fix duplicates for autoCreate and manualCreate
// TODO: PHP Docs overlopen
// TODO: improve log consent if error happens

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
            $nav['subnav']['dashboard'] = [
                'label' => Craft::t('cookie-banner', 'Dashboard'), 
                'url' => 'cookie-banner/dashboard',
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
        
        if ($currentUser->checkPermission("cookie-banner:access-settings")) {
            $nav['subnav']['settings'] = [
                'label' => Craft::t('cookie-banner', 'Settings'), 
                'url' => 'cookie-banner/settings'
            ];
        }

        return $nav;
    }
}
