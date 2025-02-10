<?php declare(strict_types=1);

namespace abilita\payment\services;

use abilita\payment\helper\WC_Abilita_Helper;
use abilita\payment\services\WC_Abilita_Logger_Service;
use WC_Payment_Gateways;

defined('ABSPATH') || exit;

class WC_Abilita_Client_Service
{
    const HTTP_USER_AGENT_NAME          = 'abilita WooCommerce-Payment';

    const ABILITA_TRANSACTIONS_ENDPOINT = 'rest/transactions';
    const ABILITA_PAYMENT_ENDPOINT      = 'rest/payment';
    const ABILITA_MANDATE_ENDPOINT      = 'rest/create_mandate_reference';
    const ABILITA_AUTHORIZE_ENDPOINT    = 'rest/authorize';
    const ABILITA_REVERSE_ENDPOINT      = 'rest/reverse';
    const ABILITA_REFUND_ENDPOINT       = 'rest/refund';

    const POSTBACK_ADVANCED             = '/wc-api/abilita-advance-postback';
    const POSTBACK_AIIA                 = '/wc-api/abilita-aiia-postback';
    const POSTBACK_CREDITCARD           = '/wc-api/abilita-creditcard-postback';
    const POSTBACK_INVOICE              = '/wc-api/abilita-invoice-postback';
    const POSTBACK_PAYPAL               = '/wc-api/abilita-paypal-postback';
    const POSTBACK_SEPA                 = '/wc-api/abilita-sepa-postback';

    const PAYMENT_TYPE_ADVANCED         = 'advance';
    const PAYMENT_TYPE_AIIA             = 'aiia';
    const PAYMENT_TYPE_CREDITCARD       = 'cc';
    const PAYMENT_TYPE_PAYPAL           = 'paypal';
    const PAYMENT_TYPE_INVOICE_B2C      = 'kar';
    const PAYMENT_TYPE_INVOICE_B2B      = 'kar_b2b';
    const PAYMENT_TYPE_SEPA_B2C         = 'dd';
    const PAYMENT_TYPE_SEPA_B2B         = 'dd_b2b';

    const PAYMENT_GATEWAY_INVOICE_ID    = 'abilita-invoice';
    const PAYMENT_GATEWAY_SEPA_ID       = 'abilita-sepa';

    private $abilitaHelper;
    private $abilitaLogger;
    private $apiEndpoint;
    private $apiApikey;
    private $apiApikeyOutgoing;
    private $apiRuntime;

    public function __construct()
    {
        $this->abilitaHelper = new WC_Abilita_Helper();
        $this->abilitaLogger = new WC_Abilita_Logger_Service();

        $this->apiRuntime    = get_option('ABILITA_API_RUNTIME', '');

        if ($this->apiRuntime == 'LIVE') {
            $this->apiEndpoint       = rtrim(get_option('ABILITA_ENDPOINT_LIVE', ''), '/') . '/';
            $this->apiApikey         = get_option('ABILITA_API_KEY_LIVE', '');
            $this->apiApikeyOutgoing = get_option('ABILITA_OUTGOING_API_KEY_LIVE', '');
        } else {
            $this->apiEndpoint       = rtrim(get_option('ABILITA_ENDPOINT_TEST', ''), '/') . '/';
            $this->apiApikey         = get_option('ABILITA_API_KEY_TEST', '');
            $this->apiApikeyOutgoing = get_option('ABILITA_OUTGOING_API_KEY_TEST', '');
        }
    }

