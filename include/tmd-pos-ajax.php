<?php
/**
 * tmd pos ajax fucntion 
 * 
 * @package WooCommerce Point Of Sale
 * @since 1.0.2
 * @author TMD Studio
 */

 // Exit if accessed directly
defined( "ABSPATH" ) || exit;

if( !function_exists('tmdpos_varaiation_filter') ){
    add_action( 'wp_ajax_tmdpos_varaiation_filter', 'tmdpos_varaiation_filter');
    add_action( 'wp_ajax_nopriv_tmdpos_varaiation_filter', 'tmdpos_varaiation_filter');
    function tmdpos_varaiation_filter() {
        global $wpdb, $wp_session;
        $json=array();

        $table_name  = $wpdb->prefix.'wc_product_meta_lookup';
        $product_id  = isset( $_POST['product_id'] ) ? sanitize_text_field( absint($_POST['product_id'] ) ) : 0; 
        $currency    = isset( $_POST['currency'] ) ? sanitize_text_field( $_POST['currency'] ) : ''; 
        $query       = $wpdb->prepare( "SELECT * FROM $table_name WHERE `product_id` = %d", $product_id );
        $product_detailss = $wpdb->get_results( $query );
        $product_qty      = absint( '1' );

        if(isset($product_detailss)){
            foreach ($product_detailss as $product_details) {
                $_product       = wc_get_product( $product_details->product_id ); /*product detail by id*/
                $product_price  = $_product->get_price_excluding_tax();  // product price with and without tax
                $price_excl_tax = wc_get_price_excluding_tax( $_product ); // price without Tax
                $price_incl_tax = wc_get_price_including_tax( $_product );  // price with Tax
                $tax_amount     = $price_incl_tax - $price_excl_tax; // Tax amount

                $json=array(
                    'product_id'        =>  $product_details->product_id,
                    'product_name'      =>  get_the_title( $product_details->product_id ),
                    'product_price'     =>  $_product->get_price(),
                    'product_qty'       =>  $product_qty,
                    'product_cost'      =>  $product_price,
                    'product_tax'       =>  $tax_amount,
                    'currency'          =>  $currency,
                );
                if(!empty($_SESSION['pos_items'][$product_id])){
                    $_SESSION['pos_items'][$product_id]['product_qty']  +=$product_qty;
                    $_SESSION['pos_items'][$product_id]['product_tax']  +=$tax_amount;
                    $_SESSION['pos_items'][$product_id]['product_cost'] +=$product_price;
                }
                else{
                    $_SESSION['pos_items'][$product_id] = $json;
                }
            }   
        }
        header("Content-type: application/json; charset=utf-8");
        echo wp_json_encode($_SESSION); 
        die();      
    }

}

if( !function_exists( 'tmdpos_cart_session' ) ){
    // tmd get session data
    add_action( 'wp_ajax_tmdpos_cart_session', 'tmdpos_cart_session');
    add_action( 'wp_ajax_nopriv_tmdpos_cart_session', 'tmdpos_cart_session');
    function tmdpos_cart_session() {
        global $wp_session;
        $json = array();
        $cart_items = !empty( $_SESSION['pos_items'] ) ? $_SESSION['pos_items'] : false; 
        if ( !empty( $cart_items ) ){
            foreach ($cart_items as $key => $items){
                $json['pos_items'][]=array(
                    'product_id'    => $items['product_id'],
                    'product_name'  => $items['product_name'],
                    'product_price' => $items['product_price'],
                    'product_qty'   => $items['product_qty'],
                    'product_cost'  => $items['product_cost'],
                    'currency'      => $items['currency'],
                );
            }   
        }
        header("Content-type: application/json; charset=utf-8");
        echo wp_json_encode($json); 
        die();
    }

}


if( !function_exists( 'tmdpos_update_cart' ) ){
    // update cart cost
    add_action( 'wp_ajax_tmdpos_update_cart', 'tmdpos_update_cart');
    add_action( 'wp_ajax_nopriv_tmdpos_update_cart', 'tmdpos_update_cart');
    function tmdpos_update_cart(){
        global $wpdb, $wp_session;

        $cart_key = isset($_POST['cart_id']) ? sanitize_text_field( absint( $_POST['cart_id'] ) )  : '';
        $inputval = isset($_POST['inputval']) ? sanitize_text_field( absint( $_POST['inputval'] ) ) : '';

        if(!empty($inputval)){
            $table_name       = $wpdb->prefix.'wc_product_meta_lookup';
            $query            = $wpdb->prepare( "SELECT * FROM $table_name WHERE `product_id` = %d", $cart_key );
            $product_detailss = $wpdb->get_results( $query );

            foreach ($product_detailss as $product_details) {
                $_product       = wc_get_product( $product_details->product_id );
                $product_price  = $_product->get_price_excluding_tax();  // product price without tax
                $price_excl_tax = wc_get_price_excluding_tax( $_product ); // price without Tax
                $price_incl_tax = wc_get_price_including_tax( $_product );  // price with Tax
                $tax_amount     = $price_incl_tax - $price_excl_tax; // Tax amount

                if(!empty($_SESSION['pos_items'][$cart_key])){   
                    if( $product_details->stock_quantity != null ){

                        if( $inputval <= $product_details->stock_quantity ):
                            $_SESSION['pos_items'][$cart_key]['product_qty']  = $inputval;
                            $_SESSION['pos_items'][$cart_key]['product_cost'] = $product_price * $inputval;
                            $_SESSION['pos_items'][$cart_key]['product_tax']  = $tax_amount * $inputval;
                        endif;
                    }
                    else{
                        $_SESSION['pos_items'][$cart_key]['product_qty']  = $inputval;
                        $_SESSION['pos_items'][$cart_key]['product_cost'] = $product_price * $inputval;
                        $_SESSION['pos_items'][$cart_key]['product_tax']  = $tax_amount * $inputval;
                    }
                }
                else{
                    $_SESSION['pos_items'][$cart_key] = $json;
                }
            }
        }

        header("Content-type: application/json; charset=utf-8");
        echo wp_json_encode( $_SESSION );
        die();
    }
    
}


if( !function_exists( 'tmdpos_cart_checkout' ) ){

    // calculate cart total
    add_action( 'wp_ajax_tmdpos_cart_checkout', 'tmdpos_cart_checkout');
    add_action( 'wp_ajax_nopriv_tmdpos_cart_checkout', 'tmdpos_cart_checkout');
    function tmdpos_cart_checkout(){
        global $wp_session, $woocommerce;
        $json          = array();
        $coupon_amount = wc_format_decimal( sanitize_text_field( $_POST['coupon_amount'] ), 2 );
        $cart_items    = !empty( $_SESSION['pos_items'] ) ? $_SESSION['pos_items'] : false;
        $tmd_coupon    = !empty( $_SESSION['tmd_coupon'] ) ? $_SESSION['tmd_coupon'] : false;
        $coupon_amount = !empty( $coupon_amount ) ? $coupon_amount : '';
        
        foreach ( $cart_items as $values ) :
            $subtotal   +=  $values['product_cost'];
            $total_tax  +=  $values['product_tax'];

            $json['total_tax']   = $total_tax;
            $json['subtotal']   += $values['product_cost'];
            $json['currency']    = $values['currency'];

            if( $_SESSION['coupon_amount'] > ( $subtotal + $total_tax )  ){
                $json['total'] = ( $subtotal + $total_tax ) - ( $subtotal + $total_tax );
            }
            else{
                $json['total'] = ( $subtotal + $total_tax ) - $_SESSION['coupon_amount'];
            }
        endforeach;

        // revert coupon detail
        foreach ( $tmd_coupon as $coupon_value ):
            $json['tmd_coupon'] = $coupon_value;
        endforeach;

        header("Content-type: application/json; charset=utf-8");
        echo wp_json_encode($json);
        die();
    }
}


if( !function_exists( 'tmdpos_remove_cart_items' ) ){
    // remove cart items
    add_action( 'wp_ajax_tmdpos_remove_cart_items', 'tmdpos_remove_cart_items');
    add_action( 'wp_ajax_nopriv_tmdpos_remove_cart_items', 'tmdpos_remove_cart_items');
    function tmdpos_remove_cart_items(){
        global $wp_session;
        $remove_id = isset( $_POST['remove_id'] ) ? sanitize_text_field( absint( $_POST['remove_id'] ) ) : 0;
        unset( $_SESSION['pos_items'][$remove_id] );
        unset( $_SESSION['tmd_coupon'] );
        unset( $_SESSION['coupon_amount'] );
        header("Content-type: application/json; charset=utf-8");
        die();
    }
}


if( !function_exists( 'tmdpos_clear_cart_items' ) ){
    // clear cart items
    add_action( 'wp_ajax_tmdpos_clear_cart_items', 'tmdpos_clear_cart_items');
    add_action( 'wp_ajax_nopriv_tmdpos_clear_cart_items', 'tmdpos_clear_cart_items');
    function tmdpos_clear_cart_items(){
        global $wp_session;
        unset( $_SESSION['pos_items'] );
        unset( $_SESSION['tmd_coupon'] );
        unset( $_SESSION['coupon_amount'] );
        header("Content-type: application/json; charset=utf-8");
        die();
    }
}


if( !function_exists( 'tmdpos_order_now' ) ){
    // order Now start
    add_action( 'wp_ajax_tmdpos_order_now', 'tmdpos_order_now');
    add_action( 'wp_ajax_nopriv_tmdpos_order_now', 'tmdpos_order_now');
    function tmdpos_order_now(){
        global $wp_session, $wpdb, $woocommerce, $post;
        $json          = array();
        $tmd_pos_user  = wp_get_current_user();
        $tmd_pos_order = $wpdb->prefix . "tmd_pos_order";
        $coupon_code   = sanitize_text_field( $_POST['coupon_code'] );
        $order         = wc_create_order();
        $pos_order_id  = $order->id;

        /*tmd pos order data */
        $pos_order_data    = $_POST['tmd_order_data'];
        $existing_customer = sanitize_text_field( absint( $_POST['existing_customer'] ) );
        $payment_id        = sanitize_text_field( $_POST['payment_id'] );
        $cart_items        = ! empty( $_SESSION['pos_items'] ) ? $_SESSION['pos_items'] : false;

        // cart total/subtal
        $subtotal     = $total_tax = $qty = '';
        $shipping     = absint( $pos_order_data['shipping_cost'] );
        $discount     = absint( $pos_order_data['discount'] );
        $order_note   = sanitize_textarea_field( $pos_order_data['order_note'] );
        $order_status = sanitize_text_field( $pos_order_data['order_status'] );

        // tmd pos order
        $tmd_order_value       = array('order_meta' => $pos_order_id, 'order_value' => wp_json_encode($pos_order_data));
        $tmd_pos_order_details = $wpdb->insert( $tmd_pos_order, $tmd_order_value );

        // wc order create    
        foreach ($cart_items  as $key => $cart_items_value ):
            $product = wc_get_product( $cart_items_value['product_id'] );
            $order->add_product( $product, $cart_items_value['product_qty'] );
            $order->set_total( $cart_items_value['product_qty'] * $product->get_price() );
        endforeach;
        
        $order_type = array('first_name' => 'Tmd', 'last_name' =>'Pos');

        if( !empty( $existing_customer ) ){
            $order->set_customer_id( $existing_customer );
        }
        else{
            $order->set_customer_id($tmd_pos_user->ID);
        }

        // set shipping address
        if( ! empty( $existing_customer ) ):
            $customer       = new WP_User( $existing_customer );
            $customer_phone = get_user_meta( $existing_customer, 'billing_phone', true ); // Customer shipping details
            
            $shipping_address = array(
                'first_name' => $customer->shipping_first_name,
                'last_name'  => $customer->shipping_last_name,
                'company'    => $customer->shipping_company,
                'email'      => $customer->user_email,
                'phone'      => $customer_phone,
                'address_1'  => $customer->shipping_address_1,
                'address_2'  => $customer->shipping_address_2,
                'city'       => $customer->shipping_city,
                'state'      => $customer->shipping_state,
                'postcode'   => $customer->shipping_postcode,
                'country'    => $customer->shipping_country,
            );

            $billing_address = array(
                'first_name' => $customer->shipping_first_name,
                'last_name'  => $customer->shipping_last_name,
                'company'    => $customer->shipping_company,
                'email'      => $customer->user_email,
                'phone'      => $customer_phone,
                'address_1'  => $customer->shipping_address_1,
                'address_2'  => $customer->shipping_address_2,
                'city'       => $customer->shipping_city,
                'state'      => $customer->shipping_state,
                'postcode'   => $customer->shipping_postcode,
                'country'    => $customer->shipping_country,
            );
        endif;

        // set shipping address end
        if ( ! empty( $shipping_address ) ){
            $order->set_address( $shipping_address, 'shipping' );
        }

        // set billing address end
        if( empty( $billing_address ) ){
            $billing_address = $order_type;
        }

        // payment mode start
        $payment_gateways  =  WC()->payment_gateways->payment_gateways();
        $order->set_payment_method($payment_gateways[$payment_id]);
        $order->set_address( $billing_address, 'billing' );
        $order->set_created_via( 'programatically' );
        $order->add_order_note( $order_note ); // order note
        $order->set_customer_note( $order_note );   

        // add shipping cost
        if( $shipping > 0 ):
            $shipping_fee = new WC_Order_Item_Fee();
            $shipping_fee->set_props(
                array(
                    'method_id'    => "shipping",
                    'cost'         => $shipping,
                    'taxes'        => array(),
                    'tax'          => array(),
                    'calc_tax'     => 'per_order',
                    'total'        => $shipping,
                    'tax_class'    => NULL,
                    'total_tax'    => 0,
                    'tax_status'   => 'none',
                    'meta_data'    => array(),
                    'package'      => false,
                )
            );
            $shipping_fee->set_name( esc_html("Shipping") );// Add shipping to the order
            $order->add_item( $shipping_fee );
        endif;

        $order->apply_coupon($coupon_code); // apply coupon
        $order->calculate_totals(); // calculate total
        $order->update_status( $order_status ); // update order satus
        $order->save(); //save order

        // clear cart after order success
        unset( $_SESSION['pos_items'] );
        unset( $_SESSION['tmd_coupon'] );
        unset( $_SESSION['coupon_amount'] );

        //order validation
        $tmd_order       = wc_get_order( $pos_order_id );
        $pos_order_value = $tmd_order->get_total();

        if(!empty($discount)):
            // add discount ammount to order total
            update_post_meta($pos_order_id,'_order_total', $pos_order_value - $discount ); 
        endif;

        if( !empty( $pos_order_value ) ):
        $json['data']          =  $pos_order_id;
        $json['data_currency'] =  get_woocommerce_currency_symbol();
        endif;

        //order validation end
        header("Content-type: application/json; charset=utf-8");
        echo wp_json_encode($json);
        die();
    }
    
}


if( !function_exists( 'tmdpos_filter_product_by_cat' ) ){
    // get product by category id
    add_action( 'wp_ajax_tmdpos_filter_product_by_cat', 'tmdpos_filter_product_by_cat');
    add_action( 'wp_ajax_nopriv_tmdpos_filter_product_by_cat', 'tmdpos_filter_product_by_cat');
    function tmdpos_filter_product_by_cat() {
        global $wpdb;

        $tmd_empty_img = TMDPOS_IMAGE_PATH. 'company_logo.png';
        $category_id   = !empty( $_POST['category_id'] ) ?  sanitize_text_field($_POST['category_id']) : 0;

        $category_s_product = get_posts( array(
            'post_type'   => 'product',
            'numberposts' => -1,
            'post_status' => 'publish',
            'fields'      => 'ids',
            'tax_query'   => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => $category_id, /*category name*/
                )
            ),
        ));
        
        ?>
        
        <ul class="grid2" id="product_list">
            <?php
                foreach ( $category_s_product as $productid ) {
                    if( !empty( $productid )){
                        $_tmdpos_product            = wc_get_product( $productid  ); 
                        $tmd_pos_product_varoiation = new WC_Product_Variable( $productid );
                        $tmd_product_varoiation     = $tmd_pos_product_varoiation->get_available_variations();
                        $attributes                 = $_tmdpos_product->get_attributes();
                        $attributesdata             = $tmd_pos_product_varoiation->get_variation_attributes();
                        $attribute_keys             = array_keys( $attributesdata ); // product variable option
                        $_tmd_pos_pd_id             = $_tmdpos_product->get_id();// get product stock status
                        $stock_status               = $_tmdpos_product->get_stock_status(); // get stock status
                        $stock_pd_qty               = $_tmdpos_product->get_stock_quantity(); // get stock status
                        $variations_id              = $_tmdpos_product->get_children();// variation details
                        $product_description        = $_tmdpos_product->post->post_excerpt;
                        $image = wp_get_attachment_image_src( get_post_thumbnail_id( $productid ), 'single-post-thumbnail' );
                        ?>
                        <li>
                            <a>
                                <?php 
                                                $class=$currency=$attr=$rel= '';
                                                if( $stock_status == 'outofstock' ){
                                                    $class = 'tmd_post_outof_icon';
                                                }
                                                else{ 
                                                    if( empty( $variations_id ) ) {
                                                        if( ! empty( $_tmdpos_product->get_price_html() ) ){
                                                            $class    = 'pos_add_ro_cart'; 
                                                            $currency = 'currency = '.get_woocommerce_currency_symbol().'';
                                                            $attr     = 'data-new=""'; 
                                                            $rel      = 'data-rel='.$_tmd_pos_pd_id.'';
                                                        }
                                                        else{ 
                                                            $class = 'tmd_post_cart_unable'; 
                                                        }
                                                    } 
                                                    else {
                                                        $class = 'tmdpos_op_pd';
                                                        $attr  = 'tmd-pos-var-id='.$_tmdpos_product->get_id().'';
                                                    }
                                                } 
                                            ?>
								
								   <div class="tmd_pos_pd_img pos-add-prod<?php echo esc_attr($_tmd_pos_pd_id); ?> <?php echo esc_attr( $class ); ?>" <?php echo esc_attr( $currency ).' '.esc_attr( $attr ).' '. esc_attr( $rel ); ?>>
                                         
                                    <img src="<?php if( has_post_thumbnail( $productid ) ) { echo esc_url( $image[0]); } else { echo esc_url($tmd_empty_img ); } ?>" alt="Image" title="<?php echo esc_attr( $_tmdpos_product->get_title() ); ?>" />

                                    <?php 
                                        if( $stock_status == 'outofstock' ){ 
                                            ?><span class="tmd_post_outof_icon"><?php esc_html_e( 'Out Of Stock', 'tmdpos' ); ?></span><?php 
                                        }
                                        else{ 
                                            if( empty( $variations_id ) ) { 
                                                ?>
                                                    <span class="pos_add_ro_cart tmd_post_cart_icon" title="<?php esc_html_e( 'Add To Cart', 'tmdpos' ); ?>" currency="<?php echo esc_attr ( get_woocommerce_currency_symbol() ); ?>" data-rel="<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></span>
                                                <?php 
                                            }
                                            else{
                                                ?>
                                                    <span class="tmd_post_cart_icon tmdpos_op_pd" tmd-pos-var-id="<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>" title="<?php esc_attr_e('Select Option','tmdpos'); ?>"></span>
                                                <?php 
                                            }
                                        } 
                                    ?>
                                </div>
                                <span class="top product_stock_qty"><?php echo esc_html( $stock_pd_qty ); ?></span> 
                            </a>
							
							
					
										

                            <div>
                                <!-- The Modal -->
                                <div id="tmd_pop_modal<?php echo esc_html( $_tmdpos_product->get_id() ); ?>" class="tmd_pop_option_pd">

                                    <!-- Modal content -->
                                    <div class="tmdpop-content tmdpos_option_div<?php echo esc_html( $_tmd_pos_pd_id ); ?>">

                                        <div class="product_detail_option">
                                            <div class="tmd_pos_pd_img">
                                                <img class="tmd_pos_variation_img tmd_pos_variation_imgsz image_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" src="<?php if( has_post_thumbnail(  $_tmdpos_product->get_id()  ) ) { echo esc_url( $image[0] ) ; } else { echo esc_url( $tmd_empty_img ); } ?>" alt="Image" title="<?php echo esc_attr( $_tmdpos_product->get_title()) ; ?>" />
                                            </div>
                                        </div>
                                        <div class="tmd_pos_product_description">
                                            <div class="cartpopclose" tmd-pos-var-id-to-close="<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>">&times;</div>
                                            <div class="product_description">
                                                <h2 class="name_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"><?php echo esc_html( $_tmdpos_product->get_title() ); ?></h2>
                                                <?php echo wp_kses_post( $product_description ); ?>
                                                <p class="variation_price price_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>">
                                                    <?php echo wp_kses_post( $_tmdpos_product->get_price_html()); ?>
                                                    <span style="display: none" class="currency_symbol get_woocommerce_currency_symbol<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"><?php echo esc_attr( get_woocommerce_currency_symbol() ); ?></span>
                                                </p>
                                            </div>

                                            <span style="display: none;" class="stock_status<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></span>
                                            <p style="display: none;"  class="stock_qty<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></p>
                                            <p style="display: none;"  class="vailiable_backorder<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></p>

                                            <div class="product_option">
                                                <table>
                                                    <tbody>
                                                    <?php 
                                                        if( count($tmd_product_varoiation) > 0 ){
                                                            ?>
                                                                <div class="product-variations-dropdown">
                                                                    <select onchange="tmd_pos_product_change_s('<?php echo esc_js( $_tmd_pos_pd_id ); ?>')" class="product_option_null product_option<?php echo esc_attr( $_tmd_pos_pd_id ) ?>" name="tmd_product_varoiation">
                                                                        <option value="null" disabled selected>---<?php esc_html_e( 'Choose an option', 'tmdpos') ?>---</option>
                                                                        <?php
                                                                            foreach( $tmd_product_varoiation as $variation ){
                                                                                $option_value = array();

                                                                                foreach( $variation['attributes'] as $attribute => $term_slug ){
                                                                                    $taxonomy = str_replace( 'attribute_', '', $attribute );
                                                                                    if(!empty(get_taxonomy( $taxonomy )->labels->singular_name)){
                                                                                        $attribute_name   = get_taxonomy( $taxonomy )->labels->singular_name; // Attribute name
                                                                                        $term_name        = get_term_by( 'slug', $term_slug, $taxonomy )->name; // Attribute value term name
                                                                                        $variation_n      = wc_get_product($variation['variation_id']); //variation name
                                                                                        $variation_st     = new WC_Product_Variation( $variation['variation_id'] );
                                                                                        $variations_stock = $variation_st->get_stock_quantity(); /*stock qty*/
                                                                                        $option_value[]   = $attribute_name . ': '.$term_name;
                                                                                    }
                                                                                }
                                                                                $option_value = implode( ' , ', $option_value );
                                                                                $stock_status = $variation['is_in_stock'] == 1 ? __( 'In Stock', 'tmdpos' ) : __('Out Of Stock', 'tmdpos');
                                                                                $backordered  = get_post_meta( $variation['variation_id'], '_backorders', true );
                                                                                ?>
                                                                                    <option 
                                                                                        data-backodr="<?php echo esc_attr( $backordered ); ?>" 
                                                                                        data-status="<?php echo esc_attr( $stock_status ); ?>" 
                                                                                        data-currency="<?php echo esc_attr(get_woocommerce_currency_symbol()); ?>" 
                                                                                        data-sku="<?php echo esc_attr( $variation_n->get_sku() ); ?>" 
                                                                                        data-stqty="<?php echo esc_attr( $variations_stock ); ?>" 
                                                                                        data-name="<?php echo esc_attr( $variation_n->get_name() );?>"  
                                                                                        data-cost="<?php echo esc_attr( get_post_meta($variation['variation_id'], '_price', true) ); ?>"
                                                                                        data-img="<?php echo esc_url( $variation['image']['url'] ); ?>" 
                                                                                        value="<?php echo esc_attr( $variation['variation_id'] ); ?>"><?php echo esc_attr( $option_value ); ?>        
                                                                                    </option>
                                                                                <?php
                                                                            }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                            <?php
                                                        }
                                                    ?>
                                                    </tbody>
                                                </table>
                                                <?php 

                                                    if( $_tmdpos_product->is_type( 'grouped' ) ){
                                                        $children_pds   = $_tmdpos_product->get_children();
                                                        if(!empty($children_pds)){
                                                            ?>
                                                                <table>
                                                                    <tbody>
                                                                        <?php 
                                                                            foreach( $children_pds as $children_pd){
                                                                                ?>
                                                                                    <tr>
                                                                                        <th><input type="radio" data-rel="<?php echo esc_attr( $children_pd ); ?>" class="grp_children_pd" value="<?php echo esc_attr( $children_pd ); ?>" name="child_product"></th>
                                                                                        <td><?php echo esc_html( get_the_title( $children_pd ) ); ?></td>
                                                                                    </tr>
                                                                                <?php 
                                                                            } 
                                                                        ?>
                                                                    </tbody>
                                                                </table>
                                                            <?php 
                                                        }
                                                    }
                                                ?>
                                            </div>

                                            <div class="tmd_pos_cart_btn">                          
                                                <button class="button option_add_to_cart"><span style="vertical-align: middle;" onclick="option_add_to_cart_pd('<?php echo esc_js( $_tmd_pos_pd_id ); ?>')" class="pos_add_ro_cart dashicons dashicons-cart active_cart" currency="<?php echo  esc_attr( get_woocommerce_currency_symbol() ); ?>"></span></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pos-p-title">
                                <h4><?php echo esc_html( substr($_tmdpos_product->get_title(), 0, 20) );?>  </h4>
                                <?php echo wp_kses_post( $_tmdpos_product->get_price_html() ); ?>
                                <input type="hidden" name="product_p<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_price() ); ?>">
                                <input type="hidden" name="product_id<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>">
                                <input type="hidden" name="product_sku<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_sku() ); ?>">
                                <input type="hidden" name="product_name<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( the_title() ); ?>">
                                <input type="hidden" name="currency_symbol" value="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>">
                        </div>
                        </li>
                        <?php
                    }
                }
            ?>
        </ul>
        <?php
        header("Content-type: application/json; charset=utf-8");
        die();      
    }   
}

