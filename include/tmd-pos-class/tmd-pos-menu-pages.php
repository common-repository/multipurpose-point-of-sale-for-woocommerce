<?php
/**
 * tmd pos menu pages
 *
 */
defined( 'ABSPATH' ) || exit;
$pos_order_datass = tmdpos_get_orders();
?>

<!-- Order List -->
<div class="tmd_pos_order_list_main">
	<div class="tmdpos-title-left"><?php esc_html_e( 'Order List', 'tmdpos' ); ?> </div>
	<span class="close_order_list dashicons dashicons-arrow-left-alt"></span>
	<table id="tmd_pos_order_list" data-order='[[ 0, "desc" ]]' class="tmd_pos_order_list display nowrap">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Order ID',' tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Customer',' tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Status',' tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Total',' tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Date Added',' tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Date Modified',' tmdpos' ); ?></th>
				<th class="tmdpos- right-align"><?php esc_html_e('Action',' tmdpos' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php 
				foreach ( $pos_order_datass as $pos_order_datas ){
					$pos_order_id  = $pos_order_datas->order_meta;
					$order_details = json_decode( $pos_order_datas->order_value );
				  	$cashier_id    = !empty( $order_details->cashier_id ) ? $order_details->cashier_id : 1;

				  	if( $cashier_id == get_current_user_id() || $current_user->roles[0] == 'administrator' ){
						$order 		  = wc_get_order( $pos_order_id );
						$order_data   = $order->get_data();
						$customer 	  = new WP_User( $order_data['customer_id'] );

						?>
							<tr>
								<td><?php echo esc_html( $pos_order_id ); ?></td>
								<td>
									<?php 
										if( ($customer->user_nicename == 'admin') ){  
											esc_html_e( 'Guest', 'tmdpos' );  
										}
										else{ 
											echo esc_html( $customer->user_nicename ); 
										}
									?>		
								</td>
								<td><?php echo esc_html( $order_data['status'] ); ?></td>
								<td><?php echo esc_html( get_woocommerce_currency_symbol().$order->get_total() ); ?></td>
								<td><?php echo esc_html( $order_data['date_created']->date('Y-m-d H:i:s') ); ?></td>
								<td><?php echo esc_html( $order_data['date_modified']->date('Y-m-d H:i:s') ); ?></td>
								<td class="tmdpos-right-align">
									<button class="button print_order_details" data-orderId="<?php echo esc_attr( $pos_order_id ); ?>"><?php esc_html_e( 'Print', 'tmdpos' ); ?></button>&nbsp;&nbsp;&nbsp;
									<button class="button edit_order_details" data-orderId="<?php echo esc_attr( $pos_order_id ); ?>"><?php esc_html_e( 'Edit', 'tmdpos' ); ?></button>
								</td>
							</tr>
						<?php
					}
				}
			?>
		</tbody>
	</table>
</div>
<!-- Order List end -->

<!-- customer List -->
<div class="tmd_pos_customer_list_main">
	<div class="tmdpos-title-left"><?php esc_html_e( 'Customer List', 'tmdpos' ); ?> </div>
	<span class="close_customer_list cursor_pointer dashicons dashicons-arrow-left-alt"></span>
	<?php 
		$args = [
	        'blog_id'      => 1,
	        'role__not_in' => ['administrator'],
	        'orderby'      => 'nicename',
	        'order'        => 'ASC',
	        'fields'       => 'all',
		];
		$customers = get_users($args);
	?>
	<table id="tmd_pos_customer_list" class="display nowrap">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Customer Name', 'tmdpos' ); ?></th>
				<th><?php esc_html_e( 'email', 'tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Address', 'tmdpos' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php 
				foreach ($customers as $key => $customer){
					$billing_address_1 = get_user_meta( $customer->ID, 'billing_address_1', true );
					?>
						<tr>
							<td><?php echo esc_html( $customer->display_name ); ?></td>
							<td><?php echo esc_html( $customer->user_email ); ?></td>
							<td><?php echo esc_html( $billing_address_1 ); ?></td>
						</tr>
					<?php  
				} 
			?>	
		</tbody>
	</table>
</div>
<!-- customer List end -->

<!-- product List -->
<?php 
	global $wpdb;
	$icon_url = TMDPOS_IMAGE_PATH. 'searchicon.png'; 
	$args     = array( 'post_type' => 'product', 'posts_per_page' => -1 );
	$loop     = new WP_Query( $args );
?>
<div class="tmd_pos_product_list_main">
	<div class="tmdpos-title-left"><?php esc_html_e( 'Product List', 'tmdpos' ); ?> </div>
	<span class="close_product_list cursor_pointer dashicons dashicons-arrow-left-alt"></span>
	<table id="tmd_product_list" class="display nowrap">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Product Img', 'tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Name', 'tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Quantity', 'tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Product Category', 'tmdpos' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php 

				while ( $loop->have_posts() ) : $loop->the_post();
		        	global $product;

			        $products     = wc_get_product( $product->get_id() );
					$product_qty  =  $products->get_stock_quantity();
			        $stock_status = $product->get_stock_status();  
			        $terms        = get_the_terms( $product->get_id(), 'product_cat' );
					?>

						<tr>
							<td><a><img class="tmd_product_img" src="<?php echo esc_url( get_the_post_thumbnail_url() ); ?>"></a></td>
							<td><a><?php the_title(); ?></a></td>
							<td>
								<?php echo esc_html( $product_qty ); ?>&nbsp;<br>
								<span class="<?php if( $stock_status == 'instock' ){ echo esc_attr( 'tmd_instock' ); } if( $stock_status == 'onbackorder' ){ echo esc_attr( 'tmd_onbackorder' ); } if( $stock_status == 'outofstock' ){ echo esc_attr( 'tmd_outofstock' ); } ?>"><?php echo esc_attr( $stock_status ); ?></span>
							</td>
							<td>
								<?php 
									foreach( $terms as $category){ 
										echo esc_html( $category->name ).','; 
									} 
								?>
							</td>
						</tr>
					<?php 
				endwhile;   
				wp_reset_query(); 
			?>
		</tbody>
	</table>
</div>
<!-- product List end -->


<!-- sale report  -->
<div class="tmd_pos_report_list_main">
	<div class="tmdpos-title-left"> <?php esc_html_e('Sales Report','tmdpos'); ?> </div>
	<span class="close_repost_list cursor_pointer dashicons dashicons-arrow-left-alt"></span>
	<span class="print_sale_report"><?php esc_html_e('Print', 'tmdpos'); ?></span>
	<?php 
		

	?>
	<table id="tmd_pos_repost_list" data-order='[[ 0, "desc" ]]' class="tmd_pos_repost_list display nowrap" style="width:100%">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Order ID', 'tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Date', 'tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Payment Mode', 'tmdpos' ); ?></th>
				<th><?php esc_html_e( 'VAT', 'tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Total', 'tmdpos' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php 
				global $wpdb, $woocommerce; 
				$table_order        = $wpdb->prefix . 'tmd_pos_order';
				$order_datas        = $wpdb->get_results( "SELECT * FROM $table_order" );
				$ccurrency 			= get_woocommerce_currency_symbol();
				$grand_tax_total 	= '0';
				$grand_order_total 	= '0';

				foreach( $order_datas as $order_data ){ 
				  	$order_details = json_decode( $order_data->order_value );
				  	$cashier_id    = !empty( $order_details->cashier_id ) ? $order_details->cashier_id : 1;

				  	if( $cashier_id == get_current_user_id() || $current_user->roles[0] == 'administrator' ){
				  		$tax_total   = !empty($order_details->tax_total) ? $order_details->tax_total : 0;
				  		$order_total = !empty($order_details->order_total) ? $order_details->order_total : 0;
				 		
				 		$grand_tax_total   += $tax_total;
				 		$grand_order_total += $order_total;
						?>
							<tr>
								<td><?php echo esc_html( $order_data->order_meta ); ?></td>
								<td><?php echo esc_html( date('Y-m-d', strtotime( $order_data->order_date ) ) ); ?></td>
								<td><?php echo esc_html( $order_details->payment_method ); ?></td>
								<td><?php echo esc_html( $ccurrency.$order_details->tax_total ); ?></td>
								<td><?php echo esc_html( $ccurrency.$order_details->order_total ); ?></td>
							</tr>
						<?php 
					}
				} 
			?>
		</tbody>
		<tfoot>
			<tr>
				<th></th>
				<th></th>
				<th><?php esc_html_e( 'Grand Total', 'tmdpos' );  ?></th>
				<th><?php echo esc_html( $ccurrency.$grand_tax_total ); ?></th>
				<th><?php echo esc_html( $ccurrency.$grand_order_total ); ?></th>
			</tr>
		</tfoot>
	</table>
</div>
<!-- sale report List end -->

<!-- order_update form -->
<div class="tmd_pos_order_update_container"></div>
<!-- order_update form end -->

<!-- print order div-->
<div class="tmd_order_invoice_print"></div>
<!-- print order div end-->

<!-- stock in poup div -->
<div class="tmd_stock_in_popup"></div>
<!-- stock in poup div end -->

<!-- stock print sale report data -->
<div class="tmd_pos_sale_report_main_div"></div>
<!-- stock print sale report data end -->