=== FoxyShop ===
Contributors: sparkweb
Donate link: http://www.foxy-shop.com/contact/
Tags: foxycart, shopping, cart, inventory, management, ecommerce, selling, subscription
Requires at least: 3.0
Tested up to: 3.3
Stable tag: 3.5.1
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

= 3.5.1 =
* Some plugin filenames renamed for WordPress Multisite support
* Update to get rid of WP notice in 3.3
* Changed variation newline splitting method for broader support
* Added pricing adjustments to product price in receipt template
* Added FoxyCart template update feature for 0.7.2
* Added customer, transaction, and subscription attribute support for 0.7.2 

= 3.5 =
* New Feature: Saved variations allow you to setup a variation once and reuse it on multiple products 
* Product paging on the All Products page now works properly. Update your theme file if it has been customized.
* Rolled back filter that was removing spaces from variation modifiers. Caused inventory bug with codes that had spaces in them.
* Added uninstall.php to properly clean up after plugin files are deleted
* Recommended plugins tool improved
* Added more filters

= 3.4 =
* Added selectable product add-on feature
* Added tools page to the admin
* Added plugin recommendations to tools page
* Added tool to import/export FoxyShop settings
* Added uninstall instructions
* Added built-in meta fields in site header for social media sharing
* Added soft fail if error on API call
* Inventory section re-skinned and now easier to use. Levels can be edited directly from the inventory management page.
* Updated inventory values can be imported from a spreadsheet
* The views for Orders/Subscriptions/Customers have been reskinned for a more consistent user interface and are much faster now
* Product weight can now be disabled on a per-product basis if you don't want to pass weight to the cart
* SSO: Redirection URL now persistent throughout login/redirect process
* Browser title filter will be skipped if a title isn't set
* Orders and subscriptions will display results immediately without having to make an initial search
* Added depth option to category list widget
* Bugfix: category list widget wasn't saving top-level term
* Added lots of new filter hooks
* See [Release Notes](http://www.foxy-shop.com/2011/09/version-3-4-add-on-products/) for more details

= 3.3 =
* Added functionality for automated relevant products by "product tag"
* Added setup wizard to help new users sync with their FoxyCart accounts
* Added FoxyCart 0.7.2 support
* Added "Quantity Hide" feature. If selected for a product, it will hide the quantity box on the product page.
* Added new hook: foxyshop_setup_product_info (add your own values to $product)
* Added new hook: foxyshop_admin_product_details (add your own options to the product details metabox in the admin)
* Added new config values to set cURL timeout lengths
* Added ability to custom order the related products
* Added ability to pass in variations to the shortcode and the `foxyshop_product_link()` function
* Added `$beforeVariation` and `$afterVariation` arguments to the `foxyshop_product_variations()` function to allow more customization for theme developers
* Added setup option for "no missing image" - use this if you don't want to show images for some or all products
* Added a few JavaScript hooks to the ikey process in case you need to do things before or after the image is changed
* If your variation name is a price and is the same as the adjusted price, the price in () not be shown
* WordPress registrations are now automatically logged in after signup
* Changed unserialize to maybe_unserialize to prevent any caching conflicts during upgrade
* Cleaned up a PHP notice on first activation and streamlined the automated upgrade activation process
* More optimizations and standardizations to the template redirection function
* Bugfix: Removed a bit of testing code from the datafeed endpoint that was showing up where it shouldn't have been
* Added files for jqZoom to make custom integration easier
* Upgraded to jQuery 1.6.4
* Upgraded to jQueryUI Datepicker 1.8.16
* Upgraded to prettyPhoto 3.1.3
* See [Release Notes](http://www.foxy-shop.com/2011/09/version-3-3-tags-quantity-and-increased-flexibility/) for more details

= 3.2 =
* SSO support has been reworked to be more flexible. There's now a filter called foxyshop_save_sso_to_foxycart which lets you hook your own function in to easily save more metadata.
* Cart session fields like cart, coupon, etc should not have verification attached. This has been removed.
* Fixed broken "no image" images in admin view
* Added action hook "foxyshop_save_product" for other integrations to hook into product saving process
* On the order management screen you can now access the FoxyCart receipt and the printable receipt on an order-by-order basis. Just click the FoxyCart icon for FoxyCart receipt and the Order number for the printable receipt.
* Added two new hookable actions to the order management window: foxyshop_order_search_buttons and foxyshop_order_line_item.
* Backed off the aggressive backup parsing for installs that are using "Month and Name" or "Month and Day" for their permalink structure.
* Fixed ikey targeting which had stopped working after 3.1 variation updates
* See [Release Notes](http://www.foxy-shop.com/2011/09/version-3-2-youll-be-hooked/) for more details

= 3.1.1 =
* Fixed javascript error when jQuery acting in noConflict mode
* Image uploader wouldn't work if product title had an & - fixed!
* Started the enqueue earlier for the foxyshop.css file so it is easier to unregister if desired
* Rewrote `foxyshop_simple_category_children` to add depth option (show all levels of categories)

= 3.1 =
* Complete integration with bulk update plugin. Easily change product information for multiple products at once.
* Updated variation processor to successfully handle multiple forms/products on same page.
* Made changes to the product feed generator to accomodate the new Google Product Feed requirements
* Implemented FOXYSHOP_BUNDLED_PRODUCT_FULL_PRICE feature in the straight text link function
* Updated the method for saving "no photo" images to avoid accidental settings loss
* Added specific foxyshop body classes to help with CSS targeting
* Added admin checks for restricted variation names that will cause problems
* Fixed broken help messages on settings page
* Fixed problem where a space in custom upload field name would cause validation to fail
* Changed dashboard widget to show only live transactions
* Set dashboard widget to be visible for admins only
* See [Release Notes](http://www.foxy-shop.com/2011/08/version-3-1-bulk-updates-and-more/) for more details

= 3.0.1 =
* Fixes problem on category paging caused by new permalink parsing
* Fixes admin CSS conflict with other help text
* Updated datafeed "new user" option to update password and customer ID when saving a FoxyCart account to an existing WordPress account

= 3.0 =
* Added option for FoxyShop statistics widget on admin dashboard. Turn on feature in settings if desired.
* Complete overhaul of the variation system. Code has been optimized and improved. Variation order now draggable in admin.
* Improved usability of related and bundled products interface. Now a searchable dropdown.
* Improved permalink parsing. Most permalink structures now supported.
* Default menu order now set to be post ID instead of 0. Puts new products at end by default instead of "sort of at the front."
* Corrected bug where spaces in variation field names caused validation errors.
* Lots of scripting cleanup and optimization throughout the admin.
* Updated the jQuery UI datepicker script to 1.8.15.
* Added class names to the breadcrumbs for easier styling.
* See [Release Notes](http://www.foxy-shop.com/2011/08/version-3-0-a-big-overhaul/) for more details

[View Archived Changelog](http://www.foxy-shop.com/changelog-archives/)


== Upgrade Notice ==

= 3.5.1 =
This patches an issue with mac newlines in the variations as well several other fixes and patches.