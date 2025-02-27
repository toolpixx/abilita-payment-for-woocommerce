<?php declare(strict_types=1);

namespace abilita\payment\admin;

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use abilita\payment\services\WC_Abilita_Client_Service;

defined('ABSPATH') || exit;

class WC_Abilita_Order_Addon_Status_Meta_Box
{
    private $abilitaClient;
    private $abilitaMappedStatuses;

    public function __construct()
    {
        $this->abilitaClient = new WC_Abilita_Client_Service();
        $this->abilitaMappedStatuses = $this->get_payment_statuses();

        add_action('add_meta_boxes', [$this, 'abiliita_add_meta_boxes_order']);
    }

    public function abiliita_add_meta_boxes_order($post)
    {
        $screen = wc_get_container()->get(CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled()
            ? wc_get_page_screen_id('shop-order')
            : 'shop_order';

        add_meta_box(
            'abilita_add_meta_boxes_order_status',
            '<span style="display:inline-block"><img src="'.plugin_dir_url(__FILE__).'../../assets/images/abilita.png'.'" style="padding-right:10px;">abilita - Status</span>',
            [$this, 'abilita_get_order_status_metabox'],
            $screen,
            'side',
            'high'
        );
    }

    public function abilita_get_order_status_metabox($orderScreen)
    {
        // $order = ($order instanceof WP_Post) ? wc_get_order($order->ID) : $order;
        // Global comes from woocommerce
        global $theorder;

        // Map global to local variable
        $order = $theorder;
        if (!$order) {
            return;
        }

        if (!str_contains($order->get_payment_method(), 'abilita-')) {
            return;
        }

        $i = 1;
        if (!empty($order->get_transaction_id())) {
            [
                $httpStatus,
                $error,
                $transaction
            ] = $this->abilitaClient->get_transaction($order->get_transaction_id());

            if ($httpStatus != 200) {
				
				wp_admin_notice(
					sprintf(
						/* translators: %s: HTTP-Status of response */	
						__('Es ist ein Fehler aufgetreten. Es können keine Transaktions-Details angezeigt werden. (Failed by http-status: %s)', 'abilita-payments-for-woocommerce'),
						$httpStatus
					), 
					['type' => 'error']
				);
				
				/** REMOVE
                wp_admin_notice(__('Es ist ein Fehler aufgetreten. Es können keine Transaktions-Details angezeigt werden. (Failed by http-status: '.$httpStatus.')', 'abilita-payments-for-woocommerce'), ['type' => 'error']);
				**/
				
            } elseif ($error) {
                wp_admin_notice(__('Es ist ein Fehler aufgetreten. Es können keine Transaktions-Details angezeigt werden. (Failed by api-error...)', 'abilita-payments-for-woocommerce'), ['type' => 'error']);
            } elseif (isset($transaction->error_code) && $transaction->error_code > 0) {
				wp_admin_notice(
					sprintf(
						/* translators: %s: HTTP-Errorcode and HTTP-Errormessage of response */	
						__('Es ist ein Fehler aufgetreten. Es können keine Transaktions-Details angezeigt werden. (Code: %s)', 'abilita-payments-for-woocommerce'),
						$transaction->error_code.' / '.$transaction->error_message
					), 
					['type' => 'error']
				);
            }

            if (isset($this->abilitaMappedStatuses[$transaction->status_code])) {
                $orderStatus = $this->abilitaMappedStatuses[$transaction->status_code];
                [
                    $httpStatus,
                    $error,
                    $transactionLog
                ] = $this->abilitaClient->get_transaction_log($order->get_transaction_id());

                if ($httpStatus != 200) {
					wp_admin_notice(
						sprintf(
							/* translators: %s: HTTP-Status of response */	
							__('Es ist ein Fehler aufgetreten. Es können keine Transaktions-Details angezeigt werden. (Failed by http-status: %s)', 'abilita-payments-for-woocommerce'),
							$httpStatus
						), 
						['type' => 'error']
					);
                } elseif ($error) {
                    wp_admin_notice(__('Es ist ein Fehler aufgetreten. Es können keine Transaktions-Details angezeigt werden. (Failed by api-error...)', 'abilita-payments-for-woocommerce'), ['type' => 'error']);
                } elseif (isset($transactionLog->error_code) && $transactionLog->error_code > 0) {
					wp_admin_notice(
						sprintf(
							/* translators: %s: HTTP-Errorcode and HTTP-Errormessage of response */	
							__('Es ist ein Fehler aufgetreten. Es können keine Transaktions-Details angezeigt werden. (Code: %s)', 'abilita-payments-for-woocommerce'),
							$transaction->error_code.' / '.$transaction->error_message
						), 
						['type' => 'error']
					);
                }

                echo '<div style="padding:5px;background:#efefef;text-align:center;font-weight:bold;margin-bottom:10px">'.esc_html($orderStatus).'</div>';
                foreach ($transactionLog as $transaction) {
                    echo '<div style="font-weight:bold;">'.esc_html($i).'. '.esc_html($transaction->type).'</div>';
                    echo esc_html(gmdate('d.m.Y / H:i:s', strtotime($transaction->created_at))).' Uhr<br>';
                    echo esc_html($transaction->message).'<br>';
                    echo '<hr>';
                    $i++;
                }
            }
        }
    }

    private function get_payment_statuses()
    {
        return [
            ''   => __('Zahlungsstatus', 'abilita-payments-for-woocommerce'),
            '1'  => __('Gestartet', 'abilita-payments-for-woocommerce'),
            '2'  => __('Ausstehend', 'abilita-payments-for-woocommerce'),
            '3'  => __('Abgeschlossen', 'abilita-payments-for-woocommerce'),
            '4'  => __('Fehlgeschlagen', 'abilita-payments-for-woocommerce'),
            '12' => __('Storniert', 'abilita-payments-for-woocommerce'),
            '6'  => __('Abgelehnt', 'abilita-payments-for-woocommerce'),
            '7'  => __('Zurückerstattet', 'abilita-payments-for-woocommerce'),
            '9'  => __('Registriert', 'abilita-payments-for-woocommerce')
        ];
    }
}

$orderAddonStatusMetaBox = new WC_Abilita_Order_Addon_Status_Meta_Box();