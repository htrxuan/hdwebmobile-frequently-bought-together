<?php

/**
 * Plugin Name: HDWebmobile Frequently Bought Together
 * Plugin URI: https://hdwebmobile.com/plugins/hdwebmobile-frequently-bought-together/
 * Description: An Amazon-style "frequently bought together" widget for the product page. Add several related products to cart in one click via a single WooCommerce Store API batch request.
 * Version: 1.0.0
 * Author: htrxuan - Han Tran
 * Author URI: https://hdwebmobile.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hdwebmobile-frequently-bought-together
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Requires at least: 6.9
 */

namespace htrxuan\hdfbt;

if (!defined('ABSPATH')) {
    exit;
}

define('HDFBT_VERSION', '1.0.0');
define('HDFBT_PLUGIN_FILE', __FILE__);
define('HDFBT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HDFBT_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once HDFBT_PLUGIN_DIR . 'includes/class-hdfbt-activator.php';

register_activation_hook(__FILE__, array(HDFBT_Activator::class, 'activate'));

add_action('before_woocommerce_init', function () {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', HDFBT_PLUGIN_FILE, true);
    }
});

add_action('plugins_loaded', function () {
    require_once HDFBT_PLUGIN_DIR . 'includes/class-hdfbt-core.php';
    HDFBT_Core::get_instance();
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $donate_link = '<a href="https://paypal.me/htrxuan/20" target="_blank" rel="noopener noreferrer">' . esc_html__('Donate', 'hdwebmobile-frequently-bought-together') . '</a>';
    array_unshift($links, $donate_link);
    return $links;
});
