<?php
/**
 * tmd pos report  
 *
 * @package tmd-pos-report 
 * @since 1.0.1
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="tmd-pos-report-container">
    <div id="container-flex">
        <!-- left section start HERE -->
        <div id="tmd-pos-amin-left-menu">
            <?php include TMDPOS_PLUGIN_PATH. 'include/tmd-pos-report/templates/left-sidebar.php' ; ?>
        </div>

        <!-- left section end HERE -->
        <div id="tmd-pos-admin-right-content">
            <?php include TMDPOS_PLUGIN_PATH. 'include/tmd-pos-report/templates/general.php' ; ?>
        </div>
    </div>
</div>