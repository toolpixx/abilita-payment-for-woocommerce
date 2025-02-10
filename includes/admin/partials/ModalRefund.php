<dialog id="modalRefund">
    <div class="modalHeader"><?php echo esc_html(__('Transaktion Rückerstatten', 'abilita-payments-for-woocommerce')); ?></div>
    <div class="modalHeaderError" style="display:none"></div>
    <div class="modalHeaderSuccess" style="display:none"><?php echo esc_html(__('Die Transaktion wurde Rückerstattet.', 'abilita-payments-for-woocommerce')); ?></div>
    <p class="modalInfo">
        <?php echo esc_html(__('Bitte beachten: Sie können Teilbeträge oder auch volle Beträge zurückerstatten. Wenn Sie keine das Feld "Betrag" leer lassen, wird der voll Betrag rückerstattet. Der Betrag der Rückerstattung sollte jedoch den Gesamtbetrag nicht überschreiten.', 'abilita-payments-for-woocommerce')); ?>
    </p>
    <form method="dialog" class="modalContent">
        <input type="hidden" name="refundTransactionId" id="refundTransactionId">
        <input type="hidden" name="refundPaymentType" id="refundPaymentType">
        <label class="modalLabel"><?php echo esc_html(__('Bestellnummer', 'abilita-payments-for-woocommerce')); ?></label>
        <input type="text" value="" name="refundOrderId" id="refundOrderId" disabled>
        <br><br>
        <label class="modalLabel"><?php echo esc_html(__('Zahlungsart', 'abilita-payments-for-woocommerce')); ?></label>
        <input type="text" value="" name="refundPaymentTypeTranslated" id="refundPaymentTypeTranslated" disabled>
        <br><br>
        <label class="modalLabel"><?php echo esc_html(__('Gesamtsumme', 'abilita-payments-for-woocommerce')); ?></label>
        <input type="text" value="" name="refundAmount" id="refundAmount" disabled style="width:30%">
        <br><br>
        <label class="modalLabel"><?php echo esc_html(__('Betrag', 'abilita-payments-for-woocommerce')); ?></label>
        <input type="text" value="" name="refundAmountNew" id="refundAmountNew" style="width:30%">
        <br><br>
        <label class="modalLabel"><?php echo esc_html(__('Kommentar', 'abilita-payments-for-woocommerce')); ?></label>
        <input type="text" value="" name="refundComment" id="refundComment" style="width:66%">
        <div class="modalFooter">
            <button class="button-secondary modalButtonCancel"><?php echo esc_html(__('Schließen', 'abilita-payments-for-woocommerce')); ?></button>
            <button type="button" class="button-secondary modalButtonSubmit" id="modalRefundSubmit"><?php echo esc_html(__('Übernehmen', 'abilita-payments-for-woocommerce')); ?></button>
        </div>
    </form>
</dialog>