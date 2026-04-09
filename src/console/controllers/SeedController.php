<?php

namespace digitalastronaut\craftcookiebanner\console\controllers;

use DateTime;

use Craft;
use craft\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

use digitalastronaut\craftcookiebanner\elements\ConsentRecord;

class SeedController extends Controller {
    public $defaultAction = 'index';

    public function actionIndex(): void {
        $this->stdout("Use this controller to seed data for cookie banner related tables" . PHP_EOL, Console::FG_CYAN);
    }

    public function actionConsentRecords(int $count = 5, bool $fixedIpAddress = false): int {
        $this->stdout("Preparing fake consent records..." . PHP_EOL, Console::FG_CYAN);

        $languages = ['en-US', 'de-DE', 'fr-FR', 'es-ES', 'nl-NL'];
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
            'Mozilla/5.0 (X11; Linux x86_64)',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X)',
            'Mozilla/5.0 (Android 11; Mobile; rv:90.0)',
        ];
        $actions = ['Accept all', 'Accept selected', 'Refuse all'];

        for ($i = 0; $i < $count; $i++) {
            $consentRecord = new ConsentRecord();

            $fakeIp = rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
            if ($fixedIpAddress) $fakeIp = '178.119.216.167';

            $secret = Craft::$app->getConfig()->getGeneral()->securityKey;
            $ipAddressHash = hash_hmac('sha256', $fakeIp, $secret);
            $shortHash = substr($ipAddressHash, 0, 10);

            $now = new DateTime();
            $oneMonthAgo = (new DateTime())->modify('-30 days');
            $randomTimestamp = mt_rand($oneMonthAgo->getTimestamp(), $now->getTimestamp());
            $consentTimestamp = (new DateTime())->setTimestamp($randomTimestamp);

            $consentRecord->title = "Consent {$shortHash}";
            $consentRecord->ipAddressHash = $ipAddressHash;
            $consentRecord->sessionId = uniqid("", true);
            $consentRecord->userAgent = $userAgents[array_rand($userAgents)];
            $consentRecord->language = $languages[array_rand($languages)];
            $consentRecord->consentTimestamp = $consentTimestamp;
            $consentRecord->consentExpiry = (clone $consentTimestamp)->modify('+12 months');
            $consentRecord->consentAction = $actions[array_rand($actions)];
            $consentRecord->essentialCookies = true;
            $consentRecord->functionalCookies = (bool)rand(0, 1);
            $consentRecord->analyticalCookies = (bool)rand(0, 1);
            $consentRecord->advertisementCookies = (bool)rand(0, 1);
            $consentRecord->personalizationCookies = (bool)rand(0, 1);
            $consentRecord->consentMethod = 'Seeder script';
            $consentRecord->bannerVersion = 'v' . rand(1, 2) . '.' . rand(0, 9) . '.' . rand(0, 99);
            $consentRecord->privacyPolicyVersion = 'v' . rand(1, 3) . '.' . rand(0, 20) . '.' . rand(0, 99);
            $consentRecord->cookiePolicyVersion = 'v' . rand(1, 2) . '.' . rand(0, 15) . '.' . rand(0, 99);

            Craft::$app->elements->saveElement($consentRecord);

            // progress output
            $progress = intval((($i + 1) / $count) * 100);
            $this->stdout("\rInserting: {$progress}% (" . ($i + 1) . "/{$count})");
        }

        $this->stdout(PHP_EOL . "Done seeding {$count} fake consent-record(s)." . PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }
}
