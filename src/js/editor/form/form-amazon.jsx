/*!
 * @handle hamzon-form-amazon
 * @deps wp-element, hamazon-i18n, hamazon-form-base
 */

const React = wp.element;
const { __ } = wp.i18n;
const { FormBase } = wp.hamazon;

class FormAmazon extends FormBase {
	constructor( params ) {
		super( params );
		this.state = {
			query: '',
			selectedOption: 'All',
			selectedOrder: 'Relevance',
			curPage: 1,
		};
	}

	buildParams() {
		return {
			query: this.state.query,
			index: this.state.selectedOption,
			page: this.state.curPage,
			order: this.state.selectedOrder,
		};
	}

	render() {
		const { service } = this.props;
		return (
			<div className="hamazon-modal-form-wrapper">
				<div className="hamazon-modal-form">
					<div className="hamazon-modal-form-item input">
						<label htmlFor="hamazon-input-amazon-query"
							className="hamazon-modal-form-label">
							{ __( 'Search Keyword', 'wp-hamazon' ) }
						</label>
						<input id="hamazon-input-amazon-query" className="regular-text hamazon-modal-input-text input-control"
							placeholder={ __( 'Enter search keywords…', 'wp-hamazon' ) }
							value={ this.state.query }
							onChange={ ( e ) => {
								this.setState( {
									query: e.target.value,
									curPage: 1,
								} );
							} } />
					</div>
					<div className="hamazon-modal-form-item">
						<label htmlFor="hamazon-input-amazon-category"
							className="hamazon-modal-form-label">{ __( 'Category', 'wp-hamazon' ) }</label>
						<select id="hamazon-input-amazon-category" value={ this.state.selectedOption }
							onChange={ ( e ) => {
								this.setState( {
									selectedOption: e.target.value,
									curPage: 1,
								} );
							} }>
							{ service.data.options.map( ( option ) => {
								return <option key={ option.key } value={ option.key }>{ option.label }</option>;
							} ) }
						</select>
					</div>
					{ service.data.orders && (
						<div className="hamazon-modal-form-item">
							<label htmlFor="hamazon-input-amazon-order"
								className="hamazon-modal-form-label">{ __( 'Order', 'wp-hamazon' ) }</label>
							<select id="hamazon-input-amazon-order" value={ this.state.selectedOrder }
								onChange={ ( e ) => {
									this.setState( {
										selectedOrder: e.target.value,
									} );
								} }>
								{ service.data.orders.map( ( option ) => {
									return <option key={ option.value } value={ option.value }>{ option.label }</option>;
								} ) }
							</select>
						</div>
					) }
					<div className="hamazon-modal-form-item">
						<button onClick={ ( e ) => {
							this.submitHandler( e );
						} } className="button-primary">
							{ __( 'Search', 'wp-hamazon' ) }
						</button>
					</div>
				</div>
				{ this.paginate() }
			</div>
		);
	}
}

wp.hamazon.FormAmazon = FormAmazon;
