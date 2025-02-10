<?php declare(strict_types=1);

namespace abilita\payment\admin;

use \abilita\payment\services\WC_Abilita_Client_Service;

defined('ABSPATH') || exit;

class WC_Abilita_Order_Status_Handler {

    private $abilitaClient;

    public function __construct()
    {
        $this->abilitaClient = new WC_Abilita_Client_Service();

        add_action('woocommerce_create_refund'         , [$this, 'abilita_order_create_refund']);
        add_action('woocommerce_order_status_refunded' , [$this, 'abilita_order_refunded']);
        add_action('woocommerce_order_status_cancelled', [$this, 'abilita_order_cancelled']);
        add_action('admin_notices'                     , [$this, 'abilita_order_status_notice']);
    }

    public function abilita_order_status_notice()
    {
        echo esc_html($this->abilita_order_status_get_notice('abilita_order_status_notice'));
    }

    public function abilita_order_create_refund($refund)
    {
        $orderId         = $refund->get_parent_id();
        $order           = wc_get_order($refund->get_parent_id());
        $payment_details = wc_get_payment_gateway_by_order($order);

        if (
            $payment_details &&
            in_array($payment_details->get_abilita_payment_name(), ABILITA_PAYMENT_CAN_REFUNDED)
        ) {
            [
                $httpStatus,
                $error,
                $response
            ] = $this->abilitaClient->refund_payment([
                'transaction_id' => $order->get_transaction_id(),
                'amount' => $refund->get_amount(),
                'comment' => $refund->get_reason() ?? __('Teilrückerstattung ausgelöst durch Status-Aktualisierung im Shop', 'abilita-payments-for-woocommerce')
            ]);

            $message = 'Die Bestellung #' . $orderId . ' wurde beim Zahlungsdienstleister erfolgreich teilrückerstattet.';
            $noticeType = 'success';

            if ($httpStatus != 200) {
                $message = 'Die Bestellung #' . $orderId . ' konnte beim Zahlungsdienstleister nicht zurückerstattet werden. (Failed by http-status: ' . $httpStatus . ')';
                $noticeType = 'error';
            } elseif ($error) {
                $message = 'Die Bestellung #' . $orderId . ' konnte beim Zahlungsdienstleister nicht zurückerstattet werden. (Failed by api-error...)';
                $noticeType = 'error';
            } elseif (isset($response->error_code) && $response->error_code > 0) {
                $message = 'Die Bestellung #' . $orderId . ' konnte beim Zahlungsdienstleister nicht zurückerstattet werden. (Code: ' . $response->error_code . ' / ' . $response->error_message . ')';
                $noticeType = 'error';
            }

            $notice = $this->create_transient_notice($message, $noticeType);
            set_transient('abilita_order_status_notice_' . $orderId, $notice, 30);
        }
    }

    public function abilita_order_refunded($orderId)
    {
        $order           = wc_get_order($orderId);
        $payment_details = wc_get_payment_gateway_by_order($order);

        if (
            $payment_details &&
            in_array($payment_details->get_abilita_payment_name(), ABILITA_PAYMENT_CAN_REFUNDED)
        ) {
            $orderRefunds = $order->get_refunds();

            if ($orderRefunds) {
                foreach($orderRefunds as $refund ){
                    foreach( $refund->get_items() as $item_id => $item ){
                        $refundedAmount += $item->get_total();
                    }
                }

                $refundedAmount = $order->get_total()+$refundedAmount.PHP_EOL;
            } else {
                $refundedAmount = 0;
            }

            [
                $httpStatus,
                $error,
                $response
            ] = $this->abilitaClient->refund_payment([
                'transaction_id' => $order->get_transaction_id(),
                'amount'         => $refundedAmount,
                'comment'        => __('Komplette Rückerstattung ausgelöst durch Status-Aktualisierung im Shop', 'abilita-payments-for-woocommerce')
            ]);

            $message = 'Die Bestellung #'.$orderId.' wurde beim Zahlungsdienstleister erfolgreich zurückerstattet.';
            $noticeType = 'success';

            if ($httpStatus != 200) {
                $message = 'Die Bestellung #'.$orderId.' konnte beim Zahlungsdienstleister nicht zurückerstattet werden. (Failed by http-status: '.$httpStatus.')';
                $noticeType = 'error';
            } elseif ($error) {
                $message = 'Die Bestellung #'.$orderId.' konnte beim Zahlungsdienstleister nicht zurückerstattet werden. (Failed by api-error...)';
                $noticeType = 'error';
            } elseif (isset($response->error_code) && $response->error_code > 0) {
                $message = 'Die Bestellung #'.$orderId.' konnte beim Zahlungsdienstleister nicht zurückerstattet werden. (Code: '.$response->error_code.' / '.$response->error_message.')';
                $noticeType = 'error';
            }

            $notice = $this->create_transient_notice($message, $noticeType);
            set_transient('abilita_order_status_notice_' . $orderId, $notice, 30);
        }
    }

