<?php
/**
 * tmd pos payment gateway
 *
 * @package tmd_pos_payment_card_gateway
 *
 */
defined( 'ABSPATH' ) || exit;

add_action('init', 'tmdpos_payment_gateway_method_card');
function tmdpos_payment_gateway_method_card(){

    class TMDPos_Payment_Gateway_Card extends WC_Payment_Gateway {
        /**
         * Constructor for the tmd pos pay gateway.
         */
        public function __construct() {

    	    $this->id          = 'tmd_pos_card';
		    $this->title       = __( 'Card', 'tmdpos' );
		    $this->description = '';
		    $this->icon        = apply_filters( 'woocommerce_tmd_pos_card_icon', '' );
            $this->has_fields  = true;

            // Actions
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }
    }

    /**
     * Tmd pos payment methode
     *
     * @package tmd_pos_pay
     * @return $tmdpos_pay_card
     */
    add_filter( 'woocommerce_payment_gateways', 'tmdpos_pay_gateway_card' );
    function tmdpos_pay_gateway_card( $tmdpos_pay_card ) {
        $tmdpos_pay_card[] = 'TMDPos_Payment_Gateway_Card'; 
        return $tmdpos_pay_card;
    }
}

add_action('init', 'tmdpos_payment_gateway_method_cash');
function tmdpos_payment_gateway_method_cash(){

    class TMDPos_Payment_Gateway_Cash extends WC_Payment_Gateway {

        /**
         * Constructor for the tmd pos pay gateway.
         */
        public function __construct() {
    	    $this->id          = 'tmd_pos_cash';
		    $this->title       = __( 'Cash', 'tmdpos' );
		    $this->description = '';
		    $this->icon        = apply_filters( 'woocommerce_tmd_pos_card_icon', '' );
            $this->has_fields  = true;

            // Actions
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }
    }
    
    /**
     * Tmd pos payment methode
     *
     * @package tmd_pos_pay
     * @return $tmdpos_pay_cash
     */
    add_filter( 'woocommerce_payment_gateways', 'tmdpos_pay_gateway_cash' );
    function tmdpos_pay_gateway_cash( $tmdpos_pay_cash ) {
        $tmdpos_pay_cash[] = 'TMDPos_Payment_Gateway_Cash'; 
        return $tmdpos_pay_cash;
    }        
}