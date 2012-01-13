=== FoxyShop ===
Contributors: sparkweb
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2AHG2QMABF8SG
Tags: foxycart, shopping, cart, inventory, management, ecommerce, selling, subscription
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 3.9
FoxyShop provides a robust shopping cart and inventory management tool for use with FoxyCart's hosted e-commerce solution.

== Description ==

FoxyShop is a complete shopping cart solution for WordPress. This plugin connects to [FoxyCart's]((http://affiliate.foxycart.com/idevaffiliate.php?id=211)) hosted shopping cart service and will allow you to manage your inventory from a WordPress backend. FoxyShop was built to make the integration of FoxyCart and WordPress a breeze. Products are easy to manage and the flexible templates make it easy for developers to quickly build their storefront. The FoxyShop plugin is exhaustively documented, actively maintained, and completely free.

Visit [foxy-shop.com](http://www.foxy-shop.com/) for full documentation and instructions.

[youtube http://www.youtube.com/watch?v=HkS-J3XTGIk]

= Just a Few of the Many FoxyShop Features: =

* Fully customizable theme files and CSS
* Unlimited images per product with popup slideshow
* Widget support for featured categories
* Manage product inventory within the WordPress admin
* Set up product categories and subcategories
* Drag-and-drop product sorting
* Complete flexibility for product variations and pricing
* Sale pricing with optional date controls
* Digital products and subscriptions
* Allow WordPress users to checkout with their account
* Flexible product and category discounts
* Multiple shipping recipients
* Inventory management
* Internationalization support
* Field validation to prevent form tampering
* Lots more... [See Complete Feature List!](http://www.foxy-shop.com/foxyshop-features/)


== Installation ==

Copy the folder to your WordPress 
'*/wp-content/plugins/*' folder.

1. Activate the '*FoxyShop*' plugin in your WordPress admin '*Plugins*'
1. Go to '*Products / Manage Settings*' in your WordPress admin.
1. Enter your FoxyCart domain.
1. Copy and paste the supplied API key into your FoxyCart admin area (Advanced) and check the "enable verification" checkbox.
1. All other settings are optional. See [Docs](http://www.foxy-shop.com/documentation/installation-instructions/) for more details and a setup video.

== Frequently Asked Questions ==

There's a thorough FAQ section located at [http://www.foxy-shop.com/faq/](http://www.foxy-shop.com/faq/).


== Screenshots ==

1. Admin Settings
2. Product Listing
3. Product Management
4. Custom Product Order
5. Order Management
6. Inventory Levels


== Changelog ==

= 3.9 =
* Added cloud-zoom image slideshow support
* Added feature to skip FoxyCart includes on some or all pages
* Added filters for all role-based permission pages: you can now set custom roles for plugin access
* Added action hooks to fire after transactions have been archived
* Added filter for product slug within product setup (aids dynamic rewrite strings)
* Added filter for description field variation
* Changed default theme files so that FoxyShop header and footer files are outside of the foxyshop_container element
* Shipping category renamed "FoxyCart Category" to avoid confusion. Allow default category to load so that the category type can be set.
* Images uploaded while product is untitled will now be called "Image" instead of "Auto Draft"
* Added warnings so that the & and " characters can't be saved in the product code
* Bugfix: Minimum quantities that were entered without a maximum quantity weren't being saved
* Bugfix: Expired Google Products authentication now correctly prompts for renewal
* See [Release Notes](http://www.foxy-shop.com/2012/01/version-3-9-image-zooming-and-more/) for more details

= 3.8 =
* Updating price in bulk now lets you update dynamically with +, -, or by percentage.
* Added a helper function for updating inventory levels
* Added ability to show more than 50 orders per page for 0.7.1+ users with FOXYSHOP_API_ENTRIES_PER_PAGE constant
* Improved paging navigation for API processes (transactions, customers, subscriptions)
* Added an "Archive All" option for the Manage Orders screen
* Inventory codes can now be forced from the inventory import system even if they haven't been added before
* Inventory connectors available for QuickBooks (through ConsoliBYTE) and SmartTurn
* cURL connection error now displays actual error
* Transaction receipts outside the default date filter now viewable
* Added MinFraud score (0.7.2+) to transaction details
* Many datafeed template improvements
* "No Weight" can now be set as a system default if your products don't use weight
* Added a filter so that the date format can be adjusted on order page
* Admin nag bars are now limited to admins only
* Fixed jQuery error (variations) which was appearing in iOS 4
* Fixed some errors in the subscription datafeed process
* See [Release Notes](http://www.foxy-shop.com/2011/12/version-3-8-inventory-and-api-updates/) for more details

= 3.7.1 =
* Fixed a PHP error in the default theme file causing the datafeed to fail

= 3.7 =
* New: UPS WorldShip Integration
* New: Manage your Google Products directly from the FoxyShop admin
* Added built-in Google Product Feed fields
* Manage Google Product Feed with the Customer Field Bulk Editor
* Added 0.7.2 feature: sync with FoxyCart's list of downloadables for easier product entry
* Updated to jQuery 1.7.1
* FoxyShop Import/Export tool now includes saved variations
* Fix: reset post data after related product WP_Query loop
* Fix: uninstall issue and potential missing datafeed key on new installs
* See [Release Notes](http://www.foxy-shop.com/2011/11/version-3-7-google-products-integration-ups-worldship/) for more details

= 3.6.1 =
* Product sitemap is now a dynamic url + fixed namespace issue
* Added 0.7.2 feature to automatically pull your shipping category list from FoxyCart
* Added 0.7.2 receipt template caching functionality
* Updated to jQuery 1.7.0
* Made some admin styling tweaks
* See [Release Notes](http://www.foxy-shop.com/2011/11/version-3-6-1-dynamic-sitemaps/) for more details

= 3.6 =
* Important: Double serialization corrected!
* Updated datafeed so that FoxyShop processes are now protected within functions
* Added filters for the related products areas
* Added "Minimize" button to product variations to help manage when there are many
* Added 0.7.2 custom field search support to orders, subscriptions, and customers
* Added transaction template change support for subscriptions
* Subscription detail view now updated immediately after saving in admin
* Bugfix: Bundled text link wasn't adding url with numeric prefix
* Ampersand not allowed in product variation name. Doesn't work with validation.
* See [Release Notes](http://www.foxy-shop.com/2011/11/version-3-6-no-more-double-serialization/) for more details

= 3.5.4 =
* Fixing a variation javascript syntax error from 3.5.3

= 3.5.3 =
* Important: Now trims whitespace around product titles - fix for FoxyCart functionality change
* New Feature: Set manual order of add-on products
* Hidden variations won't be sent to cart or be counted for ikey
* Bugfix: Add-on products box not showing up in admin unless "Related Products" enabled. Fixed!
* See [Release Notes](http://www.foxy-shop.com/2011/10/version-3-5-3-whitespace-add-on-order/) for more details

= 3.5.2 =
* Added some responsive CSS to the admin so that variations will look better on smaller screens
* Removed erroneous "NOT FOUND" text on order management screen

= 3.5.1 =
* Some plugin filenames renamed for WordPress Multisite support
* Update to get rid of WP notice in 3.3
* Changed variation newline splitting method for broader support
* Added pricing adjustments to product price in receipt template
* Added FoxyCart template update feature for 0.7.2
* Added customer, transaction, and subscription attribute support for 0.7.2
* Added check on tools page to ensure that mcrypt is installed

= 3.5 =
* New Feature: Saved variations allow you to setup a variation once and reuse it on multiple products 
* Product paging on the All Products page now works properly. Update your theme file if it has been customized.
* Rolled back filter that was removing spaces from variation modifiers. Caused inventory bug with codes that had spaces in them.
* Added uninstall.php to properly clean up after plugin files are deleted
* Recommended plugins tool improved
* Added more filters
* See [Release Notes](http://www.foxy-shop.com/2011/10/version-3-5-saved-variations/) for more details


[View Archived Changelog](http://www.foxy-shop.com/changelog-archives/)


== Upgrade Notice ==

= 3.9 =
Added cloud-zoom feature, fine-grained permissions, and other features and bugfixes
