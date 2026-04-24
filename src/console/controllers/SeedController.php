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

use Carbon\Carbon;

use craft\console\Controller;

use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\helpers\CookieBanner as CookieBannerHelper;

use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Class SeedController
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 */
class SeedController extends Controller {
    public $defaultAction = 'index';

    /**
     * @return int
     */
    public function actionIndex(): int {
        $this->stdout("Use this controller to seed data for cookie banner related tables" . PHP_EOL, Console::FG_CYAN);

        return ExitCode::OK;
    }

    /**
     * @param int $count
     * @param bool $fixedIpAddress
     * @param string $period
     * @return int
     */
    public function actionConsentRecords(
        int $count = 5,
        bool $fixedIpAddress = false,
        string $period = '30 days'
    ): int {
        $this->stdout("Preparing fake consent records over period: {$period}..." . PHP_EOL, Console::FG_CYAN);

        $languages = ['en-US', 'de-DE', 'fr-FR', 'es-ES', 'nl-NL'];
        $actions = ['Accept all', 'Accept selected', 'Refuse all'];
        $userAgents = CookieBannerHelper::EXAMPLE_USER_AGENTS;

        $now = Carbon::now();
        $startDate = Carbon::now()->modify("-{$period}");
        $secret = Craft::$app->getConfig()->getGeneral()->securityKey;

        for ($i = 0; $i < $count; $i++) {
            $fakeIp = rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
            if ($fixedIpAddress) $fakeIp = '178.119.216.167';

            $ipAddressHash = hash_hmac('sha256', $fakeIp, $secret);
            $shortHash = substr($ipAddressHash, 0, 10);

            $randomTimestamp = mt_rand($startDate->getTimestamp(), $now->getTimestamp());
            $consentTimestamp = Carbon::now()->setTimestamp($randomTimestamp);

            $acceptAll = (bool) rand(0, 1);

            CookieBanner::getInstance()
                ->getConsentRecords()
                ->createConsentRecord([
                    'title' => "Consent {$shortHash}",
                    'ipAddressHash' => $ipAddressHash,
                    'sessionId' => uniqid("", true),
                    'userAgent' => $userAgents[array_rand($userAgents)],
                    'language' => $languages[array_rand($languages)],
                    'consentTimestamp' => $consentTimestamp,
                    'consentAction' => $actions[array_rand($actions)],
                    'necessaryCookies' => true,
                    'preferenceCookies' => $acceptAll ?: (bool)rand(0, 1),
                    'analyticalCookies' => $acceptAll ?: (bool)rand(0, 1),
                    'marketingCookies' => $acceptAll ?: (bool)rand(0, 1),
                    'uncategorizedCookies' => $acceptAll ?: (bool)rand(0, 1),
                    'consentMethod' => 'Seeder script',
                    'bannerVersion' => 'v1.0.0',
                    'privacyPolicyVersion' => 'v1.0.0',
                    'cookiePolicyVersion' => 'v1.0.0',
                ]);

            $progress = intval((($i + 1) / $count) * 100);
            $this->stdout("\rInserting: {$progress}% (" . ($i + 1) . "/{$count})");
        }

        $this->stdout(PHP_EOL . "Done seeding {$count} fake consent-record(s)." . PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }
}
