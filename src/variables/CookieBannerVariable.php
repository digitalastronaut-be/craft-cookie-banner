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

namespace digitalastronaut\craftcookiebanner\variables;

use Craft;
use yii\base\Behavior;
use digitalastronaut\craftcookiebanner\elements\ConsentRecord;
use digitalastronaut\craftcookiebanner\elements\db\ConsentRecordQuery;

/**
 * Class CookieBannerVariable
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 */
class CookieBannerVariable extends Behavior {
    /**
     * @param array $criteria
     * @return ConsentRecordQuery
     */
    public function consentRecords(array $criteria = []): ConsentRecordQuery {
        return Craft::configure(ConsentRecord::find(), $criteria);
    }
}