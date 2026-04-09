<?php

namespace digitalastronaut\craftcookiebanner\controllers;

use Craft;
use craft\web\Controller;
use craft\web\Response;

use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\elements\ConsentRecord;
use digitalastronaut\craftcookiebanner\helpers\Table;

use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;

use DateTime;

class ConsentRecordsController extends Controller {
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = true;

    public function actionIndex(): Response {
        return $this->redirect("cookie-banner/consent-records");
    }

    public function actionView(): Response {
        $this->requirePermission("cookie-banner:access-consent-records");
        
        return $this->renderTemplate('cookie-banner/_consentRecords.twig', [
            "consentStatistics" => CookieBanner::getInstance()->getConsentRecords()->getCategorizedConsentRecordStats(),
        ]);
    }
            
    public function actionEdit(int $id): Response {
        $this->requirePermission("cookie-banner:access-consent-records");

        $element = ConsentRecord::find()->id($id)->one();
        $matchingConsentRecords = ConsentRecord::find()->ipAddressHash($element->ipAddressHash)->all();

        if (!$element) throw new BadRequestHttpException('No element was identified by the request.');
        
        return $this->renderTemplate('cookie-banner/_consentRecord.twig', [
            "element" => $element,
            "matchingConsentRecords" => $matchingConsentRecords,
        ]);
    }

    public function actionCreate(): Response {
        $settings = CookieBanner::getInstance()->getSettings();
        $body = $this->request->bodyParams;

        $consentRecord = new ConsentRecord();

        $secret = Craft::$app->getConfig()->getGeneral()->securityKey;
        $ipAddress = $this->request->getHeaders()['x-forwarded-for'] ?? "Unknown";
        $ipAddressHash = hash_hmac('sha256', $ipAddress, $secret);
        $shortHash = substr($ipAddressHash, 0, 10);

        $consentRecord->title = "Consent {$shortHash}";
        $consentRecord->ipAddressHash = $ipAddressHash;
        $consentRecord->sessionId = Craft::$app->getSession()->getId();
        $consentRecord->userAgent = $this->request->getHeaders()->get('User-agent') ?? "Unknown";
        $consentRecord->language = $body['language'];
        $consentRecord->consentTimestamp = new DateTime();
        // TODO: remove expiry field as expired records cannot be stored
        $consentRecord->consentExpiry = (new DateTime())->modify('+12 months');
        $consentRecord->consentAction = $body['consentAction'];

        $consentRecord->essentialCookies = $body['consentCategories']['essentialCookies'];
        $consentRecord->functionalCookies = $body['consentCategories']['functionalCookies'];
        $consentRecord->analyticalCookies = $body['consentCategories']['analyticalCookies'];
        $consentRecord->advertisementCookies = $body['consentCategories']['advertisementCookies'];
        $consentRecord->personalizationCookies = $body['consentCategories']['personalizationCookies'];

        $consentRecord->consentMethod = 'Cookie banner';
        $consentRecord->bannerVersion = $settings->cookieBannerVersion;
        $consentRecord->privacyPolicyVersion = $settings->privacyPolicyVersion;
        $consentRecord->cookiePolicyVersion = $settings->cookiePolicyVersion;

        $succes = Craft::$app->elements->saveElement($consentRecord);

        return $this->asJson(["succes" => true]);
    }

    public function actionGetChartData(): Response {
        $rows = (new Query())
            ->select([
                'date' => new Expression('DATE(cr.consentTimestamp)'),
                'count' => new Expression('COUNT(*)'),
            ])
            ->from(['cr' => Table::COOKIE_BANNER_CONSENT_RECORDS])
            ->innerJoin(['elements' => '{{%elements}}'], 'elements.id = cr.id')
            ->where([
                '>=',
                'cr.consentTimestamp',
                new Expression('DATE_SUB(CURDATE(), INTERVAL 30 DAY)')
            ])
            ->groupBy(new Expression('DATE(cr.consentTimestamp)'))
            ->orderBy(['date' => SORT_ASC])
            ->all();

        $indexed = [];

        foreach ($rows as $row) {
            $indexed[$row['date']] = (int)$row['count'];
        }

        $data = [];
        $today = new DateTime();

        for ($i = 30; $i >= 0; $i--) {
            $date = (clone $today)->modify("-{$i} days")->format('Y-m-d');

            $data[] = [
                'date' => $date,
                'count' => $indexed[$date] ?? 0,
            ];
        }

        return $this->asJson(['data' => $data]);
    }
}
