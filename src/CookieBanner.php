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
use craft\events\RegisterUserPermissionsEvent;
use craft\events\SiteEvent;
use craft\events\TemplateEvent;

use craft\helpers\UrlHelper;
use craft\services\Elements;
use craft\services\Sites;
use craft\services\UserPermissions;

use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;

use digitalastronaut\craftcookiebanner\elements\ConsentRecord;
use digitalastronaut\craftcookiebanner\models\Settings;
use digitalastronaut\craftcookiebanner\records\Content;
use digitalastronaut\craftcookiebanner\services\ServicesTrait;
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
    use ServicesTrait;

    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;
    public bool $hasCpSection = true;

    public function init(): void {
        parent::init();

        // dd($this->getCookieDetection()->categorizeCookies());

        $this->registerVariables();
        $this->registerTemplateRoots();
        $this->registerElementTypes();
        $this->registerAssetBundles();
        $this->registerTwigExtension();

        // $this->getConsentRecords()->cleanup();

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

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            $this->registerPermissions();
            $this->registerCpRoutes();
            $this->registerCpEvents();
        }

        if (Craft::$app->getRequest()->getIsSiteRequest()) {
            $this->registerSiteRoutes();
            $this->registerCookieBanner();
        }

        Craft::info(Craft::t('cookie-banner', '{name} plugin loaded', ['name' => $this->name]));
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

    private function registerPermissions(): void {
        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $event->permissions[] = [
                'heading' => 'Cookie banner',
                'permissions' => [
                    'cookie-banner:access-settings' => [
                        'label' => Craft::t('cookie-banner', 'Access settings'),
                    ],
                    'cookie-banner:access-cookies-and-vendors' => [
                        'label' => Craft::t('cookie-banner', 'Access cookies and vendors')
                    ],
                    'cookie-banner:access-content' => [
                        'label' => Craft::t('cookie-banner', 'Access content')
                    ],
                    'cookie-banner:access-appearance' => [
                        'label' => Craft::t('cookie-banner', 'Access appearance')
                    ],
                    'cookie-banner:access-consent-records' => [
                        'label' => Craft::t('cookie-banner', 'Access consent records')
                    ],
                ],
            ];
        });
    }

    public function getCpNavItem(): ?array {
        $item = parent::getCpNavItem();

        $item['icon'] = "@digitalastronaut/craftcookiebanner/web/icons/shield.svg";
        $item['url'] = 'cookie-banner';
        $item['subnav'] = [
            'gettingStarted' => ['label' => 'Getting started', 'url' => 'cookie-banner/getting-started'],
            'cookiesAndVendors' => ['label' => 'Cookies and vendors', 'url' => 'cookie-banner/cookies-and-vendors'],
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
                $event->rules['cookie-banner/getting-started'] = 'cookie-banner/getting-started/index';
                $event->rules['cookie-banner/cookies-and-vendors'] = 'cookie-banner/cookies-and-vendors/index';
                $event->rules['cookie-banner/cookies-and-vendors/edit/<name>'] = 'cookie-banner/cookies-and-vendors/edit';
                $event->rules['cookie-banner/cookies-and-vendors/create'] = 'cookie-banner/cookies-and-vendors/create';
                $event->rules['cookie-banner/content'] = 'cookie-banner/content/index';
                $event->rules['cookie-banner/content/add-cookie'] = 'cookie-banner/content/add-cookie';
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
        Event::on(
            Elements::class, 
            Elements::EVENT_REGISTER_ELEMENT_TYPES, 
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ConsentRecord::class;
            }
        );
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
                // Craft::$app->getView()->registerHtml($dataLayerScript, View::POS_HEAD);
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

                    $languageCode = strtolower(explode('-', $event->site->language)[0]);

                    $baseContent = file_get_contents(CookieBanner::getInstance()->getBasePath() . "/static/content/{$languageCode}.json");
                    if (!$baseContent) $baseContent = file_get_contents(CookieBanner::getInstance()->getBasePath() . "/static/content/en.json");

                    $content->siteId = $event->site->id;
                    $content->attributes = json_decode($baseContent, true);
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

    private function registerTwigExtension(): void {
        Craft::$app->view->registerTwigExtension(new CookieBannerTwigExtension());
    }
}
