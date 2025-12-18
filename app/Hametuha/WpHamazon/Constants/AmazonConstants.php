<?php

namespace Hametuha\WpHamazon\Constants;

use Amazon\ProductAdvertisingAPI\v1\ApiException;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item;
use Amazon\ProductAdvertisingAPI\v1\Configuration;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource;
use Hametuha\WpHamazon\Pattern\StaticPattern;
use Hametuha\WpHamazon\Service\Amazon;
use Tarosky\PlasticSearch\Api\Search;

/**
 * Amazon constants holder
 * @package hamazon
 * phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
 */
class AmazonConstants extends StaticPattern {

	/**
	 * Search Index values for AWS
	 *
	 * @return array
	 */
	public static function get_search_index() {
		static $search_index = null;
		if ( is_null( $search_index ) ) {
			$search_index = array(
				'All'                     => __( 'All', 'hamazon' ),
				'AmazonVideo'             => __( 'Prime Video', 'hamazon' ),
				'Apparel'                 => __( 'Apparel', 'hamazon' ),
				'Appliances'              => __( 'Appliances', 'hamazon' ),
				'Automotive'              => __( 'Car & Bike', 'hamazon' ),
				'Baby'                    => __( 'Baby', 'hamazon' ),
				'Beauty'                  => __( 'Beauty', 'hamazon' ),
				'Books'                   => __( 'Books', 'hamazon' ),
				'Classical'               => __( 'Classical', 'hamazon' ),
				'CreditCards'             => __( 'CreditCards', 'hamazon' ),
				'Computers'               => __( 'Computers', 'hamazon' ),
				'DigitalMusic'            => __( 'Digital Music', 'hamazon' ),
				'Electronics'             => __( 'Electronics', 'hamazon' ),
				'Fashion'                 => __( 'Fashion', 'hamazon' ),
				'ForeignBooks'            => __( 'Foreign Books', 'hamazon' ),
				'GiftCards'               => __( 'Gift Cards', 'hamazon' ),
				'GroceryAndGourmetFood'   => __( 'Food & Beverage', 'hamazon' ),
				'HealthPersonalCare'      => __( 'Health Personal Care', 'hamazon' ),
				'Hobbies'                 => __( 'Hobbies', 'hamazon' ),
				'HomeAndKitchen'          => __( 'Home & Kitchen', 'hamazon' ),
				'Industrial'              => __( 'Industrial', 'hamazon' ),
				'Jewelry'                 => __( 'Jewelry', 'hamazon' ),
				'KindleStore'             => __( 'Kindle Store', 'hamazon' ),
				'MobileApps'              => __( 'Mobile Apps', 'hamazon' ),
				'MoviesAndTV'             => __( 'Movies & TV', 'hamazon' ),
				'Music'                   => __( 'Music', 'hamazon' ),
				'MusicalInstruments'      => __( 'Musical Instruments', 'hamazon' ),
				'OfficeProducts'          => __( 'Office Products', 'hamazon' ),
				'PetSupplies'             => __( 'Pet Supplies', 'hamazon' ),
				'Shoes'                   => __( 'Shoes & Bags', 'hamazon' ),
				'Software'                => __( 'Software', 'hamazon' ),
				'SportsAndOutoors'        => __( 'Sports & Outdoors', 'hamazon' ),
				'ToolsAndHomeImprovement' => __( 'DIY & Gardening', 'hamazon' ),
				'Toys'                    => __( 'Toys', 'hamazon' ),
				'VideoGames'              => __( 'Video Games', 'hamazon' ),
				'Watches'                 => __( 'Watches', 'hamazon' ),
			);
		}

		return $search_index;
	}

