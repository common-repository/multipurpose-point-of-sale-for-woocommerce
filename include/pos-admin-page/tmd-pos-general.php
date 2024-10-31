<?php
/**
 * Tmd Pos General Setting.
 * 
 * @package tmd-pos-general
 */
defined( 'ABSPATH' ) || exit;
global $wpdb;

$table = $wpdb->prefix . "tmd_pos";
$query = $wpdb->prepare("SELECT * FROM $table WHERE `tmd_option` = %s", 'tmd_general_option');
$datas = $wpdb->get_row($query);
$data  = !empty( $datas->tmd_option_value ) ? json_decode( $datas->tmd_option_value ) : '';
	
$pos_layout = !empty( $data->_pos_layout ) ? $data->_pos_layout : '';
$catdata  	= !empty( $data->_pos_categories ) ? $data->_pos_categories : '';
$cat_list   = get_categories( array( 'taxonomy'=>'product_cat', 'title'=>'', 'orderby'=>'name' ) );

$layout_one   = TMDPOS_PLUGIN_URL .'assets/images/pos-layout-one.png';
$layout_two   = TMDPOS_PLUGIN_URL .'assets/images/pos-layout-two.png';
$layout_three = TMDPOS_PLUGIN_URL .'assets/images/pos-layout-three.png';

?>
<table class="pos-gereral-container form-table">
	<tbody>
		<tr>
			<th><?php esc_html_e( 'Select Category', 'tmdpos' ); ?></th>
			<td>
				<select name="category_list[]" multiple class="pos_category_select">
					<?php 
						if( is_array( $cat_list ) ){
							foreach( $cat_list as $cat ){ 
								?>
								<option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php if ( is_array( $catdata ) ){ foreach( $catdata as $cats ) { selected( $cats, $cat->term_id ); } } ?>><?php echo esc_html( $cat->name ); ?></option>
								<?php
							}
						}
					?>
				</select>
				<p><?php esc_html_e( 'Select multiple categories to display categories on Pos screen. ( Ctrl+Click to select/unselect multiple )', 'tmdpos'); ?></p>
			</td>
		</tr>

		<tr>
			<th><?php esc_html_e( 'Pos Layout', 'tmdpos'); ?></a></th>
			<td>
				<div class="pos-div-flex">
					<div class="pos_layout_box">
						<div class="layout_img"><label for="layout_one"><img class="pod_layout_active" src="<?php echo esc_url( $layout_one ); ?>" alt="" /><?php esc_html_e( 'Layout One', 'tmdpos' ); ?></label></div>
						<div class="layout_select"><input id="layout_one" class="input-input-hidden" type="radio" name="pos_layout" value="layout_one" <?php checked( $pos_layout, 'layout_one' ); ?> /></div>
					</div>

					<div class="pos_layout_box tmd-position-relative">
						<div class="layout_img"><label for="layout_two"><img class="" src="<?php echo esc_url( $layout_two ); ?>" alt="" /><?php esc_html_e( 'Layout Two', 'tmdpos' ); ?></label></div>
						<div class="tmd-prolink">
							<a href="https://www.tmdextensions.com/woocommerce-plugin/woocommerce-point-of-sale" target="_blank" class="tmd-update-pro-link"><?php esc_html_e( 'Buy PRO ', 'tmdpos'); ?></a>
						</div>
					</div>

					<div class="pos_layout_box tmd-position-relative">
						<div class="layout_img"><label for="layout_three"><img class="" src="<?php echo esc_url( $layout_three ); ?>" alt="" /><?php esc_html_e( 'Layout Three', 'tmdpos' ); ?></label></div>
						<div class="tmd-prolink">
							<a href="https://www.tmdextensions.com/woocommerce-plugin/woocommerce-point-of-sale" target="_blank" class="tmd-update-pro-link"><?php esc_html_e( 'Buy PRO ', 'tmdpos'); ?></a>
						</div>
					</div>
				</div>
				<br />
				<p><?php esc_html_e( 'Select Pos screen Layout', 'tmdpos' ); ?></p>
			</td>
		</tr>
	</tbody>
</table>