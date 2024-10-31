<?php
/**
 * tmd pos checkout setting
 *
 * @package tmd-pos-chekout
 */

defined( 'ABSPATH' ) || exit;

global $wpdb;
$table = $wpdb->prefix . "tmd_pos";
$query = $wpdb->prepare( "SELECT * FROM $table WHERE `tmd_option` = %s", 'tmd_checkout_option' );
$datas = $wpdb->get_row($query); // Execute the prepared query
$data  = !empty( $datas->tmd_option_value ) ? json_decode( $datas->tmd_option_value ) : '';
?>

<table class="pos-checkout-container form-table">		
	<tbody>
		<tr>
			<th><?php esc_html_e( 'Tmd Pos Order Status', 'tmdpos' ); ?></th>
			<td>
				<select name="pos_order_status">
					<?php 
						foreach ( wc_get_order_statuses() as $key => $value ){
							?>
								<option value="<?php echo esc_attr($key); ?>" <?php if( is_object( $data ) ){ selected($data->_order_status, $key); } ?>><?php echo esc_html( $value ); ?></option>
							<?php 
						};
					?>
				</select>
			</td>
		</tr>
	</tbody>
</table>	