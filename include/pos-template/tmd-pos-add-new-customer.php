<?php
/**
* template name : add new customer
*
* @package tmd pos add new customer
* @since 1.0.1
*/
defined( 'ABSPATH' ) || exit;

global $woocommerce;
// country list 
$countries_obj   = new WC_Countries();
$countries       = $countries_obj->__get( 'countries' );
?>

<div class="tmdpos-container-madal">
	<div class="tmd-pos-flex-center">
		<div class="tmdpo-modal">
			<table>
				<tbody>
					<tr>
						<th><?php esc_html_e( 'Add Customer', 'tmdpos' ); ?></th>
						<td><p><button class="tmd-modal-close button-primary"><?php esc_html_e( 'close',  'tmdpos' ); ?></button></p></td>
					</tr>
					<tr>
						<th class="tmd_pos_th"><?php esc_html_e( 'Name', 'tmdpos' ); ?><span class="text-red">*</span></th>
						<td class="tmd_pos_td">
							<input type="text" name="customer_name" class="tmd_msg_n" placeholder="<?php esc_attr_e('Enter Name', 'tmdpos' ); ?>" />
							<span class="name_error text-red"></span>
						</td>
					</tr>
					<tr>
						<th class="tmd_pos_th"><?php esc_html_e( 'Email', 'tmdpos' ); ?><span class="text-red">*</span></th>
						<td class="tmd_pos_td">
							<input class="tmd_msg_e" type="email" name="customer_email" placeholder="<?php esc_attr_e( 'Enter Email', 'tmdpos' ); ?>" />
							<span class="email_error text-red"></span>
						</td>
					</tr>
					<tr>
						<th class="tmd_pos_th"><?php esc_html_e( 'Phone', 'tmdpos' ); ?><span class="text-red">*</span></th>
						<td class="tmd_pos_td">
							<input class="tmd_msg_p" type="number" name="customer_phone" placeholder="<?php esc_attr_e( 'Enter Phone Number', 'tmdpos' ); ?>" />
							<span class="phone_error text-red"></span>
						</td>
					</tr>
					<tr>
						<th class="tmd_pos_th"><?php esc_html_e( 'Address', 'tmdpos' ); ?></th>
						<td class="tmd_pos_td">
							<input type="text" placeholder="<?php esc_attr_e( 'Enter Address', 'tmdpos'); ?>" name="customer_address" />
						</td>
					</tr>
					<tr>
						<th class="tmd_pos_th"><?php esc_html_e( 'Address 2', 'tmdpos' ); ?></th>
						<td class="tmd_pos_td">
							<input type="text" placeholder="<?php esc_attr_e( 'Enter Address 2', 'tmdpos'); ?>" name="customer_address2"  />
						</td>
					</tr>
					<tr>
						<th class="tmd_pos_th"><?php esc_html_e('City', 'tmdpos'); ?></th>
						<td class="tmd_pos_td">
							<input type="text" placeholder="<?php esc_attr_e( 'Enter City', 'tmdpos' ); ?>" name="customer_city" />
						</td>
					</tr>
					<tr>
						<th class="tmd_pos_th"><?php esc_html_e('Postcode', 'tmdpos'); ?><span class="text-red">*</span></th>
						<td class="tmd_pos_td">
							<input type="number" placeholder="<?php esc_attr_e( 'Enter Postcode', 'tmdpos' ); ?>" name="customer_postcode" />
							<span class="postcode_error text-red"></span>
						</td>
					</tr>
					<tr>
						<th class="tmd_pos_th"><?php esc_html_e( 'Country', 'tmdpos' ); ?></th>
						<td class="tmd_pos_td">
							<select name="customer_country" class="customer_select customer_select_country">
								<option value="null" disabled selected>---<?php esc_html_e('Select Country', 'tmdpos'); ?>---</option>
								<?php 
									foreach ( $countries as $key => $countries ){
        								echo wp_sprintf('<option value="%1$s">%2$s</option>', esc_attr( $key ), esc_attr( $countries ) );
									}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th class="tmd_pos_th state_td"><?php esc_html_e( 'State', 'tmdpos' ); ?></th>
						<td class="tmd_pos_td state_td">
							<select name="customer_state" class="customer_select customer_select_state">
								<option value="null" disabled selected>---<?php esc_html_e( 'Select State', 'tmdpos' ); ?>---</option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<p>
								<button class="button button-primary save_new_customer"><?php esc_html_e( 'Save', 'tmdpos' ); ?></button>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>