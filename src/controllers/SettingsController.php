<?php

namespace digitalastronaut\craftcookiebanner\controllers;

use Craft;
use craft\web\Controller;
use craft\web\Response;
use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\records\Content;

class SettingsController extends Controller{
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    public function actionIndex(): Response {
        $this->requirePermission("cookie-banner:access-settings");
        
        $settings = CookieBanner::getInstance()->getSettings();
        
        return $this->renderTemplate('cookie-banner/pages/_settings.twig', [
            'settings' => $settings,
        ]);
    }
}
