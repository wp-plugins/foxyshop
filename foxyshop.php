<?php /*

**************************************************************************
Plugin Name: FoxyShop
Plugin URI: http://www.foxy-shop.com/
Description: FoxyShop is a full integration for FoxyCart and WordPress, providing a robust shopping cart and inventory management tool.
Author: SparkWeb Interactive, Inc.
Version: 1.1
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

Thanks you for using this plugin. Please see www.foxy-shop.com for 
installation instructions and lots of helpful advice on how to get
the most out of FoxyShop.

**************************************************************************/

//Setup Variables
define('FOXYSHOP_DIR',WP_PLUGIN_URL."/foxyshop");
define('FOXYSHOP_PATH', dirname(__FILE__));
$foxyshop_settings = unserialize(get_option("foxyshop_settings"));

//If you don't need Widgets or Shortcodes you can comment out these lines:
include('widgetcode.php');
include('shortcodesettings.php');

//Put FoxyCart includes and jQuery on public pages
if (!is_admin()) {
	if ($foxyshop_settings['use_jquery']) add_action('init', 'foxyshop_insert_jquery');
	add_action('wp_head', 'foxyshop_insert_foxycart_files');
}

//Put Foxyshop Styles on front pages and load in the admin styles as well
if (is_admin()) {
	wp_enqueue_style('meta_css', FOXYSHOP_DIR . '/css/meta.css');
} else {
	wp_enqueue_style('foxyshop_css', FOXYSHOP_DIR . '/css/foxyshop.css');
	if ($foxyshop_settings['ga']) add_action('wp_footer', 'foxyshop_insert_google_analytics', 100);
}


//Custom Post Type and Taxonomy
include_once('customposttype.php');

//Settings Page
add_action('admin_menu', 'foxyshop_settings_menu');
add_action('admin_init', 'set_foxyshop_settings');
include_once('settings.php');

//Main Functions
include('helperfunctions.php');

//Template Fallback (files are in /themefiles/)
include('templateredirect.php');

//Plugin Activation Function
register_activation_hook(__FILE__, 'foxyshop_activation');
//register_deactivation_hook( __FILE__, 'foxyshop_deactivation' );

function foxyshop_activation() {
	add_option('foxyshop_set_rewrite_rules',"1");
}
function foxyshop_deactivation() {
	flush_rewrite_rules();
}
?>