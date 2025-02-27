<?php defined('ABSPATH') || exit; ?>
<div class="wrap" style="margin: 0px;">
    <table style="width:100%;border:15px solid #ececec;background: #ececec;">
        <tr>
            <td>
                <div class="tablenav top">
                    <form method="post" id="transactionForm">
                        <?php wp_nonce_field( 'abilita_nonce_action', 'abilita_nonce_settings_transactions' ); ?>
                        <input type="hidden" name="search" value="1">
                        <?php
                        foreach ($filterOptions as $name => $details) {
                            echo '<select name="' . esc_html($name) . '" id="' . esc_html($name) . '" style="margin-bottom: 3px;">';
                            foreach ($details['options'] as $value => $label) {
                                $isSelected = $value == $details['selected'] ? 'selected' : '';
                                echo '<option value="' . esc_html($value) . '"' . esc_html($isSelected) . '>' . esc_html($label) . '</option>';
                            }
                            echo '</select>';
                        }
                        ?>
                        <input type="date" name="dateFrom" placeholder="Suchbegriff" value="<?php echo esc_html($dateFrom); ?>">
                        <span>
                            <?php echo esc_html(__('bis', 'abilita-payments-for-woocommerce')); ?>
                        </span>
                        <input type="date" name="dateTo" placeholder="Suchbegriff" value="<?php echo esc_html($dateTo); ?>">
                        <input type="text" name="query" placeholder="Suchbegriff" value="<?php echo esc_html($query); ?>">
                        <input type="submit" value="Suche" class="button action">
                        <?php if (!$transactions) { ?>
                            <span style="display:inline-block;padding-left:10px;padding-right:10px;background-color:#fce4e4;color:#cc0033;border-radius:4px;border: 1px solid #fcc2c3;height:28px;">
                                <?php echo esc_html(__('Ihre Suche lieferte keine Ergebnisse', 'abilita-payments-for-woocommerce')); ?>
                            </span>
                        <?php } ?>
                    </form>
                </div>
                <?php if ($transactions) { ?>
                    <table class="wp-list-table widefat fixed striped table-view-list">
                        <thead>
                        <tr>
                            <th scope="col" class="manage-column"><?php echo esc_html(__('Bestellnummer', 'abilita-payments-for-woocommerce')); ?></th>
                            <th scope="col" class="manage-column"><?php echo esc_html(__('Status', 'abilita-payments-for-woocommerce')); ?></th>
                            <th scope="col" class="manage-column"><?php echo esc_html(__('Methode', 'abilita-payments-for-woocommerce')); ?></th>
                            <th scope="col" class="manage-column" style="text-align: right;width:8%"><?php echo esc_html(__('Gesamtsumme', 'abilita-payments-for-woocommerce')); ?></th>
                            <th scope="col" class="manage-column" style="text-align: right;width:8%"><?php echo esc_html(__('Zurückerstattet', 'abilita-payments-for-woocommerce')); ?></th>
                            <th scope="col" class="manage-column" style="text-align: right;width:13%"><?php echo esc_html(__('Datum', 'abilita-payments-for-woocommerce')); ?></th>
                            <th scope="col" class="manage-column" style="text-align: right;width:10%"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($transactions as $transaction) { ?>
                            <tr>
                                <td><?php echo esc_html($transaction->order_id); ?></td>
                                <td style="text-align: center">
                                        <span
                                            <?php if (in_array($transaction->status, ['reversed', 'declined'])) { ?>
                                                class="red"
                                            <?php } else if (in_array($transaction->status, ['completed', 'refunded'])) { ?>
                                                class="green"
                                            <?php } else { ?>
                                                class="orange"
                                            <?php } ?>>
                                            <?php echo esc_html($abilitaPaymentStatuses[$transaction->status_code]).' [Code: '.esc_html($transaction->status_code).']'; ?>
                                        </span>
                                </td>
                                <td><?php echo esc_html($abilitaPaymentNames[$transaction->payment_method]); ?></td>
                                <td style="text-align: right">
                                    <?php echo esc_html(number_format($transaction->amount, 2, ',', '.')); ?> <?php echo esc_html($transaction->currency); ?>
                                </td>
                                <td style="text-align: right">
                                    <?php if ($transaction->refunded_amount > 0) { ?>
                                        -<?php echo esc_html(number_format($transaction->refunded_amount, 2, ',', '.')); ?> <?php echo esc_html($transaction->currency); ?>
                                    <?php } else { ?>
                                        --
                                    <?php } ?>
                                </td>
                                <td style="text-align: right"><?php echo esc_html(gmdate('d.m.Y', strtotime($transaction->created_at))) ?> - <?php echo esc_html(gmdate('H:i', strtotime($transaction->created_at))) ?> Uhr</td>
                                <td style="text-align: right">
                                    <?php
                                    if (
                                        !in_array($transaction->status, ['reversed', 'declined']) && $transaction->amount > $transaction->refunded_amount &&
                                        in_array($transaction->payment_method, ABILITA_PAYMENT_CAN_REFUNDED)
                                    ) {
                                        ?>
                                        <select class="paymentAction">
                                            <option value=""><?php echo esc_html(__('Aktion', 'abilita-payments-for-woocommerce')); ?></option>
                                            <?php if (!in_array($transaction->status, ['completed'])) { ?>
                                                <?php if (in_array($transaction->payment_method, ABILITA_PAYMENT_CAN_CANCELLED_OR_REAUTHORIZE)) { ?>
                                                    <option
                                                        value="reauthorize"
                                                        data-order-id="<?php echo esc_html($transaction->order_id); ?>"
                                                        data-amount="<?php echo esc_html(number_format($transaction->amount, 2, ',', '.')); ?>"
                                                        data-transaction-id="<?php echo esc_html($transaction->transaction_id); ?>"
                                                        data-payment-type="<?php echo esc_html($transaction->payment_method); ?>"
                                                        data-payment-type-translated="<?php echo esc_html($abilitaPaymentNames[$transaction->payment_method]); ?>"
                                                    ><?php echo esc_html(__('Erneut autorisieren', 'abilita-payments-for-woocommerce')); ?></option>
                                                    <option
                                                        value="cancel"
                                                        data-order-id="<?php echo esc_html($transaction->order_id); ?>"
                                                        data-amount="<?php echo esc_html(number_format($transaction->amount, 2, ',', '.')); ?>"
                                                        data-transaction-id="<?php echo esc_html($transaction->transaction_id); ?>"
                                                        data-payment-type="<?php echo esc_html($transaction->payment_method); ?>"
                                                        data-payment-type-translated="<?php echo esc_html($abilitaPaymentNames[$transaction->payment_method]); ?>"
                                                    ><?php echo esc_html(__('Stornieren', 'abilita-payments-for-woocommerce')); ?></option>
                                                <?php } ?>
                                            <?php } ?>
                                            <option
                                                value="refund"
                                                data-order-id="<?php echo esc_html($transaction->order_id); ?>"
                                                data-amount="<?php echo esc_html(number_format($transaction->amount, 2, ',', '.')); ?>"
                                                data-transaction-id="<?php echo esc_html($transaction->transaction_id); ?>"
                                                data-payment-type="<?php echo esc_html($transaction->payment_method); ?>"
                                                data-payment-type-translated="<?php echo esc_html($abilitaPaymentNames[$transaction->payment_method]); ?>"
                                                >
                                                <?php echo esc_html(__('Rückerstattung', 'abilita-payments-for-woocommerce')); ?>
                                            </option>
                                        </select>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            </td>
        </tr>
    </table>
</div>