	/**
	 * Search item with string.
	 *
	 * @since 5.0 Change return value.
	 *
	 * @param string $keyword
	 * @param int    $page
	 * @param string $index
	 * @param string $order
	 *
	 * @return \WP_Error|array
	 * @throws \Exception
	 */
	public static function search_with( $keyword, $page = 1, $index = 'ALL', $order = 'Relevance' ) {
		$config = self::get_config();
		if ( is_wp_error( $config ) ) {
			return $config;
		}
		$api_instance = new DefaultApi( new \GuzzleHttp\Client(), $config );
		$item_count   = 10;
		$page         = (int) min( 10, max( 1, $page ) );

		# Forming the request
		$request = new SearchItemsRequest();
		$request->setSearchIndex( $index );
		$request->setKeywords( $keyword );
		$request->setItemCount( $item_count );
		$request->setItemPage( $page );
		//      $request->setLanguagesOfPreference( AmazonLocales::get_language_locale() ); // This raises api error.
		$request->setPartnerTag( self::get_partner_tag() );
		$request->setPartnerType( PartnerType::ASSOCIATES );
		$request->setResources( self::get_resources() );
		$request->setSortBy( $order );
		$invalid_properties = self::validate_request( $request );
		if ( is_wp_error( $invalid_properties ) ) {
			return $invalid_properties;
		}

		# Sending the request
		try {
			$response = $api_instance->searchItems( $request );
			$errors   = $response->getErrors();
			if ( $errors ) {
				$error = new \WP_Error();
				foreach ( $errors as $e ) {
					$error->add( 'invalid_request', $e->getMessage(), array(
						'response' => $e->getCode(),
					) );
				}
				return $error;
			}

			$items   = $response->getSearchResult();
			$total   = $items ? $items->getTotalResultCount() : 0;
			$results = array(
				'total_page'   => ceil( $total / 10 ),
				'total_result' => $items->getTotalResultCount(),
				'items'        => array(),
			);
			if ( $items ) {
				foreach ( $items->getItems() as $item ) {
					$results['items'][] = self::convert_item( $item );
				}
			}
			return $results;
		} catch ( \Exception $exception ) {
			return new \WP_Error( 'api_request', sprintf( '[%s] %s', $exception->getCode(), $exception->getMessage() ) );
		}
	}

	/**
	 * Convert item to associative array.
	 *
	 * @param Item $item
	 * @return array
	 */
	public static function convert_item( $item ) {
		$info   = json_decode( $item, true );
		$node   = $item->getBrowseNodeInfo();
		$atts   = self::get_attributes( $info );
		$price  = 'N/A';
		$offers = $item->getOffers();
		if ( $item->getOffers() ) {
			foreach ( $item->getOffers()->getListings() as $offer ) {
				$price = $offer->getPrice()->getDisplayAmount();
				break;
			}
		}
		$date     = '';
		$date_gmt = '';
		foreach ( array(
			'ContentInfo' => 'PublicationDate',
			'ProductInfo' => 'ReleaseDate',
		) as $key => $sub_key ) {
			if ( ! empty( $info['ItemInfo'][ $key ][ $sub_key ]['DisplayValue'] ) ) {
				$date_gmt = $info['ItemInfo'][ $key ][ $sub_key ]['DisplayValue'];
				$date     = date_i18n( get_option( 'date_format' ), strtotime( $date_gmt ) );
				break;
			}
		}
		$images = $item->getImages();
		return apply_filters( 'hamazon_item_array', array(
			'title'      => (string) $item->getItemInfo()->getTitle()->getDisplayValue(),
			'rank'       => $node ? $item->getBrowseNodeInfo()->getWebsiteSalesRank()->getSalesRank() : '',
			'category'   => $node ? $item->getBrowseNodeInfo()->getWebsiteSalesRank()->getDisplayName() : '',
			'asin'       => $item->getASIN(),
			'price'      => $price,
			'attributes' => $atts,
			'date'       => $date,
			'date_gmt'   => $date_gmt,
			'image'      => $images ? $images->getPrimary()->getMedium()->getURL() : '',
			'images'     => array(
				'medium' => $images ? $images->getPrimary()->getMedium()->getURL() : '',
				'large'  => $images ? $images->getPrimary()->getLarge()->getURL() : '',
			),
			'url'        => $item->getDetailPageURL(),
		), $item );
	}

