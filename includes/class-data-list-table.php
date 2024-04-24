<?php
/**
 * List_Data class definition.
 *
 * @since   1.0.0
 * @version 1.0.0
 */

namespace Pondermatic\Strategy11\Challenge;

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
	 * @since 1.0.0
	 */
	protected string $comparison_property;

	/**
	 * @since 1.0.0
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
	 * @inheritDoc
	 * @since 1.0.0
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		$response = Core::$challenge_api->get_challenge_response_body();
		if ( is_wp_error( $response ) ) {
			$response = new stdClass();
		}
		$this->response = $response;
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	public function ajax_user_can(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Returns a formatted date from the user item.
	 *
	 * @since 1.0.0
	 * @param stdClass $item
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
	 * @param object|array $item
	 * @param string       $column_name
	 */
	protected function column_default( $item, $column_name ): string {
		return $item->$column_name;
	}


	/**
	 * Returns an integer comparison.
	 *
	 * @since 1.0.0
	 * @param stdClass $a
	 * @param stdClass $b
	 * @return int
	 */
	protected function compare_integers( stdClass $a, stdClass $b ): int {
		$property = $this->comparison_property;
		if ( (int) $a->$property > (int) $b->$property ) {
			return 1 * $this->sort_order;
		} elseif ( (int) $a->$property < (int) $b->$property ) {
			return - 1 * $this->sort_order;
		} else {
			return 0;
		}
	}

	/**
	 * Returns a string comparison.
	 *
	 * @since 1.0.0
	 * @param stdClass $a
	 * @param stdClass $b
	 * @return int
	 */
	protected function compare_strings( stdClass $a, stdClass $b ): int {
		$property = $this->comparison_property;
		return strnatcasecmp( $a->$property, $b->$property ) * $this->sort_order;
	}

	/**
	 * @inheritDoc
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
	 * @inheritDoc
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
	 * @inheritDoc
	 * @since 1.0.0
	 */
	public function prepare_items(): void {
		foreach ( $this->response->data->rows as $row ) {
			$this->items[] = $row;
		}
		$this->set_pagination_args( [
			'total_items' => count( $this->items ),
		] );

		$this->items = $this->sort_items( $this->items );
	}

	/**
	 * Sorts the items.
	 *
	 * @since 1.0.0
	 * @param stdClass[] $items
	 * @return stdClass[]
	 */
	protected function sort_items( array $items ): array {
		$order            = $_GET['order'] ?? '';
		$this->sort_order = $order === 'desc' ? - 1 : 1;

		$orderby = sanitize_text_field( $_GET['orderby'] ?? '' );
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
