<?php
/*
 * Plugin Name: Hamazon
 * Plugin URI: https://wordpress.org/plugins/wp-hamazon/
 * Description: An affiliate plugin specialized for amazon. Forked from tmkm-amazon.
 * Author: Fumiki Takahashi
 * Author URI: https://takahashifumiki.com
 * Version: 5.1.3
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Text Domain: hamazon
 * Domain Path: /languages/
 * License: GPL3 or Later
*/

// Do not load directly
defined( 'ABSPATH' ) || die();

// Register initialization hook
add_action( 'plugins_loaded', 'hamazon_init' );

/**
 * Get plugin information
 *
 * @param string $key
 *
 * @return null
 */
function hamazon_info( $key ) {
	static $version = null;
	if ( is_null( $version ) ) {
		$version = get_file_data( __FILE__, array(
			'version' => 'Version',
			'php'     => 'Requires PHP',
		) );
	}
	return isset( $version[ $key ] ) ? $version[ $key ] : null;
}

/**
 * Initialization
 *
 * @package hamazon
 * @since 2.0.0
 */
function hamazon_init() {
	// Check PHP version
	if ( version_compare( phpversion(), hamazon_info( 'php' ), '<' ) ) {
		add_action( 'admin_notices', 'hamazon_warnings' );
		return;
	}
	// Load global functions
	require_once __DIR__ . '/functions.php';
	// Bootstrap at init action to ensure translations are loaded.
	add_action( 'init', 'hamazon_bootstrap' );
}

/**
 * Bootstrap plugin
 *
 * @package hamazon
 * @since 5.1.4
 */
function hamazon_bootstrap() {
	// Load translations.
	load_plugin_textdomain( 'hamazon', false, basename( dirname( __FILE__ ) ) . '/languages' );
	// Bootstrap
	require_once __DIR__ . '/vendor/autoload.php';
	Hametuha\WpHamazon\BootStrap::get_instance();
	// Register command on CLI environment.
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::add_command( 'hamazon', Hametuha\WpHamazon\Commands::class );
	}
}

/**
 * Notice for PHP version
 *
 * @internal
 * @package hamazon
 * @since 3.0.0
 */
function hamazon_warnings() {
	printf(
		'<div class="error"><p>%s</p></div>',
		sprintf(
			// translators: %1$s is PHP version, %2$s is current PHP version.
			esc_html__( 'Hamazon requires PHP %1$s and over, but your version is %2$s.', 'hamazon' ),
			hamazon_info( 'php' ),
			phpversion()
		)
	);
}
