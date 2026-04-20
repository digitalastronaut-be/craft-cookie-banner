<?php

namespace digitalastronaut\craftcookiebanner\controllers;

use Craft;
use craft\web\Controller;

use yii\web\Response;
use yii\web\BadRequestHttpException;

use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\elements\ConsentRecord;

/**
 * Getting Started controller
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

        $settings = CookieBanner::getInstance()->getSettings();

        return $this->renderTemplate('cookie-banner/pages/_dashboard.twig', [
            'consentRecordsChartMetrics' => CookieBanner::getInstance()->getConsentRecords()->getChartData(),
            'cookiesAndVendorsChartMetrics' => [
                'vendors' => CookieBanner::getInstance()->getCookiesAndVendors()->getCookieChartData(),
                'cookies' => CookieBanner::getInstance()->getCookiesAndVendors()->getVendorsChartData(),
            ],
            'gettingStartedProgress' => $settings->gettingStartedProgress,
        ]);
    }

    public function actionSkipGuide(): Response {
        $this->requirePermission("cookie-banner:update-guide-progress");
        $this->requirePostRequest();

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

    public function actionCompleteGuideStep(): Response {
        $this->requirePermission('cookie-banner:update-guide-progress');
        $this->requirePostRequest();

        $step = Craft::$app->getRequest()->getRequiredBodyParam('step');

        if (!in_array($step, self::VALID_STEPS, true)) {
            throw new BadRequestHttpException("Invalid guide step: $step");
        }

        $settings = CookieBanner::getInstance()->getSettings();
        $settings->gettingStartedProgress[$step] = true;

        Craft::$app->plugins->savePluginSettings(CookieBanner::getInstance(), $settings->toArray());

        Craft::$app->getSession()->setSuccess('Step completed successfully');
        return $this->redirectToPostedUrl();
    }
}

