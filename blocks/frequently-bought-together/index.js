( function ( blocks, element, i18n ) {
	var el = element.createElement;
	var __ = i18n.__;

	// Static placeholder in the editor -- the real widget (anchor + companion products,
	// checkboxes, running total) is server-rendered on the front end via the block's PHP
	// render_callback, which reads the real postId context.
	blocks.registerBlockType( 'hdfbt/frequently-bought-together', {
		apiVersion: 3,
		title: __( 'Frequently Bought Together', 'hdwebmobile-frequently-bought-together' ),
		description: __(
			'Shows a "frequently bought together" widget for the current product.',
			'hdwebmobile-frequently-bought-together'
		),
		icon: 'cart',
		category: 'woocommerce',
		usesContext: [ 'postId' ],
		edit: function () {
			return el(
				'div',
				{ className: 'hdfbt-block-editor-placeholder' },
				el(
					'strong',
					{ className: 'hdfbt-block-editor-placeholder__title' },
					__( 'Frequently Bought Together', 'hdwebmobile-frequently-bought-together' )
				),
				el(
					'span',
					{ className: 'hdfbt-block-editor-placeholder__hint' },
					__( 'Companion products render here on the front end.', 'hdwebmobile-frequently-bought-together' )
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.i18n );