if( !function_exists( 'tmdpos_order_print' ) ){
    // tmd pos order print ajax
    add_action( 'wp_ajax_tmdpos_order_print', 'tmdpos_order_print');
    add_action( 'wp_ajax_nopriv_tmdpos_order_print', 'tmdpos_order_print');
    function tmdpos_order_print() {
        
        global $wpdb;
        $pos_order_id    = sanitize_text_field( absint( $_POST['pos_order_id'] ) );
        $tmd_pos_order   = $wpdb->prefix . "tmd_pos_order";
        $pos_table       = $wpdb->prefix . "tmd_pos"; //admin setting invoice table
        $query           = $wpdb->prepare( "SELECT * FROM $pos_table WHERE `tmd_option` = %s", 'tmd_invoice_option' );        
        $invoice_datas   = $wpdb->get_row( $query );// Run the prepared query
        $invoicedatas    = json_decode($invoice_datas->tmd_option_value);

        $query           = $wpdb->prepare( "SELECT * FROM $tmd_pos_order WHERE order_meta = %d", $pos_order_id );
        $order_data      = $wpdb->get_row( $query );// Run the prepared query
        $order_meta_datas= json_decode( $order_data->order_value ); // get order data form tmd pos table

        if( isset( $pos_order_id ) ):
            // tmd pos order table data    
            $order         = wc_get_order( $pos_order_id ); // get order detial form wc
            $order_status  = $order_meta_datas->order_status;
            $shipping_cost = $order_meta_datas->shipping_cost;
            $discount      = $order_meta_datas->discount;
            $paid_amount   = $order_meta_datas->paid_amount;
            $order_total   = $order_meta_datas->wt_dis_total;
            $change        = $order_meta_datas->change;
            $order_note    = $order_meta_datas->order_note;
            /*tmd pos order table data end*/  
            if( $order->get_status() != 'pending' && $order->get_status() != 'processing'  && $order->get_status() != 'on-hold' && $order->get_status() != 'refunded'  && $order->get_status() != 'cancelled'  && $order->get_status() != 'failed' ){
                ?>
                <div class="order_invoice_page" style="margin: 0 auto;">
                    <?php if( $invoicedatas->reciept_formate == 'Full Size' ): ?>
                        <div class="height_width_setting" style="width:<?php echo esc_attr( $invoicedatas->reciept_width ); ?>px;">
                            <p style="text-align: center;"><?php esc_html_e('Receipt','tmdpos'); ?></p>

                            <?php 
                                if( empty( $invoicedatas->show_logo ) ){
                                    ?>
                                        <div style="
                                            height:<?php echo esc_attr( $invoicedatas->logo_size_height ); ?>;
                                            width:<?php echo esc_attr( $invoicedatas->logo_size_width ); ?>;
                                            background: url(<?php echo esc_url( $invoicedatas->reciept_logo ); ?>) no-repeat;
                                            background-size: <?php echo esc_attr( $invoicedatas->logo_size_height ); ?>px <?php echo esc_attr( $invoicedatas->logo_size_width ); ?>px;">        
                                        </div>
                                    <?php
                                } 
                            ?>      

                            <table style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="text-align: left; margin-bottom: 5px; width: 100%; display: block;"><?php esc_html_e( 'TMD POS', 'tmdpos' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="display: flex;">
                                            <section style="width: 50%; padding: 10px; border: 1px solid #c5c2c2; letter-spacing: 0.7px; line-height: 1.2;">
                                                
                                                <?php if( empty( $invoicedatas->show_store_name ) ): ?>       
                                                    <strong><?php echo esc_html( get_bloginfo( 'name' ) ); ?></strong><br>
                                                <?php endif; ?>

                                                <?php 
                                                    $user   = wp_get_current_user(); // The current user
                                                    $country = $user->billing_country;
                                                    $state   = $user->billing_state;
                                                    // The main address pieces:
                                                    $store_address     = get_option( 'woocommerce_store_address' );
                                                    $store_address_2   = get_option( 'woocommerce_store_address_2' );
                                                    $store_city        = get_option( 'woocommerce_store_city' );
                                                    $store_postcode    = get_option( 'woocommerce_store_postcode' );
                                                    $store_raw_country = get_option( 'woocommerce_default_country' );
                                                    $split_country     = explode( ":", $store_raw_country );
                                                    $store_country     = $split_country[0]; // Split the country/state
                                                    $store_state       = WC()->countries->get_states( $country )[$state];
                                                    
                                                    // store info
                                                    if( empty( $invoicedatas->show_store_address ) ){
                                                        ?>
                                                            <b><?php esc_html_e('Address 1:', 'tmdpos' ); ?></b><?php echo esc_html( $store_address ); ?> <br/>
                                                            <?php 
                                                                if ($store_address_2) {
                                                                    ?>
                                                                    <b><?php esc_html_e( 'Address 2:', 'tmdpos' ); ?></b>&nbsp;<?php echo esc_html( $store_address_2 ); ?><br />
                                                                <?php 
                                                                }
                                                            ?>
                                                            <b><?php esc_html_e('Store Location:', 'tmdpos' ); ?></b>&nbsp;<?php echo esc_html( $store_city.', '.$store_state.' '.$store_postcode); ?> <br />
                                                            <?php echo esc_html( $store_country ); ?>
                                                        <?php
                                                    }       
                                                    // extra store info 
                                                    if( !empty( $invoicedatas->invoice_extra_info ) ){
                                                        ?>
                                                            <b><?php esc_html_e( 'GST', 'tmdpos' ); ?></b>&nbsp;<?php echo esc_html( $invoicedatas->invoice_extra_info ); ?><br />
                                                        <?php 
                                                    }
                                                ?>
                                            </section>

                                            <section style="width: 50%; padding: 10px; border: 1px solid #c5c2c2; letter-spacing: 0.7px; line-height: 1.2;">
                                                <?php
                                                    $order_status  = $order->get_status(); // Get the order status 
                                                    $currency      = $order->get_currency(); // Get the currency used  
                                                    $date_created  = $order->get_date_created();
                                                    $order_date    = $date_created->date('Y-m-d h:i:sa');
                                                    if( empty( $invoicedatas->show_order_date ) ){ 
                                                        ?>
                                                            <b><?php esc_html_e( 'Date Added', 'tmdpos'); ?> </b>&nbsp; <?php echo esc_html( $order_date ); ?><br />
                                                        <?php
                                                    }
                                                    if( empty( $invoicedatas->show_invoice_number ) ){
                                                        ?>
                                                            <b><?php esc_html_e( 'Invoice No.', 'tmdpos'); ?> </b>&nbsp; <?php echo esc_html( $pos_order_id ); ?><br />
                                                        <?php
                                                    } 
                                                    ?>
                                                        <b><?php esc_html_e( 'Order Status', 'tmdpos'); ?> </b>&nbsp; <?php echo esc_html( $order_status ); ?><br />
                                                    <?php 
                                                    if( empty( $invoicedatas->show_Payment_mode ) ){
                                                        ?>
                                                            <b><?php esc_html_e( 'Payment Method', 'tmdpos'); ?> </b>&nbsp; <?php echo esc_html( $order_meta_datas->payment_method ); ?><br />
                                                        <?php
                                                    }
                                                    if( empty( $invoicedatas->show_cashier_name ) ){
                                                        ?>
                                                            <b><?php esc_html_e( 'Cashier', 'tmdpos'); ?> </b>&nbsp; <?php echo esc_html( $order_meta_datas->cashier ); ?><br />
                                                        <?php
                                                    }
                                                    if( empty( $invoicedatas->show_customer_name ) ){
                                                        if( !empty( $order_meta_datas->existing_customer ) ){
                                                            ?>
                                                                <b><?php esc_html_e( 'Customer', 'tmdpos'); ?> </b>&nbsp; <?php echo esc_html( $order_meta_datas->existing_customer ); ?><br />
                                                            <?php
                                                        } 
                                                        else {
                                                            ?>
                                                                <b><?php esc_html_e( 'Customer', 'tmdpos'); ?> </b>&nbsp; <?php echo esc_html( $order_meta_datas->tmd_pos_customer ); ?><br />
                                                            <?php
                                                        }
                                                    }
                                                ?>
                                            </section>
                                        </td>
                                    </tr>
                            
                                    <tr>
                                        <td style="display: flex;">
                                            <?php 
                                                if( empty( $invoicedatas->show_shipping_address ) ){
                                                    if( !empty($order_meta_datas->shipping_cost) ){
                                                        ?>
                                                            <section style="border: 1px solid #c5c2c2; width: 50%; padding: 10px;">
                                                                <label><strong><?php esc_html_e( 'Shipping address :', 'tmdpos' ); ?></strong></label><br />
                                                                <?php
                                                                    $customer       = new WP_User( $order_meta_datas->shop_customer );
                                                                    $customer_phone = get_user_meta( $order_meta_datas->shop_customer, 'billing_phone', true );
                                                                    // Customer shipping information details
                                                                    ?>
                                                                        <strong><?php echo esc_html( $customer->shipping_first_name ); ?></strong><br />
                                                                        <strong><?php esc_html_e('Phone :', 'tmdpos' ); ?></strong><?php echo esc_html( $customer_phone ); ?>
                                                                    <?php
                                                                    echo esc_html( $customer->shipping_company ).'<br />';
                                                                    echo esc_html( $customer->shipping_address_1 ).'<br />';
                                                                    echo esc_html( $customer->shipping_address_2 ).'<br />';
                                                                    echo esc_html( $customer->shipping_city ).'<br />';
                                                                    echo esc_html( $customer->shipping_state ).'<br />';
                                                                    echo esc_html( $customer->shipping_postcode ).'<br />';
                                                                    echo esc_html( $customer->shipping_country ).'<br />';
                                                                    echo esc_html( $order->get_customer_note() );
                                                                ?>
                                                            </section>
                                                        <?php 
                                                    }
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table style="width: 100%; border-collapse: collapse; margin-top: 30px;">
                                <thead style="text-align: left;">
                                    <tr>
                                        <th style="border: 1px solid #c5c2c2; padding: 10px; width: 40%;"><?php esc_html_e( 'Product', 'tmdpos'); ?></th>
                                        <th style="border: 1px solid #c5c2c2; padding: 10px; width: 20%;"><?php esc_html_e( 'Category', 'tmdpos'); ?></th>
                                        <th style="border: 1px solid #c5c2c2; padding: 10px; width: 20%;"><?php esc_html_e( 'Quantity', 'tmdpos'); ?></th>
                                        <th style="border: 1px solid #c5c2c2; padding: 10px; width: 20%;"><?php esc_html_e( 'Price', 'tmdpos'); ?></th>
                                    </tr>
                                </thead>

                                <tbody style="text-align: left;">
                                    <?php  
                                        // Iterating through each WC_Order_Item_Product objects
                                        foreach ($order->get_items() as $item_key => $item ):
                                            $item_id       = $item->get_id();
                                            $product       = $item->get_product(); // Get the WC_Product object
                                            $product_price = $product->get_price();
                                            $product_id    = $item->get_product_id(); // the Product id
                                            $variation_id  = $item->get_variation_id(); // the Variation id
                                            $terms         = get_the_terms( $product_id, 'product_cat' );/*product category*/

                                            foreach ($terms as  $value) {
                                                $product_cats = $value->name;
                                            }

                                            $item_name      = $item->get_name(); // Name of the product
                                            $quantity       = $item->get_quantity();  
                                            $line_subtotal  = wc_format_decimal( $item->get_subtotal(), 2); // Line subtotal (non discounted)
                                            $line_total_tax = $item->get_total_tax(); // Line total tax (discounted)
                                            $currency       = get_woocommerce_currency_symbol();
                                            ?>
                                                <tr>
                                                    <td style="border: 1px solid #c5c2c2; padding: 10px; width: 40%;"><?php echo esc_html( $item_name ); ?></td>
                                                    <td style="border: 1px solid #c5c2c2; padding: 10px; width: 20%;"><?php echo esc_html( $product_cats ); ?></td>
                                                    <td style="border: 1px solid #c5c2c2; padding: 10px; width: 20%;"><?php echo esc_html( $quantity ); ?></td>
                                                    <td style="border: 1px solid #c5c2c2; padding: 10px; width: 20%;"><?php echo esc_html( $currency.( $product_price * $quantity ) ); ?></td>
                                                </tr>
                                            <?php
                                        endforeach;
                                    ?>
                                </tbody>
                            </table>
                                
                            <table style="width: 100%; margin: 30px auto; border-collapse: collapse; ">
                                <tr>
                                    <th style="text-align: right; border: 1px solid #c5c2c2; padding: 5px; width:80%;"><?php esc_html_e('Subtotal', 'tmdpos'); ?></th>
                                    <td style="border: 1px solid #c5c2c2; padding: 5px; width: 20%;"><?php echo esc_html( $currency.$order_meta_datas->_subtotal ); ?></td>
                                </tr>

                                <?php if( !empty( $order_meta_datas->shipping_cost ) ): ?>
                                    <tr>
                                        <th style="text-align: right; border: 1px solid #c5c2c2; padding: 5px; width:80%;"><?php esc_html_e('Shipping', 'tmdpos'); ?></th>
                                        <td style="border: 1px solid #c5c2c2; padding: 5px; width: 20%;"><?php echo esc_html( $currency.$order_meta_datas->shipping_cost ); ?></td>
                                    </tr>
                                <?php endif; ?>

                                <?php if( !empty( $order_meta_datas->discount ) ): ?>
                                    <tr>
                                        <th style="text-align: right; border: 1px solid #c5c2c2; padding: 5px; width:80%;"><?php esc_html_e('Discount', 'tmdpos'); ?></th>
                                        <td style="border: 1px solid #c5c2c2; padding: 5px; width: 20%;"><?php echo esc_html( $currency.$order_meta_datas->discount ); ?></td>
                                    </tr>
                                <?php endif; ?>

                                <?php  if( !empty( $order_meta_datas->coupon_amount ) ): ?>
                                    <tr>
                                        <th style="text-align: right; border: 1px solid #c5c2c2; padding: 5px; width:80%;"><?php esc_html_e('Coupon', 'tmdpos'); ?></th>
                                        <td style="border: 1px solid #c5c2c2; padding: 5px; width: 20%;"><?php echo '-'. esc_html( $currency.$order_meta_datas->coupon_amount ); ?></td>
                                    </tr>
                                <?php endif; ?>

                                <tr>
                                    <th style="text-align: right; border: 1px solid #c5c2c2; padding: 5px; width:80%;"><?php esc_html_e('Tax', 'tmdpos'); ?></th>   
                                    <td style="border: 1px solid #c5c2c2; padding: 5px; width: 20%;"><?php  echo esc_html( $currency.$order_meta_datas->tax_total ); ?></td>
                                </tr>

                                <tr>
                                    <th style="text-align: right; border: 1px solid #c5c2c2; padding: 5px; width:80%;"><?php esc_html_e('Total', 'tmdpos'); ?></th>
                                    <td style="border: 1px solid #c5c2c2; padding: 5px; width: 20%;"><?php echo esc_html( $currency.$order_meta_datas->wt_dis_total ); ?></td>
                                </tr>
                                <?php $change = !empty( $order_meta_datas->change ) ? $order_meta_datas->change : 0.00; ?>

                                <?php if( empty( $invoicedatas->show_changes ) ): ?>
                                    <tr>
                                        <th style="text-align: right; border: 1px solid #c5c2c2; padding: 5px; width:80%;"><?php esc_html_e('Change', 'tmdpos'); ?></th>
                                        <td style="border: 1px solid #c5c2c2; padding: 5px; width: 20%;"><?php echo esc_html( $currency.$change ); ?></td>
                                    </tr>
                                <?php endif; ?>
                                <!-- footer -->
                            </table>
                            <p><?php echo esc_html( $invoicedatas->invoice_thanks_msg ); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if( $invoicedatas->reciept_formate == 'Reciept Print' ): ?>

                        <style>
                            #invoice-tmdpos {
                                box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5);
                                padding: 2mm;
                                margin: 0 auto;
                                width: <?php echo esc_html( $invoicedatas->reciept_width );?>mm;
                                background: #FFF;
                            }

                            #invoice-tmdpos ::selection {
                                background: #f31544;
                                color: #FFF;
                            }

                            #invoice-tmdpos ::moz-selection {
                                background: #f31544;
                                color: #FFF;
                            }

                            #invoice-tmdpos h1 {
                                font-size: 1.5em;
                                color: #222;
                            }

                            #invoice-tmdpos h2 {
                                font-size: .9em;
                            }

                            #invoice-tmdpos h3 {
                                font-size: 1.2em;
                                font-weight: 300;
                                line-height: 2em;
                            }

                            #invoice-tmdpos p {
                                font-size: .7em;
                                color: #666;
                                line-height: 1.2em;
                            }

                            #invoice-tmdpos #top,
                            #mid,
                            #bot {
                                /* Targets all id with 'col-' */
                                border-bottom: 1px solid #EEE;
                            }

                            #invoice-tmdpos #top {
                                min-height: 100px;
                            }

                            #invoice-tmdpos #mid {
                                min-height: 80px;
                            }

                            #invoice-tmdpos #bot {
                                min-height: 50px;
                            }

                            #invoice-tmdpos #top .logo {
                                height: <?php echo esc_html( $invoicedatas->logo_size_height );?>;
                                width: <?php echo esc_html( $invoicedatas->logo_size_width );?>;
                                <?php if(empty($invoicedatas->show_logo )): ?> 
                                    background: url(<?php echo esc_url( $invoicedatas->reciept_logo ); ?>) no-repeat;
                                <?php endif;?>
                                background-size: <?php echo esc_html( $invoicedatas->logo_size_height ); ?>px <?php echo esc_html( $invoicedatas->logo_size_width );?>px;
                                -webkit-print-color-adjust: exact;
                            }

                            #invoice-tmdpos .info {
                                display: block;
                                margin-left: 0;
                            }

                            #invoice-tmdpos .title {
                                float: right;
                            }

                            #invoice-tmdpos .title p {
                                text-align: right;
                            }

                            #invoice-tmdpos table {
                                width: 100%;
                                border-collapse: collapse;
                            }

                            #invoice-tmdpos td {
                                padding: 5px 0 5px 5px;
                            }

                            #invoice-tmdpos .tabletitle {
                                padding: 5px;
                                font-size: .5em;
                                background: #EEE;
                            }

                            #invoice-tmdpos .service {
                                border-bottom: 1px solid #EEE;
                            }

                            #invoice-tmdpos .item {
                                width: 24mm;
                            }

                            #invoice-tmdpos .itemtext {
                                font-size: .5em;
                            }

                            #invoice-tmdpos #legalcopy {
                                margin-top: 5mm;
                            }
                        </style>

                        <?php    
                            $user    = wp_get_current_user(); // The current user
                            $country = $user->billing_country;
                            $state   = $user->billing_state;

                            // The main address pieces:
                            $store_address     = get_option( 'woocommerce_store_address' );
                            $store_address_2   = get_option( 'woocommerce_store_address_2' );
                            $store_city        = get_option( 'woocommerce_store_city' );
                            $store_postcode    = get_option( 'woocommerce_store_postcode' );
                            $store_raw_country = get_option( 'woocommerce_default_country' );
                            $split_country     = explode( ":", $store_raw_country ); //The country/state
                            $store_country     = $split_country[0]; // Country and state separated:
                            $store_state       = WC()->countries->get_states( $country )[$state];

                            // get order detial form wc 
                            $order         = wc_get_order( $pos_order_id );
                            $order_status  = $order->get_status(); // Get the order status 
                            $currency      = $order->get_currency(); // Get the currency used  
                            $date_created  = $order->get_date_paid();
                            $order_date    = $date_created->date('Y-m-d h:i:sa');
                        ?>
                        <div id="invoice-tmdpos">  
                            <center id="top">
                                <div class="logo"></div>
                                <div class="info"> 
                                    <?php 
                                        if( empty( $invoicedatas->show_store_name ) ){
                                            ?>
                                                <h2><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h2>
                                            <?php
                                        } 
                                    ?>
                                </div><!--End Info-->
                            </center><!--End InvoiceTop-->
                            
                            <div id="mid">
                                <div class="info">
                                    <h2><?php esc_html_e('Contact Info', 'tmdpos' ); ?></h2>
                                    <p style="letter-spacing: 0.7px; line-height: 1.2;"> 
                                        <?php
                                            if( empty( $invoicedatas->show_store_address ) ){
                                                ?>
                                                    <b><?php esc_html('Address 1:', 'tmdpos'); ?></b>&nbsp;<?php esc_html( $store_address ); ?><br>
                                                    <?php 
                                                        if ( $store_address_2 ) {
                                                            ?>
                                                                <b><?php esc_html_e( 'Address 2:', 'tmdpos'); ?></b>&nbsp;<?php echo esc_html( $store_address_2 ); ?><br />
                                                            <?php
                                                        }
                                                    ?>
                                                    <b><?php esc_html_e( 'Store Location:', 'tmdpos' ); ?></b>&nbsp;<?php echo esc_html( $store_city.', '.$store_state. ' '.$store_postcode ); ?> <br />
                                                <?php
                                                echo esc_html( $store_country ).'<br />';
                                            } 

                                            /*extra store info*/
                                            if( !empty( $invoicedatas->invoice_extra_info ) ){
                                                ?>
                                                    <b><?php esc_html_e( 'GST', 'tmdpos' ); ?></b>&nbsp;<?php echo esc_html( $invoicedatas->invoice_extra_info ); ?>
                                                <?php
                                            }
                                        ?>
                                    </p>
                                </div>
                            </div>

                            <div id="mid">
                                <div class="info">
                                    <h2><?php esc_html_e( 'Order details', 'tmdpos' ); ?></h2>
                                    <p style="letter-spacing: 0.7px; line-height: 1.2;">
                                        <?php 
                                            if( empty( $invoicedatas->show_order_date ) ){
                                                ?>
                                                    <b><?php esc_html_e('Date Added', 'tmdpos'); ?></b>&nbsp;<?php echo esc_html( $order_date ); ?><br />
                                                <?php
                                            }

                                            if( empty( $invoicedatas->show_invoice_number ) ){
                                                ?>
                                                    <b><?php esc_html_e('Invoice No.', 'tmdpos'); ?></b>&nbsp;<?php echo esc_html( $pos_order_id ); ?><br />
                                                <?php
                                            }
                                            ?>
                                                <b><?php esc_html_e('Order Status', 'tmdpos'); ?></b>&nbsp;<?php echo esc_html( $order_status ); ?><br />
                                            <?php
                                            if( empty( $invoicedatas->show_Payment_mode ) ){
                                                ?>
                                                    <b><?php esc_html_e('Payment Method', 'tmdpos'); ?></b>&nbsp;<?php echo esc_html( $order_meta_datas->payment_method ); ?><br />
                                                <?php
                                            }
                                            if( empty( $invoicedatas->show_cashier_name ) ){
                                                ?>
                                                    <b><?php esc_html_e('Cashier', 'tmdpos'); ?></b>&nbsp;<?php echo esc_html( $order_meta_datas->cashier ); ?><br />
                                                <?php
                                            }

                                            if( empty( $invoicedatas->show_customer_name ) ){
                                                if( !empty( $order_meta_datas->existing_customer ) ){
                                                    ?>
                                                        <b><?php esc_html_e('Customer', 'tmdpos'); ?></b>&nbsp;<?php echo esc_html( $order_meta_datas->existing_customer ); ?><br />
                                                    <?php
                                                } 
                                                else {
                                                    ?>
                                                        <b><?php esc_html_e('Customer', 'tmdpos'); ?></b>&nbsp;<?php echo esc_html( $order_meta_datas->tmd_pos_customer ); ?><br />
                                                    <?php
                                                }
                                            }  
                                        ?>
                                    </p>
                                </div>
                            </div>

                            <?php  
                                if( empty( $invoicedatas->show_shipping_address ) ){
                                    if( !empty($order_meta_datas->shipping_cost) ){
                                        ?>
                                            <div id="mid">
                                                <div class="info">
                                                    <h2><?php esc_html_e( 'Shipping Address', 'tmdpos' ); ?></h2>
                                                    <p>
                                                        <?php
                                                            $customer       = new WP_User( $order_meta_datas->shop_customer );
                                                            $customer_phone = get_user_meta( $order_meta_datas->shop_customer, 'billing_phone', true);
                                                            // Customer shipping information detail
                                                            ?>
                                                                <strong><?php echo esc_html( $customer->shipping_first_name ); ?></strong><br />
                                                                <strong><?php esc_html_e('Phone :', 'tmdpos'); ?> <?php echo esc_html( $customer_phone );?> </strong>
                                                            <?php
                                                            echo esc_html( $customer->shipping_company ).'<br />';
                                                            echo esc_html( $customer->shipping_address_1 ).'<br />';
                                                            echo esc_html( $customer->shipping_address_2 ).'<br />';
                                                            echo esc_html( $customer->shipping_city ).'<br />';
                                                            echo esc_html( $customer->shipping_state) .'<br />';
                                                            echo esc_html( $customer->shipping_postcode ).'<br />';
                                                            echo esc_html( $customer->shipping_country ).'<br />';
                                                            echo esc_html( $order->get_customer_note() );
                                                        ?>
                                                    </p>
                                                </div>
                                            </div>
                                        <?php
                                    }
                                }
                            ?>

                            <!--End Invoice Mid-->
                            <div id="bot">
                                <div id="table">
                                    <table>
                                        <tr class="tabletitle">
                                            <td class="item"><h2><?php esc_html_e( 'Items', 'tmdpos'); ?></h2></td>
                                            <td class="Hours"><h2><?php esc_html_e( 'Qty', 'tmdpos'); ?></h2></td>
                                            <td class="Rate"><h2><?php esc_html_e( 'Sub Total', 'tmdpos'); ?></h2></td>
                                        </tr>

                                        <?php 

                                            foreach ($order->get_items() as $item_key => $item ):
                                                $item_id = $item->get_id();
                                                $product       = $item->get_product(); // Get the WC_Product object
                                                $product_price = $product->get_price();
                                                $product_id    = $item->get_product_id(); // the Product id
                                                $variation_id  = $item->get_variation_id(); // the Variation id
                                                $terms         = get_the_terms( $product_id, 'product_cat' );/*product category*/
                                                
                                                foreach ($terms as  $value) {
                                                    $product_cats = $value->name;
                                                }
                                                
                                                $item_name      = $item->get_name(); // Name of the product
                                                $quantity       = $item->get_quantity();  
                                                $line_subtotal  = wc_format_decimal( $item->get_subtotal(), 2); // Line subtotal (non discounted)
                                                $line_total_tax = $item->get_total_tax(); // Line total tax (discounted)
                                                $currency       = get_woocommerce_currency_symbol();

                                                ?>
                                                    <tr class="service">
                                                        <td class="tableitem"><p class="itemtext"><?php echo esc_html( $item_name ); ?></p></td>
                                                        <td class="tableitem"><p class="itemtext"><?php echo esc_html( $quantity ); ?></p></td>
                                                        <td class="tableitem"><p class="itemtext"><?php echo esc_html( $currency.$line_subtotal ); ?></p></td>
                                                    </tr>
                                                <?php 
                                            endforeach; 
                                        ?>

                                        <tr class="tabletitle">
                                            <td></td>
                                            <td class="Rate"><h2><?php esc_html_e( 'Subtotal', 'tmdpos' ); ?></h2></td>
                                            <td class="payment"><h2><?php echo esc_attr( $currency.$order_meta_datas->_subtotal ); ?></h2></td>
                                        </tr>

                                        <?php if( !empty( $order_meta_datas->shipping_cost ) ): ?>
                                            <tr class="tabletitle">
                                                <td></td>
                                                <td class="Rate"><h2><?php esc_html_e( 'Shipping', 'tmdpos' ); ?></h2></td>
                                                <td class="payment"><h2><?php echo esc_attr( $currency.$order_meta_datas->shipping_cost ); ?></h2></td>
                                            </tr>
                                        <?php endif;?> 

                                        <?php if( !empty( $order_meta_datas->discount ) ): ?>
                                            <tr class="tabletitle">
                                                <td></td>
                                                <td class="Rate"><h2><?php esc_html_e( 'Discount', 'tmdpos' ); ?></h2></td>
                                                <td class="payment"><h2><?php echo esc_attr( $currency.$order_meta_datas->discount ); ?></h2></td>
                                            </tr>
                                        <?php endif; ?>

                                        <tr class="tabletitle">
                                            <td></td>
                                            <td class="Rate"><h2><?php esc_html_e( 'Tax','tmdpos' ); ?></h2></td>
                                            <td class="payment"><h2><?php echo esc_attr( $currency.$order_meta_datas->tax_total ); ?></h2></td>
                                        </tr>

                                        <?php if( !empty( $order_meta_datas->coupon_amount ) ): ?>
                                        <tr class="tabletitle">
                                            <td></td>
                                            <td class="Rate"><h2><?php esc_html_e( 'Coupon', 'tmdpos' ); ?></h2></td>
                                            <td class="payment"><h2><?php  echo '-'.esc_html( $currency.$order_meta_datas->coupon_amount ); ?></h2></td>
                                        </tr>
                                        <?php endif; ?>

                                        <tr class="tabletitle">
                                            <td></td>
                                            <td class="Rate"><h2><?php esc_html_e('Total','tmdpos'); ?></h2></td>
                                            <td class="payment"><h2><?php echo esc_html( $currency.$order_meta_datas->wt_dis_total ); ?></h2></td>
                                        </tr>

                                    <?php  if( empty( $invoicedatas->show_changes ) ): ?>
                                        <tr class="tabletitle">
                                            <td></td>
                                            <td class="Rate"><h2><?php esc_html_e('Change','tmdpos'); ?></h2></td>
                                            <td class="payment"><h2><?php echo esc_html( $currency.$change ); ?></h2></td>
                                        </tr>
                                    <?php endif; ?>

                                    </table>
                                </div><!--End Table-->

                                <div id="legalcopy">
                                    <p class="legal"><?php echo esc_html( $invoicedatas->invoice_thanks_msg ); ?></p>
                                </div>

                            </div><!--End InvoiceBot-->
                        </div><!--End Invoice-->

                        <!-- receipt bill  html end-->
                    </div>
                <?php endif; ?>
                <?php

            }
            else{
                ?><p class="print_order_message"><?php esc_html_e('Order Is Not Completed Please Complete Order First To Print Order Receipt.', 'tmdpos'); ?></p><?php 
            }
        endif;
        header("Content-type: application/json; charset=utf-8"); 
        die();
    }
    /*tmd pos order print ajax end*/
    
}


