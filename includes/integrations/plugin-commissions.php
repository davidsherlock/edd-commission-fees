<?php
/**
 * Integration functions to make Commission Fees compatible with EDD Commissions
 *
 * @package     EDD\CommissionFees
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Integration functions to make Custom Deliverables compatible with EDD Commissions
 *
 * @since 1.0.0
 */
class EDD_Commission_Fees_Commissions {

	/**
	 * Get things started
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct() {

		if ( ! class_exists( 'EDDC' ) ) {
			return;
		}

		if ( ! defined( 'EDD_COMMISSIONS_VERSION' ) ){
			return;
		}

		// Make sure we are at the minimum version of EDD Commissions - which is 3.3.
		add_action( 'admin_notices', array( $this, 'edd_commission_fees_too_old_notice' ) );

		// Adds the {commission_fee} email tag to existing template tag list
		add_filter( 'eddc_email_template_tags', array( $this, 'edd_commission_fees_add_email_template_tags' ), 10, 1 );

		// Render the {commission_fee} email tag within the message body
		add_filter( 'eddc_sale_alert_email', array( $this, 'edd_commission_fees_render_email_template_tags' ), 10, 6 );

		// Commissions fees card view information
		add_action( 'eddc_commission_card_bottom', array( $this, 'edd_commission_fees_commission_card_bottom' ), 10, 1 );

		// Calculate and update the commission amount, taking into account the fee
		add_action( 'eddc_insert_commission', array( $this, 'edd_commission_fees_adjust_commission_fee_amount' ), 10, 6 );

		// Insert the commission metadata
		add_action( 'eddc_insert_commission', array( $this, 'edd_commission_fees_insert_commission_meta' ), 10, 6 );

		// Add a payment note for this commission fee charge
		add_action( 'edd_commission_fees_insert_fee', array( $this, 'edd_commission_fees_record_commission_note'), 10, 7 );

		// Show fees on commissions frontend shortcodes?
		if ( edd_commission_fees_global_enable_frontend() ) {

			// Commissions Shortcode [edd_commissions] fee addition - Table Headers
			add_action( 'eddc_user_commissions_unpaid_head_row_end', array( $this, 'edd_commission_fees_user_commissions_unpaid_head_row_end' ), 10 );
			add_action( 'eddc_user_commissions_paid_head_row_end', array( $this, 'edd_commission_fees_user_commissions_paid_head_row_end' ), 10 );
			add_action( 'eddc_user_commissions_revoked_head_row_end', array( $this, 'edd_commission_fees_user_commissions_revoked_head_row_end' ), 10 );

			// Commissions Shortcode [edd_commissions] fee addition - Table Rows
			add_action( 'eddc_user_commissions_unpaid_row_end', array( $this, 'edd_commission_fees_user_commissions_unpaid_row_end' ), 10, 1 );
			add_action( 'eddc_user_commissions_paid_row_end', array( $this, 'edd_commission_fees_user_commissions_paid_row_end' ), 10, 1 );
			add_action( 'eddc_user_commissions_revoked_row_end', array( $this, 'edd_commission_fees_user_commissions_revoked_row_end' ), 10, 1 );

		}


		// Register commission batch exporter for our custom "fees" report
		if ( is_admin() ) {

			add_action( 'edd_register_batch_exporter', array( $this, 'edd_commission_fees_register_commissions_fees_report_details_batch_export' ), 1 );
			add_filter( 'eddc_export_classes', array( $this, 'edd_commission_fees_commissions_export_classes' ), 10, 1 );
			add_filter( 'eddc_report_types_tooltip_desc', array( $this, 'edd_commission_fees_commissions_export_tooltip_description' ) );
			
		}

	}


	/**
	 * Make sure we are at the minimum version of EDD Commissions - which is 3.3.
	 *
	 * @since       1.0.0
	 * @access      public
	 * @return      void
	 */
	public function edd_commission_fees_too_old_notice(){

		if ( defined( 'EDD_COMMISSIONS_VERSION' ) && version_compare( EDD_COMMISSIONS_VERSION, '3.4' ) == -1 ){
			?>
			<div class="notice notice-error">
				<p><?php echo __( 'EDD Commission Fees: Your version of EDD Commissions must be updated to version 3.4 or later to use the Commission Fees extension in conjunction with Commissions.', 'edd-commission-fees' ); ?></p>
			</div>
			<?php
		}
	}


