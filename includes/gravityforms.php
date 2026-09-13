<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gravity Forms integration.
 */

add_action( 'plugins_loaded', 'vcaptcha_gf_init' );

function vcaptcha_gf_init() {
    if ( ! class_exists( 'GFForms' ) ) return;

    $site_key   = get_option( 'vcaptcha_site_key', '' );
    $secret_key = get_option( 'vcaptcha_secret_key', '' );
    if ( $site_key === '' || $secret_key === '' ) return;

    add_action( 'wp_enqueue_scripts', 'vcaptcha_gf_enqueue' );
    add_filter( 'gform_form_tag', 'vcaptcha_gf_inject_widget', 10, 2 );
    add_filter( 'gform_entry_is_spam', 'vcaptcha_gf_verify', 10, 3 );
}

function vcaptcha_gf_enqueue() {
    $site_key = get_option( 'vcaptcha_site_key', '' );
    $mode     = get_option( 'vcaptcha_mode', 'invisible' );

    wp_enqueue_script(
        'vcaptcha',
        'https://www.vcaptcha.com/1/api.js?k=' . urlencode( $site_key ),
        array(),
        VCAPTCHA_VERSION,
        array( 'strategy' => 'async' )
    );

    if ( $mode === 'invisible' ) {
        $inline = <<<'JSCODE'
function vcaptchaInitAll() {
    document.querySelectorAll('.v-captcha:not([data-vc-init])').forEach(function(el) {
        el.setAttribute('data-vc-init', '1');
        var instance = new vCaptcha(el);
        el._vcaptchaInstance = instance;
        var form = el.closest('form');
        if (!form) return;
        var submitBtn = form.querySelector('[type="submit"]');
        if (!submitBtn) return;
        submitBtn.addEventListener('click', function(e) {
            var tokenInput = form.querySelector('input[name="vcaptcha-response"]');
            if (tokenInput && tokenInput.value !== '') return;
            e.preventDefault();
            e.stopImmediatePropagation();
            el.addEventListener('vcaptcha:success', function handler() {
                el.removeEventListener('vcaptcha:success', handler);
                submitBtn.click();
            }, { once: true });
            instance._verify();
        });
    });
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', vcaptchaInitAll);
} else { vcaptchaInitAll(); }
JSCODE;
    } else {
        $inline = <<<'JSCODE'
function vcaptchaInitAll() {
    document.querySelectorAll('.v-captcha:not([data-vc-init])').forEach(function(el) {
        el.setAttribute('data-vc-init', '1');
        var instance = new vCaptcha(el);
        if (!el.querySelector('.lc-box')) instance._render();
        el._vcaptchaInstance = instance;
    });
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', vcaptchaInitAll);
} else { vcaptchaInitAll(); }
JSCODE;
    }
    wp_add_inline_script( 'vcaptcha', $inline, 'after' );
}

function vcaptcha_gf_inject_widget( $form_tag, $form ) {
    $site_key = get_option( 'vcaptcha_site_key', '' );
    $mode     = get_option( 'vcaptcha_mode', 'invisible' );

    if ( $mode === 'invisible' ) {
        $widget = sprintf( '<div class="v-captcha" data-sitekey="%s" style="display:none"></div>', esc_attr( $site_key ) );
    } else {
        $widget = sprintf( '<div class="v-captcha" data-sitekey="%s" style="margin:10px 0"></div>', esc_attr( $site_key ) );
    }

    // Inject after the opening form tag
    return $form_tag . $widget;
}

function vcaptcha_gf_verify( $is_spam, $form, $entry ) {
    if ( $is_spam ) return $is_spam;

    $token     = isset( $_POST['vcaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['vcaptcha-response'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    $result    = vcaptcha_verify( $token, $remote_ip );

    return ! $result['success'];
}
