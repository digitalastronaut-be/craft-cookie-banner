<?php

namespace digitalastronaut\craftcookiebanner\controllers;

use Craft;
use craft\web\Controller;

use yii\web\Response;

use digitalastronaut\craftcookiebanner\CookieBanner;

class AppearanceController extends Controller {
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = true;

    public function actionIndex(): Response {
        $settings = CookieBanner::getInstance()->getSettings();

        return $this->renderTemplate("cookie-banner/_appearance", [
            'settings' => $settings,
        ]);
    }
}