	/**
	 * Adds the {fee} and {fee_amount} email tags to the template tag list
	 *
	 * @since       1.0.0
	 * @access      public
	 * @param       array $tags The email template tags
	 * @return      array $tags The merged email template tags
	 */
	public function edd_commission_fees_add_email_template_tags( $tags ) {

	  $additional_tags = array(
			array(
				'tag'         => 'fee',
				'description' => __( 'The fee charged on the commission amount', 'edd-commission-fees' ),
			),
			array(
				'tag'         => 'fee_amount',
				'description' => __( 'The commission amount after the fee deduction', 'edd-commission-fees' ),
			),
		);

	  // Combined the two arrays
		$tags = array_merge( $tags, $additional_tags );

	  return $tags;
	}


	/**
	 * Render the {fee} and {fee_amount} email tags within the message contents
	 *
	 * @since       1.0.0
	 * @access      public
	 * @param				string $message - the email body/message
	 * @param				int $used_id - the commission recipient user_id
	 * @param				float $commission_amount - the base commission amount
	 * @param				float $rate - the commission rate
	 * @param				int $download_id - the download id
	 * @param				int $commission_id - the commission id
	 * @return      string $message - the modified email body/message
	 */
	public function edd_commission_fees_render_email_template_tags( $message, $user_id, $commission_amount, $rate, $download_id, $commission_id ){

		$commission   					= eddc_get_commission( $commission_id );
		$commission_fee_amount	= $this->edd_commission_fees_calculate_commission_amount( $commission_amount, $download_id, $user_id );
		$commission_fee 				= $commission_amount - $commission_fee_amount;

		$message 								= str_replace( '{fee}', html_entity_decode( edd_currency_filter( edd_format_amount( $commission_fee ) ) ), $message );
		$message 								= str_replace( '{fee_amount}', html_entity_decode( edd_currency_filter( edd_format_amount ( $commission_fee_amount ) ) ), $message );

		return $message;
	}


	/**
	 * Commissions [edd_commissions] shortcode - Unpaid commissions table header
	 *
	 * @since       1.0.0
	 * @access      public
	 * @return      void
	 */
	public function edd_commission_fees_user_commissions_unpaid_head_row_end() {
		?>
		<th class="edd_commission_fee"><?php _e('Fee', 'edd-commission-fees'); ?></th>

		<?php
	}


	/**
	 * Commissions [edd_commissions] shortcode - Unpaid commissions table row
	 *
	 * @since       1.0.0
	 * @access      public
	 * @param				object $commission commission object which use to get the meta
	 * @return      void
	 */
	public function edd_commission_fees_user_commissions_unpaid_row_end( $commission ) {

		$fee = $commission->get_meta( '_edd_commission_fees', false ); ?>

		<td class="edd_commission_fee"><?php echo html_entity_decode( edd_currency_filter( edd_format_amount( $fee[0]['fee'] ) ) ); ?></td>

		<?php
	}


	/**
	 * Commissions [edd_commissions] shortcode - Paid commissions table header
	 *
	 * @since       1.0.0
	 * @access      public
	 * @return      void
	 */
	public function edd_commission_fees_user_commissions_paid_head_row_end() {
		?>
		<th class="edd_commission_fee"><?php _e('Fee', 'edd-commission-fees'); ?></th>

		<?php
	}


	/**
	 * Commissions [edd_commissions] shortcode - Paid commissions table row
	 *
	 * @since       1.0.0
	 * @access      public
	 * @param				object $commission commission object which use to get the meta
	 * @return      void
	 */
	public function edd_commission_fees_user_commissions_paid_row_end( $commission ) {

		$fee = $commission->get_meta( '_edd_commission_fees', false ); ?>

		<td class="edd_commission_fee"><?php echo html_entity_decode( edd_currency_filter( edd_format_amount( $fee[0]['fee'] ) ) ); ?></td>

		<?php
	}


	/**
	 * Commissions [edd_commissions] shortcode - Revoked commissions table header
	 *
	 * @since       1.0.0
	 * @access      public
	 * @return      void
	 */
	public function edd_commission_fees_user_commissions_revoked_head_row_end() {
		?>
		<th class="edd_commission_fee"><?php _e('Fee', 'edd-commission-fees'); ?></th>

		<?php
	}


