<?php

namespace digitalastronaut\craftcookiebanner\models;

use Craft;
use craft\base\Model;

/**
 * Cookie banner settings
 */
class Settings extends Model {
    public bool $cookieBannerEnabled = true;
    public string $cookieBannerVersion = 'v1.0.0';
    public string $privacyPolicyVersion = 'v1.4.7';
    public string $cookiePolicyVersion = 'v1.3.1';

    public string $cookieBannerPosition = 'bottom-left';
    public string $preferencesAction = 'button';
    public bool $showCookieCategoriesPreview = false;
    public bool $showCookieTables = true;
    public string $titleFont = '"system-ui", sans-serif';
    public string $textFont = '"system-ui", sans-serif';

    public string $bannerBackground = 'ffffff';
    public string $bannerBorderColor = 'ffffff';
    public string $titleColor = '3F4d5a';
    public string $textColor = '596673';
    public string $linkColor = '2563eb';
    public string $linkHoverColor = '123a97';
    public int $bannerBorderThickness = 0;
    public int $bannerOverlayOpacity = 0;

    public string $buttonBackground = 'd9e0e8';
    public string $buttonColor = '3F4d5a';
    public string $buttonBorderColor = 'd9e0e8';
    public string $buttonHoverBackground = '3F4d5a';
    public string $buttonHoverColor = 'ffffff';
    public string $buttonHoverBorderColor = '404d5a';
    public int $buttonBorderThickness = 0;

    public string $toggleBackgroundOff = 'e5e7eb';
    public string $toggleBackgroundOn = '009c8c';
    public string $toggleColor = 'ffffff';

    public string $cookieGroupTitleColor = '3F4d5a';
    public string $cookieGroupTextColor = '596673';
    public string $cookieGroupLinkColor = '2663eb';
    public string $cookieGroupLinkHoverColor = '143a97';
    public string $cookieGroupBackground = 'f3f7fb';
    public string $cookieGroupBorderColor = 'e5e7eb';
    public string $cookieGroupHoverBackground = 'f3f7fb';
    public string $cookieGroupHoverBorderColor = 'afb6bf';
    public int $cookieGroupBorderThickness = 1;

    public string $cookieTableTitleColor = '3F4d5a';
    public string $cookieTableTextColor = '596673';
    public string $cookieTableBackground = 'ffffff';
    public string $cookieTableBorderColor = 'e5e7eb';
    public int $cookieTableBorderThickness = 1;

    public string $buttonSize = 'small';
    public string $bannerStyle = 'square';


}
