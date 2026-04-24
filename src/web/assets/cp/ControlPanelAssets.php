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

namespace digitalastronaut\craftcookiebanner\web\assets\cp;

use craft\web\AssetBundle;

/**
 * Class CookieBannerAssets
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 */
class ControlPanelAssets extends AssetBundle {
    /**
     * @return void
     */
    public function init(): void {
        $this->sourcePath = "@digitalastronaut/craftcookiebanner/web/assets/cp/dist";
        
        $this->css = ['index.css'];
        $this->js = ['index.js'];
        
        parent::init();
    }
}