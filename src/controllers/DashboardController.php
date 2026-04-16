<?php

namespace digitalastronaut\craftcookiebanner\controllers;

use Craft;
use craft\web\Controller;

use yii\web\Response;

use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\elements\ConsentRecord;

/**
 * Getting Started controller
 */
class DashboardController extends Controller {
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    public function actionIndex(): Response {
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

    public function actionCompleteLegalPagesStep(): Response {
        $this->requirePostRequest();
        
        $settings = CookieBanner::getInstance()->getSettings();
        $settings->gettingStartedProgress['legalPagesStepCompleted'] = true;

        Craft::$app->plugins->savePluginSettings(CookieBanner::getInstance(), $settings->toArray());

        Craft::$app->getSession()->setSuccess("Legal pages step completed");
        return $this->redirectToPostedUrl();
    }
        
    public function actionCompleteDeferScriptsStep(): Response {
        $this->requirePostRequest();
        
        $settings = CookieBanner::getInstance()->getSettings();
        $settings->gettingStartedProgress['deferScriptsStepCompleted'] = true;
        
        Craft::$app->plugins->savePluginSettings(CookieBanner::getInstance(), $settings->toArray());
            
        Craft::$app->getSession()->setSuccess("Defer third party scripts step completed");
        return $this->redirectToPostedUrl();
    }
        
    public function actionCompleteContentStep(): Response {
        $this->requirePostRequest();
        
        $settings = CookieBanner::getInstance()->getSettings();
        $settings->gettingStartedProgress['contentStepCompleted'] = true;
        
        Craft::$app->plugins->savePluginSettings(CookieBanner::getInstance(), $settings->toArray());
            
        Craft::$app->getSession()->setSuccess("Content step completed");
        return $this->redirectToPostedUrl();
    }

    public function actionCompleteAppearanceStep(): Response {
        $this->requirePostRequest();
        
        $settings = CookieBanner::getInstance()->getSettings();
        $settings->gettingStartedProgress['appearanceStepCompleted'] = true;

        Craft::$app->plugins->savePluginSettings(CookieBanner::getInstance(), $settings->toArray());

        Craft::$app->getSession()->setSuccess("Appearance step completed");
        return $this->redirectToPostedUrl();
    }

    public function actionCompleteFinalSettingsStep(): Response {
        $this->requirePostRequest();
        
        $settings = CookieBanner::getInstance()->getSettings();
        $settings->gettingStartedProgress['finalSettingsStepCompleted'] = true;

        Craft::$app->plugins->savePluginSettings(CookieBanner::getInstance(), $settings->toArray());

        Craft::$app->getSession()->setSuccess("Final settings step completed");
        return $this->redirectToPostedUrl();
    }
}

