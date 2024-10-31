<?php
defined( 'ABSPATH' ) || exit;

/**
 * Tmd pos get admin url page slug  
 * 
 * @since 1.0.1
 **/
if ( !function_exists( 'tmdpos_admin_url' ) ) {
    function tmdpos_admin_url( $id = null ){
        if ( ! empty( $_GET['page'] ) && $_GET['page'] == 'tmd-pos' ){
            $url = $id ? admin_url().'admin.php?page=tmd-pos&tmd_tab='.$id : admin_url().'admin.php?page=tmd-pos';
        }
        else if ( ! empty( $_GET['page'] ) && $_GET['page'] == 'tmd_pos' ){
            $url = $id ? admin_url().'admin.php?page=tmd_pos&tab='.$id : admin_url().'admin.php?page=tmd_pos';
        }
        else{
            $url = '';
        }
        return $url;
    }
}

/**
 * Tmd pos get pos admin active tab class  
 * 
 * @since 1.0.1
 **/
if ( !function_exists( 'tmdpos_active_tab' ) ) {
    function tmdpos_active_tab( $slug, $default, $class = '' ){
     
        $active = '';
        $tab    = isset($_GET[$slug]) ? sanitize_text_field( $_GET[$slug] ) : '';

        if ( $class != '' && !empty( $tab ) && $default){
            $active = $default === $tab ? $class : ''; 
        }
        else if ( !empty( $tab ) && $default ){
            $active = $default === $tab ? 'tmd_pos_tab_active' : ''; 
        }
        return $active;
    }
}


/**
 * Tmd pos get total order product quantities  
 * 
 * @since 1.0.1
 **/
if ( !function_exists( 'tmdpos_get_order_total_product_qty' ) ) {
    function tmdpos_get_order_total_product_qty( $order_item ){
        $total_qty = 0;
        foreach ( $order_item as $item_id => $item ){
            $total_qty += $item['quantity'];
        }
        return $total_qty;
    }
}

/**
 * Tmd pos get payment method title by payment id 
 * 
 * @since 1.0.1
 */
if ( !function_exists( 'tmdpos_get_order_payment_title' ) ) {
    function tmdpos_get_order_payment_title( $id ){   
        $payment_title = '';
        if( $id ){
            $tmd_pos_wc_gateway       = new WC_Payment_Gateways();
            $tmd_pos_payment_gateways = $tmd_pos_wc_gateway->payment_gateways();

            if( ! empty( $tmd_pos_payment_gateways ) ){
                foreach( $tmd_pos_payment_gateways as $gateway_id => $gateway ){ 
                    if ( $gateway_id === $id ){
                        $payment_title = $gateway->get_title();
                        break;
                    }
                }
            }
        }
        return $payment_title;
    }
}

/**
 * Tmd pos get tmd pos order data 
 * 
 * @since 1.0.1
 **/
if ( !function_exists( 'tmdpos_get_orders' ) ) {
    function tmdpos_get_orders(){
        global $wpdb;
        $pos_table_order    = $wpdb->prefix . 'tmd_pos_order';
        $pos_order_datass   = $wpdb->get_results( "SELECT * FROM $pos_table_order" );
        return $pos_order_datass;
    }
}

/**
 * Tmd pos get order total payment type 
 * 
 * @since 1.0.1
 **/
if ( !function_exists( 'tmdpos_get_order_total_by_payment_type' ) ) {
    function tmdpos_get_order_total_by_payment_type(){
        $datas      = tmdpos_get_orders();
        $wc_gateway = new WC_Payment_Gateways();
        $gateways   = $wc_gateway->payment_gateways();
        $data       = [];

        if( !empty( $datas ) ){
            foreach( $gateways as $id => $type ){
                $g_total    = 0;
                $total_tax  = 0;
                $total      = 0;
                foreach( $datas as $pos_order_datas ){
                    $pos_order_id   = $pos_order_datas->order_meta;
                    $order          = wc_get_order( $pos_order_id );
                    $order_data     = $order->get_data();
                    $payment_method = $order_data['payment_method'];
                    $payment_title  = $order_data['payment_method_title'];
                    $customer_id    = $order_data['customer_id'];
                    $user           = get_user_by( 'id', $customer_id );
                    $order_qty      = tmdpos_get_order_total_product_qty( $order->get_items() );

                    if( $payment_method == $id ){
                        $total_tax  += $order->get_total_tax();
                        $total      += $order->get_total();
                    }
                }
                $g_total = $total + $total_tax;
                $data[]  = array( 'id' =>  $id, 'total' => $g_total );
            }
        }
        return $data;
    }
}

/**
 * Tmd pos get order total by payment type 
 * 
 * @since 1.0.1
 **/
if ( !function_exists( 'tmdpos_get_order_by_chasier' ) ) {
    function tmdpos_get_order_by_chasier( $user_id ){
        $order_datas     = tmdpos_get_orders();
        $orders          = [];
        $orders['total'] = 0;
        foreach ( $order_datas as $pos_order_datas ){
            $pos_order_id  = $pos_order_datas->order_meta;
            $order_details = json_decode( $pos_order_datas->order_value );
            $cashier_id    = !empty( $order_details->cashier_id ) ? $order_details->cashier_id : 1;

            if( $cashier_id == $user_id ){
                $order       = wc_get_order( $pos_order_id );
                $order_data  = $order->get_data();
                $customer    = new WP_User( $order_data['customer_id'] );

                $orders['cashier']  = get_user_by( 'id', $user_id )->display_name;
                $orders['total']    += $order->get_total();
            }
        }
        return $orders;
    }
}

/**
 * Tmd pos get recent sale 
 * 
 * @since 1.0.1
 **/
if ( ! function_exists( 'tmdpos_get_recent_sale' ) ) {
    function tmdpos_get_recent_sale(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'tmd_pos_order';
        $datas      = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY `tmd_order_id` DESC LIMIT 5" );
        return $datas;
    }
}