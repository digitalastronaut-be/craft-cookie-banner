# Release Notes for Cookie banner

## 1.0.17-beta - 2026-11-06

- Removed base consent script from the head.

> [!NOTE]  
> Users should manually add `gtag('consent', 'default'` event to set the inital consent for google consent mode V2 to work correctly

## 1.0.17-beta - 2026-11-06

### Features

- Detected from browser storage always took the $\_COOKIE variable as input. Now it uses the cookies set on the frontend as reference via a cached value.

### Bugfixes

- Fix detected from browser wildcard cookies not always showing as not defined [#3](https://github.com/digitalastronaut-be/craft-cookie-banner/issues/9)
- Fix a css scope issue [#10](https://github.com/digitalastronaut-be/craft-cookie-banner/issues/9)
- Fix an action url [#9](https://github.com/digitalastronaut-be/craft-cookie-banner/issues/9)
- Added commonJS webpack.mix.cjs [#4](https://github.com/digitalastronaut-be/craft-cookie-banner/issues/9)

## 1.0.16-beta - 2026-11-06

- Fix return default object when cookie is not set from twig variable

## 1.0.15-beta - 2026-11-06

- The `cookie-banner:consent-onload` event now gets called when the `DOMContentLoaded` event is triggered to prevent other event listeners missing it.
- A craft varialbe was added so you can easily check consnent in your twig templates `{% if craft.cookieBanner.consent.necessaryCookies %}{% endif %}`

## 1.0.14-beta - 2026-19-05

- Cookie categories with no cookies are now excluded from the cookie banner

## 1.0.13-beta - 2026-30-04

- Fixed authors missing in composer.json
- Fixed changelog formatting

## 1.0.12-beta - 2026-29-04

- Fixed bug in the issue calculation

## 1.0.11-beta - 2026-27-04

- Fixed bug in the issue calculation

## 1.0.10-beta - 2026-24-04

- Cleanup method moved to a queue job
- Dashboard chart queries optimized
- Added Frontend and CP asset bundles for small download sizes on the frontend
- Chart styling improved
- Fixed a date issue where the consent records of today didn't show on the graph
- Fixed some MySQL specific queries with ANSI SQL
- Optimised the issues queries and added caching
- Fixed permissions

## 1.0.9-beta - 2025-21-04

- Bulk import error handling
- Bulk import input sanitizing

## 1.0.8-beta - 2026-21-04

- Added copy appearance from option to make repeated appearance configurations easy
- Fixed appearance default value

## 1.0.7-beta - 2026-21-04

- Changed manage cookie selector from `[data-manage-consent]` to links with anchor #manage-consent for easier management in the CP
- Fixed appearance page code field not showing if categories/tables setting was disabled.

## 1.0.6-beta - 2026-21-04

- Performance optimalisations + Documentation

## 1.0.2-beta - 2026-16-04

- Fixed composer release issues

## 1.0.1-beta - 2026-16-04

- Fixed saving settings bug

## 1.0.0-beta - 2026-16-04

- Initial release
