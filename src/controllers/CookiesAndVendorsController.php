<?php

namespace digitalastronaut\craftcookiebanner\controllers;

use Craft;
use Exception;
use craft\web\Controller;

use yii\web\Response;

use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\helpers\CookieBanner as CookieBannerHelper;
use digitalastronaut\craftcookiebanner\records\Content;
use yii\web\BadRequestHttpException;

// Refactoring routes when all actions are ok functionality wise
// actionIndex (GET) -> Load cookiesAndVendors.twig
// actionManuallyAddCookie (GET) -> Load createCookie.twig
// actionManuallyAddCookie (POST) -> Create cookie
// actionAutoAddCookie (POST) -> Create cookie based on cookie DB data
// actionEditCookie (GET) -> Load createCookie.twig
// actionEditCookie (POST) -> Edit cookie
// actionBlacklistCookie (POST) -> Blacklist a cookie
// actionDeleteCookieForAllSites (DELETE) -> Delete cookie for all sites

class CookiesAndVendorsController extends Controller {
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    public function actionIndex(): Response {
        $settings = CookieBanner::getInstance()->getSettings();

        return $this->renderTemplate("cookie-banner/_cookiesAndVendors", [
            'settings' => $settings,
        ]);
    }

    public function actionCreate(): Response {
        $settings = CookieBanner::getInstance()->getSettings();
        $sites = Craft::$app->getSites()->getAllSites();

        if (!$this->request->isPost) {
            $autoFillCookie = $this->request->getParam('autoFillCookie');

            $cookieForAllSites = [];
            
            if ($autoFillCookie) {
                foreach ($sites as $site) {
                    $data = CookieBanner::getInstance()
                        ->getCookieDetection()
                        ->getCookieDataFromDatabase($autoFillCookie, explode("-", $site->language)[0]);

                    $cookieForAllSites[$site->id] = [
                        'siteName' => $site->name,
                        'name' => $data['cookie']['name'],
                        'group' => $data['cookie']['category'],
                        'purpose' => $data['languageMatch'] ? $data['cookie']['description'] : "",
                        'expiration' => $data['languageMatch'] ? $data['cookie']['retention'] : "",
                        'enabled' => true,
                    ];
                }
            } else {
                foreach ($sites as $site) {
                    $cookieForAllSites[$site->id] = [
                        'siteName' => $site->name,
                        'name' => "",
                        'group' => null,
                        'purpose' => "",
                        'expiration' => "",
                        'enabled' => true,
                    ];
                }
            }

            return $this->renderTemplate("cookie-banner/_createCookie", [
                'settings' => $settings,
                'cookieForAllSites' => $cookieForAllSites,
            ]);
        } else {
            $db = Craft::$app->getDb();
            $transaction = $db->beginTransaction();

            try {
                foreach ($sites as $site) {
                    $category = $this->request->bodyParams['category'];
                    $newCookie = $this->request->bodyParams['fields']['cookieForAllSites'][$site->id];

                    if (!$newCookie['name']) {
                        throw new BadRequestHttpException("Cookie name param missing for " . $site->name);
                    }

                    $content = Content::find()->where(['siteId' => $site->id])->one();

                    if (!$content) {
                        throw new \Exception("Content record missing for site {$site->id}");
                    }

                    $cookies = $content->getAttribute($category) ?? [];

                    $cookies[] = [
                        'name' => $newCookie['name'],
                        'group' => $newCookie['group'],
                        'purpose' => $newCookie['purpose'],
                        'expiration' => $newCookie['expiration'],
                        'enabled' => $newCookie['enabled'],
                    ];

                    $content->setAttribute($category, $cookies);

                    if (!$content->save()) {
                        throw new \Exception('Failed saving content: ' . json_encode($content->getErrors()));
                    }
                }

                $transaction->commit();

                Craft::$app->getSession()->setSuccess($newCookie['name'] . ' cookie created for all sites.');
                return $this->redirect('cookie-banner/cookies-and-vendors');

            } catch (\Throwable $e) {
                $transaction->rollBack();

                Craft::error($e->getMessage(), __METHOD__);
                Craft::$app->getSession()->setError('Failed to create cookie ' . $e->getMessage());

                return $this->redirectToPostedUrl();
            }
        }
    }

