<?php defined('ABSPATH') || exit; ?>
<table class="form-table" style="margin-bottom:20px;background: white;width:100%;border:15px solid #ececec">
    <tr>
        <td colspan="2">
            <h2><?php echo esc_html(__('Wichtige Hinweise', 'abilita-payments-for-woocommerce')); ?></h2>
            <p>
                <?php echo esc_html(__('Damit Sie unser Plugin in vollen Funktionsumfang nutzen können, beachten Sie bitte die folgenden Hinweise.', 'abilita-payments-for-woocommerce')); ?>
            </p>
        </td>
    </tr>
</table>
<table class="form-table" style="margin-bottom:20px;background: white;width:100%;border:15px solid #ececec">
    <tr>
        <td valign="top" style="width:10%">
            <b><?php echo esc_html(__('Aktivierung Zahlungsarten', 'abilita-payments-for-woocommerce')); ?></b>
        </td>
        <td valign="top">
            Bevor Sie die abilita PAY Zahlungsarten aktivieren, hinterlegen Sie im Reiter „API Zugangsdaten" erst Ihre API-Schlüssel und API-Endpunkte. Die Zahlungsarten
            werden andernfalls nicht funktionieren bzw. angezeigt. Erst wenn Sie alle Einstellungen vorgenommen haben, werden die Zahlungsarten störungsfrei arbeiten.
            <br><br>
            <a href="<?php echo esc_html(admin_url('admin.php?page=abilita_settings_page&tab=settingsApi')); ?>" target="_blank">Hier gelangen Sie zu den API-Eintstellungen</a>
        </td>
    </tr>
</table>
<table class="form-table" style="margin-bottom:20px;background: white;width:100%;border:15px solid #ececec">
    <tr>
        <td valign="top" style="width:10%">
            <b><?php echo esc_html(__('Zahlungstatus', 'abilita-payments-for-woocommerce')); ?></b>
        </td>
        <td valign="top">
            Ordnen Sie bitte die abilita PAY Zahlungsstatus den WooCommerce-Status zu. Die Standard-Einstellungen vom Plugin können von Ihren Einstellungen abweichend sein.
            <br><br>
            <a href="<?php echo esc_html(admin_url('admin.php?page=abilita_settings_page&tab=settingsStatus')); ?>" target="_blank">Hier gelangen Sie zu den Zahlungsstatus-Eintstellungen</a>
        </td>
    </tr>
</table>
<table class="form-table" style="margin-bottom:20px;background: white;width:100%;border:15px solid #ececec">
    <tr>
        <td valign="top" style="width:10%">
            <b><?php echo esc_html(__('Ware reservieren (Minuten)', 'abilita-payments-for-woocommerce')); ?></b>
        </td>
        <td valign="top">
            WooCommerce hat in seinen Standard-Einstellungen 60 Minuten für Reservierung von Ware einer Bestellung hinterlegt. Dies bedeutet, dass wenn ein
            Kunde mit den Zahlungsarten: <b>Vorkasse</b>, <b>Rechnung</b> oder <b>SEPA-Lastschrift</b> eine Bestellung durchführt,
            reserviert WooCommerce die bestellten Produkte lediglich für 60 Minuten. Nach 60 Minuten würde eine Bestellung automatisch
            von WooCommerce storniert und der Bestand aufgewertet.
            <br><br>
            Da bei den Zahlungsarten <b>Vorkasse</b>, <b>Rechnung</b> oder <b>SEPA-Lastschrift</b> der Zahlungseingang einige Tage dauern kann,
            sollten Sie den hinterlegten Wert komplett entfernen und die Änderung speichern.
            <br><br>
            <a href="/wp-admin/admin.php?page=wc-settings&tab=products&section=inventory" target="_blank">Hier gelangen Sie zu den Lagerbestand-Einstellungen</a>
        </td>
    </tr>
</table>
<table class="form-table" style="margin-bottom:20px;background: white;width:100%;border:15px solid #ececec">
    <tr>
        <td valign="top" style="width:10%">
            <b><?php echo esc_html(__('Permalinks', 'abilita-payments-for-woocommerce')); ?></b>
        </td>
        <td valign="top">
            Achten Sie bitte darauf, dass Sie die Permalink-Einstellungen nicht auf <b>"Individuelle Struktur"</b> gesetzt haben. Hintergrund ist,
            dass in diesem Fall keine WooCommerce bzw. WordPress API-Endpunkte mehr aufgerufen werden können. Diese werden allerdings für alle
            Zahlungsarten benötigt. abilita PAY meldet über diese API-Endpunkte aktuelle Nachrichten einer Bestellung und der genutzten Zahlungsart.
            <br><br>
            Bitte nutzen Sie nur: <b>Tag und Name</b>, <b>Monat und Name</b>, <b>Numerisch</b> oder <b>Beitragsname</b>
            <br><br>
            Wir gehen davon aus, dass Sie diese Einstellungen aus SEO-Gründen bereits gemacht haben.
            <br><br>
            <a href="/wp-admin/options-permalink.php" target="_blank">Hier gelangen Sie zu den Permalinks</a>
        </td>
    </tr>
</table>
<style>
    small.info {
        color:gray;
        font-style: italic;
    }
</style>