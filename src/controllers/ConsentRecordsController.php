<?php

namespace digitalastronaut\craftcookiebanner\controllers;

use Craft;
use craft\web\Controller;
use craft\web\Response;

use Carbon\Carbon;

use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\elements\ConsentRecord;

use yii\web\BadRequestHttpException;

use digitalastronaut\craftcookiebanner\jobs\PurgeExpiredConsentRecords;

use Throwable;

class ConsentRecordsController extends Controller {
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = ['create'];

    public function actionIndex(): Response {
        return $this->redirect("cookie-banner/consent-records");
    }

    public function actionView(): Response {
        $this->requirePermission("cookie-banner:access-consent-records");
        
        return $this->renderTemplate('cookie-banner/pages/_consentRecords.twig', [
            "consentStatistics" => ConsentRecord::find()->categoryAcceptancePercentages(),
        ]);
    }
            
    public function actionEdit(int $id): Response {
        $this->requirePermission("cookie-banner:access-consent-records");

        $element = ConsentRecord::find()->id($id)->one();
        
        if (!$element) throw new BadRequestHttpException('No element was identified by the request.');

        $matchingConsentRecords = ConsentRecord::find()->ipAddressHash($element->ipAddressHash)->all();
        
        return $this->renderTemplate('cookie-banner/pages/_consentRecord.twig', [
            "element" => $element,
            "matchingConsentRecords" => $matchingConsentRecords,
        ]);
    }

    public function actionCreate(): Response {
        $settings = CookieBanner::getInstance()->getSettings();
        $secret = Craft::$app->getConfig()->getGeneral()->securityKey;
        $sessionId = Craft::$app->getSession()->getId();

        $this->requirePostRequest();

        try {
            $body = $this->request->bodyParams;
            $userAgent = $this->request->getHeaders()->get('User-agent') ?? "Unknown";
            $ipAddress = $this->request->getHeaders()['x-forwarded-for'] ?? "Unknown";

            if (
                !$body['consentAction'] &&
                !$body['consentCategories'] &&
                !$body['language']
            ) throw new BadRequestHttpException('Missing body params');

            $ipAddressHash = hash_hmac('sha256', $ipAddress, $secret);
            $shortHash = substr($ipAddressHash, 0, 10);

            CookieBanner::getInstance()
                ->getConsentRecords()
                ->createConsentRecord([
                    'title' => "Consent {$shortHash}",
                    'ipAddressHash' => $ipAddressHash,
                    'sessionId' => $sessionId,
                    'userAgent' => $userAgent,
                    'language' => $body['language'],
                    'consentAction' => $body['consentAction'],
                    'necessaryCookies' => $body['consentCategories']['necessaryCookies'],
                    'preferenceCookies' => $body['consentCategories']['preferenceCookies'],
                    'analyticalCookies' => $body['consentCategories']['analyticalCookies'],
                    'marketingCookies' => $body['consentCategories']['marketingCookies'],
                    'uncategorizedCookies' => $body['consentCategories']['uncategorizedCookies'],
                    'consentTimestamp' => new Carbon(),
                    'consentMethod' => 'Cookie banner',
                    'bannerVersion' => $settings->bannerVersion,
                    'privacyPolicyVersion' => $settings->privacyPolicyVersion,
                    'cookiePolicyVersion' => $settings->cookiePolicyVersion,
                ]);

            return $this->asJson(["succes" => true]);
            
        } catch(Throwable $error) {
            Craft::error($error->getMessage(), __METHOD__);

            $this->response->statusCode = 500;
            return $this->asJson([
                "success" => false,
                "error" => "Something went wrong",
            ]);
        }
    }

    public function actionGetChartData(): Response {
        $this->requirePermission("cookie-banner:access-consent-records");

        $data = CookieBanner::getInstance()->getConsentRecords()->getChartData();

        return $this->asJson($data);
    }
}
