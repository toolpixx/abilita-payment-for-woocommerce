<?php declare(strict_types=1);

namespace abilita\payment\gateways;

use abilita\payment\helper\WC_Abilita_Helper;
use abilita\payment\services\WC_Abilita_Form_Service;
use abilita\payment\services\WC_Abilita_Logger_Service;
use WC_Payment_Gateway;
use WC_HTTPS;

defined('ABSPATH') || exit;

class WC_Abilita_Base_Payment extends WC_Payment_Gateway
{
    const PAYMENT_STATUS_FAILED  = 'wc-failed';
    const PAYMENT_STATUS_ON_HOLD = 'wc-on-hold';

    public $abilitaHelper;
    public $abilitaFormService;
    public $abilitaLoggerService;
    public $id;
    public $abilita_payment_name;
    public $ordernumberPrefix;
    public $customerId;
    public $payload;
    public $order;
    public $orderId;
    public $allowB2B;
    public $enabled;
    public $completeState;
    public $minAmount;
    public $maxAmount;
    public $countries;
    public $blockedCount;
    public $loggedInCustomer;
    public $loggedInCustomerOrdersCount;
    public $allowDifferentAddress;
    public $allowPhonenumber;

    public $bankAccountHolder;
    public $bankName;
    public $bankIban;
    public $bankBic;

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
        $this->abilitaHelper         = new WC_Abilita_Helper();
        $this->abilitaFormService    = new WC_Abilita_Form_Service();
        $this->abilitaLoggerService  = new WC_Abilita_Logger_Service();
        $this->ordernumberPrefix      = get_option('ABILITA_ORDERNUMBER_PREFIX', '');
        $this->customerId            = get_current_user_id();

