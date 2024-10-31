<?php 
/**
 * tmd pos hold order list data
 */

defined( 'ABSPATH' ) || exit;
global $wpdb;

$table_option     = $wpdb->prefix.'tmd_pos_option';
$hold_order_datas = $wpdb->get_results( "SELECT * FROM $table_option ORDER BY `tmd_option_id` DESC" );
?>
<div class="tmd_pos_holdorder_list">

	<div class="hold_order_list_main">
		<div class="close_hold_order_list">&#10006;</div>
		<div class="for_message_div"></div>
	
		<?php 
			if( !empty( $hold_order_datas ) ){ 
				?>
					<table class="tmdpos_hold_order_table display nowrap dataTable no-footer">
						<thead>
							<tr>
								<th class="tmd-text-left"><?php esc_html_e( 'Hold On Id', 'tmdpos' ); ?></th>
								<th class="tmd-text-left"><?php esc_html_e( 'Hold Reason', 'tmdpos' ); ?></th>
								<th class="tmd-text-right"><?php esc_html_e( 'Action', 'tmdpos' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php 
								foreach ($hold_order_datas as $hold_order_data){ 
									$holdorder_value = json_decode($hold_order_data->option_value);
									$hold_note       = !empty($holdorder_value->hold_note) ? $holdorder_value->hold_note:'';
									?>
										<tr>
											<td class="tmd-text-left"><?php echo esc_html( $hold_order_data->tmd_option_id ); ?></td>
											<td class="tmd-text-left"><?php echo esc_html( $hold_note ); ?></td>
											<td class="tmd-text-right">
												<button data-hold-id="<?php echo esc_attr( $hold_order_data->tmd_option_id ); ?>" class="button-primary hold_order_add_to_cart"><?php esc_html_e('Add To Cart', 'tmdpos'); ?></button>
											</td>
										</tr>
									<?php
								}
							?>
						</tbody>
					</table>
				<?php  
			}
			else{  
				?>	
					<p class="tmd-text-center"><span><?php esc_html_e( 'Ther is no hold order found in the list !', 'tmdpos' ); ?></span></p>	
				<?php 
			} 
		?>
	</div>
</div>