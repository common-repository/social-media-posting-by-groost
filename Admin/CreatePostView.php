<?php

namespace Groost\WooCommerce\Admin;

use Groost\WooCommerce\Plugin;
use Groost\WooCommerce\Utils\Api;
use Groost\WooCommerce\Utils\Product;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CreatePostView
{
    const PAGE_ID = 'groost-wc-social-post';

    private static function getProducts()
    {
        // This will be changed to paginated result in next versions
        return wc_get_products([
            'posts_per_page' => 100000,
        ]);
    }

    private static function onSubmitForm()
    {
        $productId = sanitize_text_field($_POST['groost-wc-product-id']);
        $product = wc_get_product($productId);

        if (!$product) {
            return;
        }

        $data = [
            'text' => sanitize_textarea_field($_POST['groost_wc_product_primarytext']),
            'imageUrl' => Product::getImagePath(sanitize_text_field($_POST['groost-wc-image-id'])),
            'productId' => esc_attr($product->get_id()),
            'draft' => isset($_POST['groost-wc-post-draft']),
            'darkPost' => isset($_POST['groost-wc-post-dark-post']),
            'size' => sanitize_text_field($_POST['groost-wc-image-size']),
        ];
        $post = Api::makePostCall('/api/wordpress/post', $data);

        if (isset($_POST['draft'])) {
            wp_redirect(Plugin::API_URL . '/post/edit/' . esc_attr($post->id));
        } else {
            wp_redirect('admin.php?page=' . Plugin::SUBMENU_SLUG . '&tab=posts');
        }
    }

    public static function render()
    {
        if (isset($_POST['action']) && sanitize_text_field($_POST['action']) === 'groost-new-post') {
            self::onSubmitForm();
        }

        $defaultSize = 'landscape';

        ?>
        <div class="wrap">
            <div id="poststuff">
                <h1 class="wp-heading-inline"><?php _e('Create post to your Facebook page', 'groost-wc-social') ?></h1>
                <form method="post">
                    <input type="hidden" name="action" value="groost-new-post"/>
                    <input type="hidden" name="groost-wc-image-id"/>
                    <input type="hidden" name="groost-wc-image-size" value="<?php echo $defaultSize ?>"/>
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="postbox-container-2" class="postbox-container">
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="handle ui-sortable-handle"><?php _e('Product', 'groost-wc-social') ?></h2>
                                </div>
                                <div class="inside">
                                    <p class="form-field">
                                        <select id="groost-wc-product-id" name="groost-wc-product-id">
                                            <option value=""><?php _e('Select product', 'groost-wc-social') ?></option>
                                            <?php foreach (self::getProducts() as $product) { ?>
                                                <option value="<?php echo esc_attr($product->get_data()['id']) ?>"><?php echo esc_attr($product->get_data()['name']) ?></option>
                                            <?php } ?>
                                        </select>
                                    </p>
                                </div>
                            </div>
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="handle ui-sortable-handle"><?php _e('Headline', 'groost-wc-social') ?></h2>
                                </div>
                                <div class="inside">
                                    <p class="form-field">
                                        <input name="groost-wc-product-headline" type="text" id="groost-wc-product-headline" value="" class="large-text">
                                    </p>
                                    <p class="form-field">
                                        Add a product property to your text.
                                    </p>
                                    <p class="form-field">
                                        <a class="button" href="#" onclick="addPropertyToText(jQuery('#groost-wc-product-headline'), 'name')">name</a>
                                        <a class="button" href="#" onclick="addPropertyToText(jQuery('#groost-wc-product-headline'), 'price')">price</a>
                                        <a class="button" href="#" onclick="addPropertyToText(jQuery('#groost-wc-product-headline'), 'description')">description</a>
                                    </p>
                                </div>
                            </div>
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="handle ui-sortable-handle"><?php _e('Description', 'groost-wc-social') ?></h2>
                                </div>
                                <div class="inside">
                                    <p class="form-field">
                                        <textarea
                                                name="groost_wc_product_primarytext"
                                                id="groost_wc_product_primarytext"
                                                placeholder=""
                                                rows="20"
                                                cols="20"
                                        ></textarea>
                                    </p>
                                    <p class="form-field">
                                        Add a product property to your text.
                                    </p>
                                    <p class="form-field">
                                        <a class="button" href="#" onclick="addPropertyToText(jQuery('#groost_wc_product_primarytext'), 'name')">name</a>
                                        <a class="button" href="#" onclick="addPropertyToText(jQuery('#groost_wc_product_primarytext'), 'price')">price</a>
                                        <a class="button" href="#" onclick="addPropertyToText(jQuery('#groost_wc_product_primarytext'), 'description')">description</a>
                                    </p>
                                </div>
                            </div>
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="handle ui-sortable-handle"><?php _e('Image', 'groost-wc-social') ?></h2>
                                </div>
                                <div class="inside groost-wp-product_images">

                                </div>
                            </div>
                        </div>
                        <div id="postbox-container-1" class="postbox-container">
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="handle ui-sortable-handle"><?php _e('Publish', 'groost-wc-social') ?></h2>
                                </div>
                                <div class="inside">
                                    <p class="form-field">
                                        <input type="submit" name="groost-wc-post-draft" value="<?php _e('Save and edit in groost app', 'groost-wc-social') ?>" class="button button-secondary"/>
                                    </p>
                                    <p class="form-field">
                                        <input type="submit" name="groost-wc-post-dark-post" value="<?php _e('Publish as dark post', 'groost-wc-social') ?>" class="button button-secondary"/>
                                    </p>
                                    <p class="form-field">
                                        <input type="submit" name="force-publish" value="<?php _e('Publish immediately', 'groost-wc-social') ?>" class="button button-primary"/>
                                    </p>
                                </div>
                            </div>
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="handle ui-sortable-handle"><?php _e('Post type', 'groost-wc-social') ?></h2>
                                </div>
                                <div class="inside">
                                    <div class="groost-wc_post_types">
                                            <div class="groost_wc_post_type_container landscape active" data-size="landscape">
                                                <div class="groost_wc_post_type_layout"></div>
                                                <div class="groost_wc_post_type_label">landscape</div>
                                            </div>
                                            <div class="groost_wc_post_type_container square" data-size="square">
                                                <div class="groost_wc_post_type_layout"></div>
                                                <div class="groost_wc_post_type_label">square</div>
                                            </div>
                                            <div class="groost_wc_post_type_container paper" data-size="paper">
                                                <div class="groost_wc_post_type_layout"></div>
                                                <div class="groost_wc_post_type_label">paper</div>
                                            </div>
                                            <div class="groost_wc_post_type_container portrait" data-size="portrait">
                                                <div class="groost_wc_post_type_layout"></div>
                                                <div class="groost_wc_post_type_label">portrait</div>
                                            </div>
                                    </div>
                                </div>
                            </div>
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="handle ui-sortable-handle"><?php _e('Preview', 'groost-wc-social') ?></h2>
                                </div>
                                <div class="inside">
                                    <div class="groost-wc_fbpost">
                                        <div class="groost-wc_fbpost-page-header">
                                            <div class="groost-wc_fbpost-page-photo"></div>
                                            <div class="groost-wc_fbpost-page-name">Social media page</div>
                                        </div>
                                        <div class="groost-wc_fbpost-primary-text"></div>
                                        <div class="groost-wc_fbpost-image <?php echo $defaultSize ?>" data-size="<?php echo $defaultSize ?>"></div>
<!--                                        <div class="groost-wc_fbpost-link">-->
<!--                                            <div class="groost-wc_fbpost-link-headline-url">--><?//= parse_url(get_bloginfo('url'), PHP_URL_HOST) ?><!--</div>-->
<!--                                            <div class="groost-wc_fbpost-link-headline-title"></div>-->
<!--                                        </div>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}