	/**
	 * Commissions [edd_commissions] shortcode - Paid commissions table row
	 *
	 * @since       1.0.0
	 * @access      public
	 * @param				object $commission commission object which use to get the meta
	 * @return      void
	 */
	public function edd_commission_fees_user_commissions_revoked_row_end( $commission ) {

		$fee = $commission->get_meta( '_edd_commission_fees', false ); ?>

		<td class="edd_commission_fee"><?php echo html_entity_decode( edd_currency_filter( edd_format_amount( $fee[0]['fee'] ) ) ); ?></td>

		<?php
	}


	/**
	 * Adjust the recorded commission amount based on fee calculation
	 *
	 * Fee won't be adjusted in these instances:
	 *
	 * 1. The fee amount is greater (or equal) to the base commission amount AND Allow £0.00 commissions is disabled
	 * 2. If Disable Fee Adjustment isn't checked (enabled)
	 *
	 * @since       1.0.0
	 * @access      public
	 * @param				int $recipient - the commission recipient user id
	 * @param				float $commission_amount - the base commission amount
	 * @param				float $rate - the base commission amount
	 * @param				int $download_id - the commission download id
	 * @param				int $commission_id - the commission id
	 * @param				int $payment_id - the commission payment id
	 * @return      void
	 */
	public function edd_commission_fees_adjust_commission_fee_amount( $recipient, $commission_amount, $rate, $download_id, $commission_id, $payment_id ) {

		// Bail early if download, user or global commission fees are disabled
		if ( edd_commission_fees_disabled ( $download_id, $recipient ) ) {
			return;
		}

		$rate = $this->edd_commission_fees_get_recipient_rate( $download_id, $recipient );

		// Bail early if commission rate is 0 (empty) or null
		if ( 0 == $rate || NULL == $rate ) {
			return;
		}

		// Calculate the commission fee amount and fee amount (base commission amount - fee)
		$commission_fee	= $this->edd_commission_fees_calculate_commission_amount( $commission_amount, $download_id, $recipient );
		$fee_amount = $commission_amount - (float) $commission_fee;

		// Bail early if the fee amount is greater (or equal) to the base commission amount AND Allow £0.00 commissions is disabled
		if ( floatval( $fee_amount ) >= $commission_amount && edd_get_option( 'edd_commission_fees_allow_zero_value', 'yes' ) == 'no' ) {
			return;
		}

		// If Disable Fee Adjustment isn't checked, update the commission amount to the new value (minus the fee amount)
		if ( ! edd_commission_fees_fee_adjustment_disabled() ) {
			$commission   					= eddc_get_commission( $commission_id );
			$commission->amount     = $commission_fee;
			$commission->save();
		}

		// Hook to trigger recording the payment note(s)
		do_action( 'edd_commission_fees_insert_fee', $recipient, $commission_amount, $fee_amount, $rate, $download_id, $commission_id, $payment_id );
	}


