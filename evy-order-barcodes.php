<?php
/**
 * Plugin Name: Evy – Order Barcodes
 * Version: 1.0.0 (Based 1.9.1)
 * Plugin URI: https://github.com/EvyOfficer
 * Description: Generates unique barcodes for your orders - perfect for e-tickets, packing slips, reservations and a variety of other uses.
 * Author: EvyOfficer
 * Author URI: https://github.com/EvyOfficer
 * Text Domain: evy-order-barcodes
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Requires at least: 6.7
 * Tested up to: 6.7
 * WC requires at least: 9.7
 * WC tested up to: 9.8
 *
 * @package woocommerce-order-barcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_ORDER_BARCODES_VERSION', '1.9.1' ); // WRCS: DEFINED_VERSION.
define( 'WC_ORDER_BARCODES_FILE', __FILE__ );
define( 'WC_ORDER_BARCODES_DIR_PATH', untrailingslashit( plugin_dir_path( WC_ORDER_BARCODES_FILE ) ) );
define( 'WC_ORDER_BARCODES_DIR_URL', untrailingslashit( plugins_url( '/', WC_ORDER_BARCODES_FILE ) ) );

// Activation hook.
register_activation_hook( __FILE__, 'wc_order_barcodes_activate' );

/**
 * Activation function.
 */
function wc_order_barcodes_activate() {
	update_option( 'woocommerce_order_barcodes_version', WC_ORDER_BARCODES_VERSION );
}

// Plugin init hook.
add_action( 'plugins_loaded', 'wc_order_barcodes_init' );

/**
 * Initialize plugin.
 */
function wc_order_barcodes_init() {

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'wc_order_barcodes_woocommerce_deactivated' );
		return;
	}

	// Initialise plugin.
	add_action( 'before_woocommerce_init', 'wc_order_barcodes_load_classes', 5 );
	add_action( 'before_woocommerce_init', 'wc_order_barcodes_compatibility_declaration' );
	add_action( 'before_woocommerce_init', 'WC_Order_Barcodes', 15 );
	add_action( 'after_setup_theme', 'wc_order_barcodes_load_textdomain' );
}

/**
 * Load plugin classes.
 */
function wc_order_barcodes_load_classes() {
	// Autoload.
	require_once WC_ORDER_BARCODES_DIR_PATH . '/vendor/autoload.php';

	// Include order util trait class file.
	require_once WC_ORDER_BARCODES_DIR_PATH . '/includes/trait-woocommerce-order-util.php';

	// Include barcode generator files.
	require_once WC_ORDER_BARCODES_DIR_PATH . '/lib/barcode_generator/class-woocommerce-order-barcodes-generator-tclib.php';

	// Include plugin class files.
	require_once WC_ORDER_BARCODES_DIR_PATH . '/includes/class-woocommerce-order-barcodes.php';
	require_once WC_ORDER_BARCODES_DIR_PATH . '/includes/class-woocommerce-order-barcodes-settings.php';

	// Include plugin functions file.
	require_once WC_ORDER_BARCODES_DIR_PATH . '/includes/woocommerce-order-barcodes-functions.php';

	if ( is_admin() ) {
		require_once WC_ORDER_BARCODES_DIR_PATH . '/includes/class-woocommerce-order-barcodes-privacy.php';
	}
}

/**
 * WooCommerce feature compatibility declaration.
 *
 * @return void
 */
function wc_order_barcodes_compatibility_declaration() {
	if ( ! class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		return;
	}

	// Declare High-Performance Order Storage (HPOS) compatibility
	// See https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book#declaring-extension-incompatibility.
	\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WC_ORDER_BARCODES_FILE );

	// Declare Cart/Checkout Blocks compatibility.
	// See https://developer.woocommerce.com/2023/08/18/cart-and-checkout-blocks-becoming-the-default-experience/.
	\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', WC_ORDER_BARCODES_FILE );
}

/**
 * Load localization.
 */
function wc_order_barcodes_load_textdomain() {
	load_plugin_textdomain( 'woocommerce-order-barcodes', false, basename( WC_ORDER_BARCODES_DIR_PATH ) . '/languages' );
}

/**
 * WooCommerce Deactivated Notice.
 */
function wc_order_barcodes_woocommerce_deactivated() {
	/* translators: %s: WooCommerce link */
	echo '<div class="error"><p>' . sprintf( esc_html__( 'WooCommerce Order Barcodes requires %s to be installed and active.', 'woocommerce-order-barcodes' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</p></div>';
}
