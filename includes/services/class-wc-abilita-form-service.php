<?php declare(strict_types=1);

namespace abilita\payment\services;

use abilita\payment\services\WC_Abilita_VatId_Service;

defined('ABSPATH') || exit;

class WC_Abilita_Form_Service
{
    private $abilitaVatIdService;

    public function __construct()
    {
        $this->abilitaVatIdService = new WC_Abilita_VatId_Service();
    }

    public function getPostDataRequest($key)
    {
        $post_data = [];
        if (isset($_POST[$key]) && !empty($_POST[$key])) {
            parse_str(
                sanitize_text_field(
                    wp_unslash(
                        $_POST[$key]
                    )
                ),
                $post_data
            );

            $_POST = array_merge(
                $_POST,
                $post_data
            );
        }
    }

    public function get_payment_birthday_fields($paymentMethod)
    {
        $birthdayField = '';
        $birthday =
            WC()->session->get('billing_birthyear') . '-' .
            WC()->session->get('billing_birthmonth') . '-' .
            WC()->session->get('billing_birthday');

        if (!empty($birthday)) {
            list(
                $paysierBoniBirthyear,
                $paysierBoniBirthmonth,
                $paysierBoniBirthday
                ) = explode('-', $birthday);
        } else {
            $paysierBoniBirthyear = '';
            $paysierBoniBirthmonth = '';
            $paysierBoniBirthday = '';
        }

        $day_options = ['' => __('Tag', 'abilita-payments-for-woocommerce')];
        for ($i = 1; $i <= 31; $i++) {
            if ($i < 10) {
                $day = '0' . $i;
            } else {
                $day = $i;
            }
            $day_options[$day] = $day;
        }

        $month_options = ['' => __('Monat', 'abilita-payments-for-woocommerce')];
        for ($i = 1; $i <= 12; $i++) {
            if ($i < 10) {
                $month = '0' . $i;
            } else {
                $month = $i;
            }
            $month_options[$month] = $month;
        }


        $year_options = ['' => __('Jahr', 'abilita-payments-for-woocommerce')];
        foreach (range(gmdate('Y') - (float)18, gmdate('Y') - (float)80) as $i) {
            $year_options[$i] = $i;
        }

        $birthdayField .= '<p class="abilitaBirthdayLabel">' . __('Bitte geben Sie Ihr Geburtsdatum an:', 'abilita-payments-for-woocommerce') . '</p>';
        $birthdayField .= '<p><select name="abilitaBirthday[' . $paymentMethod . ']" id="abilitaBirthday-' . $paymentMethod . '" class="abilitaBirthday" required="required">';
        foreach ($day_options as $value => $label) {
            $selected = '';
            if ($value == $paysierBoniBirthday) {
                $selected = ' selected';
            }
            $birthdayField .= '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
        }
        $birthdayField .= '</select>';

        $birthdayField .= '<select name="abilitaBirthmonth[' . $paymentMethod . ']" id="abilitaBirthmonth-' . $paymentMethod . '" class="abilitaBirthmonth" required="required">';
        foreach ($month_options as $value => $label) {
            $selected = '';
            if ($value == $paysierBoniBirthmonth) {
                $selected = ' selected';
            }
            $birthdayField .= '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
        }
        $birthdayField .= '</select>';

        $birthdayField .= '<select name="abilitaBirthyear[' . $paymentMethod . ']" id="abilitaBirthyear-' . $paymentMethod . '" class="abilitaBirthyear" required="required">';
        foreach ($year_options as $value => $label) {
            $selected = '';
            if ($value == $paysierBoniBirthyear) {
                $selected = ' selected';
            }
            $birthdayField .= '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
        }
        $birthdayField .= '</select></p>';

        return $birthdayField;
    }

    public function get_payment_birthday($post)
    {
        if (isset($post['abilitaBirthday'][$post['payment_method']])) {
            $abilitaBirthday   = isset($post['abilitaBirthday'][$post['payment_method']]) ? (int)sanitize_text_field($post['abilitaBirthday'][$post['payment_method']]) : 0;
            $abilitaBirthmonth = isset($post['abilitaBirthmonth'][$post['payment_method']]) ? (int)sanitize_text_field($post['abilitaBirthmonth'][$post['payment_method']]) : 0;
            $abilitaBirthyear  = isset($post['abilitaBirthyear'][$post['payment_method']]) ? (int)sanitize_text_field($post['abilitaBirthyear'][$post['payment_method']]) : 0;
            return gmdate('d.m.Y', strtotime($abilitaBirthyear . '-' . $abilitaBirthmonth . '-' . $abilitaBirthday));
        }

        return null;
    }

