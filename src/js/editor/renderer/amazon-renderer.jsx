/*!
 * @hahdle hamazon-amazon-renderer
 * @deps wp-element, hamazon-base-renderer, hamazon-i18n
 */

const React = wp.element;
const { BaseRenderer } = wp.hamazon;
const { __ } = wp.i18n;

class AmazonRenderer extends BaseRenderer {
	getCode() {
		return '[tmkm-amazon asin="' + this.props.item.asin + '"][/tmkm-amazon]';
	}

	getTitle() {
		return (
			<h3 className="hamazon-item-title">
				{ this.props.item.title }
				<small>{ this.props.item.category }</small>
			</h3>
		);
	}

	getMeta() {
		const { item } = this.props;
		const meta = [];
		if ( item.attributes.contributors ) {
			for ( const role in item.attributes.contributors ) {
				if ( ! item.attributes.contributors.hasOwnProperty( role ) ) {
					continue;
				}
				meta.push( {
					label: role,
					value: item.attributes.contributors[ role ].join( ', ' ),
				} );
			}
		}
		for ( const key of [ 'brand', 'manufacturer' ] ) {
			if ( ! item.attributes[ key ] ) {
				continue;
			}
			meta.push( {
				label: __( 'Brand', 'wp-hamazon' ),
				value: item.attributes[ key ],
			} );
			break;
		}
		if ( item.date ) {
			meta.push( {
				label: __( 'Release Date', 'wp-hamazon' ),
				value: item.date,
			} );
		}
		return (
			<div className="hamazon-item-creator">
				{ meta.map( ( info, index ) => {
					return (
						<p key={ 'hamazon-item-meta-string-' + index }>
							<small>{ info.label }</small>
							{ info.value }
						</p>
					);
				} ) }
			</div>
		);
	}
}

wp.hamazon.AmazonRenderer = AmazonRenderer;