	/**
	 * Insert the commission metadata
	 *
	 * For various reasons we store the following data:
	 *
	 * 	1. Original commission base amount (before calculations/adjustment) (float)
	 * 	2. Commission fee amount (after adjustments) (float)
	 * 	3. The "fee" amount (commission base amount - Commission fee amount) (float)
	 * 	4. Commission fee rate (float)
	 * 	5. Calculation base (exclusive or inclusive) (string)
	 * 	6. Type (percentage or flat) (string)
	 *
	 * @since       1.0.0
	 * @access      public
	 * @param				int $recipient - the commission recipient user id
	 * @param				float $commission_amount - the base commission amount
	 * @param				float $rate - the base commission amount
	 * @param				int $download_id - the commission download id
	 * @param				int $commission_id - the commission id
	 * @param				int $payment_id - the commission payment id
	 * @return      void
	 */
	public function edd_commission_fees_insert_commission_meta( $recipient, $commission_amount, $rate, $download_id, $commission_id, $payment_id ) {

		// Bail early if download, user or global fees are disabled
		if ( edd_commission_fees_disabled ( $download_id, $recipient ) ) {
			return;
		}

		$rate 					 	= $this->edd_commission_fees_get_recipient_rate( $download_id, $recipient );

		// Bail early if rate is 0 or null
		if ( 0 == $rate || NULL == $rate ) {
			return;
		}

		$commission   		= eddc_get_commission( $commission_id );
		$calc_base 			 	= edd_get_option( 'edd_commission_fees_type', 'percentage' );
		$calc_type 			 	= edd_get_option( 'edd_commission_fees_calc_base', 'yes' );

		// Set commission meta key
		$meta_key 				= '_edd_commission_fees';

		// Get $calc_type friendly name
		if ( 'yes' === $calc_type ) {
			$calc_type = 'exclusive';
		} else {
			$calc_type = 'inclusive';
		}

		// Get the commission "fee" amount and store it
		$commission_fee 	= $this->edd_commission_fees_calculate_commission_amount( $commission_amount, $download_id, $recipient, true );
		$fee_amount 			= $commission_amount - $commission_fee;

		$args = array(
			'base_amount' 	=> $commission_amount,
			'amount'				=> $commission_fee,
			'fee'						=> $fee_amount,
			'rate' 					=> $rate,
			'calc_base'			=> $calc_base,
			'calc_type'			=> $calc_type
		);

		// If flat rate type remove calc type (inclusive/exclusive) from args
		if ( 'flat' === $calc_base ) {
			unset ( $args['calc_type'] );
		}

		// Update commission meta
		$commission->update_meta( $meta_key, $args );

		do_action( 'edd_commission_fees_insert_commission_meta', $recipient, $commission_amount, $fee_amount, $rate, $download_id, $commission_id, $payment_id );

	}


	/**
	 * Store a payment note about this commission fee
	 *
	 * This makes it really easy to find commissions recorded for a specific payment.
	 * Especially useful for when payments are refunded
	 *
	 * @since       1.0.0
	 * @access      public
	 * @param				int $recipient - the commission recipient user id
	 * @param				float $commission_amount - the base commission amount
	 * @param				float $fee_amount - the commission fee
	 * @param				float $rate - the base commission amount
	 * @param				int $download_id - the commission download id
	 * @param				int $commission_id - the commission id
	 * @param				int $payment_id - the commission payment id
	 * @return      void
	 */
	public function edd_commission_fees_record_commission_note( $recipient, $commission_amount, $fee_amount, $rate, $download_id, $commission_id, $payment_id ) {

		// Bail if download, user or global fees are disabled
		if ( edd_commission_fees_disabled ( $download_id, $recipient ) ) {
			return;
		}

		$note = sprintf(
			__( 'Commission fee of %s charged for %s &ndash; <a href="%s">View</a>', 'edd-commission-fees' ),
			edd_currency_filter( edd_format_amount( $fee_amount ) ),
			get_userdata( $recipient )->display_name,
			admin_url( 'edit.php?post_type=download&page=edd-commissions&payment=' . $payment_id )
		);

		edd_insert_payment_note( $payment_id, $note );
	}

