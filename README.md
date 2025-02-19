
<center># Installations- und Konfigurationsanleitung</center>
<br>
<center>**<span style="color:#D4161B">abilita PAY</span> für WooCommerce**</center>

In diesem Dokument wird die Installation und Konfiguration des WooCommerce-Plugins <span style="color:#D4161B">abilita PAY</span> Schritt für Schritt erläutert. Das Plugin ermöglicht eine nahtlose Integration verschiedener Zahlungsoptionen in Ihren WooCommerce-Shop und bietet eine benutzerfreundliche Verwaltung von Transaktionen.

**1 Vorbereitung**

- Um das Plugin zu installieren, benötigen Sie die Berechtigung, neue Plugins in WordPress hinzuzufügen. Diese Berechtigung ist standardmäßig für Administratoren verfügbar.

- Melden Sie sich im Admin-Bereich Ihrer WordPress-Installation an.

**2 Plugin-Zip hochladen**

- **Installationsseite aufrufen**

  Navigieren Sie im WordPress-Dashboard zu **_Plugins > Installieren_**, um die Seite für die Plugin-Installation zu öffnen.


- **Plugin hochladen**

  Klicken Sie auf **_Plugin hochladen_**, um den Upload-Bereich zu öffnen.


- **Datei auswählen**

  Wählen Sie die zuvor auf Ihre Festplatte heruntergeladene Plugin-ZIP-Datei vom Speicherort aus und klicken Sie anschließend auf **_Jetzt_** **_installieren_**.


- **Plugin aktivieren**

  Nach erfolgreichem Upload werden Sie zur Bestätigungsseite weitergeleitet. Klicken Sie dort auf **_Plugin aktivieren_**, um das Plugin zu aktivieren.


- **Erfolgreiche Installation**

  Nach erfolgreicher Aktivierung erscheint der Menüpunkt **_abilita PAY_** in der linken Toolbar des WordPress-Dashboards.

**3 Plugin-Einstellungen**

- Navigieren Sie in der Admin-Navigation zum Menüpunkt **_abilita PAY_**.

- Folgende Einstellungs-Bereiche werden Ihnen dargestellt
    - _Transaktionen_
    - _Bezahlstatus_
    - _API Zugangsdaten_
    - _Checkliste_
    - _Sonstiges_

  Diese werden in den nächsten Punkten Schritt für Schritt erläutert.

**3.1 Plugin-Einstellungen / Transaktionen**

- Auf dieser Seite werden alle Transaktionen der verschiedenen Zahlungsarten angezeigt, die in Ihrem Shop durchgeführt wurden.

- Aus Leistungsgründen werden standardmäßig nur die Transaktionen der letzten 24 Stunden geladen.

- Sie können Transaktionen nach Zahlungsart und Zahlungsstatus filtern.

- Zusätzlich steht eine Suchfunktion zur Verfügung, mit der Sie gezielt nach bestimmten Bestellungen suchen können.

**3.2 Plugin-Einstellungen / Bezahlstatus**

- Auf dieser Seite werden Ihre WooCommerce-Statuswerte den entsprechenden abilita-Statuswerten zugeordnet.

  Diese Einstellungen sind standardmäßig mit Installation schon so gesetzt, dass ein reibungsloser Ablauf in der Zusammenarbeit mit abilita gewährleistet ist.

  Ändern Sie diese Zuordnung daher nur unter vorheriger Absprache mit abilita.

- Achten Sie besonders darauf, dass der abilita-Status “completed” nicht dem WooCommerce-Status “Abgeschlossen” zugewiesen werden darf, da dieser in WooCommerce bedeutet, dass die Bestellung vollständig abgewickelt wurde.

WooCommerce verfügt leider über keinen differenzierteren Zahlungsstatus wie “Zahlung abgeschlossen”.

**3.3 Plugin-Einstellungen / API-Zugangsdaten**

- Geben Sie an dieser Stelle bitte Ihre API-Zugangsdaten ein, um Ihren Shop mit dem abilita Payment Gateway zu verbinden. An welcher Stelle Sie diese finden, erklären wir Ihnen im Nachfolgenden.

