<?php
/**
 * Template for PHG block.
 *
 * @package hamazon
 * @since 5.0.0
 * @version 5.0.0
 * @var stdClass $item    Item information
 * @var string   $content HTML string if description is entered.
 * @var string   $price   Price
 * @var string   $kind    Genre
 * @var string   $link    Affiliate URL
 * @var string   $image   Image URL
 * @var string   $artist  Artist name.
 */
?>
<div class="tmkm-amazon-view wp-hamazon-dmm">
	<p class="tmkm-amazon-img">
		<a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="sponsored noreferrer noopener">
			<img class="tmkm-amazon-image" src="<?php echo esc_url( $image ); ?>" alt=""/>
		</a>
	</p>
	<p class="tmkm-amazon-title">
		<a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="sponsored noreferrer noopener">
			<?php echo esc_html( $item->trackName ); ?>
		</a>
	</p>
	<p class="category">
		<span class="label tmkm-amazon-label"><?php esc_html_e( 'Category', 'wp-hamazon' ); ?></span>
		<em class="tmkm-amazon-value"><?php echo esc_html( $kind ); ?></em>
	</p>
	<p class="price tmkm-amazon-row tmkm-amazon-price">
		<span class="label tmkm-amazon-label"><?php esc_html_e( 'Price', 'wp-hamazon' ); ?></span>
		<em class="tmkm-amazon-value tmkm-amazon-price-number"><?php echo esc_html( $price ); ?></em>
	</p>
	<?php if ( isset( $item->genres ) && $item->genres ) : ?>
		<p class="genre tmkm-amazon-row">
			<span class="label tmkm-amazon-label"><?php esc_html_e( 'Genre', 'wp-hamazon' ); ?></span>
			<em class="tmkm-amazon-value"><?php echo esc_html( implode( ', ', $item->genres ) ); ?></em>
		</p>
	<?php endif; ?>
	<?php
	foreach ( array(
		'author' => array( __( 'Author', 'wp-hamazon' ), $artist ),
	) as $key => $labels ) :
		list( $label, $name ) = $labels;
		?>
		<p class="tmkm-amazon-row <?php echo esc_attr( $key ); ?>">
			<span class="tmkm-amazon-label label"><?php echo esc_html( $label ); ?></span>
			<em class="tmkm-amazon-value"><?php echo esc_html( $name ); ?></em>
		</p>
	<?php endforeach; ?>
	<?php if ( $content ) : ?>
		<p class="additional-description">
			<?php echo wp_kses_post( $content ); ?>
		</p>
	<?php endif; ?>
	<p class="tmkm-amazon-actions">
		<a class="btn tmkm-amazon-btn tmkm-amazon-btn-phg" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="sponsored noreferrer noopener">
			<?php esc_html_e( 'Open iTunes', 'wp-hamazon' ); ?>
		</a>
	</p>
	<p class="tmkm-amazon-vendor vendor">
		<a href="https://www.apple.com/itunes/affiliates/" target="_blank" rel="nofollow">Supported by PHG iTunes Affiliate</a>
	</p>
</div>
