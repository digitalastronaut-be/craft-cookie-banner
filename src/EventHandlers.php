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
use craft\events\DefineBehaviorsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\events\SiteEvent;
use craft\events\TemplateEvent;

use craft\services\Elements;
use craft\services\Sites;
use craft\services\UserPermissions;

use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;

use yii\base\Event;

use digitalastronaut\craftcookiebanner\CookieBanner as CookieBannerPlugin;
use digitalastronaut\craftcookiebanner\elements\ConsentRecord;
use digitalastronaut\craftcookiebanner\records\Appearance;
use digitalastronaut\craftcookiebanner\records\Content;
use digitalastronaut\craftcookiebanner\variables\CookieBannerVariable;
use digitalastronaut\craftcookiebanner\web\assets\CookieBannerAssets;
use digitalastronaut\craftcookiebanner\web\twig\CookieBannerTwigExtension;

/**
 * Class EventHandlers
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 */
class EventHandlers {
    /**
     * @return void
     */
    public static function register(): void {
        self::registerSharedEvents();

        if (Craft::$app->request->isConsoleRequest) self::registerConsoleEvents();
        if (Craft::$app->request->isSiteRequest) self::registerSiteEvents();
        if (Craft::$app->request->isCpRequest) self::registerCpEvents();
    }

    /**
     * @return void
     */
    private static function registerSharedEvents(): void {
        self::registerVariables();
        self::registerTemplateRoots();
        self::registerElementTypes();
        self::registerAssetBundles();
    }

    /**
     * @return void
     */
    private static function registerCpEvents(): void {
        self::registerPermissions();
        self::registerCpRoutes();
        self::registerSiteModelEvents();      
        self::registerTwigExtension();
    }

    /**
     * @return void
     */
    private static function registerSiteEvents(): void {
        self::registerSiteRoutes();
        self::registerCookieBannerHtml();
        self::registerTwigExtension();
    }

    /**
     * @return void
     */
    private static function registerConsoleEvents(): void {
        // Register any console events
    }

