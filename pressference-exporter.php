<?php
/**
 * Plugin Name: Pressference Exporter
 * Plugin URI: 
 * Description: Export Orders, Products, Customers, Reviews, Categories, Attributes, Tags and Coupon from WooCommerce store.
 * Version: 1.0.3
 * Author: Pressference
 * Author URI: https://pressference.com/
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: pf-exporter
 * Requires PHP: 7.2
 *
 * WC requires at least: 2.3
 * WC tested up to: 6.0
 */

if (!defined('ABSPATH')) {
	exit;// die('No script kiddies please!');
}

if (!defined('PRESSFERENCE_FILE')) {
	define('PRESSFERENCE_FILE', __FILE__);
}

if (!defined('PRESSFERENCE_TEXT_DOMAIN')) {
	define('PRESSFERENCE_TEXT_DOMAIN', 'pf-exporter');
}

if (!class_exists('Pressference_Exporter')) {
	include_once dirname(PRESSFERENCE_FILE).'/class/class-pressference-exporter.php';
}

$GLOBALS['pfe'] = Pressference_Exporter::init();
