<?php
/**
 * Test_Admin_Table class definition.
 *
 * @since 1.0.1
 * @version 1.0.1
 */

namespace Pondermatic\Strategy11\Challenge\PHPUnit;

use DOMDocument;
use Pondermatic\WordpressPhpunitFramework\Test_Case;
use Pondermatic\Strategy11\Challenge\Admin;
use Pondermatic\Strategy11\Challenge\Data_List_Table;

/**
 * Tests methods in the Admin and Data_List_Table classes.
 *
 * @since 1.0.1
 */
class Test_Admin_Table extends Test_Case {
	/**
	 * Instance of the Admin class.
	 *
	 * @since 1.0.1
	 * @var Admin
	 */
	protected Admin $admin;

	/**
	 * Instance of the Data_List_Table class.
	 *
	 * @since 1.0.1
	 * @var Data_List_Table
	 */
	protected Data_List_Table $table;

	/**
	 * Decode named entities that are not defined in XML.
	 *
	 * @since 1.0.1
	 * @param string $string Subject.
	 * @return string
	 */
	protected function decode_non_xml_named_entities( string $string ): string {
		$xml_named_entities = [
			'&amp;',
			'&lt;',
			'&gt;',
			'&apos;',
			'&quot;',
		];
		preg_match_all( '/&\w.+;/', $string, $matches );
		$search_replace = [];
		foreach ( $matches[0] as $match ) {
			if ( in_array( $match, $xml_named_entities, true ) ) {
				continue;
			}
			$search_replace[ $match ] = html_entity_decode( $match );
		}
		return str_replace( array_keys( $search_replace ), $search_replace, $string );
	}

	/**
	 * This method is called before each test.
	 *
	 * @inheritDoc
	 * @since 1.0.1
	 */
	public function setUp(): void {
		// Fake a request to this plugin's admin page.
		// @todo Is there a better way to test a request to our plugin's admin page?
		global $plugin_page;
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$plugin_page = basename( dirname( __FILE__, 4 ) );
		$this->go_to( 'wp-admin/admin.php?page=pondermatic-strategy11-challenge' );
		set_current_screen( 'admin.php' );

		// Return mocked data instead of making a call to the remote API.
		new Mock_Data();
	}

	/**
	 * Instantiates and configures the Admin object.
	 *
	 * @since 1.0.1
	 */
	protected function setup_admin_object(): void {
		$this->admin = new Admin();

		/**
		 * Tell Admin object that the current admin page is for our plugin.
		 *
		 * @see wp-admin/admin.php `do_action( "load-{$page_hook}" );`
		 */
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		do_action( 'load-toplevel_page_pondermatic-strategy11-challenge' );

		// Remove hooked functions that call PHP header() because
		// wordpress-tests-lib/includes/bootstrap uses PHP system() which displays output.
		remove_action( 'admin_init', 'wp_admin_headers' );
		remove_action( 'admin_init', 'send_frame_options_header' );

		/**
		 * Tell Admin object that the WordPress admin page is initializing.
		 *
		 * @see wp-admin/admin.php `do_action( 'admin_init' );`
		 */
		do_action( 'admin_init' );
	}

	/**
	 * Tests that the correct CSS files are sent to the browser.
	 *
	 * @since 1.0.1
	 */
	public function test_styles(): void {
		$this->setup_admin_object();

		ob_start();
		$this->admin->render_page();
		ob_end_clean();
		$this->assertTrue( wp_style_is( 'psc' ) );
	}

	/**
	 * Tests the structure of the table.
	 *
	 * @since 1.0.1
	 */
	public function test_table(): void {
		$this->table = new Data_List_Table();
		$this->table->prepare_items();
		$this->table->views();

		// Output the table.
		ob_start();
		$this->table->display();
		$html = ob_get_clean();

		// Sanitize HTML for DOMDocument::loadXML().
		// * Decode all non-XML named entities.
		// * <input> elements to be inside a <form> block.
		$html = $this->decode_non_xml_named_entities( $html );
		$html = '<form>' . $html . '</form>';

		$dom = new DOMDocument( '1.0', 'utf-8' );
		$dom->loadXML( $html );

		// There should be only one table.
		$tables = $dom->getElementsByTagName( 'table' );
		$this->assertCount( 1, $tables );

		// The table should have one header.
		$headers = $tables->item( 0 )->getElementsByTagName( 'thead' );
		$this->assertCount( 1, $headers );

		// The header should have one row.
		$rows = $headers->item( 0 )->getElementsByTagName( 'tr' );
		$this->assertCount( 1, $rows );

		// The header row should have five cells.
		$cells = $rows->item( 0 )->getElementsByTagName( 'th' );
		$this->assertCount( 5, $cells );

		// The table should have one body.
		$bodies = $tables->item( 0 )->getElementsByTagName( 'tbody' );
		$this->assertCount( 1, $bodies );

		// The body should have three rows.
		$rows = $bodies->item( 0 )->getElementsByTagName( 'tr' );
		$this->assertCount( 3, $rows );

		// Every row should have five cells.
		foreach ( $rows as $row ) {
			$cells = $row->getElementsByTagName( 'td' );
			$this->assertCount( 5, $cells );
		}

		// The table should have one footer.
		$footers = $tables->item( 0 )->getElementsByTagName( 'tfoot' );
		$this->assertCount( 1, $footers );

		// The footer should have one row.
		$rows = $footers->item( 0 )->getElementsByTagName( 'tr' );
		$this->assertCount( 1, $rows );

		// The footer row should have five cells.
		$cells = $rows->item( 0 )->getElementsByTagName( 'th' );
		$this->assertCount( 5, $cells );
	}
}
