<?php

namespace abilita\payment;

use abilita\payment\admin\WC_Abilita_Abilita_Admin;
use abilita\payment\gateways\WC_Abilita_Base_Payment_Loader;
use abilita\payment\actions\WC_Abilita_Add_Actions;
use abilita\payment\filters\WC_Abilita_Admin_Filters;
use abilita\payment\filters\WC_Abilita_Frontend_Filters;

defined('ABSPATH') || exit;

class WC_Abilita_Plugin_Loader
{
    protected $plugin_name;
    protected $version;

    public function __construct()
    {
        if (defined('WOOCOMMERCE_VERSION')) {
            $this->version = WC_ABILITA_PAYMENT_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = WC_ABILITA_PAYMENT_NAME;

        $this->load_dependencies();
        $this->load_defines();
    }

    public function load_dependencies()
    {
        /**
         * Helper
         */
        require_once(plugin_dir_path(__FILE__).'helper/class-wc-abilita-helper.php');

        /**
         * Services
         */
        require_once(plugin_dir_path(__FILE__).'services/class-wc-abilita-logger-service.php');
        require_once(plugin_dir_path(__FILE__).'services/class-wc-abilita-form-service.php');
        require_once(plugin_dir_path(__FILE__).'services/class-wc-abilita-client-service.php');
        require_once(plugin_dir_path(__FILE__).'services/class-wc-abilita-vatid-service.php');

        /**
         * Payment-Gateways
         */
        require_once(plugin_dir_path(__FILE__).'gateways/class-wc-abilita-base-payment-loader.php');

        //
        // Add other actions
        //
        require_once(plugin_dir_path(__FILE__).'actions/class-wc-abilita-add-actions.php');

        /**
         * Add other filter
         */
        require_once(plugin_dir_path(__FILE__).'filters/class-wc-abilita-admin-filters.php');
        require_once(plugin_dir_path(__FILE__).'filters/class-wc-abilita-frontend-filters.php');

        /**
         * Admin Settings
         */
        if (is_admin()) {
            require_once(plugin_dir_path(__FILE__).'admin/class-wc-abilita-payment-reauthorize.php');
            require_once(plugin_dir_path(__FILE__).'admin/class-wc-abilita-payment-cancel.php');
            require_once(plugin_dir_path(__FILE__).'admin/class-wc-abilita-payment-refund.php');
            require_once(plugin_dir_path(__FILE__).'admin/class-wc-abilita-order-status-handler.php');
            require_once(plugin_dir_path(__FILE__).'admin/class-wc-abilita-order-addon-billing-address.php');
            require_once(plugin_dir_path(__FILE__).'admin/class-wc-abilita-order-addon-status-meta-box.php');
            require_once(plugin_dir_path(__FILE__).'admin/class-wc-abilita-payment-transactions.php');
            require_once(plugin_dir_path(__FILE__).'admin/class-wc-abilita-settings-other.php');
            require_once(plugin_dir_path(__FILE__).'admin/class-wc-abilita-settings-api.php');
            require_once(plugin_dir_path(__FILE__).'admin/class-wc-abilita-settings-status.php');
            require_once(plugin_dir_path(__FILE__).'admin/class-wc-abilita-admin.php');
        }
    }

    public function run()
    {
        /**
         * Add other actions
         */
        $abilita_actions = new WC_Abilita_Add_Actions();

        /**
         * Add other filter
         */
        $abilita_admin_filters    = new WC_Abilita_Admin_Filters();
        $abilita_frontend_filters = new WC_Abilita_Frontend_Filters();

        /**
         * Admin Settings
         */
        if (is_admin()) {
            $admin = new WC_Abilita_Abilita_Admin();
        }

        /**
         * Payment-Gateways
         */
        $wc_abilita_base_payment_loader = new WC_Abilita_Base_Payment_Loader();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }

    public function load_defines()
    {
        define('ABILITA_PAYMENT_LINK_HOMEPAGE_INVOICE' , 'https://abilita.de/payment/zahlungsarten/gesicherte-rechnung-coin4/');
        define('ABILITA_PAYMENT_LINK_HOMEPAGE_SEPA'    , 'https://abilita.de/payment/zahlungsarten/gesicherte-lastschrift-coin4/');
        define('ABILITA_PAYMENT_LINK_HOMEPAGE_ADVANCED', 'https://abilita.de/payment/zahlungsarten/coin4-vorkasse/');
        define('ABILITA_PAYMENT_LINK_HOMEPAGE_AIIA'    , 'https://abilita.de/payment/zahlungsarten/sepa-echtzeitueberweisung-coin4-direkt/');
        define('ABILITA_PAYMENT_LINK_HOMEPAGE_OVERVIEW', 'https://abilita.de/payment/zahlungsarten/');

        define('ABILITA_PAYMENT_CAN_CANCELLED_OR_REAUTHORIZE', [
            'kar',
            'kar_b2b',
            'paypal',
            'cc'
        ]);

        define('ABILITA_PAYMENT_CAN_REFUNDED', [
            'kar',
            'kar_b2b',
            'dd',
            'dd_b2b',
            'paypal',
            'cc'
        ]);

        define('ABILITA_PAYMENT_SPECIFICATION_TABLE', [
            ''        => __('Zahlungsart'       , 'abilita-payments-for-woocommerce'),
            'advance' => __('Vorkasse'          , 'abilita-payments-for-woocommerce'),
            'aiia'    => __('Direkt-Überweisung', 'abilita-payments-for-woocommerce'),
            'cc'      => __('Kreditkarte'       , 'abilita-payments-for-woocommerce'),
            'paypal'  => __('PayPal'            , 'abilita-payments-for-woocommerce'),
            'dd'      => __('SEPA B2C'          , 'abilita-payments-for-woocommerce'),
            'dd_b2b'  => __('SEPA B2B'          , 'abilita-payments-for-woocommerce'),
            'kar'     => __('Rechnung B2C'      , 'abilita-payments-for-woocommerce'),
            'kar_b2b' => __('Rechnung B2B'      , 'abilita-payments-for-woocommerce')
        ]);

        define('ABILITA_STANDARD_STATUSES_CONFIG', [
            'started'            => 'wc-on-hold',
            'pending'            => 'wc-on-hold',
            'completed'          => 'wc-on-hold',
            'error'              => 'wc-failed',
            'canceled'           => 'wc-cancelled',
            'declined'           => 'wc-cancelled',
            'refunded'           => 'wc-refunded',
            'authorized'         => 'wc-pending',
            'registered'         => 'wc-pending',
            'debt_collection'    => 'wc-on-hold',
            'debt_paid'          => 'wc-on-hold',
            'reversed'           => 'wc-cancelled',
            'chargeback'         => 'wc-cancelled',
            'factoring'          => 'wc-on-hold',
            'debt_declined'      => 'wc-cancelled',
            'factoring_declined' => 'wc-cancelled'
        ]);

        define('ABILITA_SALUTATIONS', [
            'm' => 'Herr',
            'f' => 'Frau',
            'd' => 'Divers'
        ]);

        define('ABILITA_PAYMENT_ALLOWED_HTML', [
            'b'      => [],
            'br'     => [],
            'p'      => [],
            'i'      => [],
            'select' => [
                'name'     => [],
                'id'       => [],
                'class'    => [],
                'required' => []
            ],
            'option' => [
                'value'    => [],
                'selected' => []
            ],

        ]);
    }
}