<?php

namespace Hametuha\WpHamazon\Pattern;

/**
 * Abstract class for Service
 *
 * @package hamazon
 */
abstract class AbstractService extends Singleton {

	/**
	 * Service name in alphanumeric format
	 *
	 * @var string
	 */
	public $name = 'service';

	/**
	 * Used for title
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * Returns array
	 *
	 * @return array format is [ 'key' => 'foo', 'default' => 'var', 'label' => 'hoge', 'description' => '', 'type' => 'text', 'options' => [] ]
	 */
	abstract protected function get_option_names();

	/**
	 * Get description
	 *
	 * @return string;
	 */
	protected function get_service_description() {
		return '';
	}

	/**
	 * Constructor
	 */
	protected function __construct() {
		// Register Option screen
		add_action( 'admin_init', array( $this, 'register_options' ), 11 );
		// Register REST API
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
		// Add data filter
		add_filter( 'hamazon_service_variables', function ( $data, $key ) {
			if ( $this->name === $key ) {
				$data = $this->filter_data( $data );
			}
			return $data;
		}, 1, 2 );
		add_action( 'init', array( $this, 'short_code_set_up' ), 12 );
	}

	/**
	 * Register short codes
	 */
	public function short_code_set_up() {
		if ( ! $this->is_valid() ) {
			return;
		}
		/**
		 * hamazon_shortcode_settings
		 *
		 * Filters shortcode setting
		 *
		 * @param array  $settings
		 * @param string $service
		 * @return array
		 */
		$short_codes = apply_filters( 'hamazon_shortcode_settings', $this->short_code_setting(), $this->name );
		foreach ( $short_codes as $short_code => $setting ) {
			// Register short code
			add_shortcode( $short_code, function ( $atts, $content = '' ) use ( $short_code, $setting ) {
				$default = array();
				foreach ( $setting as $attribute ) {
					$default[ $attribute['attr'] ] = isset( $attribute['default'] ) ? $attribute['default'] : '';
				}
				$atts   = shortcode_atts( $default, $atts, $short_code );
				$result = $this->short_code_callback( $short_code, $atts, $content );
				if ( is_wp_error( $result ) ) {
					$html = wp_kses_post( sprintf( '<p class="hamazon">%s</p>', $result->get_error_message() ) );
					/**
					 * hamazon_error_message_html
					 *
					 * Filter error message for hamazon.
					 *
					 * @param string    $html
					 * @param \WP_Error $result
					 * @param string    $service
					 * @return string
					 */
					return apply_filters( 'hamazon_error_message_html', $html, $result, $this->name );
				} else {
					return $result;
				}
			} );
			// Add for shortcake
			add_action( 'register_shortcode_ui', function () use ( $short_code, $setting ) {
				shortcode_ui_register_for_shortcode( $short_code, array(
					// translators: %s is Service name.
					'label'         => sprintf( __( '%s Affiliate Tag', 'hamazon' ), $this->title ),
					'listItemImage' => 'dashicons-money',
					'inner_content' => array(
						'label' => __( 'Content', 'hamazon' ),
					),
					'attrs'         => $setting,
				) );
			} );
			// Add block editor.
		}
	}

	/**
	 * Handle short code format
	 *
	 * @param $short_code_name
	 * @param array $attributes
	 * @param string $content
	 * @return string|\WP_Error
	 */
	abstract public function short_code_callback( $short_code_name, array $attributes = array(), $content = '' );

	/**
	 * Return short code settings
	 *
	 * @return array
	 */
	abstract public function short_code_setting();

	/**
	 * Filter data passed to react.
	 *
	 * @param $data
	 * @return array|\stdClass
	 */
	protected function filter_data( $data ) {
		return $data;
	}

	/**
	 * Register options
	 */
	public function register_options() {
		$section = "hamazon_setting_{$this->name}";
		$title   = $this->title . sprintf( '<span class="dashicons dashicons-%s"></span>', $this->is_valid() ? 'yes' : 'no' );
		if ( $this->is_valid() ) {
			$icon = sprintf( '<small class="valid"><span class="dashicons dashicons-yes"></span> %s</small>', esc_html__( 'Valid', 'hamazon' ) );
		} else {
			$icon = sprintf( '<small class="invalid"><span class="dashicons dashicons-no"></span> %s</small>', esc_html__( 'Invalid', 'hamazon' ) );
		}
		add_settings_section(
			$section,
			$this->title . $icon,
			function () {
				$desc = $this->get_service_description();
				if ( ! empty( $desc ) ) {
					printf( '<p class="descirptoin">%s</p>', wp_kses_post( $desc ) );
				}
			},
			'wp-hamazon'
		);
		foreach ( $this->get_option_names() as $option ) {
			$option      = wp_parse_args( $option, array(
				'type'        => 'text',
				'label'       => '',
				'default'     => '',
				'description' => '',
				'options'     => array(),
			) );
			$option_name = "hamazon_{$option['key']}";
			add_settings_field(
				$option_name,
				isset( $option['label'] ) ? $option['label'] : ucfirst( $option['label'] ),
				function () use ( $option, $option_name ) {
					$current_value = get_option( $option_name, $option['default'] );
					switch ( $option['type'] ) {
						case 'radio':
							foreach ( $option['options'] as $label => $value ) {
								printf(
									'<label class="hamazon-inline-block"><input type="radio" name="%s" value="%s" %s/> %s</label>',
									esc_attr( $option_name ),
									esc_attr( $value ),
									checked( $current_value, $value, false ),
									esc_html( $label )
								);
							}
							break;
						default:
							printf(
								'<input class="regular-text" type="%1$s" name="%2$s" id="%2$s" value="%3$s" />',
								esc_attr( $option['type'] ),
								esc_attr( $option_name ),
								esc_attr( $current_value )
							);
							break;
					}
					if ( $option['description'] ) {
						printf( '<p class="description">%s</p>', wp_kses_post( $option['description'] ) );
					}
				},
				'wp-hamazon',
				$section
			);
			register_setting( 'wp-hamazon', $option_name );
		}
	}

	/**
	 * Get specified option value
	 *
	 * @global array $hamazon_settings
	 * @param string $key
	 * @return string
	 */
	public function get_option( $key ) {
		foreach ( $this->get_option_names() as $option ) {
			if ( $key !== $option['key'] ) {
				continue;
			}
			$key     = "hamazon_{$option['key']}";
			$default = isset( $option['default'] ) ? $option['default'] : '';
			return get_option( $key, $default );
		}
	}

	/**
	 * Register REST endpoint
	 */
	public function rest_api_init() {
		if ( $this->is_valid() ) {
			register_rest_route( 'hamazon/v3', untrailingslashit( $this->name ), array(
				'method'              => 'GET',
				'callback'            => array( $this, 'handle_rest_request' ),
				'args'                => $this->get_rest_arguments(),
				'permission_callback' => null,
			) );
		}
	}

	/**
	 * Get rest arguments.
	 *
	 * @return array
	 */
	abstract public function get_rest_arguments();

	/**
	 * Request Handler
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	abstract public function handle_rest_request( \WP_REST_Request $request );
}
