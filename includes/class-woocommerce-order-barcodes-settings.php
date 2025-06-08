<?php
/**
 * Class WooCommerce_Order_Barcodes_Settings.
 *
 * @package woocommerce-order-barcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WooCommerce_Order_Barcodes_Settings.
 *
 * A class to handle the plugin settings.
 */
class WooCommerce_Order_Barcodes_Settings {

	/**
	 * The single instance of WooCommerce_Order_Barcodes_Settings.
	 *
	 * @var     object
	 * @since   1.0.0
	 * @static
	 */
	private static $instance = null;

	/**
	 * The main plugin object.
	 *
	 * @var   object
	 * @since 1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 *
	 * @var   array
	 * @since 1.0.0
	 */
	public $settings = array();

	/**
	 * WooCommerce settings general object.
	 *
	 * @var WC_Settings_General
	 */
	protected WC_Settings_General $wc_settings_general;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @param WooCommerce_Order_Barcodes $wc_order_barcodes Main plugin object.
	 */
	public function __construct( $wc_order_barcodes ) {

		// Set main plugin class as parent.
		$this->parent = $wc_order_barcodes;

		// Initialise settings.
		$this->init_settings();

		// Set up settings fields.
		add_filter( 'woocommerce_general_settings', array( $this, 'add_settings' ), 10, 1 );
		add_action( 'woocommerce_admin_field_barcode_colors', array( $this, 'colour_settings' ) );
		add_action( 'woocommerce_admin_field_barcode_bgcolor', array( $this, 'bgcolor_settings' ) );
		add_action( 'woocommerce_settings_save_general', array( $this, 'colour_settings_save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );

		// Add settings link to plugins list table.
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ), array( $this, 'add_settings_link' ) );
	} // End __construct ()

	/**
	 * Initialise settings
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function init_settings() {
		$this->settings = $this->settings_fields();
	} // End init_settings ()

	/**
	 * Get WooCommerce settings general object.
	 *
	 * @return WC_Settings_General
	 */
	public function get_wc_settings_general(): WC_Settings_General {
		if ( empty( $this->wc_settings_general ) ) {
			$this->wc_settings_general = new WC_Settings_General();
		}

		return $this->wc_settings_general;
	}

	/**
	 * Build settings fields
	 *
	 * @since  1.0.0
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields() {

		// Set up available barcode types.
		$type_options = array(
			'code39'     => __( 'Code 39', 'woocommerce-order-barcodes' ),
			'code93'     => __( 'Code 93', 'woocommerce-order-barcodes' ),
			'code128'    => __( 'Code 128', 'woocommerce-order-barcodes' ),
			'datamatrix' => __( 'Data Matrix', 'woocommerce-order-barcodes' ),
			'qr'         => __( 'QR Code', 'woocommerce-order-barcodes' ),
		);

		// Register settings fields.
		$settings = array(
			array(
				'title' => __( 'Order Barcodes', 'woocommerce-order-barcodes' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'order_barcodes',
			),

			array(
				'title'    => __( 'Enable Barcodes', 'woocommerce-order-barcodes' ),
				'desc'     => __( 'This will enable unique barcode generation for each order.', 'woocommerce-order-barcodes' ),
				'id'       => 'wc_order_barcodes_enable',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'class'    => 'checkbox',
				'desc_tip' => true,
			),

			array(
				'title'    => __( 'Barcode Type', 'woocommerce-order-barcodes' ),
				'desc'     => __( 'This is the type of barcode that will be generated for your orders - changing this will only affect future orders.', 'woocommerce-order-barcodes' ),
				'id'       => 'wc_order_barcodes_type',
				'css'      => 'min-width:350px;',
				'default'  => 'code128',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'desc_tip' => true,
				'options'  => $type_options,
			),

			array( 'type' => 'barcode_colors' ),
			array( 'type' => 'barcode_bgcolor' ),

			array(
				'type' => 'sectionend',
				'id'   => 'order_barcodes',
			),
		);

		/**
		 * Allow settings to be filtered.
		 *
		 * @since 1.0.0
		 */
		$settings = apply_filters( 'wc_order_barcodes_settings_fields', $settings );

