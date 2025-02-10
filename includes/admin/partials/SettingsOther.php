<form method="post" action="<?php echo esc_html(admin_url('admin.php?page=abilita_settings_page&tab=settingsOther')); ?>">
    <?php wp_nonce_field( 'abilita_nonce_action', 'abilita_nonce_settings_other' ); ?>
    <?php settings_fields($this->abilitaSettingsGroup); ?>
    <?php do_settings_sections($this->abilitaSettingsGroup); ?>

    <table class="form-table" style="margin-bottom:20px;background: white;width:100%;border:15px solid #ececec">
        <tr>
            <td colspan="2">
                <h2><?php echo esc_html(__('Checkout-Formularfeld (Anrede)', 'abilita-payments-for-woocommerce')); ?></h2>
                <p>
                    <?php echo esc_html(__('Hier können Sie die Formularfelder für die "Anrede" Bestellprozess definieren und aktivieren. Einige Zahlungsarten setzen eine Anrede voraus, so dass eine der beiden Optionen aktiviert oder hinterlegt sein muss.', 'abilita-payments-for-woocommerce')); ?>
                </p>
            </td>
        </tr>
        <tr>
            <td valign="top" style="width:30%">
                <b><?php echo esc_html(__('Anrede', 'abilita-payments-for-woocommerce')); ?></b>
                <br>
                <small class="info">
                    <?php echo esc_html(__('Sie können dieses Feld aktivieren, falls Sie es noch nicht auf andere Weise konfiguriert haben.', 'abilita-payments-for-woocommerce')); ?>
                </small>
            </td>
            <td valign="top">
                <input type="checkbox" name="ABILITA_FORM_FIELD_SALUTATION" value="1" <?php if ($this->formFieldSalutation == '1') { ?>checked="checked"<?php } ?>/> <b>Feldname:</b> billing_title
            </td>
        </tr>
        <tr>
            <td valign="top" style="width:30%">
                <b><?php echo esc_html(__('Eigenes Feld (Mapping)', 'abilita-payments-for-woocommerce')); ?></b>
                <br>
                <small class="info">
                    <?php echo esc_html(__('Wenn Sie bereits ein eigenes Feld für die Anrede definiert und aktiviert haben, so geben Sie hier an wie das Feld heisst. z.B. "billing_salutation".', 'abilita-payments-for-woocommerce')); ?>
                </small>
            </td>
            <td valign="top">
                <input type="text" name="ABILITA_FORM_FIELD_OWN_SALUTATION_NAME" value="<?php echo esc_html($this->formFieldOwnSalutationName); ?>" size="40" maxlength="50" placeholder="z.B. billing_salutation"/>
            </td>
        </tr>
    </table>

    <table class="form-table" style="margin-bottom:20px;background: white;width:100%;border:15px solid #ececec">
        <tr>
            <td colspan="2">
                <h2><?php echo esc_html(__('Checkout-Formularfeld (Firma)', 'abilita-payments-for-woocommerce')); ?></h2>
                <p>
                    <?php echo esc_html(__('Sie können hier mit den Platzhalter-Text von WooCommerce mit Ihrem eigenen Text überschreiben.', 'abilita-payments-for-woocommerce')); ?>
                </p>
            </td>
        </tr>
        <tr>
            <td valign="top" style="width:30%">
                <b><?php echo esc_html(__('Platzhalter-Text / Firma', 'abilita-payments-for-woocommerce')); ?></b>
                <br>
                <small class="info">
                    <?php echo esc_html(__('Wenn Sie hier einen Text hinterlegen, wird der Platzhalter-Text für das Feld „Firma“ im Bestellformular durch Ihren eigenen Text überschrieben..', 'abilita-payments-for-woocommerce')); ?>
                </small>
            </td>
            <td valign="top">
                <input type="text" name="ABILITA_FORM_FIELD_COMPANY_LABEL_TEXT" value="<?php echo esc_html($this->formFieldCompanyLabelText); ?>" size="100" maxlength="100"/>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding:0px 10px 0px 10px"><hr></td>
        </tr>
        <tr>
            <td valign="top" style="width:30%">
                <b><?php echo esc_html(__('Umsatzsteuer-ID', 'abilita-payments-for-woocommerce')); ?></b>
                <br>
                <small class="info">
                    <?php echo esc_html(__('Mit dieser Option haben Sie die Möglichkeit, dass Kunden eine Umsatzsteuer-ID angeben können.', 'abilita-payments-for-woocommerce')); ?>
                </small>
            </td>
            <td valign="top">
                <input type="checkbox" name="ABILITA_FORM_FIELD_VAT_ID" value="1" <?php if ($this->formFieldCompanyVatId == '1') { ?>checked="checked"<?php } ?>/>
            </td>
        </tr>
        <tr>
            <td valign="top" style="width:30%">
                <b><?php echo esc_html(__('Platzhalter-Text / Umsatzsteuer-ID', 'abilita-payments-for-woocommerce')); ?></b>
                <br>
                <small class="info">
                    <?php echo esc_html(__('Wenn Sie hier einen Text hinterlegen, wird der Platzhalter-Text für das Feld „Umsatzsteuer-ID“ im Bestellformular durch Ihren eigenen Text überschrieben.', 'abilita-payments-for-woocommerce')); ?>
                </small>
            </td>
            <td valign="top">
                <input type="text" name="ABILITA_FORM_FIELD_VAT_ID_LABEL_TEXT" value="<?php echo esc_html($this->formFieldCompanyVatIdLabelText); ?>" size="100" maxlength="100"/>
            </td>
        </tr>
        <tr>
            <td valign="top" style="width:30%">
                <b><?php echo esc_html(__('Eigenes Feld (Mapping)', 'abilita-payments-for-woocommerce')); ?></b>
                <br>
                <small class="info">
                    <?php echo esc_html(__('Wenn Sie bereits ein eigenes Feld für die Umsatzsteuer-ID definiert und aktiviert haben, so geben Sie hier an wie das Feld heisst. z.B. "billing_vat_id".', 'abilita-payments-for-woocommerce')); ?>
                </small>
            </td>
            <td valign="top">
                <input type="text" name="ABILITA_FORM_FIELD_OWN_VAT_ID_NAME" value="<?php echo esc_html($this->formFieldOwnVatIdName); ?>" size="40" maxlength="50" placeholder="z.B. billing_vat_id"/>
            </td>
        </tr>
    </table>

    <table class="form-table" style="margin-bottom:20px;background: white;width:100%;border:15px solid #ececec">
        <tr>
            <td valign="top" style="width:30%">
                <h2><?php echo esc_html(__('Bestellnummer/Auftragsnummer-Präfix', 'abilita-payments-for-woocommerce')); ?></h2>
                <p>
                    <?php echo esc_html(__('Wenn Sie mit einer Bestellnummer/Auftragsnummer-Präfix arbeiten, geben Sie diesen Präfix bitte hier ein.', 'abilita-payments-for-woocommerce')); ?>
                </p>
            </td>
            <td valign="top">
                <input type="text" name="ABILITA_ORDERNUMBER_PREFIX" value="<?php echo esc_html($this->ordernumberPrefix); ?>" size="40" maxlength="50" placeholder=""/>
            </td>
        </tr>
    </table>

    <table class="form-table" style="margin-bottom:20px;background: white;width:100%;border:15px solid #ececec">
        <tr>
            <td colspan="2">
                <h2><?php echo esc_html(__('Logger', 'abilita-payments-for-woocommerce')); ?></h2>
                <p>
                    <?php echo esc_html(__('Die folgenden Optionen werden zur Fehlersuche genutzt. Bitte lassen Sie diese Optionen deaktiviert. Nur der Plugin-Support sollte diese Optionen aktivieren.', 'abilita-payments-for-woocommerce')); ?>
                </p>
            </td>
        </tr>
        <tr>
            <td valign="top" style="width:30%">
                <b><?php echo esc_html(__('Logger', 'abilita-payments-for-woocommerce')); ?></b>
                <br>
                <small class="info">
                    <?php echo esc_html(__('Mit dieser Option wird die komplette Kommunikation welche über die API läuft mit geschrieben.', 'abilita-payments-for-woocommerce')); ?>
                </small>
            </td>
            <td valign="top">
                <input type="checkbox" name="ABILITA_PAYMENT_DEBUG_LOGGER" value="1" <?php if ($this->paymentDebugLogger == '1') { ?>checked="checked"<?php } ?>/>
            </td>
        </tr>
    </table>

    <table class="form-table" style="background: white;width:100%;border:15px solid #ececec">
        <tr>
            <td>
                <h2><?php echo esc_html(__('CSS für HTML-SELECT-Felder des Geburtsdatums.', 'abilita-payments-for-woocommerce')); ?></h2>
                <p>
                    <?php echo esc_html(__('Definieren Sie hier das CSS für das Erscheinungsbild der HTML-SELECT-Felder, zur Angabe des Geburtsdatums des Kunden auf der Bestellübersicht. Wird angezeigt, sobald ein Kunde eine Zahlungsart auswählt bei der ein Geburtsdatum verpflichtend ist.', 'abilita-payments-for-woocommerce')); ?>
                </p>
            </td>
        </tr>
        <tr>
            <td>
                <textarea name="ABILITA_CSS_BIRTHDAY" style="width: 100%;height:240px;"><?php echo esc_attr($this->cssBirthday); ?></textarea>
            </td>
        </tr>
    </table>

    <div style="float:right">
        <?php submit_button(); ?>
    </div>
</form>
<style>
    small.info {
        color:gray;
        font-style: italic;
    }
</style>