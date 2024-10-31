<?php
/**
 * tmd pos pro feature 
 * 
 * @package pos admin feature
 */

defined("ABSPATH") || exit;
?>
<div class="wrap">
	<h2><?php echo esc_html( $GLOBALS['title'] ); ?></h2>
	<div id="tmd-pos-pro-container">
		<h1><?php esc_html_e( 'Get our PRO plugin to unlock all Fetuers.', 'tmdpos'); ?></h1>
		<a href="<?php echo esc_url( TMDPOS_PRO_URL ); ?>" target="_blank" class="tmd-update-pro-link">
		<?php esc_html_e( 'Buy PRO ', 'tmdpos'); ?></a>
		<table id="table-tablefreepaid" class="table-col-2 component-table">
		    <thead>
				<tr>
					<th class="not-used"><span class=""><?php esc_html_e('Features', 'tmdpos'); ?></span></th>
					<th class="table-col-head table-col-1" scope="col"><?php esc_html_e('Pro', 'tmdpos'); ?></th>
					<th class="table-col-head table-col-2" scope="col"><?php esc_html_e('Open', 'tmdpos'); ?></th>
			 	</tr>
		    </thead>
		    
		    <tbody>
			    <tr>
					<th scope="row"><?php esc_html_e('Pos Dashbord', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
				</tr>
		     	<tr>
					<th scope="row"><?php esc_html_e( 'Pos Screen', 'tmdpos'); ?></th>
			  		<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
			  		<td class="table-col-2"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
		     	</tr>
			  	<tr>
			  		<th scope="row"><?php esc_html_e( 'Multiple Pos Screen', 'tmdpos'); ?></th>
			  		<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
			  		<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
		     	</tr>
			  	<tr>
					<th scope="row"><?php esc_html_e( 'Hold Orders', 'tmdpos'); ?></th>
			  		<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
			  		<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
		     	</tr>
			   	<tr>
			   		<th scope="row"><?php esc_html_e( 'Clear Cart', 'tmdpos'); ?></th>
			  		<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
			  		<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
		     	</tr>
			    <tr>
					<th scope="row"><?php esc_html_e( 'Tmd Pos Order Status', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
				</tr>
		       	<tr>
		       		<th scope="row"><?php esc_html_e( 'Inventory Barcode', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
			   	</tr>
			   	<tr>
			   		<th scope="row"><?php esc_html_e( 'Show Out Of Stock Warning', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
			    </tr> 
			   	<tr>
			   		<th style="text-align:center" colspan="3"><strong><?php esc_html_e( 'Invoice Setting', 'tmdpos'); ?></strong></th>
			   	</tr>
			   	<tr>
					<th scope="row"><?php esc_html_e( 'Logo', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
			    </tr>   	
				<tr>
					<th scope="row"><?php esc_html_e( 'Logo size', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
			    </tr>	
				<tr>
					<th scope="row"><?php esc_html_e( 'Reciept Formats', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
			    </tr>		
				<tr>
					<th scope="row"><?php esc_html_e( 'Reciept Size', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
			    </tr>	
				<tr>
					<th scope="row"><?php esc_html_e( 'Show Store Logo', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
			    </tr>	
				<tr>
					<th scope="row"><?php esc_html_e( 'Show Store Name', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
			    </tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Show Store Address', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
			    </tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Show Order Date & Time', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
			    </tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Show Invoice Number', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
			    </tr>	
				<tr>
					<th scope="row"><?php esc_html_e( 'Show Cashier Name', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
			    </tr>	
				<tr>
					<th scope="row"><?php esc_html_e( 'Show Customer Name', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
			    </tr>		
				<tr>
					<th scope="row"><?php esc_html_e( 'Show Shipping Address', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
			    </tr>	
				<tr>
					<th scope="row"><?php esc_html_e( 'Show Payment Mode', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
			    </tr>	
				<tr>
					<th scope="row"><?php esc_html_e( 'Show Change', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
			    </tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Show Extra Information (like TIN No. ST No.)', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
			    </tr>	
				<tr>
					<th scope="row"><?php esc_html_e( 'Invoice Text', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
			    </tr>	
				<tr>
					<th scope="row"><?php esc_html_e( 'Payment Gateways', 'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
			    </tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Pos Frontend user',  'tmdpos'); ?></th>
					<td class="table-col-1"><i class="i-icon i-checked"><span class="sr-only"><?php esc_html_e('yes', 'tmdpos'); ?></span></i></td>
					<td class="table-col-2"><i class="i-icon i-x"><span class="sr-only"><?php esc_html_e('No', 'tmdpos'); ?></span></i></td>
			    </tr>	
			</tbody>
		</table>
	</div>
</div>	