	/**
	 * Add the commission fee information to the single commission view
	 *
	 * @since       1.0.0
	 * @access      public
	 * @param				int $commission_id - the commission id
	 * @return      void
	 */
	public function edd_commission_fees_commission_card_bottom( $commission_id ) {
		if ( ! $commission_id ) {
			return;
		}

		$commission   				= eddc_get_commission( $commission_id );
		$commission_meta 			= $this->edd_commission_fees_get_commission_meta( $commission_id );

		if ( ! $commission_meta || empty( $commission_meta ) ) {
			return;
		}

		$commission_fee_rate 	= $this->edd_commission_fees_format_rate ( $commission_meta[0]['rate'], $commission_meta[0]['calc_base'] );
		$store_commission			= $this->edd_commission_fees_get_store_commission( $commission->payment_id, $commission );

		?>
		<div class="info-wrapper item-section">
			<h3>Commission Fees</h3>
			<form id="edit-item-info">
				<div class="item-info">
					<table class="widefat striped">
						<tbody>
							<tr>
								<td class="row-title">
									<label for="tablecell"><?php _e( 'Commmission Fee', 'edd-commission-fees' ); ?></label>
								</td>
								<td style="word-wrap: break-word">
									<?php echo html_entity_decode( edd_currency_filter( edd_format_amount( $commission_meta[0]['fee'] ) ) ); ?>
								</td>
							</tr>
							<tr>
								<td class="row-title">
									<label for="tablecell"><?php _e( 'Fee Type', 'edd-commission-fees' ); ?></label>
								</td>
								<td style="word-wrap: break-word">
									<?php echo ucwords ( $commission_meta[0]['calc_base'] ); ?>
								</td>
							</tr>
							<?php if ( !empty ( $commission_meta[0]['calc_type'] ) ) : ?>
							<tr>
								<td class="row-title">
									<label for="tablecell"><?php _e( 'Calculation Base', 'edd-commission-fees' ); ?></label>
								</td>
								<td style="word-wrap: break-word">
									<?php echo ucwords ( $commission_meta[0]['calc_type'] ); ?>
								</td>
							</tr>
							<?php endif; ?>
						  <tr>
							 <td class="row-title">
									<label for="tablecell"><?php _e( 'Fee Rate', 'edd-commission-fees' ); ?></label>
								</td>
								<td style="word-wrap: break-word">
									<?php echo $commission_fee_rate; ?>
								</td>
							</tr>
							<?php if ( !empty ( $store_commission ) ) : ?>
							<tr>
								<td class="row-title">
									<label for="tablecell"><?php _e( 'Store Commission', 'edd-commission-fees' ); ?></label>
								</td>
								<td style="word-wrap: break-word">
									<?php echo html_entity_decode( edd_currency_filter( edd_format_amount( $store_commission ) ) ); ?>
								</td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
				<div class="clear"></div>
			</form>
		</div>

	<?php
	}


	/**
	 * Calculate the store commission fee based on the commissions settings.
	 *
	 * @since       1.0.0
	 * @access      public
	 * @param				float $commission_amount - the commission amount
	 * @param				float $download_price - the download item price
	 * @param				float $download_subtotal - the download item subtotal
	 * @param				float $download_tax - the download item tax
	 * @return      float the calculated store commission amount
	 */
	public function edd_commission_fees_calculate_store_commission_amount( $commission_amount = 0, $download_price = 0, $download_subtotal = 0, $download_tax = 0 ) {

		$calc_base 				= edd_get_option( 'edd_commissions_calc_base', 'subtotal' );

		$store_commission = 0;

		if ( isset( $commission_amount ) && $commission_amount != 0 ) {

			// Calculate Store Commission (based on Commissions Calc Base Option)
			switch ( $calc_base ) {
				case 'subtotal':
					// Commissions Calc Base - Subtotal
					$store_commission = $download_subtotal - $commission_amount;
					break;
				case 'total_pre_tax':
					// Commissions Calc Base - Total without Taxes
					$store_commission = $download_price - $download_tax - $commission_amount;
					break;
				default:
					// Commissions Calc Base - Total with Taxes
					$store_commission = $download_price - $commission_amount;
					break;
			}

		} else {

				$store_commission = number_format( 0, 2 );

		}

		return apply_filters( 'edd_commission_fees_calculate_store_commission_amount', (float) $store_commission, $commission_amount, $download_price, $download_subtotal, $download_tax );
	}


	/**
	 * Calculate the commission fee amount.
	 *
	 * @since       1.0.0
	 * @access      public
	 * @param       float $commission_amount The base (original) commission amount
 	 * @param       int $download_id The download id - used to get the recipient rate
	 * @param       int $user_id The user ID to retrieve commission rate for
	 * @param       bool $allow_negative Set to true to return negative values (useful for storing accurate fee metadata)
	 * @return      float the commission amount
	 */
	public function edd_commission_fees_calculate_commission_amount( $commission_amount = 0, $download_id = 0, $recipient = 0, $allow_negative = false ) {

		$calc_base 			 	= edd_get_option( 'edd_commission_fees_type', 'percentage' );
		$calc_type 			 	= edd_get_option( 'edd_commission_fees_calc_base', 'yes' );

		$rate 					 	= $this->edd_commission_fees_get_recipient_rate( $download_id, $recipient );
		$calculated_rate 	= $rate / 100;

		$commission_fee 	= 0;

		if ( isset( $commission_amount ) && $commission_amount != 0 ) {

			// Calculate Store Commission (based on Commissions Calc Base Option)
			switch ( $calc_base ) {
				case 'percentage':
					// Commissions Fee Calc Base - Percentage
					if ( 'yes' == $calc_type ) {
						// Exclusive calculation
						if( $calculated_rate < 1 || true === $allow_negative ) {
							$commission_fee = $commission_amount - ( $commission_amount * $calculated_rate );
						} else {
							$commission_fee = number_format( 0, 2 );
						}

					} else {
						// Inclusive calculation
						if( $calculated_rate < 1 ) {
							$calculated_rate = $calculated_rate + 1;
							$commission_fee = ( $commission_amount / $calculated_rate );
						} else {
							$commission_fee = number_format( 0, 2 );
						}
					}
					break;
				case 'flat':
					// Commissions Fee Calc Base - Flat Amount
					$commission_fee = $commission_amount - $rate;

					// Set the commission fee to 0 if the fee is higher than the rate
					if ( $rate >= $commission_amount && false === $allow_negative ) {
						$commission_fee = number_format( 0, 2 );
					}
					break;
			}

		} else {

				$commission_fee = number_format( 0, 2 );

		}


		return apply_filters( 'edd_commission_fees_calculate_commission_amount', (float) $commission_fee, $download_id, $recipient, $allow_negative );
	}


