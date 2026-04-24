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

namespace digitalastronaut\craftcookiebanner\console\controllers;

use Craft;
use craft\console\Controller;

use digitalastronaut\craftcookiebanner\jobs\PurgeExpiredConsentRecords;

use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Class ConsentRecordsController
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 */
class ConsentRecordsController extends Controller {
    public $defaultAction = 'index';

    public function actionIndex(): int {
        $this->stdout("Use this controller to purge expired consent records" . PHP_EOL, Console::FG_CYAN);

        return ExitCode::OK;
    }

    public function actionPurgeExpired(): int {
        Craft::$app->queue->push(new PurgeExpiredConsentRecords());

        $this->stdout("Purging expired consent records.\n");

        return ExitCode::OK;
    }
}
