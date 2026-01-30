<?php

namespace Hametuha\WpHamazon\Service;


use Hametuha\WpHamazon\Constants\AmazonLocales;
use Hametuha\WpHamazon\Pattern\AbstractService;
use Hametuha\WpHamazon\Constants\AmazonConstants;

/**
 * Amazon class
 * @package hamazon
 */
class Amazon extends AbstractService {

	public $name = 'amazon';

	public $title = 'Amazon';

	/**
	 * Get default options
	 *
	 * @return array
	 */
	protected function get_option_names() {
		return array(
			array(
				'key'   => 'associatesid',
				'label' => __( 'Partner Tag', 'hamazon' ),
			),
			array(
				'key'   => 'credentialId',
				'label' => __( 'Credential ID', 'hamazon' ),
			),
			array(
				'key'   => 'credentialSecret',
				'label' => __( 'Credential Secret', 'hamazon' ),
			),
			array(
				'key'     => 'locale',
				'label'   => __( 'Locale', 'hamazon' ),
				'default' => 'JP',
				'type'    => 'radio',
				'options' => AmazonLocales::get_locale_labels(),
			),
		);
	}

	/**
	 * Detect if this service is valid
	 *
	 * @return bool
	 */
	public function is_valid() {
		return $this->get_option( 'associatesid' ) && $this->get_option( 'credentialId' ) && $this->get_option( 'credentialSecret' );
	}


	/**
	 * Filter data passed to react.
	 *
	 * @param $data
	 * @return array|\stdClass
	 */
	protected function filter_data( $data ) {
		$constants = AmazonConstants::get_search_index();
		$orders    = AmazonLocales::get_sort_orders();
		return array(
			'options' => array_map( function ( $key, $value ) {
				return array(
					'key'   => $key,
					'label' => $value,
				);
			}, array_keys( $constants ), array_values( $constants ) ),
			'orders'  => array_map( function ( $key, $value ) {
				return array(
					'value' => $key,
					'label' => $value,
				);
			}, array_keys( $orders ), array_values( $orders ) ),
		);
	}

	/**
	 * Get rest arguments.
	 *
	 * @return array
	 */
	public function get_rest_arguments() {
		return array(
			'query' => array(
				'required'          => true,
				'description'       => __( 'Search keyword.', 'hamazon' ),
				'validate_callback' => function ( $query ) {
					return ! empty( $query );
				},
			),
			'page'  => array(
				'default'           => 1,
				'description'       => __( 'Specified page.', 'hamazon' ),
				'validate_callback' => function ( $page ) {
					return is_numeric( $page ) && ( 0 < $page );
				},
			),
			'index' => array(
				'default'             => 'All',
				'required'            => true,
				'description'         => __( 'Search category.', 'hamazon' ),
				'validation_callback' => function ( $index ) {
					$indexed = AmazonConstants::get_search_index();
					return isset( $indexed[ $index ] );
				},
			),
			'order' => array(
				'default'             => 'Relevance',
				'required'            => true,
				'description'         => 'Order of search results',
				'validation_callback' => function ( $order ) {
					$orders = AmazonLocales::get_sort_orders();
					return isset( $orders[ $order ] );
				},
			),
		);
	}

	/**
	 * Handle Amazon search request
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function handle_rest_request( \WP_REST_Request $request ) {
		$response = AmazonConstants::search_with( $request['query'], $request['page'], $request['index'], $request['order'] );
		if ( is_wp_error( $response ) ) {
			return $response;
		} else {
			return new \WP_REST_Response( $response );
		}
	}

	/**
	 * Get setting
	 *
	 * @return array
	 */
	public function short_code_setting() {
		return array(
			'tmkm-amazon' => array(
				array(
					'label' => 'ASIN',
					'type'  => 'text',
					'attr'  => 'asin',
				),
			),
		);
	}

	protected function get_service_description() {
		// translators: %s is a URL.
		return sprintf( __( 'Display link via Amazon Creators API. You can get credentials from <a href="%s" target="_blank" rel="noopener noreferrer">Associate Central</a> (Tools > Creator API).', 'hamazon' ), 'https://affiliate.amazon.co.jp/tools/creator-api' );
	}


	/**
	 * Get short code
	 *
	 * @param string $short_code
	 * @param array $attributes
	 * @param string $content
	 * @return string
	 */
	public function short_code_callback( $short_code, array $attributes = array(), $content = '' ) {
		switch ( $short_code ) {
			case 'tmkm-amazon':
				return AmazonConstants::format_amazon( $content, $attributes );
				break;
			default:
				return '';
				break;
		}
	}
}
