<?php declare(strict_types=1);

namespace abilita\payment\admin;

use abilita\payment\services\WC_Abilita_Client_Service;
use abilita\payment\admin\WC_Abilita_Order_Addon_Billing_Address;
use abilita\payment\admin\WC_Abilita_Order_Addon_Status_Meta_Box;
use abilita\payment\admin\WC_Abilita_Order_Status_Handler;
use abilita\payment\admin\WC_Abilita_Payment_Cancel;
use abilita\payment\admin\WC_Abilita_Payment_Reauthorize;
use abilita\payment\admin\WC_Abilita_Payment_Refund;
use abilita\payment\admin\WC_Abilita_Payment_Transactions;
use abilita\payment\admin\WC_Abilita_Settings_Api;
use abilita\payment\admin\WC_Abilita_Settings_Mandate;
use abilita\payment\admin\WC_Abilita_Settings_Other;
use abilita\payment\admin\WC_Abilita_Settings_Status;

defined('ABSPATH') || exit;

class WC_Abilita_Abilita_Admin {

    const ABILITA_SETTINGS_GROUP = 'abilita-settings-group';
    private $abilitaClient;

    public function __construct()
    {
        add_action('admin_menu', [$this, 'abilita_create_menu']);
    }

    public function abilita_create_menu()
    {
        add_menu_page(
            'abilita PAY',
            'abilita PAY',
            'manage_options',
            'abilita_settings_page',
            [$this, 'abilita_settings_page'],
            plugins_url('/../../assets/images/abilita.png', __FILE__)
        );

        /**
        add_submenu_page(
            'abilita_settings_page',
            'Bezahlstatus',
            'Bezahlstatus',
            'manage_options',
            'abilita_settings_page&tab=settingsStatus',
            [$this, 'abilita_settings_page']
        );

        add_submenu_page(
            'abilita_settings_page',
            'API Zugangsdaten',
            'API Zugangsdaten',
            'manage_options',
            'abilita_settings_page&tab=settingsApi',
            [$this, 'abilita_settings_page']
        );

        add_submenu_page(
            'abilita_settings_page',
            'Checkliste',
            'Checkliste',
            'manage_options',
            'abilita_settings_page&tab=settingsInfo',
            [$this, 'abilita_settings_page']
        );

        add_submenu_page(
            'abilita_settings_page',
            'Sonstiges',
            'Sonstiges',
            'manage_options',
            'abilita_settings_page&tab=settingsOther',
            [$this, 'abilita_settings_page']
        );
        **/

        add_action('admin_init', [$this, 'register_abilita_settings']);
    }

    public function register_abilita_settings()
    {
        // Api
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_API_KEY_LIVE', ['type' => 'string']);
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_OUTGOING_API_KEY_LIVE', ['type' => 'string']);
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_ENDPOINT_LIVE', ['type' => 'string']);
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_API_STATUS_LIVE', ['type' => 'string']);
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_API_KEY_TEST', ['type' => 'string']);
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_OUTGOING_API_KEY_TEST', ['type' => 'string']);
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_ENDPOINT_TEST', ['type' => 'string']);
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_API_STATUS_TEST', ['type' => 'string']);
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_API_RUNTIME', ['type' => 'string']);

        // Statusmapping
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_MAPPED_STATUSES', ['string']);

        // Other
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_FORM_FIELD_SALUTATION', ['type' => 'string', 'default' => 1]);
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_FORM_FIELD_OWN_SALUTATION_NAME', ['type' => 'string']);
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_FORM_FIELD_COMPANY_LABEL_TEXT', ['type' => 'string', 'default' => 'Für Bestellungen auf Firmenrechnung (B2B) im folgenden bitte die Firmendaten angeben.']);
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_FORM_FIELD_VAT_ID', ['type' => 'string', 'default' => 1]);
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_FORM_FIELD_VAT_ID_LABEL_TEXT', ['type' => 'string', 'default' => 'Ihre Umsatzsteuer-ID']);
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_FORM_FIELD_OWN_VAT_ID_NAME', ['type' => 'string']);
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_ORDERNUMBER_PREFIX', ['type' => 'string']);
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_PAYMENT_DEBUG_LOGGER', ['type' => 'string']);
        register_setting(self::ABILITA_SETTINGS_GROUP, 'ABILITA_CSS_BIRTHDAY', ['type' => 'string']);
    }

