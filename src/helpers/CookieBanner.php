<?php

namespace digitalastronaut\craftcookiebanner\helpers;

abstract class CookieBanner {
    public const COOKIE_GROUPS = [
        'necessaryCookies',
        'preferenceCookies',
        'analyticalCookies',
        'marketingCookies',
        'uncategorizedCookies'
    ];

    public const CONTROL_PANEL_COOKIES = [
        "Craft-*",
        "*_identity",
        "*_username",
    ];
}