# Cookie banner

This plugin provides a fully featured toolset for managing cookies and displaying a GDPR compliant cookie banner on your website.

## Requirements

This plugin requires Craft CMS 5.8.0 or later, and PHP 8.2 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Cookie banner”. Then press “Install”.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project

# tell Composer to load the plugin
composer require digitalastronaut/craft-cookie-banner

# tell Craft to install the plugin
php craft plugin/install cookie-banner
```

This plugin provides a fully featured toolset for managing cookie consent and displaying a GDPR compliant banner on your website. It brings the same powerfull features as many premium CMP's whilst being seamlessly integrated into the Craft CMS control panel.

## Features

1. Native multi-site support and translations
2. No config files or coding required. Fully configurable from the control panel
3. Contains a database of 4000+ cookies and vendors translated in 7 languages (NL, EN, ES, IT, FR, DE, PL)
4. Bulk insert and automated categorization of cookies and vendors
5. Automated scans and suggestions
6. Customize the UI from the control panel for each site
7. Consent records with automatic cleanup
8. 0 Dependencies 18.7 KiB (~6.5 KiB gzip) JS bundle
9. Dashboard with consent statistics
10. Twig helper for generating cookie tables
11. Google V2 Consent & Custom JS events support
12. Cookie/vendor blacklist

| Feature          | Our Plugin                                             | CMP                                                |
| ---------------- | ------------------------------------------------------ | -------------------------------------------------- |
| **Access**       | Directly integrated within the Craft CMS control panel | External, outside the CMS                          |
| **Site support** | Multi-site support                                     | Single site only                                   |
| **Branding**     | Fully customizable, no third-party branding            | Third-party branding/contributions included        |
| **Pricing**      | Simple flat annual fee                                 | Variable cost based on traffic and number of pages |
