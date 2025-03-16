<?php

namespace abilita\payment;

defined('ABSPATH') || exit;

class WC_Abilita_Plugin_Updater
{
    private $downloadServer = 'https://downloads.symfonian.de';
    private $plugin_name;
    private $version;
    private $slug;

    public function __construct()
    {
        if (defined('WC_ABILITA_PAYMENT_VERSION')) {
            $this->version = WC_ABILITA_PAYMENT_VERSION;
        } else {
            $this->version = '1.0.0';
        }

        $this->slug = 'abilita-payments-for-woocommerce/bootstrap.php';
        $this->plugin_name = WC_ABILITA_PAYMENT_NAME;

        add_filter('pre_set_site_transient_update_plugins'             , [$this, 'abilitapay_pre_set_site_transient_update_plugins']);
        add_filter('plugins_api'                                       , [$this, 'abilitapay_plugins_api'], 10, 3);
        add_action('after_plugin_row_abilita-payments-for-woocommerce', [$this, 'apilitapay_custom_plugin_update_message'], 10, 2);
    }

    public function apilitapay_custom_plugin_update_message($plugin_file, $plugin_data)
    {
        $update_info = $this->abilitapay_fetch_plugin_update_info();
        if ($update_info && version_compare($this->version, $update_info['new_version'], '<')) {
            $new_version = esc_html($update_info['new_version']);
            $download_url = esc_url($update_info['download_url']);

            echo '<tr class="plugin-update-tr">
                <td colspan="3" class="plugin-update colspanchange">
                    <div class="update-message notice inline notice-warning notice-alt">
                        <p>1Eine neue Version (' . $new_version . ') ist verfügbar. 
                        <a href="' . $download_url . '">Jetzt herunterladen</a>.</p>
                    </div>
                </td>
              </tr>';
        }
    }

    public function abilitapay_pre_set_site_transient_update_plugins($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $update_info = $this->abilitapay_fetch_plugin_update_info();

        if ($update_info && version_compare($this->version, $update_info['new_version'], '<')) {

            $plugin_slug = $this->slug;
            $transient->response[$plugin_slug] = (object) [
                'slug'        => $plugin_slug,
                'new_version' => $update_info['new_version'],
                'package'     => $update_info['download_url'],
                'url'         => $update_info['homepage']
            ];
        }

        return $transient;
    }

    public function abilitapay_plugins_api($result, $action, $args)
    {
        if ($action !== 'plugin_information' || $args->slug !== $this->slug) {
            return $result;
        }

        $update_info = $this->abilitapay_fetch_plugin_update_info();
        if ($update_info && version_compare($this->version, $update_info['new_version'], '<')) {
            $plugin_info = [
                'name'          => $this->plugin_name,
                'slug'          => $this->slug,
                'version'       => $update_info['new_version'],
                'author'        => $this->plugin_name,
                'homepage'      => $update_info['homepage'],
                'sections'      => [
                    'changelog'   => $update_info['change_log'],
                ],
                'download_link' => $update_info['download_url']
            ];

            return (object) $plugin_info;
        }
    }

    private function abilitapay_fetch_plugin_update_info() {
        $response = wp_remote_get($this->downloadServer.'/woocommerce/abilita-oayments-for-woocommerce');

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!isset($data['new_version']) || !isset($data['download_url'])) {
            return false;
        }

        return $data;
    }
}