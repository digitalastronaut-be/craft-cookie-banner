<?php 

namespace digitalastronaut\craftcookiebanner\variables;

use Craft;
use yii\base\Behavior;
use digitalastronaut\craftcookiebanner\elements\ConsentRecord;
use digitalastronaut\craftcookiebanner\elements\db\ConsentRecordQuery;

class CookieBannerVariable extends Behavior {
    public function consentRecords(array $criteria = []): ConsentRecordQuery {
        return Craft::configure(ConsentRecord::find(), $criteria);
    }
}