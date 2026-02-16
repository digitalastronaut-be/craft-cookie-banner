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
}