if( !function_exists( 'tmdpos_get_country' ) ){

    /*tmd pos add new customer country state select*/
    add_action( 'wp_ajax_tmdpos_get_country', 'tmdpos_get_country');
    add_action( 'wp_ajax_nopriv_tmdpos_get_country', 'tmdpos_get_country');
    function tmdpos_get_country(){
        global $woocommerce;
        $json[]        = array();
        $countries_obj = new WC_Countries();// country list 
        $countries     = $countries_obj->__get('countries');

        foreach ( $countries as $key => $countries ){
            $json['countries'][]    = $countries;
            $json['country_code'][] = $key;
        }
        header("Content-type: application/json; charset=utf-8");
        echo wp_json_encode($json); 
        die();
    }
    
}


if( !function_exists( 'tmdpos_get_state' ) ){
    add_action( 'wp_ajax_tmdpos_get_state', 'tmdpos_get_state');
    add_action( 'wp_ajax_nopriv_tmdpos_get_state', 'tmdpos_get_state');
    function tmdpos_get_state(){
        global $woocommerce;
        $json[]       = array();
        $country_code = sanitize_text_field( $_POST['country_code'] );
        if( ! empty( $country_code ) ){
            $countries_obj  = new WC_Countries();
            $countries      = $countries_obj->__get('countries');
            $country_state  = $countries_obj->get_states($country_code);
            foreach ($country_state as $state_code => $state){
            $json['country_state_code'][] =  $state_code;
            $json['country_state'][] =  $state; 
            }
        }
        header("Content-type: application/json; charset=utf-8");
        echo wp_json_encode($json); 
        die();
    }  
}

if( !function_exists( 'tmdpos_save_customer_data' ) ){
    // tmd pos add new customer country state select end
add_action( 'wp_ajax_tmd_save_customer_data', 'tmd_save_customer_data');
add_action( 'wp_ajax_nopriv_tmd_save_customer_data', 'tmd_save_customer_data');
  function tmd_save_customer_data(){
    $customer = $_POST['customer_data'];
    $use_name = $customer['name'];
    $email    = $customer['email'];
    $def_pass = '';
    if (empty($customer['id'])) {
        $user   = wc_create_new_customer( $email, $use_name, $def_pass );
        $json   = '<div class="tmd_success_notice"><label>'.esc_html(__('Customer added successfully, Now select Customer', 'tmdpos')).'</label><span class="success_notice_close cursor_pointer">&#10006;</span></div>';
    }
    else{
       $user = $customer['id'];
       $json = '<div class="tmd_success_notice"><label>'.esc_html(__('Customer Updated successfully, Now select Customer', 'tmdpos')).'</label><span class="success_notice_close cursor_pointer">&#10006;</span></div>';
    }
    
    // billing address
    update_user_meta( $user, 'first_name', $customer['name'] );
    update_user_meta( $user, 'billing_first_name', $customer['name'] );
    update_user_meta( $user, 'billing_email', $customer['email'] );
    update_user_meta( $user, 'billing_phone', $customer['phone'] );
    update_user_meta( $user, 'billing_address_1', $customer['address']);
    update_user_meta( $user, 'billing_address_2', $customer['address2'] );
    update_user_meta( $user, 'billing_city', $customer['city']);
    update_user_meta( $user, 'billing_postcode', $customer['postcode'] );
    update_user_meta( $user, 'billing_country', $customer['country']);
    update_user_meta( $user, 'billing_state', $customer['state'] );
    // shipping address
    update_user_meta( $user, 'shipping_first_name', $customer['name'] );
    update_user_meta( $user, 'shipping_address_1', $customer['address']);
    update_user_meta( $user, 'shipping_address_2', $customer['address2'] );
    update_user_meta( $user, 'shipping_city', $customer['city']);
    update_user_meta( $user, 'shipping_postcode', $customer['postcode'] );
    update_user_meta( $user, 'shipping_country', $customer['country']);
    update_user_meta( $user, 'shipping_state', $customer['state'] );
    header("Content-type: application/json; charset=utf-8");
    echo wp_json_encode($json);
    die();
}  
}