    public function actionEdit(string $name): Response {
        if ($this->request->isPost) {
            $db = Craft::$app->getDb();
            $transaction = $db->beginTransaction();

            try {
                foreach ($this->request->bodyParams['fields']['cookieForAllSites'] as $cookie) {
                    $site = Craft::$app->sites->getSiteByHandle($cookie['siteHandle']);

                    $currentCategory = $this->request->bodyParams['currentCategory'];
                    $newCategory = $this->request->bodyParams['category'];

                    $content = Content::find()
                        ->where(['siteId' => $site->id])
                        ->one();

                    if (!$content) throw new \Exception("Content record missing for site {$site->id}");

                    if ($currentCategory !== $newCategory) {
                        $oldCookies = $content->getAttribute($currentCategory) ?? [];

                        $oldCookies = array_values(array_filter(
                            $oldCookies,
                            fn($oldCookie) => $oldCookie['name'] !== $cookie['name']
                        ));

                        $content->setAttribute($currentCategory, $oldCookies);
                    }

                    $newCookies = $content->getAttribute($newCategory) ?? [];
                    $newCookiesByName = array_column($newCookies, null, 'name');

                    $newCookiesByName[$cookie['name']] = [
                        'name' => $cookie['name'],
                        'group' => $cookie['group'],
                        'purpose' => $cookie['purpose'],
                        'expiration' => $cookie['expiration'],
                        'enabled' => (bool)($cookie['enabled'] ?? false),
                    ];

                    $content->setAttribute($newCategory, array_values($newCookiesByName));

                    if (!$content->save()) {
                        throw new \Exception('Failed saving content: ' . json_encode($content->getErrors()));
                    }
                }

                $transaction->commit();

                Craft::$app->getSession()->setNotice('Cookie saved for all sites.');
                return $this->redirect("cookie-banner/cookies-and-vendors", 200);
            } catch (\Throwable $e) {
                $transaction->rollBack();

                Craft::error($e->getMessage(), __METHOD__);
                Craft::$app->getSession()->setError('Could not save cookie.');

                return $this->redirectToPostedUrl();
            }
        }

        $settings = CookieBanner::getInstance()->getSettings();
        $allContent = Content::find()->all();
        
        $cookieForAllSites = [];
        
        foreach ($allContent as $content) {
            $site = Craft::$app->getSites()->getSiteById($content->siteId);
            $siteName = $site ? $site->name : 'unknown';
            
            $cookieGroups = CookieBannerHelper::COOKIE_GROUPS;
            $cookieFound = false;

            foreach ($cookieGroups as $groupField) {
                $cookies = empty($content->$groupField) ? [] : $content->$groupField;

                foreach ($cookies as $cookie) {
                    if ($cookie['name'] !== $name) continue;

                    $cookieFound = true;
                    $cookieForAllSites[] = [
                        'siteName' => $siteName,
                        'name' => $cookie['name'] ?? null,
                        'group' => $cookie['group'] ?? null,
                        'purpose' => $cookie['purpose'] ?? null,
                        'expiration' => $cookie['expiration'] ?? null,
                        'enabled' => $cookie['enabled'] ?? true,
                        'category' => $groupField ?? null,
                        'hiddenInputs' => [
                            'siteHandle' => $site->handle,
                        ],
                    ];
                }
            }
            
            // If cookie was not found in any group for this site, add blank entry
            if (!$cookieFound) {
                $cookieForAllSites[] = [
                    'siteName' => $siteName,
                    'name' => $name,
                    'group' => null,
                    'purpose' => null,
                    'expiration' => null,
                    'enabled' => true,
                    'category' => 'uncategorizedCookies',
                    'hiddenInputs' => [
                        'siteHandle' => $site->handle,
                    ],
                ];
            }
        }

        return $this->renderTemplate("cookie-banner/_editCookie", [
            'settings' => $settings,
            'cookieForAllSites' => $cookieForAllSites,
        ]);
    }

