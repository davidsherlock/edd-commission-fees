<?php
/**
 * Batch Commissions Fees Report Details Export Class.
 *
 * This class handles commissions details report export.
 *
 * @package     EDD\CommissionFees
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2017, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * EDD_Commissions_Report_Export Class
 *
 * @since 1.0.0
 */
class EDD_Batch_Commissions_Fees_Report_Details_Export extends EDD_Batch_Export {


	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @since       3.4
	 * @access      public
	 * @var         string
	 */
	public $export_type = 'commissions_report_fee_details';

	private $args;


	/**
	 * Set the export headers.
	 *
	 * @since       3.4
	 * @access      public
	 * @return      void
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! edd_is_func_disabled( 'set_time_limit' ) ) {
			set_time_limit( 0 );
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'edd_commissions_report_export_filename', 'edd-export-' . $this->export_type . '-' . date( 'm' ) . '-' . date( 'Y' ) ) . '.csv' );
		header( "Expires: 0" );
	}


	/**
	 * Output the CSV columns.
	 *
	 * We make use of this function to set up the header of the earnings report.
	 *
	 * @access      public
	 * @since       3.4
	 * @return      string $cols The generated CSV header row
	 */
	public function print_csv_cols() {
		$cols = array(
			__( 'ID', 'edd-commission-fees' ),
			__( 'User ID', 'edd-commission-fees' ),
			__( 'User', 'edd-commission-fees' ),
			sprintf( __( '%s ID', 'edd-commission-fees' ), edd_get_label_singular() ),
			sprintf( __( '%s Name', 'edd-commission-fees' ), edd_get_label_singular() ),
			__( 'Payment ID', 'edd-commission-fees' ),
			__( 'Amount', 'edd-commission-fees' ),
			__( 'Currency', 'edd-commission-fees' ),
			__( 'Type', 'edd-commission-fees' ),
			__( 'Rate', 'edd-commission-fees' ),
			__( 'Status', 'edd-commission-fees' ),
			__( 'Fee', 'edd-commission-fees' ),
			__( 'Fee Rate', 'edd-commission-fees' ),
			__( 'Fee Type', 'edd-commission-fees' ),
			__( 'Calculation Base', 'edd-commission-fees' ),
			__( 'Base Amount', 'edd-commission-fees' ),
			__( 'Date Created', 'edd-commission-fees' ),
			__( 'Date Paid', 'edd-commission-fees' ),
		);

		$col_data = implode( ',', $cols ) . "\r\n";
		$this->stash_step_data( $col_data );

		return $col_data;
	}


	/**
	 * Print the CSV rows for the current step.
	 *
	 * @since       3.4
	 * @access      public
	 * @return      mixed false|string $row_data The data for a given row
	 */
	public function print_csv_rows() {
		$row_data = '';

		$data = $this->get_data();

		if ( $data ) {
			foreach ( $data as $commission ) {
				$download = false;
				if ( ! empty( $commission->download_id ) ) {
					$download = new EDD_Download( $commission->download_id );
					$download_name = $download->get_name();
					if ( $download->has_variable_prices() ) {
						$download_name .= ' - ' . edd_get_price_option_name( $download->ID, $commission->price_id, $commission->payment_id );
					}
				}

				$user = get_userdata( $commission->user_id );

				$commission = eddc_get_commission( $commission->id );

				$commission_meta = $commission->get_meta( '_edd_commission_fees', false );

				if ( empty( $commission_meta ) ) {
					$commission_meta = array();
				}

				$row_data_array = array(
					'id'            => $commission->id,
					'user_id'       => $commission->user_id,
					'user_name'     => $user->display_name,
					'download_id'   => ! empty( $download ) ? $commission->download_id : __( 'N/A', 'eddc' ),
					'download_name' => ! empty( $download ) ? $download_name : __( 'N/A', 'eddc' ),
					'payment_id'    => $commission->payment_id,
					'amount'        => $commission->amount,
					'currency'      => $commission->currency,
					'type'          => $commission->type,
					'rate'          => $commission->rate,
					'status'        => $commission->status,
					'fee'						=> ! empty( $commission_meta[0]['fee'] ) 			 ? edd_format_amount ( $commission_meta[0]['fee'] ) : __( 'N/A', 'edd-commission-fees' ),
					'fee_rate'			=> ! empty( $commission_meta[0]['rate'] ) 		 ? $commission_meta[0]['rate'] : __( 'N/A', 'edd-commission-fees' ),
					'fee_type'			=> ! empty( $commission_meta[0]['calc_base'] ) ? $commission_meta[0]['calc_base'] : __( 'N/A', 'edd-commission-fees' ),
					'calc_base'			=> ! empty( $commission_meta[0]['calc_type'] ) ? $commission_meta[0]['calc_type'] : __( 'N/A', 'edd-commission-fees' ),
					'base_amount'		=> ! empty( $commission_meta[0]['base_amount'] ) ? edd_format_amount ( $commission_meta[0]['base_amount'] ) : __( 'N/A', 'edd-commission-fees' ),
					'date_created'  => $commission->date_created,
					'date_paid'     => $commission->date_paid,
				);

				$row_data .= implode( ',', $row_data_array ) . "\r\n";
			}

			$this->stash_step_data( $row_data );

			return $row_data;
		}

		return false;
	}