	/**
	 * Get item attributes
	 *
	 * @param array $item
	 *
	 * @return array
	 */
	public static function get_attributes( $item ) {
		$attributes = array();
		// Set contributors
		if ( ! empty( $item['ItemInfo']['ByLineInfo']['Contributors'] ) ) {
			foreach ( $item['ItemInfo']['ByLineInfo']['Contributors'] as $contributor ) {
				if ( ! isset( $attributes['contributors'] ) ) {
					$attributes['contributors'] = array();
				}
				$name = $contributor['Name'];
				$role = $contributor['Role'];
				if ( ! isset( $attributes['contributors'][ $role ] ) ) {
					$attributes['contributors'][ $role ] = array();
				}
				$attributes['contributors'][ $role ][] = $name;
			}
		}
		// Set brand & manufacturer
		foreach ( array( 'Brand', 'Manufacturer' ) as $key ) {
			$attributes[ strtolower( $key ) ] = ! empty( $item['ItemInfo']['ByLineInfo'][ $key ] )
				? $item['ItemInfo']['ByLineInfo'][ $key ]['DisplayValue'] : '';
		}
		// Product Info
		if ( ! empty( $item['ItemInfo']['ProductInfo']['IsAdultProduct']['DisplayValue'] ) ) {
			$attributes['is_adult'] = $item['ItemInfo']['ProductInfo']['IsAdultProduct']['DisplayValue'];
		} else {
			$attributes['is_adult'] = '';
		}
		return $attributes;
	}

	/**
	 * Get item from ASIN code.
	 *
	 * @since 5.0 Change return value.
	 * @param string $asin     ASIN code.
	 * @param bool   $fallback Whether to return fallback data on API error. Default true.
	 *
	 * @return array|\WP_Error
	 */
	public static function get_item_by_asin( $asin, $fallback = true ) {
		$config = self::get_config();
		if ( is_wp_error( $config ) ) {
			return $fallback ? self::get_fallback_item( $asin ) : $config;
		}
		$apiInstance = new DefaultApi(
		/*
		 * If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
		 * This is optional, `GuzzleHttp\Client` will be used as default.
		 */
			new \GuzzleHttp\Client(),
			$config
		);

		$item_ids = array( $asin );

		# Forming the request
		$request = new GetItemsRequest();
		$request->setItemIds( $item_ids );
		$request->setPartnerTag( self::get_partner_tag() );
		$request->setPartnerType( PartnerType::ASSOCIATES );
		$request->setResources( self::get_resources() );

		# Validating request
		$invalid_properties = self::validate_request( $request );
		if ( is_wp_error( $invalid_properties ) ) {
			return $fallback ? self::get_fallback_item( $asin ) : $invalid_properties;
		}

		# Sending the request
		try {
			$response = $apiInstance->getItems( $request );
			$errors   = $response->getErrors();
			if ( $errors ) {
				return $fallback ? self::get_fallback_item( $asin ) : new \WP_Error( 'invalid_request', $errors[0]->getMessage(), array( 'response' => $errors[0]->getCode() ) );
			}

			# Parsing the response
			if ( $response->getItemsResult() ) {
				foreach ( $response->getItemsResult()->getItems() as $item ) {
					return self::convert_item( $item );
				}
			}
			throw new \Exception( __( 'Sorry, but item not found.', 'hamazon' ) );
		} catch ( \Exception $exception ) {
			return $fallback ? self::get_fallback_item( $asin ) : new \WP_Error( 'api_request', sprintf( '[%s] %s', $exception->getCode(), $exception->getMessage() ) );
		}
	}

	/**
	 * Detect if string is ASIN
	 *
	 * @param $asin
	 *
	 * @return bool
	 */
	private static function is_asin( $asin ) {
		return (bool) preg_match( '/^[0-9a-zA-Z]{10,13}$/', trim( $asin ) );
	}


