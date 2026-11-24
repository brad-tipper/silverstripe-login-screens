# silverstripe-login-screens

Brand the Silverstripe login screens from YAML. Extends [silverstripe/login-forms](https://github.com/silverstripe/login-forms) (login, lost password, change password): set hex colours and pick a logo from your theme. No template or Sass work.

Version 0.1, Silverstripe 6, PHP 8.3+.

## Install

```sh
composer require bradtipper/silverstripe-login-screens
```

Run `dev/build?flush=1` afterwards.

## Configure

```yaml
---
Name: login-screens-project
After: '#login-screens'
---
BradTipper\LoginScreens\LoginScreen:
  logo: 'images/logo.svg'          # path inside your theme
  logo_dark: 'images/logo-dark.svg' # optional, used in dark mode
  logo_max_width: 120               # px, optional
  logo_max_height: 80               # px, optional
  hide_silverstripe_brand: true     # hides the "Silverstripe CMS" footer (default true)
  colors:
    page_background: '#f4f1ea'
    button_background: '#1f6f5c'
    button_text: '#ffffff'
    link: '#1f6f5c'
  dark_colors:                      # optional, same keys
    page_background: '#101614'
    button_background: '#4fd1a8'
    button_text: '#06241b'
```

## Options

- `logo`, `logo_dark`: looked up in the project themes (`SilverStripe\View\SSViewer.themes`), then the login themes. A module path (`vendor/module: path`) or a full URL also works. With no logo, the site title from SiteConfig is shown, as in login-forms.
- Colour keys (3 or 6 digit hex, anything else is ignored): `page_background`, `page_text`, `form_background`, `form_text`, `field_background`, `field_border`, `field_text`, `link`, `button_background`, `button_text`, `button_hover_background`, `button_hover_text`, `error`, `warning`.
- `button_hover_background` is derived from `button_background` when not set. Outline buttons follow `link`.
- `colors` apply in light mode, `dark_colors` in dark mode. Dark mode follows the visitor's system setting (login-forms `enable_dark_mode`).

## How it works

A Security extension adds the colours as an inline stylesheet that overrides the CSS variables login-forms already defines. A small theme (`themes/login-screens`) overrides the `AppBrand` include to render the logo. Only the login screens are affected; the CMS keeps its own styling.

## Licence

BSD-3-Clause
