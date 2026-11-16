/*!
 * @handle hamazon-form-base
 * @deps jquery, wp-element, hamazon-i18n, wp-api-fetch
 */
const React = wp.element;
const $ = jQuery;
const { __ } = wp.i18n;

class FormBase extends React.Component {
	constructor( params ) {
		super( params );
		this.state = {
			curPage: 1,
			totalPage: 0,
		};
	}

	buildParams() {
		return {};
	}

	clearSearch() {
		this.setState( {
			totalPage: 0,
		} );
		this.props.submitHandler( [] );
	}

	methodName() {
		return 'GET';
	}

	submitHandler( event ) {
		if ( event ) {
			event.preventDefault();
		}
		// Do search
		const params = $.param( this.buildParams() );
		this.props.setLoading( true );
		switch ( this.methodName() ) {
			case 'GET':
				wp.apiFetch( {
					path: `hamazon/v3/${ this.props.service.key }?${ params }`,
				} ).then( ( response ) => {
					this.setState( {
						totalPage: response.total_page,
					} );
					this.props.submitHandler( response.items );
				} ).catch( () => {
					this.props.submitHandler( [] );
				} ).finally( () => {
					this.props.setLoading( false );
				} );
				break;
			default:
				// no current desc.
				break;
		}
	}

	paginate() {
		const totalPage = this.state.totalPage;
		if ( ! totalPage || 2 > totalPage ) {
			return null;
		}
		const showPrevious = ( 1 < this.state.curPage );
		const showNext = ( totalPage !== this.state.curPage );
		const pages = [ 1 ];
		for ( let i = -3; i < 4; i++ ) {
			const page = this.state.curPage + i;
			switch ( i ) {
				case 0:
					if ( page > 1 && page !== totalPage ) {
						pages.push( page );
					}
					break;
				case -3:
					if ( page > 1 ) {
						pages.push( null );
					}
					break;
				case 3:
					if ( page < totalPage ) {
						pages.push( null );
					}
					break;
				default:
					if ( i < 0 ) {
						if ( page > 1 && i < totalPage ) {
							pages.push( page );
						}
					} else if ( page < totalPage ) {
						pages.push( page );
					}
					break;
			}
		}
		if ( 1 < totalPage ) {
			pages.push( totalPage );
		}
		return (
			<div className="hamazon-pagination">
				{ ( () => {
					return showPrevious ? (
						<button key="hamazon-pagination-item-first" className="hamazon-pagination-item"
							onClick={ ( event ) => {
								this.setState( {
									curPage: this.state.curPage - 1,
								}, () => {
									this.submitHandler( event );
								} );
							} }>
							{ __( 'Previous', 'wp-hamazon' ) }
						</button>
					) : null;
				} )() }
				{ pages.map( ( p, index ) => {
					let className = 'hamazon-pagination-item';
					const keyName = 'hamazon-pagination-item-' + index;
					if ( null === p ) {
						className += ' empty';
						return (
							<span key={ keyName } className={ className }>&hellip;</span>
						);
					} else if ( this.state.curPage === p ) {
						className += ' current';
						return (
							<span key={ keyName } className={ className }>{ p }</span>
						);
					}
					return (
						<button key={ keyName } className={ className } onClick={ ( event ) => {
							this.setState( {
								curPage: this.state.curPage + 1,
							}, () => {
								this.submitHandler( event );
							} );
						} }>{ p }</button>
					);
				}, this ) }
				{ ( () => {
					return showNext ? (
						<button key="hamazon-pagination-item-last" className="hamazon-pagination-item"
							onClick={ ( event ) => {
								this.setState( {
									curPage: this.state.curPage + 1,
								}, () => {
									this.submitHandler( event );
								} );
							} }>
							{ __( 'Next', 'wp-hamazon' ) }
						</button>
					) : null;
				} )() }
			</div>
		);
	}
}

wp.hamazon.FormBase = FormBase;
