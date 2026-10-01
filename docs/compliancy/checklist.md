# Checklist

::: danger Not a compliance guarantee
Working through this checklist does not guarantee GDPR compliance, and ticking every box is not legal proof of anything. It's a practical starting point for developers, covering the technical and organizational basics most sites and webshops need. See the [full disclaimer](/compliancy/introduction) before relying on it, and get sign-off from whoever is legally responsible for the site (the data controller) and, where the stakes are high, a qualified privacy lawyer.
:::

Work through **General websites** on every project. Add **Webshops** on top of it whenever the site handles orders, payments, or customer accounts.

## General websites

### Legal foundation

- [ ] A **privacy policy** exists, is linked from every page (commonly in the footer and in the cookie banner itself — see [Banner content](/plugin/banner-content)), and accurately describes what's actually collected, why, for how long, and who it's shared with.
- [ ] A separate (or combined) **cookie policy** lists every cookie and vendor actually in use — kept in sync with the [Cookies & vendors](/plugin/cookie-and-vendor-management) registry, not written once and forgotten.
- [ ] You know who the **data controller** is for this site (almost always the client, not the agency) and who, if anyone, is the appointed **Data Protection Officer** or privacy contact.
- [ ] For every non-trivial form, integration or data flow, someone has explicitly decided its **lawful basis** (consent, contract, legal obligation, legitimate interest, etc.) — this isn't a developer's call to make alone, but it is a developer's job to ask the question before building.
- [ ] If the site is likely to be used by **minors**, age-appropriate consent handling (e.g. parental consent flows) has been considered — GDPR's default age threshold is 16, but member states can lower it to 13.

### Consent & cookies

