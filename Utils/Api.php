<?php

namespace Groost\WooCommerce\Utils;

use Groost\WooCommerce\Plugin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Api
{
    public static function getAppApiKey()
    {
        global $wpdb;

        $appApiKey = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . $wpdb->prefix . Plugin::DB_TABLE_SETTING . '` WHERE `key` = %s', 'APP_API_KEY'));

        return $appApiKey;
    }

    public static function makeGetCall($path, $data = [])
    {
        $apiKey = self::getAppApiKey();

        if (!$apiKey) {
            return null;
        }

        $url = Plugin::API_URL . $path . '?' . http_build_query(array_merge([
            'access_token' => $apiKey->value,
        ], $data));

        $request = wp_remote_get($url);
        $body = wp_remote_retrieve_body($request);

        $json = json_decode($body);

        if (isset($json->code) && isset($json->message)) {
            throw new \Exception("Failed to make post request");
        }

        return $json;
    }

    public static function makePostCall($path, $data)
    {
        $apiKey = self::getAppApiKey();

        if (!$apiKey) {
            return null;
        }

        $url = Plugin::API_URL . $path . '?' . http_build_query(array_merge([
                'access_token' => $apiKey->value,
            ]));

        $request = wp_remote_post($url, [
            'body' => $data,
        ]);
        $body = wp_remote_retrieve_body($request);

        $json = json_decode($body);

        if (isset($json->code) && isset($json->message)) {
            throw new \Exception("Failed to make post request");
        }

        return $json;
    }
}