    public function abilita_order_cancelled($orderId)
    {
        $order           = wc_get_order($orderId);
        $payment_details = wc_get_payment_gateway_by_order($order);

        if ($payment_details && in_array($payment_details->get_abilita_payment_name(), ABILITA_PAYMENT_CAN_CANCELLED_OR_REAUTHORIZE)) {
            [
                $httpStatus,
                $error,
                $response
            ] = $this->abilitaClient->reverse_payment([
                'transaction_id' => $order->get_transaction_id(),
                'comment'        => __('Stornierung ausgelöst durch Status-Aktualisierung im Shop.', 'abilita-payments-for-woocommerce')
            ]);

            $message    = 'Die Bestellung #'.$orderId.' wurde beim Zahlungsdienstleister erfolgreich storniert.';
            $noticeType = 'success';

            if ($httpStatus != 200) {
                $message = 'Die Bestellung #'.$orderId.' konnte beim Zahlungsdienstleister nicht storniert werden. (Failed by http-status: '.$httpStatus.')';
                $noticeType = 'error';
            } elseif ($error) {
                $message = 'Die Bestellung #'.$orderId.' konnte beim Zahlungsdienstleister nicht storniert werden. (Failed by api-error...)';
                $noticeType = 'error';
            } elseif (isset($response->error_code) && $response->error_code > 0) {
                $message = 'Die Bestellung #'.$orderId.' konnte beim Zahlungsdienstleister nicht storniert werden. (Code: '.$response->error_code.' / '.$response->error_message.')';
                $noticeType = 'error';
            }
        } else {
            $message = 'Die Stornierung der Bestellung #'.$orderId.' ist beim Zahlungsdienstleister für diese Zahlungsmethode nicht zu lässig.';
            $noticeType = 'error';
        }

        $notice = $this->create_transient_notice($message, $noticeType);
        set_transient('abilita_order_status_notice_' . $orderId, $notice, 30);
    }

    private function abilita_order_status_get_notice($transientKey)
    {
        global $wpdb;

		$transients = $wpdb->get_results($wpdb->prepare(
            'SELECT option_name FROM '.$wpdb->options.' WHERE option_name LIKE %s',
            [
                '%_transient_'.$transientKey.'%'
            ]
        ));

		if ($transients) {
			foreach ($transients as $transient) {
                $transient_key = str_replace('_transient_', '', $transient->option_name);
                $message       = get_transient($transient_key);

                if ($message) {
                    delete_transient($transient_key);
                    return $message;
                }
			}
		}
    }

    private function create_transient_notice($message, $noticeType)
    {
        $output  = '<div class="notice notice-'.$noticeType.' is-dismissible">';
        $output .= '<p>'.esc_html($message).'</p>';
        $output .= '</div>';
        return $output;
    }
}

$orderStatusHandler = new WC_Abilita_Order_Status_Handler();