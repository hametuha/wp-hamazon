<?php
/**
 * Template for Amazon block.
 *
 * @package hamazon
 * @since 5.0.0
 * @version 5.0.0
 * @var array  $item       Item information
 * @var string $desc       HTML string if description is entered.
 * @var string $asin       ASIN code of product.
 * @var array  $extra_atts Extra attributes.
 */
$image_url = hamazon_no_image();
if ( isset( $item['images']['large'] ) ) {
	$image_url = $item['images']['large'];
} elseif ( isset( $item['images']['medium'] ) ) {
	$image_url = $item['images']['medium'];
} elseif ( isset( $item['image'] ) ) {
	$image_url = $item['image'];
}
?>
<div class="tmkm-amazon-view wp-hamazon-amazon" data-store="amazon" data-asin="<?php echo esc_attr( $asin ); ?>">
	<p class="tmkm-amazon-img">
		<a href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="sponsored noreferrer noopener">
			<img class="tmkm-amazon-image" src="<?php echo esc_attr( $image_url ); ?>" alt="" />
		</a>
	</p>
	<p class="tmkm-amazon-title">
		<a href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="sponsored noreferrer noopener">
			<?php echo esc_html( $item['title'] ); ?>
			<?php if ( $item['category'] ) : ?>
				<small class="tmkm-amazon-category"><?php echo esc_html( $item['category'] ); ?></small>
			<?php endif; ?>
		</a>
	</p>

	<p class="tmkm-amazon-price price tmkm-amazon-row">
		<span class="label tmkm-amazon-label"><?php esc_html_e( 'Price', 'wp-hamazon' ); ?></span>
		<em class="tmkm-amazon-value tmkm-amazon-price-number"><?php echo $item['price'] ? esc_html( $item['price'] ) : 'N/A'; ?></em>
	</p>

	<?php if ( $item['rank'] ) : ?>
		<p class="tmkm-amazon-rank tmkm-amazon-row">
			<span class="label tmkm-amazon-label"><?php esc_html_e( 'Rank', 'wp-hamazon' ); ?></span>
			<em class="tmkm-amazon-value tmkm-amazon-rank">
				<?php
				// phpcs:disable WordPress.WP.I18n.NoEmptyStrings
				// translators: %s is ranking number.
				echo esc_html( sprintf( _x( '%s', 'Amazon Ranking', 'wp-hamazon' ), number_format( $item['rank'] ) ) );
				?>
			</em>
		</p>
	<?php endif; ?>

	<?php
	if ( ! empty( $item['attributes']['contributors'] ) ) {
		foreach ( $item['attributes']['contributors'] as $role => $users ) {
			if ( 3 < count( $users ) ) {
				$users   = array_slice( $users, 0, 3 );
				$users[] = _x( 'and more', 'Amazon Contributors', 'wp-hamazon' );
			}
			printf(
				'<p class="tmkm-amazon-contributor tmkm-amazon-row"><span class="tmkm-amazon-label">%s</span><em class="tmkm-amazon-value tmkm-amazon-contributors-name">%s</em></p>',
				esc_html( $role ),
				esc_html( implode( ', ', $users ) )
			);
		}
	}
	?>

	<?php
	foreach ( array( 'brand' => __( 'Publisher', 'wp-hamazon' ), 'manufacturer' => __( 'Publisher', 'wp-hamazon' ) ) as $key => $label ) {
		if ( empty( $item['attributes'][ $key ] ) ) {
			continue;
		}
			printf(
				'<p class="tmkm-amazon-brand tmkm-amazon-row"><span class="tmkm-amazon-label">%s</span><em class="tmkm-amazon-value tmkm-amazon-brand-name">%s</em></p>',
				esc_html( $label ),
				esc_html( $item['attributes'][ $key ] )
			);
			break;
	}
	?>

	<?php if ( ! empty( $item['date'] ) ) : ?>
		<p class="tmkm-amazon-date tmkm-amazon-row">
			<span class="label tmkm-amazon-label"><?php esc_html_e( 'Released', 'wp-hamazon' ); ?></span>
			<em class="tmkm-amazon-value tmkm-amazon-rank"><?php echo esc_html( $item['date'] ); ?></em>
		</p>
	<?php endif; ?>

	<?php echo $desc; ?>

	<p class="tmkm-amazon-actions">
		<a class="btn tmkm-amazon-btn tmkm-amazon-btn-amazon" href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="sponsored noreferrer noopener">
			<?php esc_html_e( 'Open Amazon', 'wp-hamazon' ); ?>
		</a>
	</p>
	<p class="vendor tmkm-amazon-vendor">
		<a href="https://affiliate.amazon.co.jp/gp/advertising/api/detail/main.html" target="_blank" rel="nofollow">Supported by amazon Product Advertising API</a>
	</p>
</div>
