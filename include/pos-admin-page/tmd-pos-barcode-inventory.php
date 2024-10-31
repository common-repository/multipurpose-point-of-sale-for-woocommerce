<?php
/**
 * tmd pos barcode inventory 
 */
defined( 'ABSPATH' ) || exit;

$args = array( 'post_type' => 'product', 'posts_per_page' => -1);
$loop = new WP_Query( $args );

?>

<div class="tmd_pos_barcode_inventory_main">
	<table id="tmd_barcode_inventory_tbl">
		<thead>
			<tr class="tmd_barcode_invet_head">
				<th><?php esc_html_e( 'Image', 'tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Name', 'tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Category', 'tmdpos' ); ?></th>
				<th><?php esc_html_e( 'ID', 'tmdpos' ); ?></th>
				<th><?php esc_html_e( 'SKU', 'tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Action', 'tmdpos' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php 
				while ( $loop->have_posts() ) : $loop->the_post();
	        		global $product;
					$products = wc_get_product( $product->get_id() );
	        		$terms 	  = get_the_terms( $product->get_id(), 'product_cat' );

						?>
							<tr class="tmd_barcode_invet_body">
								<td><img class="tmd_product_img" src="<?php echo esc_url( get_the_post_thumbnail_url() ); ?>"></td>

								<td><a><?php the_title(); ?></a></td>

								<td>
									<?php  
										foreach( $terms as $category){ 
											echo esc_html( $category->name.','); 
										}
									?>		
								</td>

								<td>
									<input type="number" name="pd_variation_id" value="<?php echo esc_attr( $product->get_id() ); ?>" placeholder="Product Id" class="input_td" disabled="disabled" />
								</td>

								<td>
									<input type="text" name="product_sku" value="<?php echo esc_attr( $products->get_sku() ); ?>" placeholder="Product Sku" class="input_td" disabled="disabled" />
								</td>

								<td>
									<a href="#" class="button-primary disabled"><?php esc_html_e( 'Generate Barcode', 'tmdpos' ); ?></a>
									<a href="https://www.tmdextensions.com/woocommerce-plugin/woocommerce-point-of-sale" target="_blank" class="tmd-update-pro-link"><?php esc_html_e( 'Buy PRO', 'tmdpos' ); ?></a>
								</td>
							</tr>
						<?php 
					endwhile;   
				wp_reset_query(); 
			?>
		</tbody>
	</table>
</div>