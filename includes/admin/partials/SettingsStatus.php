<?php defined('ABSPATH') || exit; ?>
<form method="post" action="<?php echo esc_html(admin_url('admin.php?page=abilita_settings_page&tab=settingsStatus')); ?>">
    <?php wp_nonce_field( 'abilita_nonce_action', 'abilita_nonce_settings_status' ); ?>
    <?php settings_fields($this->abilitaSettingsGroup); ?>
    <?php do_settings_sections($this->abilitaSettingsGroup); ?>

    <table class="wp-list-table widefat striped table-view-list" style="margin-bottom:20px;background: white;width:100%;border:15px solid #ececec">
        <tr style="background-color: #fff;">
            <td colspan="4">
                <h2><?php echo esc_html(__('Bezahlstatus an einer Bestellung automatisch setzen', 'abilita-payments-for-woocommerce')); ?></h2>
                <p>
                    <?php echo esc_html(__('Bitte weisen Sie hier Ihre WooCommmerce-Bestellstatus den abilita-Status zu.', 'abilita-payments-for-woocommerce')); ?>
                </p>
                <br>
                <p style="text-align: center;padding-bottom:16px">
                    <span style="background: red;color: white;padding: 10px">
                        <?php echo esc_html(__('Bitte ändern Sie dieses Status-Mapping nicht ohne Rücksprache mit abilita. Die mit der Installation automatisch gesetzten Status garantieren einen reibungslosen Ablauf.', 'abilita-payments-for-woocommerce')); ?>
                    </span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="width:25%;font-size: 16px;border-right:1px solid silver">
                <b><?php echo esc_html(__('WooCommerce', 'abilita-payments-for-woocommerce')); ?></b>
            </td>
            <td colspan="3" style="font-size: 16px;text-align:center">
                <b><?php echo esc_html(__('abilita PAY', 'abilita-payments-for-woocommerce')); ?></b>
            </td>
        </tr>
        <tr>
            <td style="width:25%;border-right:1px solid silver">
            </td>
            <td style="text-align:center">
                <b><?php echo esc_html(__('Code', 'abilita-payments-for-woocommerce')); ?></b>
            </td>
            <td style="text-align:center">
                <b><?php echo esc_html(__('Status', 'abilita-payments-for-woocommerce')); ?></b>
            </td>
            <td>
                <b><?php echo esc_html(__('Beschreibung', 'abilita-payments-for-woocommerce')); ?></b>
            </td>
        </tr>
        <?php foreach ($this->mappedAbilitaStatuses as $key => $status) { ?>
            <tr>
                <td style="vertical-align: top;padding-top:20px;padding-bottom:20px;border-right:1px solid silver">
                    <select name="ABILITA_MAPPED_STATUSES[<?php echo esc_html($this->mappedAbilitaStatuses[$key]['name']); ?>]" style="width:270px">
                        <?php foreach ($this->mappedWoocommerceStatuses as $woocommerceStatusKey => $woocommerceStatusValue) { ?>
                            <option value="<?php echo esc_html($woocommerceStatusKey); ?>" <?php if (isset($this->mappedSavedStatuses[$this->mappedAbilitaStatuses[$key]['name']]) && $this->mappedSavedStatuses[$this->mappedAbilitaStatuses[$key]['name']] == $woocommerceStatusKey) { ?>selected="selected"<?php } ?>>
                                <?php echo esc_html($woocommerceStatusValue); ?> (<?php echo esc_html($woocommerceStatusKey); ?>)
                            </option>
                        <?php } ?>
                    </select>
                </td>
                <td style="vertical-align: top;padding-top:20px;padding-bottom:20px;text-align:center">
                    <b><?php echo esc_html($key); ?></b>
                </td>
                <td style="vertical-align: top;padding-top:20px;padding-bottom:20px;text-align:center">
                    <b><?php echo esc_html($this->mappedAbilitaStatuses[$key]['name']); ?></b>
                </td>
                <td style="vertical-align: top;padding-top:20px;padding-bottom:20px">
                    <?php echo wp_kses($this->mappedAbilitaStatuses[$key]['info'], ABILITA_PAYMENT_ALLOWED_HTML); ?>
                </td>
            </tr>
        <?php } ?>
    </table>

    <div style="float:right">
        <a href="<?php echo esc_html(wp_nonce_url('admin.php?page=abilita_settings_page&tab=settingsStatus&reset=1', 'abilita_nonce_action', 'abilita_nonce_settings_status_reset')); ?>" class="button" style="background: red;border:1px solid red;color: white;">Status-Mapping zurücksetzen</a>
        <input type="submit" name="submit" id="submit" class="button button-primary" value="Änderungen speichern">
    </div>
</form>
<style>
    small.info {
        color:gray;
        font-style: italic;
    }
</style>