    public function abilita_settings_page()
    {
        $default_tab = null;
        $tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : $default_tab;

        if ($tab == 'settingsOther') {
            if (isset($_POST['action']) && $_POST['action'] == 'update' && check_admin_referer('abilita_nonce_action', 'abilita_nonce_settings_other')) {
                update_option('ABILITA_FORM_FIELD_SALUTATION'           , isset($_POST['ABILITA_FORM_FIELD_SALUTATION'])          ? sanitize_text_field(wp_unslash($_POST['ABILITA_FORM_FIELD_SALUTATION'])) : '');
                update_option('ABILITA_FORM_FIELD_OWN_SALUTATION_NAME'  , isset($_POST['ABILITA_FORM_FIELD_OWN_SALUTATION_NAME']) ? sanitize_text_field(wp_unslash($_POST['ABILITA_FORM_FIELD_OWN_SALUTATION_NAME'])) : '');
                update_option('ABILITA_FORM_FIELD_COMPANY_LABEL_TEXT'   , isset($_POST['ABILITA_FORM_FIELD_COMPANY_LABEL_TEXT'])  ? sanitize_text_field(wp_unslash($_POST['ABILITA_FORM_FIELD_COMPANY_LABEL_TEXT'])) : '');
                update_option('ABILITA_FORM_FIELD_VAT_ID'               , isset($_POST['ABILITA_FORM_FIELD_VAT_ID'])              ? sanitize_text_field(wp_unslash($_POST['ABILITA_FORM_FIELD_VAT_ID'])) : '');
                update_option('ABILITA_FORM_FIELD_VAT_ID_LABEL_TEXT'    , isset($_POST['ABILITA_FORM_FIELD_VAT_ID_LABEL_TEXT'])   ? sanitize_text_field(wp_unslash($_POST['ABILITA_FORM_FIELD_VAT_ID_LABEL_TEXT'])) : '');
                update_option('ABILITA_FORM_FIELD_OWN_VAT_ID_NAME'      , isset($_POST['ABILITA_FORM_FIELD_OWN_VAT_ID_NAME'])     ? sanitize_text_field(wp_unslash($_POST['ABILITA_FORM_FIELD_OWN_VAT_ID_NAME'])) : '');
                update_option('ABILITA_ORDERNUMBER_PREFIX'              , isset($_POST['ABILITA_ORDERNUMBER_PREFIX'])             ? sanitize_text_field(wp_unslash($_POST['ABILITA_ORDERNUMBER_PREFIX'])) : '');
                update_option('ABILITA_PAYMENT_DEBUG_LOGGER'            , isset($_POST['ABILITA_PAYMENT_DEBUG_LOGGER'])           ? sanitize_text_field(wp_unslash($_POST['ABILITA_PAYMENT_DEBUG_LOGGER'])) : '');
                update_option('ABILITA_CSS_BIRTHDAY'                    , isset($_POST['ABILITA_CSS_BIRTHDAY'])                   ? wp_unslash($_POST['ABILITA_CSS_BIRTHDAY']) : '');
            } else if (isset($_POST['action']) && $_POST['action'] == 'update' && check_admin_referer('abilita_nonce_action', 'abilita_nonce_settings_other')) {
                die('Permission denied...');
            }
        }

        if ($tab == 'settingsStatus') {
            if (isset($_POST['action']) && $_POST['action'] == 'update' && check_admin_referer('abilita_nonce_action', 'abilita_nonce_settings_status')) {
                update_option('ABILITA_MAPPED_STATUSES', isset($_POST['ABILITA_MAPPED_STATUSES']) ? sanitize_text_field(wp_json_encode($_POST['ABILITA_MAPPED_STATUSES'])) : '');
            } else if (isset($_POST['action']) && $_POST['action'] == 'update' && !check_admin_referer('abilita_nonce_action', 'abilita_nonce_settings_status')) {
                die('Permission denied...');
            }

            if (get_option('ABILITA_MAPPED_STATUSES') == '') {
                update_option('ABILITA_MAPPED_STATUSES', sanitize_text_field(wp_json_encode(ABILITA_STANDARD_STATUSES_CONFIG)));
            }

            if (isset($_GET['reset']) && $_GET['reset'] == '1' && check_admin_referer('abilita_nonce_action', 'abilita_nonce_settings_status_reset')) {
                update_option('ABILITA_MAPPED_STATUSES', sanitize_text_field(wp_json_encode(ABILITA_STANDARD_STATUSES_CONFIG)));
            } else if (isset($_GET['reset']) && $_GET['reset'] == '1' && !check_admin_referer('abilita_nonce_action', 'abilita_nonce_settings_status_reset')) {
                die('Permission denied...');
            }

            $ABILITA_MAPPED_STATUSES = json_decode(get_option('ABILITA_MAPPED_STATUSES'), true);
        }

        if ($tab == 'settingsApi') {
            if (isset($_POST['action']) && $_POST['action'] == 'update' && check_admin_referer('abilita_nonce_action', 'abilita_nonce_settings_api')) {
                update_option('ABILITA_API_KEY_LIVE'         , isset($_POST['ABILITA_API_KEY_LIVE'])          ? sanitize_text_field(wp_unslash($_POST['ABILITA_API_KEY_LIVE'])) : '');
                update_option('ABILITA_OUTGOING_API_KEY_LIVE', isset($_POST['ABILITA_OUTGOING_API_KEY_LIVE']) ? sanitize_text_field(wp_unslash($_POST['ABILITA_OUTGOING_API_KEY_LIVE'])) : '');
                update_option('ABILITA_ENDPOINT_LIVE'        , isset($_POST['ABILITA_ENDPOINT_LIVE'])         ? sanitize_text_field(wp_unslash($_POST['ABILITA_ENDPOINT_LIVE'])) : '');
                update_option('ABILITA_API_KEY_TEST'         , isset($_POST['ABILITA_API_KEY_TEST'])          ? sanitize_text_field(wp_unslash($_POST['ABILITA_API_KEY_TEST'])) : '');
                update_option('ABILITA_OUTGOING_API_KEY_TEST', isset($_POST['ABILITA_OUTGOING_API_KEY_TEST']) ? sanitize_text_field(wp_unslash($_POST['ABILITA_OUTGOING_API_KEY_TEST'])) : '');
                update_option('ABILITA_ENDPOINT_TEST'        , isset($_POST['ABILITA_ENDPOINT_TEST'])         ? sanitize_text_field(wp_unslash($_POST['ABILITA_ENDPOINT_TEST'])) : '');
                update_option('ABILITA_API_RUNTIME'          , isset($_POST['ABILITA_API_RUNTIME'])           ? sanitize_text_field(wp_unslash($_POST['ABILITA_API_RUNTIME'])) : '');
            } else if (isset($_POST['action']) && $_POST['action'] == 'update' && !check_admin_referer('abilita_nonce_action', 'abilita_nonce_settings_api')) {
                die('Permission denied...');
            }

            $ABILITA_API_KEY_LIVE          = get_option('ABILITA_API_KEY_LIVE');
            $ABILITA_OUTGOING_API_KEY_LIVE = get_option('ABILITA_OUTGOING_API_KEY_LIVE');
            $ABILITA_ENDPOINT_LIVE         = get_option('ABILITA_ENDPOINT_LIVE');
            $ABILITA_API_KEY_TEST          = get_option('ABILITA_API_KEY_TEST');
            $ABILITA_OUTGOING_API_KEY_TEST = get_option('ABILITA_OUTGOING_API_KEY_TEST');
            $ABILITA_ENDPOINT_TEST         = get_option('ABILITA_ENDPOINT_TEST');
            $ABILITA_API_RUNTIME           = get_option('ABILITA_API_RUNTIME');

            if (get_option('ABILITA_API_RUNTIME') == '') {
                $ABILITA_API_RUNTIME = 'TEST';
            } else {
                $ABILITA_API_RUNTIME = get_option('ABILITA_API_RUNTIME');
            }

            $this->abilitaClient = new WC_Abilita_Client_Service();
            [
                $httpStatus,
                $error,
                $response
            ] = $this->abilitaClient->get_transaction_list([]);

            if (isset($response->error_code) && $response->error_code > 0) {
                if (get_option('ABILITA_API_RUNTIME') == 'LIVE') {
                    update_option('ABILITA_API_STATUS_LIVE', 0);
                } else {
                    update_option('ABILITA_API_STATUS_TEST', 0);
                }
                wp_admin_notice(
					sprintf(
						/* translators: %s: Name of used runtime */
                        __('Bitte überprüfen Sie Ihre API-Daten für die %s-Umgebung.', 'abilita-payments-for-woocommerce'),
                        get_option('ABILITA_API_RUNTIME')
					), 
					['type' => 'error']
				);
            } else {
                if (isset($_POST['action']) && $_POST['action'] == 'update') {
                    if (get_option('ABILITA_API_RUNTIME') == 'LIVE') {
                        update_option('ABILITA_API_STATUS_LIVE', 1);
                    } else {
                        update_option('ABILITA_API_STATUS_TEST', 1);
                    }
                }
            }
        }

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"></h1>
        </div>
        <div class="wrap2" style="width:99%">
            <a href="https://abilita.de/payment/zahlungsarten/" target="_blank">
                <img src="<?php echo esc_html(plugin_dir_url(__FILE__)).'../../assets/images/abilita_header_settings.png'; ?>" style="width:100%"/>
            </a>
            <nav class="nav-tab-wrapper">
                <a href="<?php echo esc_html(admin_url('admin.php?page=abilita_settings_page')); ?>" class="nav-tab <?php if($tab === null):?>nav-tab-active<?php endif; ?>"><?php echo esc_html(__( 'Transaktionen', 'abilita-payments-for-woocommerce'));?></a>
                <a href="<?php echo esc_html(admin_url('admin.php?page=abilita_settings_page&tab=settingsStatus')); ?>" aria-current="page" class="nav-tab <?php if($tab === 'settingsStatus'):?>nav-tab-active<?php endif; ?>"><?php echo esc_html(__( 'Bezahlstatus', 'abilita-payments-for-woocommerce'));?></a>
                <a href="<?php echo esc_html(admin_url('admin.php?page=abilita_settings_page&tab=settingsApi')); ?>" aria-current="page" class="nav-tab <?php if($tab === 'settingsApi'):?>nav-tab-active<?php endif; ?>"><?php echo esc_html(__( 'API Zugangsdaten', 'abilita-payments-for-woocommerce'));?></a>
                <a href="<?php echo esc_html(admin_url('admin.php?page=abilita_settings_page&tab=settingsInfo')); ?>" aria-current="page" class="nav-tab <?php if($tab === 'settingsInfo'):?>nav-tab-active<?php endif; ?>"><?php echo esc_html(__( 'Checkliste', 'abilita-payments-for-woocommerce'));?></a>
                <a href="<?php echo esc_html(admin_url('admin.php?page=abilita_settings_page&tab=settingsOther')); ?>" aria-current="page" class="nav-tab <?php if($tab === 'settingsOther'):?>nav-tab-active<?php endif; ?>"><?php echo esc_html(__( 'Sonstiges', 'abilita-payments-for-woocommerce'));?></a>
            </nav>

            <div class="tab-content">
                <?php
                switch($tab) {
                    default:
                        $paymentTransactions = new WC_Abilita_Payment_Transactions(
                            $this->get_mapped_abilita_statuses()
                        );
                        $paymentTransactions->execute();
                        break;
                    case 'settingsInfo':
                        include(plugin_dir_path(__FILE__) . 'partials/SettingsInfo.php');
                        break;
                    case 'settingsOther':
                        $settingOther = new WC_Abilita_Settings_Other(self::ABILITA_SETTINGS_GROUP);
                        $settingOther->get_view();
                        break;
                    case 'settingsApi':
                        $settingApi = new WC_Abilita_Settings_Api(self::ABILITA_SETTINGS_GROUP);
                        $settingApi->get_view();
                        break;
                    case 'settingsStatus':
                        $settingStatus = new WC_Abilita_Settings_Status(
                            self::ABILITA_SETTINGS_GROUP,
                            $ABILITA_MAPPED_STATUSES,
                            $this->get_mapped_abilita_statuses(),
                            $this->get_mapped_woocommerce_statuses()
                        );
                        $settingStatus->get_view();
                        break;
                    case 'log':
                        echo "log";
                        break;
                }
                ?>
            </div>
        </div>
        <style>
            .abilitaBoxLink {
                text-decoration:none;
                float:left;
                margin-bottom:20px;
            }

            .abilitaBoxLink:hover {
                text-decoration:underline;
            }
        </style>
        <?php
    }

