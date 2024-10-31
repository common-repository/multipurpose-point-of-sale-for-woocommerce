<?php
/**
 * Tmd pos invoice function
 *
 * @package tmd-pos-invoice
 */
defined( 'ABSPATH' ) || exit;
global $wpdb;

$pos_table        = $wpdb->prefix . "tmd_pos";
$query            = $wpdb->prepare("SELECT * FROM $pos_table WHERE `tmd_option` = %s", 'tmd_invoice_option');
$invoice_datas    = $wpdb->get_row($query);
$company_logo_url = TMDPOS_PLUGIN_URL. 'assets/images/company_logo.png';

if(!empty($invoice_datas->tmd_option_value)){
	$invoicedatas = json_decode($invoice_datas->tmd_option_value);
}

?>
<table class="pos-invoice-container form-table">
	<tbody class="tmd_vertical_top">
		<tr>
			<th><?php esc_html_e( 'Logo', 'tmdpos' ); ?></th>
			<td>
				<img style="display: block;" class="tmd-pos-img" id="recieptlogo" src="<?php if(!empty($invoicedatas->reciept_logo)){ echo esc_url( $invoicedatas->reciept_logo ); } else{ echo esc_url( $company_logo_url ); } ?>" />
				<input type="hidden" name="reciept_logo" value="<?php if(!empty($invoicedatas->reciept_logo)){ echo $invoicedatas->reciept_logo; } ?>" id="reciept_logo" class="regular-text"><p hidden="hidden"><input type="button" name="tmd-pos-upload-btn" id="tmd-pos-upload-btn" class="button" value="Upload Image"></p>
			</td>
		</tr>
		
		<tr>
			<th><?php esc_html_e( 'Logo size', 'tmdpos' ); ?></th>
			<td>
				<input min="1" type="number" name="logo_size_height" placeholder="Height" value="<?php if(!empty($invoicedatas->logo_size_height)){ echo esc_attr( $invoicedatas->logo_size_height ); } else{ echo 50; } ?>" /> 
				<input min="1" type="number" name="logo_size_width" placeholder="Width" value="<?php if(!empty($invoicedatas->logo_size_width)){ echo esc_attr( $invoicedatas->logo_size_width ); }else{ echo 50; } ?>" />
			</td>
		</tr>
		
		<tr>
			<th><?php esc_html_e( 'Reciept Formats', 'tmdpos' ); ?></th>
			<td>
				<select class="tmd-pos-select" name="reciept_formate">
					<option <?php if(!empty($invoicedatas->reciept_formate)){ selected( $invoicedatas->reciept_formate, 'Reciept Print'); }?> ><?php esc_html_e('Reciept Print', 'tmdpos'); ?></option>
					<option <?php if(!empty($invoicedatas->reciept_formate)){ selected( $invoicedatas->reciept_formate, 'Full Size'); }?> ><?php esc_html_e('Full Size', 'tmdpos'); ?></option>
				</select>
			</td>
		</tr>
		
		<tr>
			<th><?php esc_html_e( 'Reciept Size', 'tmdpos' ); ?></th>
			<td>
				<input min="1" type="number" name="reciept_width" value="<?php if(!empty($invoicedatas->reciept_width)){ echo esc_attr( $invoicedatas->reciept_width ); } else { echo 50; } ?>" />
				<p><?php esc_html_e( 'Reciept Size ex:50', 'tmdpos' ); ?></p>
			</td>
		</tr>
		
		<tr>
			<th><?php esc_html_e( 'Show Store Logo', 'tmdpos' ); ?></th>
			<td>
				<input type="checkbox" value="enable" name="show_logo" <?php if(!empty($invoicedatas->show_logo)){ echo "checked"; } ?> /><?php esc_html_e( 'Disable', 'tmdpos' ); ?>
			</td>
		</tr>
		
		<tr>
			<th><?php esc_html_e('Show Store Name', 'tmdpos'); ?></th>
			<td>
				<input type="checkbox" value="enable" name="show_store_name" <?php if(!empty($invoicedatas->show_store_name)){ echo "checked"; } ?> /><?php esc_html_e( 'Disable', 'tmdpos' ); ?>
			</td>
		</tr>

		<tr>
			<th><?php esc_html_e( 'Show Store Address', 'tmdpos' ); ?></th>
			<td><input type="checkbox" value="enable" name="show_store_address" <?php if(!empty($invoicedatas->show_store_address)){ echo "checked"; } ?>/><?php esc_html_e( 'Disable', 'tmdpos' ); ?></td>
		</tr>

		<tr>
			<th><?php esc_html_e( 'Show Order Date & Time', 'tmdpos' ); ?></th>
			<td><input type="checkbox" value="enable" name="show_order_date" <?php if(!empty($invoicedatas->show_order_date)){ echo "checked"; } ?> /><?php esc_html_e( 'Disable', 'tmdpos' ); ?></td>
		</tr>

		<tr>
			<th><?php esc_html_e( 'Show Invoice Number', 'tmdpos' ); ?></th>
			<td><input type="checkbox" value="enable" name="show_invoice_number" <?php if(!empty($invoicedatas->show_invoice_number)){ echo "checked"; } ?> /><?php esc_html_e( 'Disable', 'tmdpos' ); ?></td>
		</tr>
		
		<tr>
			<th><?php esc_html_e( 'Show Cashier Name', 'tmdpos' ); ?></th>
			<td><input type="checkbox" value="enable" name="show_cashier_name" <?php if(!empty($invoicedatas->show_cashier_name)){ echo "checked"; } ?> /><?php esc_html_e( 'Disable', 'tmdpos' ); ?></td>
		</tr>
		
		<tr>
			<th><?php esc_html_e( 'Show Customer Name', 'tmdpos' ); ?></th>
			<td><input type="checkbox" value="enable" name="show_customer_name" <?php if(!empty($invoicedatas->show_customer_name)){ echo "checked"; } ?> /><?php esc_html_e( 'Disable', 'tmdpos' ); ?></td>
		</tr>
		
		<tr>
			<th><?php esc_html_e( 'Show Shipping Address', 'tmdpos' ); ?></th>
			<td><input type="checkbox" value="enable" name="show_shipping_address" <?php if(!empty($invoicedatas->show_shipping_address)){ echo "checked"; } ?> /><?php esc_html_e( 'Disable','tmdpos' ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Show Payment Mode', 'tmdpos' ); ?></th>
			<td><input type="checkbox" value="enable" name="show_Payment_mode" <?php if(!empty($invoicedatas->show_Payment_mode)){ echo "checked"; } ?> /><?php esc_html_e( 'Disable', 'tmdpos' ); ?></td>
		</tr>
		
		<tr>
			<th><?php esc_html_e('Show Change','tmdpos'); ?></th>
			<td><input type="checkbox" value="enable" name="show_changes" <?php if(!empty($invoicedatas->show_changes)){ echo "checked"; } ?> /><?php esc_html_e( 'Disable', 'tmdpos' ); ?></td>
		</tr>
		
		<tr>
			<th class="head-wth-textarea"><?php esc_html_e( 'Show Extra Information (like TIN No. ST No.)', 'tmdpos' ); ?></th>
			<td class="body-wth-textarea"><textarea name="invoice_extra_info" placeholder="<?php esc_attr_e( 'Show Extra Information (like TIN No. ST No.)', 'tmdpos' ); ?>"><?php if(!empty($invoicedatas->invoice_extra_info)){ echo esc_html( $invoicedatas->invoice_extra_info ); } ?></textarea></td>
		</tr>
		
		<tr>
			<th class="head-wth-textarea"><?php esc_html_e('Invoice Text'); ?></th>
			<td class="body-wth-textarea"><textarea name="invoice_thanks_msg" placeholder="<?php esc_attr_e( "Eg. 'Thankyou For Shopping With Us '", 'tmdpos'); ?>"><?php if(!empty($invoicedatas->invoice_thanks_msg)){ echo esc_html( $invoicedatas->invoice_thanks_msg ); } ?></textarea></td>
		</tr>
	</tbody>
</table>

