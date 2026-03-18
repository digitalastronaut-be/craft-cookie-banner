<?php

namespace digitalastronaut\craftcookiebanner\services;

use Craft;
use craft\base\Component;
use digitalastronaut\craftcookiebanner\helpers\CookieBanner;
use digitalastronaut\craftcookiebanner\records\Content;

use yii\db\Exception;
use yii\web\BadRequestHttpException;

class CookiesAndVendorsService extends Component {
    /**
     * @param bool $categorized
     * @return array|mixed[]
     */
    public function getAllCookies(bool $categorized = false) {
        $content = Content::find()->one();
        $categorizedCookies = $content->getAttributes(CookieBanner::COOKIE_GROUPS);

        if (!$content) return [];

        $uncategorizedCookies = [];

        foreach ($categorizedCookies as $category => $cookies) {
            if (!is_array($cookies)) continue;

            foreach ($cookies as $cookie) {
                $cookie['category'] = $category;
                $uncategorizedCookies[] = $cookie;
            }
        }

        return $categorized ? $categorizedCookies : $uncategorizedCookies;
    }

    public function createCookie() {
        $sites = Craft::$app->getSites()->getAllSites();

        $database = Craft::$app->getDb();
        $transaction = $database->beginTransaction();

        try {
            foreach ($sites as $site) {
                $category = $this->request->bodyParams["category"];
                $newCookie = $this->request->bodyParams['fields']['cookieForAllSites'][$site->id];

                if (!$newCookie['name']) {
                    return new BadRequestHttpException("Cookie name param missing for " . $site->name);
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
                    throw new Exception(
                        'Failed saving content for site: ' .
                        $site->name .
                        json_encode($content->getErrors())
                    );
                }
            }

            $transaction->commit();

            Craft::$app->getSession()->setSuccess($newCookie['name'] . ' cookie created for all sites.');
            return $this->redirect('cookie-banner/cookies-and-vendors');
        } catch (Exception $error) {
            $transaction->rollBack();

            Craft::error($error->getMessage(), __METHOD__);
            Craft::$app->getSession()->setError('Failed to create cookie ' . $error->getMessage());

            return $this->redirectToPostedUrl();
        }
    }
}