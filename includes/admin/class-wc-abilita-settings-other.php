<?php declare(strict_types=1);

namespace abilita\payment\admin;

defined('ABSPATH') || exit;

class WC_Abilita_Settings_Other {

    private $abilitaSettingsGroup;
    private $formFieldSalutation;
    private $formFieldOwnSalutationName;
	private $formFieldCompanyLabelText;
    private $formFieldCompanyVatId;
    private $formFieldOwnVatIdName;
    private $formFieldCompanyVatIdLabelText;
    private $ordernumberPrefix;
    private $paymentDebugLogger;
    private $cssBirthday;

    public function __construct($abilitaSettingsGroup)
    {
        $this->abilitaSettingsGroup       = $abilitaSettingsGroup;
        $this->formFieldSalutation        = !empty(get_option('ABILITA_FORM_FIELD_SALUTATION')) ? 1 : false;
        $this->formFieldOwnSalutationName = get_option('ABILITA_FORM_FIELD_OWN_SALUTATION_NAME');
        $this->ordernumberPrefix           = get_option('ABILITA_ORDERNUMBER_PREFIX');
        $this->formFieldCompanyVatId      = get_option('ABILITA_FORM_FIELD_VAT_ID');
        $this->formFieldOwnVatIdName      = get_option('ABILITA_FORM_FIELD_OWN_VAT_ID_NAME');
        $this->paymentDebugLogger         = !empty(get_option('ABILITA_PAYMENT_DEBUG_LOGGER')) ? 1 : false;

        if (get_option('ABILITA_FORM_FIELD_COMPANY_LABEL_TEXT') == '' && isset($_POST['action']) && $_POST['action'] != 'update') {
            $this->formFieldCompanyLabelText = __('Für Bestellungen auf Firmenrechnung (B2B) im folgenden bitte die Firmendaten angeben.', 'abilita-payments-for-woocommerce');
        } else {
            $this->formFieldCompanyLabelText = get_option('ABILITA_FORM_FIELD_COMPANY_LABEL_TEXT');
        }

        if (get_option('ABILITA_FORM_FIELD_VAT_ID_LABEL_TEXT') == '' && isset($_POST['action']) && $_POST['action'] != 'update') {
            $this->formFieldCompanyVatIdLabelText = __('Falls vorhanden, bitte die Umsatzsteuer-ID eingeben.', 'abilita-payments-for-woocommerce');
        } else {
            $this->formFieldCompanyVatIdLabelText = get_option('ABILITA_FORM_FIELD_VAT_ID_LABEL_TEXT');
        }

        if (get_option('ABILITA_CSS_BIRTHDAY') == '') {
            $this->cssBirthday = file_get_contents(plugin_dir_path(__FILE__) . 'assets/css/DefaultBirthdayField.css');
        } else {
            $this->cssBirthday = get_option('ABILITA_CSS_BIRTHDAY');
        }
    }

    public function get_view()
    {
        require_once('partials/SettingsOther.php');
    }
}
