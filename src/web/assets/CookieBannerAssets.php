<?php

namespace digitalastronaut\craftcookiebanner\web\assets;

use craft\web\AssetBundle;

class CookieBannerAssets extends AssetBundle {
    
    public function init(): void {
        $this->sourcePath = "@digitalastronaut/craftcookiebanner/web/assets/dist";
        
        $this->css = ['index.css'];
        $this->js = ['index.js'];
        
        parent::init();
    }
}