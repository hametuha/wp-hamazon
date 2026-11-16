/*!
 * @handle hamazon-base-renderer
 * @deps wp-element, hamazon-i18n
 */

const React = wp.element;
const { __ } = wp.i18n;

class BaseRenderer extends React.Component {
	copy( event, copy ) {
		event.preventDefault();
		// eslint-disable-next-line no-alert
		window.prompt( __( 'Copy this string.', 'wp-hamazon' ), copy );
	}

	getTitle() {
		return null;
	}

	getCode() {
		return '';
	}

	getPrice() {
		return (
			<div className="hamazon-item-price">
				{ this.props.item.price }
			</div>
		);
	}

	getMeta() {
		return null;
	}

	extraButtons() {
		return null;
	}

	render() {
		return (
			<div className="hamazon-item">
				{ ( this.props.item.image ) && (
					<div className="hamazon-item-image">
						<img src={ this.props.item.image } alt="" />
					</div>
				) }
				<div className="hamazon-item-content">
					{ ( () => {
						return this.getTitle();
					} )() }
					{ ( () => {
						return this.getPrice();
					} )() }
					{ ( () => {
						return this.getMeta();
					} )() }
					<div className="hamazon-item-meta">
						<button className="button-primary" onClick={ ( event ) => {
							event.preventDefault();
							this.props.selectHandler( this.getCode() );
						} }>{ __( 'Insert', 'wp-hamazon' ) }</button>
						<button className="button" onClick={ ( e ) => {
							this.copy( e, this.getCode() );
						} }>{ __( 'Copy Code', 'wp-hamazon' ) }</button>
						<a className="button" href={ this.props.item.url } target="_blank" rel="noopener noreferer noreferrer">
							{ __( 'View', 'wp-hamazon' ) }
						</a>
						<button className="button" onClick={ ( e ) => {
							this.copy( e, this.props.item.url );
						} }>{ __( 'Copy Link', 'wp-hamazon' ) }</button>
						{ ( () => {
							return this.extraButtons();
						} )() }
					</div>
				</div>
			</div>
		);
	}
}

wp.hamazon.BaseRenderer = BaseRenderer;
