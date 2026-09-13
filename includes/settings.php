<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── Register settings ─────────────────────────────────────────────────────────

add_action( 'admin_init', 'vcaptcha_register_settings' );
function vcaptcha_register_settings() {
    register_setting( 'vcaptcha_settings', 'vcaptcha_site_key',      [ 'sanitize_callback' => 'sanitize_text_field' ] );
    register_setting( 'vcaptcha_settings', 'vcaptcha_secret_key',    [ 'sanitize_callback' => 'sanitize_text_field' ] );
    register_setting( 'vcaptcha_settings', 'vcaptcha_mode',          [ 'sanitize_callback' => 'sanitize_text_field' ] );
    register_setting( 'vcaptcha_settings', 'vcaptcha_min_score',     [ 'sanitize_callback' => 'absint' ] );
    register_setting( 'vcaptcha_settings', 'vcaptcha_error_message', [ 'sanitize_callback' => 'sanitize_text_field' ] );

    // Per-integration toggles
    $integrations = vcaptcha_get_integrations();
    foreach ( $integrations as $key => $integration ) {
        register_setting( 'vcaptcha_settings', 'vcaptcha_enable_' . $key, [ 'sanitize_callback' => 'absint' ] );
    }
}

/**
 * Return the list of all supported integrations with their detection callbacks.
 */
function vcaptcha_get_integrations() {
    return [
        'cf7'           => [ 'label' => 'Contact Form 7',              'detected' => class_exists( 'WPCF7' ) ],
        'wpforms'       => [ 'label' => 'WPForms',                     'detected' => function_exists( 'wpforms' ) ],
        'ninjaforms'    => [ 'label' => 'Ninja Forms',                 'detected' => class_exists( 'Ninja_Forms' ) ],
        'fluentforms'   => [ 'label' => 'Fluent Forms',                'detected' => function_exists( 'wpFluentForm' ) || defined( 'FLUENTFORM' ) ],
        'formidable'    => [ 'label' => 'Formidable Forms',            'detected' => class_exists( 'FrmForm' ) ],
        'woo_checkout'  => [ 'label' => 'WooCommerce Checkout',        'detected' => function_exists( 'WC' ) ],
        'woo_register'  => [ 'label' => 'WooCommerce Registration',    'detected' => function_exists( 'WC' ) ],
        'givewp'        => [ 'label' => 'GiveWP Donation Forms',       'detected' => function_exists( 'give' ) ],
        'wp_login'      => [ 'label' => 'WordPress Login',             'detected' => true ],
        'wp_register'   => [ 'label' => 'WordPress Registration',      'detected' => true ],
        'wp_lostpass'   => [ 'label' => 'WordPress Lost Password',     'detected' => true ],
    ];
}

/**
 * Helper: check if a specific integration is enabled.
 */
function vcaptcha_integration_enabled( $key ) {
    return (bool) get_option( 'vcaptcha_enable_' . $key, 1 ); // default on
}

// ── Add admin menu ────────────────────────────────────────────────────────────

add_action( 'admin_menu', 'vcaptcha_add_admin_menu' );
function vcaptcha_add_admin_menu() {
    add_options_page(
        'vCaptcha Settings',
        'vCaptcha',
        'manage_options',
        'vcaptcha',
        'vcaptcha_settings_page'
    );
}

// ── Settings page HTML ────────────────────────────────────────────────────────

