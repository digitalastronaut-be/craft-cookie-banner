<?php

namespace digitalastronaut\craftcookiebanner;

use Craft;
use craft\base\Event;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\DefineBehaviorsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\SiteEvent;
use craft\events\TemplateEvent;
use craft\helpers\UrlHelper;
use craft\services\Elements;
use craft\services\Sites;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;

use digitalastronaut\craftcookiebanner\elements\ConsentRecord;
use digitalastronaut\craftcookiebanner\helpers\CookieBanner as CookieBannerHelper;
use digitalastronaut\craftcookiebanner\models\Settings;
use digitalastronaut\craftcookiebanner\records\Content;
use digitalastronaut\craftcookiebanner\variables\CookieBannerVariable;
use digitalastronaut\craftcookiebanner\web\assets\CookieBannerAssets;
use digitalastronaut\craftcookiebanner\web\twig\CookieBannerTwigExtension;

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
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;
    public bool $hasCpSection = true;

    public function init(): void {
        parent::init();

        $this->registerVariables();
        $this->registerTemplateRoots();
        $this->registerElementTypes();
        $this->registerAssetBundles();
        $this->registerTwigExtension();

        // TODO: Cascade for content table fixen
        // TODO: Cookie table rendering for cookie page with twig extension
        // TODO: Loading animation when saving consent so it's more clear to the user
        // TODO: Automatic cookie detection via php $_COOKIE global and maybe add automatic descriptions based on cookie names
        
        $this->setComponents([]);

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            $this->registerCpRoutes();
            $this->registerCpEvents();
        }
        
        if (Craft::$app->getRequest()->getIsSiteRequest()) {
            $this->registerSiteRoutes();
            $this->registerCookieBanner();
        }
    }

    protected function createSettingsModel(): ?Model {
        return Craft::createObject(Settings::class);
    }

    public function getSettingsResponse(): mixed {
		return Craft::$app->controller->redirect(UrlHelper::cpUrl('cookie-banner/settings'));
	}

    private function registerTemplateRoots(): void {
        Event::on(
            View::class,
            View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
            function(RegisterTemplateRootsEvent $event) {
                $event->roots['cookie-banner'] = $this->getBasePath() . '/templates';
            }
        );
    }

    public function registerVariables(): void {
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_DEFINE_BEHAVIORS,
            function(DefineBehaviorsEvent $event) {
                $event->sender->attachBehaviors([CookieBannerVariable::class]);
            }
        );
    }

    public function getCpNavItem(): ?array {
        $item = parent::getCpNavItem();

        $item['icon'] = "@digitalastronaut/craftcookiebanner/web/icons/shield.svg";
        $item['url'] = 'cookie-banner';
        $item['subnav'] = [
            'complianceChecklist' => ['label' => 'Compliance checklist', 'url' => 'cookie-banner/compliance-checklist'],
            'consentRecords' => ['label' => 'Consent records', 'url' => 'cookie-banner/consent-records'],
            'content' => ['label' => 'Content', 'url' => 'cookie-banner/content'],
            'appearance' => ['label' => 'Appearance', 'url' => 'cookie-banner/appearance'],
            'settings' => ['label' => 'Settings', 'url' => 'cookie-banner/settings'],
        ];

        return $item;
    }

    private function registerCpRoutes(): void {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['cookie-banner'] = 'cookie-banner/consent-records/index';
                $event->rules['cookie-banner/compliance-checklist'] = 'cookie-banner/compliance-checklist/index';
                $event->rules['cookie-banner/content'] = 'cookie-banner/content/index';
                $event->rules['cookie-banner/appearance'] = 'cookie-banner/appearance/index';
                $event->rules['cookie-banner/consent-records'] = 'cookie-banner/consent-records/view';
                $event->rules['cookie-banner/consent-records/create'] = 'cookie-banner/consent-records/create';
                $event->rules['cookie-banner/consent-records/<id:\d+>'] = 'cookie-banner/consent-records/edit';
                $event->rules['cookie-banner/settings'] = 'cookie-banner/settings/index';             
            }
        );
    }

    private function registerSiteRoutes(): void {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['cookie-banner/consent-records/create'] = 'cookie-banner/consent-records/create';
            }
        );
    }

    private function registerElementTypes(): void {
        Event::on(Elements::class, Elements::EVENT_REGISTER_ELEMENT_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = ConsentRecord::class;
        });
    }

    private function registerAssetBundles(): void {
        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_TEMPLATE,
            function() {
                Craft::$app->getView()->registerAssetBundle(CookieBannerAssets::class);
            }
        );
    }

    private function registerTwigExtension(): void {
        Craft::$app->view->registerTwigExtension(new CookieBannerTwigExtension());
    }

    private function registerCookieBanner(): void {
        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE,
            function (TemplateEvent $event) {
                $currentSiteId = Craft::$app->getSites()->getCurrentSite()->id;

                $settings = $this->getSettings();
                $content = Content::find()->where(['siteId' => $currentSiteId])->one();

                if (!$settings->cookieBannerEnabled) return;

                $dataLayerScript = Craft::$app->getView()->renderTemplate('cookie-banner/components/_dataLayerScript');
                $bannerHtml = Craft::$app->getView()->renderTemplate('cookie-banner/components/_banner.twig', [
                    'settings' => $settings,
                    'banner' => $content
                ]);

                Craft::$app->getView()->registerHtml($bannerHtml, View::POS_BEGIN);
                Craft::$app->getView()->registerHtml($dataLayerScript, View::POS_HEAD);
            }
        );
    }

    private function registerCpEvents(): void {
        Event::on(
            Sites::class,
            Sites::EVENT_AFTER_SAVE_SITE,
            function (SiteEvent $event) {
                if ($event->isNew) {
                    $content = new Content();

                    $content->siteId = $event->site->id;
                    $content->attributes = CookieBannerHelper::BASE_CONTENT;
                    $content->save(false); 
                }
            }
        );

        Event::on(
            Sites::class,
            Sites::EVENT_AFTER_DELETE_SITE,
            function (SiteEvent $event) {
                Content::deleteAll(['siteId' => $event->site->id]);
            }
        );
    }
}