if( !function_exists( 'tmdpos_update_order_form' ) ){
    // tmd pos update order
    add_action( 'wp_ajax_tmdpos_update_order_form', 'tmdpos_update_order_form');
    add_action( 'wp_ajax_nopriv_tmdpos_update_order_form', 'tmdpos_update_order_form');
    function tmdpos_update_order_form(){
        global $wpdb;

        $existing_order_id = sanitize_text_field( absint( $_POST['order_id'] ) );
        $order             = wc_get_order( $existing_order_id );
        $user_id           = get_post_meta( $existing_order_id, '_customer_user', true );
        $customer          = new WC_Customer( $user_id );
        $data              = $order->get_data(); // The Order data
        $customer_name     = $customer->get_display_name();
        $order_data        = $order->get_data(); // The Order data
        $order_status      = $order_data['status'];
        $payment_mode      = $order->get_payment_method();

        $pos_table                = $wpdb->prefix . "tmd_pos";
        $tmd_pos_wc_gateway       = new WC_Payment_Gateways();
        $tmd_pos_payment_gateways = $tmd_pos_wc_gateway->payment_gateways();
        $query                    = $wpdb->prepare( "SELECT * FROM $pos_table WHERE `tmd_option` = %s", 'tmd_payment_option' );
        $paymentdatas             = $wpdb->get_row( $query );
        $payment_datas            = json_decode($paymentdatas->tmd_option_value);
        $tmd_pos_order_status     = wc_get_order_statuses();

        ?>
            <!-- order edit pop-up -->
            <div class="order_edit_main">
                <div class="tmd_model_container">
                    <button class="close_oder_update cursor_pointer">&#10006;</button>
                    <div class="tmd_notice_update"></div>
                    
                    <table class="tmd_pos_table">
                        <tr>
                            <th class="tmd_pos_th" ><?php esc_html_e( 'Customer', 'tmdpos' ); ?></th>
                            <td class="tmd_pos_td">
                                <input type="text" value="<?php echo esc_attr( $customer_name ); ?>" readonly />
                                <input type="hidden" class="update_order_id" name="order_id" value="<?php echo esc_attr( $existing_order_id ); ?>" readonly />
                            </td>
                        </tr>

                        <tr>
                            <th class="tmd_pos_th"><?php esc_html_e('Order Status','tmdpos'); ?></th>
                            <td class="tmd_pos_td">
                                <select class="tmd_pos_select up_order_status" name="up_order_status">
                                    <option>---<?php esc_html_e('Select Order Status', 'tmdpos' ); ?>---</option>
                                    <?php foreach ($tmd_pos_order_status as $status_key => $order_satus_value): ?>
                                        <option <?php selected( 'wc-'.$order_status, $status_key );?> value="<?php echo esc_attr( $status_key ); ?>"><?php echo esc_html( $order_satus_value ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th class="tmd_pos_th"><?php esc_html_e( 'Payment Status', 'tmdpos' ); ?></th>
                            <td class="tmd_pos_td">
                                <select class="tmd_pos_select up_order_payment">
                                    <option>---<?php esc_html_e('Select Payment Status', 'tmdpos' ); ?>---</option>
                                    <?php foreach ($tmd_pos_payment_gateways as $payment_gateway_id => $tmd_pos_payment_mode): ?>
                                        <option value="<?php echo esc_attr( $payment_gateway_id) ; ?>" <?php selected($payment_mode , $payment_gateway_id); ?>><?php echo esc_html( $tmd_pos_payment_mode->get_title() ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th class="tmd_pos_th"><?php esc_html_e( 'Comment', 'tmdpos' ); ?></th>
                            <td class="tmd_pos_td"><textarea class="tmd_pos_select up_order_note" name="order_note"><?php echo esc_html( $order->get_customer_note() ); ?></textarea></td>
                        </tr>

                        <tr>
                            <th class="tmd_pos_th"></th>
                            <td class="tmd_pos_td"><button class="button button-primary update_order"><?php esc_html_e('Update Order', 'tmdpos'); ?></button></td>
                        </tr>
                    </table>
                </div>
            </div>
            <!-- order edit pop-up end-->
        <?php
        header("Content-type: application/json; charset=utf-8");  
        die();
    }
    
}

if( !function_exists( 'tmdpos_update_order' ) ){
    add_action( 'wp_ajax_tmdpos_update_order', 'tmdpos_update_order');
    add_action( 'wp_ajax_nopriv_tmdpos_update_order', 'tmdpos_update_order');
    function tmdpos_update_order(){
        $json=array();
        $order_id      = sanitize_text_field( absint( $_POST['order_id'] ) )  ;
        $order_status  = sanitize_text_field( $_POST['order_status'] );
        $order_payment = sanitize_text_field( $_POST['order_payment'] );
        $order_note    = sanitize_text_field( $_POST['order_note'] );
        $order         = new WC_Order( $order_id );

        $order->set_customer_note( $order_note ); 
        $payment_gateways = WC()->payment_gateways->payment_gateways();
        $order->set_payment_method($payment_gateways[$order_payment]);
        $order->update_status( $order_status ); 
        $order->save();

        $json = '<div class="thnx_message"><label>'.esc_html( __( 'Order update successfully', 'tmdpos' ) ).'</label></div>';
        header("Content-type: application/json; charset=utf-8");
        echo wp_json_encode($json);
        
        die();
    }
    /*tmd pos update order end*/    
}

if( !function_exists( 'tmdpos_order_print_from_list' ) ){
    /*order invoice print from list */
    add_action( 'wp_ajax_tmdpos_order_print_from_list', 'tmdpos_order_print_from_list');
    add_action( 'wp_ajax_nopriv_tmdpos_order_print_from_list', 'tmdpos_order_print_from_list');
    function tmdpos_order_print_from_list(){
        global $wpdb;
        $tmd_pos_order   =  $wpdb->prefix . "tmd_pos_order";
        $pos_table       =  $wpdb->prefix . "tmd_pos";
        $pos_order_id    =  sanitize_text_field( $_POST['order_id'] ) ;

        //admin setting invoice table
        $query           = $wpdb->prepare( "SELECT * FROM $pos_table WHERE tmd_option = %s", 'tmd_invoice_option' );
        $invoice_datas   = $wpdb->get_row( $query );
        $invoicedatas    = json_decode($invoice_datas->tmd_option_value);

        // get order data form tmd pos table
        $query            = $wpdb->prepare( "SELECT * FROM $tmd_pos_order WHERE order_meta = %d", $pos_order_id );
        $order_data       = $wpdb->get_row($query);
        $order_meta_datas = json_decode($order_data->order_value);

        if(isset($pos_order_id)):     
            $order         = wc_get_order( $pos_order_id );
            $order_status  = $order_meta_datas->order_status;
            $shipping_cost = $order_meta_datas->shipping_cost;
            $discount      = $order_meta_datas->discount;
            $paid_amount   = $order_meta_datas->paid_amount;
            $order_total   = $order_meta_datas->wt_dis_total;
            $change        = $order_meta_datas->change;
            $order_note    = $order_meta_datas->order_note;

            if( $order->get_status() != 'pending' && $order->get_status() != 'processing'  && $order->get_status() != 'on-hold' && $order->get_status() != 'refunded'  && $order->get_status() != 'cancelled'  && $order->get_status() != 'failed' ){
                ?>
                    <div class="order_invoice_page" style="margin: 0 auto;">
                        <?php if( $invoicedatas->reciept_formate == 'Full Size' ): ?>
                            <div class="height_width_setting" style="width:<?php echo esc_attr( $invoicedatas->reciept_width ); ?>px;">
                                <p style="text-align: center;"><?php esc_html_e( 'Receipt', 'tmdpos' ); ?></p>

                                <?php if( empty( $invoicedatas->show_logo ) ): ?>       
                                    <div style="height: <?php echo esc_attr( $invoicedatas->logo_size_height ); ?>;
                                    width: <?php echo esc_attr($invoicedatas->logo_size_width) ; ?>;
                                    background: url(<?php echo esc_url( $invoicedatas->reciept_logo ); ?>) no-repeat;
                                    background-size: <?php echo esc_attr( $invoicedatas->logo_size_height ); ?>px <?php echo esc_html( $invoicedatas->logo_size_width ); ?>px;
                                    -webkit-print-color-adjust: exact;"></div>
                                <?php endif; ?>

                                <table style="width: 100%;">
                                    <!-- header -->
                                    <thead>
                                        <tr>
                                            <th style="text-align: left; margin-bottom: 5px; width: 100%; display: block;"><?php esc_html_e( 'TMD POS', 'tmdpos' ); ?></th>
                                        </tr>
                                    </thead>
                                    <!-- header end-->

                                    <tbody>
                                        <tr>
                                            <td style="display: flex;">
                                                <section style="width: 50%; padding: 10px; border: 1px solid #c5c2c2; letter-spacing: 0.7px; line-height: 1.2;">

                                                    <?php if( empty( $invoicedatas->show_store_name ) ): ?>       
                                                        <strong><?php echo esc_attr( get_bloginfo( 'name' ) ); ?></strong><br>
                                                    <?php endif; ?>

                                                    <?php 
                                                        $user    = wp_get_current_user(); // The current user
                                                        $country = $user->billing_country;
                                                        $state   = $user->billing_state;

                                                        // The main address pieces:
                                                        $store_address     = get_option( 'woocommerce_store_address' );
                                                        $store_address_2   = get_option( 'woocommerce_store_address_2' );
                                                        $store_city        = get_option( 'woocommerce_store_city' );
                                                        $store_postcode    = get_option( 'woocommerce_store_postcode' );

                                                        // The country/state
                                                        $store_raw_country = get_option( 'woocommerce_default_country' );

                                                        // Split the country/state
                                                        $split_country = explode( ":", $store_raw_country );

                                                        // Country and state separated:
                                                        $store_country = $split_country[0];
                                                        $store_state = WC()->countries->get_states( $country )[$state];
                                                        
                                                        if( empty( $invoicedatas->show_store_address ) ):       

                                                            echo '<b>'.esc_html( 'Address 1:', 'tmdpos' ).'</b>&nbsp;'. esc_html( $store_address ) . "<br>";
                                                            echo ( $store_address_2 ) ? '<b>'.esc_html('Address 2:', 'tmdpos').'</b>&nbsp;'. esc_attr( $store_address_2 ) . "<br>" : '';
                                                            echo '<b>'.esc_html('Store Location:', 'tmdpos').'</b>&nbsp;'.esc_html( $store_city ) . ', ' . esc_html( $store_state ) . ' ' . esc_html( $store_postcode ) . "<br>";
                                                            echo esc_html( $store_country ).'<br>';
                                                        endif;

                                                        /*extra store info*/
                                                        if( !empty( $invoicedatas->invoice_extra_info ) ):
                                                            echo '<b>'.esc_html( 'GST', 'tmdpos' ).':</b>&nbsp;'.esc_html( $invoicedatas->invoice_extra_info ).'<br>';
                                                        endif;
                                                    ?>
                                                </section>
                                                <section style="width: 50%; padding: 10px; border: 1px solid #c5c2c2; letter-spacing: 0.7px; line-height: 1.2;">
                                                    <?php
                                                        $order_status = $order->get_status(); // Get the order status 
                                                        $currency     = $order->get_currency(); // Get the currency used  
                                                        $date_created = $order->get_date_created();
                                                        $order_date   = $date_created->date('Y-m-d h:i:sa');

                                                        if( empty( $invoicedatas->show_order_date ) ): 
                                                            echo '<b>'.esc_html( 'Date Added', 'tmdpos' ).':</b>&nbsp;'.esc_html( $order_date ).'<br>';
                                                        endif;

                                                        if( empty( $invoicedatas->show_invoice_number ) ): 
                                                            echo '<b>'.esc_html( 'Invoice No.', 'tmdpos' ).':</b>&nbsp;'.esc_html( $pos_order_id ).'<br>';
                                                        endif;

                                                            echo '<b>'.esc_html( 'Order Status', 'tmdpos' ).':</b>&nbsp;'.esc_html( $order_status ).'<br>';

                                                        if( empty( $invoicedatas->show_Payment_mode ) ):
                                                            echo '<b>'.esc_html( 'Payment Method', 'tmdpos' ).':</b>&nbsp;'.esc_html( $order_meta_datas->payment_method ).'<br>';
                                                        endif;

                                                        if( empty( $invoicedatas->show_cashier_name ) ):
                                                            echo '<b>'.esc_html( 'Cashier', 'tmdpos' ).':</b>&nbsp;'.esc_html( $order_meta_datas->cashier ).'<br>';
                                                        endif;
                                                            
                                                        if( empty( $invoicedatas->show_customer_name ) ):
                                                            if( !empty( $order_meta_datas->existing_customer ) ){
                                                                echo '<b>'.esc_html( 'Customer', 'tmdpos' ).':</b>&nbsp;'.esc_html( $order_meta_datas->existing_customer ).'<br>';
                                                            } 
                                                            else {
                                                                echo '<b>'.esc_html( 'Customer', 'tmdpos' ).':</b>&nbsp;'. esc_html( $order_meta_datas->tmd_pos_customer ).'<br>';
                                                            }
                                                        endif;
                                                    ?>
                                                </section>
                                            </td>
                                        </tr>
                                
                                        <tr>
                                            <td style="display: flex;">
                                                <?php if( empty( $invoicedatas->show_shipping_address ) ): ?>
                                                    <?php if( !empty($order_meta_datas->shipping_cost) ): ?>
                                                        <section style="border: 1px solid #c5c2c2; width: 50%; padding: 10px;">
                                                            <label><strong><?php esc_html_e( 'Shipping address :', 'tmdpos' ); ?></strong><br></label>
                                                            <?php
                                                                $customer = new WP_User( $order_meta_datas->shop_customer );
                                                                $customer_phone = get_user_meta( $order_meta_datas->shop_customer, 'billing_phone', true);
                                                                // Customer shipping information details
                                                                echo '<strong>'.esc_html( $customer->shipping_first_name ).'</strong><br>';
                                                                echo '<strong>'.esc_html( 'Phone :', 'tmdpos' ).'</strong>'.esc_html( $customer_phone );
                                                                echo esc_html( $customer->shipping_company ).'<br>';
                                                                echo esc_html( $customer->shipping_address_1 ).'<br>';
                                                                echo esc_html( $customer->shipping_address_2 ).'<br>';
                                                                echo esc_html( $customer->shipping_city ).'<br>';
                                                                echo esc_html( $customer->shipping_state ).'<br>';
                                                                echo esc_html( $customer->shipping_postcode ).'<br>';
                                                                echo esc_html( $customer->shipping_country ).'<br>';
                                                                echo esc_html( $order->get_customer_note() );
                                                            ?>
                                                        </section>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table style="width: 100%; border-collapse: collapse; margin-top: 30px;">

                                    <thead style="text-align: left;">
                                        <tr>
                                            <th style="border: 1px solid #c5c2c2; padding: 10px; width: 40%;"><?php esc_html_e( 'Product', 'tmdpos' ); ?></th>
                                            <th style="border: 1px solid #c5c2c2; padding: 10px; width: 20%;"><?php esc_html_e( 'Category', 'tmdpos' ); ?></th>
                                            <th style="border: 1px solid #c5c2c2; padding: 10px; width: 20%;"><?php esc_html_e( 'Quantity', 'tmdpos' ); ?></th>
                                            <th style="border: 1px solid #c5c2c2; padding: 10px; width: 20%;"><?php esc_html_e( 'Price', 'tmdpos' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody style="text-align: left;">
                                        <?php  

                                            // Iterating through each WC_Order_Item_Product objects
                                            foreach ($order->get_items() as $item_key => $item ):
                                                $item_id       = $item->get_id();
                                                $product       = $item->get_product(); // Get the WC_Product object
                                                $product_price = $product->get_price();
                                                $product_id    = $item->get_product_id(); // the Product id
                                                $variation_id  = $item->get_variation_id(); // the Variation id
                                                $terms         = get_the_terms( $product_id, 'product_cat' );

                                                /*product category*/
                                                foreach ($terms as  $value) {
                                                    $product_cats = $value->name;
                                                }

                                                $item_name      = $item->get_name(); // Name of the product
                                                $quantity       = $item->get_quantity();  
                                                $line_subtotal  = wc_format_decimal( $item->get_subtotal(), 2); // Line subtotal (non discounted)
                                                $line_total_tax = $item->get_total_tax(); // Line total tax (discounted)
                                                $currency       = get_woocommerce_currency_symbol();
                                                
                                                ?>
                                                    <tr>
                                                        <td style="border: 1px solid #c5c2c2; padding: 10px; width: 40%;"><?php echo esc_html( $item_name ); ?></td>
                                                        <td style="border: 1px solid #c5c2c2; padding: 10px; width: 20%;"><?php echo esc_html( $product_cats ); ?></td>
                                                        <td style="border: 1px solid #c5c2c2; padding: 10px; width: 20%;"><?php echo esc_html( $quantity ); ?></td>
                                                        <td style="border: 1px solid #c5c2c2; padding: 10px; width: 20%;"><?php echo esc_html( $currency.($product_price * $quantity ) ); ?></td>
                                                    </tr>
                                                <?php
                                            endforeach;
                                            /*get order detial form wc end*/
                                        ?>
                                    </tbody>
                                </table>
                                <!-- body end-->

                                <table style="width: 100%; margin: 30px auto; border-collapse: collapse; ">
                                    <!-- footer -->
                                    <tr>
                                        <th style="text-align: right; border: 1px solid #c5c2c2; padding: 5px; width:80%;"><?php esc_html_e( 'Subtotal', 'tmdpos' ); ?></th>
                                        <td style="border: 1px solid #c5c2c2; padding: 5px; width: 20%;"><?php echo esc_html( $currency.$order_meta_datas->_subtotal ); ?></td>
                                    </tr>

                                    <?php if( !empty( $order_meta_datas->shipping_cost ) ): ?>
                                        <tr>
                                            <th style="text-align: right; border: 1px solid #c5c2c2; padding: 5px; width:80%;"><?php esc_html_e( 'Shipping', 'tmdpos' ); ?></th>
                                            <td style="border: 1px solid #c5c2c2; padding: 5px; width: 20%;"><?php echo esc_html( $currency.$order_meta_datas->shipping_cost ); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    
                                    <?php if( !empty( $order_meta_datas->discount ) ): ?>
                                        <tr>
                                            <th style="text-align: right; border: 1px solid #c5c2c2; padding: 5px; width:80%;"><?php esc_html_e( 'Discount', 'tmdpos' ); ?></th>
                                            <td style="border: 1px solid #c5c2c2; padding: 5px; width: 20%;"><?php echo $currency.$order_meta_datas->discount; ?></td>
                                        </tr>
                                    <?php endif; ?>

                                    <?php if( !empty( $order_meta_datas->coupon_amount ) ): ?>
                                        <tr>
                                            <th style="text-align: right; border: 1px solid #c5c2c2; padding: 5px; width:80%;"><?php esc_html_e('Coupon','tmdpos'); ?></th>
                                            <td style="border: 1px solid #c5c2c2; padding: 5px; width: 20%;"><?php echo '-'.esc_html( $currency.$order_meta_datas->coupon_amount ); ?></td>
                                        </tr>
                                    <?php endif; ?>

                                    <tr>
                                        <th style="text-align: right; border: 1px solid #c5c2c2; padding: 5px; width:80%;"><?php esc_html_e( 'Tax', 'tmdpos' ); ?></th>   
                                        <td style="border: 1px solid #c5c2c2; padding: 5px; width: 20%;"><?php echo esc_html( $currency.$order_meta_datas->tax_total ); ?></td>
                                    </tr>

                                    <tr>
                                        <th style="text-align: right; border: 1px solid #c5c2c2; padding: 5px; width:80%;"><?php esc_html_e( 'Total', 'tmdpos' ); ?></th>
                                        <td style="border: 1px solid #c5c2c2; padding: 5px; width: 20%;"><?php echo esc_html( $currency.$order_meta_datas->wt_dis_total ); ?></td>
                                    </tr>
                                    <?php $change = !empty( $order_meta_datas->change ) ? $order_meta_datas->change : 0; ?>

                                    <?php if( empty( $invoicedatas->show_changes ) ): ?>
                                        <tr>
                                            <th style="text-align: right; border: 1px solid #c5c2c2; padding: 5px; width:80%;"><?php esc_html_e( 'Change', 'tmdpos' ); ?></th>
                                            <td style="border: 1px solid #c5c2c2; padding: 5px; width: 20%;"><?php echo esc_html( $currency.$change ) ; ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <!-- footer -->
                                </table>
                                <p><?php echo esc_html( $invoicedatas->invoice_thanks_msg ); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if( $invoicedatas->reciept_formate == 'Reciept Print' ): ?>
                            <!-- receipt bill  hmtl start-->
                            <style>
                                #invoice-tmdpos{
                                    box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5);
                                    padding:2mm;
                                    margin: 0 auto;
                                    width: <?php echo esc_html( $invoicedatas->reciept_width ); ?>mm;
                                    background: #FFF;  
                                }

                                #invoice-tmdpos ::selection {background: #f31544; color: #FFF;}
                                #invoice-tmdpos ::moz-selection {background: #f31544; color: #FFF;}
                                #invoice-tmdpos h1{
                                    font-size: 1.5em;
                                    color: #222;
                                }

                                #invoice-tmdpos h2{
                                    font-size: .9em;
                                }

                                #invoice-tmdpos h3{
                                    font-size: 1.2em;
                                    font-weight: 300;
                                    line-height: 2em;
                                }

                                #invoice-tmdpos p{
                                    font-size: .7em;
                                    color: #666;
                                    line-height: 1.2em;
                                }
                                
                                #invoice-tmdpos #top, #mid,#bot{ /* Targets all id with 'col-' */
                                    border-bottom: 1px solid #EEE;
                                }

                                #invoice-tmdpos #top{ min-height: 100px; }
                                #invoice-tmdpos #mid{ min-height: 80px; } 
                                #invoice-tmdpos #bot{ min-height: 50px; }

                                #invoice-tmdpos #top .logo{
                                    height: <?php echo esc_html( $invoicedatas->logo_size_height ); ?>;
                                    width: <?php echo esc_html( $invoicedatas->logo_size_width ); ?>;
                                    <?php if( empty( $invoicedatas->show_logo ) ): ?>
                                        background: url(<?php echo esc_url( $invoicedatas->reciept_logo ); ?>) no-repeat;
                                    <?php endif; ?>
                                    background-size: <?php echo esc_html( $invoicedatas->logo_size_height ); ?>px <?php echo esc_html( $invoicedatas->logo_size_width ); ?>px;
                                    -webkit-print-color-adjust: exact;
                                }
                                #invoice-tmdpos .info{
                                    display: block;
                                    margin-left: 0;
                                }
                                #invoice-tmdpos .title{
                                    float: right;
                                }
                                #invoice-tmdpos .title p{
                                    text-align: right;
                                } 
                                #invoice-tmdpos table{
                                    width: 100%;
                                    border-collapse: collapse;
                                }
                                #invoice-tmdpos td{
                                    padding: 5px 0 5px 5px;
                                }
                                #invoice-tmdpos .tabletitle{
                                    padding: 5px;
                                    font-size: .5em;
                                    background: #EEE;
                                }
                                #invoice-tmdpos .service{border-bottom: 1px solid #EEE;}
                                #invoice-tmdpos .item{width: 24mm;}
                                #invoice-tmdpos .itemtext{font-size: .5em;}

                                #invoice-tmdpos #legalcopy{
                                    margin-top: 5mm;
                                }
                            </style>
                            
                            <?php    
                                $user    = wp_get_current_user(); // The current user
                                $country = $user->billing_country;
                                $state   = $user->billing_state;

                                // The main address pieces:
                                $store_address     = get_option( 'woocommerce_store_address' );
                                $store_address_2   = get_option( 'woocommerce_store_address_2' );
                                $store_city        = get_option( 'woocommerce_store_city' );
                                $store_postcode    = get_option( 'woocommerce_store_postcode' );

                                // The country/state
                                $store_raw_country = get_option( 'woocommerce_default_country' );

                                // Split the country/state
                                $split_country     = explode( ":", $store_raw_country );

                                // Country and state separated:
                                $store_country = $split_country[0];
                                $store_state   = WC()->countries->get_states( $country )[$state];

                                /*get order detial form wc */
                                $order         = wc_get_order( $pos_order_id );
                                $order_status  = $order->get_status(); // Get the order status 
                                $currency      = $order->get_currency(); // Get the currency used  
                                $date_created  = $order->get_date_paid();
                                $order_date    = $date_created->date('Y-m-d h:i:sa');
                            ?>
                            
                            <div id="invoice-tmdpos">
                                
                                <center id="top">
                                    <div class="logo"></div>
                                    <div class="info"> 
                                        <?php if( empty( $invoicedatas->show_store_name ) ): ?>
                                            <h2><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h2>
                                        <?php endif; ?>
                                    </div><!--End Info-->
                                </center><!--End InvoiceTop-->
                                    
                                <div id="mid">
                                    <div class="info">
                                        <h2><?php esc_html_e( 'Contact Info', 'tmdpos' ); ?></h2>
                                        <p style="letter-spacing: 0.7px; line-height: 1.2;"> 
                                            <?php
                                                if( empty( $invoicedatas->show_store_address ) ): 

                                                    echo '<b>'.esc_html('Address 1:', 'tmdpos').'</b>&nbsp;'. esc_attr( $store_address ). "<br>";
                                                    echo ( $store_address_2 ) ? '<b>'.esc_html('Address 2:', 'tmdpos' ).'</b>&nbsp;'. esc_attr( $store_address_2 ) . "<br>" : '';
                                                    echo '<b>'.esc_html('Store Location:', 'tmdpos' ).'</b>&nbsp;'. esc_html( $store_city ).', '.esc_html( $store_state ) . ' ' . esc_html( $store_postcode ). "<br>";
                                                    echo esc_html( $store_country ).'<br>';
                                                endif;
                                                
                                                /*extra store info*/
                                                if( !empty( $invoicedatas->invoice_extra_info ) ):
                                                    echo '<b>'.esc_html( 'GST', 'tmdpos' ).':</b>&nbsp;'. esc_html( $invoicedatas->invoice_extra_info ).'<br>';
                                                endif;
                                            ?>
                                        </p>
                                    </div>
                                </div>

                                <div id="mid">
                                    <div class="info">
                                        <h2><?php esc_html_e( 'Order details', 'tmdpos' ); ?></h2>
                                        <p style="letter-spacing: 0.7px; line-height: 1.2;">
                                            <?php 
                                                if( empty( $invoicedatas->show_order_date ) ):
                                                    echo '<b>'.esc_html( 'Date Added', 'tmdpos' ).':</b>&nbsp;'. esc_html( $order_date ).'<br>';
                                                endif;

                                                if( empty( $invoicedatas->show_invoice_number ) ):
                                                    echo '<b>'.esc_html( 'Invoice No.', 'tmdpos' ).':</b>&nbsp;'. esc_html( $pos_order_id ).'<br>';
                                                endif;

                                                echo '<b>'.esc_html( 'Order Status', 'tmdpos' ).':</b>&nbsp;'.esc_html( $order_status ).'<br>';

                                                if( empty( $invoicedatas->show_Payment_mode ) ):
                                                    echo '<b>'.esc_html( 'Payment Method', 'tmdpos' ).':</b>&nbsp;'. esc_html( $order_meta_datas->payment_method ).'<br>';
                                                endif;

                                                if( empty( $invoicedatas->show_cashier_name ) ):
                                                    echo '<b>'.esc_html( 'Cashier', 'tmdpos' ).':</b>&nbsp;'. esc_html( $order_meta_datas->cashier ).'<br>';
                                                endif;
                                                
                                                if( empty( $invoicedatas->show_customer_name ) ):   
                                                    if( !empty( $order_meta_datas->existing_customer ) ){

                                                        echo '<b>'.esc_html( 'Customer', 'tmdpos' ).':</b>&nbsp;'. esc_html( $order_meta_datas->existing_customer ).'<br>';
                                                    } 
                                                    else {
                                                        echo '<b>'.esc_html( 'Customer', 'tmdpos' ).':</b>&nbsp;'. esc_html( $order_meta_datas->tmd_pos_customer ).'<br>';
                                                    }
                                                endif;
                                            ?>
                                        </p>
                                    </div>
                                </div>

                                <?php  if( empty( $invoicedatas->show_shipping_address ) ): ?>
                                    <?php if( !empty($order_meta_datas->shipping_cost) ): ?>
                                        <div id="mid">
                                            <div class="info">
                                            <h2><?php esc_html_e( 'Shipping Address', 'tmdpos' ); ?></h2>
                                                <p>
                                                    <?php
                                                        $customer = new WP_User( $order_meta_datas->shop_customer );
                                                        $customer_phone = get_user_meta( $order_meta_datas->shop_customer, 'billing_phone', true);
                                                        // Customer shipping information details
                                                        echo '<strong>'.esc_html( $customer->shipping_first_name ).'</strong><br>';
                                                        echo '<strong>'.esc_html('Phone :', 'tmdpos' ). esc_html( $customer_phone ).'</strong>';
                                                        echo esc_html( $customer->shipping_company ).'<br>';
                                                        echo esc_html( $customer->shipping_address_1 ).'<br>';
                                                        echo esc_html( $customer->shipping_address_2 ).'<br>';
                                                        echo esc_html( $customer->shipping_city ).'<br>';
                                                        echo esc_html( $customer->shipping_state ).'<br>';
                                                        echo esc_html( $customer->shipping_postcode ).'<br>';
                                                        echo esc_html( $customer->shipping_country ).'<br>';
                                                        echo esc_html( $order->get_customer_note() );
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <!--End Invoice Mid-->
                                <div id="bot">
                                    <div id="table">
                                        <table>
                                            <tr class="tabletitle">
                                                <td class="item"><h2><?php esc_html_e( 'Items', 'tmdpos' ); ?></h2></td>
                                                <td class="Hours"><h2><?php esc_html_e( 'Qty', 'tmdpos' ); ?></h2></td>
                                                <td class="Rate"><h2><?php esc_html_e( 'Sub Total', 'tmdpos' ); ?></h2></td>
                                            </tr>
                                            <?php 
                                                //order details 
                                                foreach ($order->get_items() as $item_key => $item ):
                                                    $item_id       = $item->get_id();
                                                    $product       = $item->get_product(); // Get the WC_Product object
                                                    $product_price = $product->get_price();
                                                    $product_id    = $item->get_product_id(); // the Product id
                                                    $variation_id  = $item->get_variation_id(); // the Variation id
                                                    $terms         = get_the_terms( $product_id, 'product_cat' );

                                                    foreach ($terms as  $value) {
                                                        $product_cats = $value->name;
                                                    }

                                                    $item_name      = $item->get_name(); // Name of the product
                                                    $quantity       = $item->get_quantity();  
                                                    $line_subtotal  = wc_format_decimal( $item->get_subtotal(), 2); // Line subtotal (non discounted)
                                                    $line_total_tax = $item->get_total_tax(); // Line total tax (discounted)
                                                    $currency       = get_woocommerce_currency_symbol();

                                                    ?>
                                                        <tr class="service">
                                                            <td class="tableitem"><p class="itemtext"><?php echo esc_html( $item_name ); ?></p></td>
                                                            <td class="tableitem"><p class="itemtext"><?php echo esc_html( $quantity ); ?></p></td>
                                                            <td class="tableitem"><p class="itemtext"><?php echo esc_html( $currency.$line_subtotal ); ?></p></td>
                                                        </tr>
                                                    <?php 
                                                endforeach; 
                                            ?>

                                            <tr class="tabletitle">
                                                <td></td>
                                                <td class="Rate"><h2><?php esc_html_e( 'Subtotal', 'tmdpos' ); ?></h2></td>
                                                <td class="payment"><h2><?php echo esc_html( $currency.$order_meta_datas->_subtotal ); ?></h2></td>
                                            </tr>

                                            <?php if( !empty( $order_meta_datas->shipping_cost ) ): ?>
                                                <tr class="tabletitle">
                                                    <td></td>
                                                    <td class="Rate"><h2><?php esc_html_e( 'Shipping', 'tmdpos' ); ?></h2></td>
                                                    <td class="payment"><h2><?php echo esc_html( $currency.$order_meta_datas->shipping_cost ); ?></h2></td>
                                                </tr>
                                            <?php endif;?>

                                            <?php if( !empty( $order_meta_datas->discount ) ): ?>
                                                <tr class="tabletitle">
                                                    <td></td>
                                                    <td class="Rate"><h2><?php esc_html_e( 'Discount', 'tmdpos' ); ?></h2></td>
                                                    <td class="payment"><h2><?php echo esc_html( $currency.$order_meta_datas->discount ); ?></h2></td>
                                                </tr>
                                            <?php endif; ?>

                                            <tr class="tabletitle">
                                                <td></td>
                                                <td class="Rate"><h2><?php esc_html_e( 'Tax', 'tmdpos' ); ?></h2></td>
                                                <td class="payment"><h2><?php echo esc_html( $currency.$order_meta_datas->tax_total ); ?></h2></td>
                                            </tr>

                                            <?php if( !empty( $order_meta_datas->coupon_amount ) ): ?>
                                                <tr class="tabletitle">
                                                    <td></td>
                                                    <td class="Rate"><h2><?php esc_html_e( 'Coupon', 'tmdpos' ); ?></h2></td>
                                                    <td class="payment"><h2><?php echo '-'. esc_html( $currency.$order_meta_datas->coupon_amount ); ?></h2></td>
                                                </tr>
                                            <?php endif; ?>

                                            <tr class="tabletitle">
                                                <td></td>
                                                <td class="Rate"><h2><?php esc_html_e( 'Totals', 'tmdpos' ); ?></h2></td>
                                                <td class="payment"><h2><?php echo esc_html( $currency.$order_meta_datas->wt_dis_total ); ?></h2></td>
                                            </tr>
                                            <?php $change = !empty( $order_meta_datas->change ) ? $order_meta_datas->change : 0.00; ?>
                                            <?php if( empty( $invoicedatas->show_changes ) ): ?>
                                                <tr class="tabletitle">
                                                    <td></td>
                                                    <td class="Rate"><h2><?php esc_html_e( 'Change', 'tmdpos' ); ?></h2></td>
                                                    <td class="payment"><h2><?php echo esc_html( $currency.$change ); ?></h2></td>
                                                </tr>
                                            <?php endif; ?>
                                        </table>
                                    </div><!--End Table-->

                                    <div id="legalcopy"><p class="legal"><?php echo esc_html( $invoicedatas->invoice_thanks_msg ); ?></p></div>
                                </div><!--End InvoiceBot-->
                            </div><!--End Invoice-->
                        <?php endif; ?>
                    </div><!-- receipt bill  html end-->
                <?php
            }
            else{
                ?><p class="print_order_message"><?php esc_html_e('Order Is Not Completed Please Complete Order First To Print Order Receipt.','tmdpos'); ?></p><?php 
            }
        endif;

        header("Content-type: application/json; charset=utf-8"); 
        die();
    }
    // order invoice print from list end    
}

