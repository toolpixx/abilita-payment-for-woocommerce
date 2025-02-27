<?php defined('ABSPATH') || exit; ?>
<form method="post" action="<?php echo esc_html(admin_url('admin.php?page=abilita_settings_page&tab=settingsApi')); ?>">
    <?php wp_nonce_field( 'abilita_nonce_action', 'abilita_nonce_settings_api' ); ?>
    <?php settings_fields($this->abilitaSettingsGroup); ?>
    <?php do_settings_sections($this->abilitaSettingsGroup); ?>

    <table class="form-table" style="background: white;width:100%;border:15px solid #ececec">
        <tr>
            <td colspan="2">
                <h2><?php echo esc_html(__('API Zugangsdaten', 'abilita-payments-for-woocommerce')); ?></h2>
                <p>
                    <?php echo esc_html(__('Bitte geben Sie unten Ihre API-Einstellungen ein. Die API Keys und API Domains finden Sie in Ihrem abilita Dashboard.', 'abilita-payments-for-woocommerce')); ?>
                </p>
            </td>
        </tr>
        <tr>
            <td valign="top" style="width:30%">
                <b><?php echo esc_html(__('API-Umgebung', 'abilita-payments-for-woocommerce')); ?></b>
            </td>
            <td valign="top">
                <select name="ABILITA_API_RUNTIME" id="ABILITA_API_RUNTIME" required="required">
                    <option <?php if ($this->apiRuntime == '') { ?>selected<?php } ?> value=""><?php echo esc_html(__('Bitte wählen', 'abilita-payments-for-woocommerce')); ?></option>
                    <option <?php if ($this->apiRuntime == 'LIVE') { ?>selected<?php } ?> value="LIVE"><?php echo esc_html(__('Produktion', 'abilita-payments-for-woocommerce')); ?></option>
                    <option <?php if ($this->apiRuntime == 'TEST') { ?>selected<?php } ?> value="TEST"><?php echo esc_html(__('Test', 'abilita-payments-for-woocommerce')); ?></option>
                </select>
            </td>
        </tr>
        <tr class="abilitaApiLive">
            <td colspan="2" style="padding:0px 10px 0px 10px"><hr></td>
        </tr>
        <tr class="abilitaApiLive">
            <td valign="top" style="width:30%">
                <b><?php echo esc_html(__('API Key (Produktion)', 'abilita-payments-for-woocommerce')); ?></b>
            </td>
            <td valign="top">
                <input type="text" name="ABILITA_API_KEY_LIVE" id="ABILITA_API_KEY_LIVE" value="<?php echo esc_html($this->apiKeyLive); ?>" size="50"/>
                <br>
                <small><?php echo esc_html(__('Bitte geben Sie hier Ihren abilita API-Key ein.', 'abilita-payments-for-woocommerce')); ?></small>
            </td>
        </tr>
        <tr class="abilitaApiLive">
            <td valign="top" style="width:30%">
                <b><?php echo esc_html(__('Outgoing Key (Produktion)', 'abilita-payments-for-woocommerce')); ?></b>
            </td>
            <td valign="top">
                <input type="text" name="ABILITA_OUTGOING_API_KEY_LIVE" id="ABILITA_OUTGOING_API_KEY_LIVE" value="<?php echo esc_html($this->apiKeyOutgoingLive); ?>" size="50"/>
                <br>
                <small><?php echo esc_html(__('Bitte geben Sie hier den ausgehenden abilita API-Key ein.', 'abilita-payments-for-woocommerce')); ?></small>
            </td>
        </tr>
        <tr class="abilitaApiLive">
            <td valign="top" style="width:30%">
                <b><?php echo esc_html(__('API Domain (Produktion)', 'abilita-payments-for-woocommerce')); ?></b>
            </td>
            <td valign="top">
                <input type="text" name="ABILITA_ENDPOINT_LIVE" id="ABILITA_ENDPOINT_LIVE" value="<?php echo esc_html($this->apiEndpointLive); ?>" size="50"/>
            </td>
        </tr>
        <tr class="abilitaApiTest">
            <td valign="top" style="width:30%">
                <b><?php echo esc_html(__('API Key (Test)', 'abilita-payments-for-woocommerce')); ?></b>
            </td>
            <td valign="top">
                <input type="text" name="ABILITA_API_KEY_TEST" id="ABILITA_API_KEY_TEST" value="<?php echo esc_html($this->apiKeyTest); ?>" size="50"/>
                <br>
                <small><?php echo esc_html(__('Bitte geben Sie hier Ihren abilita API-Key ein.', 'abilita-payments-for-woocommerce')); ?></small>
            </td>
        </tr>
        <tr class="abilitaApiTest">
            <td valign="top" style="width:30%">
                <b><?php echo esc_html(__('Outgoing Key (Test)', 'abilita-payments-for-woocommerce')); ?></b>
            </td>
            <td valign="top">
                <input type="text" name="ABILITA_OUTGOING_API_KEY_TEST" id="ABILITA_OUTGOING_API_KEY_TEST" value="<?php echo esc_html($this->apiKeyOutgoingTest); ?>" size="50"/>
                <br>
                <small><?php echo esc_html(__('Bitte geben Sie hier den ausgehenden abilita API-Key ein.', 'abilita-payments-for-woocommerce')); ?></small>
            </td>
        </tr>
        <tr class="abilitaApiTest">
            <td valign="top" style="width:30%">
                <b><?php echo esc_html(__('API Domain (Test)', 'abilita-payments-for-woocommerce')); ?></b>
            </td>
            <td valign="top">
                <input type="text" name="ABILITA_ENDPOINT_TEST" id="ABILITA_ENDPOINT_TEST" value="<?php echo esc_html($this->apiEndpointTest); ?>" size="50"/>
            </td>
        </tr>
    </table>

    <div style="float:right">
        <?php submit_button(); ?>
    </div>
</form>
<script>
    jQuery(document).ready(function() {

        var ABILITA_API_RUNTIME = '<?php echo esc_html($this->apiRuntime); ?>';
        change_abilita_runtime(ABILITA_API_RUNTIME);

        jQuery('#ABILITA_API_RUNTIME').change(function() {
            change_abilita_runtime(this.value);
        });

        function change_abilita_runtime(runtime)
        {
            if (runtime == 'LIVE') {
                jQuery('.abilitaApiLive').show();
                jQuery('.abilitaApiTest').hide();

                jQuery('#ABILITA_API_KEY_LIVE, #ABILITA_OUTGOING_API_KEY_LIVE, #ABILITA_ENDPOINT_LIVE').prop('required', true);
                jQuery('#ABILITA_API_KEY_TEST, #ABILITA_OUTGOING_API_KEY_TEST, #ABILITA_ENDPOINT_TEST').prop('required', false);
            } else if (runtime == 'TEST') {
                jQuery('.abilitaApiTest').show();
                jQuery('.abilitaApiLive').hide();

                jQuery('#ABILITA_API_KEY_TEST, #ABILITA_OUTGOING_API_KEY_TEST, #ABILITA_ENDPOINT_TEST').prop('required', true);
                jQuery('#ABILITA_API_KEY_LIVE, #ABILITA_OUTGOING_API_KEY_LIVE, #ABILITA_ENDPOINT_LIVE').prop('required', false);
            } else {
                jQuery('.abilitaApiTest, .abilitaApiLive').hide();
            }
        }

    });
</script>
<style>
    small.info {
        color:gray;
        font-style: italic;
    }
</style>