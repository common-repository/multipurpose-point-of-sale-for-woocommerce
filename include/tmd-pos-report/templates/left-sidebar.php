<?php
/**
 * Template Name : Left sidebar 
 *  
 * @package tmd-pos-report/left-sidebar
 * @since 1.0.1
 */
defined( 'ABSPATH' ) || exit;
$tmd_pos_wc_gateway       = new WC_Payment_Gateways();
$tmd_pos_payment_gateways = $tmd_pos_wc_gateway->payment_gateways();

?>

<h1><?php esc_html_e( 'Tmd Pos Report', 'tmdpos' ); ?></h1>

<!-- menu one -->
<ul class="menu">
	<li><?php esc_html_e( 'Main', 'tmdpos' ); ?></li><!-- Title -->
	<li class="<?php echo ! empty( $_GET['tmd_tab'] ) ? esc_attr( tmdpos_active_tab( 'tmd_tab', 'dashboard' ) ) : esc_attr( 'tmd_pos_tab_active' ); ?>">
		<a href="<?php echo esc_url( tmdpos_admin_url('dashboard') ); ?>"><?php esc_html_e('Dashboard', 'tmdpos'); ?></a>
	</li>
	<li>
		<a target="_blank" href="<?php echo esc_url( home_url('pos-screen') ); ?>"><?php esc_html_e('Open POS', 'tmdpos'); ?></a>
	</li>
</ul>
<!-- menu one END -->

<!-- menu two -->
<a href="<?php echo esc_url( TMDPOS_PRO_URL ); ?>" target="_blank" class="tmd-update-pro-link">
<?php esc_html_e( 'Buy PRO ', 'tmdpos'); ?></a>
<ul class="menu">
	<li><?php esc_html_e( 'Reports', 'tmdpos' ); ?></li><!-- Title -->
	<li class="<?php echo esc_attr( tmdpos_active_tab( 'tmd_tab', 'product_sale_report' ) ); ?>">
		<a href="#"><?php esc_html_e( 'Product Sale Report', 'tmdpos' ); ?></a>
	</li>
	<li class="<?php echo esc_attr( tmdpos_active_tab( 'tmd_tab', 'forecast_stock_report' ) ); ?>">
		<a href="#"><?php esc_html_e( 'Forecast Stock Report', 'tmdpos' ); ?></a>
	</li>
	<li class="<?php echo esc_attr( tmdpos_active_tab( 'tmd_tab', 'day_book' ) ); ?>">
		<a href="#"><?php esc_html_e( 'Day Book', 'tmdpos' ); ?></a>
	</li>
</ul>
<!-- menu two END -->

<!-- menu two three -->
<a href="<?php echo esc_url( TMDPOS_PRO_URL ); ?>" target="_blank" class="tmd-update-pro-link"><?php esc_html_e( 'Buy PRO ', 'tmdpos'); ?></a>
<ul class="menu">
	<li><?php esc_html_e( 'Payment Mode', 'tmdpos' ); ?></li></li><!-- Title -->
	<?php
		if( ! empty( $tmd_pos_payment_gateways ) ){
			foreach( $tmd_pos_payment_gateways as $gateway_id => $gateway ){
				echo sprintf( '<li class="%1$s"><a href="#" >%2$s <span class="tmd-color-icon random-color"></span></a></li>', esc_attr( tmdpos_active_tab('tmd_tab', $gateway_id) ), esc_html($gateway->get_title()) );
			}
		}
	?>
</ul>
<!-- menu two three END -->