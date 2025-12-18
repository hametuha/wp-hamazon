<?php

namespace Hametuha\WpHamazon\Constants;


class AmazonLocales {

	/**
	 * Get locale informations.
	 *
	 * @see {https://webservices.amazon.com/paapi5/documentation/common-request-parameters.html#host-and-region}
	 * @return array
	 */
	public static function get_locales() {
		$locales  = array(
			'AU' => array(
				'label'  => 'Australia',
				'region' => 'us-west-2',
				'domain' => 'com.au',
			),
			'BZ' => array(
				'label'  => 'Brasil',
				'domain' => 'com.br',
			),
			'CA' => array(
				'label'  => 'Canada',
				'domain' => 'ca',
			),
			'FR' => array(
				'label'  => 'France',
				'region' => 'eu-west-1',
				'domain' => 'fr',
			),
			'DE' => array(
				'label'  => 'Deautsch',
				'region' => 'eu-west-1',
				'domain' => 'de',
			),
			'IN' => array(
				'label'  => 'India',
				'region' => 'eu-west-1',
				'domain' => 'in',
			),
			'IT' => array(
				'label'  => 'Italia',
				'region' => 'eu-west-1',
				'domain' => 'it',
			),
			'JP' => array(
				'label'  => '日本',
				'region' => 'us-west-2',
				'domain' => 'co.jp',
			),
			'MX' => array(
				'label'  => 'México',
				'domain' => 'com.mx',
			),
			'NL' => array(
				'label'  => 'Nederland',
				'region' => 'eu-west-1',
				'domain' => 'nl',
			),
			'SP' => array(
				'label'  => 'Singapore',
				'region' => 'us-west-2',
				'domain' => 'sg',
			),
			'ES' => array(
				'label'  => 'España',
				'region' => 'eu-west-1',
				'domain' => 'es',
			),
			'TK' => array(
				'label'  => 'Türkiye',
				'region' => 'eu-west-1',
				'domain' => 'com.tr',
			),
			'AE' => array(
				'label'  => 'الإمارات العربيّة المتّحدة',
				'region' => 'eu-west-1',
				'domain' => 'ae',
			),
			'UK' => array(
				'label'  => 'United Kingdom',
				'region' => 'eu-west-1',
				'domain' => 'co.uk',
			),
			'US' => array(
				'label' => 'United States',
			),
		);
		$filtered = array();
		foreach ( $locales as $locale => $option ) {
			$filtered[ $locale ] = wp_parse_args( $option, array(
				'label'  => '',
				'region' => 'us-east-1',
				'domain' => 'com',
			) );
		}

		return $filtered;
	}

	/**
	 *
	 * Get locales.
	 *
	 * @return array
	 */
	public static function get_locale_labels() {
		$filtered = array();
		foreach ( self::get_locales() as $key => $locale ) {
			$filtered[ $locale['label'] ] = $key;
		}
		return $filtered;
	}

	/**
	 * Geet host
	 *
	 * @param string $locale
	 * @return string
	 */
	public static function get_host( $locale ) {
		return self::get_key( 'domain', $locale );
	}

	/**
	 * Get host and key.
	 *
	 * @param string $locale
	 *
	 * @return string
	 */
	public static function get_region( $locale ) {
		return self::get_key( 'region', $locale );
	}

	/**
	 * Get current locale.
	 *
	 * @return string
	 */
	public static function get_language_locale() {
		$locale = get_user_locale();
		if ( 'ja' === $locale ) {
			$locale = 'ja_JP';
		}
		return $locale;
	}

	/**
	 * Get available sort order.
	 *
	 * @return array
	 */
	public static function get_sort_orders() {
		return array(
			'AvgCustomerReviews' => _x( 'Review Rank', 'Sort order', 'hamazon' ),
			'Featured'           => _x( 'Featured', 'Sort order', 'hamazon' ),
			'NewestArrival'      => _x( 'Newer', 'Sort order', 'hamazon' ),
			'Price:HighToLow'    => _x( 'Higher Cost', 'Sort order', 'hamazon' ),
			'Price:LowToHigh'    => _x( 'Cheaper Cost', 'Sort order', 'hamazon' ),
			'Relevance'          => _x( 'Relevant', 'Sort order', 'hamazon' ),
		);
	}

	/**
	 * Get key
	 *
	 * @param string $key
	 * @param string $locale
	 * @return string
	 */
	private static function get_key( $key, $locale ) {
		$locales = self::get_locales();
		if ( ! isset( $locales[ $locale ] ) || ! isset( $locales[ $locale ][ $key ] ) ) {
			return '';
		}
		$value = $locales[ $locale ][ $key ];
		switch ( $key ) {
			case 'domain':
				$value = sprintf( 'webservices.amazon.%s', $value );
				break;
		}
		return $value;
	}

	/**
	 * Get product page URL domain for a locale.
	 *
	 * @param string $locale Locale code (e.g., 'JP', 'US').
	 * @return string Domain for product page (e.g., 'co.jp', 'com').
	 */
	public static function get_product_domain( $locale ) {
		$locales = self::get_locales();
		if ( ! isset( $locales[ $locale ] ) || ! isset( $locales[ $locale ]['domain'] ) ) {
			return 'com';
		}
		return $locales[ $locale ]['domain'];
	}

	/**
	 * Generate Amazon product page URL from ASIN.
	 *
	 * @param string $asin        ASIN code.
	 * @param string $locale      Locale code (e.g., 'JP', 'US').
	 * @param string $partner_tag Associate tag.
	 * @return string Product page URL.
	 */
	public static function get_product_url( $asin, $locale, $partner_tag = '' ) {
		$domain = self::get_product_domain( $locale );
		$url    = sprintf( 'https://www.amazon.%s/dp/%s', $domain, $asin );
		if ( $partner_tag ) {
			$url = add_query_arg( [
				'ref' => 'nosim',
				'tag' => $partner_tag,
			], $url );
		}
		return $url;
	}
}
