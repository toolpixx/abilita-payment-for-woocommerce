<?php declare(strict_types=1);

namespace abilita\payment\admin;

defined('ABSPATH') || exit;

class WC_Abilita_Settings_Api {

    private $abilitaSettingsGroup;
    private $apiKeyLive;
    private $apiKeyOutgoingLive;
    private $apiEndpointLive;
    private $apiKeyTest;
    private $apiKeyOutgoingTest;
    private $apiEndpointTest;
    private $apiRuntime;

    public function __construct($abilitaSettingsGroup)
    {
        $this->abilitaSettingsGroup = $abilitaSettingsGroup;
        $this->apiKeyLive           = get_option('ABILITA_API_KEY_LIVE');
        $this->apiKeyOutgoingLive   = get_option('ABILITA_OUTGOING_API_KEY_LIVE');
        $this->apiEndpointLive      = get_option('ABILITA_ENDPOINT_LIVE');
        $this->apiKeyTest           = get_option('ABILITA_API_KEY_TEST');
        $this->apiKeyOutgoingTest   = get_option('ABILITA_OUTGOING_API_KEY_TEST');
        $this->apiEndpointTest      = get_option('ABILITA_ENDPOINT_TEST');
        $this->apiRuntime           = get_option('ABILITA_API_RUNTIME');
    }

    public function get_view()
    {
        require_once('partials/SettingsApi.php');
    }
}
