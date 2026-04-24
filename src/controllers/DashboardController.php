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

use yii\web\Response;
use yii\web\BadRequestHttpException;

use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\models\Settings;

/**
 * Class DashboardController
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 */
class DashboardController extends Controller {
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    private const VALID_STEPS = [
        'legalPagesStepCompleted',
        'deferScriptsStepCompleted',
        'contentStepCompleted',
        'appearanceStepCompleted',
        'finalSettingsStepCompleted',
    ];

    public function actionIndex(): Response {
        $this->requirePermission("cookie-banner:access-dashboard");

        /** @var Settings $settings */
        $settings = CookieBanner::getInstance()->getSettings();

        return $this->renderTemplate('cookie-banner/pages/_dashboard.twig', [
            'consentRecordsChart' => CookieBanner::getInstance()->getConsentRecords()->getChartData(),
            'cookiesAndVendorsChart' => [
                'vendors' => CookieBanner::getInstance()->getCookiesAndVendors()->getCookieChartData(),
                'cookies' => CookieBanner::getInstance()->getCookiesAndVendors()->getVendorsChartData(),
            ],
            'gettingStartedProgress' => $settings->gettingStartedProgress,
        ]);
    }

    /**
     * @return Response
     */
    public function actionSkipGuide(): Response {
        $this->requirePermission("cookie-banner:update-guide-progress");
        $this->requirePostRequest();

        /** @var Settings $settings */
        $settings = CookieBanner::getInstance()->getSettings();

        $settings->gettingStartedProgress['legalPagesStepCompleted'] = true;
        $settings->gettingStartedProgress['deferScriptsStepCompleted'] = true;
        $settings->gettingStartedProgress['contentStepCompleted'] = true;
        $settings->gettingStartedProgress['appearanceStepCompleted'] = true;
        $settings->gettingStartedProgress['finalSettingsStepCompleted'] = true;

        Craft::$app->plugins->savePluginSettings(CookieBanner::getInstance(), $settings->toArray());

        Craft::$app->getSession()->setSuccess("Setup guide skipped");
        return $this->redirectToPostedUrl();
    }

    /**
     * @throws BadRequestHttpException
     * @return Response
     */
    public function actionCompleteGuideStep(): Response {
        $this->requirePermission('cookie-banner:update-guide-progress');
        $this->requirePostRequest();

        $step = Craft::$app->getRequest()->getRequiredBodyParam('step');

        if (!\in_array($step, self::VALID_STEPS, true)) {
            throw new BadRequestHttpException("Invalid guide step: $step");
        }

        /** @var Settings $settings */
        $settings = CookieBanner::getInstance()->getSettings();
        $settings->gettingStartedProgress[$step] = true;

        Craft::$app->plugins->savePluginSettings(CookieBanner::getInstance(), $settings->toArray());

        Craft::$app->getSession()->setSuccess('Step completed successfully');
        return $this->redirectToPostedUrl();
    }
}