	/**
	 * Create HTML Source With Asin
	 *
	 * @param string $asin
	 * @param array $extra_atts
	 *
	 * @return string|\WP_Error
	 * @since 3.0.0 May return WP_Error
	 */
	public static function format_amazon( $asin, $extra_atts = array() ) {
		try {
			if ( self::is_asin( $asin ) ) {
				// Old format like [tmkm-amazon]000000000[/tmkm-amazon]
				$content = $extra_atts['description'];
			} elseif ( self::is_asin( $extra_atts['asin'] ) ) {
				// New format
				$content = $asin;
				$asin    = $extra_atts['asin'];
			} else {
				throw new \Exception( __( 'ASIN format is wrong.', 'hamazon' ), 400 );
			}

			$cache_key = 'amazon_api5_' . $asin;
			$cache     = get_transient( $cache_key );
			if ( false !== $cache ) {
				$item = $cache;
			} else {
				// Pass false to get WP_Error on failure, so we can handle fallback ourselves.
				$item = self::get_item_by_asin( $asin, false );
			}

			$is_fallback = false;
			if ( is_wp_error( $item ) ) {
				// API failed, use fallback.
				$item        = self::get_fallback_item( $asin );
				$is_fallback = true;
			} else {
				set_transient( $cache_key, $item, 60 * 60 * 24 );
			}

			$content = trim( $content );
			if ( ! empty( $content ) ) {
				$desc = sprintf( '<p class="additional-description">%s</p>', wp_kses_post( $content ) );
			} else {
				$desc = '';
			}

			// Choose template based on whether this is a fallback.
			$template = $is_fallback ? 'fallback' : 'single';
			$html     = hamazon_template( 'amazon', $template, array(
				'item'        => $item,
				'extra_atts'  => $extra_atts,
				'asin'        => $asin,
				'desc'        => $desc,
				'is_fallback' => $is_fallback,
			) );

			/**
			 * wp_hamazon_amazon
			 *
			 * Filter output of amazon
			 *
			 * @since 5.0 Change $item attributes to array.
			 * @param string $html
			 * @param array $item
			 * @param array $extra_atts
			 * @param string $content
			 *
			 * @return string
			 */
			return apply_filters( 'wp_hamazon_amazon', $html, $item, $extra_atts, $content );
		} catch ( \Exception $e ) {
			return new \WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Get fallback item data when API is unavailable.
	 *
	 * This method scrapes the Amazon product page to get the title
	 * and caches the result using transients.
	 *
	 * @param string $asin ASIN code.
	 * @return array Minimal item data for fallback display.
	 */
	public static function get_fallback_item( $asin ) {
		$service     = Amazon::get_instance();
		$locale      = $service->get_option( 'locale' ) ?: 'JP';
		$partner_tag = self::get_partner_tag();
		$cache_key   = 'hamazon_fallback_' . $asin . '_' . $locale;

		// Check transient cache first.
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$url   = AmazonLocales::get_product_url( $asin, $locale, $partner_tag );
		$title = self::scrape_amazon_title( $asin, $locale );

		$item = apply_filters( 'hamazon_fallback_item', array(
			'title'      => $title,
			'rank'       => '',
			'category'   => '',
			'asin'       => $asin,
			'price'      => '',
			'attributes' => array(),
			'date'       => '',
			'date_gmt'   => '',
			'image'      => '',
			'images'     => array(
				'medium' => '',
				'large'  => '',
			),
			'url'        => $url,
		), $asin, $locale, $partner_tag );

		// Cache for 7 days (fallback data doesn't change often).
		set_transient( $cache_key, $item, 7 * DAY_IN_SECONDS );

		return $item;
	}

	/**
	 * Scrape Amazon product page to get the title.
	 *
	 * @param string $asin   ASIN code.
	 * @param string $locale Locale code.
	 * @return string Product title or empty string on failure.
	 */
	private static function scrape_amazon_title( $asin, $locale ) {
		$domain = AmazonLocales::get_product_domain( $locale );
		$url    = sprintf( 'https://www.amazon.%s/dp/%s', $domain, $asin );

		$response = wp_remote_get( $url, array(
			'timeout'    => 10,
			'user-agent' => 'Mozilla/5.0 (compatible; WordPress/' . get_bloginfo( 'version' ) . ')',
		) );

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return '';
		}

		// Try to extract title from <title> tag.
		if ( preg_match( '/<title[^>]*>([^<]+)<\/title>/i', $body, $matches ) ) {
			$title = trim( $matches[1] );
			// Remove common suffixes like " | Amazon.co.jp" or " - Amazon.com".
			$title = preg_replace( '/\s*[|\-]\s*Amazon\.[a-z.]+$/i', '', $title );
			return html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );
		}

		return '';
	}

