<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Fluent Forms integration.
 * FF submits data as a URL-encoded string in $_POST['data']. // phpcs:ignore WordPress.Security.NonceVerification.Missing
 */

add_action( 'plugins_loaded', 'vcaptcha_ff_init' );

function vcaptcha_ff_init() {
    if ( ! function_exists( 'wpFluentForm' ) && ! defined( 'FLUENTFORM' ) ) return;
    if ( ! vcaptcha_integration_enabled( 'fluentforms' ) ) return;

    add_action( 'fluentform/render_item_submit_button', 'vcaptcha_ff_render_widget', 9, 2 );
    add_action( 'fluentform/before_insert_submission', 'vcaptcha_ff_verify', 10, 3 );
}

function vcaptcha_ff_render_widget( $submitButton, $form ) {
    $site_key = get_option( 'vcaptcha_site_key', '' );
    $mode     = get_option( 'vcaptcha_mode', 'invisible' );

    if ( $mode === 'invisible' ) {
        printf( '<div class="v-captcha" data-sitekey="%s" style="display:none"></div>', esc_attr( $site_key ) );
    } else {
        printf( '<div class="v-captcha" data-sitekey="%s" style="margin:10px 0"></div>', esc_attr( $site_key ) );
    }
}

function vcaptcha_ff_verify( $insertData, $data, $form ) {
    $site_key   = get_option( 'vcaptcha_site_key', '' );
    $secret_key = get_option( 'vcaptcha_secret_key', '' );
    if ( $site_key === '' || $secret_key === '' ) return;

    $token = '';
    if ( isset( $_POST['vcaptcha-response'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $token = sanitize_text_field( wp_unslash( $_POST['vcaptcha-response'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    } elseif ( isset( $_POST['data'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        parse_str( wp_unslash( $_POST['data'] ), $parsed ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        if ( isset( $parsed['vcaptcha-response'] ) ) {
            $token = sanitize_text_field( $parsed['vcaptcha-response'] );
        }
    }

    $remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    $result    = vcaptcha_verify( $token, $remote_ip );

    if ( ! $result['success'] ) {
        wp_send_json( array(
            'errors' => array(
                'vcaptcha' => get_option( 'vcaptcha_error_message', 'Bot verification failed. Please try again.' ),
            ),
        ), 422 );
        exit;
    }
}
