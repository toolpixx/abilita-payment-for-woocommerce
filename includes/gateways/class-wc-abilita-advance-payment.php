<?php declare(strict_types=1);

namespace abilita\payment\gateways;

use abilita\payment\gateways\WC_Abilita_Base_Payment;
use abilita\payment\services\WC_Abilita_Client_Service;
use abilita\payment\services\WC_Abilita_Form_Service;

defined('ABSPATH') || exit;

class WC_Abilita_Advance_Payment extends WC_Abilita_Base_Payment
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
        $this->id                    = 'abilita-advance';
        $this->abilita_payment_name  = 'advance';
        $this->icon                  = plugin_dir_url(false).'abilita-payments-for-woocommerce/assets/images/'.$this->id.'.png';
        $this->has_fields             = true;
        $this->method_title          = __('abilita PAY (Coin4Vorkasse)', 'abilita-payments-for-woocommerce');
        $this->method_description    = __('Sie zahlen einfach vorab per klassischer Banküberweisung.', 'abilita-payments-for-woocommerce');
        $this->abilita_pay_box_link  = ABILITA_PAYMENT_LINK_HOMEPAGE_ADVANCED;
        $this->abilita_pay_box_title = __('Vorkasse', 'abilita-payments-for-woocommerce');
        $this->abilita_pay_box_text  = __('Sicher und zuverlässig – Zahlung im Voraus für Ihre maximale Sicherheit und schnellen Versand für Ihre Kunden.', 'abilita-payments-for-woocommerce');
        $this->abilita_pay_box_image = plugin_dir_url(false).'abilita-payments-for-woocommerce/assets/images/coin4_vorkasse.png';

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

        $this->bankAccountHolder = $this->get_option('bank_account_holder');
        $this->bankName          = $this->get_option('bank_name');
        $this->bankIban          = $this->get_option('bank_iban');
        $this->bankBic           = $this->get_option('bank_bic');

        add_action('woocommerce_thankyou'                    , [$this, 'abilita_thankyou_page']);
        add_action('woocommerce_email_before_order_table'    , [$this, 'abilita_email_instructions'], 10, 3);
        add_action('woocommerce_api_abilita-advance-postback', [$this, 'abilita_advance_postback']);

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
                'default'     => __('Vorkasse', 'abilita-payments-for-woocommerce'),
                'description' => __( 'Beschreibung der Zahlungsmethode, die Kunden bei der Kaufabwicklung sehen.', 'abilita-payments-for-woocommerce'),
                'desc_tip'    => true,
                'custom_attributes' => [
                    'required' => 'required'
                ]
            ],
            'description' => [
                'title'       => __('Beschreibung', 'abilita-payments-for-woocommerce'),
                'type'        => 'textarea',
                'default'     => $this->method_description,
                'description' => __( 'Beschreibung der Zahlungsmethode, die Kunden auf der Website sehen.', 'abilita-payments-for-woocommerce'),
                'desc_tip'    => true,
                'custom_attributes' => [
                    'required' => 'required'
                ]
            ],
            'instructions' => [
                'title'       => __( 'Anweisungen', 'abilita-payments-for-woocommerce'),
                'type'        => 'textarea',
                'default'     => __("Bitte überweisen Sie den Gesamtbetrag von {total} EUR auf das unten angegebene Konto.\n\nGeben Sie im Verwendungszweck Ihre Bestellnummer {ordernumber} an, damit wir Ihre Zahlung korrekt zuordnen können.\n\n{bankdata}\n\nIhre Bestellung wird erst nach Zahlungseingang bearbeitet. Je nach Bank kann der Zahlungseingang 1–3 Werktage dauern. Sobald wir Ihre Zahlung erhalten haben, werden wir Sie per E-Mail informieren und den Versand umgehend veranlassen.", 'abilita-payments-for-woocommerce'),
                'description' => __( 'Anweisungen, die der „Danke“-Seite und Bestellbestätigung hinzugefügt werden.', 'abilita-payments-for-woocommerce'),
                'desc_tip'    => true,
            ],
            'complete_state' => [
                'title' => __('Bestellstatus', 'abilita-payments-for-woocommerce'),
                'description' => __('Welchen Bestellstatus soll die Bestellung nach einer fehlerfreien Zahlung erhalten?', 'abilita-payments-for-woocommerce'),
                'type' => 'select',
                'options' => array_merge(['disabled' => __('Bitte wählen', 'abilita-payments-for-woocommerce')], wc_get_order_statuses()),
                'default' => 'wc-pending',
                'desc_tip' => true,
                'custom_attributes' => [
                    'required' => 'required'
                ]
            ],
            'countries' => [
                'title'       => 'Erlauben für folgende Länder',
                'type'        => 'multiselect',
                'css'         => 'height:120px',
                'description' => 'Wenn Sie weitere Länder hinterlegen möchten, müssen Sie diese erst unter <a href="/wp-admin/admin.php?page=wc-settings&tab=general">"allgemeinen Einstellungen" => "In bestimmte Länder verkaufen"</a> hinterlegen.',
                'default'     => ['DE', 'AT', 'CH'],
                'options'     => WC()->countries->get_allowed_countries(),
                'custom_attributes' => [
                    'required' => 'required'
                ]
            ],
            'bank_account_holder' => [
                'title'       => __('Name des Zahlungsempfängers', 'abilita-payments-for-woocommerce'),
                'type'        => 'text',
                'default'     => '',
                'custom_attributes' => [
                    'required' => 'required'
                ]

            ],
            'bank_name' => [
                'title'       => __('Name des Bankinstitut', 'abilita-payments-for-woocommerce'),
                'type'        => 'text',
                'default'     => '',
                'custom_attributes' => [
                    'required' => 'required'
                ]

            ],
            'bank_iban' => [
                'title'       => __('IBAN', 'abilita-payments-for-woocommerce'),
                'type'        => 'text',
                'default'     => '',
                'custom_attributes' => [
                    'required' => 'required'
                ]

            ],
            'bank_bic' => [
                'title'       => __('BIC', 'abilita-payments-for-woocommerce'),
                'type'        => 'text',
                'default'     => '',
                'custom_attributes' => [
                    'required' => 'required'
                ]
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

        if (!$formValidateTitle) {
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
        ] = $abilitaClient->advance_payment($this->get_advance_payload());

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
        $this->set_order_status($this->completeState);
        wc_reduce_stock_levels($this->orderId);
        $this->order->add_order_note(wp_json_encode($response));
        $this->order->save();
        WC()->cart->empty_cart();

        return [
            'result' => 'success',
            'redirect' => $this->get_return_url($this->order),
        ];
    }

    public function abilita_advance_postback()
    {
        $this->handle_postback(__FUNCTION__);
        $this->handle_response();
    }

    public function abilita_thankyou_page($orderId)
    {
        $order = wc_get_order($orderId);
        $this->execute_instructions($order);
    }

    public function abilita_email_instructions($order, $sent_to_admin, $plain_text = false)
    {
        $this->execute_instructions($order);
    }

    private function execute_instructions($order)
    {
        if ($this->id === $order->get_payment_method()) {
            $this->get_bank_details($order);
        }
    }

    public function get_bank_details($order)
    {
        $bankData = [
            'bank_account_holder' => [
                'label' => __('Kontoinhaber', 'abilita-payments-for-woocommerce'),
                'value' => $this->bankAccountHolder,
            ],
            'bank_name' => [
                'label' => __('Name der Bank', 'abilita-payments-for-woocommerce'),
                'value' => $this->bankName,
            ],
            'bank_iban' => [
                'label' => __('IBAN', 'abilita-payments-for-woocommerce'),
                'value' => $this->bankIban,
            ],
            'bank_bic' => [
                'label' => __('BIC/Swift', 'abilita-payments-for-woocommerce'),
                'value' => $this->bankBic,
            ]
        ];

        $bankDataHtml = '<address>';
        $bankDataHtml .= '<ul>' . PHP_EOL;

        foreach ($bankData as $field_key => $field) {
            if (!empty($field['value'])) {
                $bankDataHtml .= '<li class="'.esc_attr( $field_key ).'">'.wp_kses_post( $field['label'] ).': <strong>'.wp_kses_post( wptexturize($field['value'])).'</strong></li>'.PHP_EOL;
            }
        }

        $bankDataHtml .= '</ul>';
        $bankDataHtml .= '</address>';

        $instructions = $this->get_option('instructions');
        $instructions = str_replace('{total}', $order->get_formatted_order_total(), $instructions);
        $instructions = str_replace('{date}', $order->get_date_created()->date('d.m.Y'), $instructions);
        $instructions = str_replace('{ordernumber}', $this->ordernumberPrefix.$order->get_order_number(), $instructions);
        $instructions = str_replace('{company}', $this->bankAccountHolder, $instructions);
        $instructions = str_replace('{bankdata}', $bankDataHtml, $instructions);

        $bodyHtml = '<section class="woocommerce-customer-details">';
        $bodyHtml .= '<section class="woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses">';
        $bodyHtml .= '<div class="woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1"><h2 class="woocommerce-column__title">' . esc_html__( 'Unsere Bankverbindung', 'abilita-payments-for-woocommerce') . '</h2></section>';
        $bodyHtml .= wp_kses_post(
            wpautop(
                wptexturize(
                    wp_kses_post($instructions)
                )
            )
        );
        $bodyHtml .= '</div>';
        $bodyHtml .= '</section>';
        $bodyHtml .= '</section>';
        $bodyHtml .= '<br><br>';
        echo wp_kses_post($bodyHtml);
    }
}

