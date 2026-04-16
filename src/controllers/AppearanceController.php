<?php

namespace digitalastronaut\craftcookiebanner\controllers;

use Craft;
use craft\web\Controller;

use yii\db\Exception;
use yii\web\Response;
use yii\web\BadRequestHttpException;

use digitalastronaut\craftcookiebanner\records\Appearance;

use Throwable;

class AppearanceController extends Controller {
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = true;

    /**
     * @return Response
     */
    public function actionIndex(): Response {
        $currentSiteId = Craft::$app->getSites()->getSiteByHandle($this->request->queryParams['site'] ?? Craft::$app->sites->primarySite->handle)->id;
        $appearance = Appearance::find()->where(['siteId' => $currentSiteId])->one();

        return $this->renderTemplate("cookie-banner/pages/_appearance", [
            'appearance' => $appearance,
        ]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws MissingComponentException
     * @throws Exception
     */
    public function actionSave(): Response {
        $this->requirePostRequest();
        $this->requirePermission("cookie-banner:edit-appearance");

        try {
            $siteId = $this->request->getBodyParam("siteId");
            $fields = $this->request->getBodyParam("fields");
            
            if (!$siteId || !$fields) throw new BadRequestHttpException('Missing body params');
            
            $site = Craft::$app->getSites()->getSiteById($siteId);
            $appearance = Appearance::find()->where(['siteId' => $siteId])->one();

            if (!$appearance || !$site) throw new Exception("Appearance settings not found for siteId: {$siteId}");

            $appearance->setAttributes($fields);

            if (!$appearance->save()) {
                throw new Exception(sprintf(
                    'Failed saving appearance for site "%s": %s',
                    $site->name,
                    json_encode($appearance->getErrors())
                ));
            }

            Craft::$app->getSession()->setSuccess(Craft::t('cookie-banner', "Appearance saved successfully for {$site->name}"));
            return $this->redirectToPostedUrl();

        } catch(Throwable $error) {
            Craft::error($error->getMessage(), __METHOD__);
            Craft::$app->getSession()->setError('Error saving appearance: ' . $error->getMessage());

            return $this->redirectToPostedUrl();
        }
    }
}
