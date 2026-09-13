<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Verify a vCaptcha token with the siteverify endpoint.
 *
 * @param string $token     The vcaptcha-response token from the form submission.
 * @param string $remote_ip Optional visitor IP for logging.
 *
 * @return array {
 *   bool   $success
 *   int    $score
 *   string $method
 *   array  $error_codes
 * }
 */
function vcaptcha_verify( string $token, string $remote_ip = '' ): array {
    $secret = get_option( 'vcaptcha_secret_key', '' );

    // Guard: not configured
    if ( $secret === '' ) {
        return [
            'success'     => false,
            'score'       => 0,
            'method'      => '',
            'error_codes' => [ 'missing-input-secret' ],
        ];
    }

    // Guard: empty token
    if ( trim( $token ) === '' ) {
        return [
            'success'     => false,
            'score'       => 0,
            'method'      => '',
            'error_codes' => [ 'missing-input-response' ],
        ];
    }

    // Build POST body
    $body = [
        'secret'   => $secret,
        'response' => $token,
    ];
    if ( $remote_ip !== '' ) {
        $body['remoteip'] = $remote_ip;
    }

    // Call siteverify using WordPress HTTP API
    $response = wp_remote_post( VCAPTCHA_API_URL, [
        'timeout' => 10,
        'body'    => $body,
    ] );

    // Handle connection errors
    if ( is_wp_error( $response ) ) {
        return [
            'success'     => false,
            'score'       => 0,
            'method'      => '',
            'error_codes' => [ 'connection-failed' ],
        ];
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( ! is_array( $body ) ) {
        return [
            'success'     => false,
            'score'       => 0,
            'method'      => '',
            'error_codes' => [ 'invalid-response' ],
        ];
    }

    $success = (bool) ( $body['success'] ?? false );
    $score   = (int)  ( $body['score']   ?? 0 );
    $method  = (string)( $body['method'] ?? '' );

    // Check minimum score threshold
    $min_score = (int) get_option( 'vcaptcha_min_score', 35 );
    if ( $success && $score < $min_score ) {
        $success = false;
        $body['error-codes'][] = 'score-too-low';
    }

    return [
        'success'     => $success,
        'score'       => $score,
        'method'      => $method,
        'error_codes' => (array) ( $body['error-codes'] ?? [] ),
    ];
}
