<?php
/**
 * tmd pos stock seeting 
 * 
 * @package tmd-pos-stock
 */
defined( 'ABSPATH' ) || exit;
global $wpdb;
$pos_table   = $wpdb->prefix . "tmd_pos";
$query       = $wpdb->prepare("SELECT * FROM $pos_table WHERE `tmd_option` = %s", 'tmd_stock_option');
$stock_datas = $wpdb->get_row($query);
$stockdatas  = !empty($stock_datas->tmd_option_value) ? json_decode($stock_datas->tmd_option_value):'';
?>
<table class="pos-stock-container form-table">
	<tbody>
		<tr>
			<th><label><?php esc_html_e( 'Show Out Of Stock Warning', 'tmdpos' ); ?></label></th>
			<td>
				<input type="radio" name="product_status" value="enable" <?php if(is_object($stockdatas)){ checked( $stockdatas->product_status, 'enable' ); } ?> /><?php esc_html_e( 'Enable', 'tmdpos' ); ?> &nbsp; 
				<input type="radio" name="product_status" value="disable" <?php if(is_object($stockdatas)){ checked( $stockdatas->product_status, 'disable' ); } ?> /><?php esc_html_e( 'Disable', 'tmdpos' ); ?>
			</td>
		</tr>
	</tbody>
</table>
