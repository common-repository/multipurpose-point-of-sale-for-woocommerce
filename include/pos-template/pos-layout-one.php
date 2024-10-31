<?php
/**
 * Tempalet name pos layout one
 * 
 * @since 1.2
 * @package pos-layout-one 
 **/
defined( 'ABSPATH' ) || exit;
?>
<!-- tmdpos left -->
<div class="tmdpos_left">
    <form>
        <div class="tmdpos_search">
            <i class="tmd_pos_search_icon add_product_to_cart" aria-hidden="true"><img src="<?php echo esc_url( $searchicon_url ); ?>" /></i> 
               <input type="search" id="product_search_filter" class="scan_product_cart" placeholder="<?php esc_attr_e('Search by product name and SKU', 'tmdpos' ); ?>" onkeypress="return event.keyCode != 13;"/>
        </div>  
    </form>
    <div class="tmdpos_products">
         <ul class="grid2" id="product_list">
                        <?php 
                            $idArray = array();
                            $cart_items = !empty( $_SESSION['pos_items'] ) ? $_SESSION['pos_items'] : false; 
                            if ( !empty( $cart_items ) ){
                                $idArray = array_map(function ($item) {
                                    return $item['product_id'];
                                }, $cart_items); 
                            }

                            while ( $tmdposloop->have_posts() ) : $tmdposloop->the_post();  

                                $_tmdpos_product            = wc_get_product( $tmdposloop->post->ID  ); 
                                $tmd_pos_product_varoiation = new WC_Product_Variable( $_tmdpos_product->get_id() );
                                $tmd_product_varoiation     = $tmd_pos_product_varoiation->get_available_variations();
                                $attributes                 = $_tmdpos_product->get_attributes();
                                $attributesdata             = $tmd_pos_product_varoiation->get_variation_attributes();
                                $attribute_keys             = array_keys( $attributesdata );// product variable option
                                $_tmd_pos_pd_id             = $_tmdpos_product->get_id();// get product stock status
                                $stock_status               = $_tmdpos_product->get_stock_status(); // get stock status
                                $stock_pd_qty               = $_tmdpos_product->get_stock_quantity(); // get stock status
                                $variations_id              = $_tmdpos_product->get_children();// variation details
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
                                                <?php  
                                                    if (in_array($_tmd_pos_pd_id, $idArray)) {
                                                        ?>
                                                            <div class="item_added_indicator" id="item_added_indicator<?php echo esc_attr($_tmd_pos_pd_id); ?>"><i class="fa fa-check-circle" aria-hidden="true"></i></div>
                                                        <?php
                                                    }

                                                    if ($variations_id) {
                                                        foreach ($variations_id as $var_id) {
                                                            if (in_array($var_id, $idArray)) {
                                                                ?>
                                                                    <div class="item_added_indicator" id="item_added_indicator<?php echo esc_attr($var_id); ?>"><i class="fa fa-check-circle" aria-hidden="true"></i></div>
                                                                <?php
                                                            }
                                                        }
                                                    }
                                                ?>
                                                <img src="<?php echo esc_url( wp_get_attachment_url( get_post_thumbnail_id( $_tmdpos_product->get_id() ) ) ) ; ?>" alt="Image" title="<?php the_title(); ?>" height="120" width="120" />
                                            </div>
                                            <span class="top product_stock_qty"><?php echo esc_html( $stock_pd_qty ); ?></span>
                                        </a>
                                    
                                        <div class="pos-productholder">
                                            <!-- The Modal -->
                                            <div id="tmd_pop_modal<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>" class="tmd_pop_option_pd">

                                                <!-- Modal content -->
                                                <div class="tmdpop-content tmdpos_option_div<?php echo esc_attr( $_tmd_pos_pd_id ); ?>">

                                                    <div class="cartpopclose" tmd-pos-var-id-to-close="<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>">&times;</div>

                                                    <div class="product_detail_option">
                                                        <div class="tmd_pos_pd_img">
                                                            <img class="tmd_pos_variation_img tmd_pos_variation_imgsz image_pos_product<?php echo $_tmd_pos_pd_id; ?>" src="<?php echo esc_url( wp_get_attachment_url( get_post_thumbnail_id( $_tmdpos_product->get_id() ) ) ) ; ?>" alt="Image" title="<?php the_title(); ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="tmd_pos_product_description">
                                                        <div class="product_description">
                                                            <h2 class="name_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"><?php the_title(); ?></h2>
                                                            <?php echo html_entity_decode( $product_description ); ?>
                                                            <p class="variation_price price_pos_product<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"><?php echo $_tmdpos_product->get_price_html(); ?><span style="display: none" class="currency_symbol get_woocommerce_currency_symbol<?php echo esc_html($_tmd_pos_pd_id); ?>"><?php get_woocommerce_currency_symbol(); ?></span></p>
                                                        </div>


                                                        <?php if(!empty($stockdatas)): if( $stockdatas->product_status == 'enable' ): ?>
                                                            <span style="display: none;" class="stock_status<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></span>
                                                        <?php endif; endif; ?>
                                                        <p style="display: none;" class="stock_qty<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></p>
                                                        <p style="display: none;" class="vailiable_backorder<?php echo esc_attr( $_tmd_pos_pd_id ); ?>"></p>

                                                        <div class="product_option">
                                                            <?php 
                                                                if( count( $tmd_product_varoiation ) > 0 ){
                                                                    ?>
                                                                    <div class="product-variations-dropdown">
                                                                        <select onchange="tmd_pos_product_change_s('<?php echo esc_js( $_tmd_pos_pd_id) ?>')" class="product_option_null product_option<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" name="tmd_product_varoiation">
                                                                            <option value="null" disabled selected>---
            																<?php esc_html_e( 'Choose an option', 'tmdpos' );  ?>---</option>
                                                                            <?php
                                                                                foreach( $tmd_product_varoiation as $variation ){
                                                                                    $option_value = array();

                                                                                    foreach( $variation['attributes'] as $attribute => $term_slug ){
                                                                                        $taxonomy = str_replace( 'attribute_', '', $attribute );
                                                                                        if(!empty(get_taxonomy( $taxonomy )->labels->singular_name)):
                                                                                            $attribute_name = get_taxonomy( $taxonomy )->labels->singular_name; // Attribute name
                                                                                            $term_name      = get_term_by( 'slug', $term_slug, $taxonomy )->name; // Attribute value term name
                                                                                            $variation_n    = wc_get_product($variation['variation_id']); //variation name
                                                                                            $variation_st   = new WC_Product_Variation( $variation['variation_id'] );
                                                                                            $variations_stock = $variation_st->get_stock_quantity(); /*stock qty*/
                                                                                            $option_value[]   = $attribute_name . ': '.$term_name;
                                                                                        endif;
                                                                                    }
                                                                                    $option_value = implode( ' , ', $option_value );
                                                                                    $backordered  = get_post_meta( $variation['variation_id'], '_backorders', true );
                                                                                    $stock_status = $variation['is_in_stock'] == 1 ? __( 'In Stock', 'tmdpos' ) : __( 'Out Of Stock', 'tmdpos' );
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

                                                                if( $_tmdpos_product->is_type( 'grouped' ) ):
                                                                    $children_pds  = $_tmdpos_product->get_children();
                                                                    if(!empty($children_pds)):
                                                                        ?>
                                                                            <table>
                                                                                <tbody>
                                                                                    <?php foreach( $children_pds as $children_pd): ?>
                                                                                        <tr>
                                                                                            <th><input type="radio" data-rel="<?php echo esc_attr( $children_pd ); ?>" class="grp_children_pd" value="<?php echo esc_attr( $children_pd ); ?>" name="child_product"></th>
                                                                                            <td><?php echo esc_attr( get_the_title( $children_pd ) ); ?></td>
                                                                                        </tr>
                                                                                    <?php endforeach; ?>
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
                                                                 <span style="vertical-align: middle;" data-varparentid="<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>" class="pos_add_ro_cart dashicons dashicons-cart active_cart" currency="<?php echo get_woocommerce_currency_symbol(); ?>" data-new=""></span>
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
                                                    <?php echo $_tmdpos_product->get_price_html(); ?>
                                                    <input type="hidden" name="product_p<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_price() ); ?>" />
                                                    <input type="hidden" name="product_id<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_id() ); ?>" />
                                                    <input type="hidden" name="product_sku<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php echo esc_attr( $_tmdpos_product->get_sku() ); ?>" />
                                                    <input type="hidden" name="product_name<?php echo esc_attr( $_tmd_pos_pd_id ); ?>" value="<?php the_title(); ?>" />
                                                    <input type="hidden" name="currency_symbol" value="<?php get_woocommerce_currency_symbol(); ?>" />
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php 
                            endwhile;
                        ?>
                    </ul>
        <div class="tmd_pos_load_more_div"><a href="#"><button type="button" class="button tmd_pos_load_more_btn"><?php esc_html_e( 'Load More', 'tmdpos' ); ?></button></a></div>
    </div>
    <div class="tmdpos_items">
        <ul class="grid">
            <?php 
                $query  = $wpdb->prepare("SELECT * FROM $pos_table WHERE `tmd_option` = %s", 'tmd_general_option');
                $general_data = $wpdb->get_row($query);
                $generalpotiondatas = ! empty( $general_data->tmd_option_value ) ? json_decode( $general_data->tmd_option_value ) : '';
					$terms = get_terms( 'product_cat', array(
					'orderby'    => 'name',
					'order'      => 'ASC',
					'hide_empty' => true,
					) );

					if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
              	foreach ( $terms as $term ) {
			        ?>
                        <li class="product_cat" data-cat="<?php echo esc_attr( $term->name ); ?>">
							<a class="cursor_pointer"><?php echo esc_html( $term->name ); ?></a>
						</li>
                      <?php
					 } 
					}
            ?>
        </ul>
    </div>
</div>
<!-- /tmdpos left -->

<!-- tmdpos right -->
<div class="tmdpos_right tmd_pos_order_data">
    <div class="table-responsive">
        <table class="border-bottom">
            <tbody>
                <tr>
                    <td width="75%"><a><?php echo esc_attr( $current_user->display_name ); ?></a></td>
                    <td class="text-right"><a class="bg-light tmd_pos_clearcart cursor_pointer" onclick="alert('<?php echo esc_js( __('Buy PRO to unlock all features', 'tmdpos') ); ?>');"><?php esc_html_e( 'Clear Cart','tmdpos' ); ?></a></td>
                </tr>
            </tbody>
        </table>
        <table class="border-bottom">
            <tbody>
                <tr>
                    <td class="text-left"><strong><?php esc_html_e( 'Product', 'tmdpos' ); ?></strong></td>
                    <td class="text-left"><strong><?php esc_html_e( 'Price', 'tmdpos' ); ?></strong></td>
                    <td class="text-left"><strong><?php esc_html_e( 'Qty', 'tmdpos' ); ?></strong></td>
                    <td class="text-center"><strong><?php esc_html_e( 'Total', 'tmdpos' ); ?></strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <!-- order success messgae start-->
        <div class="order_success"></div>
        <!-- order success messgae end-->
        
        <table class="border poschangeoverTable">
            <tbody></tbody>
        </table>
    </div>

    <div class="tmdpos_amount">        
        <div class="tmd_button_row">
            <button onclick="alert('<?php echo esc_js( __('Buy PRO to unlock all features', 'tmdpos') ); ?>');" class="button tmd_pos_hold_order"><?php esc_html_e('Hold','tmdpos'); ?></button>
            <button onclick="alert('<?php echo esc_js( __('Buy PRO to unlock all features', 'tmdpos') ); ?>');" class="button tmd_pos_apply_coupon"><?php esc_html_e('Apply Coupon','tmdpos'); ?></button>
        </div>

        <div id="tmd_pos_tax">
            <div class="inline_tax">
                <div class="Subtotal">
                    <span class="tax_label"><?php esc_html_e( 'Subtotal', 'tmdpos'); ?></span>
                    <span class="pos_inline_Sub_totalSpan tax_rates tmd_float_rigth"></span><br>        
                </div>
                <div class="Subtotal">
                    <span class="tax_label"><?php esc_html_e( 'VAT', 'tmdpos' ); ?></span>
                    <span class="pos_inline_tax_totalSpan tax_rates tmd_float_rigth"></span>
                    <input type="hidden" class="pos_inline_tax_totalinput" name="tax_total" value="" /> 
                </div>
                <div class="Subtotal tmd_coupon_span"></div>
            </div>
        </div>

        <div class="tmd_button_row">
            <button onclick="alert('<?php echo esc_js( __('Buy PRO to unlock all features', 'tmdpos') ); ?>');"  class="button hold_order_list"><?php esc_html_e( 'Hold Orders', 'tmdpos' ); ?></button>

            <a id="tmd_pos_checkout" class="btn-pay cursor_pointer">
                <span><?php esc_html_e( 'Pay', 'tmdpos' ); ?></span>
                    <input type="hidden" name="pos_total" class="pos_inline_total" />
                <span class="pos_inline_totalSpan"></span>
            </a>
        </div>
    </div>
</div>
<!-- tmdpos right -->