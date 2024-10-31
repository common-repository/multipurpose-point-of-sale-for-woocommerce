<?php
/**
 * Tmd Pos payment Setting.
 *
 * @package tmd-pos-stock
 */	
defined( 'ABSPATH' ) || exit;
global $wpdb;

$tmd_pos_wc_gateway       = new WC_Payment_Gateways();
$tmd_pos_payment_gateways = $tmd_pos_wc_gateway->payment_gateways();
$pos_table                = $wpdb->prefix . "tmd_pos";
$query                    = $wpdb->prepare("SELECT * FROM $pos_table WHERE `tmd_option` = %s", 'tmd_payment_option');
$paymentdatas             = $wpdb->get_row($query);

if( !empty( $paymentdatas->tmd_option_value ) ){
	$payment_datas = json_decode($paymentdatas->tmd_option_value);
}

?>
<table class="pos-payment-container form-table" id="tmd-pos-payment">
	<caption><h2><?php esc_html_e('Payment Gateways','tmdpos'); ?></h2></caption>
	<tbody>
		<tr>
			<!-- <th class="pay-gateway-title"></th> -->
			<td class="body-wth-textarea">
				<table class="tmd-pos-pay-gateway">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Gateway', 'tmdpos' ); ?></th>
							<th><?php esc_html_e( 'Default', 'tmdpos' ); ?></th>
							<th><?php esc_html_e( 'POS Pay ID', 'tmdpos' ); ?></th>
						</tr>
					</thead>

					<tbody>
						<?php foreach( $tmd_pos_payment_gateways as $payment_gateway_id =>  $tmd_pos_gateway ): ?>
							<tr>
								<td class="tmd_pos_gateway_title">
									<label><?php echo esc_html( $tmd_pos_gateway->get_title() ); ?></label>
								</td>
								<td>
									<input type="radio" name="tmd_pos_gateway" <?php if(!empty($payment_datas)): foreach($payment_datas as $paymentdatas){ if(!empty($paymentdatas)){ if($paymentdatas == 'default'.'_'.$payment_gateway_id) { echo "checked"; } } } endif; ?> value="default<?php echo '_'.esc_attr( $payment_gateway_id ); ?>" />
								</td>
								<td><label><?php echo esc_html( $payment_gateway_id ); ?></label></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
