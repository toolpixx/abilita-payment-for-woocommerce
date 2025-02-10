<?php declare(strict_types=1);

namespace abilita\payment\admin;

use abilita\payment\services\WC_Abilita_Client_Service;

defined('ABSPATH') || exit;

if (
    isset($_SERVER['REQUEST_METHOD'])     &&
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['cancelPaymentType'])    &&
    isset($_POST['cancelTransactionId'])  &&
    isset($_POST['cancelComment'])        &&
    !empty($_POST['cancelComment'])
) {
    $reauthorize = new WC_Abilita_Payment_Cancel();
    $reauthorize->execute();
} else if (
    isset($_SERVER['REQUEST_METHOD'])     &&
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['cancelPaymentType'])    &&
    isset($_POST['cancelTransactionId'])  &&
    isset($_POST['cancelComment'])        &&
    empty($_POST['cancelComment'])
) {
    die(wp_json_encode([
        'status'  => 'invalid',
        'code'    => '1000',
        'message' => __('Bitte prüfen Sie Ihre Eingabe.', 'abilita-payments-for-woocommerce')
    ], JSON_THROW_ON_ERROR));
}

class WC_Abilita_Payment_Cancel {

    private $abilitaClient;

    public function __construct()
    {
        $this->abilitaClient = new WC_Abilita_Client_Service();
    }

    public function execute()
    {
        $cancelTransactionId = isset($_POST['cancelTransactionId']) ? sanitize_text_field(wp_unslash($_POST['cancelTransactionId'])) : null;
        $cancelComment       = isset($_POST['cancelComment'])       ? sanitize_text_field(wp_unslash($_POST['cancelComment']))       : null;

        [
            $httpStatus,
            $error,
            $response
        ] = $this->abilitaClient->reverse_payment([
            'transaction_id' => $cancelTransactionId,
            'comment'        => $cancelComment ?? __('Manually payment reverse by WooCommerce', 'abilita-payments-for-woocommerce')
        ]);

        if ($httpStatus != 200) {
            die(wp_json_encode([
                'status'  => 'error',
                'code'    => 0,
                'message' => 'Failed by http-status: '.$httpStatus
            ], JSON_THROW_ON_ERROR));
        }

        if ($error) {
            die(wp_json_encode([
                'status'  => 'error',
                'code'    => 0,
                'message' => __('Failed by api-error...', 'abilita-payments-for-woocommerce')
            ], JSON_THROW_ON_ERROR));
        }

        if (isset($response->error_code) && $response->error_code > 0) {
            die(wp_json_encode([
                'status' => 'error',
                'code' => $response->error_code,
                'message' => $response->error_message
            ], JSON_THROW_ON_ERROR));
        }

        // @todo Bestellung im Shop stornieren

        die(wp_json_encode([
            'status'  => 'sucess'
        ], JSON_THROW_ON_ERROR));
    }
}