<?php
/**
 * Google Maps Timezone API Client Class
 *
 * @package     ArrayPress/Utils
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Google\Timezone;

use WP_Error;

/**
 * Class Client
 *
 * A comprehensive utility class for interacting with the Google Maps Timezone API.
 */
class Client {

	/**
	 * API key for Google Maps
	 *
	 * @var string
	 */
	private string $api_key;

	/**
	 * Base URL for the Timezone API
	 *
	 * @var string
	 */
	private const API_ENDPOINT = 'https://maps.googleapis.com/maps/api/timezone/json';

	/**
	 * Whether to enable response caching
	 *
	 * @var bool
	 */
	private bool $enable_cache;

	/**
	 * Cache expiration time in seconds
	 *
	 * @var int
	 */
	private int $cache_expiration;

	/**
	 * Initialize the Timezone client
	 *
	 * @param string $api_key          API key for Google Maps
	 * @param bool   $enable_cache     Whether to enable caching (default: true)
	 * @param int    $cache_expiration Cache expiration in seconds (default: 24 hours)
	 */
	public function __construct( string $api_key, bool $enable_cache = true, int $cache_expiration = 86400 ) {
		$this->api_key          = $api_key;
		$this->enable_cache     = $enable_cache;
		$this->cache_expiration = $cache_expiration;
	}

	/**
	 * Get timezone information for a specific location
	 *
	 * @param float       $latitude  Latitude of the location
	 * @param float       $longitude Longitude of the location
	 * @param int|null    $timestamp Timestamp for historical timezone data (optional)
	 * @param string|null $language  Language code for localized responses (optional)
	 *
	 * @return Response|WP_Error Response object or WP_Error on failure
	 */
	public function get_timezone( float $latitude, float $longitude, ?int $timestamp = null, ?string $language = null ) {
		// Validate coordinates
		if ( $latitude < - 90 || $latitude > 90 ) {
			return new WP_Error(
				'invalid_latitude',
				__( 'Latitude must be between -90 and 90 degrees', 'arraypress' )
			);
		}

		if ( $longitude < - 180 || $longitude > 180 ) {
			return new WP_Error(
				'invalid_longitude',
				__( 'Longitude must be between -180 and 180 degrees', 'arraypress' )
			);
		}

		// Use current timestamp if none provided
		$timestamp = $timestamp ?? time();

		// Prepare request parameters
		$params = [
			'location'  => $latitude . ',' . $longitude,
			'timestamp' => $timestamp,
			'key'       => $this->api_key
		];

		// Add optional language parameter
		if ( $language ) {
			$params['language'] = $language;
		}

		// Generate cache key if caching is enabled
		$cache_key = null;
		if ( $this->enable_cache ) {
			$cache_key   = $this->get_cache_key( wp_json_encode( $params ) );
			$cached_data = get_transient( $cache_key );
			if ( false !== $cached_data ) {
				return new Response( $cached_data );
			}
		}

		$response = $this->make_request( $params );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Cache the response if caching is enabled
		if ( $this->enable_cache && $cache_key ) {
			set_transient( $cache_key, $response, $this->cache_expiration );
		}

		return new Response( $response );
	}

	/**
	 * Make a request to the Timezone API
	 *
	 * @param array $params Request parameters
	 *
	 * @return array|WP_Error Response array or WP_Error on failure
	 */
	private function make_request( array $params ) {
		$url = add_query_arg( $params, self::API_ENDPOINT );

		$response = wp_remote_get( $url, [
			'timeout' => 15,
			'headers' => [
				'Accept' => 'application/json'
			]
		] );

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'api_error',
				sprintf(
					__( 'Timezone API request failed: %s', 'arraypress' ),
					$response->get_error_message()
				)
			);
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code !== 200 ) {
			return new WP_Error(
				'api_error',
				sprintf(
					__( 'Timezone API returned error code: %d', 'arraypress' ),
					$response_code
				)
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error(
				'json_error',
				__( 'Failed to parse Timezone API response', 'arraypress' )
			);
		}

		// Check for API errors
		if ( isset( $data['status'] ) && $data['status'] !== 'OK' ) {
			return new WP_Error(
				'api_error',
				sprintf(
					__( 'Timezone API returned error: %s', 'arraypress' ),
					$data['status']
				)
			);
		}

		return $data;
	}

	/**
	 * Generate cache key
	 *
	 * @param string $identifier Cache identifier
	 *
	 * @return string Cache key
	 */
	private function get_cache_key( string $identifier ): string {
		return 'google_timezone_' . md5( $identifier . $this->api_key );
	}

	/**
	 * Clear cached data
	 *
	 * @param string|null $identifier Optional specific cache to clear
	 *
	 * @return bool True on success, false on failure
	 */
	public function clear_cache( ?string $identifier = null ): bool {
		if ( $identifier !== null ) {
			return delete_transient( $this->get_cache_key( $identifier ) );
		}

		global $wpdb;
		$pattern = $wpdb->esc_like( '_transient_google_timezone_' ) . '%';

		return $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
					$pattern
				)
			) !== false;
	}
}