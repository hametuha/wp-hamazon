/*!
 *
 * @handle hamazon-search-box
 * @deps wp-element, hamazon-i18n, hamazon-amazon-renderer, hamazon-form-amazon, hamazon-dmm-renderer, hamazon-form-dmm, hamazon-phg-renderer, hamazon-form-phg
 */

const React = wp.element;
const { __ } = wp.i18n;

// Services
const { AmazonRenderer, FormAmazon, DmmRenderer, FormDmm, PhgRenderer, FormPhg } = wp.hamazon;

class SearchBox extends React.Component {
	constructor() {
		super();
		this.state = {
			loading: false,
			items: [],
		};
	}

	setLoading( isLoading ) {
		this.setState( {
			loading: isLoading,
		} );
	}

	submitHandler( items ) {
		this.setState( {
			items,
		} );
	}

	render() {
		let classes = 'hamazon-modal-service';
		if ( this.props.active ) {
			classes += ' active';
		}
		if ( this.state.loading ) {
			classes += ' loading';
		}
		let index = 0;
		let Renderer = false;
		let SearchForm = false;
		switch ( this.props.service.key ) {
			case 'amazon':
				Renderer = AmazonRenderer;
				SearchForm = FormAmazon;
				break;
			case 'dmm':
				Renderer = DmmRenderer;
				SearchForm = FormDmm;
				break;
			case 'phg':
				SearchForm = FormPhg;
				Renderer = PhgRenderer;
				break;
		}
		if ( Renderer ) {
			if ( this.state.items.length ) {
				return (
					<div className={ classes }>
						<SearchForm submitHandler={ ( items ) => {
							this.submitHandler( items );
						} } setLoading={ ( isLoading ) => this.setLoading( isLoading ) } service={ this.props.service } />
						<div className="hamazon-search-result">
							{ this.state.items.map( ( item ) => {
								const itemKey = this.props.service.key + '-' + index;
								index++;
								switch ( this.props.service.key ) {
									default:
										return <Renderer key={ itemKey } item={ item } selectHandler={ ( code ) => {
											this.props.insertCode( code );
										} } />;
								}
							}, this ) }
						</div>
					</div>
				);
			}
			return (
				<div className={ classes }>
					<SearchForm submitHandler={ ( items ) => {
						this.submitHandler( items );
					} } setLoading={ ( isLoading ) => this.setLoading( isLoading ) } service={ this.props.service } />
					<div className="hamazon-search-result-empty">
						<div className="hamazon-modal-search-result-empty">
							{ __( 'No results found. Please try different query.', 'wp-hamazon' ) }
						</div>
					</div>
				</div>
			);
		}
		return <div className={ classes }>
			<div className="hamazon-modal-search-result-error">{ __( 'This service is not available.', 'wp-hamazon' ) }</div>
		</div>;
	}
}

wp.hamazon.SearchBox = SearchBox;
