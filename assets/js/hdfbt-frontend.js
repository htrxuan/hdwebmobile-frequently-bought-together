( function () {
	'use strict';

	if ( typeof window.hdfbtParams === 'undefined' ) {
		return;
	}

	var params = window.hdfbtParams;

	function formatPrice( amount ) {
		var currency = params.currency;
		var decimals = parseInt( currency.decimals, 10 );
		var fixed = amount.toFixed( isNaN( decimals ) ? 2 : decimals );
		var parts = fixed.split( '.' );

		parts[ 0 ] = parts[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, currency.thousandSeparator );
		var formatted = parts.join( currency.decimalSeparator );

		switch ( currency.position ) {
			case 'left':
				return currency.symbol + formatted;
			case 'right':
				return formatted + currency.symbol;
			case 'left_space':
				return currency.symbol + ' ' + formatted;
			case 'right_space':
				return formatted + ' ' + currency.symbol;
			default:
				return currency.symbol + formatted;
		}
	}

	function recalculate( widget ) {
		var checkboxes = widget.querySelectorAll( '[data-hdfbt-checkbox]' );
		var anchorPrice = parseFloat( widget.getAttribute( 'data-hdfbt-anchor-price' ) );
		var total = isNaN( anchorPrice ) ? 0 : anchorPrice;
		var count = 1; // the anchor itself, always included.

		checkboxes.forEach( function ( checkbox ) {
			if ( checkbox.checked ) {
				count += 1;
				var price = parseFloat( checkbox.getAttribute( 'data-hdfbt-price' ) );
				if ( ! isNaN( price ) ) {
					total += price;
				}
			}
		} );

		var totalEl = widget.querySelector( '[data-hdfbt-total]' );
		if ( totalEl ) {
			// The currency symbol from get_woocommerce_currency_symbol() is an HTML entity
			// (e.g. "&#8363;" for ₫) meant for markup, not plain text -- this is admin-configured
			// WooCommerce settings data, not user input, so innerHTML here is safe and correct.
			totalEl.innerHTML = formatPrice( total ); // eslint-disable-line no-unsanitized/property
		}

		var button = widget.querySelector( '[data-hdfbt-add]' );
		if ( button ) {
			button.textContent = params.addLabelTemplate.replace( '%d', count );
		}
	}

	document.addEventListener( 'change', function ( event ) {
		var checkbox = event.target.closest( '.hdfbt-widget__checkbox' );
		if ( ! checkbox ) {
			return;
		}
		var widget = checkbox.closest( '[data-hdfbt-widget]' );
		if ( widget ) {
			recalculate( widget );
		}
	} );

	var storeApiNonce = null;

	function getStoreApiNonce() {
		if ( storeApiNonce ) {
			return Promise.resolve( storeApiNonce );
		}

		return fetch( params.cartUrl, { credentials: 'same-origin' } ).then( function ( response ) {
			storeApiNonce = response.headers.get( 'Nonce' ) || response.headers.get( 'X-WC-Store-API-Nonce' );
			return storeApiNonce;
		} );
	}

	function addToCart( widget, button ) {
		var anchorId = button.getAttribute( 'data-hdfbt-anchor-id' );
		var productIds = [ anchorId ];

		widget.querySelectorAll( '[data-hdfbt-checkbox]:checked' ).forEach( function ( checkbox ) {
			productIds.push( checkbox.getAttribute( 'data-hdfbt-product-id' ) );
		} );

		button.disabled = true;

		getStoreApiNonce()
			.then( function ( nonce ) {
				// The Store API validates the Nonce per sub-request inside a batch, not just on
				// the outer /batch request -- confirmed live: omitting it here produced a 401
				// "Missing the Nonce header" for every sub-request even though the outer POST
				// itself carried a valid Nonce header.
				var requests = productIds.map( function ( id ) {
					return {
						path: '/wc/store/v1/cart/add-item',
						method: 'POST',
						headers: nonce ? { Nonce: nonce } : {},
						body: { id: parseInt( id, 10 ), quantity: 1 },
					};
				} );

				return fetch( params.cartBatchUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify( { requests: requests } ),
				} );
			} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( data ) {
				// The outer /batch response is 207 whenever the batch request itself was well
				// formed, regardless of whether the individual sub-requests inside it actually
				// succeeded -- each sub-response's own status has to be checked separately.
				var responses = ( data && data.responses ) || [];
				var allSucceeded = responses.length > 0 && responses.every( function ( item ) {
					return item.status >= 200 && item.status < 300;
				} );

				if ( allSucceeded ) {
					document.body.dispatchEvent(
						new CustomEvent( 'wc-blocks_added_to_cart', { detail: { preserveCartData: true } } )
					);
					if ( window.jQuery ) {
						window.jQuery( document.body ).trigger( 'added_to_cart' );
					}
				} else {
					var firstError = responses.find( function ( item ) {
						return item.status < 200 || item.status >= 300;
					} );
					var message = firstError && firstError.body && firstError.body.message
						? firstError.body.message.replace( /<[^>]*>/g, '' )
						: null;
					window.alert( message || params.genericErrorMessage );
				}
			} )
			.finally( function () {
				button.disabled = false;
			} );
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '[data-hdfbt-add]' );
		if ( ! button ) {
			return;
		}
		event.preventDefault();
		var widget = button.closest( '[data-hdfbt-widget]' );
		if ( widget ) {
			addToCart( widget, button );
		}
	} );
} )();
