<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * GiveWP donation form integration.
 */

add_action( 'plugins_loaded', 'vcaptcha_give_init' );

function vcaptcha_give_init() {
    if ( ! function_exists( 'give' ) ) return;
    if ( ! vcaptcha_integration_enabled( 'givewp' ) ) return;

    add_action( 'give_donation_form_before_personal_info', 'vcaptcha_give_render_widget' );
    add_action( 'give_checkout_error_checks', 'vcaptcha_give_verify' );
}

function vcaptcha_give_render_widget( $form_id ) {
    $site_key = get_option( 'vcaptcha_site_key', '' );
    $mode     = get_option( 'vcaptcha_mode', 'invisible' );
    if ( $mode === 'invisible' ) {
        printf( '<div class="v-captcha" data-sitekey="%s" style="display:none"></div>', esc_attr( $site_key ) );
    } else {
        printf( '<div class="v-captcha" data-sitekey="%s" style="margin:10px 0"></div>', esc_attr( $site_key ) );
    }
}

function vcaptcha_give_verify( $valid_data ) {
    $site_key   = get_option( 'vcaptcha_site_key', '' );
    $secret_key = get_option( 'vcaptcha_secret_key', '' );
    if ( $site_key === '' || $secret_key === '' ) return;

    $token     = isset( $_POST['vcaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['vcaptcha-response'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    $result    = vcaptcha_verify( $token, $remote_ip );

    if ( ! $result['success'] ) {
        give_set_error( 'vcaptcha_error', get_option( 'vcaptcha_error_message', 'Bot verification failed. Please try again.' ) );
    }
}
