<?php

namespace htrxuan\hdfbt;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renders the "Frequently Bought Together" widget and owns all its front-end assets.
 * Companion products are restricted to simple products only (enforced in class-hdfbt-admin.php
 * via WooCommerce's own data-exclude_type filter on the product-search field), so the widget
 * never needs to resolve a variation for an "Add all to cart" click -- every batch request item
 * is a plain {id, quantity: 1}.
 */
class HDFBT_Frontend
{

    private static $instance = null;

    /**
     * Tracks which anchor product IDs have already had the widget rendered during this request.
     * Mirrors the same de-dup guard the Wishlist plugin needed after discovering live that
     * WooCommerce's Product Collection "Legacy Template" compatibility can fire classic hooks
     * on the same product a custom block also renders -- built in here from day one instead of
     * discovering it live again.
     */
    private static $auto_rendered = array();

    private static $localized = false;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Confirmed live (during the Wishlist plugin build) that this hook fires on this
        // environment's real single-product block template -- the "Add to Cart + Options"
        // block wraps the classic single_add_to_cart_button markup and its surrounding
        // classic hooks.
        add_action('woocommerce_after_add_to_cart_button', array($this, 'render_single_product_widget'));

        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function render_single_product_widget()
    {
        global $product;

        if (!$product instanceof \WC_Product) {
            return;
        }

        echo self::render_auto_once($product->get_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_widget_html() escapes its own output.
    }

    public static function render_auto_once($product_id)
    {
        $product_id = absint($product_id);

        if (isset(self::$auto_rendered[$product_id])) {
            return '';
        }

        self::$auto_rendered[$product_id] = true;

        return self::render_widget_html($product_id);
    }

    /**
     * The shared widget renderer -- used by the classic hook above and the block's server
     * render callback, so the markup only lives in one place.
     */
    public static function render_widget_html($product_id)
    {
        $product_id = absint($product_id);
        $anchor     = $product_id ? wc_get_product($product_id) : false;

        if (!$anchor instanceof \WC_Product || !$anchor->is_type('simple') || !$anchor->is_purchasable() || !$anchor->is_in_stock()) {
            return '';
        }

        $companion_ids = get_post_meta($product_id, '_hdfbt_companion_ids', true);
        $companion_ids = is_array($companion_ids) ? array_map('absint', $companion_ids) : array();

        $companions = array();
        foreach ($companion_ids as $companion_id) {
            $companion_product = wc_get_product($companion_id);
            if ($companion_product instanceof \WC_Product && $companion_product->is_type('simple') && $companion_product->is_purchasable() && $companion_product->is_in_stock()) {
                $companions[] = $companion_product;
            }
        }

        if (empty($companions)) {
            return '';
        }

        $all_items    = array_merge(array($anchor), $companions);
        $initial_total = 0;
        foreach ($all_items as $item) {
            $initial_total += (float) $item->get_price();
        }

        ob_start();
        ?>
        <div class="hdfbt-widget" data-hdfbt-widget data-hdfbt-anchor-price="<?php echo esc_attr($anchor->get_price()); ?>">
            <h3 class="hdfbt-widget__title"><?php esc_html_e('Frequently Bought Together', 'hdwebmobile-frequently-bought-together'); ?></h3>
            <div class="hdfbt-widget__items">
                <?php foreach ($all_items as $index => $item) : ?>
                    <?php if ($index > 0) : ?>
                        <span class="hdfbt-widget__plus" aria-hidden="true">+</span>
                    <?php endif; ?>
                    <div class="hdfbt-widget__item">
                        <label class="hdfbt-widget__item-label">
                            <?php if (0 === $index) : ?>
                                <input type="checkbox" checked="checked" disabled="disabled" class="hdfbt-widget__checkbox" />
                            <?php else : ?>
                                <input type="checkbox" checked="checked" class="hdfbt-widget__checkbox" data-hdfbt-checkbox data-hdfbt-product-id="<?php echo esc_attr($item->get_id()); ?>" data-hdfbt-price="<?php echo esc_attr($item->get_price()); ?>" />
                            <?php endif; ?>
                            <?php echo wp_kses_post($item->get_image('thumbnail')); ?>
                            <span class="hdfbt-widget__item-name"><?php echo esc_html($item->get_name()); ?></span>
                            <span class="hdfbt-widget__item-price"><?php echo wp_kses_post($item->get_price_html()); ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="hdfbt-widget__footer">
                <span class="hdfbt-widget__total-label"><?php esc_html_e('Total price:', 'hdwebmobile-frequently-bought-together'); ?></span>
                <span class="hdfbt-widget__total" data-hdfbt-total><?php echo wp_kses_post(wc_price($initial_total)); ?></span>
                <button type="button" class="hdfbt-widget__add-button button" data-hdfbt-add data-hdfbt-anchor-id="<?php echo esc_attr($anchor->get_id()); ?>">
                    <?php echo esc_html(sprintf(
                        /* translators: %d: number of items */
                        __('Add %d to Cart', 'hdwebmobile-frequently-bought-together'),
                        count($all_items)
                    )); ?>
                </button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function enqueue_assets()
    {
        if (!function_exists('is_product') || !is_product()) {
            return;
        }

        wp_enqueue_style('hdfbt-frontend-css', HDFBT_PLUGIN_URL . 'assets/css/hdfbt-frontend.css', array(), self::asset_version('assets/css/hdfbt-frontend.css'));
        wp_enqueue_script('hdfbt-frontend-js', HDFBT_PLUGIN_URL . 'assets/js/hdfbt-frontend.js', array(), self::asset_version('assets/js/hdfbt-frontend.js'), true);

        self::localize_script_once();
    }

    /**
     * Uses the file's own last-modified time as the cache-busting version -- learned during
     * the Wishlist build that a static plugin-version query string leaves browsers serving a
     * stale copy of frequently-iterated CSS/JS until the plugin version itself is bumped.
     */
    public static function asset_version($relative_path)
    {
        $path = HDFBT_PLUGIN_DIR . $relative_path;
        return file_exists($path) ? (string) filemtime($path) : HDFBT_VERSION;
    }

    public static function localize_script_once()
    {
        if (self::$localized) {
            return;
        }
        self::$localized = true;

        wp_localize_script('hdfbt-frontend-js', 'hdfbtParams', array(
            // The Store API manages its own request-signing nonce via a response header (not a
            // wp_create_nonce() action) -- the frontend JS fetches a fresh one from the Store
            // API itself before its first cart request, the same pattern already verified
            // working for the Wishlist plugin's Store API add-to-cart call.
            'cartUrl'          => esc_url_raw(rest_url('wc/store/v1/cart')),
            'cartBatchUrl'     => esc_url_raw(rest_url('wc/store/v1/batch')),
            /* translators: %d: number of items */
            'addLabelTemplate'    => __('Add %d to Cart', 'hdwebmobile-frequently-bought-together'),
            'genericErrorMessage' => __('Sorry, one or more items could not be added to your cart. Please try again.', 'hdwebmobile-frequently-bought-together'),
            // Mirrors wc_price()'s own formatting settings so the JS-recomputed running total
            // (on checkbox toggle, no AJAX round trip) matches the server-rendered format.
            'currency' => array(
                'symbol'            => get_woocommerce_currency_symbol(),
                'position'          => get_option('woocommerce_currency_pos'),
                'decimalSeparator'  => wc_get_price_decimal_separator(),
                'thousandSeparator' => wc_get_price_thousand_separator(),
                'decimals'          => wc_get_price_decimals(),
            ),
        ));
    }
}
