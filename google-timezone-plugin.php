<?php
/**
 * ArrayPress - Google Maps Timezone Tester
 *
 * @package     ArrayPress\Google\Timezone
 * @author      David Sherlock
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @link        https://arraypress.com/
 * @since       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:         ArrayPress - Google Maps Timezone Tester
 * Plugin URI:          https://github.com/arraypress/google-timezone-plugin
 * Description:         A plugin to test and demonstrate the Google Maps Timezone API integration.
 * Version:             1.0.0
 * Requires at least:   6.7.1
 * Requires PHP:        7.4
 * Author:              David Sherlock
 * Author URI:          https://arraypress.com/
 * License:             GPL v2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:         arraypress-google-timezone
 * Domain Path:         /languages
 * Network:             false
 * Update URI:          false
 */

declare( strict_types=1 );

namespace ArrayPress\Google\Timezone;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class Plugin {

	/**
	 * API Client instance
	 *
	 * @var Client|null
	 */
	private ?Client $client = null;

	/**
	 * Hook name for the Google Timezone Detection admin page.
	 *
	 * @var string
	 */
	const MENU_HOOK = 'google_page_arraypress-google-timezone';

	/**
	 * Plugin constructor
	 */
	public function __construct() {
		// Load text domain for translations
		add_action( 'init', [ $this, 'load_textdomain' ] );

		// Admin hooks
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

		// Initialize client if API key exists
		$api_key = get_option( 'google_timezone_api_key' );
		if ( ! empty( $api_key ) ) {
			$this->client = new Client(
				$api_key,
				(bool) get_option( 'google_timezone_enable_cache', true ),
				(int) get_option( 'google_timezone_cache_duration', 86400 )
			);
		}
	}

	/**
	 * Load plugin textdomain
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'arraypress-google-timezone-detection',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}

	/**
	 * Enqueue admin assets
	 */
	public function enqueue_admin_assets( string $hook ): void {
		if ( $hook !== self::MENU_HOOK ) {
			return;
		}

		wp_enqueue_style(
			'google-timezone-test-admin',
			plugins_url( 'assets/css/admin.css', __FILE__ ),
			[],
			'1.0.0'
		);
	}

	/**
	 * Registers the Google menu and timezone detection submenu page in the WordPress admin.
	 *
	 * This method handles the creation of a shared Google menu across plugins (if it doesn't
	 * already exist) and adds the Timezone Detection tool as a submenu item. It also removes
	 * the default submenu item to prevent a blank landing page.
	 *
	 * @return void
	 */
	public function add_menu_page(): void {
		global $admin_page_hooks;

		if ( ! isset( $admin_page_hooks['arraypress-google'] ) ) {
			add_menu_page(
				__( 'Google', 'arraypress-google-timezone-detection' ),
				__( 'Google', 'arraypress-google-timezone-detection' ),
				'manage_options',
				'arraypress-google',
				null,
				'dashicons-google',
				30
			);
		}

		// Add the timezone submenu
		add_submenu_page(
			'arraypress-google',
			__( 'Timezone Detection', 'arraypress-google-timezone-detection' ),
			__( 'Timezone Detection', 'arraypress-google-timezone-detection' ),
			'manage_options',
			'arraypress-google-timezone-detection',
			[ $this, 'render_test_page' ]
		);

		// Remove the default submenu item
		remove_submenu_page( 'arraypress-google', 'arraypress-google' );
	}

	/**
	 * Register settings
	 */
	public function register_settings(): void {
		register_setting( 'timezone_settings', 'google_timezone_api_key' );
		register_setting( 'timezone_settings', 'google_timezone_enable_cache', 'bool' );
		register_setting( 'timezone_settings', 'google_timezone_cache_duration', 'int' );
	}

