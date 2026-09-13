<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Formidable Forms integration.
 */

add_action( 'plugins_loaded', 'vcaptcha_frm_init' );

function vcaptcha_frm_init() {
    if ( ! class_exists( 'FrmForm' ) ) return;
    if ( ! vcaptcha_integration_enabled( 'formidable' ) ) return;

    add_action( 'frm_before_submit_btn', 'vcaptcha_frm_render_widget', 10, 1 );
    add_filter( 'frm_validate_entry', 'vcaptcha_frm_verify', 10, 2 );
}

function vcaptcha_frm_render_widget( $args ) {
    $site_key = get_option( 'vcaptcha_site_key', '' );
    $mode     = get_option( 'vcaptcha_mode', 'invisible' );
    if ( $mode === 'invisible' ) {
        printf( '<div class="v-captcha" data-sitekey="%s" style="display:none"></div>', esc_attr( $site_key ) );
    } else {
        printf( '<div class="v-captcha" data-sitekey="%s" style="margin:10px 0"></div>', esc_attr( $site_key ) );
    }
}

function vcaptcha_frm_verify( $errors, $values ) {
    $site_key   = get_option( 'vcaptcha_site_key', '' );
    $secret_key = get_option( 'vcaptcha_secret_key', '' );
    if ( $site_key === '' || $secret_key === '' ) return $errors;

    $token     = isset( $_POST['vcaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['vcaptcha-response'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    $result    = vcaptcha_verify( $token, $remote_ip );

    if ( ! $result['success'] ) {
        $errors['vcaptcha'] = get_option( 'vcaptcha_error_message', 'Bot verification failed. Please try again.' );
    }

    return $errors;
}
