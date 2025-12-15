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
            dd($content->getErrors());
        }

        Craft::$app->getSession()->setNotice(Craft::t('cookie-banner', 'Content saved successfully.'));

        return $this->redirect(UrlHelper::cpUrl('cookie-banner/content'));
    }

    public function actionAddCookie() {
        $cookieName = $this->request->getBodyParam("cookieName");

        if (!$cookieName) {
            Craft::$app->getSession()->setError("Unable to add cookie: missing body param cookieName");
            return null;
        }

        $cookieData = CookieBanner::getInstance()->getCookieDetection()->getCookieDataFromDatabase($cookieName, 'en');
 
        if (!$cookieData) {
            $cookieData = [
		        "name" => $cookieName,
		        "description" => null,
		        "vendor" => null,
		        "retention" => null,
		        "category" => null
            ];
        }

        $cookieBannerContentAllLanguages = Content::find()->all();

        foreach ($cookieBannerContentAllLanguages as $content) {
            $site = Craft::$app->getSites()->getSiteById($content->siteId);
            $siteHandle = $site->handle;

            $existingCookies = CookieBanner::getInstance()->getCookieDetection()->getBannerCookies($content);

            $alreadyExists = false;
    
            foreach ($existingCookies as $cookie) {
                if (isset($cookie['name']) && $cookie['name'] === $cookieData['name']) {
                    $alreadyExists = true;
                    break;
                }
            }

            if ($alreadyExists) {
                Craft::$app->getSession()->setError("Cookie '{$cookieData['name']}' already exists.");
                return null; 
            } else {
                if ($cookieData['category']) {
                    $cookiesForCategory = $content['uncategorizedCookies'] ?? [];

                    $cookiesForCategory[] = [
                        "name" => $cookieData['name'],
                        "group" => 'default',
                        "purpose" => $cookieData['description'],
                        "expiration" => $cookieData['retention'],
                    ];

                    $content->setAttribute('uncategorizedCookies', $cookiesForCategory);
                    dd($content);
                    $content->save();   
                }


                Craft::$app->getSession()->setNotice("Cookie '{$cookieData['name']}' added to {$siteHandle} successfully.");
            }
        }

        return null;
    }
}
