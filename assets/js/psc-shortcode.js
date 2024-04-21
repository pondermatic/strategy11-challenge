/**
 * PondermaticStrategy11Challenge shortcode definition.
 *
 * @since   1.0.0
 * @version 1.0.0
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
 */
( function( $, psc ) {
	'use strict';

	/**
	 * Display the challenge data in a table-like format.
	 *
	 * @since 1.0.0
	 * @param {obj} data The data to display.
	 */
	function display( data ) {
		let pscDisplay = $( '#psc-display' );
		pscDisplay.find( '.psc-table-title' ).html( data.title );

		let headerHtml = "<tr>";
		$.each( data.data.headers, function( key, value ) {
			headerHtml += "<th>" + value + "</th>";
		} );
		headerHtml += "</tr>";
		pscDisplay.find( '.psc-table thead' ).append( headerHtml );

		$.each( data.data.rows, function( rowNum, row ) {
			let rowHtml = '<tr>';
			$.each( row, function( key, value ) {
				if ( key === 'date' ) {
					// @todo Use WP options 'date_format' and 'time_format'.
					value = new Date( value ).toString();
				}
				rowHtml += '<td>' + value + '</td>';
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
} )( window.jQuery, window.psc );
