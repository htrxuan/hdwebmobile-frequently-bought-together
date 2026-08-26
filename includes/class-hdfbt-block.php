<?php

namespace htrxuan\hdfbt;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registers the frequently-bought-together block for placement on the single-product block
 * template. No ancestor restriction: confirmed live that this theme's real Single Product
 * template has no wrapping "woocommerce/single-product" block (that block type is a distinct,
 * separately-insertable "embed one specific product" block, not an implicit page wrapper) --
 * WooCommerce's own Related Products and Product Meta blocks, which do sit directly in this
 * template, likewise declare no ancestor at all. usesContext still resolves postId correctly
 * from the current singular product outside any explicit context-providing ancestor.
 */
class HDFBT_Block
{

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('init', array($this, 'register_block'));
    }

    public function register_block()
    {
        wp_register_script(
            'hdfbt-frequently-bought-together-editor',
            HDFBT_PLUGIN_URL . 'blocks/frequently-bought-together/index.js',
            array('wp-blocks', 'wp-element', 'wp-i18n'),
            HDFBT_VERSION,
            true
        );

        register_block_type(
            HDFBT_PLUGIN_DIR . 'blocks/frequently-bought-together',
            array(
                'render_callback' => array($this, 'render'),
            )
        );
    }

    public function render($attributes, $content, $block)
    {
        // wp_localize_script() silently no-ops on a handle that isn't registered yet, so the
        // enqueue calls must run first -- confirmed live: calling localize before enqueue here
        // left window.hdfbtParams permanently undefined for the whole request, since blocks
        // render before wp_enqueue_scripts fires on this block theme, and localize_script_once()
        // only ever runs once per request.
        wp_enqueue_style('hdfbt-frontend-css', HDFBT_PLUGIN_URL . 'assets/css/hdfbt-frontend.css', array(), HDFBT_Frontend::asset_version('assets/css/hdfbt-frontend.css'));
        wp_enqueue_script('hdfbt-frontend-js', HDFBT_PLUGIN_URL . 'assets/js/hdfbt-frontend.js', array(), HDFBT_Frontend::asset_version('assets/js/hdfbt-frontend.js'), true);
        HDFBT_Frontend::localize_script_once();

        $product_id = isset($block->context['postId']) ? absint($block->context['postId']) : 0;

        if (!$product_id) {
            return '';
        }

        return HDFBT_Frontend::render_auto_once($product_id);
    }
}
