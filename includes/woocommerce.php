<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WooCommerce integration — checkout (classic + block) and registration.
 */

add_action( 'plugins_loaded', 'vcaptcha_woo_init' );

function vcaptcha_woo_init() {
    if ( ! function_exists( 'WC' ) ) return;

    $site_key   = get_option( 'vcaptcha_site_key', '' );
    $secret_key = get_option( 'vcaptcha_secret_key', '' );
    if ( $site_key === '' || $secret_key === '' ) return;

    $protect_checkout = vcaptcha_integration_enabled( 'woo_checkout' );
    $protect_register = vcaptcha_integration_enabled( 'woo_register' );

    if ( ! $protect_checkout && ! $protect_register ) return;

    if ( $protect_checkout ) {
        add_action( 'woocommerce_review_order_before_submit', 'vcaptcha_woo_render_widget' );
        add_action( 'woocommerce_checkout_process', 'vcaptcha_woo_verify_checkout' );
        add_filter( 'rest_authentication_errors', 'vcaptcha_woo_verify_block_checkout' );
    }

    if ( $protect_register ) {
        add_action( 'woocommerce_register_form', 'vcaptcha_woo_render_widget' );
        add_filter( 'woocommerce_registration_errors', 'vcaptcha_woo_verify_registration', 10, 3 );
    }
}

function vcaptcha_woo_render_widget() {
    $site_key = get_option( 'vcaptcha_site_key', '' );
    $mode     = get_option( 'vcaptcha_mode', 'invisible' );
    if ( $mode === 'invisible' ) {
        printf( '<div class="v-captcha" data-sitekey="%s" style="display:none"></div>', esc_attr( $site_key ) );
    } else {
        printf( '<div class="v-captcha" data-sitekey="%s" style="margin:10px 0"></div>', esc_attr( $site_key ) );
    }
}

function vcaptcha_woo_verify_checkout() {
    $token     = isset( $_POST['vcaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['vcaptcha-response'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    $result    = vcaptcha_verify( $token, $remote_ip );
    if ( ! $result['success'] ) {
        wc_add_notice( get_option( 'vcaptcha_error_message', 'Bot verification failed. Please try again.' ), 'error' );
    }
}

function vcaptcha_woo_verify_block_checkout( $result ) {
    if ( ! isset( $_SERVER['REQUEST_URI'] ) ) return $result;
    $uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
    if ( strpos( $uri, 'wc/store' ) === false || strpos( $uri, 'checkout' ) === false ) return $result;
    if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || $_SERVER['REQUEST_METHOD'] !== 'POST' ) return $result;

    $body  = json_decode( file_get_contents( 'php://input' ), true );
    $token = isset( $body['extensions']['vcaptcha']['token'] )
        ? sanitize_text_field( $body['extensions']['vcaptcha']['token'] )
        : ( isset( $_POST['vcaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['vcaptcha-response'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

    $remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    $verify    = vcaptcha_verify( $token, $remote_ip );

    if ( ! $verify['success'] ) {
        return new WP_Error(
            'vcaptcha_failed',
            get_option( 'vcaptcha_error_message', 'Bot verification failed. Please try again.' ),
            array( 'status' => 401 )
        );
    }
    return $result;
}

function vcaptcha_woo_verify_registration( $errors, $username, $email ) {
    $token     = isset( $_POST['vcaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['vcaptcha-response'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    $result    = vcaptcha_verify( $token, $remote_ip );
    if ( ! $result['success'] ) {
        $errors->add( 'vcaptcha_error', get_option( 'vcaptcha_error_message', 'Bot verification failed. Please try again.' ) );
    }
    return $errors;
}
