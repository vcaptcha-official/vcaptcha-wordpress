<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Contact Form 7 6.x integration.
 */

add_action( 'plugins_loaded', 'vcaptcha_cf7_init' );

function vcaptcha_cf7_init() {
    if ( ! class_exists( 'WPCF7' ) ) return;
    if ( ! vcaptcha_integration_enabled( 'cf7' ) ) return;

    add_filter( 'wpcf7_form_elements', 'vcaptcha_cf7_inject_widget', 20 );
    add_filter( 'wpcf7_posted_data',   'vcaptcha_cf7_inject_posted_data' );
    add_filter( 'wpcf7_spam',          'vcaptcha_cf7_verify_response', 9, 2 );
}

/**
 * Insert the vCaptcha widget div into the form.
 */
function vcaptcha_cf7_inject_widget( $content ) {
    $site_key   = get_option( 'vcaptcha_site_key', '' );
    $secret_key = get_option( 'vcaptcha_secret_key', '' );
    if ( $site_key === '' || $secret_key === '' ) return $content;

    $mode = get_option( 'vcaptcha_mode', 'invisible' );

    if ( $mode === 'invisible' ) {
        $widget = sprintf(
            '<div class="v-captcha" data-sitekey="%s" style="display:none"></div>',
            esc_attr( $site_key )
        );
        return $content . "\n" . $widget;
    } else {
        $widget = sprintf(
            '<div class="v-captcha" data-sitekey="%s" style="margin-bottom:1em"></div>',
            esc_attr( $site_key )
        );
        $submit_pattern = '<input class="wpcf7-form-control wpcf7-submit';
        if ( strpos( $content, $submit_pattern ) !== false ) {
            return str_replace( $submit_pattern, $widget . $submit_pattern, $content );
        }
        return $content . "\n\n" . $widget;
    }
}

/**
 * Bridge the vcaptcha-response token into CF7's internal posted data.
 */
function vcaptcha_cf7_inject_posted_data( $posted_data ) {
    $token = isset( $_POST['vcaptcha-response'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
        ? sanitize_text_field( wp_unslash( $_POST['vcaptcha-response'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
        : '';
    $posted_data['vcaptcha-response'] = $token;
    return $posted_data;
}

/**
 * Verify the vCaptcha token via wpcf7_spam filter (CF7 6.x).
 */
function vcaptcha_cf7_verify_response( $spam, $submission ) {
    if ( $spam ) return $spam;

    $site_key   = get_option( 'vcaptcha_site_key', '' );
    $secret_key = get_option( 'vcaptcha_secret_key', '' );
    if ( $site_key === '' || $secret_key === '' ) return $spam;

    $token     = '';
    $remote_ip = isset( $_SERVER['REMOTE_ADDR'] )
        ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
        : '';

    if ( $submission instanceof WPCF7_Submission ) {
        $token = (string) $submission->get_posted_data( 'vcaptcha-response' );
    }

    $verification = vcaptcha_verify( $token, $remote_ip );

    if ( ! $verification['success'] ) {
        if ( $submission instanceof WPCF7_Submission ) {
            $submission->add_spam_log( array(
                'agent'  => 'vcaptcha',
                'reason' => ( $token === '' )
                    ? 'vCaptcha token is empty.'
                    : 'vCaptcha validation failed.',
            ) );
        }
        return true;
    }

    return false;
}
