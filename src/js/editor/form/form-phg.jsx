/*!
 * @handle hamazon-form-phg
 * @deps wp-element, hamazon-i18n, hamazon-form-base
 */
const React = wp.element;
const { __ } = wp.i18n;
const { FormBase } = wp.hamazon;

class FormPhg extends FormBase {
	constructor( params ) {
		super( params );
		this.state.query = '';
		this.selectedMedia = 'all';
		this.selectedCountry = 'US';
	}

	buildParams() {
		return {
			keyword: this.state.query,
			country: this.state.selectedCountry,
			media: this.state.selectedMedia,
		};
	}

	onCountryChange( event ) {
		this.setState( {
			selectedCountry: event.target.value,
		} );
	}

	onMediaChange( event ) {
		this.setState( {
			selectedMedia: event.target.value,
		} );
	}

	onInputChange( event ) {
		this.setState( {
			page: 1,
			query: event.target.value,
		} );
	}

	render() {
		return (
			<div className="hamazon-modal-form-wrapper">
				<div className="hamazon-modal-form">
					<div className="hamazon-modal-form-item">
						<label htmlFor="hamazon-input-amazon-category"
							className="hamazon-modal-form-label">{ __( 'Category', 'wp-hamazon' ) }</label>
						<select id="hamazon-input-amazon-category" value={ this.state.selectedMedia }
							onChange={ ( e ) => {
								this.onMediaChange( e );
							} }>
							{ this.props.service.data.media.map( ( option ) => {
								return <option key={ option.key } value={ option.key }>{ option.label }</option>;
							} ) }
						</select>
					</div>
					<div className="hamazon-modal-form-item">
						<label htmlFor="hamazon-input-phg-country"
							className="hamazon-modal-form-label">{ __( 'Countries', 'wp-hamazon' ) }</label>
						<select id="hamazon-input-phg-country" value={ this.state.selectedCountry }
							onChange={ ( e ) => {
								this.onCountryChange( e );
							} }>
							{ this.props.service.data.countries.map( ( option ) => {
								return <option key={ option.key } value={ option.key }>{ option.label }</option>;
							} ) }
						</select>
					</div>
					<div className="hamazon-modal-form-item input">
						<label htmlFor="hamazon-input-amazon-query"
							className="hamazon-modal-form-label">{ __( 'Search Terms', 'wp-hamazon' ) }</label>
						<input id="hamazon-input-amazon-query" className="regular-text hamazon-modal-input-text input-control"
							value={ this.state.query }
							onChange={ ( e ) => this.onInputChange( e ) } />
					</div>
					<div className="hamazon-modal-form-item">
						<button onClick={ ( e ) => {
							this.submitHandler( e );
						} } className="button-primary">{ __( 'Search', 'wp-hamazon' ) }
						</button>
					</div>
				</div>
				{ this.paginate() }
			</div>
		);
	}
}

wp.hamazon.FormPhg = FormPhg;