    public function actionGetVendors() {
        return Content::find()->one()->cookieGroups;
    }

    public function actionGetCookies() {
        $content = Content::find()->one();

        dd("hello");
    }

    public function actionCreateVendor() {
        $sites = Craft::$app->getSites()->getAllSites();

        if (!$this->request->isPost) {
            $autoFillVendor = $this->request->getParam('autoFillVendor');

            $vendorForAllSites = [];

            if ($autoFillVendor) {
                foreach ($sites as $site) {
                    $data = CookieBanner::getInstance()
                        ->getCookieDetection()
                        ->getVendorDataFromDatabase($autoFillVendor, explode("-", $site->language)[0]);

                    $vendorForAllSites[$site->id] = [
                        'siteName' => $site->name,
                        'name' => $data['languageMatch'] ? $data['vendor']['name'] : $autoFillVendor,
                        'url' => $data['languageMatch'] ? $data['vendor']['homePage'] : "",
                        'description' => $data['languageMatch'] ? $data['vendor']['description'] : "",
                        'enabled' => true,
                    ];
                }
            } else {
                foreach ($sites as $site) {
                    $vendorForAllSites[$site->id] = [
                        'siteName' => $site->name,
                        'name' => "",
                        'url' => "",
                        'description' => "",
                        'enabled' => true,
                    ];
                }
            }

            return $this->renderTemplate("cookie-banner/_createVendor", [
                'vendorForAllSites' => $vendorForAllSites,
            ]);
        } else {
            $db = Craft::$app->getDb();
            $transaction = $db->beginTransaction();

            try {
                foreach ($sites as $site) {
                    $newVendor = $this->request->bodyParams['fields']['vendorForAllSites'][$site->id];

                    $content = Content::find()->where(['siteId' => $site->id])->one();

                    if (!$content) throw new \Exception("Content record missing for site {$site->id}");

                    $groups = $content->getAttribute("cookieGroups") ?? [];

                    $groups[] = [
                        'name' => $newVendor['name'],
                        'url' => $newVendor['url'],
                        'description' => $newVendor['description'],
                        'enabled' => $newVendor['enabled'],
                    ];

                    $content->setAttribute("cookieGroups", $groups);

                    if (!$content->save()) {
                        throw new \Exception('Failed saving content: ' . json_encode($content->getErrors()));
                    }
                }

                $transaction->commit();

                Craft::$app->getSession()->setNotice('Vendor created for all sites.');
                return $this->redirect('cookie-banner/cookies-and-vendors#vendors');

            } catch (\Throwable $e) {
                $transaction->rollBack();

                Craft::error($e->getMessage(), __METHOD__);
                Craft::$app->getSession()->setError('Could not create vendor.' . $e->getMessage());

                return $this->redirectToPostedUrl();
            }
        }
    }

