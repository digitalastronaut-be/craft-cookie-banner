<?php

namespace digitalastronaut\craftcookiebanner\controllers;

use Craft;
use craft\errors\MissingComponentException;
use craft\helpers\UrlHelper;
use craft\web\Controller;

use yii\db\Exception;
use yii\web\Response;

use digitalastronaut\craftcookiebanner\records\Content;

class ContentController extends Controller {
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = true;

    /**
     * @return Response
     */
    public function actionIndex(): Response {
        $currentSiteId = Craft::$app->getSites()->getSiteByHandle($this->request->queryParams['site'] ?? Craft::$app->sites->primarySite->handle)->id;
        $content = Content::find()->where(['siteId' => $currentSiteId])->one();
        
        return $this->renderTemplate('cookie-banner/_content.twig', [
            'banner' => $content
        ]);
    }

    /**
     * @return Response
     * @throws MissingComponentException
     * @throws Exception
     */
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
}