    public function get_salutation()
    {
        $billingSalutationKey = $this->map_salutation();
        $salutation = isset($_POST[$billingSalutationKey]) ? sanitize_text_field(wp_unslash($_POST[$billingSalutationKey])) : null;
        return !empty($salutation) ? $salutation : 'd';
    }

    public function get_vat_number()
    {
        $billingVatIdKey = $this->map_vat_number();
        $vatNumber = isset($_POST[$billingVatIdKey]) ? sanitize_text_field(wp_unslash($_POST[$billingVatIdKey])) : null;
        return !empty($vatNumber) ? $vatNumber : '';
    }

    public function map_salutation()
    {
        $ABILITA_FORM_FIELD_OWN_SALUTATION_NAME = get_option('ABILITA_FORM_FIELD_OWN_SALUTATION_NAME');
        if (!empty($ABILITA_FORM_FIELD_OWN_SALUTATION_NAME)) {
            return $ABILITA_FORM_FIELD_OWN_SALUTATION_NAME;
        }

        return 'billing_title';
    }

    public function map_vat_number()
    {
        $ABILITA_FORM_FIELD_OWN_VAT_ID_NAME = get_option('ABILITA_FORM_FIELD_OWN_VAT_ID_NAME');
        if (!empty($ABILITA_FORM_FIELD_OWN_VAT_ID_NAME)) {
            return $ABILITA_FORM_FIELD_OWN_VAT_ID_NAME;
        }

        return 'billing_vat_id';
    }

    public function validate_title()
    {
        $billingSalutationKey = $this->map_salutation();

		if (!isset($_POST[$billingSalutationKey])) {
			wc_add_notice(
                __('Bitte wählen Sie eine Anrede aus', 'abilita-payments-for-woocommerce'),
                'error'
            );
            return false;
        } else if (isset($_POST[$billingSalutationKey]) && empty($_POST[$billingSalutationKey])) {
            wc_add_notice(
                __('Bitte wählen Sie eine Anrede aus', 'abilita-payments-for-woocommerce'),
                'error'
            );
            return false;
        } else if (isset($_POST[$billingSalutationKey]) && !in_array($_POST[$billingSalutationKey], ['m', 'f', 'd'])) {
            wc_add_notice(
                __('Bitte wählen Sie eine Anrede aus', 'abilita-payments-for-woocommerce'),
                'error'
            );
            return false;
        }

        $billing_title = isset($_POST[$billingSalutationKey]) ? sanitize_text_field(wp_unslash($_POST[$billingSalutationKey])) : null;
        WC()->session->set('billing_title', $billing_title);

        return true;
    }

    public function validate_phone($allowPhonenumber)
    {
        if ($allowPhonenumber == 'yes') {
            if (!isset($_POST['billing_phone'])) {
                wc_add_notice(
                    __('Bitte geben Sie eine Telefonnummer an', 'abilita-payments-for-woocommerce'),
                    'error'
                );
                return false;
            } else if (isset($_POST['billing_phone']) && empty($_POST['billing_phone'])) {
                wc_add_notice(
                    __('Bitte geben Sie eine Telefonnummer an', 'abilita-payments-for-woocommerce'),
                    'error'
                );
                return false;
            }
        }
        return true;
    }

    public function validate_different_delivery_address(
        $allowDifferentAddressInvoice,
        $post
    )
    {
        if ($allowDifferentAddressInvoice != 'yes') {
            if (!$this->check_different_delivery_address($post) && isset($post['ship_to_different_address']) && $post['ship_to_different_address'] == 1) {
                wc_add_notice(
                    __('Bei dieser Zahlungsart dürfen Sie keine abweichende Lieferadresse benutzen. Bitte prüfen Sie Ihre Eingaben.', 'abilita-payments-for-woocommerce'),
                    'error'
                );
                return false;
            }
        }

        return true;
    }

