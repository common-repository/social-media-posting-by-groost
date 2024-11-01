<?php

namespace Groost\WooCommerce\Admin;

use Groost\WooCommerce\Plugin;
use Groost\WooCommerce\Utils\Api;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PostListView
{
    private static function getPosts($productId = null)
    {
        $params = [
            'publishedOnly' => true,
        ];

        if ($productId !== null) {
            $params['productId'] = $productId;
        }

        try {
            $data = Api::makeGetCall('/api/wordpress/post/search', $params);
        } catch(\Throwable $e) {
            $data = [];
        }

        if (empty($data)) {
            return [];
        }

        return $data;
    }

    public static function render()
    {
        $postType = get_current_screen()->post_type;
        if (!empty($postType) && $postType !== 'product') {
            return;
        }

        $isPostForm = false;

        if (isset($_GET['post']) && !empty($_GET['post'])) {
            $productId = sanitize_text_field($_GET['post']);
            $posts = self::getPosts($productId);
            $isPostForm = true;
        } else {
            $posts = self::getPosts();
        }

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e('Your social posts', 'groost-wc-social') ?></h1>
            <a href="admin.php?page=<?php echo Plugin::SUBMENU_SLUG ?>&tab=create-post<?php if ($isPostForm) { echo "&productId=" . $productId; } ?>" class="page-title-action"><?php _e('Add new', 'groost-wc-social') ?></a>
            <?php if (!$isPostForm) { ?>
                <hr class="wp-header-end">
            <?php } ?>
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                <tr>
                    <th scope="col" id="description" class="manage-column column-description"><?php _e('Description', 'groost-wc-social') ?></th>
                    <?php if (!$isPostForm) { ?>
                        <th scope="col" id="product" class="manage-column column-description"><?php _e('Product', 'groost-wc-social') ?></th>
                    <?php } ?>
                    <th scope="col" id="date" class="manage-column column-date"><?php _e('Date', 'groost-wc-social') ?></th>
                    <th scope="col" id="clicks" class="manage-column column-clicks"><?php _e('Link clicks', 'groost-wc-social') ?></th>
                    <th scope="col" id="likes" class="manage-column column-likes"><?php _e('Likes', 'groost-wc-social') ?></th>
                    <th scope="col" id="shares" class="manage-column column-shares"><?php _e('Shares', 'groost-wc-social') ?></th>
                    <th scope="col" id="comments" class="manage-column column-comments"><?php _e('Comments', 'groost-wc-social') ?></th>
                    <th scope="col" id="actions" class="manage-column column-actions"><?php _e('Actions', 'groost-wc-social') ?></th>
                </tr>
                </thead>

                <tbody id="the-list">
                <?php
                foreach ($posts as $postData) {
                    $product = wc_get_product($postData->post->wordpressProductId);
                    $publishDate = new \DateTime(esc_attr($postData->post->publishDateTime));
                    ?>
                    <tr id="post-<?php echo $postData->post->id ?>" class="iedit author-self level-0 post-<?php echo $postData->post->id ?> type-post status-publish format-standard hentry category-uncategorized">
                        <td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
                            <p>
                                <?php echo nl2br(wp_kses_post(strlen($postData->post->text) > 50 ? substr($postData->post->text, 0, 50) . '...' : $postData->post->text)) ?>
                            </p>
                            <div class="row-actions">
                            <span class="show-product">
                                <?php if ($product) { ?>
                                    <a href="<?php echo $product->get_permalink() ?>" aria-label="<?php _e('Show product on site', 'groost-wc-social') ?>" target="_blank">
                                        <?php _e('Show product on site', 'groost-wc-social') ?>
                                    </a>
                                <?php } ?>
                            </span>

                            <span class="show-post-on-page">
                                <?php if ($postData->post->facebookId && $postData->post->deleteDateTime === null) { ?>
                                    | <a href="https://www.facebook.com/<?php echo esc_attr($postData->post->facebookId) ?>" aria-label="<?php _e('Show post on your Facebook page', 'groost-wc-social') ?>" target="_blank">
                                        <?php _e('Show post on your Facebook page', 'groost-wc-social') ?>
                                    </a>
                                <?php } ?>
                            </span>
                            </div>
                        </td>
                        <?php if (!$isPostForm) { ?>
                        <td class="product column-product">
                            <?php if ($product) { ?>
                                <a href="post.php?action=edit&post=<?php echo esc_attr($product->get_id()) ?>" aria-label="Show product detail">
                                    <?php echo $product ? $product->get_data()['name'] : '' ?>
                                </a>
                            <?php } ?>
                        </td>
                        <?php } ?>
                        <td class="date column-date" data-colname="Date">
                            <?php
                                $published = false;
                                if ($postData->post->deleteDateTime) {
                                    _e('Deleted', 'groost-wc-social');
                                } elseif ($postData->post->publishDateTime) {
                                    $published = true;
                                    _e('Published', 'groost-wc-social');
                                    ?>
                                    <br>
                                    <?php echo $publishDate->format('Y/m/d H:m:s') ?>
                                    <?php
                                } elseif ($postData->post->publishingDateTime) {
                                    _e('Publishing', 'groost-wc-social');
                                } else {
                                    _e('Not published', 'groost-wc-social');
                                }
                            ?>

                        </td>
                        <td class="categories column-clicks" data-colname="<?php _e('Link clicks', 'groost-wc-social') ?>">
                            <?php echo isset($postData->stats->impressions) ? esc_attr($postData->stats->impressions) : 0 ?>
                        </td>
                        <td class="tags column-likes" data-colname="<?php _e('Likes', 'groost-wc-social') ?>">
                            <?php echo isset($postData->stats->totalReactions) ? esc_attr($postData->stats->totalReactions) : 0 ?>
                        </td>
                        <td class="comments column-shares" data-colname="<?php _e('Shares', 'groost-wc-social') ?>">
                            <?php echo isset($postData->stats->shares) ? esc_attr($postData->stats->shares) : 0 ?>
                        </td>
                        <td class="comments column-comments" data-colname="<?php _e('Comments', 'groost-wc-social') ?>">
                            <?php echo isset($postData->stats->comments) ? esc_attr($postData->stats->comments) : 0 ?>
                        </td>
                        <td class="actions column-actions" data-colname="<?php _e('Actions', 'groost-wc-social') ?>">
                            <?php if ($published) { ?>
                                <a href="<?php echo Plugin::API_URL . '/wizard/goal?postId=' . esc_attr($postData->post->id) ?>"
                                   class="button button-primary"
                                   target="_blank"
                                >
                                    <?php _e('Boost post', 'groost-wc-social') ?>
                                </a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
