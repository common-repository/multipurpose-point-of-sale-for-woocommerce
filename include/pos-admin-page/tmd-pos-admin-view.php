<?php
/**
 * tmd pos front 
 * 
 * @package pos admin tab
 */
defined( 'ABSPATH' ) || exit;
$active_tab = isset($_GET['tab']) ? sanitize_text_field( $_GET['tab'] ) : 'posgeneral' ;
?>
<div class="wrap"> 
	<h2><?php echo esc_html( $GLOBALS['title'] ); ?></h2>
    <h2 class="nav-tab-wrapper">
        <a href="<?php echo esc_url( tmdpos_admin_url('posgeneral') ); ?>" class="nav-tab <?php echo $active_tab == 'posgeneral' ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php esc_html_e( 'General', 'tmdpos' ); ?></a>

        <a href="<?php echo esc_url( tmdpos_admin_url('checkout') ); ?>" class="nav-tab <?php echo $active_tab == 'checkout' ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php esc_html_e( 'Checkout', 'tmdpos' ); ?></a>

        <a href="<?php echo esc_url( tmdpos_admin_url('tmdposbarcode') ); ?>" class="nav-tab <?php echo $active_tab == 'tmdposbarcode' ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php esc_html_e( 'Inventory Barcode', 'tmdpos' ); ?></a>

        <a href="<?php echo esc_url( tmdpos_admin_url('tmdstock') ); ?>" class="nav-tab <?php echo $active_tab == 'tmdstock' ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php esc_html_e( 'Stock', 'tmdpos' ); ?></a>

        <a href="<?php echo esc_url( tmdpos_admin_url('invoice') ); ?>" class="nav-tab <?php echo $active_tab == 'invoice' ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php esc_html_e( 'Invoice', 'tmdpos' ); ?></a>

        <a href="<?php echo esc_url( tmdpos_admin_url('payment') ); ?>" class="nav-tab <?php echo $active_tab == 'payment' ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php esc_html_e( 'Payment', 'tmdpos' ); ?></a>
    </h2>

	<form method="post" class="tmd-pos-main-container">
        
	</form>
	<form method="post" class="tmd-pos-main-container">
        <div id="tmd_pos_post_data" class="ui-sortable meta-box-sortables">
            <div class="tmd_pos_container">
            	<?php 
		        	if($active_tab == 'posgeneral') { 
		        		?>
		        			<div class="tmd-pos-button-right">
	            				<a class="button-primary" target="_blank" href="<?php echo esc_url( home_url('pos-screen') ); ?>"><?php esc_html_e( 'Pos Screen', 'tmdpos' ); ?></a>
	            			</div>
		        		<?php
						include_once TMDPOS_PLUGIN_PATH. 'include/pos-admin-page/tmd-pos-general.php'; 
						submit_button( null, 'button-primary', 'tmd_pos_general_submit');
					} 

					if( $active_tab == 'checkout') { 
						include_once TMDPOS_PLUGIN_PATH. 'include/pos-admin-page/tmd-pos-checkout.php';
						submit_button( null, 'button-primary', 'tmd_pos_checkout_submit'); 
		        	} 

		        	if($active_tab == 'tmdbarcode') { 
		        		include_once TMDPOS_PLUGIN_PATH. 'include/pos-admin-page/tmd-pos-barcode.php';  
					} 

				 	if($active_tab == 'tmdstock') { 
		        		include_once TMDPOS_PLUGIN_PATH. 'include/pos-admin-page/tmd-pos-stock.php';
		        		submit_button( null, 'button-primary', 'tmd_pos_stock_submit'); 
		        	} 

		        	if($active_tab == 'invoice') { 
						include_once TMDPOS_PLUGIN_PATH. 'include/pos-admin-page/tmd-pos-invoice.php';
						submit_button( null, 'button-primary', 'tmd_pos_invoice_submit'); 
					} 

				  	if($active_tab == 'payment') { 
		        		include_once TMDPOS_PLUGIN_PATH. 'include/pos-admin-page/tmd-pos-payment.php';
		        		submit_button( null, 'button-primary', 'tmd_pos_pay_submit'); 
		        	} 
					
					if($active_tab == 'tmdposbarcode') { 
						include_once TMDPOS_PLUGIN_PATH. 'include/pos-admin-page/tmd-pos-barcode-inventory.php'; 
				    } 
		        ?>
    		</div>
        </div>
	</form>
</div>