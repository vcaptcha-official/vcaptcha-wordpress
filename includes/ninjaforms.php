<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Ninja Forms integration.
 * NF renders via Vue.js so we poll for the form to appear.
 * Uses ninja_forms_display_before_form filter to enqueue scripts.
 */

add_action( 'plugins_loaded', 'vcaptcha_nf_init' );

function vcaptcha_nf_init() {
    if ( ! class_exists( 'Ninja_Forms' ) ) return;
    if ( ! vcaptcha_integration_enabled( 'ninjaforms' ) ) return;

    add_filter( 'ninja_forms_display_before_form', 'vcaptcha_nf_enqueue', 10, 2 );
    add_filter( 'ninja_forms_submit_data', 'vcaptcha_nf_verify' );
}

/**
 * Enqueue scripts when a NF form is displayed.
 * Uses the ninja_forms_display_before_form filter which passes $html and $form_id.
 */
function vcaptcha_nf_enqueue( $html, $form_id ) {
    static $enqueued = false;
    if ( $enqueued ) return $html;
    $enqueued = true;

    $site_key = get_option( 'vcaptcha_site_key', '' );
    $mode     = get_option( 'vcaptcha_mode', 'invisible' );

    if ( $site_key === '' ) return $html;

    // widget.php handles the main enqueue — we just need the NF polling script
    $mode_js = esc_js( $mode );
    $sitekey = esc_js( $site_key );

    add_action( 'wp_footer', function() use ( $sitekey, $mode_js ) {
        ?>
        <script>
        (function() {
            var VCAPTCHA_SITEKEY = '<?php echo esc_js( $sitekey ); ?>';
            var VCAPTCHA_MODE    = '<?php echo esc_js( $mode_js ); ?>';

            function injectWidget(form) {
                if (form.querySelector('.v-captcha')) return;

                var el = document.createElement('div');
                el.className = 'v-captcha';
                el.setAttribute('data-sitekey', VCAPTCHA_SITEKEY);

                if (VCAPTCHA_MODE === 'invisible') {
                    el.style.display = 'none';
                }

                var submitWrap = form.querySelector('.submit-container');
                if (submitWrap) {
                    submitWrap.parentNode.insertBefore(el, submitWrap);
                } else {
                    form.appendChild(el);
                }

                function tryInit() {
                    if (typeof vCaptcha === 'undefined') {
                        setTimeout(tryInit, 100);
                        return;
                    }
                    if (el._vcaptchaInstance) return;
                    var instance = new vCaptcha(el);
                    if (VCAPTCHA_MODE === 'checkbox') {
                        if (!el.querySelector('.lc-box')) instance._render();
                    }
                    el._vcaptchaInstance = instance;

                    if (VCAPTCHA_MODE === 'invisible') {
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
                    }
                }
                tryInit();
            }

            function pollForForms() {
                document.querySelectorAll('.nf-form-cont').forEach(function(container) {
                    var form = container.querySelector('form');
                    if (form && !form.querySelector('.v-captcha')) {
                        injectWidget(form);
                    }
                });
                setTimeout(pollForForms, 500);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', pollForForms);
            } else {
                pollForForms();
            }
        })();
        </script>
        <?php
    }, 99 );

    return $html;
}

/**
 * Verify the vCaptcha token on Ninja Forms submission.
 */
function vcaptcha_nf_verify( $form_data ) {
    $site_key   = get_option( 'vcaptcha_site_key', '' );
    $secret_key = get_option( 'vcaptcha_secret_key', '' );
    if ( $site_key === '' || $secret_key === '' ) return $form_data;

    $token     = isset( $_POST['vcaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['vcaptcha-response'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    $result    = vcaptcha_verify( $token, $remote_ip );

    if ( ! $result['success'] ) {
        $form_data['errors']['fields']['vcaptcha'] = get_option( 'vcaptcha_error_message', 'Bot verification failed. Please try again.' );
    }

    return $form_data;
}