    public function actionEditVendor(string $name) {
        if ($this->request->isPost) {
            $db = Craft::$app->getDb();
            $transaction = $db->beginTransaction();

            try {
                foreach ($this->request->bodyParams['fields']['vendorForAllSites'] as $vendor) {
                    $site = Craft::$app->sites->getSiteByHandle($vendor['siteHandle']);

                    $content = Content::find()
                        ->where(['siteId' => $site->id])
                        ->one();

                    if (!$content) throw new Exception("Content record missing for site {$site->id}");

                    $vendors = $content->getAttribute('cookieGroups');
                    $vendorsByName = array_column($vendors, null, 'name');

                    $vendorsByName[$vendor['name']] = [
                        'name' => $vendor['name'], 
                        'url' => $vendor['url'], 
                        'description' => $vendor['description'], 
                        'enabled' => $vendor['enabled'], 
                    ];

                    $content->setAttribute('cookieGroups', array_values($vendorsByName));

                    if (!$content->save()) {
                        throw new Exception('Failed saving content: ' . json_encode($content->getErrors()));
                    }
                }

                $transaction->commit();

                Craft::$app->getSession()->setSuccess('Vendor saved for all sites.');
                return $this->redirect("cookie-banner/cookies-and-vendors", 200);
            } catch (\Throwable $e) {
                $transaction->rollBack();

                Craft::error($e->getMessage(), __METHOD__);
                Craft::$app->getSession()->setError('Could not save vendor.');

                return $this->redirectToPostedUrl();
            }
        } else {
            $allContent = Content::find()->all();
            $vendorForAllSites = [];
        
            foreach ($allContent as $content) {
                $site = Craft::$app->getSites()->getSiteById($content->siteId);
                $siteName = $site ? $site->name : 'unknown';

                $vendors = $content['cookieGroups'];
                $vendorFound = false;

                foreach ($vendors as $vendor) {
                    if ($vendor['name'] !== $name) continue;

                    $vendorFound = true;
                    $vendorForAllSites[] = [
                        'siteName' => $siteName,
                        'name' => $vendor['name'],
                        'url' => $vendor['url'],
                        'description' => $vendor['description'],
                        'enabled' => $vendor['enabled'],
                        'hiddenInputs' => [
                            'siteHandle' => $site->handle,
                        ],
                    ];
                }

                if (!$vendorFound) {
                    $vendorForAllSites[] = [
                        'siteName' => $siteName,
                        'name' => $name,
                        'url' => "",
                        'description' => "",
                        'enabled' => $vendor['enabled'],
                        'hiddenInputs' => [
                            'siteHandle' => $site->handle,
                        ],
                    ];
                }
            }

            return $this->renderTemplate("cookie-banner/_editVendor", [
                'vendorForAllSites' => $vendorForAllSites,
            ]);
        }
    }

    public function actionAutoAddVendor() {
        $vendorName = $this->request->getParam('vendorName');

        if (!$vendorName) {
            Craft::$app->getSession()->setError('Unable to add vendor: missing body param vendorName');
            return null;
        }

        $sites = Craft::$app->sites->getAllSites();

        try {
            $db = Craft::$app->getDb();
            $transaction = $db->beginTransaction();
            
            foreach ($sites as $site) {
                $data = CookieBanner::getInstance()
                    ->getCookieDetection()
                    ->getVendorDataFromDatabase($vendorName, explode("-", $site->language)[0]);
                    
                $content = Content::find()->where(['siteId' => $site->id])->one();
                $vendors = $content->getAttribute('cookieGroups');

                $vendors[] = [
                    'name' => $data['languageMatch'] ? $data['vendor']['name'] : $vendorName, 
                    'url' => $data['vendor']['homePage'], 
                    'description' => $data['languageMatch'] ? $data['vendor']['description'] : null, 
                    'enabled' => true, 
                ];

                $content->setAttribute('cookieGroups', $vendors);
                
                if (!$content->save()) {
                    throw new Exception('Failed saving vendor: ' . json_encode($content->getErrors()));
                }
            }

            $transaction->commit();

            Craft::$app->getSession()->setSuccess("{$vendorName} was automatically added to the cookie banner");
            return $this->redirect("cookie-banner/cookies-and-vendors", 200);
        } catch (\Throwable $e) {
            $transaction->rollBack();

            Craft::error($e->getMessage(), __METHOD__);
            Craft::$app->getSession()->setError('Failed to auto add vendor.' . $e->getMessage());

            return $this->redirectToPostedUrl();
        }
    }
}
