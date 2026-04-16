<?php

namespace digitalastronaut\craftcookiebanner\controllers;

use Craft;
use craft\web\Controller;
use craft\web\Response;
use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\records\Content;

class SettingsController extends Controller{
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = true;

    public function actionIndex(): Response {
        $settings = CookieBanner::getInstance()->getSettings();
        $content = Content::find()->where(['siteId' => 1])->one();
        
        return $this->renderTemplate('cookie-banner/pages/_settings.twig', [
            'settings' => $settings,
            'content' => $content
        ]);
    }
}