    public function validate_birthday($post)
    {
        if (
            isset($post['abilitaBirthday'][$post['payment_method']]) &&
            isset($post['abilitaBirthmonth'][$post['payment_method']]) &&
            isset($post['abilitaBirthyear'][$post['payment_method']])
        ) {

            $abilitaBirthday   = (int)sanitize_text_field($post['abilitaBirthday'][$post['payment_method']]);
            $abilitaBirthmonth = (int)sanitize_text_field($post['abilitaBirthmonth'][$post['payment_method']]);
            $abilitaBirthyear  = (int)sanitize_text_field($post['abilitaBirthyear'][$post['payment_method']]);

            WC()->session->set('billing_birthday', $abilitaBirthday);
            WC()->session->set('billing_birthmonth', $abilitaBirthmonth);
            WC()->session->set('billing_birthyear', $abilitaBirthyear);

            if (!checkdate($abilitaBirthmonth, $abilitaBirthday, $abilitaBirthyear)) {
                wc_add_notice(
                    __('Bitte stellen Sie sicher, dass Ihr Geburtsdatum korrekt angegeben ist.', 'abilita-payments-for-woocommerce'),
                    'error'
                );
                return false;
            } else {
                $birthday = $abilitaBirthyear . '-' . $abilitaBirthmonth . '-' . $abilitaBirthday;
                WC()->session->set('PAYSIER_BIRTH', $birthday);
                $birthdate = new \DateTime($birthday);
                $today = new \DateTime();
                $customerAge = $today->diff($birthdate);
                if ($customerAge->y < 18) {
                    wc_add_notice(
                        __('Für die Nutzung der gewählten Zahlungsoption ist ein Mindestalter von 18 Jahren erforderlich.', 'abilita-payments-for-woocommerce'),
                        'error'
                    );
                    return false;
                }
            }

            return $birthdate->format('d.m.Y');
        }

        return true;
    }

    public function validate_sepa_legal_legitimacy($useLegalLegitimacy)
    {
        if (!$this->form_validate_legal_legitimacy($useLegalLegitimacy)) {
            wc_add_notice(
                __('Bitte bestätigen Sie die Ermächtigung zur Erteilung eines SEPA-Mandats.', 'abilita-payments-for-woocommerce'),
                'error'
            );
            return false;
        }

        return true;
    }

    public function validate_invoice_legal_legitimacy($useLegalLegitimacy)
    {
        if (!$this->form_validate_legal_legitimacy($useLegalLegitimacy)) {
            WC()->session->set('use_legal_legitimacy', false);
            wc_add_notice(
                __('Bitte bestätigen Sie, dass ein berechtigten Interesse für die Feststellung meiner Zahlungsfähigkeit vorliegt.', 'abilita-payments-for-woocommerce'),
                'error'
            );
            return false;
        }

        WC()->session->set('use_legal_legitimacy', true);
        return true;
    }

    private function form_validate_legal_legitimacy($useLegalLegitimacy)
    {
        if ($useLegalLegitimacy == 'yes') {
            if (!isset($_POST['use_legal_legitimacy'])) {
                return false;
            }
        }

        return true;
    }

    public function validate_vat_number($post)
    {
        $billingVatIdKey = $this->map_vat_number();
        $vatId = isset($post[$billingVatIdKey]) ? sanitize_text_field(wp_unslash($post[$billingVatIdKey])) : false;
        if (empty($vatId)) {
            WC()->session->set('billing_vat_id', '');
            return true;
        }

        $this->abilitaVatIdService->setVatId($vatId);
        if ($this->abilitaVatIdService->validate()) {
            WC()->session->set('billing_vat_id', $vatId);
            return true;
        }

        wc_add_notice(
            __('Bitte prüfen Sie Ihre Umsatzsteuer-ID.', 'abilita-payments-for-woocommerce'),
            'error'
        );

        return false;
    }

    public function validate_sepa_account_holder()
    {
        $accountHolder = isset($_POST['sepa_account_holder']) ? strtolower(sanitize_text_field(wp_unslash($_POST['sepa_account_holder']))) : null;
        if (empty($accountHolder)) {
            wc_add_notice(
                __('Bitte prüfen Sie die Eingabe des Kontoinhaber.', 'abilita-payments-for-woocommerce'),
                'error'
            );
            return false;
        }

        return true;
    }