	/**
	 * Get resources about product information.
	 */
	public static function get_resources() {
		return apply_filters( 'hamazon_apa_resources', array(
			SearchItemsResource::BROWSE_NODE_INFOWEBSITE_SALES_RANK,
			SearchItemsResource::IMAGESPRIMARYLARGE,
			SearchItemsResource::IMAGESPRIMARYMEDIUM,
			SearchItemsResource::ITEM_INFOTITLE,
			SearchItemsResource::ITEM_INFOBY_LINE_INFO,
			SearchItemsResource::ITEM_INFOPRODUCT_INFO,
			SearchItemsResource::ITEM_INFOCONTENT_INFO,
			SearchItemsResource::ITEM_INFOEXTERNAL_IDS,
			SearchItemsResource::ITEM_INFOTRADE_IN_INFO,
			SearchItemsResource::ITEM_INFOMANUFACTURE_INFO,
			SearchItemsResource::OFFERSLISTINGSPRICE,
			SearchItemsResource::PARENT_ASIN,
			SearchItemsResource::OFFERSLISTINGSPROGRAM_ELIGIBILITYIS_PRIME_EXCLUSIVE,
			SearchItemsResource::OFFERSLISTINGSPROGRAM_ELIGIBILITYIS_PRIME_PANTRY,
		) );
	}

	/**
	 * Get configuration.
	 *
	 * @return Configuration|\WP_Error
	 */
	public static function get_config() {
		$service    = Amazon::get_instance();
		$access_key = $service->get_option( 'accessKey' );
		$secret_key = $service->get_option( 'secretKey' );
		$tag        = self::get_partner_tag();
		$locale     = $service->get_option( 'locale' );
		if ( ! ( $access_key && $tag && $locale && $secret_key ) ) {
			return new \WP_Error( 'hamazon_invalid_arguments', __( 'Amazon Associate setting is invalid. Please fill all information.', 'hamazon' ) );
		}
		$config = new Configuration();
		$config->setAccessKey( $access_key );
		$config->setSecretKey( $secret_key );
		$host   = AmazonLocales::get_host( $locale );
		$region = AmazonLocales::get_region( $locale );
		$config->setHost( $host );
		$config->setRegion( $region );

		return $config;
	}

	/**
	 * Get partner tag.
	 *
	 * @return string
	 */
	public static function get_partner_tag() {
		$service = Amazon::get_instance();
		return $service->get_option( 'associatesid' );
	}

	/**
	 * Validate request.
	 *
	 * @param SearchItemsRequest|GetItemsRequest $request
	 *
	 * @return true|\WP_Error
	 */
	protected static function validate_request( $request ) {
		$invalid_properties = $request->listInvalidProperties();
		$length             = count( $invalid_properties );
		if ( $length > 0 ) {
			return new \WP_Error( 'invalid_property', __( 'Invalid properties for request.', 'hamazon' ) );
		} else {
			true;
		}
	}
}
