<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Central script enqueue for vCaptcha.
 * All integrations use this single enqueue — no integration file
 * should call wp_enqueue_script for vcaptcha directly.
 */
add_action( 'wp_enqueue_scripts', 'vcaptcha_enqueue_widget' );
add_action( 'login_enqueue_scripts', 'vcaptcha_enqueue_widget' );

function vcaptcha_enqueue_widget() {
    $site_key   = get_option( 'vcaptcha_site_key', '' );
    $secret_key = get_option( 'vcaptcha_secret_key', '' );
    if ( $site_key === '' || $secret_key === '' ) return;

    $mode = get_option( 'vcaptcha_mode', 'invisible' );

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
document.addEventListener('wpcf7mailsent', function() {
    document.querySelectorAll('input[name="vcaptcha-response"]').forEach(function(el) { el.value = ''; });
    document.querySelectorAll('.v-captcha').forEach(function(el) {
        el.removeAttribute('data-vc-init'); el._vcaptchaInstance = null;
    });
    vcaptchaInitAll();
});
document.addEventListener('wpformsProcessComplete', function() {
    document.querySelectorAll('input[name="vcaptcha-response"]').forEach(function(el) { el.value = ''; });
    document.querySelectorAll('.v-captcha').forEach(function(el) {
        el.removeAttribute('data-vc-init'); el._vcaptchaInstance = null;
    });
    vcaptchaInitAll();
});
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

/**
 * For checkbox mode: output the widget div that vCaptcha mounts to.
 */
function vcaptcha_widget_html(): string {
    $mode = get_option( 'vcaptcha_mode', 'invisible' );
    $site_key = get_option( 'vcaptcha_site_key', '' );
    if ( $mode === 'checkbox' ) {
        return '<div class="v-captcha" data-sitekey="' . esc_attr( $site_key ) . '"></div>';
    }
    return '<div class="v-captcha" data-sitekey="' . esc_attr( $site_key ) . '" style="display:none"></div>';
}
