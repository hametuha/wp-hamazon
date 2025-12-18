<?php
/**
 * Fallback template for Amazon block when PA-API is unavailable.
 *
 * This template is used when the PA-API returns an error (e.g., 403 due to
 * insufficient sales in the last 30 days).
 *
 * @package hamazon
 * @since 5.2.0
 * @var array  $item       Item information (minimal: asin, url only)
 * @var string $desc       HTML string if description is entered.
 * @var string $asin       ASIN code of product.
 * @var array  $extra_atts Extra attributes.
 * @var bool   $is_fallback Whether this is a fallback display.
 */

$image_url = hamazon_no_image();
?>
<div class="tmkm-amazon-view wp-hamazon-amazon wp-hamazon-amazon-fallback" data-store="amazon" data-asin="<?php echo esc_attr( $asin ); ?>">
	<p class="tmkm-amazon-img">
		<a href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="sponsored noreferrer noopener">
			<img class="tmkm-amazon-image" src="<?php echo esc_attr( $image_url ); ?>" alt="" />
		</a>
	</p>
	<p class="tmkm-amazon-title">
		<a href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="sponsored noreferrer noopener">
			<?php
			if ( ! empty( $item['title'] ) ) {
				echo esc_html( $item['title'] );
			} else {
				// translators: %s is ASIN code.
				echo esc_html( sprintf( __( 'Amazon Product (ASIN: %s)', 'hamazon' ), $asin ) );
			}
			?>
		</a>
	</p>

	<p class="tmkm-amazon-fallback-notice">
		<small><?php esc_html_e( 'Product details are currently unavailable. Click the link to view on Amazon.', 'hamazon' ); ?></small>
	</p>

	<?php echo $desc; ?>

	<p class="tmkm-amazon-actions">
		<a class="btn tmkm-amazon-btn tmkm-amazon-btn-amazon" href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="sponsored noreferrer noopener">
			<?php esc_html_e( 'Open Amazon', 'hamazon' ); ?>
		</a>
	</p>
	<p class="vendor tmkm-amazon-vendor">
		<a href="https://affiliate.amazon.co.jp/gp/advertising/api/detail/main.html" target="_blank" rel="nofollow">Supported by amazon Product Advertising API</a>
	</p>
</div>
