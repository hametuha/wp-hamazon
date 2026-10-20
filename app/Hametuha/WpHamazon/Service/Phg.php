<?php

namespace Hametuha\WpHamazon\Service;


use Hametuha\WpHamazon\Pattern\AbstractService;

/**
 * PHG affiliate
 *
 * @package hamazon
 */
class Phg extends AbstractService {

	public $name = 'phg';

	public $title = 'PHG iTunes';

	/**
	 * PHG's endpoint
	 * @see http://www.apple.com/itunes/affiliates/resources/documentation/itunes-store-web-service-search-api.html
	 */
	const SEARCH_API = 'https://itunes.apple.com/search';

	/**
	 * PHG's endpoint
	 * @see http://www.apple.com/itunes/affiliates/resources/documentation/itunes-store-web-service-search-api.html
	 */
	const LOOKUP_API = 'https://itunes.apple.com/lookup';

	/**
	 * @var int
	 */
	private $per_page = 200;

	/**
	 * Get default options
	 *
	 * @return array
	 */
	protected function get_option_names() {
		return array(
			array(
				'key'         => 'phg_id',
				'label'       => __( 'Affiliate ID', 'hamazon' ),
				// translators: %1$s is label, %2$s is affiliate ID link.
				'description' => sprintf( __( 'You can get %1$s <a href="%2$s" target="_blank">here</a>.', 'hamazon' ), __( 'Affiliate ID', 'hamazon' ), 'https://www.apple.com/jp/itunes/affiliates/' ),
			),
		);
	}

	public function short_code_setting() {
		return array(
			'phg' => array(
				array(
					'label' => 'ID',
					'attr'  => 'id',
					'type'  => 'text',
				),
				array(
					'label' => __( 'Kind', 'hamazon' ),
					'attr'  => 'kind',
					'type'  => 'text',
				),
			),
		);
	}

	public function short_code_callback( $short_code_name, array $attributes = array(), $content = '' ) {
		switch ( $short_code_name ) {
			case 'phg':
				$item = $this->find_product( $attributes['id'] );
				if ( is_wp_error( $item ) ) {
					return $item;
				}
				$out = hamazon_template( 'phg', 'single', array(
					'item'    => $item,
					'content' => $content,
					'price'   => $this->format_price( $item ),
					'kind'    => $this->format_kind( $item ),
					'link'    => $this->affiliate_url( $item ),
					'image'   => $this->artwork_url( $item ),
					'artist'  => $this->format_artist_name( $item ),
				) );
				/**
				 * wp_hamazon_phg
				 *
				 * Filter output of PHG
				 *
				 * @param string $html
				 * @param \stdClass $item
				 * @param string $content
				 * @return string
				 */
				$out = apply_filters( 'wp_hamazon_phg', $out, $item, $content );
				return $out;
				break;
			default:
				return '';
				break;
		}
	}

	/**
	 * Detect if this service is valid.
	 *
	 * @return bool
	 */
	public function is_valid() {
		return (bool) $this->get_option( 'phg_id' );
	}

	/**
	 * Filter data passed to react.
	 *
	 * @param $data
	 * @return array|\stdClass
	 */
	protected function filter_data( $data ) {
		$countries = $this->get_countries();
		$media     = $this->get_genre();
		return array(
			'countries' => array_map( function ( $key, $value ) {
				return array(
					'key'   => $key,
					'label' => $value,
				);
			}, array_keys( $countries ), array_values( $countries ) ),
			'media'     => array_map( function ( $key, $value ) {
				return array(
					'key'   => $key,
					'label' => $value,
				);
			}, array_keys( $media ), array_values( $media ) ),
		);
	}

	/**
	 * Get rest arguments.
	 *
	 * @return array
	 */
	public function get_rest_arguments() {
		return array(
			'keyword' => array(
				'required'          => true,
				'description'       => __( 'Search keyword.', 'hamazon' ),
				'validate_callback' => function ( $keyword ) {
					return ! empty( $keyword );
				},
			),
			'media'   => array(
				'required'            => true,
				'default'             => 'all',
				'description'         => __( 'Genre', 'hamazon' ),
				'validation_callback' => function ( $media ) {
					return array_key_exists( $media, $this->get_genre() );
				},
			),
			'country' => array(
				'default'             => 'US',
				'required'            => true,
				'description'         => __( 'Country code', 'hamazon' ),
				'validation_callback' => function ( $country ) {
					return in_array( $country, array_keys( $this->get_countries() ), true );
				},
			),
		);
	}

