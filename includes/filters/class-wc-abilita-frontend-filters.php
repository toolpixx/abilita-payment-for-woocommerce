<?php declare(strict_types=1);

namespace abilita\payment\filters;

defined('ABSPATH') || exit;

class WC_Abilita_Frontend_Filters {

    public function __construct()
    {
        add_filter('woocommerce_checkout_fields'              , [$this, 'abilita_add_checkout_fields'], 20);
        add_filter('woocommerce_my_account_my_orders_actions', [$this, 'abilita_remove_pay_button_my_account'], 10, 2);
    }

    public function abilita_remove_pay_button_my_account($actions, $order)
    {
        $requestEndpoint                  = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        $viewAccountOrderViewEndpoint     = get_option('woocommerce_myaccount_view_order_endpoint');
        $viewAccountOrderReceivedEndpoint = get_option('woocommerce_checkout_order_received_endpoint');

        if (
            preg_match('/'.$viewAccountOrderViewEndpoint.'/', $requestEndpoint) ||
            preg_match('/'.$viewAccountOrderReceivedEndpoint.'/', $requestEndpoint)
        ) {
            if (isset($actions['view'])) {
                unset($actions['view']);
            }
        }

        if (isset($actions['pay'])) {
            unset($actions['pay']);
        }

        if (isset($actions['cancel'])) {
            unset($actions['cancel']);
        }

        return $actions;
    }

    public function abilita_add_checkout_fields($fields)
    {
        if (get_option('ABILITA_FORM_FIELD_SALUTATION') == '1') {
            $fields['billing']['billing_title'] = [
                'type' => 'select',
                'label' => __('Anrede', 'abilita-payments-for-woocommerce'),
                'clear' => false,
                'options' => [
                    'Bitte wählen' => __('Bitte wählen', 'abilita-payments-for-woocommerce'),
                    'm' => __('Herr', 'abilita-payments-for-woocommerce'),
                    'f' => __('Frau', 'abilita-payments-for-woocommerce'),
                    'd' => __('Divers', 'abilita-payments-for-woocommerce'),
                ],
                'class' => ['form-row-wide'],
                'required' => true,
                'priority' => 1,
                'default' => WC()->session->get('billing_title', '')
            ];
        }

        if (isset($fields['billing']['billing_company']) &&
            get_option('ABILITA_FORM_FIELD_COMPANY_LABEL_TEXT') != ''
        ) {
            $fields['billing']['billing_company']['placeholder'] = get_option('ABILITA_FORM_FIELD_COMPANY_LABEL_TEXT');
        }

        if (get_option('ABILITA_FORM_FIELD_VAT_ID') == '1') {
            $billing_vat_id = [
                'billing_vat_id' => [
                    'label' => __('Umsatzsteuer-ID', 'abilita-payments-for-woocommerce'),
                    'autocomplete' => 'tax-id',
                    'class' => ['form-row-wide', 'update_totals_on_change'],
                    'priority' => 35,
                    'required' => false,
                    'attr' => [
                        'style' => 'display:none'
                    ],
                    'default' => WC()->session->get('billing_vat_id', ''),
                    'placeholder' => get_option("ABILITA_FORM_FIELD_VAT_ID_LABEL_TEXT")
                ]
            ];

            $position = array_search('billing_company', array_keys($fields['billing']));

            $fields['billing'] = array_merge(
                array_slice($fields['billing'], 0, $position + 1, true),
                $billing_vat_id,
                array_slice($fields['billing'], $position + 1, null, true)
            );
        }

        return $fields;
    }
}