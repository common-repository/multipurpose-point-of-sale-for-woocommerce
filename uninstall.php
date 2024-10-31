<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   WooCommerce Tmd POS
 * @author    The multimedia designers
 * @link      https://www.themultimediadesigner.com/
 */
defined( 'ABSPATH' ) || exit;

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
  exit;
}

global $wpdb;
/* 
 * prefixed with $wpdb->prefix from the database
 */
$table_name1 = $wpdb->prefix . 'tmd_pos';
$table_name2 = $wpdb->prefix . 'tmd_pos_option';
$table_name3 = $wpdb->prefix . 'tmd_pos_order';

// drop the table from the database.
$wpdb->query( "DROP TABLE IF EXISTS $table_name1" );
$wpdb->query( "DROP TABLE IF EXISTS $table_name2" );
$wpdb->query( "DROP TABLE IF EXISTS $table_name3" );
