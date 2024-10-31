<?php
/**
 * Plugin Name:       Multipurpose - Point of Sale for WooCommerce
 * Plugin URI:        https://www.tmdextensions.com/woocommerce-plugin/woocommerce-point-of-sale
 * Description:       Multipurpose - Point of Sale for WooCommerce is a simple interface for taking orders at the Point of Sale using your   WooCommerce store.
 * Version:           2.0
 * Requires at least: 5.6
 * Requires PHP:      7.2
 * Author:            TMD Software Pvt. Ltd
 * Author URI:        http://themultimediadesigner.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       tmdpos
 * Domain Path:       /i18n/languages
 */
defined( 'ABSPATH' ) || exit;
define( 'TMDPOS_PLUGIN_PATH' , plugin_dir_path( __FILE__ ));
define( 'TMDPOS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TMDPOS_IMAGE_PATH', TMDPOS_PLUGIN_URL. 'assets/images/' );
define( 'TMDPOS_PRO_URL', 'https://www.tmdextensions.com/woocommerce-plugin/woocommerce-point-of-sale' );
/*install tmd pos required file*/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	include_once TMDPOS_PLUGIN_PATH. 'include/class-tmdpos-init.php';
	new Tmdpos_Init;

	/**
	 * Tmd Gst Prepend Settings link to plugin actions
	 *
	 * @param param
	 * @return array
	 **/
	add_filter( "plugin_action_links_".plugin_basename( __FILE__ ), 'tmdpos_plugin_action_links' );
	function tmdpos_plugin_action_links($actions){

		$_actions = [
			'pos_settings'    => sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=tmd_pos' ) ), __( 'Settings', 'tmdpos' ) ), 
			'tmd_pos_advance' => sprintf( '<a style="color: #06b906; font-weight: 600" href="%s" target="_blank">%s</a>', esc_url( TMDPOS_PRO_URL ), __( 'Go Pro!', 'tmdpos' ) ),
		];
		return array_merge( $_actions, $actions);
	}
} 
else {

	add_action( 'admin_notices', 'tmdpos_if_woo_is_not_install_check_error_msg' );
	function tmdpos_if_woo_is_not_install_check_error_msg() {
    	?>
		    <div class="notice notice-error">
		        <p><strong><?php echo wp_kses_post( 'Warning! Please Install <a href="http://wordpress.org/plugins/woocommerce/">WooCommerce</a> First To Use Point of Sale for WooCommerce.'); ?></strong></p>
		    </div>
    	<?php
	}
}