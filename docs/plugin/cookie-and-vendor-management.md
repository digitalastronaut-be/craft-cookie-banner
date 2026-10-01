# Cookie & vendor management

**Cookie banner → Cookies and vendors** is where you get an overview of every cookie and vendor the plugin knows about across your project, cross-checked against a bundled database of 4000+ cookies and vendors (translated into NL, EN, ES, IT, FR, DE and PL).

## Automated detection and suggestions

The plugin hooks into `View::EVENT_AFTER_RENDER_PAGE_TEMPLATE` on every front-end request and records which cookie names actually show up in `$_COOKIE` for your site (`CookieDetectionService::detectBrowserCookies()`), caching them under the `detectedBrowserCookies` cache key. This is how the overview can show you cookies that are **actually being set in visitors' browsers** but aren't yet defined in your banner content — a common source of GDPR compliance gaps (e.g. a marketing script silently dropping a tracking cookie you forgot to disclose).

For every cookie name — whether it's one you've already defined, or one detected in the browser but not yet defined — the plugin tries to match it against the bundled database using wildcard patterns (e.g. `_ga_*`, `__Secure-*`), via `CookieDetectionService::cookieNameMatches()`. A match means the plugin can suggest a purpose, retention period and vendor automatically, in whichever of the 7 supported languages matches the site, falling back to English.

Cookies matching Craft's own control-panel cookies (`Craft-*`, `*_identity`, `*_username`) are recognised and excluded from compliance warnings — they're not something your visitors need to consent to since they only affect logged-in CP users, not your front-end visitors.

Vendors are suggested the same way: whenever a defined cookie matches a known vendor in the database, that vendor is suggested automatically if it isn't already in the site's `vendors` list.

## The "issues" badge

The control panel nav item shows a badge count (cached for 5 minutes under `cookie-banner:issues-count`) counting every cookie or vendor across **all sites** that is either:

- not defined at all for a given site, or
- defined but missing a purpose/expiration (cookies) or URL/description (vendor), or
- not defined for one site while it is for another (multi-site inconsistency)

Disabled or blacklisted entries don't count toward this number. The badge is invalidated automatically whenever you create, edit, delete or blacklist a cookie/vendor.

## Blacklisting

If a detected browser cookie should never be flagged as an issue (e.g. a third-party widget's internal cookie you deliberately choose not to disclose, or noise you don't want cluttering the overview), blacklist it. Blacklisted names are stored on the plugin's settings (`blacklistedCookies` / `blacklistedVendors`) and are excluded from both the overview and the issues count. This is not reversible from the UI by design — if you need to un-blacklist something, edit the plugin settings directly.

## Creating, editing and removing cookies/vendors

Cookies and vendors are always managed **per cookie/vendor name, across every site at once** — when you create or edit one from the Cookies & vendors page, the same name/purpose/expiration structure is written into every site's `Content` record in a single database transaction (all-or-nothing). This keeps your sites consistent and avoids one site disclosing a cookie that another silently omits.

Use **Bulk create** to add several cookies/vendors from the database in one go — handy when first setting up the plugin (e.g. searching for "google" and importing every matching cookie and vendor at once).

## Searching the bundled database

The database is indexed with a fuzzy search (`loilo/fuse`, a PHP port of Fuse.js) so partial or slightly misspelled names still match. This powers both the control panel's search inputs and two Twig functions you can reuse in your own templates or custom tooling:

```twig
{% set matches = searchCookieDatabase("google analytics") %}
{% set vendorMatches = searchVendorDatabase("hubspot") %}
```

Both return up to 100 fuzzy-matched entries from the bundled database (not from your site's own configured cookies).

## Rendering a cookie table on your own pages

Besides the ones above, the plugin registers a handful of global Twig functions (via `CookieBannerTwigExtension`) that are useful if you're building a standalone cookie/privacy policy page rather than relying solely on the banner's own detailed view:

```twig
{# Full categorised cookie table for the current site, respecting the
   "Cookie list style" appearance setting (grouped-by-category vs one flat table) #}
{{ renderCookiesListHtml() }}

{# Raw overview data if you want to build your own markup instead #}
{% set cookies = getCookieDetectionOverviewData() %}
{% set vendors = getVendorOverview() %}

{# Check completeness per-site for a specific cookie/vendor, e.g. to flag
   it in an internal audit page #}
{% set status = checkCookieDefinitionForEachSite("_ga") %}
{# => { "English (en-us)": "defined", "Dutch (nl-nl)": "defined-incomplete" } #}

{% set vendorStatus = checkVendorDefinitionForEachSite("Google Analytics") %}
```

`checkCookieDefinitionForEachSite()` / `checkVendorDefinitionForEachSite()` return one of `"defined"`, `"defined-incomplete"`, `"disabled"` or `"not-defined"` per site — the same logic that drives the issues badge, available for your own compliance dashboards or CI checks.

::: tip
`renderCookiesListHtml()` always renders for the **currently active site** (`Craft::$app->getSites()->getCurrentSite()`), so place it on each localized version of your cookie policy page rather than a single shared template.
:::
