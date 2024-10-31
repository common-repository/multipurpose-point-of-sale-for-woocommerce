<?php
/**
 * tmd pos access denied 404 page  
 * 
 * @since 1.0.1
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="tmd-pos-background-gradient">
    <div class="tmd-pos-circles">
        <div class="tmd-pos-circle1"></div>
        <div class="tmd-pos-circle2"></div>
    </div>
    <div class="pos-404-main-container">
        <div class="title">
            <h1><?php esc_html_e( 'Unauthorized access', 'tmdpos' ); ?></h1>
            <p><?php esc_html_e( 'You dont have permission to access this page', 'tmdpos' ); ?></p>
            <br />
            <a class="tmd-pos-button" href="<?php echo esc_url( home_url() ); ?>"><?php esc_html_e( 'Back To Home', 'tmdpos' ) ?></a>
        </div>
    </div>
</div>