<?php

namespace Hametuha\WpHamazon;


use Hametuha\WpHamazon\Pattern\AbstractService;
use Hametuha\WpHamazon\Pattern\Singleton;

/**
 * Bootstrap file
 *
 * @package Hametuha\WpHamazon
 */
class BootStrap extends Singleton {

	/**
	 * Active services
	 *
	 * @var array
	 */
	protected $active_services = array();

	/**
	 * @var AbstractService[]
	 */
	public $service_instances = array();

	/**
	 * Singleton constructor.
	 */
	protected function __construct() {
		// Setup options API
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		// Register general setting
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		// Register assets
		add_action( 'init', array( $this, 'register_assets' ), 20 );
		// Add Action links on plugin lists.
		add_filter( 'plugin_action_links', array( $this, 'plugin_page_link' ), 500, 2 );
		// Load public CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ) );
		// Scan all services and enables
		$dir             = __DIR__ . '/Service';
		$name_space_root = 'Hametuha\\WpHamazon\\Service\\';
		foreach ( scandir( $dir ) as $file ) {
			if ( preg_match( '#^([^._].*)\.php$#u', $file, $matches ) ) {
				$class_name = $name_space_root . $matches[1];
				if ( class_exists( $class_name ) ) {
					/** @var AbstractService $class_name */
					$service = $class_name::get_instance();
					if ( $service->is_valid() ) {
						$this->active_services[ $service->name ]   = $service->title;
						$this->service_instances[ $service->name ] = $service;
					}
				}
			}
		}
		if ( ! get_option( 'hamazon_option_updated' ) ) {
			$this->convert_option();
		}
		if ( $this->active_services ) {
			// O.K, let's initiate media frame!
			$this->init_media_frame();
			// Add editor style.
			add_filter( 'mce_css', array( $this, 'mce_css' ), 10, 2 );
			// Add CSS
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ) );
		}
		$this->backward_compats();

		// Fire Gutenberg.
		BlockEditor::get_instance();
	}

	/**
	 * Initialize media frame
	 */
	public function init_media_frame() {
		add_action( 'media_buttons', array( $this, 'action_media_buttons' ) );
	}

	/**
	 * Resister menu
	 */
	public function admin_menu() {
		// Add a new menu under Options:
		add_options_page(
			__( 'Hamazon Affiliate Setting', 'hamazon' ),
			__( 'Affiliate Setting', 'hamazon' ),
			'manage_options',
			'wp-hamazon',
			array( $this, 'options_page' )
		);
		add_action( 'admin_enqueue_scripts', function ( $page ) {
			if ( 'settings_page_wp-hamazon' === $page ) {
				wp_enqueue_style( 'hamazon-admin' );
			}
		} );
	}

	/**
	 * Render option screen
	 */
	public function options_page() {
		include hamazon_root_dir() . '/templates/admin.php';
	}

	/**
	 * Add setting fields
	 */
	public function register_settings() {
		add_settings_section(
			'hamazon_setting_general',
			__( 'General Setting', 'hamazon' ),
			function () {
			},
			'wp-hamazon'
		);

		// Post types
		add_settings_field(
			'hamazon_post_types',
			__( 'Available Post Types', 'hamazon' ),
			function () {
				$post_types = array_filter( get_post_types( array( 'public' => true ), OBJECT ), function ( $post_type ) {
					return 'attachment' !== $post_type->name;
				} );
				/**
				 * hamazon_valid_post_types
				 *
				 * @param array $post_types Post type objects array.
				 */
				$post_types = apply_filters( 'hamazon_valid_post_types', $post_types );
				foreach ( $post_types as $post_type ) {
					printf(
						'<label class="hamazon-inline-block"><input type="checkbox" name="hamazon_post_types[]" value="%s" %s/> %s</label>',
						esc_attr( $post_type->name ),
						checked( in_array( $post_type->name, (array) get_option( 'hamazon_post_types', array() ), true ), true, false ),
						esc_html( $post_type->label )
					);
				}
			},
			'wp-hamazon',
			'hamazon_setting_general'
		);
		register_setting( 'wp-hamazon', 'hamazon_post_types' );

		// Load CSS
		add_settings_field(
			'hamazon_load_css',
			__( 'Load CSS', 'hamazon' ),
			function () {
				foreach ( array(
					'1' => __( 'Load CSS', 'hamazon' ),
					''  => __( 'No CSS', 'hamazon' ),
				) as $value => $label ) {
					printf(
						'<label class="hamazon-inline-block"><input type="radio" name="hamazon_load_css" value="%s" %s/> %s</label>',
						esc_attr( $value ),
						checked( get_option( 'hamazon_load_css', 1 ), $value, false ),
						esc_html( $label )
					);
				}
				?>
				<p class="description">
					<?php
					esc_html_e( 'If you need original CSS, put "tmkm-amazon.css" in your theme folder. It will override default CSS.', 'hamazon' );
					// translators: %s is "No CSS"
					printf( esc_html__( 'If you choose "%s", nothing will be loaded.', 'hamazon' ), esc_html__( 'No CSS', 'hamazon' ) );
					?>
				</p>
				<?php
			},
			'wp-hamazon',
			'hamazon_setting_general'
		);
		register_setting( 'wp-hamazon', 'hamazon_load_css' );
	}

	/**
	 * Register assets
	 */
	public function register_assets() {
		$file = hamazon_root_dir() . '/wp-dependencies.json';
		if ( ! file_exists( $file ) ) {
			return;
		}
		$json = json_decode( file_get_contents( $file ), true );
		foreach ( $json as $setting ) {
			if ( empty( $setting['path'] ) ) {
				continue;
			}
			$path    = hamazon_root_dir() . '/' . $setting['path'];
			$version = file_exists( $path ) ? filemtime( $path ) : hamazon_info( 'version' );
			$url     = plugins_url( '', $path ) . '/' . basename( $path );
			$handle  = preg_replace( '/.(js|css)$/u', '', basename( $path ) );
			if ( 0 !== strpos( $handle, 'hamazon' ) ) {
				$handle = 'hamazon-' . $handle;
			}
			switch ( $setting['ext'] ) {
				case 'js':
					wp_register_script( $handle, $url, $setting['deps'], $version, true );
					break;
				case 'css':
					wp_register_style( $handle, $url, $setting['deps'], $version );
					break;
			}
		}
		wp_set_script_translations( 'hamazon-i18n', 'hamazon', hamazon_root_dir() . '/languages' );
		wp_localize_script( 'hamazon-i18n', 'Hamazon', array(
			'url'      => hamazon_asset_url( '' ),
			'services' => $this->service_data_for_script(),
		) );
		// Localize for Classic Editor
		wp_localize_script( 'hamazon-editor', 'HamazonEditor', array(
			'icon'     => hamazon_asset_url( 'img/button-icon.png' ),
			'btnLabel' => __( 'Affiliate', 'hamazon' ),
			'title'    => __( 'Affiliate Search', 'hamazon' ),
			'services' => $this->service_data_for_script(),
		) );
	}

	/**
	 * Add link on plugin list
	 *
	 * @param array $links
	 * @param string $file
	 *
	 * @return array
	 */
	public function plugin_page_link( $links, $file ) {
		if ( false !== strpos( $file, 'hamazon' ) ) {
			array_push( $links, '<a href="https://github.com/fumikito/wp-hamazon" target="_blank">Github</a>' );
			array_unshift( $links, sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'options-general.php?page=wp-hamazon' ),
				__( 'Setting', 'hamazon' )
			) );

		}

		return $links;
	}

	/**
	 * Load CSS
	 * @global array $hamazon_settings
	 */
	public function enqueue_script() {
		if ( ! is_admin() && get_option( 'hamazon_load_css' ) ) {
			list( $url, $version ) = $this->stylesheet_url();
			/**
			 * wp_hamazon_css_args
			 *
			 * Filter for WP Hamazon's CSS
			 *
			 * @param array $args handle, src, deps, version, media
			 * @return array
			 */
			$args = apply_filters( 'wp_hamazon_css_args', array(
				'handle'  => 'wp-hamazon',
				'src'     => $url,
				'deps'    => array(),
				'version' => $version,
				'media'   => 'all',
			) );
			if ( is_array( $args ) ) {
				wp_enqueue_style( $args['handle'], $args['src'], $args['deps'], $args['version'], $args['media'] );
			}
		}
	}

	/**
	 * Get style sheet
	 *
	 * @return array [ $url, $version ]
	 */
	protected function stylesheet_url() {
		if ( ! get_option( 'hamazon_load_css', 1 ) ) {
			return array();
		}
		$style = array(
			hamazon_asset_url( '/css/hamazon.css' ),
			hamazon_info( 'version' ),
		);
		foreach ( array(
			get_template_directory()   => get_template_directory_uri(),
			get_stylesheet_directory() => get_stylesheet_directory_uri(),
		) as $dir => $url ) {
			$path = $dir .= '/tmkm-amazon.css';
			if ( file_exists( $path ) ) {
				$style = array(
					$url . '/tmkm-amazon.css',
					filemtime( $path ),
				);
			}
		}
		return $style;
	}

	/**
	 * Register tinymce css.
	 *
	 * @param string $styles
	 * @param string $glue
	 * @return string
	 */
	public function mce_css( $styles, $glue = ' ,' ) {
		$css = $this->stylesheet_url();
		if ( $css ) {
			$styles .= $glue . $css[0];
		}
		return $styles;
	}

	/**
	 * Get localized scripts
	 *
	 * @param array $excludes
	 */
	public function load_hamazon_buttons( $excludes = array() ) {
		wp_enqueue_style( 'hamazon-editor' );
	}

	/**
	 * Get service array.
	 *
	 * @return array
	 */
	public function service_data_for_script() {
		return array_map( function ( $key, $value ) {
			$service = array(
				'key'   => $key,
				'label' => $value,
			);
			/**
			 * hamazon_service_variables
			 *
			 * Add service instance passed to react.
			 *
			 * @param mixed $data
			 * @param string $key
			 */
			$data            = apply_filters( 'hamazon_service_variables', null, $key );
			$service['data'] = $data;

			return $service;
		}, array_keys( $this->active_services ), array_values( $this->active_services ) );
	}

	/**
	 * Show media buttons
	 *
	 * @param string $editor_id
	 */
	public function action_media_buttons( $editor_id ) {
		static $counter = 0;

		if ( is_admin() && get_current_screen() && get_current_screen()->post_type && 'content' === $editor_id ) {
			$post_types = get_option( 'hamazon_post_types', array( 'post' ) );
			// Ensure $post_types is an array
			if ( ! is_array( $post_types ) ) {
				$post_types = array( 'post' );
			}
			if ( ! in_array( get_current_screen()->post_type, $post_types, true ) ) {
				return;
			}
		}
		// O.K. Let's move.
		++$counter;
		$this->load_hamazon_buttons();
		wp_enqueue_script( 'hamazon-editor-helper' );
		?>
		<div class="hamazon-btn-component" style="display: inline-block" id="hamazon-selector-<?php echo esc_attr( $counter ); ?>" data-editor-id="<?php echo esc_attr( $editor_id ); ?>">
		</div>
		<?php
	}

	/**
	 * Remove old short codes.
	 */
	public function backward_compats() {
		foreach ( array( 'tmkm-amazon-list', 'hamazon_linkshare', 'rakuten' ) as $code ) {
			$remove = apply_filters( 'hamazon_duplicated_short_code', true, $code );
			if ( $remove ) {
				add_shortcode( $code, function () {
					return '';
				} );
			}
		}
	}

	/**
	 * Update old option.
	 */
	public function convert_option() {
		$old_option = get_option( 'wp_tmkm_admin_options', array() );
		foreach ( array(
			'associatesid',
			'accessKey',
			'secretKey',
			'show_review',
			'post_types',
			'load_css',
			'phg_id',
			'dmm_affiliate_id',
			'dmm_api_id',
		) as $old_key ) {
			$new_key = "hamazon_{$old_key}";
			if ( isset( $old_option[ $old_key ] ) && $old_option[ $old_key ] ) {
				update_option( $new_key, $old_option[ $old_key ] );
			}
		}
		update_option( 'hamazon_option_updated', time() );
	}
}
