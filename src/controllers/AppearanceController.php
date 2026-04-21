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

namespace digitalastronaut\craftcookiebanner\controllers;

use Craft;
use craft\web\Controller;
use craft\errors\MissingComponentException;

use yii\db\Exception;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;

use digitalastronaut\craftcookiebanner\records\Appearance;

use Throwable;

/**
 * Class AppearanceController
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 */
class AppearanceController extends Controller {
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    /**
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionIndex(): Response {
        $this->requirePermission("cookie-banner:access-appearance");

        $siteHandle = $this->request->queryParams['site'] ?? Craft::$app->sites->primarySite->handle;

        $site = Craft::$app->getSites()->getSiteByHandle($siteHandle);

        if (!$site) throw new NotFoundHttpException("Invalid site handle: {$siteHandle}");

        $appearance = Appearance::find()->where(['siteId' => $site->id])->one();

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

    /**
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @return Response
     * 
     * @since v1.0.8-beta
     */
    public function actionCopyAppearanceFromSite(): Response {
        $this->requirePostRequest();
        $this->requirePermission('cookie-banner:edit-appearance');

        try {
            $sourceSiteId = $this->request->getRequiredBodyParam("sourceSiteId");
            $destinationSiteId = $this->request->getRequiredBodyParam("destinationSiteId");

            $sourceSite = Craft::$app->getSites()->getSiteById($sourceSiteId);
            $destinationSite = Craft::$app->getSites()->getSiteById($destinationSiteId);

            $sourceSiteAppearance = Appearance::find()->where(['siteId' => $sourceSiteId])->one();
            $destinationSiteAppearance = Appearance::find()->where(['siteId' => $destinationSiteId])->one();

            if (!$sourceSiteAppearance) 
                throw new NotFoundHttpException("Appearance settings not found for source siteId: {$sourceSiteId}");
            
            if (!$destinationSiteAppearance)
                throw new NotFoundHttpException("Appearance settings not found for destination siteId: {$destinationSiteId}");

            $destinationSiteAppearance->setAttributes($sourceSiteAppearance->getAttributes(null, ['id', 'siteId', 'uid']));

            if (!$destinationSiteAppearance->save()) {
                throw new Exception(\sprintf(
                    'Failed copying appearance to "%s": %s',
                    $destinationSite->name,
                    json_encode($destinationSiteAppearance->getErrors())
                ));
            }

            Craft::$app->getSession()->setSuccess(Craft::t(
                'cookie-banner',
                "Appearance copied from {$sourceSite->name} to {$destinationSite->name} successfully"
            ));

            return $this->redirectToPostedUrl();
        } catch(Throwable $error) {
            Craft::error($error->getMessage(), __METHOD__);
            Craft::$app->getSession()->setError('Error copying appearance ' . $error->getMessage());

            return $this->redirectToPostedUrl();
        }
    }
}