	/**
	 * Render test page
	 */
	public function render_test_page(): void {
		$results = $this->process_form_submissions();
		?>
        <div class="wrap timezone-test">
            <h1><?php _e( 'Google Maps Timezone API Test', 'arraypress-google-timezone-detection' ); ?></h1>

			<?php settings_errors( 'timezone_test' ); ?>

			<?php if ( empty( get_option( 'google_timezone_api_key' ) ) ): ?>
                <!-- API Key Form -->
                <div class="notice notice-warning">
                    <p><?php _e( 'Please enter your Google Maps API key to begin testing.', 'arraypress-google-timezone-detection' ); ?></p>
                </div>
				<?php $this->render_settings_form(); ?>
			<?php else: ?>
                <!-- Test Forms -->
                <div class="timezone-test-container">
                    <!-- Timezone Detection -->
                    <div class="timezone-test-section">
                        <h2><?php _e( 'Timezone Detection', 'arraypress-google-timezone-detection' ); ?></h2>
                        <form method="post" class="timezone-form">
							<?php wp_nonce_field( 'timezone_test' ); ?>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="latitude"><?php _e( 'Latitude', 'arraypress-google-timezone-detection' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" name="latitude" id="latitude" class="regular-text"
                                               value="37.4224764" step="any"
                                               placeholder="<?php esc_attr_e( 'Enter latitude...', 'arraypress-google-timezone-detection' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="longitude"><?php _e( 'Longitude', 'arraypress-google-timezone-detection' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" name="longitude" id="longitude" class="regular-text"
                                               value="-122.0842499" step="any"
                                               placeholder="<?php esc_attr_e( 'Enter longitude...', 'arraypress-google-timezone-detection' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="timestamp"><?php _e( 'Date/Time', 'arraypress-google-timezone-detection' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="datetime-local" name="timestamp" id="timestamp" class="regular-text">
                                        <p class="description">
											<?php _e( 'Optional. Leave blank for current time.', 'arraypress-google-timezone-detection' ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="language_code"><?php _e( 'Language', 'arraypress-google-timezone-detection' ); ?></label>
                                    </th>
                                    <td>
                                        <select name="language_code" id="language_code" class="regular-text">
                                            <option value=""><?php _e( 'Default', 'arraypress-google-timezone-detection' ); ?></option>
                                            <option value="en">English</option>
                                            <option value="fr">French</option>
                                            <option value="de">German</option>
                                            <option value="es">Spanish</option>
                                            <option value="it">Italian</option>
                                            <option value="ja">Japanese</option>
                                            <option value="ko">Korean</option>
                                            <option value="zh">Chinese</option>
                                        </select>
                                        <p class="description">
											<?php _e( 'Select preferred language for timezone names', 'arraypress-google-timezone-detection' ); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
							<?php submit_button( __( 'Get Timezone', 'arraypress-google-timezone-detection' ), 'primary', 'submit_timezone' ); ?>
                        </form>

						<?php if ( $results['timezone'] ): ?>
                            <h3><?php _e( 'Timezone Results', 'arraypress-google-timezone-detection' ); ?></h3>
							<?php $this->render_timezone_details( $results['timezone'] ); ?>
						<?php endif; ?>
                    </div>
                </div>

                <!-- Cache Management -->
                <div class="timezone-test-section">
                    <h2><?php _e( 'Cache Management', 'arraypress-google-timezone-detection' ); ?></h2>
                    <form method="post" class="timezone-form">
						<?php wp_nonce_field( 'timezone_test' ); ?>
                        <p class="description">
							<?php _e( 'Clear the cached timezone results. This will force new API requests for subsequent lookups.', 'arraypress-google-timezone-detection' ); ?>
                        </p>
						<?php submit_button( __( 'Clear Cache', 'arraypress-google-timezone-detection' ), 'delete', 'clear_cache' ); ?>
                    </form>
                </div>

                <!-- Settings -->
                <div class="timezone-test-section">
					<?php $this->render_settings_form(); ?>
                </div>
			<?php endif; ?>
        </div>
		<?php
	}

	/**
	 * Render settings form
	 */
	private function render_settings_form(): void {
		?>
        <h2><?php _e( 'Settings', 'arraypress-google-timezone-detection' ); ?></h2>
        <form method="post" class="timezone-form">
			<?php wp_nonce_field( 'timezone_api_key' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="google_timezone_api_key"><?php _e( 'API Key', 'arraypress-google-timezone-detection' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="google_timezone_api_key" id="google_timezone_api_key"
                               class="regular-text"
                               value="<?php echo esc_attr( get_option( 'google_timezone_api_key' ) ); ?>"
                               placeholder="<?php esc_attr_e( 'Enter your Google Maps API key...', 'arraypress-google-timezone-detection' ); ?>">
                        <p class="description">
							<?php _e( 'Your Google Maps API key. Required for making API requests.', 'arraypress-google-timezone-detection' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="google_timezone_enable_cache"><?php _e( 'Enable Cache', 'arraypress-google-timezone-detection' ); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="google_timezone_enable_cache"
                                   id="google_timezone_enable_cache"
                                   value="1" <?php checked( get_option( 'google_timezone_enable_cache', true ) ); ?>>
							<?php _e( 'Cache timezone results', 'arraypress-google-timezone-detection' ); ?>
                        </label>
                        <p class="description">
							<?php _e( 'Caching results can help reduce API usage and improve performance.', 'arraypress-google-timezone-detection' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="google_timezone_cache_duration"><?php _e( 'Cache Duration', 'arraypress-google-timezone-detection' ); ?></label>
                    </th>
                    <td>
                        <input type="number" name="google_timezone_cache_duration" id="google_timezone_cache_duration"
                               class="regular-text"
                               value="<?php echo esc_attr( get_option( 'google_timezone_cache_duration', 86400 ) ); ?>"
                               min="300" step="300">
                        <p class="description">
							<?php _e( 'How long to cache results in seconds. Default is 86400 (24 hours).', 'arraypress-google-timezone-detection' ); ?>
                        </p>
                    </td>
                </tr>
            </table>
			<?php submit_button(
				empty( get_option( 'google_timezone_api_key' ) )
					? __( 'Save Settings', 'arraypress-google-timezone-detection' )
					: __( 'Update Settings', 'arraypress-google-timezone-detection' ),
				'primary',
				'submit_api_key'
			); ?>
        </form>
		<?php
	}

	/**
	 * Process form submissions
	 */
	private function process_form_submissions(): array {
		$results = [
			'timezone' => null
		];

		if ( isset( $_POST['submit_api_key'] ) ) {
			check_admin_referer( 'timezone_api_key' );
			$api_key = sanitize_text_field( $_POST['google_timezone_api_key'] );
			$enable_cache = isset( $_POST['google_timezone_enable_cache'] );
			$cache_duration = (int) sanitize_text_field( $_POST['google_timezone_cache_duration'] );

			update_option( 'google_timezone_api_key', $api_key );
			update_option( 'google_timezone_enable_cache', $enable_cache );
			update_option( 'google_timezone_cache_duration', $cache_duration );

			$this->client = new Client( $api_key, $enable_cache, $cache_duration );
		}

		if ( ! $this->client ) {
			return $results;
		}

		// Process timezone detection test
		if ( isset( $_POST['submit_timezone'] ) ) {
			check_admin_referer( 'timezone_test' );

			$latitude = (float) sanitize_text_field( $_POST['latitude'] );
			$longitude = (float) sanitize_text_field( $_POST['longitude'] );
			$timestamp = ! empty( $_POST['timestamp'] ) ? strtotime( $_POST['timestamp'] ) : null;
			$language = ! empty( $_POST['language_code'] ) ? sanitize_text_field( $_POST['language_code'] ) : null;

			$results['timezone'] = $this->client->get_timezone( $latitude, $longitude, $timestamp, $language );
		}

		// Clear cache if requested
		if ( isset( $_POST['clear_cache'] ) ) {
			check_admin_referer( 'timezone_test' );
			$this->client->clear_cache();
			add_settings_error(
				'timezone_test',
				'cache_cleared',
				__( 'Cache cleared successfully', 'arraypress-google-timezone-detection' ),
				'success'
			);
		}

		return $results;
	}

	/**
	 * Render timezone details
	 */
	private function render_timezone_details( $result ): void {
		if ( is_wp_error( $result ) ) {
			?>
			<div class="notice notice-error">
				<p><?php echo esc_html( $result->get_error_message() ); ?></p>
			</div>
			<?php
			return;
		}

		?>
		<table class="widefat striped">
			<tbody>
			<tr>
				<th><?php _e( 'Timezone ID', 'arraypress-google-timezone-detection' ); ?></th>
				<td><?php echo esc_html( $result->get_timezone_id() ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Timezone Name', 'arraypress-google-timezone-detection' ); ?></th>
				<td><?php echo esc_html( $result->get_timezone_name() ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Abbreviated Name', 'arraypress-google-timezone-detection' ); ?></th>
				<td><?php echo esc_html( $result->get_abbreviated_name() ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Raw UTC Offset', 'arraypress-google-timezone-detection' ); ?></th>
				<td><?php echo esc_html( $result->get_raw_offset() . ' seconds' ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'DST Offset', 'arraypress-google-timezone-detection' ); ?></th>
				<td><?php echo esc_html( $result->get_dst_offset() . ' seconds' ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Total Offset', 'arraypress-google-timezone-detection' ); ?></th>
				<td><?php echo esc_html( $result->get_total_offset() . ' seconds' ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Formatted Offset', 'arraypress-google-timezone-detection' ); ?></th>
				<td><?php echo esc_html( $result->get_formatted_offset() ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Is DST Active', 'arraypress-google-timezone-detection' ); ?></th>
				<td><?php echo $result->is_dst() ? 'Yes' : 'No'; ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Local Date/Time', 'arraypress-google-timezone-detection' ); ?></th>
				<td>
					<?php
					$datetime = $result->get_datetime();
					echo $datetime ? esc_html( $datetime->format( 'Y-m-d H:i:s' ) ) : 'N/A';
					?>
				</td>
			</tr>
			</tbody>
		</table>
		<?php
	}

}

new Plugin();