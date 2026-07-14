( function( blocks, element, components, i18n ) {
	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'native-json-i18n/language-switcher', {
		title: __( 'Language Switcher', 'native-json-i18n' ),
		icon: 'translation',
		category: 'widgets',
		attributes: {
			layout: { type: 'string', default: 'horizontal' },
			showLabels: { type: 'boolean', default: true },
			textColor: { type: 'string', default: '' },
			backgroundColor: { type: 'string', default: '' },
			borderRadius: { type: 'string', default: '4px' },
			padding: { type: 'string', default: '8px 12px' },
			gap: { type: 'string', default: '8px' },
			fontSize: { type: 'string', default: '14px' }
		},
		edit: function( props ) {
			return el(
				'div',
				{ className: 'components-placeholder' },
				el( 'p', {}, __( 'Language switcher block ready. Adjust styling in the block settings.', 'native-json-i18n' ) )
			);
		},
		save: function() {
			return null;
		}
	} );
} )( window.wp.blocks, window.wp.element, window.wp.components, window.wp.i18n );
