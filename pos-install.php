<?php
/**
 * tmd pos install tables
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

global $wpdb;
$tmd_pos_pay     = $wpdb->prefix . "tmd_pos";
$tmd_pos_order   = $wpdb->prefix . "tmd_pos_order";
$tmd_pos_option  = $wpdb->prefix . "tmd_pos_option";
$charset_collate = $wpdb->get_charset_collate();
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

$sql1 = "CREATE TABLE IF NOT EXISTS $tmd_pos_pay(
	`tmd_option_id` bigint(20) NOT NULL AUTO_INCREMENT,
	`tmd_option` varchar(200) NOT NULL,
	`tmd_option_value` longtext NOT NULL,
	PRIMARY KEY (`tmd_option_id`)
)$charset_collate";
dbDelta($sql1);

$sql2 = "CREATE TABLE IF NOT EXISTS $tmd_pos_order (
	`tmd_order_id` bigint(20) NOT NULL AUTO_INCREMENT,
	`order_meta` varchar(200) NOT NULL,
	`order_value` longtext NOT NULL,
	`order_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`tmd_order_id`)
)$charset_collate";
dbDelta($sql2);

$sql3 = "CREATE TABLE IF NOT EXISTS $tmd_pos_option (
	`tmd_option_id` bigint(20) NOT NULL AUTO_INCREMENT,
	`option_value` longtext NOT NULL,
	PRIMARY KEY (`tmd_option_id`)
)$charset_collate";
dbDelta($sql3);
