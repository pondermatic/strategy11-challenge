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
 */
( function( $, psc ) {
	'use strict';

	/**
	 * @typedef data Data returned from the remote API.
	 * @property {Array} headers
	 * @property {obj} rows
	 */

	/**
	 * @typedef response
	 * @property {string} title Table title.
	 * @property {data} data
	 */

	/**
	 * @typedef rows
	 * @property {int} id Unique identifier.
	 * @property {string} fname First name.
	 * @property {string} lname Last name.
	 * @property {string} email E-mail address.
	 * @property {int} date Unix timestamp.
	 */

	/**
	 * @typedef wp
	 * @property {obj} apiRequest Functions from wp-includes/js/api-=request.js.
	 * @property {Module} datedate Functions from wp-includes/js/dist/date.js.
	 * @property {Module} escapeHtml Functions from wp-includes/js/dist/escape-html.js.
	 */

	/**
	 * @typedef apiRequest
	 * @property {function} transport jQuery.ajax().
	 */

	/**
	 * @typedef wp.date
	 * @property {function} dateI18n Formats a date (like `wp_date()` in PHP),
	 * 								 translating it into site's locale.
	 */

	/**
	 * @typedef wp.escapeHtml
	 * @property {function} escapeHTML Replaces '<' and '&' characters with HTML entities.
	 */

	/**
	 * Display the challenge data in a table-like format.
	 *
	 * @since 1.0.0
	 * @param {response} response The data to display.
	 */
	function display( response ) {
		let pscDisplay = $( '#psc-display' );
		pscDisplay.find( '.psc-table-title' ).html(
			wp.escapeHtml.escapeHTML( response.title )
		);

		let headerHtml = "<tr>";
		$.each( response.data.headers, function( key, value ) {
			headerHtml += "<th>" + wp.escapeHtml.escapeHTML( value ) + "</th>";
		} );
		headerHtml += "</tr>";
		pscDisplay.find( '.psc-table thead' ).append( headerHtml );

		$.each( response.data.rows, function( rowNum, row ) {
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
} )( window.jQuery, window.psc );
