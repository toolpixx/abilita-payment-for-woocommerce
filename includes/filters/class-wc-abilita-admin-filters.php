<?php declare(strict_types=1);

namespace abilita\payment\filters;

defined('ABSPATH') || exit;

class WC_Abilita_Admin_Filters {

    public function __construct()
    {
        add_filter('plugin_action_links_abilita-payments-for-woocommerce/bootstrap.php', [$this, 'abilita_action_links']);
    }

    public function abilita_action_links($pluginLinks)
    {
        $pluginInfo = get_plugin_data(__FILE__);

        $httpHost = null;
        if (isset($_SERVER['HTTP_HOST'])) {
            $httpHost = sanitize_text_field(
                wp_unslash($_SERVER['HTTP_HOST'])
            );
        }

        return array_merge([
            '<a href="'.esc_html(admin_url('admin.php?page=wc-settings&tab=checkout')).'">Zahlungsarten</a>',
            '<a href="'.esc_html(admin_url('admin.php?page=abilita_settings_page&tab=settingsApi')).'">Einstellungen</a>',
            '<a href="https://abilita.de/payment-prozess/" target="_blank">Konditionen vereinbaren</a>',
            '<a href="mailto:payment@abilita.de?subject=Hilfe zu '.$pluginInfo['Name'].' Version: '.$pluginInfo['Version'].' installiert auf '.$httpHost.'" target="_top">Kunden-Support</a>',
        ], $pluginLinks);
    }
}