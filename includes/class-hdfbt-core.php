<?php

namespace htrxuan\hdfbt;

if (!defined('ABSPATH')) {
    exit;
}

final class HDFBT_Core
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
        $this->includes();
        $this->init_hooks();
    }

    private function __clone()
    {
    }

    private function includes()
    {
        require_once HDFBT_PLUGIN_DIR . 'includes/class-hdfbt-frontend.php';
        require_once HDFBT_PLUGIN_DIR . 'includes/class-hdfbt-block.php';
        require_once HDFBT_PLUGIN_DIR . 'includes/class-hdfbt-admin.php';
    }

    private function init_hooks()
    {
        add_action('admin_notices', array($this, 'render_missing_woocommerce_notice'));

        if (!class_exists('WooCommerce')) {
            return;
        }

        HDFBT_Frontend::get_instance();
        HDFBT_Block::get_instance();

        if (is_admin()) {
            HDFBT_Admin::get_instance();
        }
    }

    public function render_missing_woocommerce_notice()
    {
        $screen = get_current_screen();
        if (!$screen || 'plugins' !== $screen->id) {
            return;
        }

        if (!get_transient('hdfbt_wc_missing_notice')) {
            return;
        }
        delete_transient('hdfbt_wc_missing_notice');
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php esc_html_e('HDWebmobile Frequently Bought Together requires WooCommerce to be installed and active. The plugin has been deactivated.', 'hdwebmobile-frequently-bought-together'); ?>
            </p>
        </div>
        <?php
    }
}