	/**
	 * Get countries name
	 *
	 * @return array
	 */
	protected function get_countries() {
		/**
		 * hamazon_phg_countries
		 *
		 * Country codes for iTunes affiliate.
		 *
		 * @see {https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2}
		 * @param array $countries An array like [ 'US' => 'USA' ]
		 * @return array
		 */
		return apply_filters( 'hamazon_phg_countries', array(
			'US' => 'USA',
			'JP' => '日本',
			'GB' => 'United Kingdom',
			'DD' => 'Deutschland',
			'FR' => 'France',
			'ES' => 'España',
			'IT' => 'Italia',
		) );
	}

	/**
	 * Handle Amazon search request
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \SimpleXMLElement|\WP_Error|\WP_REST_Response
	 */
	public function handle_rest_request( \WP_REST_Request $request ) {
		$args          = array(
			'media'   => $request['media'],
			'term'    => $request['keyword'],
			'lang'    => 'ja' === get_locale() ? 'ja_jp' : 'en_us',
			'country' => $request['country'],
			'limit'   => $this->per_page,
		);
		$search_result = $this->make_request( self::SEARCH_API, $args );
		if ( is_wp_error( $search_result ) ) {
			$search_result->add_data( array(
				'response' => 500,
			) );
			return $search_result;
		}
		return new \WP_REST_Response( array(
			'total_page'   => ceil( $search_result->resultCount / $this->per_page ),
			'total_result' => (int) $search_result->resultCount,
			'items'        => array_map( function ( $item ) use ( $args ) {
				return array(
					'title'      => isset( $item->trackName ) ? $item->trackName : '',
					'category'   => $this->format_kind( $item ),
					'id'         => isset( $item->trackId ) ? $item->trackId : $item->collectionId,
					'price'      => $this->format_price( $item ),
					'author'     => $this->format_artist_name( $item ),
					'attributes' => $item,
					'image'      => $this->artwork_url( $item ),
					'url'        => $this->affiliate_url( $item ),
				);
			}, $search_result->results ),
		) );
	}

	/**
	 * Get available keywords.
	 *
	 * @param \stdClass $item
	 * @param array     $keys
	 * @param string    $default_value
	 * @return string
	 */
	private function get_available_attributes( $item, $keys, $default_value ) {
		foreach ( $keys as $key ) {
			if ( isset( $item->{$key} ) ) {
				$default_value = $item->{$key};
				break;
			}
		}
		return $default_value;
	}

	/**
	 * Get artist name
	 *
	 * @param \stdClass $item
	 * @return string
	 */
	public function format_artist_name( $item ) {
		return $this->get_available_attributes( $item, array( 'artistName', 'sellerName' ), _x( 'Unknown', 'search_result', 'hamazon' ) );
	}

	/**
	 * Format price
	 *
	 * @param \stdClass $item
	 * @return string
	 */
	protected function format_price( $item ) {
		if ( isset( $item->formattedPrice ) ) {
			return $item->formattedPrice;
		} elseif ( isset( $item->trackPrice ) ) {
			return number_format( $item->trackPrice ) . ' ' . $item->currency;
		} else {
			return '---';
		}
	}

	/**
	 * Get artwork image.
	 *
	 * @param \stdClass $item
	 * @return string
	 */
	protected function artwork_url( $item ) {
		return $this->get_available_attributes( $item, array_map( function ( $size ) {
			return "artworkUrl{$size}";
		}, array( 1024, 600, 512, 100, 60 ) ), hamazon_no_image() );
	}

