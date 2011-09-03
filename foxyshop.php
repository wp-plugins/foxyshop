<?php /*

**************************************************************************
Plugin Name: FoxyShop
Plugin URI: http://www.foxy-shop.com/
Description: FoxyShop is a full integration for FoxyCart and WordPress, providing a robust shopping cart and inventory management tool.
Author: SparkWeb Interactive, Inc.
Version: 3.2
Author URI: http://www.foxy-shop.com/

**************************************************************************

Copyright (C) 2011 SparkWeb Interactive, Inc.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

**************************************************************************

Thanks you for using this plugin. Please see http://www.foxy-shop.com/ for 
installation instructions and lots of helpful advice on how to get
the most out of FoxyShop.

**************************************************************************/

//Setup Plugin Variables
define('FOXYSHOP_VERSION', "3.2");
define('FOXYSHOP_DIR',WP_PLUGIN_URL."/foxyshop");
define('FOXYSHOP_PATH', dirname(__FILE__));
$foxyshop_document_root = $_SERVER['DOCUMENT_ROOT'];
if ($foxyshop_document_root == "" || $foxyshop_document_root == "/") $foxyshop_document_root = str_replace("/wp-content/plugins/foxyshop","",FOXYSHOP_PATH);
if (!defined('FOXYSHOP_DOCUMENT_ROOT')) define('FOXYSHOP_DOCUMENT_ROOT', $foxyshop_document_root);
if (!defined('FOXYSHOP_PRODUCTS_SLUG')) define('FOXYSHOP_PRODUCTS_SLUG','products');
if (!defined('FOXYSHOP_PRODUCT_CATEGORY_SLUG')) define('FOXYSHOP_PRODUCT_CATEGORY_SLUG','product-cat');
if (!defined('FOXYSHOP_JQUERY_VERSION')) define('FOXYSHOP_JQUERY_VERSION','1.6.2');
if (!defined('FOXYSHOP_PRODUCT_NAME_SINGULAR')) define('FOXYSHOP_PRODUCT_NAME_SINGULAR','Product');
if (!defined('FOXYSHOP_PRODUCT_NAME_PLURAL')) define('FOXYSHOP_PRODUCT_NAME_PLURAL','Products');
if (!defined('FOXYSHOP_URL_BASE')) define('FOXYSHOP_URL_BASE','');
$foxyshop_settings = unserialize(get_option("foxyshop_settings"));
if (!is_array($foxyshop_settings)) $foxyshop_settings = array("domain" => "", "sort_key" => "", "enable_sso" => "", "generate_feed" => "", "manage_inventory_levels" => "", "enable_subscriptions" => "");
if (!array_key_exists('foxyshop_version',$foxyshop_settings)) $foxyshop_settings['foxyshop_version'] = '';
$foxyshop_category_sort = unserialize(get_option('foxyshop_category_sort'));

//Checks for Old Plugin Version and Perform Upgrade
if ($foxyshop_settings['foxyshop_version'] != FOXYSHOP_VERSION) add_action('admin_init', 'foxyshop_activation');

//Sets the Locale for Currency Internationalization
setlocale(LC_MONETARY, (array_key_exists('locale_code',$foxyshop_settings) ? $foxyshop_settings['locale_code'] : get_locale()));
$foxyshop_localsettings = localeconv();
if ($foxyshop_localsettings['int_curr_symbol'] == "") setlocale(LC_MONETARY, 'en_US');

//Flushes Rewrite Rules if Structure Has Changed
add_action('init', 'foxyshop_check_rewrite_rules', 99);

//Widgets and Shortcodes support
include_once('widgetcode.php');
include_once('shortcodesettings.php');

//Load Admin Scripts and Styles
if (is_admin()) {
	add_action('admin_enqueue_scripts', 'foxyshop_load_admin_scripts');

//Load FoxyShop Scripts and Styles on Public Site
} else {
	if ($foxyshop_settings['use_jquery']) add_action('init', 'foxyshop_insert_jquery');
	if (!defined('FOXYSHOP_SKIP_FOXYCART_INCLUDES')) add_action('wp_head', 'foxyshop_insert_foxycart_files');
	wp_enqueue_style('foxyshop_css', FOXYSHOP_DIR . '/css/foxyshop.css', array(), FOXYSHOP_VERSION);
	if ($foxyshop_settings['ga']) add_action('wp_footer', 'foxyshop_insert_google_analytics', 100);
}

//Custom Post Type and Taxonomy
include_once('customposttype.php');

//Custom Product Sorting
include_once('customsorting.php');

//Custom Category Sorting
include_once('categorysorting.php');

//FoxyCart API Feeds
if ($foxyshop_settings['domain']) {

	//Orders
	include_once('orders.php');

	//Customers
	include_once('customers.php');

	//Subscriptions
	if ($foxyshop_settings['enable_subscriptions']) {
		include_once('subscriptions.php');
	}
}

//Inventory Management
if ($foxyshop_settings['manage_inventory_levels']) {
	include_once('inventory.php');
}

//Generate Product Feed
if ($foxyshop_settings['generate_feed']) {
	include_once('productfeed.php');
}

//Settings Page
include_once('settings.php');

//Single Sign On
if ($foxyshop_settings['enable_sso']) {
	include_once('sso.php');
}

//Display Settings Link on Plugin Screen
add_filter('plugin_action_links', 'foxyshop_plugin_action_links', 10, 2);

//Admin Functions
include_once('adminfunctions.php');
include_once('adminajax.php');

//Frontend Helper Functions
include_once('helperfunctions.php');

//Template Redirect (files are in /themefiles/)
include_once('templateredirect.php');

//Plugin Activation Functions
register_activation_hook(__FILE__, 'foxyshop_activation');
register_deactivation_hook( __FILE__, 'foxyshop_deactivation' );
?>