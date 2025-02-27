<?php declare(strict_types=1);

defined('ABSPATH') || exit;

/*
Plugin Name:          abilita PAY
Requires Plugins:     woocommerce
Plugin URI:           https://abilita.de/payment/zahlungsarten/
Description:          abilita PAY - coin4 RECHNUNG, coin4 LASTSCHRIFT, coin4 DIREKT, coin4 Vorkasse, PayPal, Kreditkarte
Author:               abilita
Author URI:           https://abilita.de/
Tags:                 payment, gateway, ecommerce, e-commerce, store, sales, sell, shop, checkout, paypal, woo commerce, woocommmerce, invoice, sepa, advance, b2b, b2c
Text Domain:          abilita-payments-for-woocommerce
Domain Path:          /i18n/languages

Version:              1.0.2
Requires at least:    6.0
Tested up to:         6.7
Requires PHP:         8.0

WC requires at least: 8.0
WC tested up to:      8.0
Stable tag:           1.0.0

@copyright Copyright (c) 2025, abilita GmbH
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/**
 * Currently plugin version and pluginn-ame
 */
define('WC_ABILITA_PAYMENT_VERSION', '1.0.2');
define('WC_ABILITA_PAYMENT_NAME'   , 'abilita PAY');

require_once(plugin_dir_path(__FILE__).'includes/class-wc-abilita-plugin-loader.php');
$plugin = new abilita\payment\WC_Abilita_Plugin_Loader();
$plugin->run();

add_action('before_woocommerce_init', function () {
    if (class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