if( !function_exists( 'tmdpos_stock_in' ) ){
    // tmd pos stock in 
    add_action( 'wp_ajax_tmdpos_stock_in', 'tmdpos_stock_in');
    add_action( 'wp_ajax_nopriv_tmdpos_stock_in', 'tmdpos_stock_in');
    function tmdpos_stock_in(){
        global $wpdb, $product;
        $product_sku = sanitize_text_field( $_POST['product_sku'] );
        $product_id  = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $product_sku ) );
        $product     = wc_get_product( $product_id );
        ?>
            <div class="tmdpos_stock_in_popup">
                <div><span class="tmd_stock_in_close">&#10006;</span></div>
                <div class="stock_in_success_msg"></div>
                <?php 
                    if( !empty( $product_id ) ){ 
                        ?>
                            <table style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th class="tmd_stock_in_th"><?php esc_html_e( 'Product Name', 'tmdpos'); ?></th>
                                        <th class="tmd_stock_in_th"><?php esc_html_e( 'Quantity', 'tmdpos'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <?php echo esc_html( $product->get_title() ); ?>
                                            <input type="hidden" class="product_id" name="product_id" value="<?php echo esc_attr( $product_id ); ?>" />
                                        </td>
                                        <td>
                                            <input type="number" name="product_qty" class="product_qty" value="<?php echo esc_attr( $product->get_stock_quantity() ); ?>" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p><button class="button-primary tmd_stock_in_btn"><?php esc_html_e( 'Update', 'tmdpos'); ?></button></p>
                        <?php 
                    } 
                    else{
                        ?><p class="data_not_found" ><?php esc_html_e( 'No Product Found', 'tmdpos' ); ?></p><?php 
                    }
                ?>
            </div>
        <?php
        header("Content-type: application/json; charset=utf-8");   
        die();
    }  
}

if( !function_exists( 'tmdpos_stock_update' ) ){
    // tmd pos update stock qty 
    add_action( 'wp_ajax_tmdpos_stock_update', 'tmdpos_stock_update');
    add_action( 'wp_ajax_nopriv_tmdpos_stock_update', 'tmdpos_stock_update');
    function tmdpos_stock_update(){
        global $wpdb;
        $json        = array();
        $product_id  = absint( sanitize_text_field( $_POST['product_id'] ) );
        $product_qty = absint( sanitize_text_field( $_POST['product_qty'] ) );
        update_post_meta($product_id, '_stock', $product_qty);

        $json = '<p>'.esc_html(__( 'Stock In Successfully', 'tmdpos' ) ).'</p>';
        header("Content-type: application/json; charset=utf-8");
        echo wp_json_encode($json);     
        die();  
    }    
}

if( !function_exists( 'tmdpos_apply_coupon' ) ){
    // apply coupon to order
    add_action( 'wp_ajax_tmdpos_apply_coupon', 'tmdpos_apply_coupon');
    add_action( 'wp_ajax_nopriv_tmdpos_apply_coupon', 'tmdpos_apply_coupon');
    function tmdpos_apply_coupon(){
        global $wp_session, $wpdb, $product, $woocommerce;

        $json           = array();
        $tablename      = $wpdb->prefix . 'options';
        $query          = $wpdb->prepare( "SELECT * FROM $tablename WHERE `option_name` = %s", 'woocommerce_tax_display_cart' );
        $options_data   = $wpdb->get_row( $query );
        $price_incl_tax = $options_data->option_value; /*cart page price including or excluding tax*/
        $coupon_code    = sanitize_text_field( $_POST['coupon_code'] );
        $cart_subtotal  = wc_format_decimal( $_POST['cart_subtotal'], 2 );
        $cart_tax       = wc_format_decimal( $_POST['cart_tax'], 2 );
        $subtotal       = $cart_subtotal + $cart_tax;

        $coupon         = new WC_Coupon($coupon_code);
        $coupon_amount  = $coupon->get_amount(); 
        $coupon_type    = $coupon->get_discount_type(); 
        $min_spend      = $coupon->get_minimum_amount(); 
        $max_spend      = $coupon->get_maximum_amount();
        $wc_cucurrency_symbol = get_woocommerce_currency_symbol();
        $notindividual_coupon =  array_merge( $coupon->get_product_ids(), $coupon->get_product_categories() );

        if( !empty( $notindividual_coupon ) ){ #check if coupon is selected for product or catogary 

            if( !empty( $coupon_amount ) ){
                if( $price_incl_tax == 'incl' ){ 
                    $tmd_subtotal = $subtotal; 
                }
                elseif($price_incl_tax == 'excl'){
                    $tmd_subtotal = $cart_subtotal; 
                }

                if( empty($max_spend) || $tmd_subtotal < $max_spend ){
                    if( $tmd_subtotal >  $min_spend ) {
                        $coupon_data = array(
                            'id'             => $coupon->get_id(),
                            'code'           => esc_attr($coupon_code),
                            'type'           => $coupon->get_discount_type(),
                            'amount'         => wc_format_decimal($coupon->get_amount(), 2),
                            'individual_use' => ( 'yes' === $coupon->get_individual_use() ),
                            'usage_limit'    => (!empty($coupon->get_usage_limit()) ) ? $coupon->get_usage_limit() : null,
                            'expiry_date'    => (!empty($coupon->get_date_expires()->date('Y-m-d') ) ) ?  $coupon->get_date_expires()->date('Y-m-d')  : null,
                            'usage_count'    => (int) $coupon->get_usage_count(),
                            'minimum_amount' => wc_format_decimal($coupon->get_minimum_amount(), 2),
                            'maximum_amount' => wc_format_decimal($coupon->get_maximum_amount(), 2),
                        );

                        $now_datetime    = new WC_DateTime();
                        $usage_left      = $coupon_data['usage_limit'] - $coupon_data['usage_count'];
                        if( $now_datetime <  $coupon->get_date_expires() ){
                            if ( empty($coupon->get_usage_limit()) || $usage_left > 0 ) {

                                if( $coupon_type == 'fixed_cart' ){
                                    $json['data1'] = '<span class="tax_label tmd_coupon_value" data-type="fixed" data-amount="'.esc_html( $coupon_amount ).'" >'.esc_html('Coupon(Fixed-'.$coupon_amount.')').'</span> <span data-coupon="'.esc_html( $coupon_code ).'" class="tmd_float_rigth tmd_coupon_type">'.esc_html( $wc_cucurrency_symbol.' -'.$coupon_amount ).'</span>';
                                    $_SESSION['coupon_amount'] = $coupon_amount;
                                } 
                                if( $coupon_type == 'percent' ){

                                    if( $price_incl_tax == 'incl' ){
                                        $cp_amount =  ( $coupon_amount / 100 ) * $subtotal;
                                    }
                                    elseif( $price_incl_tax == 'excl' ){
                                        $cp_amount =  ( $coupon_amount / 100 ) * $cart_subtotal;
                                    }

                                    $json['data2'] = '<span class="tax_label tmd_coupon_value" data-type="percent" data-amount="'.esc_html( $cp_amount ).'">'.esc_html('Coupon('.$coupon_amount.'%)').'</span> <span data-coupon="'.esc_html( $coupon_code ).'" class="tmd_float_rigth tmd_coupon_type">'.esc_html( $wc_cucurrency_symbol.' -' .wc_format_decimal( $cp_amount, 2 ) ) .'</span>';
                                    $_SESSION['coupon_amount'] = $cp_amount;
                                }
                                if( $coupon_type == 'fixed_product' ){

                                    $json['message1'] = '<p class="error_coupon_message">'.esc_html(__( 'Unable to use this coupon code, Please try another coupon code.', 'tmdpos') ).'</p>';
                                }
                            }
                            else{
                                $json['message2'] = '<p>'.esc_html(__( 'Please use Valid Coupon Code', 'tmdpos') ).'</p>';
                            }
                        } 
                        else{
                            $json['message2'] = '<p>'.esc_html(__( 'Please use Valid Coupon Code', 'tmdpos' ) ).'</p>';
                        }
                    }
                    else{
                        $json['message2'] = '<p>'.esc_html( 'Please Spend Min '.$min_spend ).'</p>';
                    }
                }
                else{
                    $json['message2'] = '<p>'.esc_html('Please Spend Max '.$max_spend).'</p>';
                }
            }
            else{
                $json['message2'] = '<p>'.esc_html(__( 'Please use Valid Coupon Code', 'tmdpos' ) ).'</p>';
            }
        }
        else{
            $json['message2'] = '<p>'.esc_html(__( 'This Coupon is not valid for POS use', 'tmdpos' ) ).'</p>';
        }
        if( empty( $json['message1'] ||  $json['message2'] ) ){
            $_SESSION['tmd_coupon'] = $json;
        }

        header("Content-type: application/json; charset=utf-8");
        if( empty( $json['message1'] ||  $json['message2'] )){
            echo wp_json_encode($_SESSION);     
        }
        else{
            echo wp_json_encode($json); 
        }
        die();  
    }    
}

if( !function_exists( 'tmdpos_hold_order' ) ){
    // hold order function 
    add_action( 'wp_ajax_tmdpos_hold_order', 'tmdpos_hold_order');
    add_action( 'wp_ajax_nopriv_tmdpos_hold_order', 'tmdpos_hold_order');
    function tmdpos_hold_order(){
        global $wpdb, $wp_session;
        $json         = array();
        $table_option = $wpdb->prefix . 'tmd_pos_option';
        $hold_note    = sanitize_text_field( $_POST['hold_note'] ); /*order hold note */
        $cart_items   = !empty( $_SESSION['pos_items'] ) ? $_SESSION['pos_items'] : false; 

        if( !empty($cart_items) ){
            $hold_order      = array( 'hold_note' => $hold_note );
            $cart_data       = array_merge($hold_order, $cart_items);
            $order_hold_data = array('option_value' => wp_json_encode($cart_data));
            $wpdb->insert($table_option, $order_hold_data);
            unset( $_SESSION['pos_items'] );
            unset( $_SESSION['tmd_coupon'] );
            unset( $_SESSION['coupon_amount'] );

            $json = '<p><span style="color:green; text-align:center;">'.esc_html( __( 'Order hold successfully !', 'tmdpos' ) ).'</span></p>';

        } 
        else {
            $json = '<p><span style="color:red; text-align:center;">'.esc_html( __( 'There is an error to hold order.', 'tmdpos' ) ).'</span></p>';
        }
        header("Content-type: application/json; charset=utf-8");
        echo wp_json_encode($json);     
        die(); 
    }    
}

