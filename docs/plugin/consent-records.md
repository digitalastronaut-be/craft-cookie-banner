# Consent records

Every time a visitor accepts, refuses or saves specific preferences, the banner logs the decision as a `ConsentRecord` element (see **Cookie banner → Consent records**). This gives you an auditable trail that you actually asked for — and honoured — consent, which is a GDPR accountability requirement, without storing anything that directly identifies the visitor.

## What gets stored

| Field | Notes |
| --- | --- |
| `ipAddressHash` | `hash_hmac('sha256', $ipAddress, Craft::$app->getConfig()->getGeneral()->securityKey)` — the raw IP address is **never stored**, only a keyed hash. Because it's keyed with your project's security key, the same visitor produces the same hash across requests (so you can group their consent history) but the hash cannot be reversed back into an IP without your security key. |
| `sessionId` | PHP session ID at the time of consent |
| `userAgent` | Raw `User-Agent` request header |
| `language` | `navigator.language` as reported by the browser |
| `consentTimestamp` | When the decision was made |
| `consentAction` | One of `"Accept all"`, `"Accept selected"`, `"Refuse all"` |
| `necessaryCookies`, `preferenceCookies`, `analyticalCookies`, `marketingCookies`, `uncategorizedCookies` | The resulting boolean per category |
| `consentMethod` | `"Cookie banner"` for real visitor interactions, `"Seeder script"` for records created via the console seed command |
| `bannerVersion`, `privacyPolicyVersion`, `cookiePolicyVersion` | Snapshot of the version fields from **Settings** at the moment of consent — lets you prove exactly which copy of your banner/policies a visitor agreed to, even after you've since updated them |

Records are created anonymously from the front end — the `create` action (`cookie-banner/consent-records/create`) is allowed for unauthenticated requests, since it's called by every site visitor, not just logged-in users.

## Browsing records in the control panel

The index groups records by year and month in the element index sidebar, and the dashboard-style header shows acceptance percentages per category (`ConsentRecordQuery::categoryAcceptancePercentages()`). Opening an individual record also shows every other record sharing the same `ipAddressHash` — effectively that visitor's consent history over time, useful when investigating a complaint or verifying a specific decision was actually recorded.

## Retention & automatic cleanup

**Settings → Consent record retention** controls how long records are kept: `oneWeek`, `oneMonth`, `sixMonths` (default), `oneYear` or `fiveYears`. Every time a new consent record is created, the plugin automatically queues a `PurgeExpiredConsentRecords` job that hard-deletes anything older than the configured window — so cleanup happens continuously in the background without any cron setup required, as long as your project's queue runner is working.

If you want to trigger a purge manually (e.g. right after lowering the retention period, or from a scheduled task outside Craft's own queue), use the console command:

```bash
php craft cookie-banner/consent-records/purge-expired
```

## Seeding records for local development

To populate your dev environment with realistic-looking data without waiting for real traffic:

```bash
php craft cookie-banner/seed/consent-records --count=25 --period="90 days"
```

- `count` — how many fake records to create (default `5`)
- `fixedIpAddress` — when `true`, every record uses the same fake IP so you can test the "matching records for this visitor" view (default `false`)
- `period` — how far back in time to spread the fake `consentTimestamp` values (default `"30 days"`)

## Querying consent records in Twig/PHP

The plugin registers a `cookieBanner` variable on `craft.cookieBanner`, giving you a regular element query (`ConsentRecordQuery extends ElementQuery`) you can use anywhere — reporting pages, dashboards, exports:

```twig
{# Every record, newest first #}
{% set records = craft.cookieBanner.consentRecords({
    orderBy: 'consentTimestamp DESC',
}).all() %}

{# Only visitors who fully opted in to marketing #}
{% set marketingOptIns = craft.cookieBanner.consentRecords({
    marketingCookies: true,
}).all() %}

{# A specific visitor's consent history #}
{% set history = craft.cookieBanner.consentRecords({
    ipAddressHash: someHash,
}).all() %}
```

```php
use digitalastronaut\craftcookiebanner\elements\ConsentRecord;

// Records created in the last 30 days
$recent = ConsentRecord::find()
    ->consentTimestampBetween((new DateTime())->modify('-30 days'), new DateTime())
    ->all();

// Aggregate acceptance rate per category
$percentages = ConsentRecord::find()->categoryAcceptancePercentages();
// => ['acceptedNecessaryCookiesPercentage' => 100.0, 'acceptedMarketingCookiesPercentage' => 42.3, ...]

// Daily volume + acceptance counts, for your own charts
$daily = ConsentRecord::find()->countAndAcceptancePerDay();
```

Available query-specific methods beyond the standard element query API: `ipAddressHash()`, `consentTimestampBetween($from, $to)`, `groupedByMonth()`, `countAndAcceptancePerDay()` and `categoryAcceptancePercentages()`. Standard criteria params (`sessionId`, `userAgent`, `language`, `consentAction`, `consentMethod`, `bannerVersion`, `privacyPolicyVersion`, `cookiePolicyVersion`, and the five boolean category attributes) can all be passed in the criteria array like any other element query.

::: tip
Reading the live cookie on the current request (rather than historical logged records) is a different API — see [`craft.cookieBanner.consent()`](/plugin/developer-integration#reading-consent-state-in-twig-php) in the developer integration guide.
:::