	/**
	 * Get the Export Data.
	 *
	 * @since       3.4
	 * @access      public
	 * @return      mixed false|array $data The data for the CSV file
	 */
	public function get_data() {
		$commissions = edd_commissions()->commissions_db->get_commissions( $this->args );

		$data = apply_filters( 'edd_export_get_data', $commissions );
		$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $commissions );

		return $data;
	}


	/**
	 * Count the number of months we are dealing with.
	 *
	 * @since       3.4
	 * @access      private
	 * @return      int The calculated number of months for this CSV
	 */
	private function count() {
		$count_args = $this->args;
		$count_args['number'] = -1;
		unset( $count_args['paged'] );

		return edd_commissions()->commissions_db->count( $count_args );
	}


	/**
	 * Return the calculated completion percentage
	 *
	 * @since       3.4
	 * @access      public
	 * @return      int $percentage Percentage of batch processing complete
	 */
	public function get_percentage_complete() {
		$percentage = 100;

		$total = $this->count();

		if ( $total > 0 ) {
			$percentage = ( ( $this->step * 25 ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}


	/**
	 * Set the properties specific to the commissions report.
	 *
	 * @since       3.4
	 * @access      public
	 * @param       array $request The Form Data passed into the batch processing
	 * @return      void
	 */
	public function set_properties( $request ) {
		$this->start = ( isset( $request['start_month'] ) && isset( $request['start_year'] ) ) ? sanitize_text_field( $request['start_year'] ) . '-' . sanitize_text_field( $request['start_month'] ) . '-1' : '';
		$this->end   = ( isset( $request['end_month'] ) && isset( $request['end_year'] ) ) ? sanitize_text_field( $request['end_year'] ) . '-' . sanitize_text_field( $request['end_month'] ) . '-' . cal_days_in_month( CAL_GREGORIAN, sanitize_text_field( $request['end_month'] ), sanitize_text_field( $request['end_year'] ) ) : '';
		$this->status = ( isset( $request['status'] ) ) && 'all' !== $request['status'] ? sanitize_text_field( $request['status'] ) : 'all';

		$start_date = date( 'Y-m-d', strtotime( $this->start ) );
		$end_date   = date( 'Y-m-d', strtotime( $this->end ) );

		if ( strtotime( $start_date ) > strtotime( $end_date ) ) {
			return false;
		}

		$args = array(
			'number'     => 25,
			'paged'      => $this->step,
			'query_args' => array(
				'date_query' => array(
					'after'     => $start_date,
					'before'    => $end_date . ' 23:59:59',
					'inclusive' => true,
				)
			),
			'orderby' => 'id',
			'order'   => 'ASC',
		);

		if ( 'all' !== $this->status ) {
			$args['status'] = $this->status;
		} else {
			$args['status'] = array( 'paid', 'unpaid', 'revoked' );
		}

		$this->args = $args;
	}
}
