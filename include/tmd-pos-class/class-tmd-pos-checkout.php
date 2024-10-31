<?php
/**
 * tmd pos checkout
 *
 * @package tmd_pos_order_checkout
 */
defined( 'ABSPATH' ) || exit;
global $wpdb;
$pos_table                = $wpdb->prefix . "tmd_pos";/*tmd pos payment table*/
$tmd_pos_wc_gateway       = new WC_Payment_Gateways();
$tmd_pos_payment_gateways = $tmd_pos_wc_gateway->payment_gateways();
$paymentdatas             = $wpdb->get_row($wpdb->prepare("SELECT * FROM $pos_table WHERE `tmd_option` = %s", 'tmd_payment_option'));
$payment_datas            =	!empty( $paymentdatas ) ? json_decode( $paymentdatas->tmd_option_value ):'';
$tmd_general              = $wpdb->get_row($wpdb->prepare("SELECT * FROM $pos_table WHERE `tmd_option` = %s", 'tmd_general_option'));
$checkout_data            = $wpdb->get_row($wpdb->prepare("SELECT * FROM $pos_table WHERE `tmd_option` = %s", 'tmd_checkout_option'));
$checkoutpotiondatas      = !empty($checkout_data->tmd_option_value) ? json_decode($checkout_data->tmd_option_value):'';

if( !empty( $tmd_general ) ){ 
	$general_datas = json_decode( $tmd_general->tmd_option_value ); 
	$pos_user      = get_user_meta( get_current_user_id(), 'first_name', true );
}

