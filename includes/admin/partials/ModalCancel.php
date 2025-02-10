<dialog id="modalCancel">
    <div class="modalHeader"><?php echo esc_html(__('Transaktion stornieren', 'abilita-payments-for-woocommerce')); ?></div>
    <div class="modalHeaderError" style="display:none"></div>
    <div class="modalHeaderSuccess" style="display:none"><?php echo esc_html(__('Die Transaktion wurde storniert.', 'abilita-payments-for-woocommerce')); ?></div>
    <form method="dialog" class="modalContent">
        <input type="hidden" name="cancelTransactionId" id="cancelTransactionId">
        <input type="hidden" name="cancelPaymentType" id="cancelPaymentType">
        <label class="modalLabel"><?php echo esc_html(__('Bestellnummer', 'abilita-payments-for-woocommerce')); ?></label>
        <input type="text" value="" name="cancelOrderId" id="cancelOrderId" disabled>
        <br><br>
        <label class="modalLabel"><?php echo esc_html(__('Zahlungsart', 'abilita-payments-for-woocommerce')); ?></label>
        <input type="text" value="" name="cancelPaymentTypeTranslated" id="cancelPaymentTypeTranslated" disabled>
        <br><br>
        <label class="modalLabel"><?php echo esc_html(__('Gesamtsumme', 'abilita-payments-for-woocommerce')); ?></label>
        <input type="text" value="" name="cancelAmount" id="cancelAmount" disabled style="width:30%">
        <br><br>
        <label class="modalLabel"><?php echo esc_html(__('Kommentar', 'abilita-payments-for-woocommerce')); ?></label>
        <input type="text" value="" name="cancelComment" id="cancelComment" style="width:66%">
        <div class="modalFooter">
            <button class="button-secondary modalButtonCancel"><?php echo esc_html(__('Schließen', 'abilita-payments-for-woocommerce')); ?></button>
            <button type="button" class="button-secondary modalButtonSubmit" id="modalCancelSubmit"><?php echo esc_html(__('Übernehmen', 'abilita-payments-for-woocommerce')); ?></button>
        </div>
    </form>
</dialog>