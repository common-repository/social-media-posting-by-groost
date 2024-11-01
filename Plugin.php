<?php

namespace Groost\WooCommerce;

use Groost\WooCommerce\Admin\CreatePostView;
use Groost\WooCommerce\Admin\PostListView;
use Groost\WooCommerce\Admin\SettingsView;
use Groost\WooCommerce\Utils\Api;
use Groost\WooCommerce\Utils\Product;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Plugin
{
    const VERSION = '0.0.4';

    const SUBMENU_SLUG = 'groost-wc';

    const DB_TABLE_PREFIX = 'groost_';
    const DB_TABLE_SETTING = self::DB_TABLE_PREFIX . 'settings';

    const API_URL = 'http://app.groost.com';

    public function __construct()
    {
        // Add item to WooCommerce menu
        add_action('admin_menu', [$this, 'setSubmenuPages']);

        // Add AJAX actions
        add_action('wp_ajax_groost_wc_product_detail', [Product::class, 'getProductData']);

        // Add CSSs and JSs
        add_action('admin_enqueue_scripts', [$this, 'enqueueStyles']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);

        // Add list of posts to edit form
        add_action('edit_form_advanced', [PostListView::class, 'render']);
    }

    public function enqueueStyles()
    {
        wp_enqueue_style(
            'groost_woocommerce_css_post',
            plugins_url(
                '/assets/css/groost-wc-post.css',
                __FILE__
            ),
            [],
            self::VERSION
        );

        wp_enqueue_style(
            'groost_woocommerce_css_settings',
            plugins_url(
                '/assets/css/groost-wc-settings.css',
                __FILE__
            ),
            [],
            self::VERSION
        );
    }

    public function enqueueScripts()
    {
        wp_enqueue_script(
            'groost-wc-social_post-js',
            plugins_url(
                '/assets/js/groost-wc-social_post.js',
                __FILE__
            ),
            [],
            Plugin::VERSION
        );
        wp_enqueue_script(
            'groost-wc-settings-js',
            plugins_url(
                '/assets/js/groost-wc-settings.js',
                __FILE__
            ),
            [],
            Plugin::VERSION
        );
    }

    public function setSubmenuPages()
    {
        add_submenu_page(
            'woocommerce',
            __('Groost E-comm Social Management', 'groost-wc-social'),
            __('Groost E-comm Social Management', 'groost-wc-social'),
            'manage_woocommerce',
            self::SUBMENU_SLUG,
            [$this, 'renderLayout'],
            5
        );

        add_submenu_page(
            Plugin::SUBMENU_SLUG,
            __('Post to social media', 'groost-wc-social'),
            __('Post to social media', 'groost-wc-social'),
            'manage_woocommerce',
            CreatePostView::PAGE_ID,
            [CreatePostView::class, 'render']
        );
    }

    public static function install()
    {
        global $wpdb;

        $tableName = $wpdb->prefix . self::DB_TABLE_SETTING;

        // Create settings table
        if ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") !== $tableName) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE `$tableName` (`key` VARCHAR(30) NOT NULL, `value` TEXT NOT NULL) $charset_collate;";

            require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    public function renderLayout()
    {
        $activeTab = sanitize_text_field(isset($_GET['tab']) ? $_GET['tab'] : 'posts');

        $appApiKey = Api::getAppApiKey();
        $isLinkedToApp = $appApiKey !== null;

        if (!$isLinkedToApp) {
            SettingsView::render();
            return;
        }

        ?>
        <h2 class="nav-tab-wrapper">
            <a href="?page=<?php echo self::SUBMENU_SLUG ?>&tab=posts" class="nav-tab <?php echo $activeTab === 'posts' ? 'nav-tab-active' : '' ?>">Posts</a>
            <a href="?page=<?php echo self::SUBMENU_SLUG ?>&tab=create-post" class="nav-tab <?php echo $activeTab == 'create-post' ? 'nav-tab-active' : '' ?>">Create post</a>
            <a href="?page=<?php echo self::SUBMENU_SLUG ?>&tab=settings" class="nav-tab <?php echo $activeTab == 'settings' ? 'nav-tab-active' : '' ?>">Settings</a>
        </h2>
        <?php

        switch ($activeTab) {
            case 'posts':
                PostListView::render();
                break;

            case 'create-post':
                CreatePostView::render();
                break;

            case 'settings':
                SettingsView::render();
                break;
        }
    }
}
