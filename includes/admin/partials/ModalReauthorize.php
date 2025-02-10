<dialog id="modalReauthorize">
    <div class="modalHeader"><?php echo esc_html(__('Transaktion erneut autorisieren', 'abilita-payments-for-woocommerce')); ?></div>
    <div class="modalHeaderError" style="display:none"></div>
    <div class="modalHeaderSuccess" style="display:none"><?php echo esc_html(__('Die Transaktion wurde erneut autorisiert.', 'abilita-payments-for-woocommerce')); ?></div>
    <form method="dialog" class="modalContent">
        <input type="hidden" name="authorizationTransactionId" id="authorizationTransactionId">
        <input type="hidden" name="authorizationPaymentType" id="authorizationPaymentType">
        <label class="modalLabel"><?php echo esc_html(__('Bestellnummer', 'abilita-payments-for-woocommerce')); ?></label>
        <input type="text" value="" name="authorizationOrderId" id="authorizationOrderId" disabled>
        <br><br>
        <label class="modalLabel"><?php echo esc_html(__('Zahlungsart', 'abilita-payments-for-woocommerce')); ?></label>
        <input type="text" value="" name="authorizationPaymentTypeTranslated" id="authorizationPaymentTypeTranslated" disabled>
        <br><br>
        <label class="modalLabel"><?php echo esc_html(__('Gesamtsumme', 'abilita-payments-for-woocommerce')); ?></label>
        <input type="text" value="" name="authorizationAmount" id="authorizationAmount" disabled style="width:30%">
        <br><br>
        <label class="modalLabel"><?php echo esc_html(__('Neuer Betrag', 'abilita-payments-for-woocommerce')); ?></label>
        <input type="text" value="" name="authorizationAmountNew" id="authorizationAmountNew" style="width:30%">
        <br><br>
        <label class="modalLabel"><?php echo esc_html(__('Kommentar', 'abilita-payments-for-woocommerce')); ?></label>
        <input type="text" value="" name="authorizationComment" id="authorizationComment" style="width:66%">
        <div class="modalFooter">
            <button class="button-secondary modalButtonCancel"><?php echo esc_html(__('Schließen', 'abilita-payments-for-woocommerce')); ?></button>
            <button type="button" class="button-secondary modalButtonSubmit" id="modalReauthorizeSubmit"><?php echo esc_html(__('Übernehmen', 'abilita-payments-for-woocommerce')); ?></button>
        </div>
    </form>
</dialog>