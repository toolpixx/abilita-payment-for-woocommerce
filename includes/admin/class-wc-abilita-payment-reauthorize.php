<?php declare(strict_types=1);

namespace abilita\payment\admin;

use abilita\payment\services\WC_Abilita_Client_Service;

defined('ABSPATH') || exit;

if (
    isset($_SERVER['REQUEST_METHOD'])           && 
	$_SERVER['REQUEST_METHOD'] === 'POST'       &&
    isset($_POST['authorizationOrderId'])       &&
    isset($_POST['authorizationPaymentType'])   &&
    isset($_POST['authorizationTransactionId']) &&
    isset($_POST['authorizationAmountNew'])     &&
    !empty($_POST['authorizationAmountNew'])
) {
    $reauthorize = new WC_Abilita_Payment_Reauthorize();
    $reauthorize->execute();
} else if (
	isset($_SERVER['REQUEST_METHOD'])     		 && 
	$_SERVER['REQUEST_METHOD'] === 'POST' 		 &&
    isset($_POST['authorizationOrderId'])        &&
    isset($_POST['authorizationPaymentType'])    &&
    isset($_POST['authorizationTransactionId'])  &&
    isset($_POST['authorizationAmountNew'])      &&
    empty($_POST['authorizationAmountNew'])
) {
    die(wp_json_encode([
        'status'  => 'invalid',
        'code'    => '1000',
        'message' => __('Bitte prüfen Sie Ihre Eingabe.', 'abilita-payments-for-woocommerce')
    ], JSON_THROW_ON_ERROR));
}

class WC_Abilita_Payment_Reauthorize {

    private $abilitaClient;

    public function __construct()
    {
        $this->abilitaClient = new WC_Abilita_Client_Service();
    }

    public function execute()
    {
        $authorizationTransactionId = isset($_POST['authorizationTransactionId']) ? sanitize_text_field(wp_unslash($_POST['authorizationTransactionId'])) : null;
        $authorizationPaymentType   = isset($_POST['authorizationPaymentType'])   ? sanitize_text_field(wp_unslash($_POST['authorizationPaymentType']))   : null;
        $authorizationOrderId       = isset($_POST['authorizationOrderId'])       ? sanitize_text_field(wp_unslash($_POST['authorizationOrderId']))       : null;
        $authorizationAmountNew     = isset($_POST['authorizationAmountNew'])     ? sanitize_text_field(wp_unslash($_POST['authorizationAmountNew']))     : '';
        $authorizationComment       = isset($_POST['authorizationComment'])       ? sanitize_text_field(wp_unslash($_POST['authorizationComment']))       : null;

        $authorizationAmountNew = preg_replace('/\./', '', $authorizationAmountNew);
        $authorizationAmountNew = preg_replace('/,/', '.', $authorizationAmountNew);
        $authorizationAmountNew = number_format((float) $authorizationAmountNew, 2, '.', '');

        [
            $httpStatus,
            $error,
            $response
        ] = $this->abilitaClient->reauthorization_payment([
            'original_transaction_id' => $authorizationTransactionId,
            'payment_type'            => $authorizationPaymentType,
            'order_id'                => $authorizationOrderId,
            'amount'                  => $authorizationAmountNew,
            'execution_date'          => gmdate('Y-m-d'),
            'comment'                 => $authorizationComment ?? __('Manually payment reauthorize by WooCommerce', 'abilita-payments-for-woocommerce')
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