    /**
     * @return void
     */
    private static function registerVariables(): void {
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_DEFINE_BEHAVIORS,
            function(DefineBehaviorsEvent $event) {
                $event->sender->attachBehaviors([CookieBannerVariable::class]);
            }
        );
    }

    /**
     * @return void
     */
    private static function registerTemplateRoots(): void {
        Event::on(
            View::class,
            View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
            function(RegisterTemplateRootsEvent $event) {
                $event->roots['cookie-banner'] = CookieBannerPlugin::getInstance()->getBasePath() . '/templates';
            }
        );
    }

    /**
     * @return void
     */
    private static function registerElementTypes(): void {
        Event::on(
            Elements::class, 
            Elements::EVENT_REGISTER_ELEMENT_TYPES, 
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ConsentRecord::class;
            }
        );
    }

    /**
     * @return void
     */
    private static function registerAssetBundles(): void {
        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_TEMPLATE,
            function() {
                Craft::$app->getView()->registerAssetBundle(CookieBannerAssets::class);
            }
        );
    }

    /**
     * @return void
     */
    private static function registerTwigExtension(): void {
        Craft::$app->view->registerTwigExtension(new CookieBannerTwigExtension());
    }

    /**
     * @return void
     */
    private static function registerPermissions(): void {
        Event::on(
            UserPermissions::class, 
            UserPermissions::EVENT_REGISTER_PERMISSIONS, 
            function(RegisterUserPermissionsEvent $event) {
                $event->permissions[] = [
                    'heading' => 'Cookie banner',
                    'permissions' => [
                        'cookie-banner:access-dashboard' => [
                            'label' => Craft::t('cookie-banner', 'Access dashboard'),
                            'nested' => [
                                'cookie-banner:update-guide-progress' => [
                                    'label' => Craft::t('cookie-banner', 'Update guide progress')
                                ],
                            ],
                        ],
                        'cookie-banner:access-cookies-and-vendors' => [
                            'label' => Craft::t('cookie-banner', 'Access cookies and vendors'),
                            'nested' => [
                                'cookie-banner:add-cookies' => [
                                    'label' => Craft::t('cookie-banner', 'Add new cookies')
                                ],
                                'cookie-banner:edit-cookies' => [
                                    'label' => Craft::t('cookie-banner', 'Edit cookies')
                                ],
                                'cookie-banner:remove-cookies' => [
                                    'label' => Craft::t('cookie-banner', 'Remove cookies')
                                ],
                                'cookie-banner:blacklist-cookies' => [
                                    'label' => Craft::t('cookie-banner', 'Blacklist cookies')
                                ],
                                'cookie-banner:add-vendors' => [
                                    'label' => Craft::t('cookie-banner', 'Add new vendors')
                                ],
                                'cookie-banner:edit-vendors' => [
                                    'label' => Craft::t('cookie-banner', 'Edit vendors')
                                ],
                                'cookie-banner:remove-vendors' => [
                                    'label' => Craft::t('cookie-banner', 'Remove vendors')
                                ],
                                'cookie-banner:blacklist-vendors' => [
                                    'label' => Craft::t('cookie-banner', 'Blacklist vendors')
                                ],
                            ]
                        ],
                        'cookie-banner:access-content' => [
                            'label' => Craft::t('cookie-banner', 'Access content'),
                            'nested' => [
                                'cookie-banner:edit-content' => [
                                    'label' => Craft::t('cookie-banner', 'Edit content')
                                ],
                            ],
                        ],
                        'cookie-banner:access-appearance' => [
                            'label' => Craft::t('cookie-banner', 'Access appearance'),
                            'nested' => [
                                'cookie-banner:edit-appearance' => [
                                    'label' => Craft::t('cookie-banner', 'Edit appearance')
                                ],
                            ],
                        ],
                        'cookie-banner:access-consent-records' => [
                            'label' => Craft::t('cookie-banner', 'Access consent records'),
                        ],
                        'cookie-banner:access-settings' => [
                            'label' => Craft::t('cookie-banner', 'Access settings'),
                            'nested' => [
                                'cookie-banner:edit-settings' => [
                                    'label' => Craft::t('cookie-banner', 'Edit settings')
                                ],
                            ],
                        ],
                    ],
                ];
            }
        );
    }

    /**
     * @return void
     */
    private static function registerCpRoutes(): void {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['cookie-banner'] = 'cookie-banner/dashboard';

                $event->rules['cookie-banner/dashboard'] = 'cookie-banner/dashboard/index';
                $event->rules['cookie-banner/dashboard/complete-legal-pages-step'] = 'cookie-banner/dashboard/complete-legal-pages-step';

                $event->rules['cookie-banner/cookies-and-vendors'] = 'cookie-banner/cookies-and-vendors/index';
                $event->rules['cookie-banner/cookies-and-vendors/create-cookie'] = 'cookie-banner/cookies-and-vendors/create-cookie';
                $event->rules['cookie-banner/cookies-and-vendors/edit-cookie/<cookieName>'] = 'cookie-banner/cookies-and-vendors/edit-cookie';
                $event->rules['cookie-banner/cookies-and-vendors/get-cookies-chart-data'] = 'cookie-banner/cookies-and-vendors/get-cookies-chart-data';
                $event->rules['cookie-banner/cookies-and-vendors/search-cookies'] = 'cookie-banner/cookies-and-vendors/search-cookies';
                $event->rules['cookie-banner/cookies-and-vendors/create-vendor'] = 'cookie-banner/cookies-and-vendors/create-vendor';
                $event->rules['cookie-banner/cookies-and-vendors/edit-vendor/<vendorName>'] = 'cookie-banner/cookies-and-vendors/edit-vendor';
                $event->rules['cookie-banner/cookies-and-vendors/get-vendors-chart-data'] = 'cookie-banner/cookies-and-vendors/get-vendors-chart-data';
                $event->rules['cookie-banner/cookies-and-vendors/search-vendors'] = 'cookie-banner/cookies-and-vendors/search-vendors';
                $event->rules['cookie-banner/cookies-and-vendors/bulk-create'] = 'cookie-banner/cookies-and-vendors/bulk-create';

                $event->rules['cookie-banner/content'] = 'cookie-banner/content/index';
                $event->rules['cookie-banner/content/add-cookie'] = 'cookie-banner/content/add-cookie';

                $event->rules['cookie-banner/appearance'] = 'cookie-banner/appearance/index';

                $event->rules['cookie-banner/consent-records'] = 'cookie-banner/consent-records/view';
                $event->rules['cookie-banner/consent-records/create'] = 'cookie-banner/consent-records/create';
                $event->rules['cookie-banner/consent-records/<id:\d+>'] = 'cookie-banner/consent-records/edit';
                $event->rules['cookie-banner/consent-records/get-chart-data'] = 'cookie-banner/consent-records/get-chart-data';
                
                $event->rules['cookie-banner/settings'] = 'cookie-banner/settings/index';             
            }
        );
    }

    /**
     * @return void
     */
    private static function registerSiteModelEvents(): void {
        Event::on(
            Sites::class,
            Sites::EVENT_AFTER_SAVE_SITE,
            function (SiteEvent $event) {
                if ($event->isNew) {
                    $content = new Content();

                    $languageCode = strtolower(explode('-', $event->site->language)[0]);
                    $basePath = CookieBannerPlugin::getInstance()->getBasePath() . '/static/content';

                    $baseContent = file_get_contents("{$basePath}/{$languageCode}.json");
                    if (!$baseContent) $baseContent = file_get_contents("{$basePath}/default.json");

                    $content->siteId = $event->site->id;
                    $content->attributes = json_decode($baseContent, true);
                    $content->save(false); 

                    $appearance = new Appearance();

                    $appearance->siteId = $event->site->id;
                    $appearance->save(false);
                }
            }
        );

        Event::on(
            Sites::class,
            Sites::EVENT_AFTER_DELETE_SITE,
            function (SiteEvent $event) {
                Content::deleteAll(['siteId' => $event->site->id]);
                Appearance::deleteAll(['siteId' => $event->site->id]);
            }
        );
    }

    /**
     * @return void
     */
    private static function registerSiteRoutes(): void {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['cookie-banner/consent-records/create'] = 'cookie-banner/consent-records/create';
            }
        );
    }

    /**
     * @return void
     */
    private static function registerCookieBannerHtml(): void {
        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE,
            function (TemplateEvent $event) {
                $currentSiteId = Craft::$app->getSites()->getCurrentSite()->id;

                $settings = CookieBannerPlugin::getInstance()->getSettings();
                $appearance = Appearance::find()->where(['siteId' => $currentSiteId])->one();
                $content = Content::find()->where(['siteId' => $currentSiteId])->one();

                if (!$settings->cookieBannerEnabled) return;

                $dataLayerScript = Craft::$app->getView()->renderTemplate('cookie-banner/components/_dataLayerScript');
                $bannerHtml = Craft::$app->getView()->renderTemplate('cookie-banner/components/_banner.twig', [
                    'appearance' => $appearance,
                    'content' => $content
                ]);

                Craft::$app->getView()->registerHtml($bannerHtml, View::POS_END);
                Craft::$app->getView()->registerHtml($dataLayerScript, View::POS_HEAD);
            }
        );
    }
}