function vcaptcha_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    // phpcs:disable WordPress.Security.NonceVerification.Recommended -- settings_fields() handles nonce verification
    $site_key      = get_option( 'vcaptcha_site_key', '' );
    $secret_key    = get_option( 'vcaptcha_secret_key', '' );
    $mode          = get_option( 'vcaptcha_mode', 'invisible' );
    $min_score     = (int) get_option( 'vcaptcha_min_score', 35 );
    $error_message = get_option( 'vcaptcha_error_message', 'Bot verification failed. Please try again.' );
    $configured    = ( $site_key !== '' && $secret_key !== '' );
    $integrations  = vcaptcha_get_integrations();
    ?>
    <div class="wrap">
      <h1>vCaptcha Settings</h1>

      <?php if ( isset( $_GET['settings-updated'] ) ): ?>
      <div class="notice notice-success is-dismissible">
        <p>Settings saved.</p>
      </div>
      <?php endif; ?>

      <?php if ( ! $configured ): ?>
      <div class="notice notice-warning">
        <p>
          <strong>vCaptcha is not yet configured.</strong>
          <a href="https://www.vcaptcha.com/dashboard/keys" target="_blank">Get your site key and secret key</a>
          from the vCaptcha dashboard, then enter them below.
        </p>
      </div>
      <?php endif; ?>

      <form method="post" action="options.php">
        <?php settings_fields( 'vcaptcha_settings' ); ?>

        <table class="form-table" role="presentation">

          <tr>
            <th scope="row"><label for="vcaptcha_site_key">Site Key</label></th>
            <td>
              <input type="text" id="vcaptcha_site_key" name="vcaptcha_site_key"
                     value="<?php echo esc_attr( $site_key ) ?>" class="regular-text">
              <p class="description">Found in your <a href="https://www.vcaptcha.com/dashboard/keys" target="_blank">vCaptcha dashboard</a> under Site Keys.</p>
            </td>
          </tr>

          <tr>
            <th scope="row"><label for="vcaptcha_secret_key">Secret Key</label></th>
            <td>
              <input type="password" id="vcaptcha_secret_key" name="vcaptcha_secret_key"
                     value="<?php echo esc_attr( $secret_key ) ?>" class="regular-text">
              <p class="description">Keep this private. Never expose it in client-side code.</p>
            </td>
          </tr>

          <tr>
            <th scope="row">Widget Mode</th>
            <td>
              <fieldset>
                <label>
                  <input type="radio" name="vcaptcha_mode" value="invisible"
                         <?php checked( $mode, 'invisible' ); ?>>
                  <strong>Invisible</strong> — widget is hidden, verification runs automatically on form submit.
                  Best for most sites.
                </label>
                <br><br>
                <label>
                  <input type="radio" name="vcaptcha_mode" value="checkbox"
                         <?php checked( $mode, 'checkbox' ); ?>>
                  <strong>Checkbox</strong> — shows a visible "I'm not a robot" style checkbox.
                  Use if you want visitors to know bot protection is active.
                </label>
              </fieldset>
            </td>
          </tr>

          <tr>
            <th scope="row"><label for="vcaptcha_min_score">Minimum Score</label></th>
            <td>
              <input type="number" id="vcaptcha_min_score" name="vcaptcha_min_score"
                     value="<?php echo esc_attr( $min_score ) ?>" min="0" max="100" class="small-text">
              <p class="description">
                Score from 0–100 (higher = more likely human). Submissions scoring below this
                threshold will be blocked. Default: 35. Raise to be stricter, lower to be more
                permissive.
              </p>
            </td>
          </tr>

          <tr>
            <th scope="row"><label for="vcaptcha_error_message">Error Message</label></th>
            <td>
              <input type="text" id="vcaptcha_error_message" name="vcaptcha_error_message"
                     value="<?php echo esc_attr( $error_message ) ?>" class="regular-text">
              <p class="description">Shown to visitors when bot verification fails.</p>
            </td>
          </tr>

          <tr>
            <th scope="row">Protected Forms</th>
            <td>
              <fieldset>
                <p class="description" style="margin-bottom:10px">
                  Choose which forms to protect. Only plugins that are installed and active are shown here.
                  All are enabled by default.
                </p>
                <?php foreach ( $integrations as $key => $integration ) :
                    if ( ! $integration['detected'] ) continue;
                    $enabled = vcaptcha_integration_enabled( $key );
                ?>
                <label style="display:block;margin-bottom:6px">
                  <input type="checkbox" name="vcaptcha_enable_<?php echo esc_attr( $key ) ?>" value="1"
                         <?php checked( $enabled, true ); ?>>
                  <?php echo esc_html( $integration['label'] ) ?>
                </label>
                <?php endforeach; ?>
              </fieldset>
            </td>
          </tr>

        </table>

        <?php submit_button(); ?>
      </form>

      <hr>
      <h2>Status</h2>
      <table class="form-table" role="presentation">
        <tr>
          <th>Configuration</th>
          <td>
            <?php if ( $configured ): ?>
            <span style="color:green">&#10003; Configured</span>
            <?php else: ?>
            <span style="color:#d63638">&#10007; Not configured — Plugin will not be active until keys are entered above</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php foreach ( $integrations as $key => $integration ) : ?>
        <tr>
          <th><?php echo esc_html( $integration['label'] ) ?></th>
          <td>
            <?php if ( $integration['detected'] ) : ?>
              <?php if ( vcaptcha_integration_enabled( $key ) ) : ?>
              <span style="color:green">&#10003; Active</span>
              <?php else : ?>
              <span style="color:#888">&#10007; Disabled</span>
              <?php endif; ?>
            <?php else : ?>
            <span style="color:#aaa">Not installed</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <tr>
          <th>Mode</th>
          <td><?php echo esc_html( ucfirst( $mode ) ) ?></td>
        </tr>
        <tr>
          <th>Minimum Score</th>
          <td><?php echo esc_html( $min_score ) ?> / 100</td>
        </tr>
      </table>

      <hr>
      <p>
        <a href="https://www.vcaptcha.com/docs" target="_blank">Documentation</a> &middot;
        <a href="https://www.vcaptcha.com/dashboard" target="_blank">Dashboard</a> &middot;
        <a href="https://www.vcaptcha.com/contact" target="_blank">Support</a>
      </p>
    </div>
    <?php
}
