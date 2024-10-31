<?php
/**
* Template Name : General
*
* @package tmd-pos-report/general
* @since 1.0.2
*/
defined( 'ABSPATH' ) || exit;
$tmd_pos_wc_gateway       = new WC_Payment_Gateways();
$tmd_pos_payment_gateways = $tmd_pos_wc_gateway->payment_gateways();
$datas 					  = tmdpos_get_order_total_by_payment_type();

$title = []; 
$total = []; 
if( ! empty( $datas ) ){
	foreach ( $datas as $data ){
		$title[] 	= html_entity_decode( tmdpos_get_order_payment_title( $data['id'] ) );
		$total[] 	= html_entity_decode( $data['total']);
	}
}
$payment_title = json_encode($title);
$payment_total = json_encode($total);


$users = get_users( [ 'role__in' => [ 'administrator', 'tmd_pos_user'] ] );	
$cashiers = [];
$u_total  = [];
if( is_array( $users ) ){
    foreach( $users as $user ){
        if( $user->ID ){
            $orders = tmdpos_get_order_by_chasier( $user->ID );
        	foreach( $orders as $key => $order ){
        		$orders_cashier = !empty($orders['cashier'])?$orders['cashier']:"";
        		$orders_total   = !empty( $orders['total'] )?$orders['total']  : "" ;

        		$cashiers[] = html_entity_decode( $orders_cashier );
				$u_total[] 	= html_entity_decode( $orders_total );
        	}
        }
    }
}
$user_title = json_encode( array_unique($cashiers) );
$user_total = json_encode( array_unique( $u_total ) );
?>
<input type="hidden" id="tmdpos_payment_title" value="<?php echo esc_attr( $payment_title ); ?>" />
<input type="hidden" id="tmdpos_payment_total" value="<?php echo esc_attr( $payment_total ); ?>" />
<input type="hidden" id="tmdpos_user_title" value="<?php echo esc_attr( $user_title ); ?>" />
<input type="hidden" id="tmdpos_user_total" value="<?php echo esc_attr( $user_total ); ?>" />

<div class="tmdpos-pos-report-chart">
	<h2><?php esc_html_e( 'Total Sales', 'tmdpos' ); ?></h2>
	<div class="tmdpos-clearfix"></div>
		 <div class="tmd-pos-flex">
			<section id="tmd-pso-report-section-left">
				<div class="tmd-pos-sale-report">
					<canvas id="tmd-pos-report"></canvas>
				</div>
				<br />
				<h2><?php esc_html_e( 'Payment Method Report', 'tmdpos' ); ?></h2>
			</section>
			<section id="tmd-pso-report-section-right">
				<div class="tmd-pos-sale-report">
					<canvas id="tmd-pos-cashier-report"></canvas>
				</div>
				<br />
				<h2><?php esc_html_e( 'Pos Users Report', 'tmdpos' ); ?></h2>
			</section>
		</div>
	<div class="tmd-pos-lst-last-order">
		<br />
		<h2><?php esc_html_e( 'Recent Sales', 'tmdpos' ); ?></h2>
		<table>
			<tr>
				<th>#<?php esc_html_e( 'Invoice No', 'tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Customer Name', 'tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Cashiers Name', 'tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Quantity', 'tmdpos' ); ?></th>
				<th><?php esc_html_e( 'Total', 'tmdpos' ); ?></th>
			</tr>
			<?php 
				$orders = tmdpos_get_recent_sale(); 

				if( !empty( $orders ) ){
					foreach( $orders as $order ){
						$order_details  = json_decode( $order->order_value );
						$wc_order 		= wc_get_order( $order->order_meta );
						$order_data   	= $wc_order->get_data();
						$order_items   	= $wc_order->get_items();
					    $qty    		= 0;
					    foreach( $order_items as $items ){
							$qty    += $items['quantity'];        
					    }

					    $cashiers_name = !empty( get_user_by( 'id', $order_details->cashier_id )->display_name ) ? get_user_by( 'id', $order_details->cashier_id )->display_name : 'error';
					    $customer_name  = !empty(get_user_by( 'id', $order_data['customer_id'] )->display_name)? get_user_by( 'id', $order_data['customer_id'] )->display_name : "";

						?>
							<tr>
								<td><a target="_blank" href="<?php echo esc_url( admin_url('post.php?post='.absint( $order->order_meta ).'&action=edit') ) ?>"><b>#<?php echo esc_html($order->order_meta ); ?><b></a></td>
								<td><?php echo esc_html( $customer_name ); ?></td>
								<td><?php echo esc_html( $cashiers_name ); ?></td>
								<td><?php echo esc_attr( absint( $qty ) ); ?></td>
								<td><?php echo wp_kses_post( wc_price( $wc_order->get_total() ) ); ?></td>
							</tr>
						<?php
					}
				}
			?>
		</table>
	</div>
</div>
