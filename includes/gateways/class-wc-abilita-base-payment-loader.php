<?php declare(strict_types=1);

namespace abilita\payment\gateways;

use WC_Payment_Gateway;

defined('ABSPATH') || exit;

class WC_Abilita_Base_Payment_Loader {

    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'add_abilita_all_payment_method'], 0);
    }

    public function add_abilita_all_payment_method()
    {
        if (!class_exists('WC_Payment_Gateway')) {
            return;
        }

        require_once(plugin_dir_path(__FILE__).'class-wc-abilita-base-payment.php');
        require_once(plugin_dir_path(__FILE__).'class-wc-abilita-invoice-payment.php');
        require_once(plugin_dir_path(__FILE__).'class-wc-abilita-sepa-payment.php');
        require_once(plugin_dir_path(__FILE__).'class-wc-abilita-aiia-payment.php');
        require_once(plugin_dir_path(__FILE__).'class-wc-abilita-advance-payment.php');
        require_once(plugin_dir_path(__FILE__).'class-wc-abilita-paypal-payment.php');
        require_once(plugin_dir_path(__FILE__).'class-wc-abilita-creditcard-payment.php');

        add_filter('woocommerce_payment_gateways', [$this, 'abilita_add_payments']);
    }

    public function abilita_add_payments($methods)
    {
        $methods[] = WC_Abilita_Invoice_Payment::class;
        $methods[] = WC_Abilita_Sepa_Payment::class;
        $methods[] = WC_Abilita_Aiia_Payment::class;
        $methods[] = WC_Abilita_Advance_Payment::class;
        $methods[] = WC_Abilita_Paypal_Payment::class;
        $methods[] = WC_Abilita_CreditCard_Payment::class;

        return $methods;
    }
}
