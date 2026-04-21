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
use craft\errors\MissingComponentException;
use craft\web\Controller;

use yii\db\Exception;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;

use digitalastronaut\craftcookiebanner\records\Content;

use Throwable;

/**
 * Class ContentController
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 */
class ContentController extends Controller {
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    /**
     * @return Response
     * @throws ForbiddenHttpException
     */
    public function actionIndex(): Response {
        $this->requirePermission("cookie-banner:access-content");

        $siteHandle = $this->request->queryParams['site'] ?? Craft::$app->sites->primarySite->handle;

        $site = Craft::$app->getSites()->getSiteByHandle($siteHandle);

        if (!$site) throw new NotFoundHttpException("Invalid site handle: {$siteHandle}");

        $content = Content::find()->where(['siteId' => $site->id])->one();
        
        return $this->renderTemplate('cookie-banner/pages/_content.twig', [
            'content' => $content
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
        $this->requirePermission("cookie-banner:edit-content");

        try {
            $siteId = $this->request->getBodyParam("siteId");
            $fields = $this->request->getBodyParam("fields");

            if (!$siteId || !$fields) throw new BadRequestHttpException('Missing body params');

            $site = Craft::$app->getSites()->getSiteById($siteId);
            $content = Content::find()->where(['siteId' => $siteId])->one();

            if (!$content || !$site) throw new Exception("Content settings not found for siteId: {$siteId}");

            $content->setAttributes($fields);

            if (!$content->save()) {
                throw new Exception(sprintf(
                    'Failed saving content for site "%s": %s',
                    $site->name,
                    json_encode($content->getErrors())
                ));
            }

            Craft::$app->getSession()->setSuccess(Craft::t('cookie-banner', "Content saved successfully for {$site->name}"));
            return $this->redirectToPostedUrl();

        } catch(Throwable $error) {
            Craft::error($error->getMessage(), __METHOD__);
            Craft::$app->getSession()->setError('Error saving content: ' . $error->getMessage());

            return $this->redirectToPostedUrl();
        }
    }
}
