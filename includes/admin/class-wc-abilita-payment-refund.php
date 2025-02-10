<?php declare(strict_types=1);

namespace abilita\payment\admin;

use abilita\payment\services\WC_Abilita_Client_Service;

defined('ABSPATH') || exit;

if (
    isset($_SERVER['REQUEST_METHOD'])     &&
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['refundOrderId'])        &&
    isset($_POST['refundTransactionId'])  &&
    isset($_POST['refundAmountNew'])      &&
    isset($_POST['refundComment'])        &&
    !empty($_POST['refundComment'])
) {
    $reauthorize = new WC_Abilita_Payment_Refund();
    $reauthorize->execute();
} else if (
    isset($_SERVER['REQUEST_METHOD'])     &&
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['refundOrderId'])        &&
    isset($_POST['refundTransactionId'])  &&
    isset($_POST['refundAmountNew'])      &&
    isset($_POST['refundComment'])
) {
    die(wp_json_encode([
        'status'  => 'invalid',
        'code'    => '1000',
        'message' => __('Bitte prüfen Sie Ihre Eingabe.', 'abilita-payments-for-woocommerce')
    ], JSON_THROW_ON_ERROR));
}

class WC_Abilita_Payment_Refund {

    private $abilitaClient;

    public function __construct()
    {
        $this->abilitaClient = new WC_Abilita_Client_Service();
    }

    public function execute()
    {
        $refundTransactionId = isset($_POST['refundTransactionId']) ? sanitize_text_field(wp_unslash($_POST['refundTransactionId'])) : null;
        $refundAmountNew     = isset($_POST['refundAmountNew'])     ? sanitize_text_field(wp_unslash($_POST['refundAmountNew']))     : null;
        $refundComment       = isset($_POST['refundComment'])       ? sanitize_text_field(wp_unslash($_POST['refundComment']))        : null;

        if (!empty($refundAmountNew)) {
            $refundAmountNew = preg_replace('/\./', '', $refundAmountNew);
            $refundAmountNew = preg_replace('/,/', '.', $refundAmountNew);
            $refundAmountNew = number_format((float) $refundAmountNew, 2, '.', '');
        }

        [
            $httpStatus,
            $error,
            $response
        ] = $this->abilitaClient->refund_payment([
            'transaction_id' => $refundTransactionId,
            'amount'         => $refundAmountNew,
            'comment'        => $refundComment ?? __('Manually payment refund by WooCommerce', 'abilita-payments-for-woocommerce')
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
                'status'  => 'error',
                'code'    => $response->error_code,
                'message' => $response->error_message
            ], JSON_THROW_ON_ERROR));
        }

        // @todo Bestellung im Shop ReAuthorisieren

        die(wp_json_encode([
            'status'  => 'sucess'
        ], JSON_THROW_ON_ERROR));
    }
}