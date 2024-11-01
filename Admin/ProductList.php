<?php

namespace Groost\WooCommerce\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ProductList
{
    public function __construct()
    {
        // Add action to product item
        add_filter('post_row_actions', [$this, 'addActionToPostRow'], 20, 2);
    }

    public function addActionToPostRow($actions, $post)
    {
        if ($post->post_type !== 'product') {
            return $actions;
        }

        $actions['groost_post'] = '<a href="' . esc_html(admin_url('admin.php?page=' . CreatePostView::PAGE_ID . '&tab=create-post&productId=' . esc_attr($post->ID))) . '" title="Create post">' . __('Post to Facebook', 'groost-for-woocommerce') . '</a>';

        return $actions;
    }
}