* Als erstes können Sie unter dem ersten Punkt **_API-Umgebung_** wählen, ob Sie sich mit der Testumgebung (Sandbox) oder mit der Produktionsumgebung (Live) des abilita Payment Gateways verbinden wollen.

* Die API-Zugangsdaten finden Sie, indem Sie sich mit Ihren Zugangsdaten im abilita Payment Gateway einloggen.

- Hier gelangen Sie zur <span style="color:#D4161B">Testumgebung</span> des abilita Payment Gateways:
  [` https://testdashboard.abilitapay.de/auth/login`](https://testdashboard.abilitapay.de/auth/login)

* Hier gelangen Sie zur <span style="color:#D4161B">Produktionsumgebung</span> des abilita Payment Gateways:
  [` https://dashboard.abilitapay.de/auth/login`](https://dashboard.abilitapay.de/auth/login)

- Falls Sie noch keine Zugangsdaten zum abilita Payment Gateway haben, melden Sie sich bei abilita, um diese zu erhalten.

* Klicken Sie nach erfolgreichem Login in der gewünschten Umgebung des Payment Gateways rechts oben auf „Mein Unternehmen“:

  ![](https://lh7-rt.googleusercontent.com/docsz/AD_4nXdWxDKcvc3uuMSDaqYk6uHkOBqP-2mtAJyZd2aSbz0TUiq0n5jNfvq8UMZgPTPJ02bLoDIfsbROF3n0lVuPgYhS-xih8GLfBttD5sOfaXRl57Mkz1Aj6_wEYF-GHLxAQH-FiAqs?key=4fk3tE37GsxpCQMMeQ0cKttO)

- Dort finden Sie die benötigten API-Zugangsdaten:

  ![](https://lh7-rt.googleusercontent.com/docsz/AD_4nXdhc_IPX7R_wEVGV943C5cOG5AVh08gDm4M8Vc8WUMt92ICfGiSfpV-tSu5g4wfKgPyDzgQAZpoSlt58Jmm9gtFinp8TtbFAzc9sAEpFV8-MZA7RTmlraho3Qylc5At-HnW_1KTdQ?key=4fk3tE37GsxpCQMMeQ0cKttO)

_(Der Incoming Key wird für WooCommerce-Shopsysteme nicht benötigt)_

- Übernehmen Sie diese nun in Ihren WooCommerce-Shop:

  ![](https://lh7-rt.googleusercontent.com/docsz/AD_4nXfnn7QVEfrhBcPS_xiTUZ0dOBdGeQBVP4nFOZNEcbyM5tEwDEITDv7uQTG2pnBk-TnUGJ0jFmnPtRjfe6wWlBjgmbNeXd0TR14tDI8UzMhMqN72-YD_MrfcGqZfctmm9XdS76fU?key=4fk3tE37GsxpCQMMeQ0cKttO)

- <span style="color:#D4161B">Bitte achten Sie unbedingt darauf, dass Sie nicht die Daten aus der Testumgebung des abilita Payment Gateways in die Produktionsumgebung Ihres WooCommerce-Shops übernehmen oder andersherum.</span>

* Speichern Sie Ihre Eingaben, indem Sie auf **_Änderungen speichern_** klicken.

**3.4 Plugin-Einstellungen / Checkliste**

- _Aktivierung Zahlungsarten_

  Wenn Sie die vorherigen Schritte dieser Anleitung bereits erledigt haben, gibt es hier nichts weiter für Sie zu tun.

- _Zahlungsstatus_

  Wenn Sie die vorherigen Schritte dieser Anleitung bereits erledigt haben, gibt es hier nichts weiter für Sie zu tun.

- _Ware reservieren (Minuten)_

  WooCommerce hat in seinen Standard-Einstellungen 60 Minuten für die Reservierungen von Waren einer Bestellung hinterlegt. Dies bedeutet, dass wenn ein Kunde mit den Zahlungsarten: Vorkasse, Rechnung oder SEPA-Lastschrift eine Bestellung durchführt, reserviert WooCommerce die bestellten Produkte lediglich für 60 Minuten. Nach 60 Minuten würde eine Bestellung automatisch von WooCommerce storniert und der Bestand aufgewertet.

  Da bei den Zahlungsarten Vorkasse, Rechnung oder SEPA-Lastschrift der Zahlungseingang einige Tage dauern kann, sollten Sie den hinterlegten Wert komplett entfernen und die Änderung speichern.



	Navigieren Sie hierzu in Ihre Lagerbestand-Einstellungen in Ihrem Shopsystem und löschen Sie den dort evtl. vorhandenen Wert:  
  
	![](https://lh7-rt.googleusercontent.com/docsz/AD_4nXdOm_35aU5buoiKDVNroR6oVz3bX1zDyuR3QnZwO4HBd5_i2VU1f54Utszf39v7ub1WyRbD_W0B7CzJGONn3NXIME6G1eR_gTXr17QvrIXFmtsUNr1o5zVvsHaOztv78bn_ZTc5Qw?key=4fk3tE37GsxpCQMMeQ0cKttO)

- _Permalinks_

  Achten Sie bitte darauf, dass Sie die Permalink-Einstellungen nicht auf **Individuelle Struktur** gesetzt haben. Hintergrund ist, dass in diesem Fall keine WooCommerce bzw. WordPress API-Endpunkte mehr aufgerufen werden können. Diese werden allerdings für alle Zahlungsarten benötigt. abilita PAY meldet über diese API-Endpunkte aktuelle Nachrichten einer Bestellung und der genutzten Zahlungsart.

  Bitte nutzen Sie nur: **_Tag und Name, Monat und Name, Numerisch oder Beitragsname_**

  Wir gehen davon aus, dass Sie diese Einstellungen aus SEO-Gründen bereits gemacht haben. Stellen Sie es bitte trotzdem einmal sicher hier:

  ![](https://lh7-rt.googleusercontent.com/docsz/AD_4nXfEKo_PpC3pQhkN2fVOmuCLOH77kqRHelnYUOpALoyJdNqmxDJwcNLAmMzpDWw7v7qFp-4VRd_k6pGb5xplbXDm07Em8EoW_3VighK8IDhJR7y9MW6rxPcu2cNcLZjjT04zU0Z1Yw?key=4fk3tE37GsxpCQMMeQ0cKttO)

- _Germanized > Sendungen > Allgemein > Automatisierung_

  Bitte prüfen Sie, ob in Germanized die automatische Statusänderung von Bestellungen auf „fertiggestellt“ aktiviert ist. Wenn Sie mit dem JTL-Warenwirtschaftssystem arbeiten, sollten Sie diese Einstellung deaktivieren, da JTL den Bestellstatus erkennt und fälschlicherweise als „bezahlt“ markiert – auch wenn die Zahlung noch aussteht.

  Bitte deaktivieren Sie diese Option.

  ![](https://lh7-rt.googleusercontent.com/docsz/AD_4nXeVlIbWkO8C3eIwdcoLtmzcE1Cu9Xj6AXrB0kkh7QuhGGtQiyU8CuBCrmfmCM9vekv87MLH3h3EdS7jaR9rHIFTZdfvf_-qU3cy2kC40W5YRCoXySlZ_xsrG4bUmH1eOWWxn4aU0Q?key=4fk3tE37GsxpCQMMeQ0cKttO)

**3.5 Plugin-Einstellungen / Sonstiges**

- _Checkout-Formularfeld (Anrede)_

  Wählen Sie im Bereich **_Checkout-Formularfeld (Anrede)_** aus, ob Sie das Anrede-Feld von abilita PAY nutzen möchten.

  Falls Sie bereits ein Anrede-Feld durch andere Plugins in Ihrem Bestellprozess verwenden, geben Sie im Feld **_Eigenes Feld (Mapping)_** den technischen Namen des Feldes an, wie es bei Ihnen konfiguriert wurde.

- _Checkout-Formularfeld (Firma)_

  Mit dieser Einstellung können Sie den **_Place-Holder-Text_** des Firmen-Eingabefelds im Bestellprozess von abilita PAY überschreiben und durch den gewünschten Text ersetzen. Geben Sie dazu den entsprechenden **_Place-Holder-Text_** ein.

  Beispiel: Sie könnten den Text _Für Bestellungen auf Firmenrechnung (B2B) bitte die Firmendaten angeben._ verwenden.

- _Bestellnummer/Auftragsnummer-Präfix_

  Wenn Sie in Ihrem WooCommerce-Shop ein Bestellnummern-Präfix verwenden, tragen Sie dieses unter Bestellnummer/Auftragsnummer-Präfix ein.

  Beispiel: Verwenden Sie das Präfix **_BN-_**, dann geben Sie **_BN-_** in dieses Feld ein.

  <span style="color:#D4161B">Beachten Sie bitte, dass eine korrekte Zuweisung des Zahlungsstatus nur mit einer korrekten Bestellnummer erfolgen kann.</span>

- _Logger und PHP-Fehlermeldungen_

  Aktivieren Sie diese Optionen nur zur Fehlersuche. In der Regel werden die beiden Optionen vom technischen Support bei Bedarf aktiviert:

    - _Logger_

    - _PHP-Fehlermeldungen_

* _CSS für HTML-SELECT-Felder…._

    - In diesem Bereich können Sie das Aussehen der Eingabefelder für das Geburtsdatum anpassen, um sie an Ihr Layout anzupassen.

    - Falls Sie einen Webdesigner mit dem Layout Ihres Shops beauftragt haben, kann dieser die CSS-Angaben direkt im Layout-CSS Ihres Shops überschreiben.

    - Alle Eingabefelder für das Geburtsdatum verfügen über eigene CSS-Klassen.

Um Ihre Einstellungen in den Plugin-Einstellungen / Sonstiges zu übernehmen, klicken Sie bitte auf **_Änderung speichern_**.

**4 Zahlungsarten aktivieren und konfigurieren**

- Um die von Ihnen über den abilita PAY Vertrag gebuchten Zahlungsarten zu aktivieren und zu konfigurieren, navigieren Sie zu **_WooCommerce > Einstellungen_**.

* Wählen Sie hier den Reiter **_Zahlungen_** aus.

- Sie können nun über den Regler in der Spalte **_Aktiviert_** die gebuchten Zahlungsarten aktivieren.

* Die meisten abilita PAY Zahlungsarten bedürfen keiner weiteren Konfiguration über die Button **_Einrichtung abschließen_** bzw. **_Verwalten_**, da diese mit Installation bereits Standardwerte gesetzt bekommen haben, welche einen reibungslosen Ablauf gewährleisten.

- Wichtig:

  Für die Zahlungsarten **_coin4Lastschrift & coin4Vorkasse_** müssen allerdings noch weitere Angaben gemacht werden, um die Funktionalität zu gewährleisten.

  Klicken Sie hierzu den Button **_Einrichtung abschließen_** bzw. **_Verwalten_** neben der Zahlungsart und scrollen Sie dort an das Ende der Seite, füllen Sie diese Felder aus mit den Daten Ihres von abilita überwachten EBICS-Kontos:![](https://lh7-rt.googleusercontent.com/docsz/AD_4nXdV37jiR2ZhEMn1AqdsOc_eeb8bgDHN5Rm1A2JMBUD5s6TY29D9AM-bWDEgt502C71prr79lgzPZvOM3Y0hBxN5Jpb31UcHwD4w6cELFKP2wGvK1LwT8xlk6r2QlZB3DJ2kIVOP0g?key=4fk3tE37GsxpCQMMeQ0cKttO)

  ![](https://lh7-rt.googleusercontent.com/docsz/AD_4nXfPioIKhwxX32QXLnEc5XSWPYX0Orm6YRXNplhohrkNgG8kgj3N75KQv5q_6HXIa3opXl2SIDDEiJ2wmJm6J_KDfy7P-OczRoD5JxyBq-WFQWxvcCBcAns67u2sV2CGfYN_QBwR?key=4fk3tE37GsxpCQMMeQ0cKttO)

- Um Ihre Einstellungen in den Plugin-Einstellungen / Sonstiges zu übernehmen, klicken Sie bitte auf **_Änderung speichern_**.
