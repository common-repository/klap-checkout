<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             2.2.3
 * @package           KlapCheckout
 *
 * @wordpress-plugin
 * Plugin Name:       Klap Checkout
 * Plugin URI:        https://www.klap.cl/developers/plugins
 * Version:           2.2.3
 * Description:       Acepta pagos seguros y fáciles con Klap Checkout para Woocommerce. Ofrecemos varias opciones de pago, incluyendo tarjetas de crédito, débito y prepago de Visa y Mastercard, así como efectivo, transferencia bancaria y otros métodos de pago populares en Chile.
 * Author:            Klap Developers
 * Author URI:        https://www.klap.cl/
 * License:           BSD-3-Clause
 * License URI:       https://opensource.org/licenses/BSD-3-Clause
 * Text Domain:       klap_checkout_woocommerce
 * Domain Path:       /languages
 * Requires at least: 4.9.8
 * Requires PHP: 5.6
 * WC requires at least: 4.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

const MCP_PLUGIN_VERSION = '2.2.3';

date_default_timezone_set('America/Santiago');
/**
 * The code that runs during plugin activation.
 * This action is documented in plugin/class-klap_checkout-activator.php
 */
function activate_klap_checkout() {
	require_once plugin_dir_path(__FILE__) . 'plugin/klap_checkout_activator.php';
	KlapCheckoutActivator::activate();
}
register_activation_hook(__FILE__, 'activate_klap_checkout');

/**
 * The code that runs during plugin deactivation.
 * This action is documented in plugin/class-klap_checkout-deactivator.php
 */
function deactivate_klap_checkout() {
	require_once plugin_dir_path(__FILE__) . 'plugin/klap_checkout_deactivator.php';
	KlapCheckoutDeactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_klap_checkout');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
add_action('plugins_loaded', 'run_klap_checkout', 0);
function run_klap_checkout() {
	if (!class_exists('WC_Payment_Gateway')) {
    return;
  }
	require plugin_dir_path(__FILE__) . 'plugin/klap_checkout_plugin_tarjetas.php';
  require plugin_dir_path(__FILE__) . 'plugin/klap_checkout_plugin_sodexo.php';
  require plugin_dir_path(__FILE__) . 'plugin/klap_checkout_plugin_efec_trans.php';
  require plugin_dir_path(__FILE__) . 'plugin/klap_checkout_plugin_oneclick.php';
	$pluginTarjetas = new KlapCheckoutPluginTarjetas();
	$pluginTarjetas->run();
  $pluginSodexo = new KlapCheckoutPluginSodexo();
	$pluginSodexo->run();
  $pluginEfectTransf = new KlapCheckoutPluginEfecTrans();
	$pluginEfectTransf->run();
  $pluginOneClick = new  KlapCheckoutPluginOneClick();
  $pluginOneClick->run();
}


function create_table_klap_card_token() {
  require_once plugin_dir_path(__FILE__) . 'Helpers/DataBaseInstaller.php';
  DataBaseInstaller::createTableCardToken();
}

add_action('admin_init', 'create_table_klap_card_token');

add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_klap_checkout');
function woocommerce_add_gateway_klap_checkout($methods) {
	$methods[] = 'KlapCheckoutPluginTarjetas';
  $methods[] = 'KlapCheckoutPluginSodexo';
  $methods[] = 'KlapCheckoutPluginEfecTrans';
  $methods[] = 'KlapCheckoutPluginOneclick';
	return $methods;
}

add_filter('plugin_row_meta', 'klap_checkout_plugin_row_meta', 10, 2);

function klap_checkout_plugin_row_meta($links, $file) {
  if (strpos($file, 'klap_checkout.php') !== false) {
    $new_links = array(
      '<a href="https://github.com/KlapDevelopers/Manuales/raw/main/Manual_Klap_Checkout_Woocommerce.pdf">Manual de configuración</a>',
    );
    $links = array_merge($links, $new_links);
  }
  return $links;
}
