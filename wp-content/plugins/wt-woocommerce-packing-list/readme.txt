=== WooCommerce PDF Invoices, Packing Slips, Delivery Notes & Shipping Labels ===
Contributors: webtoffee
Version: 4.0.8
Tags: Label, Invoice, Packinglist, Invoice printing, Shipping, Packinglist printing, WooCommerce, Wordpress
Requires at least: 3.0.1
Requires PHP: 5.6
Tested up to: 5.4
Stable tag: 4.0.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WooCommerce PDF Invoices, Packing Slips, Delivery Notes & Shipping Labels

== Description ==

== Screenshots ==

== Changelog ==


= 4.0.8 =
*  [Improvement] Separate Email option(trigger automatically based on settings or manual) added for Packinglist and Picklist.  
*  [Improvement] Email attachment option added for Packinglist and Picklist
*  [Improvement] Order by category option added in invoice
*  [Improvement] Italian language files added
*  [Improvement] Sequential order number compatibility in orders listing section of Picklist.
*  [Improvement] New filters added to documentation. And add_filter section added to code example block.
*  [Bug fix] Print button missing in email and MyAccount->Ordres, for WooCommerce latest version
*  [Bug fix] Meta duplicate comparison fails when string contains some ascii values.
*  [Bug fix] Duplicate entries on picklist when group by category/order are not enabled.
*  [Bug fix] Network error issue while downloading PDF in some cases
*  [Bug fix] Broken PDF when multiple attachment on same mail
*  [Bug fix] Col span issue when some table columns are hidden



= 4.0.7 =
*  New filter wf_pklist_alter_print_margin_css added to alter print margin
*  New filter wf_pklist_alter_print_css added to alter print css
* [Bug fix] Extra line break within product table when variation data, product meta are empty
* [Bug fix] Product variants merged in picklist
* [Bug fix] Duplicate Meta data in certain cases for meta added via third party addons
* [Bug fix] Activation conflict with basic plugin

= 4.0.6 =
* Drop down menu converted from clickable to hover in edit order page
* [Bug fix] Total price excluded in price filter
* Restricted direct access of upload directory


= 4.0.5 =
* [Improvement] Included options for delete/download/scheduled delete of the temp files
* [Improvement] PDF option introduced for all documents
* [Improvement] Email attachment option added for proforma invoice
* [Improvement] Form validation improved
* [Improvement] Blocked all third party script tags from the HTML template for better security.
* [Improvement] Reduced temp storage
* [Improvement] Limited user capability for saving HTML document to only admins and shop owners
* [Improvement] New public filter added to alter order packages `wf_pklist_alter_order_packages`
* [Improvement] PHP 7.4 compatibility
* Tested OK with WooCommerce 3.9

= 4.0.4 =
* Introduced Proforma invoice
* Introduced Credit note
* Tested OK with Wordpress 5.3

= 4.0.3 =
* Introduced Pick list
* Optimized the PDF size to KBs
* Included option for watermarking with custom text
* Included option to edit/delete checkout fields
* [Bug fix] Image not found issue within customizer
* [Bug fix] Issue with Preview PDF for refunded orders
* [Bug fix] Missing Variation data
* Added new filter to alter the generated file for print/PDF, wf_pklist_alter_pdf_file_name
* Compatibility with Sequential order number


= 4.0.2 =
* Tested OK with WooCommerce 3.7
* PDF preview option added.
* Copy address from woocommerce option added.
* Tax column option in invoice product table.
* Bug fix and usability improvements. 

= 4.0.1 =
* [Bug fix] Email attachment missing for admin orders
* [Bug fix] Non alphabet character issue in additional checkout fields
* [Bug fix] Fixed PDF invoice template bug 

= 4.0.0 =
* UI/UX improvements
* Improved Performance
* Improved RTL support
* Improved WPML support
* Reduced plugin size


= Contact Us =
Support: https://www.webtoffee.com/category/documentation/print-invoices-packing-list-labels-for-woocommerce/
Or make use of questions and comments section in individual product page.

== Installation ==
https://www.webtoffee.com/how-to-download-install-update-woocommerce-plugin/


== Tutorial / Manual ==
https://www.webtoffee.com/product/woocommerce-pdf-invoices-packing-slips/

== Upgrade Notice ==

= 4.0.8 =
*  [Improvement] Separate Email option(trigger automatically based on settings or manual) added for Packinglist and Picklist.  
*  [Improvement] Email attachment option added for Packinglist and Picklist
*  [Improvement] Order by category option added in invoice
*  [Improvement] Italian language files added
*  [Improvement] Sequential order number compatibility in orders listing section of Picklist.
*  [Improvement] New filters added to documentation. And add_filter section added to code example block.
*  [Bug fix] Print button missing in email and MyAccount->Ordres, for WooCommerce latest version
*  [Bug fix] Meta duplicate comparison fails when string contains some ascii values.
*  [Bug fix] Duplicate entries on picklist when group by category/order are not enabled.
*  [Bug fix] Network error issue while downloading PDF in some cases
*  [Bug fix] Broken PDF when multiple attachment on same mail
*  [Bug fix] Col span issue when some table columns are hidden