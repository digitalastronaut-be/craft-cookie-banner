# Installation and setup

## Requirements

- Craft CMS 5.8.0 or later
- PHP 8.2 or later

## Installing with Composer

::: code-group

```bash [DDEV]
ddev composer require digitalastronaut/craft-cookie-banner
ddev craft plugin/install cookie-banner
```

```bash [Other]
composer require digitalastronaut/craft-cookie-banner
php craft plugin/install cookie-banner
```

:::

## Installing via the Craft plugin store

Alternatively you can install the plugin via the [Craft plugin store](https://plugins.craftcms.com/cookie-banner?craft5). Search for "Cookie banner" and press **Install**.

## What happens on install

Once installed, the plugin:

- Adds a **Cookie banner** section to the control panel nav (`cookie-banner`), with sub-pages for the dashboard, cookies & vendors, consent records, content, appearance and settings.
- Creates a `Content` and `Appearance` record for every existing site, seeded from the bundled language files in `src/static/content/<language>.json` (falling back to `default.json` when your site's language isn't translated yet).
- Registers a new `ConsentRecord` element type used to store individual consent decisions.
- Automatically creates a `Content` and `Appearance` record whenever a **new site** is added to your Craft install (`Sites::EVENT_AFTER_SAVE_SITE`), and removes them when a site is deleted — multi-site support requires no extra setup.

By default the banner is **enabled** (`cookieBannerEnabled` in the plugin settings) and will start rendering immediately on every front-end page once content has been filled in from the control panel.

## How the banner gets on the page

You do **not** need to add a Twig tag or include anywhere in your templates. The plugin hooks into `craft\web\View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE` and injects the banner markup at the end of the `<body>` (`View::POS_END`) of every site request, using the `Content` and `Appearance` records for the currently active site.

If you need to disable this for a specific environment, toggle **Settings → Cookie banner enabled** in the control panel, or set it programmatically:

```php
use digitalastronaut\craftcookiebanner\CookieBanner;

$settings = CookieBanner::getInstance()->getSettings();
$settings->cookieBannerEnabled = false;

Craft::$app->plugins->savePluginSettings(CookieBanner::getInstance(), $settings->toArray());
```

## Required setup step: Google Consent Mode V2

::: danger Required for Google Consent Mode V2 to work
The cookie banner calls `gtag('consent', 'update', …)` every time a visitor interacts with the banner (on load, accept all, accept selected, refuse all). For that call to succeed and for Consent Mode V2 to behave correctly on the **first page load**, Google requires a `gtag('consent', 'default', …)` call to run **before** Google Tag Manager (or gtag.js) loads.

The plugin cannot inject this snippet for you. Craft plugins have no guaranteed way to control the relative order of `<head>` output across other plugins/modules that also add scripts (analytics suites, tag managers, SEO plugins, etc.), so forcing this into the `<head>` automatically could just as easily end up **after** GTM on some installs — which defeats the purpose and can cause consent signals to be ignored or, worse, cookies to be dropped before consent is known. For this reason you must add it yourself, as high up in `<head>` as possible, manually, above your Google Tag Manager snippet.
:::

Add the following snippet manually, directly above your Google Tag Manager `<script>` tag, in your base layout template (e.g. `templates/_layouts/base.twig`):

```html
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('consent', 'default', {
        'ad_storage': 'denied',
        'ad_user_data': 'denied',
        'ad_personalization': 'denied',
        'analytics_storage': 'denied',
        'security_storage': 'denied',
        'personalization_storage': 'denied',
        'functionality_storage': 'denied',
    });
</script>

<!-- Google Tag Manager snippet goes here, AFTER the snippet above -->
```

A few important notes:

- This must load on **every page**, not just pages that show the banner, so the default-denied state is always in place before any tag fires.
- The cookie banner's front-end script relies on the global `gtag()` function existing. If this snippet (or your own GTM setup that defines it) isn't present, the banner's JavaScript will throw an error as soon as a visitor interacts with it, because it unconditionally calls `gtag('consent', 'update', …)` and `gtag('event', 'cookie-banner', …)`.
- Once a returning visitor already has a stored consent decision, the banner calls `gtag('consent', 'update', …)` again as soon as it mounts, so Consent Mode state is restored on every page load without the banner needing to be visible.
- See [Developer integration](/plugin/developer-integration) for the full mapping between cookie categories and Google's consent signal keys.

## Console commands

The plugin ships two console controllers, available via `php craft`:

```bash
# Purge consent records older than the configured retention period
php craft cookie-banner/consent-records/purge-expired

# Seed fake consent records for local development/demoing
php craft cookie-banner/seed/consent-records [count=5] [fixedIpAddress=false] [period="30 days"]
```

## Permissions

Access to each section of the plugin is gated by its own permission, assignable per user group under **Settings → Users → \<group\> → Cookie banner**:

| Permission | Purpose |
| --- | --- |
| `cookie-banner:access-dashboard` | View the dashboard (stats, getting-started guide) |
| `cookie-banner:update-guide-progress` | Mark getting-started steps as complete/skip the guide |
| `cookie-banner:access-cookies-and-vendors` | View the cookies & vendors overview |
| `cookie-banner:add-cookies` / `edit-cookies` / `remove-cookies` / `blacklist-cookies` | Manage cookie definitions |
| `cookie-banner:add-vendors` / `edit-vendors` / `remove-vendors` / `blacklist-vendors` | Manage vendor definitions |
| `cookie-banner:access-content` / `edit-content` | View/edit banner text, labels and cookie/vendor assignment per site |
| `cookie-banner:access-appearance` / `edit-appearance` | View/edit banner styling per site |
| `cookie-banner:access-consent-records` | View logged consent decisions |
| `cookie-banner:access-settings` / `edit-settings` | View/edit plugin settings (retention, blacklist, enabled state) |

## Next steps

1. [Fill in your banner content](/plugin/banner-content) (title, text, categories, cookies, vendors) for each site.
2. [Review detected and suggested cookies & vendors](/plugin/cookie-and-vendor-management).
3. [Style the banner](/plugin/appearance-and-styling) to match your brand.
4. [Wire up JavaScript gating for embeds/scripts](/plugin/developer-integration) that depend on consent.
