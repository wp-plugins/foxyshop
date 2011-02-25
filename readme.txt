=== FoxyShop ===
Contributors: sparkweb
Donate link: http://www.foxy-shop.com/contact/
Tags: foxycart, shopping, cart, inventory, management
Requires at least: 3.0
Tested up to: 3.1
Stable tag: 1.2

FoxyShop is a full integration for FoxyCart and WordPress, providing a robust shopping cart and inventory management tool.

== Description ==

FoxyShop is a complete shopping cart solution for WordPress. This plugin connects to FoxyCart's excellent shopping cart service and will allow you to manage your inventory from a WordPress backend. FoxyShop is designed for complete flexibility so that WordPress developers can use the plugin's helpful library and sample code to quickly configure and implement almost any shopping cart solution.

Visit [www.foxy-shop.com](http://www.foxy-shop.com/) for more details and full documentation and instructions.

= Features: =
* Manage your product inventory within the WordPress structure
* Full product information: name, price, product code, shipping weight, shipping category, minimum and maximum quantities, product description, and short product description.
* Multiple images with popup slideshow
* Complete flexibility for product variations and pricing. Chainable variations are supported so that some options aren't available until others are selected. Price is automatically updated when different selections are made.
* Full product sorting capabilities: sort by name, price, date, or customize your sorting with a drag-and-drop sorter.
* Widget support for featured categories
* Shortcode support for "Add to Cart" links
* Subscription and downloadable product support
* Discounts available in all the FoxyCart flavors: Quantity and Price, Amount and Percentage, Single Units, All Units, Buy One Get One Free, etc. Lots of flexibility.
* Sale pricing with optional date controls
* Coupon code support
* Multiple shipping recipients
* Custom file upload lets your customers send you a file when adding to the shopping cart
* Related product suggestions
* Bundled products--add more than one product to the cart at once
* Ability to hide products and categories
* Both english and metric weight support
* Custom fields allowing for your own customization
* Custom FoxyCart domain support
* Product validation support to prevent form tampering
* Automated Google Product Search Data-feed
* Supports FoxyCart versions 0.70 and 0.7.1 and includes product images in the shopping cart




== Installation ==

Copy the folder to your WordPress 
'*/wp-content/plugins/*' folder.

1. Activate the '*FoxyShop*' plugin in your WordPress admin '*Plugins*'
1. Go to '*Products / Manage Settings*' in your WordPress admin area.
1. Enter your FoxyCart domain.
1. Copy and paste the supplied API key into your FoxyCart admin area (Advanced) and check the "enable verification" checkbox.
1. All the other settings are optional.

== Frequently Asked Questions ==

= How can I edit product pages? =

Copy the files from the '*themefiles*' folder inside the plugin into your theme file. You may now edit these files. Refer to the documentation or comments within these theme files for more directions.



== Screenshots ==

1. Admin Product List
2. Create New Product Screen
3. Product Categories
4. Settings Screen
5. Public Product View
6. Public Category View

== Other == 

Plugin URI: http://www.foxy-shop.com/<br />
Author: David Hollander<br />
Author URI: http://www.foxy-shop.com/<br />

== Changelog ==

= 1.2 =
* Added custom product sorting with drag and drop capability as well as an option to select your sorting preference. Note that if you have custom theme files you may need to copy the new sorting routine on foxyshop-all-products.php, foxyshop-search.php, and foxyshop-single-category.php in order to take full advantage of the new sorting power.
* Internationalization Tweaks
* Product feed moved to its own page
* Squashed a bug with special characters in title name causing validation failure
* Squashed a bug with negative price adjustments

= 1.1 =
* Added Internationalization Support (contact me if you can help translate)
* Squashed bug where commas entered in price field would invalidate input
* Squashed bug in sale date calculation method

= 1.0 =
* Initial release

== Upgrade Notice ==

None