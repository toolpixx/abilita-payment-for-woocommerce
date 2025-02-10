<?php declare(strict_types=1);

namespace abilita\payment\admin;

use abilita\payment\services\WC_Abilita_Client_Service;
use DateTime;

defined('ABSPATH') || exit;

class WC_Abilita_Payment_Transactions {

    private $mappedAbilitaStatuses;

    public function __construct(
        $mappedAbilitaStatuses
    )
    {
        $this->mappedAbilitaStatuses = $mappedAbilitaStatuses;
    }

    public function execute()
    {
        $abilitaPaymentNames    = $this->get_payment_names();
        $abilitaPaymentStatuses = $this->get_mapped_abilita_statuses();

        wp_enqueue_script('abilita-js', plugins_url('assets/js/PaymentTransactions.js', __FILE__), ['jquery'], WC_ABILITA_PAYMENT_VERSION, true);
        wp_enqueue_style('abilita-css', plugins_url('assets/css/PaymentTransactions.css', __FILE__), WC_ABILITA_PAYMENT_VERSION, true);

        $dateFrom = gmdate('Y-m-d', strtotime('Last day'));
        $dateTo   = gmdate('Y-m-d');

        $payload = $filterPaymentType = $filterPaymentStatus = $query = '';
        if (isset($_POST['paymentType']) && !empty($_POST['paymentType'])) {
            $filterPaymentType = sanitize_text_field(wp_unslash($_POST['paymentType']));
        }

        if (isset($_POST['paymentStatus']) && !empty($_POST['paymentStatus'])) {
            $filterPaymentStatus = sanitize_text_field(wp_unslash($_POST['paymentStatus']));
        } else {
            $filterPaymentStatus = null;
        }

        if (isset($_POST['dateFrom']) && !empty($_POST['dateFrom'])) {
            $dateFrom = sanitize_text_field(wp_unslash($_POST['dateFrom']));
        } else if (isset($_POST['dateFrom']) && empty($_POST['dateFrom'])) {
            $dateFrom = gmdate('Y-m-d', strtotime('first day of january this year'));
        }

        if (isset($_POST['dateTo']) && !empty($_POST['dateTo'])) {
            $dateTo = sanitize_text_field(wp_unslash($_POST['dateTo']));
        } else if (isset($_POST['dateTo']) && empty($_POST['dateTo'])) {
            $dateTo = gmdate('Y-m-d');
        }

        $payload = [
            'status' => $filterPaymentStatus,
            'from'   => $this->get_iso8601_date($dateFrom),
            'to'     => $this->get_iso8601_date($dateTo.' 23:59:59')
        ];

        if (isset($_POST['search']) && isset($_POST['query'])) {
            $query = sanitize_text_field(wp_unslash($_POST['query']));

            $dateFromTimeStamp = strtotime($dateFrom);
            $dateToTimeStamp   = strtotime($dateTo);
            if ($dateFromTimeStamp > $dateToTimeStamp) {
                [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
            }

            $payload = [
                'status' => $filterPaymentStatus,
                'from'   => $this->get_iso8601_date($dateFrom),
                'to'     => $this->get_iso8601_date($dateTo.' 23:59:59')
            ];
        }

        if (!empty(get_option('ABILITA_API_RUNTIME'))) {

            $abilitaClient = new WC_Abilita_Client_Service();
            [
                $httpStatus,
                $error,
                $transactionList
            ] = $abilitaClient->get_transaction_list($payload);

            if ($httpStatus != 200) {
                wp_admin_notice(__('Beim abrufen der Transaktionen ist ein Fehler aufgetreten.', 'abilita-payments-for-woocommerce'), ['type' => 'error']);
            }

            if ($error) {
                wp_admin_notice(__('Beim abrufen der Transaktionen ist ein Fehler aufgetreten.', 'abilita-payments-for-woocommerce'), ['type' => 'error']);
            }

            if ($payload) {
                $transactions = null;
                foreach ($transactionList as $transaction) {
                    if (!empty($filterPaymentType)) {
                        if (isset($transaction->order_id) && preg_match('/'.$query.'/', $transaction->order_id) && $transaction->payment_method == $filterPaymentType) {
                            $transactions[] = $transaction;
                        }
                    } else if (!empty($query) && empty($filterPaymentType)) {
                        if (isset($transaction->order_id) && preg_match('/'.$query.'/', $transaction->order_id)) {
                            $transactions[] = $transaction;
                        }
                    } else if (empty($query) && !empty($filterPaymentType)) {
                        if ($transaction->payment_method == $filterPaymentType) {
                            $transactions[] = $transaction;
                        }
                    } else if (empty($query) && empty($filterPaymentType)) {
                        $transactions[] = $transaction;
                    }
                }
            } else {
                $transactions = $transactionList;
            }
        } else {
            wp_admin_notice(__('Bitte prüfen Sie Ihre API Zugangsdaten.', 'abilita-payments-for-woocommerce'), ['type' => 'error']);
            $transactions = null;
        }

        $filterOptions = [
            'paymentType' => [
                'options'  => $abilitaPaymentNames,
                'selected' => $filterPaymentType
            ],
            'paymentStatus' => [
                'options'  => $abilitaPaymentStatuses,
                'selected' => $filterPaymentStatus
            ],
        ];

        require_once('partials/PaymentTransactions.php');
        require_once('partials/ModalCancel.php');
        require_once('partials/ModalReauthorize.php');
        require_once('partials/ModalRefund.php');
    }

    private function get_mapped_abilita_statuses()
    {
        $mappedAbilitaStatuses = $this->mappedAbilitaStatuses;
        foreach ($mappedAbilitaStatuses as $key => $item) {
            $mappedAbilitaStatuses[$key] = $item['text'].' ('.$item['name'].')';
        }

        return array_replace(['' => __('Zahlungsstatus', 'abilita-payments-for-woocommerce')], $mappedAbilitaStatuses);
    }

    private function get_payment_names()
    {
        return ABILITA_PAYMENT_SPECIFICATION_TABLE;
    }

    private function get_iso8601_date($date)
    {
        $datetime = new DateTime($date);
        return $datetime->format(DateTime::ATOM);
    }
}

