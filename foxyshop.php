<?php /*

**************************************************************************
Plugin Name: FoxyShop
Plugin URI: http://www.foxy-shop.com/
Description: FoxyShop is a full integration for FoxyCart and WordPress, providing a robust shopping cart and inventory management tool.
Author: SparkWeb Interactive, Inc.
Version: 2.2.1
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
define('FOXYSHOP_DIR',WP_PLUGIN_URL."/foxyshop");
define('FOXYSHOP_PATH', dirname(__FILE__));
if (!defined('FOXYSHOP_PRODUCTS_SLUG')) define('FOXYSHOP_PRODUCTS_SLUG','products');
if (!defined('FOXYSHOP_PRODUCT_CATEGORY_SLUG')) define('FOXYSHOP_PRODUCT_CATEGORY_SLUG','product-cat');
$foxyshop_settings_defaults = array(
	'sort_key' => "menu_order",
	'generate_feed' => "",
	"products_per_page" => -1,
	"hide_subcat_children" => "on",
	"generate_product_sitemap" => "",
	"manage_inventory_levels" => "",
	"datafeed_url_key" => "",
	"inventory_alert_level" => 3,
	"ga_advanced" => "",
	"enable_sso" => "",
	"sso_account_required" => "",
	"browser_title_1" => "Products | " . get_bloginfo("name"),
	"browser_title_2" => "Product Categories | " . get_bloginfo("name"),
	"browser_title_3" => "%c | " . get_bloginfo("name"),
	"browser_title_4" => "%p | " . get_bloginfo("name"),
	"browser_title_5" => "Product Search | " . get_bloginfo("name")
);
$foxyshop_settings = wp_parse_args(unserialize(get_option("foxyshop_settings")), $foxyshop_settings_defaults);
$foxyshop_category_sort = (get_option('foxyshop_category_sort') ? unserialize(get_option('foxyshop_category_sort')) : array());
if ($foxyshop_settings['version'] == "0.70") $foxyshop_settings['version'] = "0.7.0";
if (array_key_exists('inventory_url_key', $foxyshop_settings)) $foxyshop_settings['datafeed_url_key'] = $foxyshop_settings['inventory_url_key'];

//Sets the Locale for Currency Internationalization
setlocale(LC_MONETARY, get_locale());
$foxyshop_localsettings = localeconv();
if ($foxyshop_localsettings['int_curr_symbol'] == "") setlocale(LC_MONETARY, 'en_US');

//Widgets and Shortcodes support
include('widgetcode.php');
include('shortcodesettings.php');

//Put FoxyCart includes and jQuery on public pages
if (!is_admin()) {
	if ($foxyshop_settings['use_jquery']) add_action('init', 'foxyshop_insert_jquery');
	add_action('wp_head', 'foxyshop_insert_foxycart_files');
}

//Put FoxyShop Styles on front pages and load in the admin styles as well
if (is_admin()) {
	wp_enqueue_style('foxyshop_admin_css', FOXYSHOP_DIR . '/css/foxyshop-admin.css');
} else {
	wp_enqueue_style('foxyshop_css', FOXYSHOP_DIR . '/css/foxyshop.css');
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
include('adminfunctions.php');
include('adminajax.php');

//Frontend Helper Functions
include('helperfunctions.php');

//Template Redirector (files are in /themefiles/)
include('templateredirect.php');

//Plugin Activation Functions
register_activation_hook(__FILE__, 'foxyshop_activation');
register_deactivation_hook( __FILE__, 'foxyshop_deactivation' );
?>