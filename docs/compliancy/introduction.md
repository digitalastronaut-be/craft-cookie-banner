# Introduction

::: danger This plugin does not make your website GDPR compliant on its own
GDPR compliance is a legal and organizational outcome, not a feature you install. This plugin gives you the technical tooling for cookie **consent** — a banner, a cookie/vendor registry, and a log of what visitors agreed to — which covers one requirement among many under the GDPR and the ePrivacy Directive. Installing and configuring it does **not**, by itself, make a website or webshop compliant.

Getting there — and staying there — requires correct action from **both**:

- **The developer/agency** building the site: filling in accurate banner content, wiring up consent-gated scripts and embeds correctly, applying the technical and data-handling practices described in this guide, and keeping the plugin and its dependencies up to date.
- **The site owner/operator** running it: providing accurate legal text, deciding the lawful basis for each type of processing, handling data subject requests, maintaining agreements with vendors/processors, and keeping the banner content (privacy policy, cookie policy, vendor list) accurate as the site evolves.

Neither Digital Astronaut nor this plugin can audit your specific legal situation, your jurisdiction's local rules, or what your organization actually does with the data it collects. **This guide is general technical guidance written for developers, not legal advice.** For anything business-critical, involve a qualified privacy professional or lawyer before launch.
:::

## Who this guide is for

Developers and agencies building or maintaining a Craft CMS site who need to understand what GDPR compliance actually requires, beyond showing a cookie banner — and which parts of that this plugin does and doesn't cover.

## What GDPR actually asks for

Stripped of legal language, the GDPR asks you to be able to answer five questions about every piece of personal data your site touches, at any time:

| Principle | In practice | A developer's role |
| --- | --- | --- |
| **Lawful basis** (Art. 6) | Why are you allowed to process this data? (consent, contract, legal obligation, legitimate interest, …) | Build the mechanism to capture and prove the lawful basis you were told to use — e.g. consent for marketing cookies, a checkbox for a newsletter, a contract for an order |
| **Purpose limitation** (Art. 5(1)(b)) | Data collected for one reason isn't silently reused for another | Don't let a tracking script collected "for analytics" quietly feed a different vendor; keep categories honest |
| **Data minimization** (Art. 5(1)(c)) | Only collect what you actually need | Don't add form fields, hidden tracking params, or default-enabled integrations "just in case" |
| **Storage limitation** (Art. 5(1)(e)) | Don't keep data forever | Implement real retention/cleanup, not just a policy document (see [Consent records](/plugin/consent-records#retention-automatic-cleanup) for how this plugin handles its own data) |
| **Integrity & confidentiality** (Art. 5(1)(f), Art. 32) | Keep the data secure | HTTPS, hashed/encrypted secrets, access control, patched dependencies — see the [checklist](/compliancy/checklist) |
| **Accountability** (Art. 5(2)) | Be able to *prove* all of the above, not just claim it | Logs, consent records, documented configuration — this is what the banner's audit trail is for |

## Controller vs. processor — and why it still matters for you

Under GDPR, the **data controller** (usually your client — the business that owns the site and decides why data is collected) carries the primary legal responsibility. A developer or agency building the site is often acting as a **processor**, working on the controller's instructions.

That split in legal liability doesn't remove your technical responsibility. Article 25 ("privacy by design and by default") places an explicit obligation on whoever actually *builds* the system to implement appropriate technical measures from the start — not bolt them on after a complaint. In practice: if you build a contact form with no spam/validation protection, a checkout that stores raw card numbers, or an embed that silently drops a tracking cookie before consent, "the client is the controller" won't shield the implementation from scrutiny. Build correctly by default; don't wait to be asked.

## Where this plugin fits — and where it stops

| What the plugin does | GDPR/ePrivacy requirement it supports |
| --- | --- |
| Shows a banner and collects an explicit per-category choice before non-essential cookies are set | **Prior, informed, freely-given consent** for cookies/trackers (ePrivacy Directive, GDPR Art. 7) |
| Maintains a per-site registry of cookies and vendors, with purpose/expiration | **Transparency** — visitors must be told what's collected and why (Art. 13/14) |
| Logs every consent decision with a timestamp and the exact banner/policy version shown | **Accountability** — proof you asked, and what you asked (Art. 5(2), Art. 7(1)) |
| Automatically purges consent records after a configurable retention period | **Storage limitation** for the plugin's own data (Art. 5(1)(e)) |
| Exposes JavaScript events and a Twig/PHP helper to gate embeds and scripts on consent | The **technical means** to actually honour a "refuse" decision, instead of just displaying one |

What it does **not** do for you — this is still the developer's and the site owner's job, covered in the [checklist](/compliancy/checklist):

- Writing an accurate privacy policy and cookie policy
- Deciding the correct lawful basis for each form, integration or data flow on the site
- Negotiating/signing Data Processing Agreements (DPAs) with the vendors you use
- Handling data subject requests (access, rectification, erasure, portability)
- Security hardening beyond the banner itself (hosting, auth, backups, headers, dependency updates)
- Breach detection and the 72-hour notification process
- Age verification / parental consent where the site targets minors
- Payment and financial data handling for webshops
- International data transfers to vendors outside the EU/EEA
- Data Protection Impact Assessments (DPIAs) for higher-risk processing

## How to use this guide

The [checklist](/compliancy/checklist) is split into two parts:

1. **General websites** — applies to every Craft site, regardless of what it does.
2. **Webshops** — additional items specific to e-commerce: payments, orders, customer accounts, marketing vs. transactional consent, and fulfillment data sharing.

Work through the general checklist first on every project; add the webshop section whenever the site processes orders, payments or customer accounts.
