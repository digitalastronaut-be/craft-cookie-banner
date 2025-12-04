<?php

namespace digitalastronaut\craftcookiebanner\helpers;

abstract class CookieBanner {
    public const BASE_CONTENT = [
        'title' => 'This website uses cookies',
        'text' => 'This websites uses cookies to provide you with the most optimal user experience. Want to know more? Read our cookie policy via the "Learn more" button or determine your own preferences below.',
        'privacyPolicyLinkLabel' => 'Privacy policy',
        'cookiePolicyLinkLabel' => 'Cookie policy',
        'essentialCookiesTitle' => 'Essential cookies',
        'essentialCookiesDefinition' => 'Cookies that are necessary for the normal functioning of this website. These cannot be deactivated.',
        'functionalCookiesTitle' => 'Functional cookies',
        'functionalCookiesDefinition' => 'Cookies that improve the user-friendliness of this website, such as storing language preferences.',
        'analyticalCookiesTitle' => 'Statistical cookies',
        'analyticalCookiesDefinition' => 'Cookies used to enable analyses of surfing behaviour on this website so that the website can be improved.',
        'advertisementCookiesTitle' => 'Marketing cookies',
        'advertisementCookiesDefinition' => 'Cookies that make it possible to display personalized advertisements based on surfing behavior on this website.',
        'personalizationCookiesTitle' => 'Social media cookies',
        'personalizationCookiesDefinition' => 'Cookies that are necessary for the integration of social media plug-ins with this website, such as YouTube.',
        'acceptAllButtonLabel' => 'Accept all',
        'acceptSelectedButtonLabel' => 'Accept selected',
        'refuseAllButtonLabel' => 'Refuse all',
        'determinePreferencesButtonLabel' => 'Determine preferences',
        'detailedPreferencesButtonLabel' => 'Detailed preferences',
        'cookieGroups' => [
            [
                'name' => 'Craft CMS',
                'description' => 'A bespoke content management system build for devs and authors', 
                'url' => 'https://craftcms.com/',
            ],
            [
                'name' => 'Cookie banner',
                'description' => 'This banner manages your cookie preferences and is compliant with the latest GDPR regulations.', 
                'url' => 'https://craftcms.com/',
            ],
        ],
        'essentialCookies' => [
            [
                'name' => 'CRAFT_CSRF_TOKEN',
                'group' => 'craft-cms',
                'purpose' => 'Security token used to prevent cross-site request forgery (CSRF) attacks. Ensures that form submissions and requests are coming from trusted sources.',
                'expiration' => 'Session',
            ],
            [
                'name' => 'CraftSessionId',
                'group' => 'craft-cms',
                'purpose' => 'Identifies a unique session between the browser and the server. Required for basic site functionality such as logins and form submissions.',
                'expiration' => 'Session',
            ],
            [
                'name' => 'cookie_consent',
                'group' => 'cookie-banner',
                'purpose' => "Stores the user's cookie preferences (e.g. accepted or refused categories) so that the banner does not reappear on every visit.",
                'expiration' => '1 year',
            ]
        ],
    ];
}