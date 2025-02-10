<?php declare(strict_types=1);

namespace abilita\payment\helper;

defined('ABSPATH') || exit;

class WC_Abilita_Helper
{
    public function __construct()
    {
    }

    public function get_client_ip()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            return sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
        }

        return '';
    }

    public function get_proxy_ip()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
        }
        return '';
    }

    public function get_order_statuses($status)
    {
        $orderStatuses = json_decode(get_option('ABILITA_MAPPED_STATUSES', '{}'), true);
        $orderStatuses[$status] = str_replace('wc-', '', $orderStatuses[$status]);
        return $orderStatuses[$status];
    }

    public function get_clean_class_name($class)
    {
        $class = explode('\\', $class);
        return end($class);
    }
}