?>
<div id="tmd_pos_checkout_pop" class="tmd-pos-modal-main">
	<div class="checkout-main-div tmd-pos-modal">
       <div id="dismiss-checkout" class="cursor_pointer">X</div>
		<div class="checkout_form">
			<table class="tmd_pos_table">
				<tbody>
					<tr>
						<th class="tmd_pos_th"><?php esc_html_e( 'Select Customer', 'tmdpos' );?></th>
						<td class="tmd_pos_td"><input type="checkbox" id="shop_guest" value="<?php esc_html_e('Guest', 'tmdpos'); ?>" name="tmd_pos_customer" checked="checked" /><label for="shop_guest"><?php esc_html_e(' Guest Customer', 'tmdpos') ; ?></label><input type="hidden" value="<?php if( !empty( $pos_user ) ){ echo esc_attr( $pos_user ); } ?>" name="tmd_pos_cashier" /></span></td>
						<input type="hidden" value="<?php echo esc_attr( absint( get_current_user_id() ) ); ?>" name="tmd_pos_cashier_id" />
					</tr>
					<tr>
						<th class="tmd_pos_th"></th>
						<td class="tmd_pos_td">
							<p>---<?php esc_html_e( 'OR', 'tmdpos' ); ?>---</p>
							<div class="shop_customer_select">
								<select name="shop_customer" class="shop_customer">
									<option value="0" disabled="disabled" selected="selected">---<?php esc_html_e('Select Customer', 'tmdpos'); ?>---</option>
									<?php

										$args = [
									        'blog_id'      => 1,
									        'role__not_in' => ['administrator'],
									        'orderby'      => 'nicename',
									        'order'        => 'ASC',
									        'fields'       => 'all',
										];

									    $users = get_users($args);
									    foreach ($users as $key => $value) {
									    	?>
												<option value="<?php echo esc_attr( $value->ID ); ?>" data-name="<?php echo esc_attr( $value->user_nicename ); ?>"><?php echo esc_html( $value->user_nicename ); ?></option>
									    	<?php
									    }
									?>
								</select>
							</div>
						</td>
					</tr>

					<tr>
						<th class="tmd_pos_th"><?php esc_html_e('Add Customer', 'tmdpos');?></th>
						<td class="tmd_pos_td">
							<a class="button add_new_customer" href="javascript:void(0)"><?php esc_html_e('+Add New','tmdpos'); ?></a>
							<div class="tmd_notice"></div>
							<table class="tmd_add_customer"></table>
						</td>
					</tr>

					<tr>
						<th class="tmd_pos_th"><?php esc_html_e( 'Payment Method', 'tmdpos');?></th>
						<td class="tmd_pos_td">
							<div class="payment_select">
								<select class="tmd_pos_select" name="payment_method">
									<?php 
										foreach ($tmd_pos_payment_gateways as $payment_gateway_id => $tmd_pos_payment_mode){
											?>
											<option value="<?php echo esc_attr($tmd_pos_payment_mode->get_title()); ?>" <?php if(empty($payment_datas)){ if($payment_gateway_id == 'tmd_pos_cash'){ echo 'selected'; } } ?> <?php if(!empty($payment_datas)): foreach($payment_datas as $paymentdatas){ if(!empty($paymentdatas)){ if($paymentdatas == 'default'.'_'.$payment_gateway_id) { echo "selected"; } } } endif; ?> orderpaymode="<?php echo esc_attr( $payment_gateway_id ); ?>" ><?php echo esc_html( $tmd_pos_payment_mode->get_title()); ?></option>
											<?php 
										}
									?>
								</select>
							</div>
						</td>
					</tr>

					<tr>
						<th class="tmd_pos_th"><?php esc_html_e( 'Order Status', 'tmdpos' );?></th>
						<?php $tmd_pos_order_status_lists = wc_get_order_statuses();  ?>
						<td class="tmd_pos_td">
							<div class="order_status">
								<select class="tmd_pos_select" name="order_status">
									<?php 
										foreach ($tmd_pos_order_status_lists as $tmd_pos_key => $tmd_pos_order_status_list){ 
											?>
												<option <?php if(empty($checkoutpotiondatas->_order_status)){ if( $tmd_pos_key == 'wc-completed' ){ echo 'selected'; } } ?> <?php if(!empty($checkoutpotiondatas->_order_status)){ if($checkoutpotiondatas->_order_status == $tmd_pos_key){ echo "selected"; }} ?> value="<?php echo esc_attr( $tmd_pos_key ); ?>"><?php echo esc_html( $tmd_pos_order_status_list ); ?></option>
											<?php 
										}
									?>
								</select>
							</div>
						</td>
					</tr>
					<tr>
						<th class="tmd_pos_th"><?php esc_html_e( 'Total', 'tmdpos' );?></th>
						<td class="tmd_pos_td"><input type="number" class="order_now_total" name="order_total" value="" readonly="readonly" /></td>
					</tr>
					<tr>
						<th class="tmd_pos_th"><?php esc_html_e( 'Shipping Cost', 'tmdpos' );?></th>
						<input type="hidden" name="wt_ship_total" class="wt_ship_total" value="" />
						<td class="tmd_pos_td"><input type="number" min="1" data-total="" onfocus="this.select();" oninput="shipping()" class="shipping_total" name="shipping_cost" value="0" /></td>
					</tr>
					<tr>
						<!-- order with shipping cost  -->
						<th class="tmd_pos_th"><?php esc_html_e( 'Discount', 'tmdpos' );?></th>
						<input type="hidden" name="wt_dis_total" class="wt_dis_total" value="">
						<td class="tmd_pos_td"><input type="number" min="1" data-total="" onfocus="this.select();"  oninput="discount()" class="discount_total" name="discount" value="0" /></td>
					</tr>

					<tr>
						<th class="tmd_pos_th"><?php esc_html_e( 'Paid Amount', 'tmdpos' );?></th>
						<td class="tmd_pos_td"><input type="number" min="" name="paid_amount" onfocus="this.select();" class="paid_amount" value="0" /></td>
					</tr>

					<tr>
						<th class="tmd_pos_th"><?php esc_html_e( 'Change', 'tmdpos' );?></th>
						<td class="tmd_pos_td"><input type="number" readonly="readonly" name="change" class="tmd_change" value="" /></td>
					</tr>

					<tr>
						<th class="tmd_pos_th"><?php esc_html_e( 'Note', 'tmdpos' );?></th>
						<td class="tmd_pos_td"><textarea class="tmd_pos_textarea" name="order_note"></textarea></td>
					</tr>

					<tr>
						<th class="tmd_pos_th"></th>
						<td class="tmd_pos_td"><button class="pos_order_now cursor_pointer"><?php esc_html_e( 'Order Now', 'tmdpos' ); ?></button></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>