<?php
/**
 * Plugin Name:       vCaptcha
 * Plugin URI:        https://www.vcaptcha.com/wordpress
 * Description:       Passive bot detection for WordPress. Protects forms with invisible CAPTCHA — real visitors pass silently, bots get challenged.
 * Version:           1.0.11
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            vCaptcha, LLC
 * Author URI:        https://www.vcaptcha.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       vcaptcha
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'VCAPTCHA_VERSION',    '1.0.11' );
define( 'VCAPTCHA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VCAPTCHA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'VCAPTCHA_API_URL',    'https://www.vcaptcha.com/siteverify' );
define( 'VCAPTCHA_WIDGET_URL', 'https://www.vcaptcha.com/1/api.js' );

// ── Load modules ──────────────────────────────────────────────────────────────

require_once VCAPTCHA_PLUGIN_DIR . 'includes/settings.php';
require_once VCAPTCHA_PLUGIN_DIR . 'includes/widget.php';
require_once VCAPTCHA_PLUGIN_DIR . 'includes/verify.php';
require_once VCAPTCHA_PLUGIN_DIR . 'includes/cf7.php';
require_once VCAPTCHA_PLUGIN_DIR . 'includes/wpforms.php';
require_once VCAPTCHA_PLUGIN_DIR . 'includes/core.php';
require_once VCAPTCHA_PLUGIN_DIR . 'includes/woocommerce.php';
require_once VCAPTCHA_PLUGIN_DIR . 'includes/gravityforms.php';
require_once VCAPTCHA_PLUGIN_DIR . 'includes/fluentforms.php';
require_once VCAPTCHA_PLUGIN_DIR . 'includes/ninjaforms.php';
require_once VCAPTCHA_PLUGIN_DIR . 'includes/formidable.php';
require_once VCAPTCHA_PLUGIN_DIR . 'includes/givewp.php';
#error_log( 'vCaptcha: cf7.php loaded' );

// ── Activation / deactivation ─────────────────────────────────────────────────

register_activation_hook( __FILE__, 'vcaptcha_activate' );
function vcaptcha_activate() {
    add_option( 'vcaptcha_site_key',       '' );
    add_option( 'vcaptcha_secret_key',     '' );
    add_option( 'vcaptcha_mode',           'invisible' );
    add_option( 'vcaptcha_min_score',      35 );
    add_option( 'vcaptcha_error_message',  'Bot verification failed. Please try again.' );
}

register_deactivation_hook( __FILE__, 'vcaptcha_deactivate' );
function vcaptcha_deactivate() {
    // Nothing to clean up — options are kept in case user reactivates
}
