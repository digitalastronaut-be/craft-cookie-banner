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

namespace digitalastronaut\craftcookiebanner;

use Craft;
use craft\base\Model;
use craft\base\Plugin;

use craft\helpers\UrlHelper;
use digitalastronaut\craftcookiebanner\models\Settings;
use digitalastronaut\craftcookiebanner\services\ServicesTrait;

use yii\web\Response;

/**
 * Class CookieBanner
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 *
 * @method SettingsModel getSettings()
 */
class CookieBanner extends Plugin {
    use ServicesTrait;
    use PluginTrait;

    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;
    public bool $hasCpSection = true;

    /**
     * @return void
     */
    public function init(): void {
        parent::init();

        $this->registerEvents();

        Craft::info(Craft::t('cookie-banner', '{name} plugin loaded', ['name' => $this->name]));
    }

    /**
     * @inheritDoc
     * @return Model|null
     */
    protected function createSettingsModel(): ?Model {
        return Craft::createObject(Settings::class);
    }

    /**
     * @inheritDoc
     * @return Response
     */
    public function getSettingsResponse(): mixed {
		return Craft::$app->controller->redirect(UrlHelper::cpUrl('cookie-banner/settings'));
	}

    /**
     * @inheritDoc
     * @return array|null
     */
    public function getCpNavItem(): ?array {
        $currentUser = Craft::$app->getUser();

        $nav = parent::getCpNavItem();

        $nav['icon'] = "@digitalastronaut/craftcookiebanner/web/icons/shield.svg";
        $nav['url'] = 'cookie-banner';
        $nav['badgeCount'] = Craft::$app->getCache()->getOrSet(
            'cookie-banner:issues-count',
            fn() => CookieBanner::getInstance()->getCookieDetection()->getIssues(),
            duration: 300,
        );

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
