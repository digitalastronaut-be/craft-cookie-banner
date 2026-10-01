# Appearance and styling

**Cookie banner → Appearance** controls how the banner looks, per site. Like content, each site gets its own `Appearance` record, seeded on creation from `src/static/appearance/default.json`. If you manage many sites with the same look, use **Copy appearance from site** to clone one site's styling onto another instead of re-entering every value.

## Layout & behaviour options

| Field | Values / purpose |
| --- | --- |
| `cookieBannerPosition` | CSS class applied to the banner wrapper, controls where it docks on screen |
| `preferencesAction` | `"button"` or `"link"` — whether "manage preferences" is a prominent button or a text link |
| `showCookieCategoriesPreview` | When enabled, the five category toggles are visible on the **first** screen of the banner (before opening detailed settings); when disabled, only the summary buttons show first and categories only appear once the visitor expands details |
| `showCookieTables` | Whether the full per-cookie tables (name, purpose, expiration) are rendered inside each category's detailed view, grouped by vendor |
| `cookieListStyle` | `"grouped"` or flat — used by `renderCookiesListHtml()` / the `_cookiesList.twig` component when rendering a standalone cookie policy table (see [Cookie & vendor management](/plugin/cookie-and-vendor-management#rendering-a-cookie-table-on-your-own-pages)) |
| `bannerStyle` | `"square"`, `"rounded"` or `"extra-rounded"` — drives the border-radius of the banner, its buttons/toggles and its inner elements, via fixed radius presets |
| `buttonSize` | `"small"`, `"medium"` or `"large"` — controls button padding |

## Colors, fonts & spacing

The rest of the Appearance page is a direct visual editor for the CSS custom properties rendered inline in `_banner.twig`. Every color field is stored as a raw hex value (no `#` prefix) and reassembled server-side; fonts are stored as raw `font-family` values. The full set of CSS variables, scoped to the `<cookie-banner>` element:

```css
:root {
    --ccb-banner-title-font: ...;
    --ccb-banner-text-font: ...;
    --ccb-banner-border-radius: ...;     /* derived from bannerStyle */
    --ccb-banner-background: ...;
    --ccb-banner-border-thickness: ...px;
    --ccb-banner-border-color: ...;

    --ccb-title-color: ...;
    --ccb-text-color: ...;

    --ccb-link-color: ...;
    --ccb-link-hover-color: ...;

    --ccb-button-background: ...;
    --ccb-button-color: ...;
    --ccb-button-border-radius: ...;     /* derived from bannerStyle */
    --ccb-button-border-thickness: ...px;
    --ccb-button-padding: ...;           /* derived from buttonSize */
    --ccb-button-border-color: ...;
    --ccb-button-hover-background: ...;
    --ccb-button-hover-color: ...;
    --ccb-button-hover-border-color: ...;

    --ccb-toggle-border-radius: ...;
    --ccb-toggle-background-color-off: ...;
    --ccb-toggle-background-color-on: ...;
    --ccb-toggle-color: ...;

    --ccb-cookie-group-title-color: ...;
    --ccb-cookie-group-text-color: ...;
    --ccb-cookie-group-link-color: ...;
    --ccb-cookie-group-link-hover-color: ...;
    --ccb-cookie-group-background: ...;
    --ccb-cookie-group-border-thickness: ...px;
    --ccb-cookie-group-border-color: ...;
    --ccb-cookie-group-border-radius: ...;
    --ccb-cookie-group-hover-background: ...;
    --ccb-cookie-group-hover-border-color: ...;

    --ccb-cookie-table-title-color: ...;
    --ccb-cookie-table-text-color: ...;
    --ccb-cookie-table-background: ...;
    --ccb-cookie-table-border-thickness: ...px;
    --ccb-cookie-table-border-color: ...;
    --ccb-cookie-table-border-radius: ...;
}
```

## Custom CSS escape hatch

The Appearance page includes a raw **Custom CSS** field (backed by [nystudio107/craft-code-editor](https://github.com/nystudio107/craft-code-editor) for syntax highlighting). Its content is injected verbatim at the end of the same `:root { ... }` block shown above, scoped to the `<cookie-banner>` element — use it for anything the visual editor doesn't expose, such as overriding a specific CSS variable conditionally, adding a transition, or tweaking spacing:

```css
--ccb-banner-max-width: 480px;
box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
```

::: warning
The custom CSS value is passed through Twig's `striptags` filter and rendered as raw CSS text (not inside a `<style>` tag you write yourself — the plugin already wraps it in one). Don't try to close the `:root` block or add your own `<style>` tags here; write declarations only, as if you were inside an existing rule block.
:::

## Styling beyond the control panel options

Because every custom property above is just a regular CSS variable on `<cookie-banner>`, you're not limited to the custom CSS field in the CP — you can also override any of them from your site's own compiled stylesheet, which is useful when a design token should come from your design system rather than be hardcoded per site in the CP:

```css
/* your-site/web/css/app.css */
:root {
    --ccb-button-background: var(--brand-primary);
    --ccb-button-hover-background: var(--brand-primary-dark);
}
```

All plugin-rendered markup uses the `ccb-` class prefix (e.g. `.ccb-cookie-banner`, `.ccb-button`, `.ccb-lightswitch`, `.ccb-cookies-category`), so you can also target structural elements directly if you need layout changes the appearance settings don't cover. If you need to change markup itself rather than just styling, see [template overrides](/plugin/developer-integration#overriding-the-banner-template) in the developer integration guide.
