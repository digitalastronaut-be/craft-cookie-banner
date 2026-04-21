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

namespace digitalastronaut\craftcookiebanner\jobs;

use Craft;
use craft\queue\BaseJob;

use Carbon\Carbon;
use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\elements\ConsentRecord;

/**
 * Class PurgeExpiredConsentRecords
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 */
class PurgeExpiredConsentRecords extends BaseJob {
    /**
     * @param mixed $queue
     * @return void
     */
    public function execute($queue): void {
        $expiredDate = CookieBanner::getInstance()->getConsentRecords()->getExpiredDate();

        $records = ConsentRecord::find()
            ->consentTimestampBetween(Carbon::now()->subMillennium(), $expiredDate)
            ->ids();

        $total = \count($records);
        
        foreach ($records as $index => $id) {
            Craft::$app->elements->deleteElementById($id, ConsentRecord::class, true);
            $this->setProgress($queue, ($index + 1) / $total);
        }
    }

    /**
     * @return string
     */
    protected function defaultDescription(): ?string {
        return Craft::t('cookie-banner', 'Purging expired consent records');
    }
}
