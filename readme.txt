=== vCaptcha ===
Contributors: vcaptcha
Tags: captcha, spam, bot protection, contact form 7, wpforms
Requires at least: 5.8
Tested up to: 7.1
Stable tag: 1.0.11
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Passive bot detection for WordPress. Real visitors pass silently — bots get challenged.

== Description ==

**vCaptcha** protects your WordPress forms from spam and bot abuse using passive bot detection. Unlike traditional CAPTCHAs, vCaptcha analyzes visitor behavior silently in the background. Most real visitors pass with zero interaction — no checkboxes, no puzzles.

= Features =

* **Invisible by default** — real visitors never see a challenge
* **Checkbox mode available** — shows a visible widget if preferred
* **Contact Form 7 integration** — protects all CF7 forms automatically
* **WPForms integration** — protects all WPForms forms automatically
* **Ninja Forms integration** — protects all Ninja Forms automatically
* **Fluent Forms integration** — protects all Fluent Forms automatically
* **Formidable Forms integration** — protects all Formidable Forms automatically
* **WooCommerce integration** — protects checkout and registration forms
* **GiveWP integration** — protects GiveWP 3.x classic donation forms
* **WordPress core protection** — protects login, registration, and lost password forms
* **Per-integration toggles** — enable or disable protection for each form plugin individually
* **Score threshold** — configurable minimum trust score (0–100)
* **No cookies** — vCaptcha does not set cookies on your visitors' browsers
* **Privacy-friendly** — visitor data is not used for advertising

= How it works =

1. The vCaptcha widget loads on pages with forms
2. It silently analyzes behavioral signals (mouse movement, timing, browser fingerprint)
3. Real visitors receive a signed verification token automatically
4. On form submission, the token is verified server-to-server with vCaptcha
5. Bots that fail passive scoring are shown an image challenge before they can submit

= Supported Form Plugins =

* **Contact Form 7** — all forms protected automatically, no configuration needed
* **WPForms** — all forms protected automatically, no configuration needed
* **Ninja Forms** — all forms protected automatically, no configuration needed
* **Fluent Forms** — all forms protected automatically, no configuration needed
* **Formidable Forms** — all forms protected automatically, no configuration needed
* **WooCommerce** — checkout and customer registration forms protected automatically
* **GiveWP** — classic (3.x) donation forms protected automatically. GiveWP 4.x block-based forms are not yet supported.
* **WordPress core** — login, registration, and lost password forms protected automatically

= Requirements =

* A free or paid vCaptcha account — [sign up at vcaptcha.com](https://www.vcaptcha.com)
* One or more supported form plugins (for form protection)

== Installation ==

1. Upload the `vcaptcha` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Go to **Settings → vCaptcha**
4. Enter your Site Key and Secret Key from your [vCaptcha dashboard](https://www.vcaptcha.com/dashboard/keys)
5. Choose Invisible or Checkbox mode
6. Optionally disable protection for specific form plugins under **Protected Forms**
7. Save — your forms are now protected automatically

== Frequently Asked Questions ==

= Do I need a vCaptcha account? =

Yes. A free account is available at [vcaptcha.com](https://www.vcaptcha.com). The free plan includes unlimited verifications with interactive challenges. Paid plans add passive verification so most visitors never see a challenge.

= Will this break my existing forms? =

No. The plugin hooks into each form plugin's submission process non-destructively. If vCaptcha is not configured (no keys entered), the plugin does nothing. You can also disable protection per plugin under Settings → vCaptcha → Protected Forms.

= Which form plugins are supported? =

Contact Form 7, WPForms, Ninja Forms, Fluent Forms, Formidable Forms, WooCommerce, GiveWP 3.x, and WordPress core login/registration are currently supported. All are protected automatically once you enter your site key and secret key — no changes to your forms are needed.

= What is the minimum score setting? =

vCaptcha assigns each visitor a trust score from 0 to 100. Higher scores indicate more human-like behavior. Submissions scoring below your minimum threshold are blocked. The default of 35 blocks obvious bots while allowing borderline cases to attempt an interactive challenge. Raise it for stricter protection, lower it if legitimate visitors are being blocked.

= Does vCaptcha use cookies? =

No. vCaptcha does not set cookies on your visitors' browsers.

= Is visitor data sold or used for advertising? =

No. See the [vCaptcha Privacy Policy](https://www.vcaptcha.com/privacy) for details.

= What happens if vCaptcha's servers are unavailable? =

If vCaptcha cannot be reached, the plugin will block the form submission and show an error. This is intentional — failing closed protects your form from spam during outages.

= Does GiveWP 4.x work? =

GiveWP 4.x uses a new block-based form renderer that is not yet supported. Support for GiveWP 4.x is planned for a future release. GiveWP 3.x classic forms are fully supported.

== Screenshots ==

1. Settings page — enter your site key and secret key
2. Settings page — per-integration toggles under Protected Forms
3. Invisible mode — no visible widget, verification runs automatically
4. Checkbox mode — visible widget before the submit button

== Changelog ==

= 1.0.11 =
* Initial release
* Contact Form 7 integration (CF7 6.x compatible)
* WPForms integration
* Ninja Forms integration
* Fluent Forms integration
* Formidable Forms integration
* WooCommerce checkout and registration integration
* GiveWP 3.x classic form integration
* WordPress core login, registration, and lost password integration
* Invisible and checkbox widget modes
* Per-integration enable/disable toggles in settings
* Configurable minimum score threshold
* Settings page with status indicators for all integrations
* Documentation update

== Upgrade Notice ==

= 1.0.11 =
Initial release.
