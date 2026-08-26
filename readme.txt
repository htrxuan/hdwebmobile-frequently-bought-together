=== HDWebmobile Frequently Bought Together ===
Contributors: htrxuan
Donate link: https://paypal.me/htrxuan/20
Tags: woocommerce, frequently bought together, cross-sell, upsell, bundle
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
Requires Plugins: woocommerce
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An Amazon-style "Frequently Bought Together" widget for WooCommerce. Add several related products to cart in one click.

== Description ==

HDWebmobile Frequently Bought Together shows a widget on the product page with the current product plus a few companion products you choose, each with its own checkbox and a running total. Shoppers can uncheck anything they don't want, then add everything left checked to their cart with a single click — no repeated "Add to cart" clicks, and no page reload. Under the hood, that single click sends one WooCommerce Store API batch request rather than looping several add-to-cart calls, keeping the Mini-Cart block's total accurate and in sync immediately.

= Key Features =
* One click adds every checked item to the cart via a single Store API batch request — not a series of separate add-to-cart calls
* Checkbox totals recalculate instantly in the browser, no AJAX round trip needed just to update the price
* Mini-Cart block updates immediately after adding, with no page reload
* Admin picker reuses WooCommerce's own product search field (the same one used for Upsells/Cross-sells), so it's instantly familiar
* A dedicated Gutenberg block for placing the widget on block-based single-product templates, alongside the classic hook that covers classic themes automatically
* Works for guests and logged-in shoppers alike

= Limitations (please read before installing) =
* Simple products only — both the product the widget appears on and the companion products it suggests must be simple products. Variable, grouped, and external products can't be picked as companions, and the widget doesn't appear on their own pages either, since an "Add all to cart" click needs a concrete product with no variation to resolve
* Manual curation only — you pick up to 4 companion products per product yourself; there's no automatic "customers who bought this also bought" suggestion engine based on order history in this version
* No bundle-discount pricing — every item is added at its normal price; if you want a bundle with its own discounted price, see our HDWebmobile Mix & Match Bundles plugin instead

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/hdwebmobile-frequently-bought-together` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress. WooCommerce must already be installed and active.
3. Edit a simple product, open the new "Frequently Bought Together" tab in Product Data, and pick up to 4 companion products.

== How to Use ==

= 1. Open a simple product for editing =
Go to **Products** and open any *simple* product (the tab only appears for simple products, since companions must be simple products too — see Limitations).

= 2. Pick companion products =
Click the new **Frequently Bought Together** tab under Product Data (Screenshot 1). Search and select up to 4 products to suggest alongside this one. The search only offers other simple products — variable, grouped, and external products won't appear, since the widget's one-click "Add all to cart" needs a concrete product with no variation to choose. Click **Update** to save.

= 3. What shoppers see =
On that product's page, a "Frequently Bought Together" widget appears automatically below the Add to Cart button (Screenshot 2), showing the current product plus your chosen companions, each with a checkbox (all checked by default) and its price, and a running total.

= 4. Add the block to a block-based single-product template (optional) =
If your theme uses the block-based Single Product template, you can also insert the **Frequently Bought Together** block (WooCommerce category) directly into it via the Site Editor for more control over its placement — it renders the same widget, driven by the same product data.

= 5. Add everything to cart in one click =
Shoppers can uncheck anything they don't want — the total updates instantly (Screenshot 3). Clicking **Add N to Cart** adds every checked item to the real WooCommerce cart in a single request; the Mini-Cart block updates immediately with no page reload (Screenshot 4).

== Screenshots ==

1. The "Frequently Bought Together" tab in Product Data, picking companion products.
2. The widget on a real product page, with the anchor product and its companions.
3. Unchecking a companion product updates the running total instantly.
4. The Mini-Cart updating immediately after adding all checked items to the cart.

== Changelog ==

= 1.0.0 =
* Initial release: single-batch-request add-to-cart, admin companion-product picker reusing WooCommerce's own product search, Gutenberg block for the single-product template, classic-theme support via the classic add-to-cart hook.