    public function execute($method, $context, $payload = null, $function = null)
    {
        try {
            $error = null;
            $payload['api_key'] = $this->apiApikey;
            $payload['customer_ip'] = $this->abilitaHelper->get_client_ip();

            if (!empty($this->abilitaHelper->get_proxy_ip())) {
                $payload['customer_ip_proxy'] = $this->abilitaHelper->get_proxy_ip();
            }

            $headers = [
                'User-Agent' => self::HTTP_USER_AGENT_NAME,
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->apiApikey . ':' . $this->apiApikeyOutgoing),
            ];

            $args = [
                'method'      => $method,
                'timeout'     => 30,
                'redirection' => 10,
                'headers'     => $headers,
                'body'        => in_array($method, ['POST', 'PUT']) ? wp_json_encode($payload) : null,
            ];

            $response = wp_remote_request($this->apiEndpoint . $context, $args);

            if (is_wp_error($response)) {
                $error      = $response->get_error_message();
                $httpStatus = 0;
                $response   = null;
            } else {
                $httpStatus = wp_remote_retrieve_response_code($response);
                $response   = wp_remote_retrieve_body($response);
            }

            $response = !empty($response) ? json_decode($response) : null;

            $this->abilitaLogger->log('info', __CLASS__, $function, [
                'httpStatus' => $httpStatus,
                'error'      => $error,
                'payload'    => $payload,
                'response'   => $response
            ]);

            return [
                $httpStatus,
                $error,
                $response
            ];
        } catch (Exception $e) {
            return [
                404,
                null,
                null
            ];
        }
    }

    public function get_transaction_list($params)
    {
        $param = null;
        if ($params) {
            $param = '?' . http_build_query($params);
        }

        [
            $httpStatus,
            $error,
            $transactionList
        ] = $this->execute('GET', self::ABILITA_TRANSACTIONS_ENDPOINT . $param, null, __FUNCTION__);

        return [
            $httpStatus,
            $error,
            $transactionList
        ];
    }

    public function get_transaction($transactionId)
    {
        return $this->execute('GET', self::ABILITA_TRANSACTIONS_ENDPOINT . '/' . $transactionId, null, __FUNCTION__);
    }

    public function get_transaction_log($transactionId)
    {
        return $this->execute('GET', self::ABILITA_TRANSACTIONS_ENDPOINT . '/' . $transactionId . '/log', null, __FUNCTION__);
    }

    public function invoice_payment($payload)
    {
        $payload['postback_url'] = get_site_url() . self::POSTBACK_INVOICE;
        $payload['payment_type'] = self::PAYMENT_TYPE_INVOICE_B2C;

        $paymentGateway = $this->get_payment_gateway_config(self::PAYMENT_GATEWAY_INVOICE_ID);
        if (!empty($payload['company']) && $paymentGateway->get_option('allow_b2b') == 'yes') {
            $payload['payment_type'] = self::PAYMENT_TYPE_INVOICE_B2B;
        } else if (!empty($payload['company_vat_id']) && $paymentGateway->get_option('allow_b2b') == 'yes') {
            $payload['payment_type'] = self::PAYMENT_TYPE_INVOICE_B2B;
        }

        return $this->execute('POST', self::ABILITA_PAYMENT_ENDPOINT, $payload, __FUNCTION__);
    }

    public function sepa_payment($payload)
    {
        $payload['postback_url'] = get_site_url() . self::POSTBACK_SEPA;
        $payload['payment_type'] = self::PAYMENT_TYPE_SEPA_B2C;

        $paymentGateway = $this->get_payment_gateway_config(self::PAYMENT_GATEWAY_SEPA_ID);
        if (!empty($payload['company']) && $paymentGateway->get_option('allow_b2b') == 'yes') {
            $payload['payment_type'] = self::PAYMENT_TYPE_SEPA_B2B;
        }  else if (!empty($payload['company_vat_id']) && $paymentGateway->get_option('allow_b2b') == 'yes') {
            $payload['payment_type'] = self::PAYMENT_TYPE_SEPA_B2B;
        }

        return $this->execute('POST', self::ABILITA_PAYMENT_ENDPOINT, $payload, __FUNCTION__);
    }

    public function advance_payment($payload)
    {
        $payload['postback_url'] = get_site_url() . self::POSTBACK_ADVANCED;
        $payload['payment_type'] = self::PAYMENT_TYPE_ADVANCED;

        return $this->execute('POST', self::ABILITA_PAYMENT_ENDPOINT, $payload, __FUNCTION__);
    }

    public function paypal_payment($payload)
    {
        $payload['postback_url'] = get_site_url() . self::POSTBACK_PAYPAL;
        $payload['payment_type'] = self::PAYMENT_TYPE_PAYPAL;

        return $this->execute('POST', self::ABILITA_PAYMENT_ENDPOINT, $payload, __FUNCTION__);
    }

    public function creditcard_payment($payload)
    {
        $payload['postback_url'] = get_site_url() . self::POSTBACK_CREDITCARD;
        $payload['payment_type'] = self::PAYMENT_TYPE_CREDITCARD;

        return $this->execute('POST', self::ABILITA_PAYMENT_ENDPOINT, $payload, __FUNCTION__);
    }

    public function aiia_payment($payload)
    {
        $payload['postback_url'] = get_site_url() . self::POSTBACK_AIIA;
        $payload['payment_type'] = self::PAYMENT_TYPE_AIIA;

        return $this->execute('POST', self::ABILITA_PAYMENT_ENDPOINT, $payload, __FUNCTION__);
    }

    public function reauthorization_payment($payload)
    {
        return $this->execute('POST', self::ABILITA_AUTHORIZE_ENDPOINT, $payload, __FUNCTION__);
    }

    public function reverse_payment($payload)
    {
        return $this->execute('POST', self::ABILITA_REVERSE_ENDPOINT, $payload, __FUNCTION__);
    }

    public function refund_payment($payload)
    {
        return $this->execute('POST', self::ABILITA_REFUND_ENDPOINT, $payload, __FUNCTION__);
    }

    private function get_payment_gateway_config($paymentGatewayId)
    {
        $paymentGateways = WC_Payment_Gateways::instance();
        return $paymentGateways->payment_gateways()[$paymentGatewayId];
    }
}