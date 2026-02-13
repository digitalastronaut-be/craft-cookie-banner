<?php

namespace digitalastronaut\craftcookiebanner\services;

use Craft;
use craft\base\Component;

use Carbon\Carbon;
use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\elements\ConsentRecord;

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