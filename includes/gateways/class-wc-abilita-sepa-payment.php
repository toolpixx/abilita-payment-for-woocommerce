<?php declare(strict_types=1);

namespace abilita\payment\gateways;

use abilita\payment\gateways\WC_Abilita_Base_Payment;
use abilita\payment\services\WC_Abilita_Form_Service;
use abilita\payment\services\WC_Abilita_Client_Service;

defined('ABSPATH') || exit;

class WC_Abilita_Sepa_Payment extends WC_Abilita_Base_Payment
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

    public $sepaBankAccountHolder;
    public $sepaBankName;
    public $sepaBankIban;
    public $sepaBankBic;
    public $sepaStreet;
    public $sepaZipcode;
    public $sepaCity;
    public $sepaCreditorId;

    public function __construct()
    {
        $this->abilitaFormService    = new WC_Abilita_Form_Service();
        $this->id                    = 'abilita-sepa';
        $this->abilita_payment_name  = 'dd';
        $this->icon                  = plugin_dir_url(false).'abilita-payments-for-woocommerce/assets/images/01-2025/'.$this->id.'.png';
        $this->has_fields             = true;
        $this->method_title          = __('abilita PAY (Coin4Lastschrift)', 'abilita-payments-for-woocommerce');
        $this->method_description    = __('Bezahlen Sie Ihren Einkauf bequem und sicher per Bankeinzug. Für diese Zahlungsart benötigen wir Ihr Geburtsdatum.', 'abilita-payments-for-woocommerce');
        $this->abilita_pay_box_link  = ABILITA_PAYMENT_LINK_HOMEPAGE_SEPA;
        $this->abilita_pay_box_title = __('SEPA-Lastschrift', 'abilita-payments-for-woocommerce');
        $this->abilita_pay_box_text  = __('Ermöglichen Sie direkte Abbuchungen vom Bankkonto Ihrer Kunden mit unserer <b>100% Zahlungsgarantie!</b>', 'abilita-payments-for-woocommerce');
        $this->abilita_pay_box_image = plugin_dir_url(false).'abilita-payments-for-woocommerce/assets/images/01-2025/coin4_LASTSCHRIFT_Public.png';

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

        $this->sepaBankAccountHolder       = $this->get_option('sepa_bank_account_holder');
        $this->sepaBankName                = $this->get_option('sepa_bank_name');
        $this->sepaBankIban                = $this->get_option('sepa_bank_iban');
        $this->sepaBankBic                 = $this->get_option('sepa_bank_bic');
        $this->sepaStreet                  = $this->get_option('sepa_street');
        $this->sepaZipcode                 = $this->get_option('sepa_zipcode');
        $this->sepaCity                    = $this->get_option('sepa_city');
        $this->sepaCreditorId              = $this->get_option('sepa_creditor_id');

        add_action('woocommerce_thankyou'                 , [$this, 'abilita_thankyou_page']);
        add_action('woocommerce_email_before_order_table' , [$this, 'abilita_email_instructions'], 10, 3);
        add_action('woocommerce_api_abilita-sepa-postback', [$this, 'abilita_sepa_postback']);

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
                'default'     => __('SEPA-Lastschrift', 'abilita-payments-for-woocommerce'),
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
                'default'     => __("Im Rahmen Ihres Auftrags {ordernumber} am {date} erteilen Sie {company} ein SEPA-Lastschriftmandat zum Einzug der Forderung mit folgenden Angaben:\n\n{bankdata}", 'abilita-payments-for-woocommerce'),
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
                'type'        => 'number',
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
                'description' => __('Geben Sie hier an wie viele Bestellungen ein eingeloggte Kunden bereits erfolgreich abgeschlossen haben muss um diese Zahlart nutzen zu dürfen.', 'abilita-payments-for-woocommerce'),
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
                'title'       => __('Müssen Kunden aktiv durch Klicken einer Checkbox im Checkout-Bereich dieser Zahlungsart das SEPA-Mandant erteilen?', 'abilita-payments-for-woocommerce'),
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
            ],
            'sepa_bank_account_holder' => [
                'title'       => __('Name des Zahlungsempfängers', 'abilita-payments-for-woocommerce'),
                'type'        => 'text',
                'default'     => '',
                'custom_attributes' => [
                    'required' => 'required'
                ]

            ],
            'sepa_bank_name' => [
                'title'       => __('Name des Bankinstitut', 'abilita-payments-for-woocommerce'),
                'type'        => 'text',
                'default'     => '',
                'custom_attributes' => [
                    'required' => 'required'
                ]

            ],
            'sepa_bank_iban' => [
                'title'       => __('IBAN', 'abilita-payments-for-woocommerce'),
                'type'        => 'text',
                'default'     => '',
                'custom_attributes' => [
                    'required' => 'required'
                ]

            ],
            'sepa_bank_bic' => [
                'title'       => __('BIC', 'abilita-payments-for-woocommerce'),
                'type'        => 'text',
                'default'     => '',
                'custom_attributes' => [
                    'required' => 'required'
                ]
            ],
            'sepa_street' => [
                'title'       => __('Anschrift des Zahlungsempfängers (Straße)', 'abilita-payments-for-woocommerce'),
                'type'        => 'text',
                'default'     => '',
                'custom_attributes' => [
                    'required' => 'required'
                ]
            ],
            'sepa_zipcode' => [
                'title'       => __('Anschrift des Zahlungsempfängers (Postleitzahl)', 'abilita-payments-for-woocommerce'),
                'type'        => 'text',
                'default'     => '',
                'custom_attributes' => [
                    'required' => 'required'
                ]
            ],
            'sepa_city' => [
                'title'       => __('Anschrift des Zahlungsempfängers (Stadt)', 'abilita-payments-for-woocommerce'),
                'type'        => 'text',
                'default'     => '',
                'custom_attributes' => [
                    'required' => 'required'
                ]
            ],
            'sepa_creditor_id' => [
                'title'       => __('Gläubiger-Identifikationsnummer', 'abilita-payments-for-woocommerce'),
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

        ?>
        <fieldset>
            <p class="form-row form-row-wide">
                <label for="sepa_account_holder" class="abilitaSepaAccountHolderLabel"><?php echo esc_html(__('Kontoinhaber', 'abilita-payments-for-woocommerce')); ?></label>
                <input type="text" id="sepa_account_holder" name="sepa_account_holder" class="input-text" required="required"/>
            </p>
            <p class="form-row form-row-wide">
                <label for="sepa_iban" class="abilitaSepaIbanLabel"><?php echo esc_html(__('IBAN', 'abilita-payments-for-woocommerce')); ?></label>
                <input type="text" id="sepa_iban" name="sepa_iban" class="input-text"  required="required"/>
            </p>
            <p class="form-row form-row-wide">
                <label for="sepa_bic" class="abilitaSepaBicSwiftLabel"><?php echo esc_html(__('BIC', 'abilita-payments-for-woocommerce')); ?></label>
                <input type="text" id="sepa_bic" name="sepa_bic" class="input-text" required="required"/>
            </p>
        </fieldset>
        <?php

		// $_POST comes from progress checkout with woocommerce-process-checkout-nonce
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (
            isset($_POST['billing_company']) && empty($_POST['billing_company'])       &&
            isset($_POST['billing_vat_id'])  && empty($_POST['billing_vat_id'])
        ) {
            echo wp_kses($this->abilitaFormService->get_payment_birthday_fields($this->id), ABILITA_PAYMENT_ALLOWED_HTML);
        }

        if ($this->useLegalLegitimacy == 'yes') { ?>
            <br>
            <p class="abilitaSepaBankInformationen">
                <?php echo '<b>'.esc_html(__('Name des Zahlungsempfängers:', 'abilita-payments-for-woocommerce')).'</b>'; ?><br>
                <?php echo esc_html($this->sepaBankAccountHolder); ?><br><br>
                <?php echo '<b>'.esc_html(__('Anschrift des Zahlungsempfängers:', 'abilita-payments-for-woocommerce')).'</b>'; ?><br>
                <?php echo esc_html($this->sepaStreet); ?><br>
                <?php echo esc_html($this->sepaZipcode); ?> <?php echo esc_html($this->sepaCity); ?><br><br>
                <?php echo '<b>'.esc_html(__('Kreditinstitut/Bank:', 'abilita-payments-for-woocommerce')).'</b>'; ?> <?php echo esc_html($this->sepaBankName); ?><br>
                <?php echo '<b>'.esc_html(__('IBAN:', 'abilita-payments-for-woocommerce')).'</b>'; ?> <?php echo esc_html($this->sepaBankIban); ?><br>
                <?php echo '<b>'.esc_html(__('BIC:', 'abilita-payments-for-woocommerce')).'</b>'; ?> <?php echo esc_html($this->sepaBankBic); ?><br><br>
                <?php echo '<b>'.esc_html(__('Gläubiger-Identifikationsnummer:', 'abilita-payments-for-woocommerce')).'</b>'; ?> <?php echo esc_html($this->sepaCreditorId); ?><br>
                <?php echo '<b>'.esc_html(__('Zahlungsart:', 'abilita-payments-for-woocommerce')).'</b>'; ?> <?php echo esc_html(__('Einmalige Einzugsermächtigung', 'abilita-payments-for-woocommerce')); ?>
            </p>
            <br>
            <p class="abilitaSepaErmaechtigung">
                <?php
					echo sprintf(
						/* translators: %1$s: Owner of sepa-account */
						/* translators: %2$s: Owner of sepa-account */
						esc_html(__('Ich ermächtige %1$s Zahlungen von meinem Konto mittels Lastschrift einzuziehen. Zugleich weise ich mein Kreditinstitut an, die von %2$s auf mein Konto gezogenen Lastschriften einzulösen.', 'abilita-payments-for-woocommerce')), 																		esc_html(get_option('ABILITA_SEPA_ACCOUNT_HOLDER')),
						esc_html(get_option('ABILITA_SEPA_ACCOUNT_HOLDER'))
					);
                ?>
            </p>
            <br>
            <p class="abilitaSepaNotice">
                <?php echo wp_kses(__('<b>Hinweis:</b> Innerhalb von acht Wochen ab Belastungsdatum können Sie die volle Erstattung des belasteten Betrages verlangen. Bitte beachten Sie hierfür die jeweils geltenden Rückerstattungsbedingungen Ihrer Bank.', 'abilita-payments-for-woocommerce'), ABILITA_PAYMENT_ALLOWED_HTML); ?>
            </p>
            <br>
            <label for="use_legal_legitimacy" class="checkbox">
                <input type="checkbox" id="use_legal_legitimacy" value="accepted" name="use_legal_legitimacy" class="input-checkbox" required="required"/>
                <span class="abilitaLegalLegitimacyTitle">
                    <?php echo esc_html(__('Hiermit bestätige ich, dass ich berechtigt bin, das Mandat für die oben angezeigte(n) SEPA-Lastschrifttransaktion(en) zu erteilen. Ich erteile hiermit ein SEPA-Lastschriftmandat.', 'abilita-payments-for-woocommerce')); ?>
                </span>
            </label>
        <?php }

    }

    public function validate_fields()
    {
        $formValidateTitle             = $this->abilitaFormService->validate_Title();
        $formValidatePhone             = $this->abilitaFormService->validate_phone($this->allowPhonenumber);
        $formValidateSepaAccountHolder = $this->abilitaFormService->validate_sepa_account_holder();
        $formValidateSepaIban          = $this->abilitaFormService->validate_iban();
        $formValidateSepaBic           = $this->abilitaFormService->validate_bic();
		// $_POST will validate in $this->abilitaFormService->validate_different_delivery_address
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $formValidateAddress           = $this->abilitaFormService->validate_different_delivery_address($this->allowDifferentAddress, $_POST);
		// $_POST will validate in $this->abilitaFormService->validate_different_delivery_address
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $formValidateBirthday          = $this->abilitaFormService->validate_birthday($_POST);
		// $_POST will validate in $this->abilitaFormService->validate_different_delivery_address
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $formValidateVatNumber         = $this->abilitaFormService->validate_vat_number($_POST);
        $formValidateLegalLegitimacy   = $this->abilitaFormService->validate_sepa_legal_legitimacy($this->useLegalLegitimacy);

        if ($formValidateSepaIban && $formValidateSepaBic) {
            $formValidateSepaIbanBicCountry = $this->abilitaFormService->validate_iban_bic_country();
            if (!$formValidateSepaIbanBicCountry) {
                return;
            }
        }

        if (!$formValidateTitle              ||
            !$formValidatePhone              ||
            !$formValidateAddress            ||
            !$formValidateSepaAccountHolder  ||
            !$formValidateSepaIban           ||
            !$formValidateSepaBic            ||
            !$formValidateBirthday           ||
            !$formValidateVatNumber          ||
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
            wp_send_json_error(['message' => esc_html(__('Fehler bei der Erstellung der Bestellung. Bitte versuchen Sie es erneut.', 'abilita-payments-for-woocommerce'))]);
            return;
        }

        $abilitaClient = new WC_Abilita_Client_Service();
        [
            $httpStatus,
            $error,
            $response
        ] = $abilitaClient->sepa_payment($this->get_sepa_payload());

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
        $this->add_sepa_reference_to_order($response->mandate_id);
        $this->order->save();
        WC()->cart->empty_cart();

        return [
            'result' => 'success',
            'redirect' => $this->get_return_url($this->order),
        ];
    }

    public function abilita_sepa_postback()
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
            $this->get_sepa_details($order);
        }
    }

    public function get_sepa_details($order)
    {
        $bankData = [
            'bank_account_holder' => [
                'label' => __('Zahlungsempfänger', 'abilita-payments-for-woocommerce'),
                'value' => $this->sepaBankAccountHolder,
            ],
            'street' => [
                'label' => __('Straße', 'abilita-payments-for-woocommerce'),
                'value' => $this->sepaStreet
            ],
            'bank_postcode_city' => [
                'label' => __('PLZ, Ort', 'abilita-payments-for-woocommerce'),
                'value' => $this->sepaZipcode.' '.$this->sepaCity
            ],
            'bank_name' => [
                'label' => __('Kreditinstitut/Bank', 'abilita-payments-for-woocommerce'),
                'value' => $this->sepaBankName
            ],
            'bank_iban' => [
                'label' => __('IBAN', 'abilita-payments-for-woocommerce'),
                'value' => $this->sepaBankIban
            ],
            'bank_bic' => [
                'label' => __('BIC', 'abilita-payments-for-woocommerce'),
                'value' => $this->sepaBankBic
            ],
            'bank_mandat' => [
                'label' => __('Gläubiger-ID', 'abilita-payments-for-woocommerce'),
                'value' => $this->sepaCreditorId
            ],
            'payment' => [
                'label' => __('Zahlungsart', 'abilita-payments-for-woocommerce'),
                'value' => __('Einmalige Einzugsermächtigung', 'abilita-payments-for-woocommerce')
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
        $instructions = str_replace('{company}', get_option('ABILITA_SEPA_ACCOUNT_HOLDER'), $instructions);
        $instructions = str_replace('{bankdata}', $bankDataHtml, $instructions);

        $bodyHtml = '<section class="woocommerce-customer-details">';
        $bodyHtml .= '<section class="woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses">';
        $bodyHtml .= '<div class="woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1"><h2 class="woocommerce-column__title">' . esc_html__( 'Hinweis zum Sepa-Mandat', 'abilita-payments-for-woocommerce') . '</h2></section>';
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