if( !function_exists( 'tmdpos_hold_order_to_cart' ) ){
    // hold order add to cart
    add_action( 'wp_ajax_tmdpos_hold_order_to_cart', 'tmdpos_hold_order_to_cart');
    add_action( 'wp_ajax_nopriv_tmdpos_hold_order_to_cart', 'tmdpos_hold_order_to_cart');
    function tmdpos_hold_order_to_cart(){
        global $wp_session, $wpdb;
        $json             = array();
        $hold_order_id    = absint( sanitize_text_field( $_POST['hold_order_id'] ) );
        $table_option     = $wpdb->prefix.'tmd_pos_option';
        $query            = $wpdb->prepare( "SELECT * FROM $table_option WHERE `tmd_option_id` = %d", $hold_order_id );
        $hold_order_datas = $wpdb->get_row( $query );
        $holdorder_value  = json_decode($hold_order_datas->option_value);

        foreach ($holdorder_value as $order_value) {
            if( !empty( $order_value->product_id ) ){
                $json = array(
                    'product_id'    => $order_value->product_id,
                    'product_name'  => $order_value->product_name,
                    'product_price' => $order_value->product_price,
                    'product_qty'   => $order_value->product_qty,
                    'product_cost'  => $order_value->product_cost,
                    'product_tax'   => $order_value->product_tax,
                    'currency'      => $order_value->currency,
                );
                $_SESSION['pos_items'][$order_value->product_id] = $json;
            }
            if( !empty( $order_value->product_id ) ){
                $wpdb->delete( $table_option, array( 'tmd_option_id' => $hold_order_id ) );
            }   
        }
        header("Content-type: application/json; charset=utf-8");
        echo wp_json_encode($_SESSION);     
        die();
    }    
}

if( !function_exists( 'tmdpos_sale_report_print' ) ){
    // tmd pos sale report print
    add_action( 'wp_ajax_tmdpos_sale_report_print', 'tmdpos_sale_report_print');
    add_action( 'wp_ajax_nopriv_tmdpos_sale_report_print', 'tmdpos_sale_report_print');
    function tmdpos_sale_report_print(){
        global $wpdb, $woocommerce;

        $table_order = $wpdb->prefix . 'tmd_pos_order';
        $order_datas = $wpdb->get_results( "SELECT * FROM $table_order" );
        ?>
            <table id="tmd_pos_sale_report_tbl">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Date', 'tmdpos' ); ?></th>
                        <th><?php esc_html_e( 'Order ID', 'tmdpos' ); ?></th>
                        <th><?php esc_html_e( 'Payment Mode', 'tmdpos' ); ?></th>
                        <th><?php esc_html_e( 'VAT', 'tmdpos' ); ?></th>
                        <th><?php esc_html_e( 'Total', 'tmdpos' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $grand_tax_total = $grand_order_total = 0;
                        $ccurrency       = get_woocommerce_currency_symbol();

                        foreach( $order_datas as $order_data ){ 
                            $order_details = json_decode($order_data->order_value);
                            $tax_total     = !empty($order_details->tax_total) ? $order_details->tax_total : 0;
                            $order_total   = !empty($order_details->order_total) ? $order_details->order_total : 0;

                            $grand_tax_total   += $tax_total;
                            $grand_order_total += $order_total;
                            ?>
                                <tr>
                                    <td><?php echo esc_html( date('Y-m-d', strtotime( $order_data->order_date ) ) ); ?></td>
                                    <td><?php echo '#'.esc_html( $order_data->order_meta ); ?></td>
                                    <td><?php echo esc_html( $order_details->payment_method ); ?></td>
                                    <td><?php echo esc_html( $ccurrency.$order_details->tax_total ); ?></td>
                                    <td><?php echo esc_html( $ccurrency.$order_details->order_total ); ?></td>
                                </tr>
                            <?php 
                        }  
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th></th>
                        <th></th>
                        <th><?php esc_html_e( 'Grand Total', 'tmdpos' ); ?></th>
                        <th><?php echo esc_html( $ccurrency.$grand_tax_total ); ?></th>
                        <th><?php echo esc_html( $ccurrency.$grand_order_total ); ?></th>
                    </tr>
                </tfoot>
            </table>
        <?php
        header("Content-type: application/json; charset=utf-8");
        die();
    }    
}


if( !function_exists( 'tmdpos_product_filter_by_name_sku' ) ){
    // tmd pos product filter by product name and product sku
    add_action( 'wp_ajax_tmdpos_product_filter_by_name_sku', 'tmdpos_product_filter_by_name_sku');
    add_action( 'wp_ajax_nopriv_tmdpos_product_filter_by_name_sku', 'tmdpos_product_filter_by_name_sku');
    function tmdpos_product_filter_by_name_sku(){
        global $wpdb, $post, $woocommerce;

        $table_name    = $wpdb->prefix . 'posts';
        $product_title = sanitize_text_field( $_POST['product_title'] );
        $tmd_empty_img = TMDPOS_IMAGE_PATH. 'company_logo.png';
        $product_id    = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $product_title ) );

        if( !empty( $product_id ) ){
            $args = array(
                'post_type'     => array('product', 'product_variation'),
                'post_status'   => 'publish',
                'meta_query'    => array(
                    array(
                        'key'   => '_sku',
                        'value' => $product_title,
                    )
                )
            );
            $product_data = new WP_Query( $args);
        }
        else{
            $args = array(      
                'post_type'   => 'product',
                's'           => $product_title,
                'post_status' => 'publish'
            );
            $product_data = new WP_Query( $args );
        }

        ?>
            <ul class="grid2" id="product_list">
                <?php

                    while ( $product_data->have_posts() ) : 

                        $product_data->the_post();  
                        $_tmdpos_product            = wc_get_product( $product_data->post->ID  ); 
                        $tmd_pos_product_varoiation = new WC_Product_Variable( $_tmdpos_product->get_id() );
                        $tmd_product_varoiation     = $tmd_pos_product_varoiation->get_available_variations();
                        $attributes                 = $_tmdpos_product->get_attributes();
                        $attributesdata             = $tmd_pos_product_varoiation->get_variation_attributes();/*product variable option*/
                        $attribute_keys             = array_keys( $attributesdata );
                        $_tmd_pos_pd_id             = $_tmdpos_product->get_id();/*get product stock status*/
                        $stock_status               = $_tmdpos_product->get_stock_status(); // get stock status
                        $stock_pd_qty               = $_tmdpos_product->get_stock_quantity(); // get stock status
                        $variations_id              = $_tmdpos_product->get_children();/*variation details*/
                        $product_description        = apply_filters( 'woocommerce_short_description', $product_data->post->post_excerpt );
                        ?>
                        <li>
                                <a>
									<?php 
											$class=$currency=$attr=$rel= '';
											if( $stock_status == 'outofstock' ){
												$class = 'tmd_post_outof_icon';
											}
											else{ 
												if( empty( $variations_id ) ) {
													if( ! empty( $_tmdpos_product->get_price_html() ) ){
														$class    = 'pos_add_ro_cart'; 
														$currency = 'currency = '.get_woocommerce_currency_symbol().'';
														$attr     = 'data-new=""'; 
														$rel      = 'data-rel='.$_tmd_pos_pd_id.'';
													}
													else{ 
														$class = 'tmd_post_cart_unable'; 
													}
												} 
												else {
													$class = 'tmdpos_op_pd';
													$attr  = 'tmd-pos-var-id='.$_tmdpos_product->get_id().'';
												}
											} 
										?>
                                       <div class="tmd_pos_pd_img pos-add-prod<?php echo esc_attr($_tmd_pos_pd_id); ?> <?php echo esc_attr( $class ); ?>" <?php echo esc_attr( $currency ).' '.esc_attr( $attr ).' '. esc_attr( $rel ); ?>>
                                        <img src="<?php if( has_post_thumbnail(  $_tmdpos_product->get_id()  ) ) { echo esc_url( the_post_thumbnail_url( $_tmdpos_product->get_id() ) ); } else { echo esc_url($tmd_empty_img); } ?>" alt="Image" title="<?php the_title(); ?>" />
                                    
                                        <?php 
                                            if( $stock_status == 'outofstock' ){
                                                ?><span class="tmd_post_outof_icon"><?php esc_html_e('Out Of Stock','tmdpos'); ?></span><?php 
                                            }
                                            else{ 
                                                if( empty( $variations_id ) ) { 
                                                    if( !empty( $_tmdpos_product->get_price_html() ) ){
                                                        ?>
                                                            <span class="pos_add_ro_cart tmd_post_cart_icon" title="<?php esc_html_e( 'Add To Cart', 'tmdpos' ); ?>" currency="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>" data-new="" data-rel="<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></span>
                                                        <?php 
                                                    }
                                                    else{ 
                                                        ?>
                                                            <span class="tmd_post_cart_unable"><?php esc_html_e('Disabled Add To Cart', 'tmdpos'); ?></span>'; 
                                                        <?php
                                                    }
                                                }
                                                else{
                                                    ?>
                                                        <span class="tmd_post_cart_icon tmdpos_op_pd" tmd-pos-var-id="<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>" title="<?php esc_attr_e( 'Select Option', 'tmdpos' ); ?>"></span>
                                                    <?php
                                                }
                                            }
                                        ?>
                                    </div>
                                    <span class="top product_stock_qty"><?php echo esc_html( $stock_pd_qty ); ?></span>
                                </a>
                                        
                                <div class="pos-productholder">
                                    <!-- The Modal -->
                                    <div id="tmd_pop_modal<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>" class="tmd_pop_option_pd">
                                        <!-- Modal content -->
                                        <div class="tmdpop-content tmdpos_option_div<?php echo esc_attr( $_tmd_pos_pd_id ); ?>">

                                            <div class="product_detail_option">
                                                <div class="tmd_pos_pd_img">
                                                    <img class="tmd_pos_variation_img tmd_pos_variation_imgsz image_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" src="<?php if( has_post_thumbnail(  $_tmdpos_product->get_id()  ) ) { echo esc_url( the_post_thumbnail_url( $_tmdpos_product->get_id() ) ); } else { echo esc_url( $tmd_empty_img ); } ?>" alt="Image" title="<?php the_title(); ?>" />
                                                </div>
                                            </div>

                                            <div class="tmd_pos_product_description">
                                                <div class="cartpopclose" tmd-pos-var-id-to-close="<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>">&times;</div>

                                                <div class="product_description">
                                                    <h2 class="name_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"><?php the_title(); ?></h2>
                                                    <?php echo wp_kses_post( $product_description ); ?>
                                                    <p class="variation_price price_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"><?php echo wp_kses_post( $_tmdpos_product->get_price_html() ); ?><span style="display: none" class="currency_symbol get_woocommerce_currency_symbol<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></span></p>
                                                </div>

                                                <?php 
                                                    if(!empty($stockdatas) && $stockdatas->product_status == 'enable'){
                                                        ?>
                                                            <span style="display: none;" class="stock_status<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></span>
                                                        <?php
                                                    } 
                                                ?>
                                                <p style="display: none;" class="stock_qty<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></p>
                                                <p style="display: none;" class="vailiable_backorder<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></p>

                                                <div class="product_option">
                                                    <table>
                                                        <tbody>
                                                            <?php 
                                                                if( count($tmd_product_varoiation) > 0 ){
                                                                    ?>
                                                                        <div class="product-variations-dropdown">
                                                                            <select onchange="tmd_pos_product_change_s('<?php echo esc_js( $_tmd_pos_pd_id ); ?>')" class="product_option_null product_option<?php echo esc_attr( $_tmd_pos_pd_id ) ?>" name="tmd_product_varoiation">
                                                                                <option value="null" disabled selected>---<?php esc_html_e( 'Choose an option', 'tmdpos') ?>---</option>
                                                                                <?php
                                                                                    foreach( $tmd_product_varoiation as $variation ){
                                                                                        $option_value = array();

                                                                                        foreach( $variation['attributes'] as $attribute => $term_slug ){
                                                                                            $taxonomy = str_replace( 'attribute_', '', $attribute );
                                                                                            if(!empty(get_taxonomy( $taxonomy )->labels->singular_name)){
                                                                                                $attribute_name   = get_taxonomy( $taxonomy )->labels->singular_name; // Attribute name
                                                                                                $term_name        = get_term_by( 'slug', $term_slug, $taxonomy )->name; // Attribute value term name
                                                                                                $variation_n      = wc_get_product($variation['variation_id']); //variation name
                                                                                                $variation_st     = new WC_Product_Variation( $variation['variation_id'] );
                                                                                                $variations_stock = $variation_st->get_stock_quantity(); /*stock qty*/
                                                                                                $option_value[]   = $attribute_name . ': '.$term_name;
                                                                                            }
                                                                                        }
                                                                                        $option_value = implode( ' , ', $option_value );
                                                                                        $stock_status = $variation['is_in_stock'] == 1 ? __( 'In Stock', 'tmdpos' ) : __('Out Of Stock', 'tmdpos');
                                                                                        $backordered  = get_post_meta( $variation['variation_id'], '_backorders', true );
                                                                                        ?>
                                                                                            <option 
                                                                                                data-backodr="<?php echo esc_attr( $backordered ); ?>" 
                                                                                                data-status="<?php echo esc_attr( $stock_status ); ?>" 
                                                                                                data-currency="<?php echo esc_attr(get_woocommerce_currency_symbol()); ?>" 
                                                                                                data-sku="<?php echo esc_attr( $variation_n->get_sku() ); ?>" 
                                                                                                data-stqty="<?php echo esc_attr( $variations_stock ); ?>" 
                                                                                                data-name="<?php echo esc_attr( $variation_n->get_name() );?>"  
                                                                                                data-cost="<?php echo esc_attr( get_post_meta($variation['variation_id'], '_price', true) ); ?>"
                                                                                                data-img="<?php echo esc_url( $variation['image']['url'] ); ?>" 
                                                                                                value="<?php echo esc_attr( $variation['variation_id'] ); ?>"><?php echo esc_attr( $option_value ); ?>        
                                                                                            </option>
                                                                                        <?php
                                                                                    }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    <?php
                                                                }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                    <?php 
                                                        if( $_tmdpos_product->is_type( 'grouped' ) ){
                                                            $children_pds = $_tmdpos_product->get_children();
                                                            if( !empty( $children_pds ) ){
                                                                ?>
                                                                <table>
                                                                    <tbody>
                                                                        <?php 
                                                                            foreach( $children_pds as $children_pd){
                                                                                ?>
                                                                                    <tr>
                                                                                        <th>
                                                                                            <input type="radio" data-rel="<?php echo esc_attr( $children_pd );  ?>" class="grp_children_pd" value="<?php echo esc_attr( $children_pd ); ?>" name="child_product"></th>
                                                                                        <td><?php echo esc_html( get_the_title( $children_pd ) ); ?></td>
                                                                                    </tr>
                                                                                <?php
                                                                            } 
                                                                        ?>
                                                                    </tbody>
                                                                </table>
                                                                <?php 
                                                            }
                                                        }      
                                                    ?>
                                                </div>
                                                <p style="color: red;" class="empty_product_select"></p>
                                                <div class="tmd_pos_cart_btn">                          
                                                    <button class="button option_add_to_cart"><span style="vertical-align: middle;" class="pos_add_ro_cart dashicons dashicons-cart active_cart" currency="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>" data-new=""></span></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clear"></div>       
                                </div>

                                <div class="prg-content-holder">
                                    <div class="pos-p-title">
                                        <h4><?php echo esc_attr( substr( get_the_title(), 0, 20) ); ?> </h4>
                                        <div class="pos-p-price">
                                            <?php echo wp_kses_post( $_tmdpos_product->get_price_html() ); ?>
                                            <input type="hidden" name="product_p<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_html( $_tmdpos_product->get_price() ); ?>" />
                                            <input type="hidden" name="product_id<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>" />
                                            <input type="hidden" name="product_sku<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_sku() ); ?>" />
                                            <input type="hidden" name="product_name<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php the_title(); ?>" />
                                            <input type="hidden" name="currency_symbol" value="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>" />
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php 
                    endwhile; 
                ?>
            </ul>
        <?php
        header("Content-type: application/json; charset=utf-8");
        die(); 
    }    
}

