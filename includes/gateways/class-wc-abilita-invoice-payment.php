<?php declare(strict_types=1);

namespace abilita\payment\gateways;

use abilita\payment\gateways\WC_Abilita_Base_Payment;
use abilita\payment\services\WC_Abilita_Form_Service;
use abilita\payment\services\WC_Abilita_Client_Service;

defined('ABSPATH') || exit;

class WC_Abilita_Invoice_Payment extends WC_Abilita_Base_Payment
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
    public $enabled;
    public $completeState;
    public $allowB2B;
    public $minAmount;
    public $maxAmount;
    public $countries;
    public $blockedCount;
    public $loggedInCustomer;
    public $loggedInCustomerOrdersCount;
    public $useLegalLegitimacy;
    public $allowDifferentAddress;
    public $allowPhonenumber;

    public function __construct()
    {
        $this->abilitaFormService    = new WC_Abilita_Form_Service();
        $this->id                    = 'abilita-invoice';
        $this->abilita_payment_name  = 'kar';
        $this->icon                  = plugin_dir_url(false).'abilita-payments-for-woocommerce/assets/images/'.$this->id.'.png';
        $this->has_fields             = true;
        $this->method_title          = __('abilita PAY (Coin4Rechnung)', 'abilita-payments-for-woocommerce');
        $this->method_description    = __('Mit dem Kauf auf Rechnung genießen Sie maximalen Komfort beim Online-Shoppen: Erst die Ware, dann die Zahlung! Für diese Zahlungsart benötigen wir Ihr Geburtsdatum.', 'abilita-payments-for-woocommerce');
        $this->abilita_pay_box_link  = ABILITA_PAYMENT_LINK_HOMEPAGE_INVOICE;
        $this->abilita_pay_box_title = __('Kauf auf Rechnung', 'abilita-payments-for-woocommerce');
        $this->abilita_pay_box_text  = __('Bieten Sie Ihren Kunden die Möglichkeit, bequem per Rechnung zu zahlen mit unserer <b>100% Zahlungsgarantie!</b>', 'abilita-payments-for-woocommerce');
        $this->abilita_pay_box_image = plugin_dir_url(false).'abilita-payments-for-woocommerce/assets/images/coin4_rechnung.png';

        $this->init_form_fields();
        $this->init_settings();

        $this->title                       = $this->get_option('title');
        $this->description                 = $this->get_option('description');
        $this->enabled                     = $this->get_option('enabled');
        $this->completeState               = $this->get_option('complete_state');
        $this->allowB2B                    = $this->get_option('allow_b2b');
        $this->minAmount                   = $this->get_option('min_amount');
        $this->maxAmount                   = $this->get_option('max_amount');
        $this->countries                   = $this->get_payment_countries();
        $this->blockedCount                = $this->get_option('blocked_count');
        $this->loggedInCustomer            = $this->get_option('logged_in_customer');
        $this->loggedInCustomerOrdersCount = (int) $this->get_option('logged_in_customer_orders_count');
        $this->useLegalLegitimacy          = $this->get_option('use_legal_legitimacy');
        $this->allowDifferentAddress       = $this->get_option('allow_different_address');
        $this->allowPhonenumber            = $this->get_option('allow_phonenumber');

        add_action('woocommerce_thankyou'                    , [$this, 'abilita_thankyou_page']);
        add_action('woocommerce_email_before_order_table'    , [$this, 'abilita_email_instructions'], 10, 3);
        add_action('woocommerce_api_abilita-invoice-postback', [$this, 'abilita_invoice_postback']);

        parent::__construct();
    }

    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled' => [
                'title'   => __('Zahlungsart', 'abilita-payments-for-woocommerce'),
                'type'    => 'checkbox',
                'label'   => __('Ja / Nein', 'abilita-payments-for-woocommerce'),
                'default' => 'no'
            ],
            'title' => [
                'title'       => __('Titel', 'abilita-payments-for-woocommerce'),
                'type'        => 'text',
                'default'     => __('Kauf auf Rechnung', 'abilita-payments-for-woocommerce'),
                'description' => __('Beschreibung der Zahlungsmethode, die Kunden bei der Kaufabwicklung sehen.', 'abilita-payments-for-woocommerce'),
                'desc_tip'    => true,
            ],
            'description' => [
                'title'       => __('Beschreibung', 'abilita-payments-for-woocommerce'),
                'type'        => 'textarea',
                'default'     => $this->method_description,
                'description' => __('Beschreibung der Zahlungsmethode, die Kunden auf der Website sehen.', 'abilita-payments-for-woocommerce'),
                'desc_tip'    => true,
            ],
            'instructions'    => [
                'title'       => __('Anweisungen', 'abilita-payments-for-woocommerce'),
                'type'        => 'textarea',
                'default'     => __('Ihre Bestellung war erfolgreich und wird in Kürze bearbeitet. Da Sie den "Kauf auf Rechnung" gewählt haben, erhalten Sie die Rechnung und Ihre Tracking-ID direkt per E-Mail, sobald Ihre Bestellung versandt wurde.', 'abilita-payments-for-woocommerce'),
                'description' => __('Anweisungen, die der „Danke“-Seite und Bestellbestätigung hinzugefügt werden.', 'abilita-payments-for-woocommerce'),
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
                'title'       => __('Erlauben für folgende Länder (D-A-CH Region)', 'abilita-payments-for-woocommerce'),
                'type'        => 'multiselect',
                'description' => __('Bei dieser Zahlungsart stehen Ihnen nur Länder der D-A-CH Region zur Verfügung.', 'abilita-payments-for-woocommerce'),
                'default'     => ['DE', 'AT', 'CH'],
                'options'     => [
                    'DE' => __('Deutschland', 'abilita-payments-for-woocommerce'),
                    'AT' => __('Österreich', 'abilita-payments-for-woocommerce'),
                    'CH' => __('Schweiz', 'abilita-payments-for-woocommerce'),
                ]
            ],
            'min_amount' => [
                'title'       => __('Minimale Warenkorbsumme', 'abilita-payments-for-woocommerce'),
                'type'        => 'text',
                'description' => __('Bitte passen Sie die minimale Warenkorbsumme mit der aus dem abilita PAY Vertrag an.', 'abilita-payments-for-woocommerce'),
                'default'     => 10
            ],
            'max_amount' => [
                'title'       => __('Maximale Warenkorbsumme', 'abilita-payments-for-woocommerce'),
                'type'        => 'number',
                'description' => __('Bitte passen Sie die maximale Warenkorbsumme mit der aus dem abilita PAY Vertrag an.', 'abilita-payments-for-woocommerce'),
                'default'     => 500
            ],
            'logged_in_customer' => [
                'title'       => __('Nur eingeloggte Kunden', 'abilita-payments-for-woocommerce'),
                'type'        => 'checkbox',
                'description' => __('Wenn Sie möchten, dass nur eingeloggte Kunden diese Zahlungsarten nutzen dürfen, aktivieren Sie diese Option.', 'abilita-payments-for-woocommerce'),
                'default'     => 'false'
            ],
            'logged_in_customer_orders_count' => [
                'title'       => __('Wie viele erfolgreiche Bestellungen muss ein eingeloggter Kunden haben?', 'abilita-payments-for-woocommerce'),
                'type'        => 'text',
                'description' => __('Geben Sie hier an, wie viele Bestellungen eingeloggte Kunden bereits erfolgreich abgeschlossen haben müssen, um diese Zahlart nutzen zu dürfen.', 'abilita-payments-for-woocommerce'),
                'default'     => 0
            ],
            'allow_b2b' => [
                'title'       => __('B2B Abfrage aktivieren?', 'abilita-payments-for-woocommerce'),
                'type'        => 'checkbox',
                'description' => __('Möchten Sie B2B-Abfragen zu lassen? Sobald ein Firmenname oder Umsatzsteuer-Identifikationsnummer hinterlegt wird, wird die Zahlungsart als B2B-Anfrage gesendet.', 'abilita-payments-for-woocommerce'),
                'default'     => 'yes'
            ],
            'allow_different_address' => [
                'title'       => __('Abweichende Lieferadresse erlauben?', 'abilita-payments-for-woocommerce'),
                'type'        => 'checkbox',
                'description' => '<span style="background: red;color: white;padding: 10px;">'.__('Nur in Absprache mit abilita <b>aktivieren</b>. Die 100% Zahlungsgarantie erlischt bei abweichender Lieferadresse.', 'abilita-payments-for-woocommerce').'</span>',
                'default'     => 'false'
            ],
            'allow_phonenumber' => [
                'title'       => __('Telefonnummer als Pflichtfeld behandeln?', 'abilita-payments-for-woocommerce'),
                'type'        => 'checkbox',
                'description' => '<span style="background: red;color: white;padding: 10px;">'.__('Nur in Absprache mit abilita <b>deaktivieren</b>. Die 100% Zahlungsgarantie erlischt bei fehlender Telefonnummer.', 'abilita-payments-for-woocommerce').'</span>',
                'default'     => 'yes'
            ],
            'use_legal_legitimacy' => [
                'title'       => __('Müssen Kunden aktiv durch Klicken einer Checkbox im Checkout-Bereich dieser Zahlungsart die Einwilligung zur Bonitätsprüfung geben?', 'abilita-payments-for-woocommerce'),
                'type'        => 'checkbox',
                'default'     => 'yes'
            ],
            'blocked_count' => [
                'title'       => __('Zahlungsart nach x Fehlversuchen sperren?', 'abilita-payments-for-woocommerce'),
                'type'        => 'select',
                'description' => __('Legen Sie hier fest, nach wie vielen fehlgeschlagenen Zahlungsversuchen ein Kunde für die gewählte Zahlungsart gesperrt werden soll.', 'abilita-payments-for-woocommerce'),
                'css'         => 'width:50px',
                'default'     => 3,
                'options'     => [
                    '1' => 1,
                    '2' => 2,
                    '3' => 3
                ]
            ]
        ];
    }

    public function payment_fields()
    {
        if ($this->description) {
            echo esc_html(
				$this->description
			);
        }

        $this->abilitaFormService->getPostDataRequest('post_data');

		// $_POST will validate in $this->abilitaFormService->validate_different_delivery_address
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (
            (!isset($_POST['billing_company']) || empty($_POST['billing_company'])) &&
            (!isset($_POST['billing_vat_id']) || empty($_POST['billing_vat_id']))
        ) {
            echo wp_kses($this->abilitaFormService->get_payment_birthday_fields($this->id), ABILITA_PAYMENT_ALLOWED_HTML);
        }

        if ($this->useLegalLegitimacy == 'yes') { ?>

            <?php
                $useLegalLegitimacy = esc_html(WC()->session->get('use_legal_legitimacy'));
            ?>

            <hr style="margin: 10px;">
            <label for="use_legal_legitimacy" class="checkbox">
                <input type="checkbox" id="use_legal_legitimacy" value="accepted" name="use_legal_legitimacy" class="input-checkbox" required="required" <?php if ($useLegalLegitimacy) { ?>checked="checked"<?php } ?> />
                <span class="abilitaLegalLegitimacyTitle">Ich stimme hiermit der Verarbeitung meiner Daten zum Zwecke einer Risikoprüfung zu.</span>
                <small class="woocommerce-terms-and-conditions-checkbox-text abilitaLegalLegitimacy">
                    <?php echo esc_html(__('Die von Ihnen im Bestellprozess angegebenen Daten werden durch den Zahlungsdienstleister im Rahmen einer Bonitätsprüfung zur Ermittlung des Verkäuferrisikos verarbeitet. Falls Sie dies nicht möchten, wählen Sie bitte eine andere Zahlungsart.', 'abilita-payments-for-woocommerce')); ?>
                </small>
            </label>
        <?php }
    }

    public function validate_fields()
    {
        $formValidateTitle           = $this->abilitaFormService->validate_title();
        $formValidatePhone           = $this->abilitaFormService->validate_phone($this->allowPhonenumber);
		// $_POST will validate in $this->abilitaFormService->validate_different_delivery_address
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $formValidateAddress         = $this->abilitaFormService->validate_different_delivery_address($this->allowDifferentAddress, $_POST);
		// $_POST will validate in $this->abilitaFormService->validate_different_delivery_address
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $formValidateBirthday        = $this->abilitaFormService->validate_birthday($_POST);
		// $_POST will validate in $this->abilitaFormService->validate_different_delivery_address
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $formValidateVatNumber       = $this->abilitaFormService->validate_vat_number($_POST);
        $formValidateLegalLegitimacy = $this->abilitaFormService->validate_invoice_legal_legitimacy($this->useLegalLegitimacy);

        if (!$formValidateTitle           ||
            !$formValidatePhone           ||
            !$formValidateAddress         ||
            !$formValidateBirthday        ||
            !$formValidateVatNumber       ||
            !$formValidateLegalLegitimacy
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
        ] = $abilitaClient->invoice_payment($this->get_invoice_payload());

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

        $this->add_birthday_to_order();
        $this->add_salutation_to_order();
        $this->add_vat_number_to_order();

        $this->order->set_transaction_id($response->transaction_id);
        $this->set_order_status($this->completeState);
        wc_reduce_stock_levels($this->orderId);
        $this->order->add_order_note(wp_json_encode($response));
        $this->order->save();
        WC()->cart->empty_cart();
        WC()->session->set('use_legal_legitimacy', null);

        return [
            'result' => 'success',
            'redirect' => $this->get_return_url($this->order),
        ];
    }

    public function abilita_invoice_postback()
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
        if ($this->id === $order->get_payment_method() && $order->has_status('on-hold')) {

            $instructions = $this->get_option('instructions');
            $instructions = str_replace('{total}', $order->get_formatted_order_total(), $instructions);
            $instructions = str_replace('{date}', $order->get_date_created()->date('d.m.Y'), $instructions);
            $instructions = str_replace('{ordernumber}', $this->ordernumberPrefix.$order->get_order_number(), $instructions);
            $instructions = str_replace('{company}', get_option('ABILITA_SEPA_ACCOUNT_HOLDER'), $instructions);

            $bodyHtml = '<section class="woocommerce-customer-details">';
            $bodyHtml .= '<section class="woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses">';
            $bodyHtml .= '<div class="woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1"><h2 class="woocommerce-column__title">' . esc_html__( 'Hinweis zum Kauf auf Rechnung', 'abilita-payments-for-woocommerce') . '</h2></section>';
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
}

