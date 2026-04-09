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

        return $this->renderTemplate('cookie-banner/_dashboard.twig', [
            "consentRecordsChart" => [
                "total" => ConsentRecord::find()->count(),
            ],
            "gettingStartedProgress" => $settings->gettingStartedProgress,
        ]);
    }

    public function actionSkipGuide(): Response {
        $settings = CookieBanner::getInstance()->getSettings();

        $settings->gettingStartedProgress['legalPagesStepCompleted'] = true;
        $settings->gettingStartedProgress['deferScriptsStepCompleted'] = true;
        $settings->gettingStartedProgress['contentStepCompleted'] = true;
        $settings->gettingStartedProgress['appearanceStepCompleted'] = true;
        $settings->gettingStartedProgress['finalSettingsStepCompleted'] = true;

        return $this->redirectToPostedUrl();
    }

    public function actionCompleteLegalPagesStep(): Response {
        $this->requirePostRequest();
        
        $settings = CookieBanner::getInstance()->getSettings();
        $settings->gettingStartedProgress['legalPagesStepCompleted'] = true;

        Craft::$app->plugins->savePluginSettings(CookieBanner::getInstance(), $settings->toArray());

        return $this->redirectToPostedUrl();
    }

    public function actionCompleteDeferScriptsStep(): Response {
        $this->requirePostRequest();
        
        $settings = CookieBanner::getInstance()->getSettings();
        $settings->gettingStartedProgress['deferScriptsStepCompleted'] = true;

        Craft::$app->plugins->savePluginSettings(CookieBanner::getInstance(), $settings->toArray());

        return $this->redirectToPostedUrl();
    }

    public function actionCompleteContentStep(): Response {
        $this->requirePostRequest();
        
        $settings = CookieBanner::getInstance()->getSettings();
        $settings->gettingStartedProgress['contentStepCompleted'] = true;

        Craft::$app->plugins->savePluginSettings(CookieBanner::getInstance(), $settings->toArray());

        return $this->redirectToPostedUrl();
    }

    public function actionCompleteAppearanceStep(): Response {
        $this->requirePostRequest();
        
        $settings = CookieBanner::getInstance()->getSettings();
        $settings->gettingStartedProgress['appearanceStepCompleted'] = true;

        Craft::$app->plugins->savePluginSettings(CookieBanner::getInstance(), $settings->toArray());

        return $this->redirectToPostedUrl();
    }

    public function actionCompleteFinalSettingsStep(): Response {
        $this->requirePostRequest();
        
        $settings = CookieBanner::getInstance()->getSettings();
        $settings->gettingStartedProgress['finalSettingsStepCompleted'] = true;

        Craft::$app->plugins->savePluginSettings(CookieBanner::getInstance(), $settings->toArray());

        return $this->redirectToPostedUrl();
    }
}