	/**
	 * Gets the default commission fee rate
	 *
	 * @since       1.0.0
	 * @return      float
	 */
	function edd_commission_fees_get_default_rate() {
		global $edd_options;

		$rate = isset( $edd_options['edd_commission_fees_default_rate'] ) ? $edd_options['edd_commission_fees_default_rate'] : false;

		return (float) apply_filters( 'edd_commission_fees_default_rate', $rate );
	}


	/**
	 *
	 * Retrieves the commission fee rate for a product and user
	 *
	 * If $download_id is empty, the default rate from the user account is retrieved.
	 * If no default rate is set on the user account, the global default is used.
	 *
	 * This function requires very strict typecasting to ensure the proper rates are used at all times.
	 *
	 * 0 is a permitted rate so we cannot use empty(). We always use NULL to check for non-existent values.
	 *
 	 * @since 1.0.0
	 * @param       $download_id INT The ID of the download product to retrieve the commission rate for
	 * @param       $user_id INT The user ID to retrieve commission rate for
	 * @return      $rate INT|FLOAT The commission rate
	 */
	function edd_commission_fees_get_recipient_rate( $download_id = 0, $user_id = 0 ) {
		$rate = null;

		$download_rate = edd_commission_fees_get_download_rate( $download_id );

		// Check for a download specific rate
		if ( ! empty( $download_rate ) && ( null === $rate || '' === $rate ) ) {
			$rate = $download_rate;

			if ( '' === $rate ) {
				$rate = null;
			}
		}

		// Check for a user specific global rate
		if ( ! empty( $user_id ) && ( null === $rate || '' === $rate ) ) {
			$rate = get_user_meta( $user_id, 'eddcf_user_fee_rate', true );

			if ( '' === $rate ) {
				$rate = null;
			}
		}

		// Check for an overall global rate
		if ( null === $rate && $this->edd_commission_fees_get_default_rate() ) {
			$rate = $this->edd_commission_fees_get_default_rate();
		}

		// Set rate to 0 if no rate was found
		if ( null === $rate || '' === $rate ) {
			$rate = 0;
		}

		return apply_filters( 'edd_commission_fees_get_recipient_rate', (float) $rate, $download_id, $user_id );
	}


	/**
	 * This function retrieves the audio files meta from the download and runs them through a filter.
	 * Anywhere you retrieve audio files download meta, you should do it using this function.
	 *
	 * @since 1.0.0
	 * @param int $download_id
	 *
	 * @return void
	 */
	public function edd_commission_fees_get_commission_meta( $commission_id = 0 ) {
		if ( empty( $commission_id ) ) {
			return;
		}

		$commission = eddc_get_commission( $commission_id );

		if ( ! $commission ) {
			return false;
		}

		// Get the commission meta from the database
		$commission_meta = $commission->get_meta( '_edd_commission_fees', false );

		// Filter those
		$commission_meta = apply_filters( 'edd_commission_fees_get_commission_meta', $commission_meta, $commission_id );

		return $commission_meta;
	}


