<?php

namespace digitalastronaut\craftcookiebanner\controllers;

use Craft;
use craft\web\Controller;

use yii\web\Response;

use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\records\Content;

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
            $emptyCookieForAllSites = [];

            foreach ($sites as $site) {
                $emptyCookieForAllSites[] = [
                    'siteName' => $site->name,
                    'name' => "",
                    'group' => null,
                    'purpose' => "",
                    'expiration' => "",
                    'enabled' => true,
                ];
            }

            return $this->renderTemplate("cookie-banner/_createCookie", [
                'settings' => $settings,
                'emptyCookieForAllSites' => $emptyCookieForAllSites,
            ]);
        } else {
            $this->request->bodyParams;

            dd($this->request->bodyParams);

            return $this->asJson(["message" => "Need to implment cookie creation logic"]);
        }
    }

    public function actionEdit(string $name): Response {
        $settings = CookieBanner::getInstance()->getSettings();
        $allContent = Content::find()->all();
        
        $cookieForAllSites = [];
        
        foreach ($allContent as $content) {
            $site = Craft::$app->getSites()->getSiteById($content->siteId);
            $siteName = $site ? $site->name : 'unknown';
            
            $cookieGroups = [
                'necessaryCookies',
                'preferenceCookies',
                'analyticalCookies',
                'marketingCookies',
                'uncategorizedCookies'
            ];
            
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
}
