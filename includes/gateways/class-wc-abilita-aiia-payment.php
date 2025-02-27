<?php declare(strict_types=1);

namespace abilita\payment\gateways;

use abilita\payment\gateways\WC_Abilita_Base_Payment;
use abilita\payment\services\WC_Abilita_Client_Service;
use abilita\payment\services\WC_Abilita_Form_Service;

defined('ABSPATH') || exit;

class WC_Abilita_Aiia_Payment extends WC_Abilita_Base_Payment
{
    public $abilitaFormService;
    public $id;
    public $abilita_payment_name;
    public $icon;
    public $has_fields;
    public $method_title;
    public $method_description;
    public $abilita_pay_box_link;
    public $abilita_pay_box_title;
    public $abilita_pay_box_text;
    public $abilita_pay_box_image;
    public $title;
    public $description;
    public $payload;
    public $enabled;
    public $completeState;
    public $allowB2B;
    public $minAmount;
    public $maxAmount;
    public $countries;
    public $blockedCount;
    public $loggedInCustomer;
    public $loggedInCustomerOrdersCount;
    public $allowDifferentAddress;

    public $bankAccountHolder;
    public $bankName;
    public $bankIban;
    public $bankBic;

    public function __construct()
    {
        $this->abilitaFormService    = new WC_Abilita_Form_Service();
        $this->id                    = 'abilita-aiia';
        $this->abilita_payment_name  = 'aiia';
        $this->icon                  = plugin_dir_url(false).'abilita-payments-for-woocommerce/assets/images/'.$this->id.'.png';
        $this->has_fields             = true;
        $this->method_title          = __('abilita PAY (Coin4Direkt)', 'abilita-payments-for-woocommerce');
        $this->method_description    = __('Mit der SEPA Direkt-Überweisung können Sie den Betrag sofort und sicher an uns überweisen, sodass wir Ihre Bestellung umgehend bearbeiten können.', 'abilita-payments-for-woocommerce');
        $this->abilita_pay_box_link  = ABILITA_PAYMENT_LINK_HOMEPAGE_AIIA;
        $this->abilita_pay_box_title = __('Direkt-Überweisung', 'abilita-payments-for-woocommerce');
        $this->abilita_pay_box_text  = __('Mit der SEPA-Echtzeitüberweisung sorgen Sie für eine schnelle und sichere Zahlungsabwicklung für Ihre Kunden.', 'abilita-payments-for-woocommerce');
        $this->abilita_pay_box_image = plugin_dir_url(false).'abilita-payments-for-woocommerce/assets/images/coin4_direkt.png';

        $this->init_form_fields();
        $this->init_settings();

        $this->title             = $this->get_option('title');
        $this->description       = $this->get_option('description');
        $this->enabled           = $this->get_option('enabled');
        $this->completeState     = $this->get_option('complete_state');
        $this->allowB2B          = 'yes';
        $this->minAmount         = 0;
        $this->maxAmount         = 1000000;
        $this->countries         = $this->get_payment_countries();
        $this->blockedCount      = 1000000000;
        $this->loggedInCustomer  = 'no';

        add_action('woocommerce_api_abilita-aiia-success'  , [$this, 'abilita_aiia_success']);
        add_action('woocommerce_api_abilita-aiia-error'    , [$this, 'abilita_aiia_error']);
        add_action('woocommerce_api_abilita-aiia-postback' , [$this, 'abilita_aiia_postback']);

        parent::__construct();
    }

    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled' => [
                'title'   =>  __('Zahlungsart', 'abilita-payments-for-woocommerce'),
                'type'    => 'checkbox',
                'label'   => __('Ja / Nein', 'abilita-payments-for-woocommerce'),
                'default' => 'no'
            ],
            'title' => [
                'title'       => __('Titel', 'abilita-payments-for-woocommerce'),
                'type'        => 'text',
                'default'     => __('Direkte Bank-Überweisung', 'abilita-payments-for-woocommerce'),
                'description' => __( 'Beschreibung der Zahlungsmethode, die Kunden bei der Kaufabwicklung sehen.', 'abilita-payments-for-woocommerce' ),
                'desc_tip'    => true,
            ],
            'description' => [
                'title'       => __('Beschreibung', 'abilita-payments-for-woocommerce'),
                'type'        => 'textarea',
                'default'     => $this->method_description,
                'description' => __( 'Beschreibung der Zahlungsmethode, die Kunden auf der Website sehen.', 'abilita-payments-for-woocommerce' ),
                'desc_tip'    => true,
            ],
            'complete_state' => [
                'title' => __('Bestellstatus', 'abilita-payments-for-woocommerce'),
                'description' => __('Welchen Bestellstatus soll die Bestellung nach einer fehlerfreien Zahlung erhalten?', 'abilita-payments-for-woocommerce'),
                'type' => 'select',
                'options' => array_merge(['disabled' => __('Bitte wählen', 'abilita-payments-for-woocommerce')], wc_get_order_statuses()),
                'default' => 'wc-pending',
                'desc_tip' => true,
            ],
            'countries' => [
                'title'       => 'Erlauben für folgende Länder',
                'type'        => 'multiselect',
                'css'         => 'height:120px',
                'description' => 'Wenn Sie weitere Länder hinterlegen möchten, müssen Sie diese erst unter <a href="/wp-admin/admin.php?page=wc-settings&tab=general">"allgemeinen Einstellungen" => "In bestimmte Länder verkaufen"</a> hinterlegen.',
                'default'     => ['DE', 'AT', 'CH'],
                'options'     => WC()->countries->get_allowed_countries()
            ],
            'allow_different_address' => [
                'title'       => __('Abweichende Lieferadresse erlauben?', 'abilita-payments-for-woocommerce'),
                'type'        => 'checkbox',
                'description' => '<span style="background: red;color: white;padding: 10px;">'.__('Hiermit bestätige ich ausdrücklich, dass Kunden eine abweichende Lieferadresse hinterlegen dürfen.', 'abilita-payments-for-woocommerce').'</span>',
                'default'     => 'false'
            ],
        ];
    }

    public function payment_fields()
    {
        if ($this->description) {
            echo esc_html(
				$this->description
			);
        }
    }

    public function validate_fields()
    {
        $formValidateTitle   = $this->abilitaFormService->validate_Title();
		// $_POST will validate in $this->abilitaFormService->validate_different_delivery_address
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $formValidateAddress = $this->abilitaFormService->validate_different_delivery_address($this->allowDifferentAddress, $_POST);

        if (!$formValidateTitle   ||
            !$formValidateAddress
        ) {
            return;
        }
    }

    public function process_payment($orderId)
    {
        $this->orderId = $orderId;
        $this->order   = wc_get_order($this->orderId);

        if (!$this->order || is_wp_error($this->order)) {
            wp_send_json_error(['message' => __('Fehler bei der Erstellung der Bestellung. Bitte versuchen Sie es erneut.', 'abilita-payments-for-woocommerce')]);
            return;
        }

        $abilitaClient = new WC_Abilita_Client_Service();
        [
            $httpStatus,
            $error,
            $response
        ] = $abilitaClient->aiia_payment($this->get_aiia_payload());

        $this->abilitaLoggerService->log('info', __CLASS__, __FUNCTION__ , [
            'httpStatus' => $httpStatus,
            'error'      => $error,
            'payload'    => $response
        ]);

        if ($error) {
            return $this->response_other_payment_error();
        }

        if (isset($response->error_code) && $response->error_code > 0) {
            return $this->response_payment_error($response->error_message);
        }

        $this->add_salutation_to_order();
        $this->add_vat_number_to_order();

        $this->order->set_transaction_id($response->transaction_id);
        $this->order->add_order_note(wp_json_encode($response));
        $this->order->save();

        return [
            'result' => 'success',
            'redirect' => $response->action_data->url,
        ];
    }

    public function abilita_aiia_postback()
    {
        $this->handle_postback(__FUNCTION__);
        $this->handle_response();
    }

    public function abilita_aiia_success()
    {
        $orderIdGet = '';

        // Request comes over external API without NONCE
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (isset($_GET['order_id'])) {
            $orderIdGet = sanitize_text_field(
                wp_unslash($_GET['order_id'])
            );
        }

        $orderId     = str_replace(get_option('ABILITA_ORDERNUMBER_PREFIX'), '', $orderIdGet);
        $this->order = wc_get_order($orderId);

        $this->set_order_status($this->completeState);
        wc_reduce_stock_levels($orderId);
        $this->order->save();
        WC()->cart->empty_cart();
        wp_redirect(
            $this->get_return_url($this->order)
        );
    }

    public function abilita_aiia_error()
    {
        wc_add_notice(__('Die Zahlung wurde leider abgebrochen oder es liegt ein Fehler vor. Bitte prüfen Sie Ihre Daten und wiederholen den Prozess. Alternativ wählen Sie eine andere Zahlungsart.', 'abilita-payments-for-woocommerce'), 'error');
        wp_redirect(
            wc_get_checkout_url()
        );
    }
}

