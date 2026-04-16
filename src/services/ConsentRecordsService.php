<?php

namespace digitalastronaut\craftcookiebanner\services;

use Craft;
use craft\base\Component;

use yii\base\Exception;

use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\helpers\Table;
use digitalastronaut\craftcookiebanner\elements\ConsentRecord;

use yii\db\Expression;
use yii\db\Query;

use DateTime;

use Carbon\Carbon;

class ConsentRecordsService extends Component {
    public function cleanup() {
        if (!Craft::$app->db->tableExists(Table::COOKIE_BANNER_CONSENT_RECORDS)) return;

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

    public function getChartData(): array {
        $rows = (new Query())
            ->select([
                'date' => new Expression('DATE(cr.consentTimestamp)'),
                'count' => new Expression('COUNT(*)'),
                'accepted' => new Expression("
                    SUM(
                        cr.essentialCookies = 1 AND
                        cr.functionalCookies = 1 AND
                        cr.analyticalCookies = 1 AND
                        cr.advertisementCookies = 1 AND
                        cr.personalizationCookies = 1
                    )
                "),
            ])
            ->from(['cr' => Table::COOKIE_BANNER_CONSENT_RECORDS])
            ->innerJoin(['elements' => '{{%elements}}'], 'elements.id = cr.id')
            ->where([
                '>=',
                'cr.consentTimestamp',
                new Expression('DATE_SUB(CURDATE(), INTERVAL 60 DAY)')
            ])
            ->groupBy(new Expression('DATE(cr.consentTimestamp)'))
            ->orderBy(['date' => SORT_ASC])
            ->all();

        $indexed = [];

        foreach ($rows as $row) {
            $indexed[$row['date']] = [
                'count' => (int)$row['count'],
                'accepted' => (int)$row['accepted'],
            ];
        }

        $data = [];
        $today = new DateTime();

        for ($i = 30; $i >= 0; $i--) {
            $date = (clone $today)->modify("-{$i} days")->format('Y-m-d');

            $data[] = [
                'date' => $date,
                'count' => $indexed[$date]['count'] ?? 0,
                'accepted' => $indexed[$date]['accepted'] ?? 0,
            ];
        }

        $total = 0;
        $totalAccepted = 0;

        foreach ($data as $day) {
            $total += $day['count'];
            $totalAccepted += $day['accepted'];
        }

        $days = count($data);

        $dailyAverage = $days > 0 ? $total / $days : 0;
        $acceptanceRate = $total > 0 ? $totalAccepted / $total : 0;

        return [
            'data' => $data,

            'metrics' => [
                'total' => $total,
                'dailyAverage' => round($dailyAverage, 2),
                'acceptanceRate' => round($acceptanceRate * 100, 2),
            ],
        ];
    }
}