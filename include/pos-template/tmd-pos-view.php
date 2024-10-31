<?php
/**
 * tmd pos front view 
 *
 * @package tmd-pos-view template
 */

defined( 'ABSPATH' ) || exit;
wp_head();

/*search, barcode, company logo and empty_img icon img url*/
$tmd_empty_img  = TMDPOS_IMAGE_PATH. 'company_logo.png';
$searchicon_url = TMDPOS_IMAGE_PATH. 'searchicon.png';
$current_user   = wp_get_current_user();

?>

<!-- tmdwppos start here -->
<div id="tmdwppos">

	<div id="tmd-pos-progress-bar-main">
	  	<div id="tmd-pos-progress-bar"></div>
	</div>
	<span class="tmd_data_loader" style="display: none;"></span>
	<input type="hidden" name="selected_customer" value="" class="tmd-pos-selected-customer">

	<?php
		if( is_user_logged_in() ){
				
			if( is_array( $current_user->roles ) && $current_user->roles[0] == 'administrator' || $current_user->roles[0] == 'tmd_pos_user' ){

				/*product detail */
				global $product, $wpdb;
				$pos_table 	 = $wpdb->prefix . "tmd_pos";
				$tmdposloop  = new WP_Query( array( 'post_type'=>'product', 'posts_per_page'=>10 ));
				$query       = $wpdb->prepare("SELECT * FROM $pos_table WHERE `tmd_option` = %s", 'tmd_stock_option');
				$stock_datas = $wpdb->get_row($query);				
				$stockdatas  = ! empty( $stock_datas->tmd_option_value ) ? json_decode($stock_datas->tmd_option_value):'';
				$query       = $wpdb->prepare("SELECT * FROM $pos_table WHERE `tmd_option` = %s", 'tmd_general_option');
				$gen_datas   = $wpdb->get_row($query);
				$gen_data    = !empty( $gen_datas->tmd_option_value )? json_decode($gen_datas->tmd_option_value):'';

				// load header
				include TMDPOS_PLUGIN_PATH . 'include/pos-template/header-one.php';
			
				?>
					<!-- pos_body start here -->
					<div class="tmdpos_body tmd-pos-layout-one">
						<?php include_once TMDPOS_PLUGIN_PATH. 'include/pos-template/pos-layout-one.php'; ?>
					</div>

					<!-- print data div -->
					<div class="tmdpos_order_print_div"></div>
					<!-- print data div end-->

					<!-- tmd pos coupon modal  -->
					<div class="tmd_pos_coupon_model"></div>
					<!-- tmd pos coupon modal end  -->

					<!-- tmd pos hold order modal -->
					<div class="tmd_pos_hold_order_modal"></div>
					<!-- tmd pos hold order modal end -->

					<!-- order not complete print msg div -->
					<div class="error_to_print_order_receipt">
						<div class="tmd_print_errro_main_div">
							<div class="close_tmd_print_error_dilog">&#10006;</div>
							<div class="tmd_print_error_message"></div>
						</div>
					</div>
					<!-- order not complete print msg div end -->
				<?php

				include_once TMDPOS_PLUGIN_PATH . 'include/tmd-pos-class/tmd-pos-menu-pages.php'; //order print
				include_once TMDPOS_PLUGIN_PATH . 'include/tmd-pos-class/class-tmd-pos-checkout.php'; //order checkout
				include_once TMDPOS_PLUGIN_PATH . 'include/tmd-pos-class/tmd-pos-hold-orderlist.php'; //hold orders list
				include TMDPOS_PLUGIN_PATH . 'include/pos-template/tmd-pos-notice-modal.php';
			}
			else{
				include_once TMDPOS_PLUGIN_PATH. 'include/pos-template/access-404.php'; //404 access denined
			}
		}
		else{
			include_once TMDPOS_PLUGIN_PATH. 'include/pos-template/pos-login.php'; //pos login 
		}
	?>
</div>
<style> html { margin-top: 0 !important; } </style>