- [ ] The [mandatory Google Consent Mode V2 snippet](/plugin/installation-and-setup#required-setup-step-google-consent-mode-v2) is in place **above** Google Tag Manager/gtag, on every page — not just pages that happen to show the banner.
- [ ] No cookie, pixel, or script outside the "necessary" category fires **before** the visitor has made a choice. Verify this with your browser's dev tools (Application → Cookies) on a clean/incognito session, not just by reading the code.
- [ ] Every cookie actually set by the site (check the [detected browser cookies](/plugin/cookie-and-vendor-management#automated-detection-and-suggestions) overview) is defined with an accurate purpose and expiration, or deliberately blacklisted with a documented reason.
- [ ] Embeds and third-party scripts (video players, maps, chat widgets, social share buttons, web fonts loaded from a third-party CDN) are gated on the correct consent category using the [JavaScript events or server-side helper](/plugin/developer-integration), not left always-on.
- [ ] Refusing or changing consent actually stops the relevant trackers — re-test the "refuse all" and "accept selected" paths after every deploy that touches analytics/marketing tooling, not just "accept all".
- [ ] Self-host fonts, icons and other static assets where practical instead of pulling them from a third-party CDN at render time — a request to a third party on every page view can itself leak the visitor's IP address regardless of consent state, and several EU courts have specifically flagged unconsented Google Fonts CDN loading as a GDPR issue.
- [ ] The banner's content, action buttons and category labels are in the visitor's language on multi-site/multi-language installs — see [Banner content](/plugin/banner-content#multi-site-behaviour).

### Data subject rights

- [ ] There's a known process (even if manual) for handling an **access request** — someone asking "what data do you have on me?" — including data that lives outside Craft (mailing list tools, CRMs, analytics platforms).
- [ ] There's a known process for a **deletion/erasure request**, covering the database, backups (or a documented backup retention period after which they age out naturally), and any third-party tools that received the same data.
- [ ] Contact forms, account registration, and any other place that collects personal data make clear what it'll be used for at the point of collection (Art. 13 "right to be informed"), not only in a separate privacy policy nobody reads.
- [ ] Marketing communication (newsletters, promotional email) uses a genuine **opt-in**, ideally double opt-in, and is kept separate from transactional/necessary communication — never pre-ticked, never bundled into "accept terms".
- [ ] Every marketing email includes a working unsubscribe mechanism, and unsubscribes are actually honoured going forward.

### Security & technical measures

- [ ] The site is served over **HTTPS** everywhere, with no mixed content.
- [ ] Craft's `securityKey` and all API keys/secrets live in environment variables, never committed to the repository.
- [ ] Devmode/debug output and detailed error pages are disabled in production.
- [ ] Craft core, plugins and Composer/NPM dependencies are kept up to date — security patches aren't optional once personal data is involved.
- [ ] Control panel access uses strong authentication (enforce a password policy, consider 2FA) and the [permission set for this plugin](/plugin/installation-and-setup#permissions) is scoped per user group — not every editor needs `cookie-banner:edit-settings`.
- [ ] Forms are protected against spam/abuse (honeypot/captcha, rate limiting) and validate/sanitize input server-side, not just client-side.
- [ ] Backups are encrypted at rest (or stored somewhere that is), access to them is restricted, and they're retained no longer than necessary.
- [ ] Logs that include IP addresses or other personal data (web server logs, error logs, the plugin's own [consent records](/plugin/consent-records)) have a defined retention period and are actually purged — don't let "temporary" debug logging become permanent storage.
- [ ] Basic security headers are set (`Content-Security-Policy`, `X-Content-Type-Options`, `Referrer-Policy`, `Strict-Transport-Security` at minimum).

### Vendors & data transfers

- [ ] Every third-party service that processes personal data on your behalf (hosting, analytics, email delivery, CRM, chat widgets, payment providers) has a signed **Data Processing Agreement** in place with the controller.
- [ ] For vendors based outside the EU/EEA (most US cloud services), an appropriate transfer mechanism is in place — Standard Contractual Clauses, an adequacy decision, or equivalent — this is a legal/contractual question for the controller, but flag it if you're introducing a new non-EU vendor into the stack.
- [ ] You maintain (or the controller maintains, with your input as the one who knows the stack) a record of processing activities covering what each vendor receives and why.

### Breach readiness

- [ ] There's a documented point of contact and a basic process for what happens if personal data is exposed (compromised database, leaked backup, misconfigured access) — GDPR requires notifying the relevant supervisory authority within **72 hours** of becoming aware of a breach, which is not enough time to improvise a process from scratch.
- [ ] Monitoring/alerting exists for things that would indicate a breach (failed login spikes, unexpected data exports, error-rate anomalies) — even a minimal setup is better than finding out from a customer.

## Webshops

Everything above still applies. In addition, for sites that process orders, payments or customer accounts:

### Checkout & payments

- [ ] Card and payment details are **never stored or transmitted through your own servers in raw form** — use a PCI-DSS-compliant payment provider (Mollie, Stripe, Adyen, …) and let them handle the sensitive data entirely.
- [ ] Guest checkout is available, or at minimum account creation isn't forced just to complete a purchase — forcing an account is data over-collection if you don't need it for anything other than this one order.
- [ ] Checkout forms only collect the fields actually needed to fulfill and invoice the order — resist adding "nice to have" fields (birthday, extra phone numbers) without a specific, stated purpose.
- [ ] Fraud-prevention checks (address/IP verification, velocity checks) are disclosed, at least generally, and are based on a legitimate-interest assessment, not silently bolted on.

### Customer accounts & order retention

- [ ] A **retention policy exists for order and invoice data** that reconciles GDPR's storage-limitation principle with local tax/accounting law — most EU countries legally require invoices to be kept for 7–10 years, which overrides "delete on request" for that specific financial record. Document this so a deletion request can be answered correctly: financial records are retained for the legal minimum, everything else tied to the customer (marketing profile, browsing history, saved preferences) is deleted.
- [ ] Dormant/inactive customer accounts have a defined lifecycle (e.g. anonymize or delete after N years of inactivity) rather than accumulating indefinitely.
- [ ] Customers can view and correct their own stored data (addresses, account details) directly, reducing the volume of manual access/rectification requests.

### Marketing vs. transactional communication

- [ ] Order confirmations, shipping updates and other **transactional emails** are treated as necessary for the contract (no separate marketing consent needed) — but make sure nothing marketing-flavored (upsells, newsletter content) is smuggled into them without consent.
- [ ] Newsletter sign-up during checkout is a genuinely separate, unticked opt-in — not a pre-checked box or a condition of completing the order.
- [ ] **Abandoned cart emails and retargeting/remarketing ads** are gated on marketing consent having actually been granted — this is exactly the kind of processing the `marketingCookies`/`analyticalCookies` categories in the banner are meant to gate; see [gating embeds and scripts](/plugin/developer-integration#example-gating-a-third-party-script) for the implementation pattern.

### Fulfillment & third parties

- [ ] Data shared with couriers/logistics partners (name, address, phone) is limited to what's needed to deliver the order, and a DPA covers that sharing.
- [ ] Data shared with warehousing, ERP, accounting or CRM systems integrated with the webshop is covered by the same vendor/DPA review as any other processor.
- [ ] If the shop ships internationally, be aware that customer and shipping data may cross into jurisdictions with their own rules on top of GDPR — flag this to the controller rather than assuming EU rules are the only consideration.

### Reviews & public-facing content

- [ ] Product reviews, Q&A, or testimonials that display a reviewer's name/photo have their own consent at the point of submission, independent of the general cookie/marketing consent.
- [ ] Moderation can actually remove a review (and the personal data in it) on request, including from any third-party reviews widget in use.

---

This checklist is deliberately practical, not exhaustive — it won't cover every edge case for every jurisdiction or business model. Treat it as the floor, not the ceiling, and revisit it whenever you add a new integration, form, or data flow to a project.