	/**
	 * Get request from DMM
	 *
	 * @param string $api
	 * @param array $args
	 * @return array|mixed|object|\WP_Error
	 */
	public function make_request( $api, $args = array() ) {
		$queries = array();
		$url     = add_query_arg( $args, $api );
		// Make Request
		$default_time_out = apply_filters( 'hamazon_default_timeout', 30, $args, 'phg' );
		$result           = wp_remote_get( $url, array(
			'timeout' => $default_time_out,
		) );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		$json = json_decode( $result['body'] );
		if ( ! $json ) {
			return new \WP_Error( 'hamazon_phg_failed', __( 'Failed to get iTunes response.', 'hamazon' ), array(
				'status' => 500,
			) );
		}
		return $json;
	}

	/**
	 * Get item information
	 *
	 * @param string $id
	 * @param bool   $cache
	 * @return array|mixed|object|\WP_Error
	 */
	public function find_product( $id, $cache = true ) {
		$lang = ( 'ja' === get_locale() ) ? 'ja_jp' : 'en_us';
		$args = array(
			'id'      => $id,
			'country' => strtoupper( explode( '_', $lang )[1] ),
			'lang'    => $lang,
		);
		$key  = "hamazon_phg_{$id}";
		if ( $cache ) {
			$transient = get_transient( $key );
			if ( false !== $transient ) {
				return $transient;
			}
		}
		$result = $this->make_request( self::LOOKUP_API, $args );
		if ( is_wp_error( $result ) ) {
			return $result;
		} elseif ( ! $result->resultCount ) {
			return new \WP_Error( '404', __( 'Sorry, but item not found.' ) );
		} else {
			$item = $result->results[0];
			set_transient( $key, $item, 60 * 60 * 24 );
			return $item;
		}
	}

	/**
	 * Get kind label
	 *
	 * @param \stdClass $item
	 * @return string
	 */
	public function format_kind( $item ) {
		$kind = isset( $item->kind ) ? $item->kind : $item->wrapperType;
		switch ( $kind ) {
			case 'tv-episode':
				$kind = 'tvShow';
				break;
			case 'podcast-episode':
				$kind = 'podcast';
				break;
			case 'software-package':
				$kind = 'software';
				break;
			case 'music-video':
				$kind = 'musicVideo';
				break;
			case 'feature-movie':
				$kind = 'movie';
				break;
			case 'song':
				$kind = 'music';
				break;
		}
		$genre = $this->get_genre();
		if ( isset( $genre[ $kind ] ) ) {
			return $genre[ $kind ];
		}
		$others = array(
			'book'                => __( 'Book', 'hamazon' ),
			'album'               => __( 'Album', 'hamazon' ),
			'coached-audio'       => __( 'Coached Audio', 'hamazon' ),
			'interactive-booklet' => __( 'Interactive Booklet', 'hamazon' ),
			'pdf'                 => 'PDF',
		);
		return isset( $others[ $kind ] ) ? $others[ $kind ] : $kind;
	}

	/**
	 * Retrun genres
	 *
	 * @return array
	 */
	public function get_genre() {
		return array(
			'all'        => __( 'All', 'hamazon' ),
			'movie'      => __( 'Movie', 'hamazon' ),
			'podcast'    => __( 'Podcast', 'hamazon' ),
			'music'      => __( 'Music', 'hamazon' ),
			'musicVideo' => __( 'Music Video', 'hamazon' ),
			'audiobook'  => __( 'Audio Book', 'hamazon' ),
			'shortFilm'  => __( 'Short Film', 'hamazon' ),
			'tvShow'     => __( 'TV Show', 'hamazon' ),
			'software'   => __( 'Software', 'hamazon' ),
			'ebook'      => __( 'eBook', 'hamazon' ),
		);
	}

	/**
	 * Get affiliate link.
	 *
	 * @see http://www.apple.com/itunes/affiliates/resources/documentation/basic-affiliate-link-guidelines-for-the-phg-network-jp.html
	 * @param Object $item
	 * @return string
	 */
	public function affiliate_url( $item ) {
		if ( isset( $item->trackViewUrl ) ) {
			$url = $item->trackViewUrl;
		} elseif ( isset( $item->collectionViewUrl ) ) {
			$url = $item->collectionViewUrl;
		} else {
			return false;
		}
		return add_query_arg( array(
			'at' => $this->get_option( 'phg_id' ),
			'ct' => is_admin() ? 'wphamazon-admin' : 'wphamazon',
		), $url );
	}
}
