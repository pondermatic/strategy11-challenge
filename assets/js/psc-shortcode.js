/**
 * PondermaticStrategy11Challenge shortcode definition.
 *
 * @since   1.0.0
 * @version 1.0.1
 */

if ( 'undefined' === typeof window.psc ) {
	/**
	 * PondermaticStrategy11Challenge namespace object.
	 *
	 * @namespace psc
	 */
	window.psc = {};
}
// noinspection JSUnresolvedReference .
if ( 'undefined' === typeof window.psc.shortcode ) {
	/**
	 * Shortcode object.
	 *
	 * @namespace psc.shortcode
	 */
	window.psc.shortcode = {};
}

/**
 * Fetch and display challenge data from our WordPress API.
 *
 * @since 1.0.0
 * @param {jQuery} $ jQuery library.
 * @param {obj} psc PondermaticStrategy11Challenge namespace.
 * @param {obj} wp WordPress namespace.
 * @param {obj} wp.escapeHtml Functions from wp-includes/js/dist/escape-html.js.
 * @param {function} wp.escapeHtml.escapeHTML Replaces '<' and '&' characters with HTML entities.
 */
( function( $, psc, wp ) {
	'use strict';

	/**
	 * Display the challenge data in a table-like format.
	 *
	 * @since 1.0.0
	 * @param {obj} data The data to display.
	 */
	function display( data ) {
		let pscDisplay = $( '#psc-display' );
		pscDisplay.find( '.psc-table-title' ).html(
			wp.escapeHtml.escapeHTML( data.title )
		);

		let headerHtml = "<tr>";
		$.each( data.data.headers, function( key, value ) {
			headerHtml += "<th>" + wp.escapeHtml.escapeHTML( value ) + "</th>";
		} );
		headerHtml += "</tr>";
		pscDisplay.find( '.psc-table thead' ).append( headerHtml );

		$.each( data.data.rows, function( rowNum, row ) {
			let rowHtml = '<tr>';
			$.each( row, function( key, value ) {
				if ( typeof value !== 'string' ) {
					value = String( value );
				}
				if ( key === 'date' ) {
					value = wp.date.dateI18n( parseInt( value ) );
				}
				rowHtml += '<td>' + wp.escapeHtml.escapeHTML( value ) + '</td>';
			} )
			rowHtml += '</tr>';
			pscDisplay.find( '.psc-table tbody' ).append( rowHtml );
		} );
	}

	/**
	 * Fetch the challenge data from our WordPress API.
	 *
	 * @param {string} url The REST API url on this server.
	 * @since 1.0.0
	 */
	function fetchData( url ) {
		wp.apiRequest.transport( url )
			.done( display )
			.fail( function( jqXHR, textStatus, errorThrown ) {
				console.error( jqXHR, textStatus, errorThrown );
			} );
	}

	/**
	 * Fetch challenge data and display it.
	 *
	 * @param {string} url The REST API url on this server.
	 * @since 1.0.0
	 */
	psc.shortcode.load = function load( url ) {
		fetchData( url );
	};
} )( window.jQuery, window.psc, wp );