if( !function_exists( 'tmdpos_load_more_product' ) ){
    // tmd Pos page load more product
    add_action( 'wp_ajax_tmdpos_load_more_product', 'tmdpos_load_more_product');
    add_action( 'wp_ajax_nopriv_tmdpos_load_more_product', 'tmdpos_load_more_product');
    function tmdpos_load_more_product(){

        $tmd_empty_img = TMDPOS_IMAGE_PATH. 'company_logo.png';
        $load_qty      = absint( sanitize_text_field( $_POST['load_more_pd'] ) );/*product detail */
        $tmdposloop    = new WP_Query( array('post_type' => 'product', 'posts_per_page' => 12 + $load_qty ) );
        ?>
            <ul class="grid2" id="product_list"> 
                <?php 
                    while ( $tmdposloop->have_posts() ) : $tmdposloop->the_post();  

                        $_tmdpos_product            = wc_get_product( $tmdposloop->post->ID  ); 
                        $tmd_pos_product_varoiation = new WC_Product_Variable( $_tmdpos_product->get_id() );
                        $tmd_product_varoiation     = $tmd_pos_product_varoiation->get_available_variations();
                        $attributes                 = $_tmdpos_product->get_attributes();
                        $attributesdata             = $tmd_pos_product_varoiation->get_variation_attributes();
                        $attribute_keys             = array_keys( $attributesdata );
                        $_tmd_pos_pd_id             = $_tmdpos_product->get_id();
                        $stock_status               = $_tmdpos_product->get_stock_status(); // get stock status
                        $stock_pd_qty               = $_tmdpos_product->get_stock_quantity(); // get stock status
                        $variations_id              = $_tmdpos_product->get_children();
                        $product_description        = apply_filters( 'woocommerce_short_description', $tmdposloop->post->post_excerpt );
                        ?>

                            <li>
                                <a>
								    <?php 
                                                $class=$currency=$attr=$rel= '';
                                                if( $stock_status == 'outofstock' ){
                                                    $class = 'tmd_post_outof_icon';
                                                }
                                                else{ 
                                                    if( empty( $variations_id ) ) {
                                                        if( ! empty( $_tmdpos_product->get_price_html() ) ){
                                                            $class    = 'pos_add_ro_cart'; 
                                                            $currency = 'currency = '.get_woocommerce_currency_symbol().'';
                                                            $attr     = 'data-new=""'; 
                                                            $rel      = 'data-rel='.$_tmd_pos_pd_id.'';
                                                        }
                                                        else{ 
                                                            $class = 'tmd_post_cart_unable'; 
                                                        }
                                                    } 
                                                    else {
                                                        $class = 'tmdpos_op_pd';
                                                        $attr  = 'tmd-pos-var-id='.$_tmdpos_product->get_id().'';
                                                    }
                                                } 
                                            ?>
											
                                  <div class="tmd_pos_pd_img pos-add-prod<?php echo esc_attr($_tmd_pos_pd_id); ?> <?php echo esc_attr( $class ); ?>" <?php echo esc_attr( $currency ).' '.esc_attr( $attr ).' '. esc_attr( $rel ); ?>>
                                          
									
                                    <img src="<?php if( has_post_thumbnail(  $_tmdpos_product->get_id()  ) ) { echo esc_url( the_post_thumbnail_url( $_tmdpos_product->get_id() ) ); } else { echo esc_url( $tmd_empty_img ); } ?>" alt="Image" title="<?php the_title(); ?>" />
                                            
                                        <?php 
                                            if( $stock_status == 'outofstock' ){ 
                                                ?><span class="tmd_post_outof_icon"><?php esc_html_e( 'Out Of Stock', 'tmdpos' ); ?></span><?php 
                                            }
                                            else{ 
                                                if( empty( $variations_id ) ) { 
                                                    if( !empty( $_tmdpos_product->get_price_html() ) ){
                                                        ?>
                                                            <span class="pos_add_ro_cart tmd_post_cart_icon" title="<?php esc_html_e( 'Add To Cart', 'tmdpos' ); ?>" currency="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>" data-new="" data-rel="<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></span>
                                                        <?php 
                                                    }
                                                    else{ 
                                                        echo sprintf( '<span class="tmd_post_cart_unable">'.esc_html( 'Disabled Add To Cart', 'tmdpos' ).'</span>' ); 
                                                    }
                                                }
                                                else{
                                                    ?>
                                                        <span class="tmd_post_cart_icon tmdpos_op_pd" tmd-pos-var-id="<?php echo esc_attr($_tmdpos_product->get_id()); ?>" title="<?php esc_attr_e( 'Select Option', 'tmdpos' ); ?>"></span>
                                                    <?php
                                                }
                                            }
                                        ?>
                                    </div>
                                    <span class="top product_stock_qty"><?php echo esc_html( $stock_pd_qty ); ?></span>
                                </a>
                                        
                                <div class="pos-productholder">
                                    <!-- The Modal -->
                                    <div id="tmd_pop_modal<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>" class="tmd_pop_option_pd">

                                        <!-- Modal content -->
                                        <div class="tmdpop-content tmdpos_option_div<?php echo esc_attr( $_tmd_pos_pd_id );  ?>">

                                            <div class="product_detail_option">
                                                <div class="tmd_pos_pd_img">
                                                    <img class="tmd_pos_variation_img tmd_pos_variation_imgsz image_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" src="<?php if( has_post_thumbnail( $_tmdpos_product->get_id() ) ) { echo esc_url( the_post_thumbnail_url( $_tmdpos_product->get_id() ) ); } else { echo esc_url( $tmd_empty_img ); } ?>" alt="Image" title="<?php the_title(); ?>" />
                                                </div>
                                            </div>

                                            <div class="tmd_pos_product_description">

                                                <div class="cartpopclose" tmd-pos-var-id-to-close="<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>">&times;</div>

                                                <div class="product_description">
                                                    <h2 class="name_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"><?php the_title(); ?></h2>
                                                    <?php echo html_entity_decode( $product_description ); ?>
                                                    <p class="variation_price price_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"><?php echo $_tmdpos_product->get_price_html(); ?><span style="display: none" class="currency_symbol get_woocommerce_currency_symbol<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"><?php echo get_woocommerce_currency_symbol(); ?></span></p>
                                                </div>

                                                <?php if ( ! empty( $stockdatas ) ) : if ( $stockdatas->product_status == 'enable' ): ?>
                                                    <span style="display: none;" class="stock_status<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></span>
                                                <?php endif; endif; ?>

                                                <p style="display:none;" class="stock_qty<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></p>
                                                <p style="display:none;" class="vailiable_backorder<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></p>

                                                <div class="product_option">
                                                    <table>
                                                        <tbody>
                                                            <?php 
                                                                if( count($tmd_product_varoiation) > 0 ){
                                                                    ?>
                                                                        <div class="product-variations-dropdown">
                                                                            <select onchange="tmd_pos_product_change_s('<?php echo esc_js( $_tmd_pos_pd_id ); ?>')" class="product_option_null product_option<?php echo esc_attr( $_tmd_pos_pd_id ) ?>" name="tmd_product_varoiation">
                                                                                <option value="null" disabled selected>---<?php esc_html_e( 'Choose an option', 'tmdpos') ?>---</option>
                                                                                <?php
                                                                                    foreach( $tmd_product_varoiation as $variation ){
                                                                                        $option_value = array();

                                                                                        foreach( $variation['attributes'] as $attribute => $term_slug ){
                                                                                            $taxonomy = str_replace( 'attribute_', '', $attribute );
                                                                                            if(!empty(get_taxonomy( $taxonomy )->labels->singular_name)){
                                                                                                $attribute_name   = get_taxonomy( $taxonomy )->labels->singular_name; // Attribute name
                                                                                                $term_name        = get_term_by( 'slug', $term_slug, $taxonomy )->name; // Attribute value term name
                                                                                                $variation_n      = wc_get_product($variation['variation_id']); //variation name
                                                                                                $variation_st     = new WC_Product_Variation( $variation['variation_id'] );
                                                                                                $variations_stock = $variation_st->get_stock_quantity(); /*stock qty*/
                                                                                                $option_value[]   = $attribute_name . ': '.$term_name;
                                                                                            }
                                                                                        }
                                                                                        $option_value = implode( ' , ', $option_value );
                                                                                        $stock_status = $variation['is_in_stock'] == 1 ? __( 'In Stock', 'tmdpos' ) : __('Out Of Stock', 'tmdpos');
                                                                                        $backordered  = get_post_meta( $variation['variation_id'], '_backorders', true );
                                                                                        ?>
                                                                                            <option 
                                                                                                data-backodr="<?php echo esc_attr( $backordered ); ?>" 
                                                                                                data-status="<?php echo esc_attr( $stock_status ); ?>" 
                                                                                                data-currency="<?php echo esc_attr(get_woocommerce_currency_symbol()); ?>" 
                                                                                                data-sku="<?php echo esc_attr( $variation_n->get_sku() ); ?>" 
                                                                                                data-stqty="<?php echo esc_attr( $variations_stock ); ?>" 
                                                                                                data-name="<?php echo esc_attr( $variation_n->get_name() );?>"  
                                                                                                data-cost="<?php echo esc_attr( get_post_meta($variation['variation_id'], '_price', true) ); ?>"
                                                                                                data-img="<?php echo esc_url( $variation['image']['url'] ); ?>" 
                                                                                                value="<?php echo esc_attr( $variation['variation_id'] ); ?>"><?php echo esc_attr( $option_value ); ?>        
                                                                                            </option>
                                                                                        <?php
                                                                                    }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    <?php
                                                                }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                    
                                                    <?php 
                                                        if( $_tmdpos_product->is_type( 'grouped' ) ){
                                                            $children_pds = $_tmdpos_product->get_children();
                                                            if( !empty( $children_pds ) ){
                                                                ?>
                                                                    <table>
                                                                        <tbody>
                                                                            <?php 
                                                                                foreach( $children_pds as $children_pd){ 
                                                                                    ?>
                                                                                        <tr>
                                                                                            <th>
                                                                                                <input type="radio" data-rel="<?php echo esc_attr( $children_pd );  ?>" class="grp_children_pd" value="<?php echo esc_attr( $children_pd ); ?>" name="child_product"></th>
                                                                                            <td><?php echo esc_html( get_the_title( $children_pd ) ); ?></td>
                                                                                        </tr>
                                                                                    <?php 
                                                                                } 
                                                                            ?>
                                                                        </tbody>
                                                                    </table>
                                                                <?php 
                                                            }
                                                        }
                                                    ?>
                                                </div>
                                                <p style="color: red;" class="empty_product_select"></p>

                                                <div class="tmd_pos_cart_btn">                          
                                                    <button class="button option_add_to_cart"><span style="vertical-align: middle;" class="pos_add_ro_cart dashicons dashicons-cart active_cart" currency="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>" ></span></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>

                                <div class="prg-content-holder">
                                    <div class="pos-p-title">
                                        <h4><?php echo esc_html( substr( get_the_title(), 0, 20 ) ); ?></h4>
                                        <div class="pos-p-price">
                                            <?php echo wp_kses_post( $_tmdpos_product->get_price_html() ); ?>
                                            <input type="hidden" name="product_p<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_price() ); ?>" />
                                            <input type="hidden" name="product_id<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>" />
                                            <input type="hidden" name="product_sku<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_sku() ); ?>" />
                                            <input type="hidden" name="product_name<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php the_title(); ?>" />
                                            <input type="hidden" name="currency_symbol" value="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>" />
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php 
                    endwhile; 
                ?>
            </ul>

            <!-- button load more -->
            <div class="tmd_pos_load_more_div">
                <a href="#"><button type="button" class="button tmd_pos_load_more_btn"><?php esc_html_e( 'Load More', 'tmdpos' ); ?></button></a>
            </div>
        <?php

        header("Content-type: application/json; charset=utf-8");
        die(); 
    }
    /*tmd Pos page load more product end*/    
}

add_action( 'wp_ajax_tmd_pos_user_login', 'tmd_pos_user_login');
add_action( 'wp_ajax_nopriv_tmd_pos_user_login', 'tmd_pos_user_login');
function tmd_pos_user_login(){
    $json      = array();
    $info      = array();
    $user_name = !empty( $_POST['udata']['uname'] ) ? sanitize_text_field( $_POST['udata']['uname'] ) : ''; 
    $user_pass = !empty( $_POST['udata']['upass'] ) ? sanitize_text_field( $_POST['udata']['upass'] ) : '';
    $remember  = !empty( $_POST['udata']['remember'] ) ? sanitize_text_field( $_POST['udata']['remember'] ) : false;

    $info['user_login']     = $user_name;
    $info['user_password']  = $user_pass;
    $info['remember']       = $remember;

    $user      = get_user_by('login', $user_name);
    $user_role = !empty ( $user->roles[0] ) ?  $user->roles[0] : ''; 

    if (  $user_role === 'administrator' || $user_role === 'tmd_pos_user'  ){
        $user_signon = wp_signon( $info, false );
        if ( is_wp_error( $user_signon )){
            $json['status']  = 'fail';
            $json['message'] = esc_html( __( 'Opps! Invalid username or password.', 'tmdpos' ) );
        }
        else{
            $json['status']  = 'success';
            $json['message'] = esc_html( __( 'Login successfully, redirecting...', 'tmdpos' ) );
        }
    }
    else{
        $json['status']  = 'fail';
        $json['message'] = esc_html( __( 'Unable to login, please try with different id.', 'tmdpos' ) );
    }
    echo wp_json_encode( $json );
    header("Content-type: application/json; charset=utf-8");
    die(); 
}
if( !function_exists( 'tmdpos_layout_two_product_filter_by_name_sku' ) ){
    // layout two tmd pos product filter by product name and product sku 
    add_action( 'wp_ajax_tmdpos_layout_two_product_filter_by_name_sku', 'tmdpos_layout_two_product_filter_by_name_sku');
    add_action( 'wp_ajax_nopriv_tmdpos_layout_two_product_filter_by_name_sku', 'tmdpos_layout_two_product_filter_by_name_sku');
    function tmdpos_layout_two_product_filter_by_name_sku(){
        global $wpdb, $post, $woocommerce;

        $table_name    = $wpdb->prefix . 'posts';
        $tmd_empty_img = TMDPOS_IMAGE_PATH. 'company_logo.png';
        $product_title = ! empty( $_POST['product_title'] ) ? sanitize_text_field( $_POST['product_title'] ) : '';
        $product_id    = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_sku' AND meta_value='%s' LIMIT 1", $product_title ) );

        if( ! empty( $product_id ) ){
            $args = array(
                'post_type'     => array('product', 'product_variation'),
                'post_status'   => 'publish',
                'meta_query'    => array(
                    array(
                        'key'   => '_sku',
                        'value' => $product_title,
                    )
                )
            );
            $product_data = new WP_Query( $args);
        }
        else{
            $args = array(      
                'post_type'      => 'product',
                's'              => $product_title,
                'post_status'    => 'publish',
                'posts_per_page' => 10,
            );
            $product_data = new WP_Query( $args );
        }

        ?>
            <ul class="product-inner-holder">
                <?php 
                    while ( $product_data->have_posts() ) : $product_data->the_post();  
                        $_tmdpos_product            = wc_get_product( $product_data->post->ID  ); 
                        $tmd_pos_product_varoiation =  new WC_Product_Variable( $_tmdpos_product->get_id() );
                        $tmd_product_varoiation     =  $tmd_pos_product_varoiation->get_available_variations();
                        $attributes                 =  $_tmdpos_product->get_attributes();                    
                        $attributesdata             =  $tmd_pos_product_varoiation->get_variation_attributes();/*product variable option*/
                        $attribute_keys             =  array_keys( $attributesdata );
                        $_tmd_pos_pd_id             =  $_tmdpos_product->get_id();/*get product stock status*/
                        $stock_status               =  $_tmdpos_product->get_stock_status(); // get stock status
                        $stock_pd_qty               =  $_tmdpos_product->get_stock_quantity(); // get stock status
                        $variations_id              =  $_tmdpos_product->get_children();/*variation details*/
                        $product_description        =  apply_filters( 'woocommerce_short_description', $product_data->post->post_excerpt );

                        ?>
                            <li>
                                <?php 
                            
                                    $class = $currency = $attr = $rel = '';

                                    if( $stock_status == 'outofstock' ){
                                        $class = 'tmd_post_outof_icon';
                                    }
                                    else{ 
                                        if( empty( $variations_id ) ) {
                                            if( ! empty( $_tmdpos_product->get_price_html() ) ){
                                                $class    = 'pos_add_ro_cart tmd_post_cart_icon'; 
                                                $currency = 'currency = '.get_woocommerce_currency_symbol().''; 
                                                $attr     = 'data-new=""'; 
                                                $rel      = 'data-rel='.$_tmd_pos_pd_id.'';
                                            }
                                            else{ 
                                                $class = 'tmd_post_cart_unable'; 
                                            }
                                        } 
                                        else {
                                            $class = 'tmdpos_op_pd tmd_post_cart_icon';
                                            $attr  = 'tmd-pos-var-id='.$_tmdpos_product->get_id().'';
                                        }
                                    }
                                ?>
                                <div class="product-box-pos tmd_pos_pd_img <?php echo esc_attr( $class ); ?>" <?php echo esc_attr( $currency ).' '.esc_attr( $attr ).' '. esc_attr( $rel ); ?> >
                                    <img src="<?php if( has_post_thumbnail(  $_tmdpos_product->get_id()  ) ) { echo the_post_thumbnail_url( $_tmdpos_product->get_id() ); } else { echo esc_url( $tmd_empty_img ); } ?>" alt="Image" title="<?php the_title(); ?>" />
                                </div>
                                <span class="top product_stock_qty"><?php echo esc_attr( $stock_pd_qty );  ?></span>

                                <div class="pos-productholder">
                                    <!-- The Modal -->
                                    <div id="tmd_pop_modal<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>" class="tmd_pop_option_pd">

                                        <!-- Modal content -->
                                        <div class="tmdpop-content tmdpos_option_div<?php echo esc_attr( $_tmd_pos_pd_id ); ?>">
                                            <div class="product_detail_option">
                                                <div class="tmd_pos_pd_img">
                                                    <img class="tmd_pos_variation_img tmd_pos_variation_imgsz image_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" src="<?php if( has_post_thumbnail(  $_tmdpos_product->get_id()  ) ) { echo esc_url( the_post_thumbnail_url( $_tmdpos_product->get_id() ) ); } else { echo esc_url( $tmd_empty_img ); } ?>" alt="Image" title="<?php the_title(); ?>" />
                                                </div>
                                            </div>

                                            <div class="tmd_pos_product_description">
                                                <div class="cartpopclose" tmd-pos-var-id-to-close="<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>">&times;</div>

                                                <div class="product_description">
                                                    <h2 class="name_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"><?php the_title(); ?></h2>
                                                    <?php echo wp_kses_post( $product_description ); ?>
                                                    <p class="variation_price price_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"><?php echo wp_kses_post( $_tmdpos_product->get_price_html() ); ?> <span style="display: none" class="currency_symbol get_woocommerce_currency_symbol<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"><?php echo esc_attr( get_woocommerce_currency_symbol() ); ?></span>
                                                    </p>
                                                </div>

                                                <?php 
                                                    if(!empty($stockdatas) && $stockdatas->product_status == 'enable'){
                                                        ?>
                                                            <span style="display: none;" class="stock_status<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></span>
                                                        <?php  
                                                    } 
                                                ?> 
                                                <p style="display: none;" class="stock_qty<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></p>
                                                <p style="display: none;" class="vailiable_backorder<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></p>

                                                <div class="product_option">
                                                    <table>
                                                        <tbody>
                                                            <?php 
                                                                if( count($tmd_product_varoiation) > 0 ){
                                                                    ?>
                                                                        <div class="product-variations-dropdown">
                                                                            <select onchange="tmd_pos_product_change_s('<?php echo esc_js( $_tmd_pos_pd_id ); ?>')" class="product_option_null product_option<?php echo esc_attr( $_tmd_pos_pd_id ) ?>" name="tmd_product_varoiation">
                                                                                <option value="null" disabled selected>---<?php esc_html_e( 'Choose an option', 'tmdpos') ?>---</option>
                                                                                <?php
                                                                                    foreach( $tmd_product_varoiation as $variation ){
                                                                                        $option_value = array();

                                                                                        foreach( $variation['attributes'] as $attribute => $term_slug ){
                                                                                            $taxonomy = str_replace( 'attribute_', '', $attribute );
                                                                                            if(!empty(get_taxonomy( $taxonomy )->labels->singular_name)){
                                                                                                $attribute_name   = get_taxonomy( $taxonomy )->labels->singular_name; // Attribute name
                                                                                                $term_name        = get_term_by( 'slug', $term_slug, $taxonomy )->name; // Attribute value term name
                                                                                                $variation_n      = wc_get_product($variation['variation_id']); //variation name
                                                                                                $variation_st     = new WC_Product_Variation( $variation['variation_id'] );
                                                                                                $variations_stock = $variation_st->get_stock_quantity(); /*stock qty*/
                                                                                                $option_value[]   = $attribute_name . ': '.$term_name;
                                                                                            }
                                                                                        }
                                                                                        $option_value = implode( ' , ', $option_value );
                                                                                        $stock_status = $variation['is_in_stock'] == 1 ? __( 'In Stock', 'tmdpos' ) : __('Out Of Stock', 'tmdpos');
                                                                                        $backordered  = get_post_meta( $variation['variation_id'], '_backorders', true );
                                                                                        ?>
                                                                                            <option 
                                                                                                data-backodr="<?php echo esc_attr( $backordered ); ?>" 
                                                                                                data-status="<?php echo esc_attr( $stock_status ); ?>" 
                                                                                                data-currency="<?php echo esc_attr(get_woocommerce_currency_symbol()); ?>" 
                                                                                                data-sku="<?php echo esc_attr( $variation_n->get_sku() ); ?>" 
                                                                                                data-stqty="<?php echo esc_attr( $variations_stock ); ?>" 
                                                                                                data-name="<?php echo esc_attr( $variation_n->get_name() );?>"  
                                                                                                data-cost="<?php echo esc_attr( get_post_meta($variation['variation_id'], '_price', true) ); ?>"
                                                                                                data-img="<?php echo esc_url( $variation['image']['url'] ); ?>" 
                                                                                                value="<?php echo esc_attr( $variation['variation_id'] ); ?>"><?php echo esc_attr( $option_value ); ?>        
                                                                                            </option>
                                                                                        <?php
                                                                                    }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    <?php
                                                                }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                    <?php 
                                                        if( $_tmdpos_product->is_type( 'grouped' ) ){
                                                            $children_pds = $_tmdpos_product->get_children();
                                                            if( !empty( $children_pds ) ){
                                                                ?>
                                                                    <table>
                                                                        <tbody>
                                                                            <?php 
                                                                                foreach( $children_pds as $children_pd ){
                                                                                    ?>
                                                                                        <tr>
                                                                                            <th>
                                                                                                <input type="radio" data-rel="<?php echo esc_attr( $children_pd );  ?>" class="grp_children_pd" value="<?php echo esc_attr( $children_pd ); ?>" name="child_product" />
                                                                                            </th>
                                                                                            <td><?php echo get_the_title( $children_pd ); ?></td>
                                                                                        </tr>
                                                                                    <?php 
                                                                                }     
                                                                            ?>
                                                                        </tbody>
                                                                    </table>
                                                                <?php 
                                                            }
                                                        }
                                                    ?>
                                                </div>
                                                <p style="color: red;" class="empty_product_select"></p>

                                                <div class="tmd_pos_cart_btn">        
                                                    <button class="button option_add_to_cart">
                                                        <span style="vertical-align: middle;" class="pos_add_ro_cart dashicons dashicons-cart active_cart" currency="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>" data-new=""></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>

                                <div class="prg-content-holder">
                                    <div class="pos-p-title">
                                        <h4><?php echo esc_html( substr(get_the_title(), 0, 20) ); ?></h4>

                                        <div class="pos-p-price">
                                            <?php echo wp_kses_post( $_tmdpos_product->get_price_html() ); ?>
                                            <input type="hidden" name="product_p<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_price() ); ?>" />

                                            <input type="hidden" name="product_id<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>" />
                                            <input type="hidden" name="product_sku<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_sku() ); ?>" />
                                            <input type="hidden" name="product_name<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php the_title(); ?>" />
                                            <input type="hidden" name="currency_symbol" value="<?php echo get_woocommerce_currency_symbol(); ?>" />
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php 
                    endwhile; 
                ?>
                <!--- end product loop--->
                <br />
                <div class="clear"></div>
            </ul>
        <?php

        header("Content-type: application/json; charset=utf-8");
        die(); 
    }    
}

