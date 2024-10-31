<?php
/**
* tmd pos final class
*/
defined( 'ABSPATH' ) || exit;

if( ! class_exists('Tmdpos_Init' ) ){   
    /**
     * Tmd Pos june Update 2023 1.0.2 
     * 
     * @since 1.0.2
     **/
    final class Tmdpos_Init{

        public function __construct(){

            // pos load files
            $this->tmdpos_load_file();

            // pos add admin menu
            add_action( 'admin_menu', array( $this, 'admin_menu' ) );

            // pos front script style
            add_action( 'wp_enqueue_scripts', array( $this, 'front_style_script' ) );

            // pos admin script style
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_scrit_style' ) );

            // set pos user session
            add_action( 'admin_init', array( $this, 'tmdpos_session' ) , 1);

            // unset pos user session
            add_action( 'wp_logout', array( $this, 'tmdpos_session_unset' ) );

            // add pos user role
            add_action( 'admin_init', array( $this, 'add_pos_user_roles' ) );

            // add pos menu
            add_action( 'admin_init', array( $this, 'tmdpos_add_pos_screen' ) );

            // add pos template to poss menu
            add_filter( 'page_template', array( $this, 'tmdpos_screen_template' ) );

            // pos page
            add_action( 'admin_init', array( $this, 'tmdpos_check_pos_page' )  );
        }

        /**
         * load plugin file
         */
        public function tmdpos_load_file(){
            include_once TMDPOS_PLUGIN_PATH. 'pos-install.php';
            include_once TMDPOS_PLUGIN_PATH. 'include/function.php';
            include_once TMDPOS_PLUGIN_PATH. 'include/tmd-pos-ajax.php';
            include_once TMDPOS_PLUGIN_PATH. 'include/class-tmdpos-savedata.php';
            include_once TMDPOS_PLUGIN_PATH. 'include/tmd-pos-class/class-tmd-pos-gateway.php';
        }

        /**
         * show menu in admin page
         */
        public function admin_menu(){

            add_menu_page( 'TMD POS', 'TMD POS', 'manage_woocommerce', 'tmd-pos', array( $this, 'tmdpos_admin_report' ) ,'dashicons-store', 54 );
            add_submenu_page( 'tmd-pos', 'TMD POS', 'TMD POS', 'manage_woocommerce', 'tmd-pos', array( $this, 'tmdpos_admin_report' ) );
            add_submenu_page( 'tmd-pos', 'POS Setting', 'POS Setting', 'manage_woocommerce', 'tmd_pos', array( $this, 'tmdpos_settings' ) );
            add_submenu_page( 'tmd-pos', 'POS User',  'POS User', 'manage_woocommerce', 'tmd_pos_user', array( $this, 'tmdpos_user' ) ); 
            add_submenu_page( 'tmd-pos', 'PRO Features', 'PRO Features', 'manage_woocommerce', 'tmd_pos_pro_fetures', array( $this, 'tmdpos_pro_fetures' ) );
        }

        public function tmdpos_admin_report(){
            include_once TMDPOS_PLUGIN_PATH . 'include/tmd-pos-report/tmd-pos-report.php';
        }

        public function tmdpos_settings(){
            include_once TMDPOS_PLUGIN_PATH . 'include/pos-admin-page/tmd-pos-admin-view.php';
        }

        public function tmdpos_user(){
            include_once TMDPOS_PLUGIN_PATH . 'include/tmd-pos-user/tmd-pos-user.php';
        }
        
        public function tmdpos_pro_fetures(){
            include_once TMDPOS_PLUGIN_PATH . 'include/tmd-pos-pro/tmd-pos-pro.php';
        }

        /**
         * tmd POS style
         */
        public function admin_scrit_style(){
            // style
            wp_enqueue_media();
            wp_enqueue_style( 'tmdpos-admin-style', TMDPOS_PLUGIN_URL. 'assets/css/pos-admin.min.css' );
            wp_enqueue_style( 'tmdpos-data-table', TMDPOS_PLUGIN_URL. 'assets/css/data-table.min.css' );

            // script
            wp_enqueue_script( 'tmdpos-graph-minjs', TMDPOS_PLUGIN_URL. 'assets/js/graph-canvas.min.js', array('jquery'), '');
            wp_enqueue_script( 'tmdpos-admin-script', TMDPOS_PLUGIN_URL. 'assets/js/admin-script.js', array('jquery') );
            wp_enqueue_script( 'tmdpos-datatable-js', TMDPOS_PLUGIN_URL. 'assets/js/data-table.min.js', array('jquery'), '');
        }

        public function front_style_script(){
            // style
            wp_enqueue_style( 'tmdpos-front', TMDPOS_PLUGIN_URL. 'assets/css/pos-front-min.css' );
            wp_enqueue_style( 'tmdpos-data-table', TMDPOS_PLUGIN_URL. 'assets/css/data-table.min.css' );

            // script
            wp_enqueue_script( 'tmdpos-front-script', TMDPOS_PLUGIN_URL. 'assets/js/front-script.js', array('jquery') );
            wp_enqueue_script( 'tmdpos-datatable-js', TMDPOS_PLUGIN_URL. 'assets/js/data-table.min.js', array('jquery'), '');
            
            // ajax
            wp_enqueue_script( 'tmdpos-ajax', TMDPOS_PLUGIN_URL. 'assets/js/ajax.js' , array('jquery') );
            wp_localize_script('tmdpos-ajax', 'tmd_ajax_url', array( 'ajax_url' => admin_url('admin-ajax.php')));

        }
        
        /*tmd pos sesssion*/
        public function tmdpos_session(){
            if (!session_id())
            session_start();
        }

        /*destroy session*/
        public function tmdpos_session_unset(){
            if (session_id()) 
            session_destroy();
        }

        public function add_pos_user_roles(){
            add_role(
                'tmd_pos_user',
                __( 'POS User', 'tmdpos' ),
                array(
                    'read'         => true,  
                    'edit_posts'   => true,
                    'delete_posts' => false, 
                    'publish_post' => true
                )
            );
        }

        /*tmd pos screen*/
        public function tmdpos_add_pos_screen(){
            $query  = new WP_Query( array('post_type' => 'page', 'title' => 'Pos Screen') );
            $page   = !empty( $query->post ) ? $query->post->post_title : '';
            $pageid = !empty( $query->post ) ? $query->post->ID : '';

            if( $page !== 'Pos Screen' ){
                $pos_page = array(
                    'post_type'    => 'page',
                    'post_title'   => 'Pos Screen',
                    'post_content' => '',
                    'post_status'  => 'publish',
                    'post_author'  => 1,
                );
                wp_insert_post( $pos_page );
            }
        }

        /*assign pos template to pos screen*/
        public function tmdpos_screen_template( $page_template ){
            $query  = new WP_Query( array('post_type' => 'page', 'title' => 'Pos Screen' ) );
            $page   = !empty( $query->post ) ? $query->post->post_title : '';
            $pageid = !empty( $page ) && $page == 'Pos Screen' ? $query->post->ID : '';
                
            if ( $pageid == get_the_ID() ){
                $page_template =  TMDPOS_PLUGIN_PATH . 'include/pos-template/tmd-pos-view.php';
            }
            return $page_template;
        }

        public function tmdpos_check_pos_page(){
            
            if( !empty( $_GET['page'] )){
                $page = sanitize_text_field( $_GET['page'] );
                if( $page=='tmd-pos' || $page=='tmd_pos' || $page=='tmd_pos_user' || $page=='tmd_pos_pro_fetures'){
                    add_filter( 'admin_footer_text', array( $this, 'tmdpos_admin_footer_content' ), 11 );
                    add_filter( 'update_footer', '__return_empty_string', 11 );
                }
            }
        }

        public function tmdpos_admin_footer_content(){
            $html = sprintf('<p>%1$s<a target="_blank" href="%2$s"><b>%3$s</b> <small><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-half"></span><small></a></p>', __('Thank You For Choosing Multipurpose - Point of Sale for WooCommerce, Give Us Your '),  esc_url('https://www.tmdextensions.com/woocommerce-plugin/woocommerce-point-of-sale'), __( 'Valuable Feedback', 'tmdpos' ) );
            return html_entity_decode( $html );
        }
    }/*class end here Tmdpos_Init*/
}