    private function get_mapped_abilita_statuses()
    {
        return [
            '1' => [
                'name' => 'started',
                'text' => esc_html(__('Gestartet', 'abilita-payments-for-woocommerce')),
                'info' => wp_kses(__('Bezahlstatus bei einer gestarteten Transaktion.', 'abilita-payments-for-woocommerce'), ABILITA_PAYMENT_ALLOWED_HTML)
            ],
            '2' => [
                'name' => 'pending',
                'text' => esc_html(__('In Wartestellung', 'abilita-payments-for-woocommerce')),
                'info' => wp_kses(__('Bezahlstatus bei einer ausstehenden Transaktion.', 'abilita-payments-for-woocommerce'), ABILITA_PAYMENT_ALLOWED_HTML)
            ],
            '3' => [
                'name' => 'completed',
                'text' => esc_html(__('Abgeschlossen', 'abilita-payments-for-woocommerce')),
                'info' => wp_kses(__('Bezahlstatus bei einer abgeschlossenen Transaktion', 'abilita-payments-for-woocommerce'), ABILITA_PAYMENT_ALLOWED_HTML)
            ],
            '4' => [
                'name' => 'error',
                'text' => esc_html(__('Fehler', 'abilita-payments-for-woocommerce')),
                'info' => wp_kses(__('Bezahlstatus bei einer fehlerhaften Transaktion.<br><i>Bei der Transaktion ist ein Fehler aufgetreten.</i>', 'abilita-payments-for-woocommerce'), ABILITA_PAYMENT_ALLOWED_HTML)
            ],
            '5' => [
                'name' => 'canceled',
                'text' => esc_html(__('Storniert', 'abilita-payments-for-woocommerce')),
                'info' => wp_kses(__('Bezahlstatus bei einer abgebrochenen Transaktion.<br><i>Der Benutzer hat die Zahlung auf der Website des Zahlungsanbieters abgebrochen.</i>', 'abilita-payments-for-woocommerce'), ABILITA_PAYMENT_ALLOWED_HTML)
            ],
            '6' => [
                'name' => 'declined',
                'text' => esc_html(__('Storniert', 'abilita-payments-for-woocommerce')),
                'info' => wp_kses(__('Bezahlstatus bei einer abgelehnten Transaktion.<br><i>Der Zahlungsanbieter hat die Zahlung abgelehnt. Zahlungen, die eine Risikoprüfung erfordern, werden als abgelehnt gekennzeichnet, wenn die Risikoprüfung fehlschlägt.</i>', 'abilita-payments-for-woocommerce'), ABILITA_PAYMENT_ALLOWED_HTML)
            ],
            '7' => [
                'name' => 'refunded',
                'text' => esc_html(__('Rückerstattet', 'abilita-payments-for-woocommerce')),
                'info' => wp_kses(__('Bezahlstatus bei einer erstatteten Transaktion.<br><i>Für eine Transaktion gibt es teilweise oder vollständige Rückerstattungen.</i>', 'abilita-payments-for-woocommerce'), ABILITA_PAYMENT_ALLOWED_HTML)
            ],
            '8' => [
                'name' => 'authorized',
                'text' => esc_html(__('Autorisiert', 'abilita-payments-for-woocommerce')),
                'info' => wp_kses(__('Bezahlstatus bei einer autorisierten Transaktion.<br><i>Die Zahlung wurde erfolgreich autorisiert, aber noch nicht ausgeführt.</i>', 'abilita-payments-for-woocommerce'), ABILITA_PAYMENT_ALLOWED_HTML)
            ],
            '9' => [
                'name' => 'registered',
                'text' => esc_html(__('Zugelassen', 'abilita-payments-for-woocommerce')),
                'info' => wp_kses(__('Bezahlstatus bei einer registrierten Transaktion.<br><i>Für diese Transaktion wurde eine Kreditkarte registriert, aber die Zahlung wurde nicht ausgeführt. Oder es wurde eine erfolgreiche Vorabprüfung durchgeführt, aber die Transaktion ist noch nicht autorisiert.</i>', 'abilita-payments-for-woocommerce'), ABILITA_PAYMENT_ALLOWED_HTML)
            ],
            '10' => [
                'name' => 'debt_collection',
                'text' => esc_html(__('Inkasso', 'abilita-payments-for-woocommerce')),
                'info' => wp_kses(__('Bezahlstatus bei Inkassoverfahren.<br><i>Für diese Transaktion wurde ein Inkassoverfahren eingeleitet.</i>', 'abilita-payments-for-woocommerce'), ABILITA_PAYMENT_ALLOWED_HTML)
            ],
            '11' => [
                'name' => 'debt_paid',
                'text' => esc_html(__('Inkasso bezahlt', 'abilita-payments-for-woocommerce')),
                'info' => wp_kses(__('Bezahlstatus bei bezahltem Inkassoverfahren.<br><i>Für diese Transaktion ist das Inkassoverfahren abgeschlossen. Die Forderung wurde eingezogen und die Transaktion gilt als erfolgreich.</i>', 'abilita-payments-for-woocommerce'), ABILITA_PAYMENT_ALLOWED_HTML)
            ],
            '12' => [
                'name' => 'reversed',
                'text' => esc_html(__('Rückbuchung', 'abilita-payments-for-woocommerce')),
                'info' => wp_kses(__('Bezahlstatus bei einer zurückgezogenen Autorisierung.<br><i>Die Autorisierung wurde rückgängig gemacht (storniert). Die Transaktion gilt als storniert.</i>', 'abilita-payments-for-woocommerce'), ABILITA_PAYMENT_ALLOWED_HTML)
            ],
            '13' => [
                'name' => 'chargeback',
                'text' => esc_html(__('Rückbuchung', 'abilita-payments-for-woocommerce')),
                'info' => wp_kses(__('Bezahlstatus bei einer Rückbuchung.<br><i>Für eine abgeschlossene oder ausstehende Transaktion wurde eine Rückbuchung veranlasst.</i>', 'abilita-payments-for-woocommerce'), ABILITA_PAYMENT_ALLOWED_HTML)
            ],
            '14' => [
                'name' => 'factoring',
                'text' => esc_html(__('Factoring', 'abilita-payments-for-woocommerce')),
                'info' => wp_kses(__('Bezahlstatus bei Kommission.<br><i>Das Geschäft ist in die Kommission eingegangen. Die Transaktion gilt als erfolgreich, weil davon ausgegangen wird, dass der Händler das Geld vom Kommissions-Partner erhalten hat.</i>', 'abilita-payments-for-woocommerce'), ABILITA_PAYMENT_ALLOWED_HTML)
            ],
            '15' => [
                'name' => 'debt_declined',
                'text' => esc_html(__('Schulden abgelehnt', 'abilita-payments-for-woocommerce')),
                'info' => wp_kses(__('Bezahlstatus bei Inkasso abgelehnt.<br><i>Inkasso wurde abgelehnt (z. B. wegen unzureichender Daten).</i>', 'abilita-payments-for-woocommerce'), ABILITA_PAYMENT_ALLOWED_HTML)
            ],
            '16' => [
                'name' => 'Factoring abgelehnt',
                'text' => esc_html(__('Factoring abgelehnt', 'abilita-payments-for-woocommerce')),
                'info' => wp_kses(__('Bezahlstatus bei Kommission abgelehnt.<br><i>Kommission wurde abgelehnt (z. B. wegen unzureichender Risikoprüfung.)</i>', 'abilita-payments-for-woocommerce'), ABILITA_PAYMENT_ALLOWED_HTML)
            ],
        ];
    }

    private function get_mapped_woocommerce_statuses()
    {
        return wc_get_order_statuses();
    }
}