	/**
	 * This function gets the "Store Commission" (earnings) based on the cart item price
	 *
	 * @since 1.0.0
	 * @param int $payment_id
	 * @param object $commission
	 *
	 * @return float $store_commission - the store earnings after commission deduction
	 */
	public function edd_commission_fees_get_store_commission( $payment_id, $commission ) {

		// Get cart details
		$cart_details = edd_get_payment_meta_cart_details ( $payment_id );
		$download = new EDD_Download( $commission->download_id );

		foreach ( $cart_details as $cart_detail ) {
			if ( $cart_detail['id'] == $commission->download_id ) {
				if ( $download->has_variable_prices() ) {
						if ( (int) $cart_detail['item_number']['options']['price_id'] == (int) $commission->price_id ) {
							$store_commission = $this->edd_commission_fees_calculate_store_commission_amount( $commission->amount, $cart_detail['price'], $cart_detail['subtotal'], $cart_detail['tax'] );
						}
					} else {
							$store_commission = $this->edd_commission_fees_calculate_store_commission_amount( $commission->amount, $cart_detail['price'], $cart_detail['subtotal'], $cart_detail['tax'] );
					}
			}
		}

		return apply_filters( 'edd_commission_fees_get_store_commission', (float) $store_commission );
	}


	/**
	 * This will take a rate and a commission fee type and format it correctly for output.
	 * For example, if the rate is 5 and the commission type is "percentage", it will return "5%" as a string.
	 * If the rate is 5 and the commission type is "flat", it will return "$5" as a string.
	 *
	 * @since       1.0.0
	 * @param       int $unformatted_rate This is the number representing the rate.
	 * @param       string $commission_type This is the type of commission.
	 * @return      string $formatted_rate This is the rate formatted for output.
	 */
	public function edd_commission_fees_format_rate( $unformatted_rate, $commission_type ){

		// If the commission type is "percentage"
		if ( 'percentage' == $commission_type ) {

			// Format the rate to have the percentage sign after it.
			$formatted_rate = $unformatted_rate . '%';

		} else {

			// If the rate is anything else, format it as if it were a flat rate, or "dollar" amount. We add the currency symbol before it. For example, "$5".
			$formatted_rate = edd_currency_filter( edd_sanitize_amount( $unformatted_rate ) );

		}

		// Filter the formatted rate so it can be modified if needed
		return apply_filters( 'edd_commission_fees_format_rate', $formatted_rate, $unformatted_rate, $commission_type );
	}


	/**
	 * Register the commissions fees report details batch exporter
	 *
	 * @since       1.0.0
	 * @return      void
	 */
	public function edd_commission_fees_register_commissions_fees_report_details_batch_export() {
		add_action( 'edd_batch_export_class_include', array( $this, 'edd_commission_fees_include_commissions_fees_report_details_batch_processor' ), 10, 1 );
	}


	/**
	 * Loads the commissions fees report batch process if needed
	 *
	 * @since       1.0.0
	 * @param       string $class The class being requested to run for the batch export for details
	 * @return      void
	 */
	public function edd_commission_fees_include_commissions_fees_report_details_batch_processor( $class ) {
		if ( 'EDD_Batch_Commissions_Fees_Report_Details_Export' === $class ) {
			require_once EDD_COMMISSION_FEES_DIR . 'includes/classes/class-batch-export-commissions-fees-report-details.php';
		}
	}


	/**
	 * Add the "Fees" report type to the commissions reporting options.
	 *
	 * @since       1.0.0
	 * @access      public
	 * @param       array $options containing the registered "Fees" report type.
	 * @return      array
	 */
	public function edd_commission_fees_commissions_export_classes( $options ) {
		$options['EDD_Batch_Commissions_Fees_Report_Details_Export'] = __( 'Fees', 'edd-commission-fees' );

		return $options;
	}


	/**
	 * Append "Fees" report type details to the tooltip description.
	 *
	 * @since       1.0.0
	 * @access      public
	 * @param       string $tooltip_desc string
	 * @return      string
	 */
	public function edd_commission_fees_commissions_export_tooltip_description( $tooltip_desc ) {
		$tooltip_desc  .= __( '<p><strong>Fees Report</strong><br />Provides a list of all commission records for the dates and status selected. Includes fee, vendor, payment and cart details for segmentation, reporting and accounting purposes.</p>', 'edd-commission-fees' );

		return $tooltip_desc;
	}


}
