# Banner content

All of the text, cookie categories and vendors shown in the banner and in the detailed preferences panel are managed per site under **Cookie banner → Content**. This maps directly onto a `Content` record (one row per site, keyed by `siteId`).

## Multi-site behaviour

Every site has its own, independent `Content` record. When a new site is added, Craft seeds it from the bundled translation closest to that site's language:

- The plugin looks for `src/static/content/<language>.json` (e.g. `nl.json`, `fr.json`, `de.json`, `es.json`, `it.json`, `pl.json`, `en.json`).
- If no translation exists for that language, it falls back to `src/static/content/default.json` (all fields empty, ready to fill in).

The content page in the control panel includes a site switcher — use it to edit each site's wording, categories, cookies and vendors independently. There's no "shared across sites" content; each locale is expected to have its own legally accurate text.

## Content fields

| Field | Description |
| --- | --- |
| `title` / `text` | Banner heading and intro copy |
| `privacyPolicyLinkLabel` / `privacyPolicyLinkURL` | Optional link to your privacy policy, shown in the banner footer |
| `cookiePolicyLinkLabel` / `cookiePolicyLinkURL` | Optional link to your cookie policy |
| `acceptAllButtonLabel` / `acceptSelectedButtonLabel` / `refuseAllButtonLabel` | Action button labels |
| `determinePreferencesButtonLabel` | Label shown when categories are collapsed (see [Appearance](/plugin/appearance-and-styling) → `showCookieCategoriesPreview`) |
| `detailedPreferencesButtonLabel` | Label shown when categories are already visible on the first screen |
| `vendors` | Array of `{ name, description, url, enabled }` — third parties whose cookies you use |

## Cookie categories

Every site has exactly five fixed categories (these match Google Consent Mode V2's signal groups — see [Developer integration](/plugin/developer-integration)):

- `necessaryCookies` — always on, cannot be refused (the checkbox is rendered `disabled checked`)
- `preferenceCookies`
- `analyticalCookies`
- `marketingCookies`
- `uncategorizedCookies`

Each category has three text fields and one list field:

- `<category>Title` — heading shown in the detailed settings panel
- `<category>Label` — short label shown next to the toggle
- `<category>Definition` — explanation text shown under the heading
- `<category>Cookies` — the actual cookies in that category, each shaped as:

```json
{
    "name": "_ga",
    "vendor": "google-analytics",
    "purpose": "Used to distinguish unique users for analytics reporting.",
    "expiration": "2 years",
    "enabled": true
}
```

`vendor` is a slug (lowercased, spaces replaced with `-`) matching the `name` of one of the entries in the site's `vendors` array — this is how a cookie gets grouped under its vendor in the detailed settings table. Use `"default"` (or leave it unset) for cookies that aren't tied to a specific vendor.

A cookie with `enabled: false` is kept in the content record (so you don't lose the entry) but is treated as "disabled" everywhere else in the plugin — it won't count toward the completeness check in [Cookie & vendor management](/plugin/cookie-and-vendor-management) and won't render in the banner's cookie tables.

## Adding cookies from the Content page

The **Add cookie** action on the Content page lets you add a single cookie, by name, to one category. If the name matches an entry in the bundled cookie database, the purpose/expiration/vendor are pre-filled automatically for every site at once (see [automated detection](/plugin/cookie-and-vendor-management#automated-detection-and-suggestions)); otherwise you fill them in manually.

For bulk-creating many cookies or vendors at once across all sites in one go, use **Cookies & vendors → Bulk create**, covered in the next page.

## Rendering banner content outside of the banner itself

If you want to show a full cookie table on a dedicated cookie/privacy policy page, don't read the `Content` record directly — use the `renderCookiesListHtml()` Twig function described in [Cookie & vendor management](/plugin/cookie-and-vendor-management#rendering-a-cookie-table-on-your-own-pages), which renders the same categorised markup used in the banner, honoring the site's `cookieListStyle` appearance setting.
