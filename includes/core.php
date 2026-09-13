<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WordPress core login and registration integration.
 */

add_action( 'plugins_loaded', 'vcaptcha_core_init' );

function vcaptcha_core_init() {
    $site_key   = get_option( 'vcaptcha_site_key', '' );
    $secret_key = get_option( 'vcaptcha_secret_key', '' );
    if ( $site_key === '' || $secret_key === '' ) return;

    $protect_login    = vcaptcha_integration_enabled( 'wp_login' );
    $protect_register = vcaptcha_integration_enabled( 'wp_register' );
    $protect_lostpass = vcaptcha_integration_enabled( 'wp_lostpass' );

    if ( ! $protect_login && ! $protect_register && ! $protect_lostpass ) return;

    if ( $protect_login ) {
        add_action( 'login_form', 'vcaptcha_core_render_widget' );
        add_filter( 'authenticate', 'vcaptcha_core_verify_login', 30, 3 );
    }

    if ( $protect_register ) {
        add_action( 'register_form', 'vcaptcha_core_render_widget' );
        add_filter( 'registration_errors', 'vcaptcha_core_verify_registration', 10, 3 );
    }

    if ( $protect_lostpass ) {
        add_action( 'lostpassword_form', 'vcaptcha_core_render_widget' );
        add_action( 'lostpassword_post', 'vcaptcha_core_verify_lostpassword' );
    }
}

function vcaptcha_core_render_widget() {
    $site_key = get_option( 'vcaptcha_site_key', '' );
    $mode     = get_option( 'vcaptcha_mode', 'invisible' );
    if ( $mode === 'invisible' ) {
        printf( '<div class="v-captcha" data-sitekey="%s" style="display:none"></div>', esc_attr( $site_key ) );
    } else {
        printf( '<div class="v-captcha" data-sitekey="%s" style="margin:10px 0"></div>', esc_attr( $site_key ) );
    }
}

function vcaptcha_core_verify_login( $user, $username, $password ) {
    if ( empty( $username ) ) return $user;
    $token     = isset( $_POST['vcaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['vcaptcha-response'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    $result    = vcaptcha_verify( $token, $remote_ip );
    if ( ! $result['success'] ) {
        return new WP_Error( 'vcaptcha_failed', get_option( 'vcaptcha_error_message', 'Bot verification failed. Please try again.' ) );
    }
    return $user;
}

function vcaptcha_core_verify_registration( $errors, $sanitized_user_login, $user_email ) {
    $token     = isset( $_POST['vcaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['vcaptcha-response'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    $result    = vcaptcha_verify( $token, $remote_ip );
    if ( ! $result['success'] ) {
        $errors->add( 'vcaptcha_error', get_option( 'vcaptcha_error_message', 'Bot verification failed. Please try again.' ) );
    }
    return $errors;
}

function vcaptcha_core_verify_lostpassword( $errors ) {
    $token     = isset( $_POST['vcaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['vcaptcha-response'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    $result    = vcaptcha_verify( $token, $remote_ip );
    if ( ! $result['success'] ) {
        $errors->add( 'vcaptcha_error', get_option( 'vcaptcha_error_message', 'Bot verification failed. Please try again.' ) );
    }
}
