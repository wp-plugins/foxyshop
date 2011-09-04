=== FoxyShop ===
Contributors: sparkweb
Donate link: http://www.foxy-shop.com/contact/
Tags: foxycart, shopping, cart, inventory, management, ecommerce, selling, subscription
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 3.2
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
1. All other settings are optional. See [Docs](http://www.foxy-shop.com/documentation/installation-instructions/) for more details.

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

= 3.2 =
* SSO support has been reworked to be more flexible. There's now a filter called foxyshop_save_sso_to_foxycart which lets you hook your own function in to easily save more metadata.
* Cart session fields like cart, coupon, etc should not have verification attached. This has been removed.
* Fixed broken "no image" images in admin view
* Added action hook "foxyshop_save_product" for other integrations to hook into product saving process
* On the order management screen you can now access the FoxyCart receipt and the printable receipt on an order-by-order basis. Just click the FoxyCart icon for FoxyCart receipt and the Order number for the printable receipt.
* Added two new hookable actions to the order management window: foxyshop_order_search_buttons and foxyshop_order_line_item.
* Backed off the aggressive backup parsing for installs that are using "Month and Name" or "Month and Day" for their permalink structure.
* Fixed ikey targeting which had stopped working after 3.1 variation updates
* See [Release Notes](http://www.foxy-shop.com/2011/09/version-3-2-youll-be-hooked/ ) for more details

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

= 2.9.1 =
* Fixed activation error.

= 2.9 =
* Added full category and product display shortcodes that can be placed on any page or post
* Added a widget to display category lists in the sidebar
* Fixed image upload problems where WordPress was not installed in the root
* Visual updates to the settings page
* Changed Admin menu icon to FoxyShop "F"
* Looks for FOXYSHOP_SKIP_FOXYCART_INCLUDES before loading the includes automatically. Set constant in wp-config.php.
* Added FOXYSHOP_PRODUCT_NAME_SINGULAR and FOXYSHOP_PRODUCT_NAME_PLURAL constant options to personalize your install with other names for "product"
* Added FOXYSHOP_URL_BASE constant for fixing IIS permalink troubles
* Added FOXYSHOP_CURL_SSL_VERIFYPEER constant to set to false if you have trouble accessing the FoxyShop API
* Admin update: wording on save/update message to indicate products
* Product attachments now filtered to ensure only images are returned
* On product page, checking to make sure that fcc object is initialized to avoid JavaScript errors
* Fixed "Undefined" prop error when adding related or bundled products
* See [Release Notes](http://www.foxy-shop.com/2011/08/version-2-9-product-shortcodes/) for more details

= 2.8.2 =
* Gorgeous new FoxyShop logo on settings page
* New option available in datafeed endpoint to add WordPress accounts after checkout
* Bugfix: SSO redirects were failing after login.

= 2.8.1 =
* Fixed bug where product and category sorting was only showing up for editors. Now available for editors and above.

= 2.8 =
* Added printable invoices to order page. Template is foxyshop-receipt.php.
* editors can now see the Category and Product sorting screens.
* Completely rebuilt upload functions for further security upgrades. Those using custom user uploads will need to read the release notes for upgrade instructions.
* See [Release Notes](http://www.foxy-shop.com/2011/07/version-2-8-printable-invoices-and-security-updates/) for more details

= 2.7.1 =
* Removed 404 headers that WordPress was generating on some FoxyShop store pages.
* Added "foxyshop" class to the WordPress body_class() function for some styling assistance.
* Bugfix: Products without variations or product code changes weren't getting inventory updates. Fixed!
* Upgraded to jQuery 1.6.2
* Removed pluggable.php early include and subscription array setup on every page load. Only loaded when necessary.
* You can now set constant FOXYSHOP_PRODUCT_TAGS in wp-config to allow tags on products.
* Added security feature to harden custom variation uploads.

= 2.7 =
* Added a variation field called Descriptive Text to allow text-only areas in the variation groups -- describe your variations or give instructions.
* Added new function foxyshop_simple_category_children() for simple category listing.
* Updated multiship.jquery.js to version 2.2 for better compatibility with hmac verification
* Small CSS change for more consistent non-bullets in breadcrumbs
* Removing sale price no longer causes PHP number_format error
* Fixed FOXYSHOP_SKIP_VERIFICATION feature
* See [Release Notes](http://www.foxy-shop.com/2011/06/version-2-7-variation-upgrades-and-other-tweaks/) for more details

= 2.6.1 =
* Corrected .prop() issue which was causing the admin product tool not to load completely in Internet Explorer.
* Small JavaScript fix: activating Buy Now button when when switching from out of stock to fully stocked product.

= 2.6 =
* Subscription data is now stored in the WordPress user record when you are using SSO. Admins can see current subscriptions in the User Profile.
* Updated subscription start and end date fields to accept natural language strtotime() arguments.
* Now passing quantity_max and quantity_min to the cart
* Added option to send inventory alert emails
* Updated inventory feature so that quantities can't be added to the cart above what is in stock
* Added customer order history function for WordPress developers.
* Fixed some WordPress 3.2 styling and jQuery issues. Now fully compatible.
* You may set bundled products to use their full value rather than $0 by putting this in your wp-config.php file: define('FOXYSHOP_BUNDLED_PRODUCT_FULL_PRICE', 1);
* Added warning for PHP users whose version is less than 5.1.2. The HMAC hash will break!
* If you absolutely need to disable the HMAC verification, you can now set this constant in your wp-config.php file: define('FOXYSHOP_SKIP_VERIFICATION', 1) - but this is NOT recommended!
* Constant TEMPLATEPATH changed to STYLESHEETPATH - if you are using a child theme the template files can now live in the child theme, though the parent theme will still be checked.
* Set constant FOXYSHOP_PRODUCT_COMMENTS in wp-config to allow comments on products.
* See [Release Notes](http://www.foxy-shop.com/2011/06/version-2-6-inventory-and-subscription-upgrades/) for more details

[View Archived Changelog](http://www.foxy-shop.com/changelog-archives/)


== Upgrade Notice ==

= 3.0 =
If you have a lot of products, the reactivation process can take a bit. Please be patient. If it times out, please try again. All product variations are being upgraded.