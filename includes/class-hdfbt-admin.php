<?php

namespace htrxuan\hdfbt;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Adds the "Frequently Bought Together" tab to the Product Data metabox. Reuses WooCommerce's
 * own wc-product-search select2 field (already enqueued on the product edit screen -- no
 * custom JS needed) with data-exclude_type to restrict the picker to simple products only,
 * confirmed to be a real, native filter supported by WC_AJAX::json_search_products().
 */
class HDFBT_Admin
{

    private static $instance = null;

    const MAX_COMPANIONS = 4;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_filter('woocommerce_product_data_tabs', array($this, 'add_product_data_tab'));
        add_action('woocommerce_product_data_panels', array($this, 'render_product_data_panel'));
        add_action('woocommerce_process_product_meta', array($this, 'save_product_meta'));
        require_once HDFBT_PLUGIN_DIR . 'includes/class-hdfbt-hub.php';
        add_filter('hdwebmobile_hub_tabs', array($this, 'register_hub_tabs'));
    }

    /**
     * This plugin has no per-product-independent settings of its own (everything lives on
     * the product-data tab), so this hub tab exists solely to host the "more plugins by
     * this author" panel -- kept minimal rather than skipped, so every hdwebmobile plugin
     * offers the same discovery path.
     */
    public function register_hub_tabs($tabs)
    {
        $tabs['frequently-bought-together'] = array(
            'label'  => __('Frequently Bought Together', 'hdwebmobile-frequently-bought-together'),
            'order'  => 50,
            'render' => array($this, 'render_plugins_page'),
        );
        return $tabs;
    }

    public function render_plugins_page()
    {
        ?>
        <p><?php esc_html_e('An Amazon-style "Frequently Bought Together" widget for WooCommerce. Add several related products to cart in one click. There\'s nothing to configure here -- go to any product\'s own "Frequently Bought Together" tab under Product Data to pick up to four companion products for it.', 'hdwebmobile-frequently-bought-together'); ?></p>
        <?php
    }

    public function add_product_data_tab($tabs)
    {
        $tabs['hdfbt'] = array(
            'label'    => __('Frequently Bought Together', 'hdwebmobile-frequently-bought-together'),
            'target'   => 'hdfbt_product_data',
            'class'    => array('show_if_simple'),
            'priority' => 21,
        );
        return $tabs;
    }

    public function render_product_data_panel()
    {
        global $post;

        $product_id     = $post->ID;
        $companion_ids  = get_post_meta($product_id, '_hdfbt_companion_ids', true);
        $companion_ids  = is_array($companion_ids) ? array_map('absint', $companion_ids) : array();

        wp_nonce_field('hdfbt_save_meta', 'hdfbt_meta_nonce');
        ?>
        <div id="hdfbt_product_data" class="panel woocommerce_options_panel hidden">
            <div class="options_group">
                <p class="form-field">
                    <label for="_hdfbt_companion_ids"><?php esc_html_e('Companion products', 'hdwebmobile-frequently-bought-together'); ?></label>
                    <select
                        id="_hdfbt_companion_ids"
                        name="_hdfbt_companion_ids[]"
                        class="wc-product-search"
                        multiple="multiple"
                        style="width: 50%;"
                        data-placeholder="<?php esc_attr_e('Search for simple products&hellip;', 'hdwebmobile-frequently-bought-together'); ?>"
                        data-action="woocommerce_json_search_products"
                        data-exclude="<?php echo intval($product_id); ?>"
                        data-exclude_type="variable,grouped,external,variation"
                    >
                        <?php
                        if (!empty($companion_ids)) {
                            _prime_post_caches($companion_ids);
                        }
                        foreach ($companion_ids as $companion_id) {
                            $companion_product = wc_get_product($companion_id);
                            if ($companion_product) {
                                echo '<option value="' . esc_attr($companion_id) . '" selected="selected">' . esc_html(wp_strip_all_tags($companion_product->get_formatted_name())) . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <?php
                    $hdfbt_help_text = sprintf(
                        /* translators: %d: maximum number of companion products */
                        __('Pick up to %d simple products to show alongside this one in the "Frequently Bought Together" widget. Variable, grouped, and external products can\'t be picked, since an "Add all to cart" click needs a concrete product with no variation to choose.', 'hdwebmobile-frequently-bought-together'),
                        self::MAX_COMPANIONS
                    );
                    echo wc_help_tip($hdfbt_help_text); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wc_help_tip() escapes its own output.
                    ?>
                </p>
            </div>
        </div>
        <?php
    }

    public function save_product_meta($post_id)
    {
        if (!isset($_POST['hdfbt_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['hdfbt_meta_nonce'])), 'hdfbt_save_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $companion_ids = isset($_POST['_hdfbt_companion_ids']) ? array_map('absint', (array) wp_unslash($_POST['_hdfbt_companion_ids'])) : array();
        $companion_ids = array_values(array_filter(array_unique($companion_ids)));
        $companion_ids = array_slice($companion_ids, 0, self::MAX_COMPANIONS);

        update_post_meta($post_id, '_hdfbt_companion_ids', $companion_ids);
    }
}
