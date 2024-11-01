<?php
/**
 * Plugin Name:       Groost E-comm Social Management
 * Plugin URI:        https://groost.com
 * Description:       Post on your social media platforms straight from your product detail and see the real impact your social media activity has on engagement and sales.
 * Version:           0.0.4
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Text domain:       groost-wc-social
 */

namespace Groost\WooCommerce;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

spl_autoload_register(function ($className) {
    if (strpos($className, 'Groost') !== false) {
        $path = str_replace(__NAMESPACE__, __DIR__, $className);
        $path = str_replace('\\', '/', $path);

        require $path . '.php';
    }
});

register_activation_hook(__FILE__, [Plugin::class, 'install']);

new Plugin;
new Admin\ProductList;
new Admin\CreatePostView;
