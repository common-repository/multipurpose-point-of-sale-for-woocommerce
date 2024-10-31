<?php
/**
 * Tmd pos data insertion
 *
 * @package tmd pos general
 * @return $posgeneral,
 */
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'Tmdpos_Save_Data' ) ) {
	
	class Tmdpos_Save_Data {
		
		function __construct(){
			add_action('admin_init', array( $this, 'save_general_tab' ) );

			add_action('admin_init', array( $this, 'save_checkout_tab' ) );

			add_action('admin_init', array( $this, 'save_stock_tab' ) );

			add_action('admin_init', array( $this, 'save_invoice_tab' ) );

			add_action('admin_init', array( $this, 'save_payment_tab' ) );
		}

		/**
		 * Tmd pos data insertion
		 *
		 * @package tmd pos general
		 * @return $posgeneral,
		 */
		public function save_general_tab(){
			global $wpdb;
			$pos_table   = $wpdb->prefix . "tmd_pos";
			$query       = $wpdb->prepare( "SELECT * FROM $pos_table WHERE tmd_option = %s", 'tmd_general_option' );
			$generaldata = $wpdb->get_row($query);
			
			if( isset( $_POST['tmd_pos_general_submit'] ) ){
				
				$categories  = !empty( $_POST['category_list'] ) ? array_map('sanitize_text_field', $_POST['category_list']) : null;
				$pos_layout  = !empty( $_POST['pos_layout'] ) ? sanitize_text_field($_POST['pos_layout']) : null;	

				$data = array(
					'_pos_categories' => $categories,
					'_pos_layout'	  => $pos_layout,
				);
				if( ! empty( $generaldata ) && $generaldata->tmd_option == 'tmd_general_option'){
					$pos_general_data = array( 'tmd_option_value' => wp_json_encode( $data ) );
					$wpdb->update( $pos_table, $pos_general_data, array('tmd_option' => 'tmd_general_option') );
				} 
				else {
					$pos_general_data = array('tmd_option' => 'tmd_general_option', 'tmd_option_value' => wp_json_encode($data));
					$wpdb->insert($pos_table,$pos_general_data);	
				}	
			}
		}

		/**
		 * Tmd pos data insertion
		 *
		 * @package tmd pos checkout
		 * @return $poscheckout,
		 */

		public function save_checkout_tab(){
			global $wpdb;
			$pos_table    = $wpdb->prefix . "tmd_pos";
			$query        = $wpdb->prepare( "SELECT * FROM $pos_table WHERE tmd_option = %s", 'tmd_checkout_option' );
			$checkoutdata = $wpdb->get_row($query );

			if( isset( $_POST['tmd_pos_checkout_submit'] ) ){
				$mailtoadmin    = !empty( $_POST['order_mail_admin'] ) ? sanitize_text_field( $_POST['order_mail_admin'] ) : null;
				$mailtocustomer = !empty( $_POST['order_mail_customer'] ) ? sanitize_text_field( $_POST['order_mail_customer'] ) : null;
				$orderstatus    = !empty( $_POST['pos_order_status'] ) ? sanitize_text_field( $_POST['pos_order_status'] ) : null;

				$data = array(
					'_order_mail_admin'		=> $mailtoadmin,
				  	'_order_mail_customer'	=> $mailtocustomer,
				  	'_order_status'			=> $orderstatus,
				);
				if(!empty($checkoutdata) && $checkoutdata->tmd_option == 'tmd_checkout_option'){
					$pos_checkout_data = array('tmd_option_value' => wp_json_encode( $data ) );
					$wpdb->update($pos_table ,$pos_checkout_data, array('tmd_option' => 'tmd_checkout_option') );
				}
				else {
					$pos_checkout_data = array('tmd_option' => 'tmd_checkout_option', 'tmd_option_value' => wp_json_encode($data));
					$poscheckout = $wpdb->insert( $pos_table, $pos_checkout_data );
				}	
			}
		}

		/**
		 * Tmd pos data insertion
		 *
		 * @package tmd pos stock
		 * @return $posstock,
		 */
		public function save_stock_tab(){

			global $wpdb;
			$pos_table   = $wpdb->prefix . "tmd_pos";
			$query       = $wpdb->prepare( "SELECT * FROM $pos_table WHERE `tmd_option` = %s", 'tmd_stock_option' );
			$stock_datas = $wpdb->get_row( $query );
			
			if(isset($_POST['tmd_pos_stock_submit'])){

				$status = !empty($_POST['product_status']) ? sanitize_text_field( $_POST['product_status'] ) : null;  
				$data   = array('product_status' => $status );
						
				if(!empty($stock_datas) && $stock_datas->tmd_option == 'tmd_stock_option'){
					$pos_stock_data = array('tmd_option_value' => wp_json_encode($data));
					$wpdb->update($pos_table, $pos_stock_data, array('tmd_option' => 'tmd_stock_option') );
				} 
				else{
					$pos_stock_data = array('tmd_option' => 'tmd_stock_option', 'tmd_option_value' => wp_json_encode($data));
					$posstock = $wpdb->insert($pos_table,$pos_stock_data);
				}	
			}
		}

		/**
		 * Tmd pos data insertion
		 *
		 * @package tmd pos invoice
		 * @return $posinvoice,
		 */
		function save_invoice_tab(){ 
			global $wpdb;
			$pos_table     = $wpdb->prefix . "tmd_pos";
			$query         = $wpdb->prepare( "SELECT * FROM $pos_table WHERE `tmd_option` = %s", 'tmd_invoice_option' );
			$invoice_datas = $wpdb->get_row($query);

			if( isset($_POST['tmd_pos_invoice_submit'] ) ){

				$reciept_logo           = !empty( $_POST['reciept_logo'] ) ? sanitize_text_field($_POST['reciept_logo'] ) : null;
				$logo_size_height       = !empty( $_POST['logo_size_height'] ) ? sanitize_text_field($_POST['logo_size_height'] ) : null;
				$logo_size_width        = !empty( $_POST['logo_size_width'] ) ? sanitize_text_field($_POST['logo_size_width'] ) : null;
				$reciept_formate        = !empty( $_POST['reciept_formate'] ) ? sanitize_text_field($_POST['reciept_formate'] ) : null;
				$reciept_width          = !empty( $_POST['reciept_width'] ) ? sanitize_text_field($_POST['reciept_width'] ) : null;
				$show_logo              = !empty( $_POST['show_logo'] ) ? sanitize_text_field($_POST['show_logo'] ) : null;
				$show_store_name        = !empty( $_POST['show_store_name'] ) ? sanitize_text_field($_POST['show_store_name'] ) : null;
				$show_store_address     = !empty( $_POST['show_store_address'] ) ? sanitize_text_field($_POST['show_store_address'] ) : null;
				$show_store_phone       = !empty( $_POST['show_store_phone'] ) ? sanitize_text_field($_POST['show_store_phone'] ) : null;
				$show_order_date        = !empty( $_POST['show_order_date'] ) ? sanitize_text_field($_POST['show_order_date'] ) : null;
				$show_invoice_number    = !empty( $_POST['show_invoice_number'] ) ? sanitize_text_field($_POST['show_invoice_number'] ) : null;
				$show_cashier_name      = !empty( $_POST['show_cashier_name'] ) ? sanitize_text_field($_POST['show_cashier_name'] ) : null;
				$show_customer_name     = !empty( $_POST['show_customer_name'] ) ? sanitize_text_field($_POST['show_customer_name'] ) : null;
				$show_shipping_address  = !empty( $_POST['show_shipping_address'] ) ? sanitize_text_field($_POST['show_shipping_address'] ) : null;
				$show_shipping_mode     = !empty( $_POST['show_shipping_mode'] ) ? sanitize_text_field($_POST['show_shipping_mode'] ) : null;
				$show_Payment_mode      = !empty( $_POST['show_Payment_mode'] ) ? sanitize_text_field($_POST['show_Payment_mode'] ) : null;
				$show_changes           = !empty( $_POST['show_changes'] ) ? sanitize_text_field($_POST['show_changes'] ) : null;
				$invoice_extra_info     = !empty( $_POST['invoice_extra_info'] ) ? sanitize_text_field($_POST['invoice_extra_info'] ) : null;
				$invoice_thanks_msg     = !empty( $_POST['invoice_thanks_msg'] ) ? sanitize_text_field($_POST['invoice_thanks_msg'] ) : null;
				
				$tmd_invoice = array(
					'reciept_logo'			=> $reciept_logo,
					'logo_size_height' 		=> $logo_size_height ,
					'logo_size_width' 		=> $logo_size_width ,
					'reciept_formate' 		=> $reciept_formate ,
					'reciept_width' 		=> $reciept_width ,
					'show_logo' 			=> $show_logo ,
					'show_store_name' 		=> $show_store_name ,
					'show_store_address' 	=> $show_store_address ,
					'show_store_phone' 		=> $show_store_phone ,
					'show_order_date' 		=> $show_order_date ,
					'show_invoice_number' 	=> $show_invoice_number ,
					'show_cashier_name' 	=> $show_cashier_name ,
					'show_customer_name' 	=> $show_customer_name ,
					'show_shipping_address' => $show_shipping_address ,
					'show_shipping_mode' 	=> $show_shipping_mode ,
					'show_Payment_mode' 	=> $show_Payment_mode ,
					'show_changes' 			=> $show_changes ,
					'invoice_extra_info' 	=> $invoice_extra_info ,
					'invoice_thanks_msg' 	=> $invoice_thanks_msg ,
				);
				
				if(!empty($invoice_datas) && $invoice_datas->tmd_option == 'tmd_invoice_option'){
					$pos_invoice_data = array('tmd_option_value' => wp_json_encode( $tmd_invoice ) );
					$wpdb->update($pos_table, $pos_invoice_data, array( 'tmd_option' => 'tmd_invoice_option' ) );
				} 
				else {
					$pos_invoice_data = array('tmd_option' => 'tmd_invoice_option', 'tmd_option_value' => wp_json_encode($tmd_invoice));
					$wpdb->insert($pos_table,$pos_invoice_data);	
				}
			}
		}

		/**
		 * Tmd pos data insertion
		 *
		 * @package tmd pos payment
		 * @return $pospayment
		 */
		function save_payment_tab(){

			global $wpdb;
			$pos_table     = $wpdb->prefix . "tmd_pos";
			// Prepare the SQL query with placeholders
			$query = $wpdb->prepare( "SELECT * FROM $pos_table WHERE `tmd_option` = %s", 'tmd_payment_option' );

			// Run the prepared query
			$payment_datas = $wpdb->get_row( $query );
			
			if( isset($_POST['tmd_pos_pay_submit'] ) ){

				$tmd_pos_gateway = !empty($_POST['tmd_pos_gateway']) ? sanitize_text_field( $_POST['tmd_pos_gateway'] ) : null;
				$tmd_payment     = array('tmd_pos_gateway' 	=>	 $tmd_pos_gateway );

				if(!empty($payment_datas) && $payment_datas->tmd_option == 'tmd_payment_option'){
					$pos_payment_data = array('tmd_option' => 'tmd_payment_option', 'tmd_option_value' => wp_json_encode($tmd_payment));
					$wpdb->update( $pos_table, $pos_payment_data, array('tmd_option' => 'tmd_payment_option') );
				} 
				else {
					$pos_payment_data = array('tmd_option' => 'tmd_payment_option', 'tmd_option_value' => wp_json_encode($tmd_payment));
					$wpdb->insert($pos_table, $pos_payment_data);	
				}	
			}
		}
	}
	
	new Tmdpos_Save_Data;
}