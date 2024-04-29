<?php
/**
 * List_Data class definition.
 *
 * @since   1.0.0
 * @version 1.0.0
 */

namespace Pondermatic\Strategy11Challenge;

use stdClass;
use WP_List_Table;

defined( 'ABSPATH' ) || exit;

/**
 * Displays the challenge data in a table.
 *
 * @since 1.0.0
 */
class Data_List_Table extends WP_List_Table {
	/**
	 * Notices to be displayed on the admin page.
	 *
	 * @since 1.0.1
	 * @var string[]
	 */
	protected array $error_notifications = [];

	/**
	 * The response property used to compare two response objects during sorting.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $comparison_property;

	/**
	 * The response from the remote challenge API.
	 *
	 * @since 1.0.0
	 * @var stdClass
	 */
	protected stdClass $response;

	/**
	 * A 1 will sort items in ascending order,
	 * while a -1 will sort in reverse.
	 *
	 * @since 1.0.0
	 * @var int 1|-1
	 */
	protected int $sort_order = 1;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param array $args @see WP_List_Table::__construct().
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );
		$this->items = [];

		$response = Core::$challenge_api->get_challenge_response_body();

		if ( is_wp_error( $response ) ) {
			$message                     = $response->get_error_message();
			$formatted                   = $response->get_error_data();
			$this->error_notifications[] = sprintf(
				'%s<br>JSON path: "%s"<br>message: "%s"',
				esc_html( $message ),
				key( $formatted ),
				current( $formatted )
			);
			add_action( 'admin_notices', [ $this, 'output_admin_notices' ] );
			$response = Core::$challenge_api->get_empty_response();
		}
		$this->response = $response;
	}

	/**
	 * Checks the current user's permissions.
	 *
	 * @since 1.0.0
	 */
	public function ajax_user_can(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Returns a formatted date from the user item.
	 *
	 * @since 1.0.0
	 * @used-by WP_List_Table::single_row_columns()
	 * @param stdClass $item The user object.
	 * @return string
	 */
	protected function column_date( stdClass $item ): string {
		return date_i18n(
			get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
			$item->date
		);
	}

	/**
	 * Returns the value from the $item object's property.
	 *
	 * @since 1.0.0
	 * @param object $item        The user object.
	 * @param string $column_name The user object's property name.
	 */
	protected function column_default( $item, $column_name ): string {
		return esc_html( $item->$column_name );
	}


	/**
	 * Returns an integer comparison between a property of two objects.
	 *
	 * @since 1.0.0
	 * @param stdClass $a Object 'a'.
	 * @param stdClass $b Object 'b'.
	 */
	protected function compare_integers( stdClass $a, stdClass $b ): int {
		$property = $this->comparison_property;
		if ( (int) $a->$property > (int) $b->$property ) {
			return $this->sort_order;
		} elseif ( (int) $a->$property < (int) $b->$property ) {
			return - $this->sort_order;
		} else {
			return 0;
		}
	}

	/**
	 * Returns a string comparison between a property of two objects.
	 *
	 * @since 1.0.0
	 * @param stdClass $a Object 'a'.
	 * @param stdClass $b Object 'b'.
	 */
	protected function compare_strings( stdClass $a, stdClass $b ): int {
		$property = $this->comparison_property;
		return strnatcasecmp( $a->$property, $b->$property ) * $this->sort_order;
	}

	/**
	 * Gets a list of columns.
	 *
	 * @see   WP_List_Table::get_columns()
	 * @since 1.0.0
	 * @return string[]
	 */
	public function get_columns(): array {
		return [
			'id'    => 'ID',
			'fname' => 'First Name',
			'lname' => 'Last Name',
			'email' => 'Email',
			'date'  => 'Date',
		];
	}

	/**
	 * Gets a list of sortable columns.
	 *
	 * @see   WP_List_Table::get_sortable_columns()
	 * @since 1.0.0
	 */
	protected function get_sortable_columns(): array {
		return [
			'id'    => [ 'id', false, '', '', 'asc' ],
			'fname' => [ 'fname' ],
			'lname' => [ 'lname' ],
			'email' => [ 'email' ],
			'date'  => [ 'date' ],
		];
	}

	/**
	 * Sends captured errors to the admin page.
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public function output_admin_notices(): void {
		foreach ( $this->error_notifications as $error ) {
			wp_admin_notice( $error, [ 'type' => 'error' ] );
		}
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @see   WP_List_Table::prepare_items()
	 * @since 1.0.0
	 */
	public function prepare_items(): void {
		foreach ( $this->response->data->rows as $row ) {
			$this->items[] = $row;
		}
		$this->set_pagination_args(
			[
				'total_items' => count( $this->items ),
			]
		);

		$this->items = $this->sort_items( $this->items );
	}

	/**
	 * Sorts the items.
	 *
	 * @since 1.0.0
	 * @param stdClass[] $items User objects.
	 * @return stdClass[]
	 */
	protected function sort_items( array $items ): array {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order            = sanitize_text_field( wp_unslash( $_GET['order'] ?? '' ) );
		$this->sort_order = $order === 'desc' ? - 1 : 1;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$orderby = sanitize_text_field( wp_unslash( $_GET['orderby'] ?? '' ) );
		switch ( $orderby ) {
			case 'date':
				$this->comparison_property = 'date';
				usort( $items, [ $this, 'compare_integers' ] );
				break;
			case 'fname':
			case 'lname':
			case 'email':
				$this->comparison_property = $orderby;
				usort( $items, [ $this, 'compare_strings' ] );
				break;
			case 'id':
			default:
				$this->comparison_property = 'id';
				usort( $items, [ $this, 'compare_integers' ] );
				break;
		}

		return $items;
	}
}
