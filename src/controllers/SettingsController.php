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

use craft\web\Controller;
use craft\web\Response;
use digitalastronaut\craftcookiebanner\CookieBanner;

use yii\web\ForbiddenHttpException;

/**
 * Class SettingsController
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 */
class SettingsController extends Controller{
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    /**
     * @throws ForbiddenHttpException
     */
    public function actionIndex(): Response {
        $this->requirePermission("cookie-banner:access-settings");
        
        $settings = CookieBanner::getInstance()->getSettings();
        
        return $this->renderTemplate('cookie-banner/pages/_settings.twig', [
            'settings' => $settings,
        ]);
    }
}