		return $settings;
	} // End settings_fields ()

	/**
	 * Markup for colour settings
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function colour_settings() {
		?>
		<tr valign="top" class="wc_order_barcodes_colours wc_order_barcodes_fields">
			<th scope="row" class="titledesc">
				<label for="wc_order_barcodes_colours_foreground"><?php esc_html_e( 'Barcode Color', 'woocommerce' ); ?></label>
				<?php echo wc_help_tip( __( 'Barcode image and text color', 'woocommerce-order-barcodes' ), true ); //phpcs:ignore --- `wc_help_tip()` has been escaped. ?>
			</th>
			<td class="forminp">
				<?php
				// Get settings.
				$colours = array_map( 'esc_attr', (array) get_option( 'wc_order_barcodes_colours' ) );

				// Set defaults.
				if ( empty( $colours['foreground'] ) ) {
					$colours['foreground'] = '#000000';
				}

				$wc_settings_general = $this->get_wc_settings_general();

				// Show colour selection inputs.
				$wc_settings_general->color_picker( __( 'Foreground', 'woocommerce-order-barcodes' ), 'wc_order_barcodes_colours_foreground', $colours['foreground'] );
				?>
			</td>
		</tr>
		<?php
	} // End colour_settings ().

	/**
	 * Markup for background colour settings
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function bgcolor_settings() {
		?>
		<tr valign="top" class="wc_order_barcodes_bgcolor wc_order_barcodes_fields">
			<th scope="row" class="titledesc">
				<label for="wc_order_barcodes_colours_background"><?php esc_html_e( 'Background Color', 'woocommerce' ); ?></label>
				<?php echo wc_help_tip( __( 'Background color for barcode', 'woocommerce-order-barcodes' ), true );//phpcs:ignore --- `wc_help_tip()` has been escaped. ?>
			</th>
			<td class="forminp">
				<?php
				// Get settings.
				$colours = array_map( 'esc_attr', (array) get_option( 'wc_order_barcodes_colours' ) );

				// Set defaults.
				if ( empty( $colours['background'] ) ) {
					$colours['background'] = '#ffffff';
				}

				$wc_settings_general = $this->get_wc_settings_general();

				// Show colour selection inputs.
				$wc_settings_general->color_picker( __( 'Background', 'woocommerce-order-barcodes' ), 'wc_order_barcodes_colours_background', $colours['background'] );
				?>
			</td>
		</tr>
		<?php
	} // End colour_settings ().

	/**
	 * Save colour settings.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function colour_settings_save() {
		check_admin_referer( 'woocommerce-settings' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) { //phpcs:ignore WordPress.WP.Capabilities.Unknown --- `manage_woocommerce` is a native WC capability
			exit;
		}

		$colours = array();

		if ( isset( $_POST['wc_order_barcodes_colours_foreground'] ) ) {
			// Set settings array.
			$colours['foreground'] = wc_format_hex( sanitize_text_field( wp_unslash( $_POST['wc_order_barcodes_colours_foreground'] ) ) );
		}

		if ( isset( $_POST['wc_order_barcodes_colours_background'] ) ) {
			// Set settings array.
			$colours['background'] = wc_format_hex( sanitize_text_field( wp_unslash( $_POST['wc_order_barcodes_colours_background'] ) ) );
		}

		if ( count( $colours ) > 0 ) {
			// Save settings.
			update_option( 'wc_order_barcodes_colours', $colours );
		}
	} // End colour_settings_save ()

	/**
	 * Add settings to WooCommerce General settings
	 *
	 * @since  1.0.0
	 * @param  array $settings Default settings.
	 * @return array           Modified settings
	 */
	public function add_settings( $settings = array() ) {
		$settings = array_merge( $settings, $this->settings );
		return $settings;
	} // End add_settings ()

	/**
	 * Load assets
	 *
	 * @since  1.0.0
	 * @param  string $hook_suffix Current hook.
	 * @return void
	 */
	public function load_assets( $hook_suffix = '' ) {
		if ( 'woocommerce_page_wc-settings' !== $hook_suffix ) {
			return;
		}
		wp_enqueue_style( $this->parent->token . '-admin' );
	} // End load_assets ()

	/**
	 * Add settings link to plugin list table
	 *
	 * @since  1.0.0
	 * @param  array $links Existing links.
	 * @return array        Modified links
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=general' ) ) . '">' . esc_html__( 'Settings', 'woocommerce-order-barcodes' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;
	} // End add_settings_link ()

	/**
	 * Main class instance - ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 *
	 * @param  WooCommerce_Order_Barcodes $wc_order_barcodes `WooCommerce_Order_Barcodes` object.
	 * @see    WC_Order_Barcodes()
	 * @return WooCommerce_Order_Barcodes_Settings instance
	 */
	public static function instance( $wc_order_barcodes ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $wc_order_barcodes );
		}
		return self::$instance;
	} // End instance ()

	/**
	 * Cloning is forbidden
	 *
	 * @since  1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'woocommerce-order-barcodes' ), esc_html( $this->parent->version ) );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden
	 *
	 * @since  1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'woocommerce-order-barcodes' ), esc_html( $this->parent->version ) );
	} // End __wakeup ()
}
