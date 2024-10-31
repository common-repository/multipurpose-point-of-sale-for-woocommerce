<?php
/**
 * Tmd POS header layout one 
 * 
 * @since 1.0.1
 **/
defined( 'ABSPATH' ) || exit;
?>
<!-- pos_topheader start here -->
<div class="tmdpos_topheader">
    <ul>
        <li class="cursor_pointer" onclick="openNav()">
            <span class="dashicons dashicons-menu"></span> 
            <i><?php esc_html_e( 'Menu', 'tmdpos' ); ?> </i>
        </li>
        <li class="tmd_pos-menu-right">
            <ul>
                <li>
                    <span class="tmd-fullscreen-button cursor_pointer"><a class="full-scrreen" href="javascript:void(0)"><span class="dashicons dashicons-fullscreen-alt"></span></a></span>
                </li>
                <li>
                    <span class="tmd_back_button"><a class="admin_back" href="javascript:void(0)"><span class="dashicons dashicons-update-alt"></span><?php esc_html_e( 'Reload', 'tmdpos' ); ?></a></span>
                </li>
                <li>
                    <div class="tmd_pos_admin_menu">
                        <span class="tmd_admin_menu_button">
                            <span class="tmd_dropdown_icon"><span class="dashicons dashicons-admin-users"></span><?php echo esc_html( $current_user->display_name ); ?> </span>
                            <div class="tmd_pos_admin_menu_content">

                                <?php 
                                    if ( !empty( $current_user->roles[0] ) && $current_user->roles[0] == 'administrator'  ){
                                        ?>
                                            <a href="#" class="cursor_pointer" onclick="openTmdPosAdmin('<?php echo admin_url('admin.php?page=tmd_pos'); ?>')">
											<?php echo wp_kses_post( get_avatar( $current_user->ID, 32 ) ).'<br>'. esc_html( $current_user->display_name ); ?></a>
                                        <?php
                                    } 
                                ?>
                                <a href="<?php echo esc_url( wp_logout_url( home_url().'/pos-screen/' ) ); ?>"><?php esc_html_e( 'Log Out', 'tmdpos' ); ?></a>
                            </div>
                        </span>
                    </div>
                </li>
            </ul>
        </li>
    </ul>
</div>
<!-- pos_topheader end here -->
<div id="tmdpos_menu" class="tmd-curtain">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <div class="tmd_pos_menu_content">
        <?php 
            if( current_user_can('administrator')  ){
                ?>
                    <a class="cursor_pointer" onclick="openTmdPosAdmin('<?php echo esc_js( admin_url( 'admin.php?page=tmd_pos' ) ); ?>')"><?php esc_html_e( 'POS Setting', 'tmdpos' ); ?></a>
                <?php 
            } 
        ?>
        <a href="#" class="tmd_order_list_btn"><?php esc_html_e( 'Orders', 'tmdpos' ); ?></a>
        <a href="#" class="tmd_customer_list_btn"><?php esc_html_e( 'Customers', 'tmdpos' ); ?></a>
        <a href="#" class="tmd_product_list_btn"><?php esc_html_e( 'Products', 'tmdpos' ); ?></a>
        <a href="#" class="tmd_pos_report_btn"><?php esc_html_e( 'Sale Report', 'tmdpos' ); ?></a>
    </div>
</div> 