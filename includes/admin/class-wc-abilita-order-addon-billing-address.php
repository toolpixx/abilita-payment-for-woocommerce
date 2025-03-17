<?php declare(strict_types=1);

namespace abilita\payment\admin;

defined('ABSPATH') || exit;

class WC_Abilita_Order_Addon_Billing_Address {

    public function __construct()
    {
        add_action('woocommerce_order_formatted_billing_address'       , [$this, 'abilita_order_formatted_billing_address'], 10, 2);
        add_action('woocommerce_admin_order_data_after_billing_address', [$this, 'abilita_admin_order_data_after_billing_address']);
    }

    public function abilita_order_formatted_billing_address($billingAddress, $order)
    {
        $order = wc_get_order($order->get_id());
        if (get_option('woocommerce_custom_orders_table_enabled') == 'yes') {
            $salutation = $order->get_meta('ABILITA_CUSTOMER_SALUTATION');
        } else {
            $salutation = get_post_meta($order->get_id(), 'ABILITA_CUSTOMER_SALUTATION', true);
        }

        if (isset(ABILITA_SALUTATIONS[$salutation])) {
            $billingAddress['first_name'] = ABILITA_SALUTATIONS[$salutation].' '.$billingAddress['first_name'];
        }

        return $billingAddress;
    }

    public function abilita_admin_order_data_after_billing_address($order)
    {
        if (get_option('woocommerce_custom_orders_table_enabled') == 'yes') {
            $vatNumber = $order->get_meta('ABILITA_CUSTOMER_VAT_ID');
            $birthday  = $order->get_meta('ABILITA_CUSTOMER_BIRTHDAY');
        } else {
            $vatNumber = get_post_meta($order->get_id(), 'ABILITA_CUSTOMER_VAT_ID', true);
            $birthday  = get_post_meta($order->get_id(), 'ABILITA_CUSTOMER_BIRTHDAY', true);
        }

        if (!empty($birthday)) {
            echo sprintf(
                wp_kses(
                /* translators: %s: Birthday of buyer */
                    __('<b>Geburtsdatum:</b><br> %s', 'abilita-payments-for-woocommerce'),
                    [
                        'b' => true,
                        'br' => true
                    ]
                ),
                esc_html($birthday)
            );
        }

        if (!empty($vatNumber)) {
            echo sprintf(
                wp_kses(
                /* translators: %s: Birthday of buyer */
                    __('<b>Umsatzsteuer-ID:</b><br> %s', 'abilita-payments-for-woocommerce'),
                    [
                        'b' => true,
                        'br' => true
                    ]
                ),
                esc_html($vatNumber)
            );
        }
    }
}

$orderAddonBillingAddress = new WC_Abilita_Order_Addon_Billing_Address();