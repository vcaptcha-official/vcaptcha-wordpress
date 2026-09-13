<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPForms integration.
 */

add_action( 'plugins_loaded', 'vcaptcha_wpforms_init' );

function vcaptcha_wpforms_init() {
    if ( ! function_exists( 'wpforms' ) ) return;
    if ( ! vcaptcha_integration_enabled( 'wpforms' ) ) return;

    add_action( 'wpforms_display_fields_after', 'vcaptcha_wpforms_inject_widget' );
    add_action( 'wpforms_process', 'vcaptcha_wpforms_verify', 10, 3 );
}

/**
 * Inject the vCaptcha widget div after WPForms fields.
 */
function vcaptcha_wpforms_inject_widget( $form_data ) {
    $site_key   = get_option( 'vcaptcha_site_key', '' );
    $secret_key = get_option( 'vcaptcha_secret_key', '' );
    if ( $site_key === '' || $secret_key === '' ) return;

    $mode = get_option( 'vcaptcha_mode', 'invisible' );

    if ( $mode === 'invisible' ) {
        printf(
            '<div class="v-captcha" data-sitekey="%s" style="display:none"></div>',
            esc_attr( $site_key )
        );
    } else {
        echo '<style>
.wpforms-container-full .v-captcha .lc-box {
    display: flex !important; align-items: center !important; gap: 12px !important;
    border: 1px solid #d1d5db !important; border-radius: 8px !important;
    background: #fff !important; box-shadow: 0 1px 3px rgba(0,0,0,.08) !important;
    padding: 12px 14px !important; overflow: hidden !important;
    min-width: 260px !important; max-width: 320px !important;
    position: relative !important;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
    font-size: 14px !important;
}
.wpforms-container-full .v-captcha .lc-chk {
    width: 24px !important; height: 24px !important; border-radius: 4px !important;
    border: 2px solid #d1d5db !important; background: #fff !important;
    flex-shrink: 0 !important; display: flex !important;
    align-items: center !important; justify-content: center !important;
}
.wpforms-container-full .v-captcha .lc-chk.pass { border-color: #16a34a !important; background: #f0fdf4 !important; }
.wpforms-container-full .v-captcha .lc-chk.error { border-color: #dc2626 !important; background: #fef2f2 !important; }
.wpforms-container-full .v-captcha .lc-lbl { display: flex !important; flex-direction: column !important; gap: 2px !important; }
.wpforms-container-full .v-captcha .lc-prog { position: absolute !important; bottom: 0 !important; left: 0 !important; height: 3px !important; width: 0 !important; background: #2563eb !important; transition: width 0.4s ease !important; }
.wpforms-container-full .v-captcha .lc-brand { margin-left: auto !important; text-align: center !important; font-size: 10px !important; color: #9ca3af !important; }
.wpforms-container-full .v-captcha .lc-brand-name { font-weight: 600 !important; font-size: 11px !important; }
.wpforms-container-full .v-captcha #lc-txt { font-size: 14px !important; font-weight: 500 !important; color: #111827 !important; }
.wpforms-container-full .v-captcha #lc-sub { font-size: 11px !important; color: #6b7280 !important; }
</style>';
        printf(
            '<div class="wpforms-field wpforms-field-vcaptcha"><div style="all:initial;display:block"><div class="v-captcha" data-sitekey="%s"></div></div></div>',
            esc_attr( $site_key )
        );
    }
}

/**
 * Verify the vCaptcha token during WPForms submission.
 */
function vcaptcha_wpforms_verify( $fields, $entry, $form_data ) {
    $site_key   = get_option( 'vcaptcha_site_key', '' );
    $secret_key = get_option( 'vcaptcha_secret_key', '' );
    if ( $site_key === '' || $secret_key === '' ) return;

    $form_id   = absint( $form_data['id'] );
    $token     = isset( $_POST['vcaptcha-response'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
        ? sanitize_text_field( wp_unslash( $_POST['vcaptcha-response'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
        : '';
    $remote_ip = isset( $_SERVER['REMOTE_ADDR'] )
        ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
        : '';

    $verification = vcaptcha_verify( $token, $remote_ip );

    if ( ! $verification['success'] ) {
        $error = get_option( 'vcaptcha_error_message', 'Bot verification failed. Please try again.' );
        wpforms()->process->errors[ $form_id ]['header'] = $error;
    }
}
