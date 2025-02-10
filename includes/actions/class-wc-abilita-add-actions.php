<?php declare(strict_types=1);

namespace abilita\payment\actions;

defined('ABSPATH') || exit;

class WC_Abilita_Add_Actions {

    public function __construct()
    {
        add_action('woocommerce_checkout_update_user_meta', [$this, 'abilita_woocommerce_checkout_update_user_meta']);
        add_action('edit_user_profile'                     , [$this, 'abilita_show_user_profile']);
        add_action('show_user_profile'                     , [$this, 'abilita_show_user_profile']);
    }

    public function abilita_woocommerce_checkout_update_user_meta($customer_id)
    {
        update_user_meta($customer_id, 'billing_birthday', '1971-03-20');
    }

    public function abilita_show_user_profile($user)
    {
        $billingBirthday = get_user_meta($user->ID, 'billing_birthday')[0] ?? '';
        ?>
        <h3><img src="<?php echo esc_html(plugins_url('../../assets/images/abilita.png', __FILE__)); ?>"> <?php esc_html(__('abilita PAY- Daten', 'abilita-payments-for-woocommerce')); ?></h3>
        <table class="form-table">
            <tr>
                <th>
                    <label><?php esc_html(__('Geburtsdatum', 'abilita-payments-for-woocommerce')); ?></label>
                </th>
                <td>
                    <input type="date" name="billing_birthday" id="billing_birthday"  value="<?php echo esc_html($billingBirthday); ?>"/><br />
                </td>
            </tr>
        </table>
        <?php
    }
}