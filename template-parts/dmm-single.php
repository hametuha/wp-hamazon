<?php
/**
 * Template for DMM block.
 *
 * @package hamazon
 * @since 5.0.0
 * @version 5.0.0
 * @var stdClass $item    Item information
 * @var string   $content HTML string if description is entered.
 * @var string   $price   Price
 */
?>
<div class="tmkm-amazon-view wp-hamazon-dmm" data-store="dmm">
	<p class="tmkm-amazon-img">
		<a href="<?php echo esc_url( $item->affiliateURL ); ?>" target="_blank" rel="sponsored noreferrer noopener">
			<img class="tmkm-amazon-image" src="<?php echo isset( $item->imageURL->large ) ? $item->imageURL->large : ( isset( $item->imageURL->small ) ? $item->imageURL->small : hamazon_no_image() ); ?>" alt=""/>
		</a>
	</p>
	<p class="tmkm-amazon-title">
		<a href="<?php echo esc_url( $item->affiliateURL ); ?>" target="_blank" rel="sponsored noreferrer noopener">
			<?php echo esc_html( $item->title ); ?>
		</a>
	</p>
	<p class="tmkm-amazon-row tmkm-amazon-genre">
		<span class="label tmkm-amazon-label"><?php esc_html_e( 'Category', 'hamazon' ); ?></span>
		<em class="tmkm-amazon-value"><?php echo esc_html( $item->category_name ); ?></em>
	</p>
	<p class="tmkm-amazon-row tmkm-amazon-price price">
		<span class="label tmkm-amazon-label"><?php esc_html_e( 'Price', 'hamazon' ); ?></span>
		<em class="tmkm-amazon-value"><?php echo esc_html( $price ); ?></em>
	</p>
	<?php
	foreach ( array(
		'manufacture' => __( 'Publisher', 'hamazon' ),
		'maker'       => __( 'Publisher', 'hamazon' ),
		'author'      => __( 'Author', 'hamazon' ),
		'genre'       => __( 'Genre', 'hamazon' ),
	) as $key => $label ) :
		if ( empty( $item->iteminfo->{$key} ) ) {
			continue;
		}
		?>
		<p class="tmkm-amazon-row tmkm-amazon-<?php echo esc_attr( $key ); ?>">
			<span class="label tmkm-amazon-label"><?php echo esc_html( $label ); ?></span>
			<em class="tmkm-amazon-value">
			<?php
			echo esc_html( implode( ', ', array_map( function ( $item ) {
				return $item->name;
			}, $item->iteminfo->{$key} ) ) )
			?>
				</em>
		</p>
	<?php endforeach; ?>
	<?php if ( $content ) : ?>
		<p class="additional-description">
			<?php echo wp_kses_post( $content ); ?>
		</p>
	<?php endif; ?>
	<p class="tmkm-amazon-actions">
		<a class="btn tmkm-amazon-btn tmkm-amazon-btn-dmm" href="<?php echo esc_url( $item->affiliateURL ); ?>" target="_blank" rel="sponsored noreferrer noopener">
			<?php esc_html_e( 'Open DMM', 'hamazon' ); ?>
		</a>
	</p>
	<p class="tmkm-amazon-vendor vendor">
		<a href="https://affiliate.dmm.com/" target="_blank" rel="nofollow">Supported by DMM Affiliate</a>
	</p>
</div>

