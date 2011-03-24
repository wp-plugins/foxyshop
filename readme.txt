=== FoxyShop ===
Contributors: sparkweb
Donate link: http://www.foxy-shop.com/contact/
Tags: foxycart, shopping, cart, inventory, management
Requires at least: 3.0
Tested up to: 3.1
Stable tag: 2.0

FoxyShop is a full integration for FoxyCart and WordPress, providing a robust shopping cart and inventory management tool.

== Description ==

FoxyShop is a complete shopping cart solution for WordPress. This plugin connects to FoxyCart's excellent shopping cart service and will allow you to manage your inventory from a WordPress backend. FoxyShop is designed for complete flexibility so that WordPress developers can use the plugin's helpful library and sample code to quickly configure and implement almost any type of ecommerce solution.

Visit [www.foxy-shop.com](http://www.foxy-shop.com/) for full documentation and instructions.

[youtube http://www.youtube.com/watch?v=roYF1CFAyUA]

= Features: =
* Manage your product inventory within the WordPress structure
* Full product information: name, price, product code, shipping weight, shipping category, minimum and maximum quantities, product description, and short product description and lots more.
* Multiple images per product with popup slideshow
* Dedicated image uploader, improves default WordPress process
* Set up product categories and subcategories. Categories can be ordered alphabetically or by a custom sort.
* Complete flexibility for product variations and pricing. Chainable variations are supported so that some options aren't available until others are selected. Price is automatically updated when different selections are made. Images can be tied to variations.
* Full product sorting capabilities: sort by name, price, date, or customize your sorting with a drag-and-drop sorter.
* Widget support for featured categories
* Discounts available in all the FoxyCart flavors: Quantity and Price, Amount and Percentage, Single Units, All Units, Buy One Get One Free, etc. Lots of flexibility.
* Sale pricing with optional date controls
* Coupon code support
* Multiple shipping recipients
* Custom file upload lets your customers send you a file when adding to the shopping cart
* Assign related product for product upselling
* Bundled products let you add more than one product to the cart at once
* Ability to hide products and categories
* Both english and metric weight support
* Support for international currencies
* Custom fields allowing for your own customization
* Custom FoxyCart domain support
* Field validation to prevent form tampering
* Shortcode support for "Add to Cart" links
* Subscription and downloadable product support
* Manage your subscriptions list using FoxyCart's API
* View orders and customers in your WordPress admin
* FoxyCart Single Sign On (SSO) support lets you sync your WordPress users and FoxyCart customer list
* Inventory level management with auto-updating features
* Creates Google Product Search datafeed
* Automated product sitemaps
* Product importing and duplication support available
* Supports FoxyCart versions 0.70 and 0.7.1 and includes product images in the shopping cart




== Installation ==

Copy the folder to your WordPress 
'*/wp-content/plugins/*' folder.

1. Activate the '*FoxyShop*' plugin in your WordPress admin '*Plugins*'
1. Go to '*Products / Manage Settings*' in your WordPress admin.
1. Enter your FoxyCart domain.
1. Copy and paste the supplied API key into your FoxyCart admin area (Advanced) and check the "enable verification" checkbox.
1. All the other settings are optional.

== Frequently Asked Questions ==

= How can I edit product pages? =
Copy the files from the '*themefiles*' folder inside the plugin into your theme file. You may now edit these files. Refer to the documentation or comments within these theme files for more directions.

= Can I import my products? =
FoxyShop works very nicely with the [CSV Importer](http://wordpress.org/extend/plugins/csv-importer/) plugin. [Click here](http://www.foxy-shop.com/2011/03/importing-products/) for a sample import file and lots of tips for a successful import.

= Any other suggestioned plugins? =
The [Duplicate Posts](http://wordpress.org/extend/plugins/duplicate-post/) plugin works great for letting you quickly copy products.

= Can I change the slug from 'products' to something else? =
Sure! Starting in version 2.0 you can easily do this. Just put this code in your wp-config.php file:

define('FOXYSHOP_PRODUCTS_SLUG','yourproductslug');
define('FOXYSHOP_PRODUCT_CATEGORY_SLUG','yourproductcategoryslug');

After making this change, be sure that you flush your permalink structure to enable the new settings. To do this, go to Settings / Permalinks and click "Save Changes".


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

= 2.0 =
* New Feature: Order Management (Using FoxyCart API)
* New Feature: Subscription Management (Using FoxyCart API)
* New Feature: Customer Management (Using FoxyCart API)
* New Feature: WordPress User Single Sign On Support (syncs with your WordPress users and lets them check out)
* Subscription support improved in product admin
* Added option for advanced Google Analytics code to assist in setting up your FoxyCart and Analytics sync (goals and funnels)
* Made it easier to change your product and category slugs by letting you define these in wp-config.php
* Added support for the FoxyCart feature "p:5" to set price directly on a variation. Note that it doesn't work well with sale prices.
* Nice-name support for shipping category code list
* Updated PrettyPhoto files to version 3.0.3
* Bugfix for uploading images on a WordPress install not in the root
* Updated plugin screenshots
* See [Release Notes](http://www.foxy-shop.com/2011/03/version-2-0-manage-orders-and-subscriptions/) for more details
[youtube http://www.youtube.com/watch?v=JFj6TFXC4Lk]

= 1.60 =
* New Feature: Manage Inventory Levels
* Added 'url' to fields passed to cart. Makes the image a clickable link. For 0.7.1 only. 
* Updated FoxyCart Include Files for 0.7.0
* Fixed price validation bug in bundled products.
* See [Release Notes](http://www.foxy-shop.com/2011/03/version-1-60-inventory-levels/) for more details

= 1.51 =
* Updated FoxyCart Include Files to ColorBox 1.3.16
* Updated jQuery to 1.4.4 to fix IE 9 problem with 1.4.2. Note 1.5 and 1.5.1 still doesn't work with FoxyCart.
* Tweaked the image key feature so that the current active image will also be the cart image in FoxyCart.
* See [Release Notes](http://www.foxy-shop.com/2011/03/version-1-51-foxycart-include-update-for-ie-9-compatibility/) for more details

= 1.50 =
* New Feature: Product images based on dropdown selection (image key)
* Thumbnail images now available in the admin product selection screen. Makes identifying your products easier.
* Improved internationalization features. Now supports local currency.
* Variations now stored inside of the main product variable. Easier to access when doing advanced integrations.
* See [Release Notes](http://www.foxy-shop.com/2011/03/version-1-5-variation-images/) for more details

= 1.41 =
* Bug Fix: Weight was not updating properly
* Added variables to make temporarily changing the slug a bit easier
* Removed the small gallery image overlay from the PrettyPhoto lightbox. It looks smoother now.
* See [Release Notes](http://www.foxy-shop.com/2011/03/version-1-4-1-maintenance-release/) for more details

= 1.4 =
* New Feature: Improved Product Image Support with Dedicated Bar
* New Feature: Product Sitemap Creation
* Calendar popups added for sale and subscription date selectors
* Theme file jQuery updates for sub-category wrapping
* Template redirect adjustments for some (wonky) WP installs
* Added settings link to WP plugin page
* Added base name customization option to breadcrumbs function (Products)
* Improved post thumbnail setting by attaching to 'after_setup_theme' hook. It gets turned on more reliably now.
* See [Release Notes](http://www.foxy-shop.com/2011/03/version-1-4-improved-image-support/) for more details

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