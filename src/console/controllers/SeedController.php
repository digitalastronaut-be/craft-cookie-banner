<?php

namespace digitalastronaut\craftcookiebanner\console\controllers;

use DateTime;

use Craft;

use craft\console\Controller;

use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\helpers\CookieBanner as CookieBannerHelper;

use yii\console\ExitCode;
use yii\helpers\Console;

class SeedController extends Controller {
    public $defaultAction = 'index';

    public function actionIndex(): void {
        $this->stdout("Use this controller to seed data for cookie banner related tables" . PHP_EOL, Console::FG_CYAN);
    }

public function actionConsentRecords(
    int $count = 5,
    bool $fixedIpAddress = false,
    string $period = '30 days'
): int {
    $this->stdout("Preparing fake consent records over period: {$period}..." . PHP_EOL, Console::FG_CYAN);

    $languages = ['en-US', 'de-DE', 'fr-FR', 'es-ES', 'nl-NL'];
    $actions = ['Accept all', 'Accept selected', 'Refuse all'];
    $userAgents = CookieBannerHelper::EXAMPLE_USER_AGENTS;

    $now = new DateTime();
    $startDate = (new DateTime())->modify("-{$period}");

    $secret = Craft::$app->getConfig()->getGeneral()->securityKey;

    for ($i = 0; $i < $count; $i++) {
        $fakeIp = rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
        if ($fixedIpAddress) $fakeIp = '178.119.216.167';

        $ipAddressHash = hash_hmac('sha256', $fakeIp, $secret);
        $shortHash = substr($ipAddressHash, 0, 10);

        $randomTimestamp = mt_rand($startDate->getTimestamp(), $now->getTimestamp());
        $consentTimestamp = (new DateTime())->setTimestamp($randomTimestamp);

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
                'essentialCookies' => true,
                'functionalCookies' => (bool)rand(0, 1),
                'analyticalCookies' => (bool)rand(0, 1),
                'advertisementCookies' => (bool)rand(0, 1),
                'personalizationCookies' => (bool)rand(0, 1),
                'consentMethod' => 'Seeder script',
                'bannerVersion' => 'v' . rand(1, 2) . '.' . rand(0, 9) . '.' . rand(0, 99),
                'privacyPolicyVersion' => 'v' . rand(1, 3) . '.' . rand(0, 20) . '.' . rand(0, 99),
                'cookiePolicyVersion' => 'v' . rand(1, 2) . '.' . rand(0, 15) . '.' . rand(0, 99),
            ]);

        $progress = intval((($i + 1) / $count) * 100);
        $this->stdout("\rInserting: {$progress}% (" . ($i + 1) . "/{$count})");
    }

    $this->stdout(PHP_EOL . "Done seeding {$count} fake consent-record(s)." . PHP_EOL, Console::FG_GREEN);

    return ExitCode::OK;
}
}
