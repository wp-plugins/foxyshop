=== FoxyShop ===
Contributors: sparkweb
Donate link: http://www.foxy-shop.com/contact/
Tags: foxycart, shopping, cart, inventory, management
Requires at least: 3.0
Tested up to: 3.1
Stable tag: 1.3

FoxyShop is a full integration for FoxyCart and WordPress, providing a robust shopping cart and inventory management tool.

== Description ==

FoxyShop is a complete shopping cart solution for WordPress. This plugin connects to FoxyCart's excellent shopping cart service and will allow you to manage your inventory from a WordPress backend. FoxyShop is designed for complete flexibility so that WordPress developers can use the plugin's helpful library and sample code to quickly configure and implement almost any shopping cart solution.

Visit [www.foxy-shop.com](http://www.foxy-shop.com/) for more details and full documentation and instructions.

= Features: =
* Manage your product inventory within the WordPress structure
* Full product information: name, price, product code, shipping weight, shipping category, minimum and maximum quantities, product description, and short product description.
* Multiple images with popup slideshow
* Setup product categories and subcategories. Categories can be ordered alphabetically or by a custom sort.
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
* Bundled products-add more than one product to the cart at once
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

= 1.3 =
* Added product category sorting feature. Sort by alphabetical (default) or set a custom order.
* Added function 'foxyshop_is_product_new()' to identify products added within a given amount of time (different CSS or a new image)
* Added a configurable fallback "Back to Products" breadcrumb bar for products that don't live in a category (or if categories aren't being used)
* Added variation variable called "price:x" that will adjust the price on the page, but not in the cart.
* Added paging setting to admin.
* Added class names to the variation elements for easier jQuery targeting.
* Added support for more accurate default weight (oz/gm).
* Added option to show or hide child category's images on parent category page. Hidden by default.
* Cleaned up theme files, added new product-loop file so that product listing will be standard on all pages.
* New options will now always have defaults set without potential for error. No need to go to Settings and click Save after upgrading.
* Rearranged and improved the settings page.
* Changed sidebar icon to be more user-friendly
* Fix for revert button on product sorting page
* Codebase optimization
* See [Release Notes](http://www.foxy-shop.com/2011/03/version-1-3-big-update/) for more details

= 1.2 =
* Added custom product sorting with drag and drop capability as well as an option to select your sorting preference. Note that if you have custom theme files you may need to copy the new sorting routine on *foxyshop-all-products.php*, *foxyshop-search.php*, and *foxyshop-single-category.php* in order to take full advantage of the new sorting power.
* Internationalization Tweaks
* Product feed moved to its own page
* Multi-level category title fix
* Squashed a bug with special characters in title name causing validation failure
* Squashed a bug with negative price adjustments
* See [Release Notes](http://www.foxy-shop.com/2011/02/version-1-2-product-sorting/) for more details

= 1.1 =
* Added Internationalization Support (contact me if you can help translate)
* Squashed bug where commas entered in price field would invalidate input
* Squashed bug in sale date calculation method
* [Release Notes](http://www.foxy-shop.com/2011/02/version-1-1-bug-fixes/)

= 1.0 =
* Initial release

== Upgrade Notice ==

None