    public function validate_iban_bic_country()
    {
        $iban = isset($_POST['sepa_iban']) ? strtolower(sanitize_text_field(wp_unslash($_POST['sepa_iban']))) : null;
        $bic  = isset($_POST['sepa_bic']) ? strtolower(sanitize_text_field(wp_unslash($_POST['sepa_bic']))) : null;

        $countryCodeIban = substr($iban, 0, 2);
        $countryCodeBic  = substr($bic, 4, 2);

        if ($countryCodeIban != $countryCodeBic) {
            wc_add_notice(
                __('Bitte prüfen Sie die Eingabe der IBAN und BIC.', 'abilita-payments-for-woocommerce'),
                'error'
            );
            return false;
        }

        return true;
    }

    public function validate_bic()
    {
        $bic = isset($_POST['sepa_bic']) ? strtoupper(sanitize_text_field(wp_unslash($_POST['sepa_bic']))) : null;

        if (!in_array(strlen($bic), [8, 11])) {
            wc_add_notice(
                __('Bitte prüfen Sie die Eingabe des BIC.', 'abilita-payments-for-woocommerce'),
                'error'
            );
            return false;
        }

        $pattern = '/^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/';
        if (!preg_match($pattern, $bic)) {
            wc_add_notice(
                __('Bitte prüfen Sie die Eingabe des BIC.', 'abilita-payments-for-woocommerce'),
                'error'
            );
            return false;
        }

        $countryCode = substr($bic, 4, 2);
        $isoCountries = [
            'AF', 'AL', 'DZ', 'AS', 'AD', 'AO', 'AI', 'AQ', 'AG', 'AR', 'AM', 'AW', 'AU', 'AT', 'AZ',
            'BS', 'BH', 'BD', 'BB', 'BY', 'BE', 'BZ', 'BJ', 'BM', 'BT', 'BO', 'BA', 'BW', 'BR', 'IO',
            'BN', 'BG', 'BF', 'BI', 'CV', 'KH', 'CM', 'CA', 'KY', 'CF', 'TD', 'CL', 'CN', 'CO', 'KM',
            'CD', 'CG', 'CR', 'CI', 'HR', 'CU', 'CY', 'CZ', 'DK', 'DJ', 'DM', 'DO', 'EC', 'EG', 'SV',
            'GQ', 'ER', 'EE', 'SZ', 'ET', 'FJ', 'FI', 'FR', 'GA', 'GM', 'GE', 'DE', 'GH', 'GR', 'GD',
            'GT', 'GN', 'GW', 'GY', 'HT', 'HN', 'HK', 'HU', 'IS', 'IN', 'ID', 'IR', 'IQ', 'IE', 'IL',
            'IT', 'JM', 'JP', 'JO', 'KZ', 'KE', 'KI', 'KP', 'KR', 'KW', 'KG', 'LA', 'LV', 'LB', 'LS',
            'LR', 'LY', 'LI', 'LT', 'LU', 'MG', 'MW', 'MY', 'MV', 'ML', 'MT', 'MH', 'MR', 'MU', 'MX',
            'FM', 'MD', 'MC', 'MN', 'ME', 'MA', 'MZ', 'MM', 'NA', 'NR', 'NP', 'NL', 'NZ', 'NI', 'NE',
            'NG', 'NO', 'OM', 'PK', 'PW', 'PA', 'PG', 'PY', 'PE', 'PH', 'PL', 'PT', 'QA', 'MK', 'RO',
            'RU', 'RW', 'WS', 'SM', 'ST', 'SA', 'SN', 'RS', 'SC', 'SL', 'SG', 'SK', 'SI', 'SB', 'SO',
            'ZA', 'SS', 'ES', 'LK', 'SD', 'SR', 'SE', 'CH', 'SY', 'TW', 'TJ', 'TZ', 'TH', 'TL', 'TG',
            'TO', 'TT', 'TN', 'TR', 'TM', 'TV', 'UG', 'UA', 'AE', 'GB', 'US', 'UY', 'UZ', 'VU', 'VA',
            'VE', 'VN', 'EH', 'YE', 'ZM', 'ZW'
        ];

        if (!in_array($countryCode, $isoCountries)) {
            wc_add_notice(
                __('Bitte prüfen Sie die Eingabe des BIC.', 'abilita-payments-for-woocommerce'),
                'error'
            );
            return false;
        }

        return true;
    }

