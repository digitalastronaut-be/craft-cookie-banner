<?php

namespace digitalastronaut\craftcookiebanner\controllers;

use Craft;
use craft\web\Controller;
use craft\web\Response;
use craft\helpers\Json;

use DateTime;

use digitalastronaut\craftcookiebanner\elements\ConsentRecord;
use yii\web\BadRequestHttpException;

class ConsentRecordsController extends Controller {
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = true;

    public function actionIndex(): Response {
        return $this->redirect("cookie-banner/consent-records");
    }

    public function actionView(): Response {
        $consentRecordsCount = ConsentRecord::find()->count();

        $acceptedEssentialCookiesCount = ConsentRecord::find()->where(["essentialCookies" => true])->count(); 
        $acceptedFunctionalCookiesCount = ConsentRecord::find()->where(["functionalCookies" => true])->count(); 
        $acceptedAnalyticalCookiesCount = ConsentRecord::find()->where(["analyticalCookies" => true])->count(); 
        $acceptedAdvertisementCookiesCount = ConsentRecord::find()->where(["advertisementCookies" => true])->count(); 
        $acceptedPersonalizationCookiesCount = ConsentRecord::find()->where(["personalizationCookies" => true])->count(); 

        if ($consentRecordsCount === 0) {
            return $this->renderTemplate('cookie-banner/_consentRecords.twig', [
                "consentStatistics" => [
                    "acceptedEssentialCookiesPercentage" => 0,
                    "acceptedFunctionalCookiesPercentage" => 0,
                    "acceptedAnalyticsCookiesPercentage" => 0,
                    "acceptedAdvertisementCookiesPercentage" => 0,
                    "acceptedPersonalizationCookiesPercentage" => 0,
                ]
            ]);
        }

        return $this->renderTemplate('cookie-banner/_consentRecords.twig', [
            "consentStatistics" => [
                "acceptedEssentialCookiesPercentage" => round((($acceptedEssentialCookiesCount / $consentRecordsCount) * 100), 1),
                "acceptedFunctionalCookiesPercentage" => round((($acceptedFunctionalCookiesCount / $consentRecordsCount) * 100), 1),
                "acceptedAnalyticsCookiesPercentage" => round((($acceptedAnalyticalCookiesCount / $consentRecordsCount) * 100), 1),
                "acceptedAdvertisementCookiesPercentage" => round((($acceptedAdvertisementCookiesCount / $consentRecordsCount) * 100), 1),
                "acceptedPersonalizationCookiesPercentage" => round((($acceptedPersonalizationCookiesCount / $consentRecordsCount) * 100), 1),
            ]
        ]);
    }

    public function actionEdit(int $id): Response {
        $element = ConsentRecord::find()->id($id)->one();
        $matchingConsentRecords = ConsentRecord::find()->ipAddressHash($element->ipAddressHash)->all();

        if (!$element) throw new BadRequestHttpException('No element was identified by the request.');
        
        return $this->renderTemplate('cookie-banner/_consentRecord.twig', [
            "element" => $element,
            "matchingConsentRecords" => $matchingConsentRecords,
        ]);
    }

    public function actionCreate(): Response {
        $body = $this->request->bodyParams;

        $consentRecord = new ConsentRecord();

        $secret = Craft::$app->getConfig()->getGeneral()->securityKey;
        $ipAddress = $this->request->getHeaders()['x-forwarded-for'] ?? "Unknown";
        $ipAddressHash = hash_hmac('sha256', $ipAddress, $secret);
        $shortHash = substr($ipAddressHash, 0, 10);

        $consentRecord->title = "Consent {$shortHash}";
        $consentRecord->ipAddressHash = $ipAddressHash;
        $consentRecord->sessionId = Craft::$app->getSession()->getId();
        $consentRecord->userAgent = $this->request->getHeaders()['User-agent'] ?? "Unknown";
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
        $consentRecord->bannerVersion = 'v1.0.0';
        $consentRecord->privacyPolicyVersion = 'v2.13.11';
        $consentRecord->cookiePolicyVersion = 'v1.7.24';

        $succes = Craft::$app->elements->saveElement($consentRecord);

        return $this->asJson(["succes" => true]);
    }
}