        add_action('woocommerce_update_options_payment_gateways_'.$this->id, [$this, 'process_admin_options']);
        add_action('wp_enqueue_scripts'                                    , [$this, 'abilita_wp_enqueue_scripts'], 20);
        add_action('woocommerce_checkout_update_user_meta'                 , [$this, 'abilita_checkout_update_user_meta']);
    }

    public function admin_options() {
        ?>
        <h3><?php echo esc_html($this->method_title); ?></h3>
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <table class="form-table">
                        <?php $this->generate_settings_html();?>
                    </table>
                </div>
                <div id="postbox-container-1" class="postbox-container">
                    <div id="side-sortables" class="meta-box-sortables ui-sortable">
                        <div class="postbox abilitaBox">
                            <a href="<?php echo esc_html($this->abilita_pay_box_link); ?>" class="abilitaBoxLink" target="_blank">
                                <div style="text-align:center;padding:12px;">
                                    <img src="<?php echo esc_html($this->abilita_pay_box_image); ?>" style="width:100%">
                                </div>
                                <div class="inside">
                                    <div class="support-widget" style="text-align:center">
                                        <b><?php echo wp_kses($this->abilita_pay_box_title, ABILITA_PAYMENT_ALLOWED_HTML); ?></b><br>
                                        <?php echo wp_kses($this->abilita_pay_box_text, ABILITA_PAYMENT_ALLOWED_HTML); ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
        <style>
            .abilitaBox {
                border:0px;
                background-color: #fff;
                padding: 10px 10px 20px;
                border-radius: 30px 30px 30px 30px;
                box-shadow: 0 0 10px 0 rgba(0, 0, 0, .5);
            }

            .abilitaBoxLink {
                text-decoration:none;
            }

            .abilitaBoxLink:hover {
                text-decoration:underline;
            }
        </style>
        <?php
    }

    public function abilita_checkout_update_user_meta($customer_id)
    {
		// $_POST comes from progress checkout with woocommerce-process-checkout-nonce
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (isset($_POST['abilitaBirthday'])) {
			// $_POST comes from progress checkout with woocommerce-process-checkout-nonce
        	// phpcs:ignore WordPress.Security.NonceVerification.Missing
            $birthday = $this->abilitaFormService->get_payment_birthday($_POST);
            wp_update_user([
                'ID' => $customer_id,
                'billing_birthday' => sanitize_text_field($birthday)
            ]);
        }

        $billingSalutationKey = $this->abilitaFormService->map_salutation();
		// $_POST comes from progress checkout with woocommerce-process-checkout-nonce
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (isset($_POST[$billingSalutationKey])) {
            wp_update_user([
                'ID' => $customer_id,
                $billingSalutationKey => sanitize_text_field(wp_unslash($_POST[$billingSalutationKey]))
            ]);
        }
    }

    public function abilita_wp_enqueue_scripts()
    {
        if (is_checkout()) {
            wp_register_script('abilita-script', plugin_dir_url(__FILE__).'../../assets/js/abilita.js', ['jquery'], WC_ABILITA_PAYMENT_VERSION, true);
            wp_enqueue_script('abilita-script');
            echo '<style>'.esc_html(get_option('ABILITA_CSS_BIRTHDAY')).'</style>';
        }
    }

    public function is_available() {

        if (is_checkout()) {
            if (!$this->check_config_values_api()) {
                return false;
            }

            if (!$this->check_config_values_advanced()) {
                return false;
            }

			if (!$this->check_config_values_sepa_mandate()) {
				return false;
            }

            $cartTotal = (float) parent::get_order_total();
			
			// $_POST comes from progress checkout with woocommerce-process-checkout-nonce
        	// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if (isset($_POST['country'])) {
				$billingCountry = strtoupper(
                    sanitize_text_field(
                            wp_unslash($_POST['country'])
                    )
                );
			} else {
				$billingCountry = strtoupper(WC()->customer->get_billing_country());	
			}
            
            $completedCustomerOrders = $this->get_completed_customer_orders();

            if ($this->enabled != 'yes') {
                return false;
            }
            //else if ($this->allowB2B == 'yes' && $billingCompany === '' && in_array($this->id, ['abilita-invoice'])) {
            //    return false;
            //}
            else if (
                ($cartTotal < $this->minAmount || $cartTotal >= $this->maxAmount) &&
                !$this->is_advance_payment()
            ) {
                return false;
            } else if (!in_array($billingCountry, $this->countries) && !empty($billingCountry)) {
                return false;
            } else if (
                $this->loggedInCustomer == 'yes' &&
                $completedCustomerOrders < $this->loggedInCustomerOrdersCount &&
                !$this->is_advance_payment() &&
                is_user_logged_in()
            ) {
                return false;
            } else if (
                $this->loggedInCustomer == 'yes' &&
                !$this->is_advance_payment() &&
                !is_user_logged_in()
            ) {
                return false;
            } else if (
                WC()->session->get('BLOCKED_COUNT_'.$this->id, 0) >= $this->blockedCount &&
                !$this->is_advance_payment()
            ) {
                //return false;
            }
        }

        return parent::is_available();
    }

    public function check_config_values_api()
    {
        if (get_option('ABILITA_API_RUNTIME') == 'LIVE' &&
            get_option('ABILITA_API_STATUS_LIVE') != 1
        ) {
            return false;
        } else if (get_option('ABILITA_API_RUNTIME') == 'TEST' &&
            get_option('ABILITA_API_STATUS_TEST') != 1
        ) {
            return false;
        }
        return true;
    }

    public function check_config_values_sepa_mandate()
    {
		if ($this->is_sepa_payment()) {
			if (empty($this->sepaBankAccountHolder) &&
				empty($this->sepaBankName)          &&
				empty($this->sepaBankIban)          &&
				empty($this->sepaBankBic)           &&
				empty($this->sepaStreet)            &&
				empty($this->sepaZipcode)           &&
				empty($this->sepaCity)              &&
                empty($this->sepaCreditorId)
			) {
				return false;
			}
		}
		
        return true;
    }

    public function check_config_values_advanced()
    {
        if ($this->is_advance_payment()) {
            if (empty($this->bankAccountHolder) &&
                empty($this->bankName) &&
                empty($this->bankIban) &&
                empty($this->bankBic)
            ) {
                return false;
            }
        }

        return true;
    }

    public function is_advance_payment()
    {
		if ($this->id == 'abilita-advance') {
			return true;
		}
        
		return false;
    }

    public function is_sepa_payment()
    {
		if ($this->id == 'abilita-sepa') {
			return true;
		}
        
		return false;
    }

    public function get_icon() {

        $icon = $this->icon ? '<img src="'.WC_HTTPS::force_https_url($this->icon).'" alt="'.esc_attr($this->get_title()).'" class="abilitaPaymentIcon"/>' : '';
        return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
    }

    public function get_advance_payload()
    {
        $this->get_billing_data();
        $this->get_shipping_data();
        $this->get_birthday_data();
        return $this->payload;
    }

    public function get_paypal_payload()
    {
        $this->get_billing_data();
        $this->get_shipping_data();

        $this->payload = array_merge($this->payload, array_filter([
            'success_url' => get_site_url().'/wc-api/abilita-paypal-success',
            'cancel_url'  => get_site_url().'/wc-api/abilita-paypal-error',
            'error_url'  => get_site_url().'/wc-api/abilita-paypal-error',
        ]));

        return $this->payload;
    }

    public function get_aiia_payload()
    {
        $this->get_billing_data();
        $this->get_shipping_data();

        $this->payload = array_merge($this->payload, array_filter([
            'success_url' => get_site_url().'/wc-api/abilita-aiia-success',
            'cancel_url'  => get_site_url().'/wc-api/abilita-aiia-error',
            'error_url'  => get_site_url().'/wc-api/abilita-aiia-error',
        ]));

        return $this->payload;
    }

    public function get_creditcard_payload()
    {
        $this->get_billing_data();
        $this->get_shipping_data();

        $this->payload = array_merge($this->payload, array_filter([
            'success_url' => get_site_url().'/wc-api/abilita-creditcard-success',
            'cancel_url'  => get_site_url().'/wc-api/abilita-creditcard-error',
            'error_url'   => get_site_url().'/wc-api/abilita-creditcard-error',
        ]));

        return $this->payload;
    }

    public function get_invoice_payload()
    {
        $this->get_billing_data();
        $this->get_shipping_data();

		// $_POST comes from progress checkout with woocommerce-process-checkout-nonce
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (
            empty($this->order->get_billing_company()) &&
            (isset($_POST['billing_vat_id']) && empty($_POST['billing_vat_id']))
        ) {
            $this->get_birthday_data();
            $this->get_risk_check_approval();
        }

        return $this->payload;
    }

    public function get_sepa_payload()
    {
        $this->get_billing_data();
        $this->get_shipping_data();
        $this->get_sepa_data();
        $this->get_birthday_data();
        $this->get_risk_check_approval();
        //$this->getMandateReference();
        return $this->payload;
    }

    public function get_billing_data()
    {
        $this->payload = [
            'order_id'    => $this->ordernumberPrefix.$this->orderId,
            'amount'      => (float) parent::get_order_total(),
            'currency'    => get_woocommerce_currency(),
            'gender'      => $this->abilitaFormService->get_salutation(),
            'first_name'  => $this->order->get_billing_first_name(),
            'last_name'   => $this->order->get_billing_last_name(),
            'address'     => $this->order->get_billing_address_1(),
            'postal_code' => $this->order->get_billing_postcode(),
            'city'        => $this->order->get_billing_city(),
            'country'     => $this->order->get_billing_country(),
            'state'       => $this->order->get_shipping_state(),
            'email'       => $this->order->get_billing_email(),
            'phone'       => $this->order->get_billing_phone()
        ];

        if (!empty($this->order->get_billing_company())) {
            $this->payload = array_merge($this->payload, array_filter([
                'company' => $this->order->get_billing_company()
            ]));
        }

		// $_POST comes from progress checkout with woocommerce-process-checkout-nonce
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (isset($_POST['billing_vat_id'])) {
            $vatId = $this->abilitaFormService->get_vat_number();
            $this->payload = array_merge($this->payload, array_filter([
                'company_vat_id' => $vatId
            ]));
        }
    }

    public function get_shipping_data()
    {
		// $_POST comes from progress checkout with woocommerce-process-checkout-nonce
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (
            (isset($_POST['ship_to_different_address']) && $this->allowDifferentAddress === 'yes') ||
            (isset($_POST['ship_to_different_address']) && $this->id === 'abilita-advance')
        ) {
            $this->payload = array_merge($this->payload, array_filter([
                'shipping_first_name'  => $this->order->get_shipping_first_name(),
                'shipping_last_name'   => $this->order->get_shipping_last_name(),
                'shipping_address'     => $this->order->get_shipping_address_1(),
                'shipping_postal_code' => $this->order->get_shipping_postcode(),
                'shipping_city'        => $this->order->get_shipping_city(),
                'shipping_country'     => $this->order->get_shipping_country(),
                'shipping_state'       => $this->order->get_shipping_state()
            ]));

            if (!empty($this->order->get_shipping_company())) {
                $this->payload = array_merge($this->payload, array_filter([
                    'shipping_company' => $this->order->get_shipping_company()
                ]));
            }

			// $_POST comes from progress checkout with woocommerce-process-checkout-nonce
        	// phpcs:ignore WordPress.Security.NonceVerification.Missing
            if (isset($_POST['billing_vat_id'])) {
                $this->payload = array_merge($this->payload, array_filter([
                    'company_vat_id' => sanitize_text_field(wp_unslash($_POST['billing_vat_id']))
                ]));
            }

        } else {
            $this->payload = array_merge($this->payload, array_filter([
                'shipping_first_name'  => $this->order->get_billing_first_name(),
                'shipping_last_name'   => $this->order->get_billing_last_name(),
                'shipping_address'     => $this->order->get_billing_address_1(),
                'shipping_postal_code' => $this->order->get_billing_postcode(),
                'shipping_city'        => $this->order->get_billing_city(),
                'shipping_country'     => $this->order->get_billing_country(),
                'shipping_state'       => $this->order->get_billing_state()
            ]));

            if (!empty($this->order->get_billing_company())) {
                $this->payload = array_merge($this->payload, array_filter([
                    'shipping_company' => $this->order->get_billing_company()
                ]));
            }
        }
    }

    public function get_sepa_data()
    {
		// $_POST comes from progress checkout with woocommerce-process-checkout-nonce
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $this->payload = array_merge($this->payload, array_filter([
            'account_holder' => isset($_POST['sepa_account_holder']) ? sanitize_text_field(wp_unslash($_POST['sepa_account_holder'])) : null,
            'iban'           => isset($_POST['sepa_iban']) ? sanitize_text_field(wp_unslash($_POST['sepa_iban'])) : null,
            'bic'            => isset($_POST['sepa_bic']) ? sanitize_text_field(wp_unslash($_POST['sepa_bic'])) : null,
        ]));
    }

    public function get_birthday_data()
    {
		// $_POST comes from progress checkout with woocommerce-process-checkout-nonce
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $this->payload = array_merge($this->payload, array_filter([
            'date_of_birth' => $this->abilitaFormService->get_payment_birthday($_POST),
        ]));
    }

    public function get_risk_check_approval()
    {
        $this->payload = array_merge($this->payload, array_filter([
            'risk_check_approval' => '1'
        ]));
    }

    // @deprecated @todo
    public function get_mandate_rReference()
    {
        $abilitaSepaHasReference = get_option('ABILITA_SEPA_HAS_REFERENCE');
        $abilitaSepaReference    = get_option('ABILITA_SEPA_REFERENCE');
        if ($abilitaSepaHasReference) {
            $this->payload = array_merge($this->payload, array_filter([
                'mandate_reference' => get_option('ABILITA_SEPA_REFERENCE')
            ]));
        }
    }

    public function get_completed_customer_orders() {

        $orderArguments = [
            'customer_id' => $this->customerId,
            'status'      => 'completed',
            'return'      => 'ids'
        ];

        $orders = wc_get_orders($orderArguments);
        return count($orders);
    }

    public function get_payment_countries()
    {
        if (is_array($this->get_option('countries'))) {
            return $this->get_option('countries');
        }

        return explode(
            ',',
            strtr(
                strtoupper(
                    $this->get_option('countries')
                ),
                [' '=>'']
            )
        );
    }

    public function set_order_status($status)
    {
        if ($status == 'disabled') {
            $status = self::PAYMENT_STATUS_ON_HOLD;
        }

        $this->order->update_status($status);
    }

    public function get_abilita_payment_name()
    {
        return $this->abilita_payment_name;
    }

    public function get_abilita_payment_id()
    {
        return $this->id;
    }

    public function add_birthday_to_order()
    {
		// $_POST comes from progress checkout with woocommerce-process-checkout-nonce
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (
            empty($this->order->get_billing_company()) &&
            (isset($_POST['billing_vat_id']) && empty($_POST['billing_vat_id']))
        ) {
			// $_POST comes from progress checkout with woocommerce-process-checkout-nonce
        	// phpcs:ignore WordPress.Security.NonceVerification.Missing
            $customerBirthday = gmdate('d.m.Y', strtotime($this->abilitaFormService->get_payment_birthday($_POST)));
            if (get_option('woocommerce_custom_orders_table_enabled') == 'yes') {
                $this->order->update_meta_data('ABILITA_CUSTOMER_BIRTHDAY', $customerBirthday);
                $this->order->save_meta_data();
            } else {
                update_post_meta($this->orderId, 'ABILITA_CUSTOMER_BIRTHDAY', $customerBirthday);
            }
        }
    }

    public function add_vat_number_to_order()
    {
        $vatId = $this->abilitaFormService->get_vat_number();
        if (get_option('woocommerce_custom_orders_table_enabled') == 'yes') {
            $this->order->update_meta_data('ABILITA_CUSTOMER_VAT_ID', $vatId);
            $this->order->save_meta_data();
        } else {
            update_post_meta($this->orderId, 'ABILITA_CUSTOMER_VAT_ID', $vatId);
        }
    }

    public function add_sepa_reference_to_order($sepaReference)
    {
        if (get_option('woocommerce_custom_orders_table_enabled') == 'yes') {
            $this->order->update_meta_data('ABILITA_SEPA_REFERENCE', $sepaReference);
            $this->order->save_meta_data();
        } else {
            update_post_meta($this->orderId, 'ABILITA_SEPA_REFERENCE', $sepaReference);
        }

        WC()->session->set('ABILITA_SEPA_REFERENCE', $sepaReference);
    }

    public function add_salutation_to_order()
    {
        $salutation = $this->abilitaFormService->get_salutation();
        if (get_option('woocommerce_custom_orders_table_enabled') == 'yes') {
            $this->order->update_meta_data('ABILITA_CUSTOMER_SALUTATION', $salutation);
            $this->order->save_meta_data();
        } else {
            update_post_meta($this->orderId, 'ABILITA_CUSTOMER_SALUTATION', $salutation);
        }
    }

    public function response_payment_error($errorMessage)
    {
        $this->set_order_status(self::PAYMENT_STATUS_FAILED);
        $this->order->add_order_note($errorMessage);
        $this->order->save();
        $this->count_up_error();
        return wp_send_json($this->get_json_error(
            __('Es ist ein Fehler aufgetreten. Bitte prüfen Sie Ihre Daten oder wählen eine andere Zahlungsart aus.', 'abilita-payments-for-woocommerce')
        ));
    }

    public function response_other_payment_error()
    {
        return wp_send_json(
            $this->get_json_error(
                __('Es ist ein Fehler aufgetreten. Bitte prüfen Sie Ihre Daten oder wählen eine andere Zahlungsart aus.', 'abilita-payments-for-woocommerce')
            )
        );
    }

    public function count_up_error()
    {
        $blockedCount = WC()->session->get('BLOCKED_COUNT_'.$this->id, 0);
        $blockedCount++;
        WC()->session->set('BLOCKED_COUNT_'.$this->id, $blockedCount);
    }

    public function get_json_error($message)
    {
        return [
            'error'    => true,
            'messages' => '<ul class="woocommerce-error"><li>'.$message.'</li></ul>',
            'refresh'  => true,
            'reload'   => false
        ];
    }

    public function handle_response()
    {
        header('HTTP/1.1 200 OK');
        header('Content-Type: application/json');
        die(wp_json_encode([
            'result' => 'success'
        ], JSON_THROW_ON_ERROR));
    }

    public function handle_postback($function)
    {
        $params = [];

        // $_POST comes over external API without NONCE
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        foreach ($_POST as $key => $value) {
            $params[$key] = sanitize_text_field($value);
        }

        try {
            if ($params) {
				// $params comes over external API without NONCE
        		// phpcs:ignore WordPress.Security.NonceVerification.Missing
                if (isset($params['order_id'])) {
					// $params comes over external API without NONCE
        			// phpcs:ignore WordPress.Security.NonceVerification.Missing
                    $params['order_id'] = str_replace(get_option('ABILITA_ORDERNUMBER_PREFIX'), '', $params['order_id']);
					// $params comes over external API without NONCE
        			// phpcs:ignore WordPress.Security.NonceVerification.Missing
                    $order = wc_get_order($params['order_id']);
					// $params comes over external API without NONCE
        			// phpcs:ignore WordPress.Security.NonceVerification.Missing
                    if ($order && $params['order_id'] == $order->get_id() &&
						$params['transaction_id'] == $order->get_transaction_id($params['transaction_id'])
                    ) {
                        $orderStatus = $order->get_status();
						// $params comes over external API without NONCE
        				// phpcs:ignore WordPress.Security.NonceVerification.Missing
                        $apiStatus   = $this->abilitaHelper->get_order_statuses($params['status']);
                        if ($orderStatus != $apiStatus) {
                            $order->update_status('wc-'.$apiStatus);
							// $params comes over external API without NONCE
        					// phpcs:ignore WordPress.Security.NonceVerification.Missing
                            $order->add_order_note(print_r($params, true));
							// $params comes over external API without NONCE
        					// phpcs:ignore WordPress.Security.NonceVerification.Missing
                            $order->add_order_note('Update from api-status: '.$params['status'].' to woocommerce-status: '.$apiStatus);
                        } else {
							// $params comes over external API without NONCE
        					// phpcs:ignore WordPress.Security.NonceVerification.Missing
                            $order->add_order_note(print_r($params, true));
							// $params comes over external API without NONCE
        					// phpcs:ignore WordPress.Security.NonceVerification.Missing
                            $order->add_order_note('Postback has nothing to do. The order has woocommerce-status: ' . $orderStatus . ' / given api-status: ' . $params['status']);
                        }
                        $order->save();
                    }
                    $this->abilitaLoggerService->log('info', __CLASS__, $function, $params);
                } else {
                    $this->abilitaLoggerService->log('alert', __CLASS__, $function, $params);
                }
            } else {
                throw new Exception('No params was send.');
            }
        } catch (Exception $e) {
			// $params, $_GET, $_POST comes over external API without NONCE
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
            $this->abilitaLoggerService->log(
                'alert',
                __CLASS__,
                $function,
                [
                    'params' => $params,
                    '_GET'   => $_GET,
                    '_POST'  => $_POST,
                    'error'  => $e
                ]
            );
        }
    }
}

