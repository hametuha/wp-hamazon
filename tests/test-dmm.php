<?php
/**
 * DMM service test
 *
 * @package hamazon
 */

use Hametuha\WpHamazon\Service\Dmm;

/**
 * Test DMM / FANZA handling.
 */
class Hamazon_Dmm_Test extends WP_UnitTestCase {

	/**
	 * Requested URLs.
	 *
	 * @var string[]
	 */
	protected $requested = array();

	/**
	 * Mock DMM API.
	 */
	public function set_up() {
		parent::set_up();
		$this->requested = array();
		add_filter( 'pre_http_request', array( $this, 'mock_api' ), 10, 3 );
	}

	/**
	 * Remove mock.
	 */
	public function tear_down() {
		remove_filter( 'pre_http_request', array( $this, 'mock_api' ), 10 );
		parent::tear_down();
	}

	/**
	 * Record request and return empty item list.
	 *
	 * @param false|array $preempt Preempt.
	 * @param array       $args    Request args.
	 * @param string      $url     URL.
	 * @return array
	 */
	public function mock_api( $preempt, $args, $url ) {
		$this->requested[] = $url;
		return array(
			'headers'  => array(),
			'body'     => wp_json_encode( array(
				'result' => array(
					'status' => 200,
					'items'  => array(),
				),
			) ),
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
			'filename' => null,
		);
	}

	/**
	 * Get site parameter of the last request.
	 *
	 * @return string
	 */
	protected function last_site() {
		$query = wp_parse_url( end( $this->requested ), PHP_URL_QUERY );
		parse_str( $query, $params );
		return $params['site'] ?? '';
	}

	/**
	 * DMM.R18 is sent to the API as FANZA.
	 */
	public function test_r18_is_sent_as_fanza() {
		Dmm::get_instance()->make_request( 'ItemList', array( 'site' => 'DMM.R18' ) );
		$this->assertSame( 'FANZA', $this->last_site() );
		Dmm::get_instance()->make_request( 'ItemList', array( 'site' => 'DMM.com' ) );
		$this->assertSame( 'DMM.com', $this->last_site() );
	}

	/**
	 * Existing short codes with DMM.R18 also request FANZA, and no items returns WP_Error.
	 */
	public function test_find_product_with_r18() {
		$result = Dmm::get_instance()->find_product( 'test00001', 'DMM.R18', '', false );
		$this->assertSame( 'FANZA', $this->last_site() );
		$this->assertWPError( $result );
	}

	/**
	 * Site parameter of REST API is validated.
	 */
	public function test_site_validation() {
		$args     = Dmm::get_instance()->get_rest_arguments();
		$validate = $args['site']['validate_callback'];
		$this->assertTrue( $validate( 'DMM.com' ) );
		$this->assertTrue( $validate( 'DMM.R18' ) );
		$this->assertFalse( $validate( 'invalid' ) );
	}

	/**
	 * Image falls back to small image, then no image.
	 */
	public function test_image_fallback() {
		$base = array(
			'title'         => 'Test',
			'category_name' => 'Category',
			'affiliateURL'  => 'https://example.com/item',
			'iteminfo'      => new stdClass(),
			'prices'        => (object) array( 'price' => '100' ),
		);
		$render = function ( $image ) use ( $base ) {
			$item = (object) array_merge( $base, array( 'imageURL' => (object) $image ) );
			return hamazon_template( 'dmm', 'single', array(
				'item'    => $item,
				'content' => '',
				'price'   => '¥100',
			) );
		};
		$this->assertStringContainsString( 'src="https://example.com/large.jpg"', $render( array(
			'large' => 'https://example.com/large.jpg',
			'small' => 'https://example.com/small.jpg',
		) ) );
		$this->assertStringContainsString( 'src="https://example.com/small.jpg"', $render( array(
			'small' => 'https://example.com/small.jpg',
		) ) );
		$this->assertStringContainsString( 'src="' . esc_url( hamazon_no_image() ) . '"', $render( array() ) );
	}

	/**
	 * Affiliate ID description warns when the ID is not for API.
	 */
	public function test_affiliate_id_description() {
		$method = new ReflectionMethod( Dmm::class, 'affiliate_id_description' );
		$method->setAccessible( true );
		update_option( 'hamazon_dmm_affiliate_id', 'example-001' );
		$this->assertStringContainsString( '<strong', $method->invoke( Dmm::get_instance() ) );
		update_option( 'hamazon_dmm_affiliate_id', 'example-990' );
		$this->assertStringNotContainsString( '<strong', $method->invoke( Dmm::get_instance() ) );
		delete_option( 'hamazon_dmm_affiliate_id' );
		$this->assertStringNotContainsString( '<strong', $method->invoke( Dmm::get_instance() ) );
	}
}