if( !function_exists( 'tmdpos_layout_two_product_filter_by_category' ) ){
    // layout tow fliter by category
    add_action( 'wp_ajax_tmdpos_layout_two_product_filter_by_category', 'tmdpos_layout_two_product_filter_by_category');
    add_action( 'wp_ajax_nopriv_tmdpos_layout_two_product_filter_by_category', 'tmdpos_layout_two_product_filter_by_category');
    function tmdpos_layout_two_product_filter_by_category() {

        global $wpdb;
        $tmd_empty_img = TMDPOS_IMAGE_PATH. 'company_logo.png';
        $category_id   = !empty( $_POST['category_id'] ) ? sanitize_text_field($_POST['category_id']) : 0;

        $category_s_product = get_posts( array(
            'post_type'   => 'product',
            'numberposts' => -1,
            'post_status' => 'publish',
            'fields'      => 'ids',
            'tax_query'   => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $category_id, /*category name*/
                )
            ),
        ));
            
        ?>
            <ul class="product-inner-holder">
                <?php
                    foreach ( $category_s_product as $productid ) {
                        if( !empty( $productid )){ 
                            $_tmdpos_product = wc_get_product( $productid  ); 
                            $tmd_pos_product_varoiation = new WC_Product_Variable( $productid );
                            $tmd_product_varoiation     = $tmd_pos_product_varoiation->get_available_variations();
                            $attributes                 = $_tmdpos_product->get_attributes();
                            $attributesdata             = $tmd_pos_product_varoiation->get_variation_attributes();
                            $attribute_keys             = array_keys( $attributesdata );/*product variable option*/
                            $_tmd_pos_pd_id             = $_tmdpos_product->get_id();/*get product stock status*/
                            $stock_status               = $_tmdpos_product->get_stock_status(); // get stock status
                            $stock_pd_qty               = $_tmdpos_product->get_stock_quantity(); // get stock status
                            $variations_id              = $_tmdpos_product->get_children();/*variation details*/
                            $product_description        = $_tmdpos_product->post->post_excerpt;
                            $image = wp_get_attachment_image_src( get_post_thumbnail_id( $productid ), 'single-post-thumbnail' );
                            ?>
                                <li>
                                    <?php 
                                        $class = $currency = $attr = $rel = '';
                                        if( $stock_status == 'outofstock' ){
                                            $class = 'tmd_post_outof_icon';
                                        }
                                        else{ 

                                            if( empty( $variations_id ) ) {
                                                if( ! empty( $_tmdpos_product->get_price_html() ) ){
                                                    $class     = 'pos_add_ro_cart tmd_post_cart_icon'; 
                                                    $currency  = 'currency = '.get_woocommerce_currency_symbol().''; 
                                                    $attr      = 'data-new=""'; 
                                                    $rel       = 'data-rel='.$_tmd_pos_pd_id.'';
                                                }
                                                else{ 
                                                    $class = 'tmd_post_cart_unable'; 
                                                }
                                            } 
                                            else {
                                                $class = 'tmdpos_op_pd tmd_post_cart_icon';
                                                $attr  = 'tmd-pos-var-id='.$_tmdpos_product->get_id().'';
                                            }
                                        }
                                    ?>
                                    <div class="product-box-pos tmd_pos_pd_img <?php echo esc_attr( $class ); ?>" <?php echo esc_html( $currency ).' '.esc_html( $attr ).' '. esc_html( $rel ); ?> >
                                        <img src="<?php if( has_post_thumbnail(  $productid  ) ) { echo esc_url( $image[0] ); } else { echo esc_url( $tmd_empty_img ); } ?>" alt="Image" title="<?php echo esc_attr( $_tmdpos_product->get_title() ); ?>" />
                                    </div>
                                    <span class="top product_stock_qty"><?php echo esc_html( $stock_pd_qty ); ?></span>
                                    <div class="pos-productholder">
                                        <!-- The Modal -->
                                        <div id="tmd_pop_modal<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>" class="tmd_pop_option_pd">
                                            <!-- Modal content -->
                                            <div class="tmdpop-content tmdpos_option_div<?php echo esc_attr( $_tmd_pos_pd_id ); ?>">

                                                <div class="product_detail_option">
                                                    <div class="tmd_pos_pd_img">
                                                        <img class="tmd_pos_variation_img tmd_pos_variation_imgsz image_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" src="<?php if( has_post_thumbnail(  $productid  ) ) { echo esc_url( $image[0] ); } else { echo esc_url( $tmd_empty_img ); } ?>" alt="Image" title="<?php echo esc_attr( $_tmdpos_product->get_title() ); ?>" />
                                                    </div>
                                                </div>

                                                <div class="tmd_pos_product_description">
                                                    <div class="cartpopclose" tmd-pos-var-id-to-close="<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>">&times;</div>

                                                    <div class="product_description">
                                                        <h2 class="name_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"><?php echo esc_html( $_tmdpos_product->get_title() ); ?></h2>
                                                        <?php echo wp_kses_post( $product_description ); ?>
                                                        <p class="variation_price price_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>">
                                                            <?php echo wp_kses_post( $_tmdpos_product->get_price_html() ); ?> 
                                                            <span style="display: none" class="currency_symbol get_woocommerce_currency_symbol<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></span>
                                                        </p>
                                                    </div>
                                                    <?php 
                                                        if(!empty($stockdatas) && $stockdatas->product_status == 'enable'){
                                                            ?>
                                                                <span style="display: none;" class="stock_status<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></span>
                                                            <?php
                                                        } 
                                                    ?>
                                                    <p style="display: none;"  class="stock_qty<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></p>
                                                    <p style="display: none;"  class="vailiable_backorder<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></p>

                                                    <div class="product_option">
                                                        <table>
                                                            <tbody>
                                                                <?php 
                                                                    if( count($tmd_product_varoiation) > 0 ){
                                                                        ?>
                                                                            <div class="product-variations-dropdown">
                                                                                <select onchange="tmd_pos_product_change_s('<?php echo esc_js( $_tmd_pos_pd_id ); ?>')" class="product_option_null product_option<?php echo esc_attr( $_tmd_pos_pd_id ) ?>" name="tmd_product_varoiation">
                                                                                    <option value="null" disabled selected>---<?php esc_html_e( 'Choose an option', 'tmdpos') ?>---</option>
                                                                                    <?php
                                                                                        foreach( $tmd_product_varoiation as $variation ){
                                                                                            $option_value = array();

                                                                                            foreach( $variation['attributes'] as $attribute => $term_slug ){
                                                                                                $taxonomy = str_replace( 'attribute_', '', $attribute );
                                                                                                if(!empty(get_taxonomy( $taxonomy )->labels->singular_name)){
                                                                                                    $attribute_name   = get_taxonomy( $taxonomy )->labels->singular_name; // Attribute name
                                                                                                    $term_name        = get_term_by( 'slug', $term_slug, $taxonomy )->name; // Attribute value term name
                                                                                                    $variation_n      = wc_get_product($variation['variation_id']); //variation name
                                                                                                    $variation_st     = new WC_Product_Variation( $variation['variation_id'] );
                                                                                                    $variations_stock = $variation_st->get_stock_quantity(); /*stock qty*/
                                                                                                    $option_value[]   = $attribute_name . ': '.$term_name;
                                                                                                }
                                                                                            }
                                                                                            $option_value = implode( ' , ', $option_value );
                                                                                            $stock_status = $variation['is_in_stock'] == 1 ? __( 'In Stock', 'tmdpos' ) : __('Out Of Stock', 'tmdpos');
                                                                                            $backordered  = get_post_meta( $variation['variation_id'], '_backorders', true );
                                                                                            ?>
                                                                                                <option 
                                                                                                    data-backodr="<?php echo esc_attr( $backordered ); ?>" 
                                                                                                    data-status="<?php echo esc_attr( $stock_status ); ?>" 
                                                                                                    data-currency="<?php echo esc_attr(get_woocommerce_currency_symbol()); ?>" 
                                                                                                    data-sku="<?php echo esc_attr( $variation_n->get_sku() ); ?>" 
                                                                                                    data-stqty="<?php echo esc_attr( $variations_stock ); ?>" 
                                                                                                    data-name="<?php echo esc_attr( $variation_n->get_name() );?>"  
                                                                                                    data-cost="<?php echo esc_attr( get_post_meta($variation['variation_id'], '_price', true) ); ?>"
                                                                                                    data-img="<?php echo esc_url( $variation['image']['url'] ); ?>" 
                                                                                                    value="<?php echo esc_attr( $variation['variation_id'] ); ?>"><?php echo esc_attr( $option_value ); ?>        
                                                                                                </option>
                                                                                            <?php
                                                                                        }
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                        <?php
                                                                    }
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                        <?php 
                                                            if( $_tmdpos_product->is_type( 'grouped' ) ){
                                                                $children_pds = $_tmdpos_product->get_children();
                                                                if( ! empty( $children_pds ) ){
                                                                    ?>
                                                                        <table>
                                                                            <tbody>
                                                                                <?php 
                                                                                    foreach( $children_pds as $children_pd ){ 
                                                                                        ?>
                                                                                        <tr>
                                                                                            <th>
                                                                                                <input type="radio" data-rel="<?php echo esc_attr( $children_pd );  ?>" class="grp_children_pd" value="<?php echo esc_attr( $children_pd ); ?>" name="child_product" />
                                                                                            </th>
                                                                                            <td><?php echo esc_html( get_the_title( $children_pd ) ); ?></td>
                                                                                        </tr>
                                                                                        <?php
                                                                                    }
                                                                                ?>
                                                                            </tbody>
                                                                        </table>
                                                                    <?php 
                                                                }
                                                            }
                                                        ?>
                                                    </div>
                                                    <p style="color: red;" class="empty_product_select"></p>
                                                    <div class="tmd_pos_cart_btn">        
                                                        <button class="button option_add_to_cart">
                                                            <span style="vertical-align: middle;" class="pos_add_ro_cart dashicons dashicons-cart active_cart" currency="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>" data-new=""></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="clear"></div>
                                    </div>

                                    <div class="prg-content-holder">
                                        <div class="pos-p-title">
                                            <h4><?php echo esc_html( substr( $_tmdpos_product->get_title(), 0, 20 ) ); ?></h4>
                                            <div class="pos-p-price">
                                                <?php echo wp_kses_post( $_tmdpos_product->get_price_html() ); ?>
                                                <input type="hidden" name="product_p<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_price() ); ?>" />
                                                <input type="hidden" name="product_id<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>" />
                                                <input type="hidden" name="product_sku<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_sku() ); ?>" />
                                                <input type="hidden" name="product_name<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_title() ); ?>" />
                                                <input type="hidden" name="currency_symbol" value="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>" />
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php
                        }
                    }
                ?>  
                <br />
                <div class="clear"></div>
            </ul>
        <?php
        header("Content-type: application/json; charset=utf-8");
        die();      
    }    
}

if( !function_exists( 'tmdpos_layout_two_load_more' ) ){
    // layout two load more
    add_action( 'wp_ajax_tmdpos_layout_two_load_more', 'tmdpos_layout_two_load_more');
    add_action( 'wp_ajax_nopriv_tmdpos_layout_two_load_more', 'tmdpos_layout_two_load_more');
    function tmdpos_layout_two_load_more(){

        $tmd_empty_img = TMDPOS_IMAGE_PATH. 'company_logo.png';
        $load_qty      = absint( sanitize_text_field( $_POST['load_more_pd'] ) );
        $tmdposloop    = new WP_Query( array( 'post_type' => 'product', 'posts_per_page' => 12 + $load_qty ) );

        ?>
            <ul class="product-inner-holder">
                <?php 
                    while ( $tmdposloop->have_posts() ) : 
                    
                        $tmdposloop->the_post();  
                        $_tmdpos_product            = wc_get_product( $tmdposloop->post->ID  ); 
                        $tmd_pos_product_varoiation = new WC_Product_Variable( $_tmdpos_product->get_id() );
                        $tmd_product_varoiation     = $tmd_pos_product_varoiation->get_available_variations();
                        $attributes                 = $_tmdpos_product->get_attributes();
                        $attributesdata             = $tmd_pos_product_varoiation->get_variation_attributes();/*product variable option*/
                        $attribute_keys             = array_keys( $attributesdata );
                        $_tmd_pos_pd_id             = $_tmdpos_product->get_id();/*get product stock status*/
                        $stock_status               = $_tmdpos_product->get_stock_status(); // get stock status
                        $stock_pd_qty               = $_tmdpos_product->get_stock_quantity(); // get stock status
                        $variations_id              = $_tmdpos_product->get_children();/*variation details*/
                        $product_description        = apply_filters( 'woocommerce_short_description', $tmdposloop->post->post_excerpt );
                        ?>
                            <li>
                                <?php 
                                    $class = $currency = $attr = $rel = '';

                                    if( $stock_status == 'outofstock' ){
                                        $class = 'tmd_post_outof_icon';
                                    }
                                    else{ 
                                        if( empty( $variations_id ) ) {
                                            if( ! empty( $_tmdpos_product->get_price_html() ) ){
                                                $class     = 'pos_add_ro_cart tmd_post_cart_icon'; 
                                                $currency  = 'currency = '.get_woocommerce_currency_symbol().''; 
                                                $attr      = 'data-new=""'; 
                                                $rel       = 'data-rel='.$_tmd_pos_pd_id.'';
                                            }
                                            else{ 
                                                $class = 'tmd_post_cart_unable'; 
                                            }
                                        } 
                                        else {
                                            $class = 'tmdpos_op_pd tmd_post_cart_icon';
                                            $attr  = 'tmd-pos-var-id='.$_tmdpos_product->get_id().'';
                                        }
                                    }
                                ?>
                                <div class="product-box-pos tmd_pos_pd_img <?php echo esc_attr( $class ); ?>" <?php echo esc_html( $currency ).' '.esc_html( $attr ).' '. esc_html( $rel ); ?>>
                                    <img src="<?php if( has_post_thumbnail(  $_tmdpos_product->get_id()  ) ) { echo esc_url( the_post_thumbnail_url( $_tmdpos_product->get_id() ) ); } else { echo esc_url( $tmd_empty_img ); } ?>" alt="Image" title="<?php the_title(); ?>" />
                                </div>
                                <span class="top product_stock_qty"><?php echo esc_html( $stock_pd_qty ); ?></span>
                                <div class="pos-productholder">
                                    <!-- The Modal -->
                                    <div id="tmd_pop_modal<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>" class="tmd_pop_option_pd">

                                        <!-- Modal content -->
                                        <div class="tmdpop-content tmdpos_option_div<?php echo esc_attr( $_tmd_pos_pd_id ); ?>">

                                            <div class="product_detail_option">
                                                <div class="tmd_pos_pd_img">
                                                    <img class="tmd_pos_variation_img tmd_pos_variation_imgsz image_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" src="<?php if( has_post_thumbnail(  $_tmdpos_product->get_id()  ) ) { echo esc_url( the_post_thumbnail_url( $_tmdpos_product->get_id() ) ); } else { echo esc_url( $tmd_empty_img ); } ?>" alt="Image" title="<?php the_title(); ?>" />
                                                </div>
                                            </div>

                                            <div class="tmd_pos_product_description">

                                                <div class="cartpopclose" tmd-pos-var-id-to-close="<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>">&times;</div>

                                                <div class="product_description">
                                                    <h2 class="name_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"><?php the_title(); ?></h2>
                                                    <?php echo wp_kses_post( $product_description ); ?>
                                                    <p class="variation_price price_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"> 
                                                        <?php echo wp_kses_post( $_tmdpos_product->get_price_html() ); ?> 
                                                        <span style="display: none" class="currency_symbol get_woocommerce_currency_symbol<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"><?php echo esc_attr( get_woocommerce_currency_symbol() ); ?></span>
                                                    </p>
                                                </div>
                                                <?php 
                                                    if(!empty($stockdatas) && $stockdatas->product_status == 'enable'){
                                                        ?>
                                                            <span style="display: none;" class="stock_status<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></span>
                                                        <?php 
                                                    } 
                                                ?>
                                                <p style="display: none;" class="stock_qty<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></p>
                                                <p style="display: none;" class="vailiable_backorder<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></p>

                                                <div class="product_option">
                                                    <table>
                                                        <tbody>
                                                            <?php 
                                                                if( count($tmd_product_varoiation) > 0 ){
                                                                    ?>
                                                                        <div class="product-variations-dropdown">
                                                                            <select onchange="tmd_pos_product_change_s('<?php echo esc_js( $_tmd_pos_pd_id ); ?>')" class="product_option_null product_option<?php echo esc_attr( $_tmd_pos_pd_id ) ?>" name="tmd_product_varoiation">
                                                                                <option value="null" disabled selected>---<?php esc_html_e( 'Choose an option', 'tmdpos') ?>---</option>
                                                                                <?php
                                                                                    foreach( $tmd_product_varoiation as $variation ){
                                                                                        $option_value = array();

                                                                                        foreach( $variation['attributes'] as $attribute => $term_slug ){
                                                                                            $taxonomy = str_replace( 'attribute_', '', $attribute );
                                                                                            if(!empty(get_taxonomy( $taxonomy )->labels->singular_name)){
                                                                                                $attribute_name   = get_taxonomy( $taxonomy )->labels->singular_name; // Attribute name
                                                                                                $term_name        = get_term_by( 'slug', $term_slug, $taxonomy )->name; // Attribute value term name
                                                                                                $variation_n      = wc_get_product($variation['variation_id']); //variation name
                                                                                                $variation_st     = new WC_Product_Variation( $variation['variation_id'] );
                                                                                                $variations_stock = $variation_st->get_stock_quantity(); /*stock qty*/
                                                                                                $option_value[]   = $attribute_name . ': '.$term_name;
                                                                                            }
                                                                                        }
                                                                                        $option_value = implode( ' , ', $option_value );
                                                                                        $stock_status = $variation['is_in_stock'] == 1 ? __( 'In Stock', 'tmdpos' ) : __('Out Of Stock', 'tmdpos');
                                                                                        $backordered  = get_post_meta( $variation['variation_id'], '_backorders', true );
                                                                                        ?>
                                                                                            <option 
                                                                                                data-backodr="<?php echo esc_attr( $backordered ); ?>" 
                                                                                                data-status="<?php echo esc_attr( $stock_status ); ?>" 
                                                                                                data-currency="<?php echo esc_attr(get_woocommerce_currency_symbol()); ?>" 
                                                                                                data-sku="<?php echo esc_attr( $variation_n->get_sku() ); ?>" 
                                                                                                data-stqty="<?php echo esc_attr( $variations_stock ); ?>" 
                                                                                                data-name="<?php echo esc_attr( $variation_n->get_name() );?>"  
                                                                                                data-cost="<?php echo esc_attr( get_post_meta($variation['variation_id'], '_price', true) ); ?>"
                                                                                                data-img="<?php echo esc_url( $variation['image']['url'] ); ?>" 
                                                                                                value="<?php echo esc_attr( $variation['variation_id'] ); ?>"><?php echo esc_attr( $option_value ); ?>        
                                                                                            </option>
                                                                                        <?php
                                                                                    }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    <?php
                                                                }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                    <?php 
                                                        if( $_tmdpos_product->is_type( 'grouped' ) ):
                                                            $children_pds = $_tmdpos_product->get_children();
                                                            if( ! empty( $children_pds ) ):
                                                                ?>
                                                                    <table>
                                                                        <tbody>
                                                                            <?php 
                                                                                foreach( $children_pds as $children_pd ){
                                                                                    ?>
                                                                                    <tr>
                                                                                        <th><input type="radio" data-rel="<?php echo esc_attr( $children_pd );  ?>" class="grp_children_pd" value="<?php echo esc_attr( $children_pd ); ?>" name="child_product"></th>
                                                                                        <td><?php echo esc_html( get_the_title( $children_pd ) ); ?></td>
                                                                                    </tr>
                                                                                    <?php     
                                                                                } 
                                                                            ?>
                                                                        </tbody>
                                                                    </table>
                                                                <?php 
                                                            endif;
                                                        endif; 
                                                    ?>
                                                </div>

                                                <p style="color: red;" class="empty_product_select"></p>
                                                <div class="tmd_pos_cart_btn">        
                                                    <button class="button option_add_to_cart">
                                                        <span style="vertical-align: middle;" class="pos_add_ro_cart dashicons dashicons-cart active_cart" currency="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>" data-new=""></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>

                                <div class="prg-content-holder">
                                    <div class="pos-p-title">
                                        <h4><?php echo esc_html( substr( get_the_title(), 0, 20 ) ); ?></h4>
                                        <div class="pos-p-price">
                                            <?php echo wp_kses_post( $_tmdpos_product->get_price_html() ); ?>
                                            <input type="hidden" name="product_p<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_price() ); ?>" />

                                            <input type="hidden" name="product_id<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>" />
                                            <input type="hidden" name="product_sku<?php echo esc_attr(  $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_sku() ); ?>" />
                                            <input type="hidden" name="product_name<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php the_title(); ?>">
                                            <input type="hidden" name="currency_symbol" value="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>">
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php 
                    endwhile; 
                ?>
                <br />
                <div class="clear"></div>
                <!--- end product loop--->
            </ul>
        <?php

        header("Content-type: application/json; charset=utf-8");
        die(); 
    }    
}


if( !function_exists( 'tmdpos_layout_two_customer_search' ) ){
    add_action('wp_ajax_tmdpos_layout_two_customer_search', 'tmdpos_layout_two_customer_search');
    add_action('wp_ajax_nopriv_tmdpos_layout_two_customer_search', 'tmdpos_layout_two_customer_search');
    function tmdpos_layout_two_customer_search(){
        $json   = [];
        $client = ! empty( $_POST['request']['term'] ) ? sanitize_text_field( $_POST['request']['term'] ) : '';
    
        if( $client ){
            $args = array(
                'search'         => '*'.$client.'*',
                'role'           => 'customer',
                'search_columns' => array( 'user_nicename', 'user_login', 'user_email' )
            );
            $user_query = new WP_User_Query( $args );
            $users      = ! empty( $user_query->results  ) ? $user_query->results  : '';
    
            if( is_array( $users ) ){
                foreach( $users as $user ){
                    $json[]  = ['id' => $user->ID, 'name' => $user->user_login]; 
                }
                echo wp_json_encode($json);
            }
        }
        header("Content-type: application/json; charset=utf-8");
        die();
    }    
}