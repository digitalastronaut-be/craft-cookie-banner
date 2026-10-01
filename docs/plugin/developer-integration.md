# Developer integration

This page covers the APIs you'll reach for as a developer integrating the banner with your own front-end code: reading consent state, reacting to consent changes in JavaScript, gating embeds/scripts, Google Consent Mode V2, and overriding the banner's markup.

If you haven't added the mandatory Google Consent Mode snippet yet, do that first — see [Installation and setup](/plugin/installation-and-setup#required-setup-step-google-consent-mode-v2).

## The consent object

Everywhere in the plugin — the stored cookie, the JavaScript events, the Twig/PHP helper, and the `ConsentRecord` element — consent is represented as the same five boolean flags:

```js
{
    necessaryCookies: true,       // always true, can't be refused
    preferenceCookies: false,
    analyticalCookies: false,
    marketingCookies: false,
    uncategorizedCookies: false,
}
```

This is stored client-side in a cookie named `craft-cookie-banner` as a JSON string (`path=/`, 1 year expiry), set by the banner itself whenever a visitor makes a choice. Before a decision has been made, every API below treats the state as `necessaryCookies: true` with everything else `false`.

## Reading consent state in Twig / PHP

A `cookieBanner` variable is registered on `craft.cookieBanner`, giving you direct access to the consent cookie server-side — useful for deciding what to render **before** any HTML reaches the browser:

```twig
{% set consent = craft.cookieBanner.consent() %}

{% if consent.marketingCookies %}
    <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ"></iframe>
{% endif %}
```

```php
use digitalastronaut\craftcookiebanner\CookieBanner;

$consent = Craft::$app->getVariable('cookieBanner')->consent();
// or, from a service/controller:
$consent = (new \digitalastronaut\craftcookiebanner\variables\CookieBannerVariable())->consent();

if ($consent['marketingCookies']) {
    // ...
}
```

::: warning Don't combine with full-page caching
`consent()` reads the raw `$_COOKIE` value for the current request. If you branch server-side rendering on it (as above) on a page that's served from a full-page cache (Blitz, static caching, a CDN), every visitor will get whichever variant was cached first, regardless of their own consent. On cached pages, render the same "gated" markup for everyone and upgrade it client-side with the JavaScript events below instead.
:::

## JavaScript events

The `<cookie-banner>` custom element dispatches two `CustomEvent`s on `document` (both `bubbles: true, composed: true`), so you can listen for them anywhere without querying the banner element itself:

| Event | Fired when | `event.detail.consent` |
| --- | --- | --- |
| `cookie-banner:consent-onload` | Once, on `DOMContentLoaded`, with whatever consent state is currently stored (or the all-`false` default if the visitor hasn't decided yet) | current consent object |
| `cookie-banner:consent-update` | Every time the visitor accepts all, refuses all, or saves selected preferences | the new consent object |

```js
function onConsentChange(event) {
    const { consent } = event.detail;
    // consent.marketingCookies, consent.analyticalCookies, etc.
}

document.addEventListener("cookie-banner:consent-onload", onConsentChange);
document.addEventListener("cookie-banner:consent-update", onConsentChange);
```

Listening to both events together is the standard pattern: `consent-onload` handles visitors who already made a choice on a previous visit (or whose page you render without knowing their consent yet), and `consent-update` handles the choice they make during the current page view.

## Example: gating an embed on a consent category

A common requirement is to not load a third-party embed (YouTube, Vimeo, Google Maps, …) until the matching category is granted, and to swap it in live the moment the visitor accepts — without a full page reload.

Markup — render a placeholder with the embed's `src` and required category stored in data attributes, and a link that reopens the banner's detailed settings (any link with `#manage-consent` in its `href` is automatically wired up by the banner, no extra JS needed):

```html
<div class="embed-gate" data-consent-category="marketingCookies" data-embed-src="https://www.youtube.com/embed/dQw4w9WgXcQ">
    <p>This video is blocked because you haven't accepted marketing cookies.</p>
    <a href="#manage-consent">Manage cookie preferences</a>
</div>
```

JavaScript — one handler, reused for both the initial load and any later change:

```js
function applyConsentToEmbeds(consent) {
    document.querySelectorAll("[data-consent-category]").forEach((gate) => {
        const isGranted = Boolean(consent[gate.dataset.consentCategory]);
        let iframe = gate.querySelector("iframe");

        if (isGranted && !iframe) {
            iframe = document.createElement("iframe");
            iframe.src = gate.dataset.embedSrc;
            iframe.loading = "lazy";
            gate.appendChild(iframe);
        }

        const placeholder = gate.querySelector("p, a");
        if (iframe) iframe.hidden = !isGranted;
        gate.querySelectorAll(":scope > p, :scope > a").forEach((el) => (el.hidden = isGranted));
    });
}

["cookie-banner:consent-onload", "cookie-banner:consent-update"].forEach((eventName) =>
    document.addEventListener(eventName, (event) => applyConsentToEmbeds(event.detail.consent)),
);
```

The `<iframe>` element is only ever created in the DOM once consent is granted — it's never present (not even hidden) beforehand, so the third party can't set any cookie or fire any request until the visitor has actually opted in.

For pages where the embed can be server-rendered safely (no full-page cache involved), combine this with the Twig example above: render the real `<iframe>` directly when `craft.cookieBanner.consent().marketingCookies` is already `true` on page load, and only fall back to the placeholder + client-side upgrade when it isn't yet.

## Example: gating a third-party script

Scripts that set their own cookies as a side effect of loading (pixels, chat widgets, heatmap tools) should only ever be injected after consent — and, because a `<script>` tag can't be "unloaded" once it has run, you should guard against injecting it more than once:

```js
let pixelLoaded = false;

function loadMarketingPixel(consent) {
    if (!consent.marketingCookies || pixelLoaded) return;

    pixelLoaded = true;

    const script = document.createElement("script");
    script.src = "https://connect.facebook.net/en_US/fbevents.js";
    script.onload = () => {
        fbq("init", "YOUR_PIXEL_ID");
        fbq("track", "PageView");
    };
    document.head.appendChild(script);
}

["cookie-banner:consent-onload", "cookie-banner:consent-update"].forEach((eventName) =>
    document.addEventListener(eventName, (event) => loadMarketingPixel(event.detail.consent)),
);
```

::: tip Revoking consent mid-session
Once a tracking script has run, there's no reliable browser API to retract the cookies it already set if a visitor later switches a category back off in the same session. For vendors where that matters, the simplest robust approach is to `location.reload()` after `cookie-banner:consent-update` fires with a category turned from `true` to `false`, so the next load never injects that script at all.
:::

## Google Consent Mode V2

Beyond the [mandatory default-consent snippet](/plugin/installation-and-setup#required-setup-step-google-consent-mode-v2), the banner's JavaScript manages Consent Mode automatically — you don't need to call `gtag('consent', 'update', …)` yourself. On every load (if a decision already exists) and after every accept/refuse/save action, it maps the five categories onto Google's consent signal keys and calls `gtag('consent', 'update', …)`:

| Category | Google Consent Mode key(s) |
| --- | --- |
| `necessaryCookies` | `security_storage` (always `granted`) |
| `preferenceCookies` | `functionality_storage` |
| `analyticalCookies` | `analytics_storage` |
| `marketingCookies` | `ad_storage`, `ad_personalization`, `ad_user_data` |
| `uncategorizedCookies` | `personalization_storage` |

It also fires `gtag('event', 'cookie-banner', consent)` after every decision — the full consent object is passed as the event payload, which you can use as a Google Tag Manager trigger (custom event `cookie-banner`) for debugging consent state in GTM's preview mode, or for building your own GA4 reporting around consent rates. Prefer the native Consent Mode signals above (rather than this event) for actually gating GTM tags — that's what Google's own tag templates check against.

## Other globals

`_banner.twig` sets a small global you can read from your own scripts if you need site context without a separate request:

```js
window.craftCookieBanner;
// => { siteId, siteHandle, siteName, csrfTokenValue }
```

## Overriding the banner template

The plugin registers its `templates/` directory as a site template root under the `cookie-banner` namespace, which means — like any Craft plugin — you can override any of its templates by creating a file at the same relative path inside your own site's `templates/` folder. For example, to customize the banner's root markup, create:

```
templates/cookie-banner/components/_banner.twig
```

Your version takes priority over the plugin's bundled one. This is an advanced customization path — the banner's JavaScript (`CookieBanner` custom element) queries specific `data-*` attributes and child elements by selector (`[data-refuse-all-button]`, `[data-accept-all-button]`, `[data-accept-selected-button]`, `[data-determine-preferences-button]`, `[data-determine-preferences-link]`, `[data-cookie-categories]`, `[data-detailed-settings]`, and checkboxes with `data-model="<category>"`), so if you override the template, keep those attributes in place or the banner's interactivity will break.

Two custom elements are involved:

- `<cookie-banner>` — the root element; owns consent state, cookie read/write, and dispatches the events documented above.
- `<collapsible-content>` — a small generic accordion (`[data-collapse-toggle]` / `[data-collapse-content]`, toggled via an `open` class) used for the category/vendor groupings inside the detailed settings panel. Reusable in your own overridden markup if you need the same expand/collapse behaviour.
