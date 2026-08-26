# HDWebmobile Frequently Bought Together

An Amazon-style "Frequently Bought Together" widget for WooCommerce. Add several related products to cart in one click.

- **WordPress.org:** https://wordpress.org/plugins/hdwebmobile-frequently-bought-together/
- **Requires:** WordPress 6.9+, WooCommerce, PHP 7.4+
- **License:** GPLv2 or later

## Description

HDWebmobile Frequently Bought Together shows a widget on the product page with the current product plus a few companion products you choose, each with its own checkbox and a running total. Shoppers can uncheck anything they don't want, then add everything left checked to their cart with a single click — no repeated "Add to cart" clicks, and no page reload. Under the hood, that single click sends one WooCommerce Store API batch request rather than looping several add-to-cart calls, keeping the Mini-Cart block's total accurate and in sync immediately.

## Features

* One click adds every checked item to the cart via a single Store API batch request — not a series of separate add-to-cart calls
* Checkbox totals recalculate instantly in the browser, no AJAX round trip needed just to update the price
* Mini-Cart block updates immediately after adding, with no page reload
* Admin picker reuses WooCommerce's own product search field (the same one used for Upsells/Cross-sells), so it's instantly familiar
* A dedicated Gutenberg block for placing the widget on block-based single-product templates, alongside the classic hook that covers classic themes automatically
* Works for guests and logged-in shoppers alike

## Development

Standard WordPress plugin structure:

```
hdwebmobile-frequently-bought-together.php    Bootstrap
includes/class-hdfbt-activator.php
includes/class-hdfbt-admin.php
includes/class-hdfbt-block.php
includes/class-hdfbt-core.php
includes/class-hdfbt-frontend.php
includes/class-hdfbt-hub.php
```

Part of the [HDWebmobile](https://hdwebmobile.com/plugins/) suite of focused, single-purpose WooCommerce plugins.

## License

GPLv2 or later. See [LICENSE](LICENSE).

