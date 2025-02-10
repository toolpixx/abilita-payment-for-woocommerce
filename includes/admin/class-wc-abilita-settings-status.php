<?php declare(strict_types=1);

namespace abilita\payment\admin;

defined('ABSPATH') || exit;

class WC_Abilita_Settings_Status {

    private $abilitaSettingsGroup;
    private $mappedSavedStatuses;
    private $mappedAbilitaStatuses;
    private $mappedWoocommerceStatuses;

    public function __construct(
        $abilitaSettingsGroup,
        $mappedSavedStatuses,
        $mappedAbilitaStatuses,
        $mappedWoocommerceStatuses
    ) {
        $this->abilitaSettingsGroup      = $abilitaSettingsGroup;
        $this->mappedSavedStatuses       = $mappedSavedStatuses;
        $this->mappedAbilitaStatuses     = $mappedAbilitaStatuses;
        $this->mappedWoocommerceStatuses = $mappedWoocommerceStatuses;
    }

    public function get_view()
    {
        require_once('partials/SettingsStatus.php');
    }
}
