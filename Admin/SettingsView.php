<?php

namespace Groost\WooCommerce\Admin;

use Groost\WooCommerce\Plugin;
use Groost\WooCommerce\Utils\Api;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class SettingsView
{
    private static function saveAppApiKey($apiKey)
    {
        global $wpdb;

        $wpdb->get_row($wpdb->prepare('INSERT INTO `' . $wpdb->prefix . Plugin::DB_TABLE_SETTING . '` SET `key` = %s, `value` = %s', 'APP_API_KEY', $apiKey));

        return $apiKey;
    }

    private static function removeAppApiKey()
    {
        global $wpdb;

        $wpdb->query($wpdb->prepare('DELETE FROM `' . $wpdb->prefix . Plugin::DB_TABLE_SETTING . '` WHERE `key` = %s', 'APP_API_KEY'));
    }

    private static function getSettings()
    {
        try {
            $data = Api::makeGetCall('/api/wordpress/settings/info');

            if (empty($data)) {
                return null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return $data;
    }

    private static function getAvailableAccounts()
    {
        try {
            $data = Api::makeGetCall('/api/wordpress/settings/facebook-pages');

            if (empty($data)) {
                return null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return $data;
    }

    private static function setUsedAccount($pageId)
    {
        try {
            $data = Api::makePostCall('/api/wordpress/settings/select-facebook-page', ['pageId' => $pageId]);

            if (empty($data)) {
                return null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return $data;
    }


    public static function render()
    {
        if (isset($_POST['action'])) {
            $action = sanitize_text_field($_POST['action']);

            if ($action === 'groost_select_fb_page') {
                self::setUsedAccount(sanitize_text_field($_POST['social-account-id']));
            }

            if ($action === 'groost_unlink_app') {
                self::removeAppApiKey();
            }
        }

        $appApiKey = Api::getAppApiKey();
        $isLinkedToApp = $appApiKey !== null;

        if (!$isLinkedToApp && isset($_GET['at']) && !empty($_GET['at'])) {
            $token = sanitize_text_field($_GET['at']);

            self::saveAppApiKey($token);

            wp_redirect('admin.php?page=' . Plugin::SUBMENU_SLUG . '&tab=settings');
        } else {
            $settings = self::getSettings();
        }

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Settings</h1>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-1">
                    <div id="postbox-container" class="postbox-container">
                        <?php if ($settings === null) { ?>
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="handle ui-sortable-handle"><?php _e('groost account', 'groost-wc-social') ?></h2>
                                </div>
                                <div class="inside">
                                    <p>Make promoting your products much simpler with <a href="https://groost.com" target="_blank">groost</a> features. Let's start now! Just click on button below.</p>
                                    <div class="actions">
                                        <a href="<?php echo Plugin::API_URL . '/api/wordpress/auth?secret=' . wp_generate_uuid4() . '&host=' . get_bloginfo('wpurl'); ?>"
                                           class="button button-primary"
                                           target="_blank">
                                            Connect account
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="handle ui-sortable-handle"><?php _e('groost account', 'groost-wc-social') ?></h2>
                                </div>
                                <div class="inside">
                                    <h3>
                                        Currently you are posting to your Facebook page
                                        <a href="https://www.facebook.com/<?php echo esc_attr($settings->selectedFacebookPage->facebookId) ?>" target="_blank">
                                            <strong><?php echo esc_attr($settings->selectedFacebookPage->name) ?></strong>
                                        </a>.
                                    </h3>
                                    <p id="groost-wc-change_page_link"><a href="javascript:void(0);" title="Change Facebook page">I would like to switch to another Facebook page</a></p>
                                    <form method="post" id="groost_change_fb_page">
                                        <input type="hidden" name="action" value="groost_select_fb_page"/>
                                        <select name="social-account-id">
                                            <option value="" disabled <?php echo esc_attr($settings->selectedFacebookPage ? '' : 'selected') ?>><?php _e('Select Facebook page', 'groost-wc-social') ?></option>
                                            <?php foreach (self::getAvailableAccounts()->data as $page) { ?>
                                                <option value="<?php echo esc_attr($page->id) ?>" <?php echo esc_attr($page->id) === esc_attr($settings->selectedFacebookPage->facebookId ? 'selected' : '') ?>>
                                                    <?php echo esc_attr($page->name) ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <br/><br/>
                                        <input type="submit" value="<?php _e('Save', 'groost-wc-social') ?>" class="button button-second"/>
                                    </form>
                                    <form method="post" id="groost-wc-disconnect_form">
                                        <input type="hidden" name="action" value="groost_unlink_app"/>
                                        <input
                                            type="submit"
                                            value="<?php _e('Disconnect from groost account', 'groost-wc-social') ?>"
                                            class="button button-secondary"
                                            data-confirm="<?php _e('Do you really want to disconnect your groost account?', 'groost-wc-social') ?>"
                                        />
                                    </form>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php

    }
}
