<?php

namespace Groost\WooCommerce\Utils;

use Groost\WooCommerce\Plugin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Product
{
    public static function getProductData()
    {
        $productId = sanitize_text_field($_POST['productId']);

        if (!$productId) {
            echo json_encode([
                'error' => __('Invalid product ID', 'groost-wc-social'),
            ]);

            wp_die();
        }

        $product = wc_get_product((int)$productId);

        if (!$product) {
            echo json_encode([
                'error' => __('Product not found.', 'groost-wc-social'),
            ]);

            wp_die();
        }

        $images = [];
        $images[] = [
            'id' => $product->get_image_id(),
            'src' => wp_get_attachment_image_src($product->get_image_id(), [1200, 1200]),
        ];

        foreach ($product->get_gallery_image_ids() as $imageId) {
            $images[] = [
                'id' => $imageId,
                'src' => wp_get_attachment_image_src($imageId, [1200, 1200]),
            ];
        }

        echo json_encode([
            'name' => wp_strip_all_tags($product->get_data()['name']),
            'description' => wp_strip_all_tags($product->get_data()['description']),
            'price' => $product->get_data()['price'],
            'image' => $images[0],
            'currency' => [
                'code' => self::getCurrencyInfo()->currencyCode,
                'symbol' => self::getCurrencyInfo()->currencySymbol,
            ],
            'images' => $images,
        ]);

        wp_die();
    }

    public static function getImagePath($imageId)
    {
        return wp_get_original_image_url($imageId);
    }

    private static function getCurrencyInfo()
    {
        $data = new \stdClass();
        $data->currencyCode = get_woocommerce_currency();
        $data->currencySymbol = get_woocommerce_currency_symbol();

        return $data;
    }
}
