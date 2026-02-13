<?php

namespace digitalastronaut\craftcookiebanner\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;

class GettingStartedController extends Controller {
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    public function actionIndex(): Response {
        return $this->renderTemplate('cookie-banner/_gettingStarted.twig');
    }
}
