# vCaptcha for WordPress

Official [vCaptcha](https://www.vcaptcha.com) WordPress plugin — passive bot detection for WordPress forms.

[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue)](https://wordpress.org/plugins/vcaptcha)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)](https://www.php.net)


## Description

**vCaptcha** protects your WordPress forms from spam and bot abuse using passive bot detection. Unlike traditional CAPTCHAs, vCaptcha analyzes visitor behavior silently in the background. Most real visitors pass with zero interaction — no checkboxes, no puzzles.

## Features

- **Invisible by default** — most real visitors never see a challenge
- **Checkbox mode available** — shows a visible widget if preferred
- **Per-integration toggles** — enable or disable protection for each form plugin individually
- **Score threshold** — configurable minimum trust score (0–100)
- **No cookies** — vCaptcha does not set cookies on your visitors' browsers
- **Privacy-friendly** — visitor data is not used for advertising

## Supported Form Plugins

| Plugin | Status |
|--------|--------|
| Contact Form 7 | ✅ Fully supported |
| WPForms | ✅ Fully supported |
| Ninja Forms | ✅ Fully supported |
| Fluent Forms | ✅ Fully supported |
| Formidable Forms | ✅ Fully supported |
| WooCommerce | ✅ Checkout and registration |
| GiveWP 3.x | ✅ Classic donation forms |
| GiveWP 4.x | ⏳ Planned (block-based forms) |
| WordPress core | ✅ Login, registration, lost password |

All supported plugins are protected automatically once you enter your site key and secret key — no changes to your forms are needed.

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- A free or paid [vCaptcha account](https://www.vcaptcha.com)

## Installation

### From WordPress.org (recommended)

1. Go to **Plugins → Add New** in your WordPress admin
2. Search for **vCaptcha**
3. Click **Install Now** then **Activate**

### Manual installation

1. Download the plugin zip from [vcaptcha.com/downloads/vcaptcha-wp.zip](https://www.vcaptcha.com/downloads/vcaptcha-wp.zip)
2. Unzip the `vcaptcha-wp.zip` file
3. Copy the `vaptcha` folder to `/wp-content/plugins/`
4. Activate through **Plugins → Installed Plugins**

### After activation

1. Go to **Settings → vCaptcha**
2. Enter your **Site Key** and **Secret Key** from your [vCaptcha dashboard](https://www.vcaptcha.com/dashboard/keys)
3. Choose **Invisible** or **Checkbox** widget mode
4. Optionally disable protection for specific integrations under **Protected Forms**
5. Save — your forms are now protected

Don't have an account? [Sign up free](https://www.vcaptcha.com/register) — no credit card required. New accounts get a 30-day Pro Plus trial automatically.

## How It Works

1. The vCaptcha widget loads on pages with forms
2. It silently analyzes behavioral signals (mouse movement, timing, browser fingerprint)
3. Real visitors receive a signed verification token automatically
4. On form submission, the token is verified server-to-server with vCaptcha
5. Bots that fail passive scoring are shown an image challenge before they can submit

## Frequently Asked Questions

**Will this break my existing forms?**
No. The plugin hooks into each form plugin's submission process non-destructively. If vCaptcha is not configured (no keys entered), the plugin does nothing.

**What is the minimum score setting?**
vCaptcha assigns each visitor a trust score from 0 to 100. The default minimum of 35 blocks obvious bots while allowing borderline cases to attempt an interactive challenge. Raise it for stricter protection, lower it if legitimate visitors are being blocked.

**What happens if vCaptcha's servers are unavailable?**
The plugin fails closed — form submissions are blocked and an error is shown. This is intentional to protect your forms during outages.

**Does GiveWP 4.x work?**
GiveWP 4.x uses a block-based form renderer that is not yet supported. GiveWP 3.x classic forms are fully supported. GiveWP 4.x support is planned for a future release.

**Does vCaptcha use cookies?**
No. vCaptcha does not set cookies on your visitors' browsers.

## Changelog

### 1.0.11 — Initial release
- Contact Form 7 integration (CF7 6.x compatible)
- WPForms integration
- Ninja Forms integration
- Fluent Forms integration
- Formidable Forms integration
- WooCommerce checkout and registration integration
- GiveWP 3.x classic form integration
- WordPress core login, registration, and lost password integration
- Invisible and checkbox widget modes
- Per-integration enable/disable toggles in settings
- Configurable minimum score threshold
- Settings page with status indicators for all integrations

## License

GPL v2 or later. See [LICENSE](LICENSE).

The vCaptcha backend service is proprietary. This plugin is open source.

---

[vcaptcha.com](https://www.vcaptcha.com) · [Documentation](https://www.vcaptcha.com/docs) · [Support](https://www.vcaptcha.com/contact) · [Privacy Policy](https://www.vcaptcha.com/privacy)
