<?php

namespace digitalastronaut\craftcookiebanner\services;

use Craft;
use craft\base\Component;

use yii\base\Exception;

use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\elements\ConsentRecord;

use Carbon\Carbon;

class ConsentRecordsService extends Component {
    public function cleanup() {
        $expiredDate = $this->getExpiredDate();

        $records = ConsentRecord::find()
            ->where(['<', 'cookie_banner_consent_records.consentTimestamp', $expiredDate->toDateTimeString()])
            ->all();
        
        foreach ($records as $record) {
            Craft::$app->elements->deleteElement($record, true);
        }
    }

    public function createConsentRecord($consentRecord) {
        try {
            
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function getCategorizedConsentRecordStats(): array {
        $consentRecordsCount = ConsentRecord::find()->count();

        $acceptedEssentialCookiesCount = ConsentRecord::find()->where(["essentialCookies" => true])->count(); 
        $acceptedFunctionalCookiesCount = ConsentRecord::find()->where(["functionalCookies" => true])->count(); 
        $acceptedAnalyticalCookiesCount = ConsentRecord::find()->where(["analyticalCookies" => true])->count(); 
        $acceptedAdvertisementCookiesCount = ConsentRecord::find()->where(["advertisementCookies" => true])->count(); 
        $acceptedPersonalizationCookiesCount = ConsentRecord::find()->where(["personalizationCookies" => true])->count(); 
        
        return [
            "acceptedEssentialCookiesPercentage" => 
                $acceptedFunctionalCookiesCount != 0 ? round((($acceptedEssentialCookiesCount / $consentRecordsCount) * 100), 1) : 0,
            "acceptedFunctionalCookiesPercentage" => 
                $acceptedFunctionalCookiesCount != 0 ? round((($acceptedFunctionalCookiesCount / $consentRecordsCount) * 100), 1) : 0,
            "acceptedAnalyticsCookiesPercentage" => 
                $acceptedAnalyticalCookiesCount != 0 ? round((($acceptedAnalyticalCookiesCount / $consentRecordsCount) * 100), 1) : 0,
            "acceptedAdvertisementCookiesPercentage" => 
                $acceptedAdvertisementCookiesCount != 0 ? round((($acceptedAdvertisementCookiesCount / $consentRecordsCount) * 100), 1) : 0,
            "acceptedPersonalizationCookiesPercentage" => 
                $acceptedPersonalizationCookiesCount != 0 ? round((($acceptedPersonalizationCookiesCount / $consentRecordsCount) * 100), 1) : 0,
        ];
    }

    private function getExpiredDate(): Carbon {
        $retentionKey = CookieBanner::getInstance()->getSettings()->consentRecordRetention;

        $now = Carbon::now();

        return match ($retentionKey) {
            'oneWeek'   => $now->subWeek(),
            'oneMonth'  => $now->subMonth(),
            'sixMonths' => $now->subMonths(6),
            'oneYear'   => $now->subYear(),
            'fiveYears' => $now->subYears(5),
            default     => $now->subMonths(6),
        };
    }
}