    public function validate_iban()
    {
        $iban = isset($_POST['sepa_iban']) ? strtolower(sanitize_text_field(wp_unslash($_POST['sepa_iban']))) : null;
        $countries = [
            'al' => 28,
            'ad' => 24,
            'at' => 20,
            'az' => 28,
            'bh' => 22,
            'be' => 16,
            'ba' => 20,
            'br' => 29,
            'bg' => 22,
            'cr' => 21,
            'hr' => 21,
            'cy' => 28,
            'cz' => 24,
            'dk' => 18,
            'do' => 28,
            'ee' => 20,
            'fo' => 18,
            'fi' => 18,
            'fr' => 27,
            'ge' => 22,
            'de' => 22,
            'gi' => 23,
            'gr' => 27,
            'gl' => 18,
            'gt' => 28,
            'hu' => 28,
            'is' => 26,
            'ie' => 22,
            'il' => 23,
            'it' => 27,
            'jo' => 30,
            'kz' => 20,
            'kw' => 30,
            'lv' => 21,
            'lb' => 28,
            'li' => 21,
            'lt' => 20,
            'lu' => 20,
            'mk' => 19,
            'mt' => 31,
            'mr' => 27,
            'mu' => 30,
            'mc' => 27,
            'md' => 24,
            'me' => 22,
            'nl' => 18,
            'no' => 15,
            'pk' => 24,
            'ps' => 29,
            'pl' => 28,
            'pt' => 25,
            'qa' => 29,
            'ro' => 24,
            'sm' => 27,
            'sa' => 24,
            'rs' => 22,
            'sk' => 24,
            'si' => 19,
            'es' => 24,
            'se' => 24,
            'ch' => 21,
            'tn' => 24,
            'tr' => 26,
            'ae' => 23,
            'gb' => 22,
            'vg' => 24,
        ];
        $chars = [
            'a' => 10,
            'b' => 11,
            'c' => 12,
            'd' => 13,
            'e' => 14,
            'f' => 15,
            'g' => 16,
            'h' => 17,
            'i' => 18,
            'j' => 19,
            'k' => 20,
            'l' => 21,
            'm' => 22,
            'n' => 23,
            'o' => 24,
            'p' => 25,
            'q' => 26,
            'r' => 27,
            's' => 28,
            't' => 29,
            'u' => 30,
            'v' => 31,
            'w' => 32,
            'x' => 33,
            'y' => 34,
            'z' => 35,
        ];

        if (!isset($countries[substr($iban, 0, 2)]) || strlen($iban) != $countries[substr($iban, 0, 2)]) {
            wc_add_notice(
                __('Bitte prüfen Sie die Eingabe der IBAN.', 'abilita-payments-for-woocommerce'),
                'error'
            );
            return false;
        }

        $movedChar = substr($iban, 4) . substr($iban, 0, 4);
        $movedCharArray = str_split($movedChar);
        $newString = '';

        foreach ($movedCharArray as $k => $v) {
            if (!is_numeric($movedCharArray[$k])) {
                $movedCharArray[$k] = $chars[$movedCharArray[$k]];
            }
            $newString .= $movedCharArray[$k];
        }

        if (bcmod($newString, '97') != 1) {
            wc_add_notice(
                __('Bitte prüfen Sie die Eingabe der IBAN.', 'abilita-payments-for-woocommerce'),
                'error'
            );
            return false;
        }

        return true;
    }

    public function check_different_delivery_address($post)
    {
        $billingAddress = md5(
            sanitize_text_field($post['billing_first_name']) .
            sanitize_text_field($post['billing_last_name']) .
            sanitize_text_field($post['billing_address_1']) .
            sanitize_text_field($post['billing_city']) .
            sanitize_text_field($post['billing_postcode']) .
            'DE'
        );

        $shippingAddress = md5(
            sanitize_text_field($post['shipping_first_name']) .
            sanitize_text_field($post['shipping_last_name']) .
            sanitize_text_field($post['shipping_address_1']) .
            sanitize_text_field($post['shipping_city']) .
            sanitize_text_field($post['shipping_postcode']) .
            'DE'
        );

        return $billingAddress == $shippingAddress;
    }
}
