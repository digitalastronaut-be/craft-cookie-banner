<?php

namespace digitalastronaut\craftcookiebanner\helpers;

abstract class CookieBanner {
    public const BASE_CONTENT = [
        'title' => 'This website uses cookies',
        'text' => 'We use cookies to support the basic operation of the site, understand how it is used, and enable certain features. Some information about site usage may be shared with third-party services that help with analytics, advertising, and social media functionality, in accordance with their respective policies.',
        'privacyPolicyLinkLabel' => 'Privacy policy',
        'privacyPolicyLinkURL' => '',
        'cookiePolicyLinkLabel' => 'Cookie policy',
        'cookiePolicyLinkURL' => '',
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
        'necessaryCookiesTitle' => 'Necessary cookies',
        'necessaryCookiesLabel' => 'Necessary',
        'necessaryCookiesDefinition' => 'Necessary cookies help make a website usable by enabling basic functions like page navigation and access to secure areas of the website. The website cannot function properly without these cookies.',
        'necessaryCookies' => [
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
        ],
        'preferenceCookiesTitle' => 'Preference cookies',
        'preferenceCookiesLabel' => 'Preference',
        'preferenceCookiesDefinition' => 'Preference cookies enable a website to remember information that changes the way the website behaves or looks, like your preferred language or the region that you are in.',
        'preferenceCookies' => [
            [
                'name' => 'cookie_consent',
                'group' => 'cookie-banner',
                'purpose' => "Stores the user's cookie preferences (e.g. accepted or refused categories) so that the banner does not reappear on every visit.",
                'expiration' => '1 year',
            ]
        ],
        'analyticalCookiesTitle' => 'Analytical cookies',
        'analyticalCookiesLabel' => 'Analytical',
        'analyticalCookiesDefinition' => 'Statistic cookies help website owners to understand how visitors interact with websites by collecting and reporting information anonymously.',
        'analyticalCookies' => [],
        'marketingCookiesTitle' => 'Marketing cookies',
        'marketingCookiesLabel' => 'Marketing',
        'marketingCookiesDefinition' => 'Marketing cookies are used to track visitors across websites. The intention is to display ads that are relevant and engaging for the individual user and thereby more valuable for publishers and third party advertisers.',
        'marketingCookies' => [],
        'uncategorizedCookiesTitle' => 'Uncategorized cookies',
        'uncategorizedCookiesLabel' => 'Uncategorized',
        'uncategorizedCookiesDefinition' => 'Uncategorized cookies are cookies that we are in the process of classifying, together with the providers of individual cookies.',
        'uncategorizedCookies' => [],
        'acceptAllButtonLabel' => 'Accept all',
        'acceptSelectedButtonLabel' => 'Accept selected',
        'refuseAllButtonLabel' => 'Refuse all',
        'determinePreferencesButtonLabel' => 'Determine preferences',
        'detailedPreferencesButtonLabel' => 'Detailed preferences',
    ];
}