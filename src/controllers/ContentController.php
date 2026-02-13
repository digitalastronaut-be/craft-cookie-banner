<?php

namespace digitalastronaut\craftcookiebanner\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use digitalastronaut\craftcookiebanner\CookieBanner;
use yii\web\Response;

use digitalastronaut\craftcookiebanner\records\Content;

class ContentController extends Controller {
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = true;

    public function actionIndex(): Response {
        $currentSiteId = Craft::$app->getSites()->getSiteByHandle($this->request->queryParams['site'] ?? Craft::$app->sites->primarySite->handle)->id;
        $content = Content::find()->where(['siteId' => $currentSiteId])->one();
        
        return $this->renderTemplate('cookie-banner/_content.twig', [
            'banner' => $content
        ]);
    }

    public function actionSave(): Response {
        $body = $this->request->bodyParams;
        $content = Content::find()->where(['siteId' => $body['siteId']])->one();

        $content->setAttributes($body['fields']);

        if (!$content->save()) {
            Craft::$app->getSession()->setError(Craft::t('cookie-banner', 'Could not save content.'));
        }

        Craft::$app->getSession()->setNotice(Craft::t('cookie-banner', 'Content saved successfully.'));

        return $this->redirect(UrlHelper::cpUrl('cookie-banner/content'));
    }

    public function actionAddCookie() {
        $cookieName = $this->request->getParam('cookieName');

        if (!$cookieName) {
            Craft::$app->getSession()->setError('Unable to add cookie: missing body param cookieName');
            return null;
        }

        $sites = Craft::$app->sites->getAllSites();

        foreach ($sites as $site) {
            $data = CookieBanner::getInstance()
                ->getCookieDetection()
                ->getCookieDataFromDatabase($cookieName, explode("-", $site->language)[0]);

            if (!$data) continue;

            $cookieData = $data['cookie'];

            if (!$data['languageMatch']) {
                $cookieData = [
                    'name' => $cookieName,
                    'description' => null,
                    'vendor' => null,
                    'retention' => null,
                    'category' => $cookieData['category']
                ];
            }
            
            $content = Content::find()->where(['siteId' => $site->id])->one();

            $existingCookies = CookieBanner::getInstance()
                ->getCookieDetection()
                ->getBannerCookies($content);

            $alreadyExists = false;
    
            foreach ($existingCookies as $cookie) {
                if (isset($cookie['name']) && $cookie['name'] === $cookieData['name']) {
                    $alreadyExists = true;
                    break;
                }
            }

            if ($alreadyExists) {
                Craft::$app->getSession()->setError("Cookie '{$cookieData['name']}' already exists for {$site->name}.");
            } else {
                if ($cookieData['category']) {
                    $cookiesForCategory = $content[$cookieData['category'] . 'Cookies'] ?? [];

                    $cookiesForCategory[] = [
                        "name" => $cookieData['name'],
                        "group" => 'default',
                        "purpose" => $cookieData['description'],
                        "expiration" => $cookieData['retention'],
                    ];

                    $content->setAttribute($cookieData['category'] . 'Cookies', $cookiesForCategory);
                    $content->save();
                }

                Craft::$app->getSession()->setNotice("Cookie '{$cookieData['name']}' added successfully.");
            }
        }

        return null;
    }

    public function actionDeleteCookieForAllSites() {
        $cookieName = $this->request->getParam("cookieName");

        $allContent = Content::find()->all();

        foreach ($allContent as $content) {            
            $cookieGroups = [
                'necessaryCookies',
                'preferenceCookies',
                'analyticalCookies',
                'marketingCookies',
                'uncategorizedCookies'
            ];
            
            foreach ($cookieGroups as $group) {
                $cookies = $content->$group ?? [];
                
                $filteredCookies = array_values(array_filter(
                    $cookies,
                    fn($cookie) => ($cookie['name'] ?? null) !== $cookieName
                ));

                $content->setAttribute($group, $filteredCookies);
            }

            $content->save();
        }

        return null;
    }
}
