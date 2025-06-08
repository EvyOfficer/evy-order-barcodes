<?php
/**
 * Class WooCommerce Order Barcodes Generator Tclib file.
 *
 * @package woocommerce-order-barcodes
 */

use Com\Tecnick\Barcode\Barcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WooCommerce_Order_Barcodes_Generator_Tclib' ) ) {
	return;
}

require_once WC_ORDER_BARCODES_DIR_PATH . '/lib/barcode_generator/class-woocommerce-order-barcodes-generator.php';

/**
 * Class WooCommerce_Order_Barcodes_Generator_Tclib
 */
class WooCommerce_Order_Barcodes_Generator_Tclib extends WooCommerce_Order_Barcodes_Generator {
	/**
	 * Barcode TC Lib object.
	 *
	 * @var dns1d.
	 */
	protected $barcode;

	/**
	 * Type of barcode that will be used.
	 *
	 * @var string.
	 */
	protected $barcode_type;

	/**
	 * Class constructor
	 *
	 * @param String $foreground_color Foreground color of the barcode.
	 * @param String $barcode_type     Type of barcode that will be used.
	 * @param String $background_color Background color of the barcode.
	 */
	public function __construct( $foreground_color, $barcode_type, $background_color = 'transparent' ) {
		// Instantiate the barcode class.
		$this->foreground_color = $foreground_color;
		$this->background_color = $background_color;
		$this->barcode          = new Barcode();
		$this->barcode_type     = $barcode_type;
	}

	/**
	 * Get barcode parameters that can be filtered by users.
	 *
	 * @param string $barcode_type Type of barcode.
	 *
	 * @return array Parameters for barcode generation.
	 */
	protected function get_barcode_parameters( string $barcode_type ): array {
		// Default parameters for different barcode types.
		$default_params = array(
			'DATAMATRIX' => array(
				'width'      => - 10,
				'height'     => - 10,
				'color'      => $this->foreground_color,
				'padding'    => array( 12, 12, 12, 12 ),
				'background' => $this->background_color,
			),
			'QRCODE'     => array(
				'width'      => - 5,
				'height'     => - 5,
				'color'      => $this->foreground_color,
				'padding'    => array( 12, 12, 12, 12 ),
				'background' => $this->background_color,
			),
			'C39'        => array(
				'width'      => - 1,
				'height'     => - 48,
				'color'      => $this->foreground_color,
				'padding'    => array( 12, 12, 12, 12 ),
				'background' => $this->background_color,
			),
			'C93'        => array(
				'width'      => - 1,
				'height'     => - 48,
				'color'      => $this->foreground_color,
				'padding'    => array( 12, 12, 12, 12 ),
				'background' => $this->background_color,
			),
			'C128'       => array(
				'width'      => - 1,
				'height'     => - 48,
				'color'      => $this->foreground_color,
				'padding'    => array( 12, 12, 12, 12 ),
				'background' => $this->background_color,
			),
		);

		/**
		 * Filters the parameters used for barcode generation.
		 * This filter allows customization of barcode dimensions, colors and padding.
		 *
		 * @since 1.9.0
		 *
		 * @param array  $params       {
		 *     An array of barcode parameters.
		 *
		 *     @type int   $width       Barcode width in user units (excluding padding). A negative value indicates the multiplication factor for each column.
		 *     @type int   $height      Barcode height in user units (excluding padding). A negative value indicates the multiplication factor for each row.
		 *     @type string $color      Foreground color in hexadecimal (e.g., '#000000').
		 *     @type array  $padding    Array of padding values [top, right, bottom, left].
		 *     @type string $background Background color in Web notation (color name, or hexadecimal code, or CSS syntax).
		 * }
		 *
		 * @param string $barcode_type The type of barcode being generated ('DATAMATRIX', 'QRCODE', 'C39', 'C93', 'C128').
		 *
		 * @return array $params The modified barcode parameters.
		 */
		return apply_filters( 'woocommerce_order_barcode_parameters', $default_params[ $barcode_type ], $barcode_type );
	}

	/**
	 * Get barcode parameters that can be filtered by users.
	 *
	 * @param String $type Type of barcode.
	 * @param String $barcode_text Barcode text.
	 *
	 * @return Barcode Barcode Object.
	 */
	protected function get_barcode_object( string $type, string $barcode_text ) {
		$params = $this->get_barcode_parameters( $type );

		return $this->barcode->getBarcodeObj( $type, $barcode_text, $params['width'], $params['height'], $params['color'], $params['padding'] )->setBackgroundColor( $params['background'] );
	}

	/**
	 * Get output of barcode based on the type.
	 *
	 * @param Barcode $bobj Barcode object.
	 * @param String  $type Type of barcode.
	 *
	 * @return String.
	 */
	protected function get_output( $bobj, $type ) {
		if ( 'PNG' === $type ) {
			return $bobj->getPngData();
		} elseif ( 'SVG' === $type ) {
			return $bobj->getSvgCode();
		}

		return $bobj->getHtmlDiv();
	}

	/**
	 * Get generated barcode.
	 *
	 * @param String $barcode        Barcode text.
	 * @param String $barcode_output Type of barcode content. Example : 'PNG', 'HTML', 'SVG'.
	 *
	 * @return String.
	 */
	public function get_generated_barcode( $barcode, $barcode_output = 'HTML' ) {
		// Generate barcode image based on string and selected type.
		switch ( $this->barcode_type ) {
			case 'datamatrix':
				$barcode_img = $this->get_datamatrix( $barcode, $barcode_output );
				break;
			case 'qr':
				$barcode_img = $this->get_qrcode( $barcode, $barcode_output );
				break;
			case 'code39':
				$barcode_img = $this->get_code_39( $barcode, $barcode_output );
				break;
			case 'code93':
				$barcode_img = $this->get_code_93( $barcode, $barcode_output );
				break;
			case 'code128':
			default:
				$barcode_img = $this->get_code_128( $barcode, $barcode_output );
				break;
		}

		return $barcode_img;
	}

	/**
	 * Generate a DATAMATRIX barcode in the specified output format.
	 *
	 * @param string $barcode_text The text to encode into a DATAMATRIX barcode.
	 * @param string $output       The desired output format. Accepts 'PNG', 'HTML', or 'SVG'. Default is 'HTML'.
	 *
	 * @return string The generated barcode content in the specified format.
	 */
	public function get_datamatrix( $barcode_text, $output = 'HTML' ): string {
		$bobj = $this->get_barcode_object( 'DATAMATRIX', $barcode_text );

		return $this->get_output( $bobj, $output );
	}

	/**
	 * Generate a QR Code barcode in the specified output format.
	 *
	 * @param string $barcode_text The text to encode into a QR Code barcode.
	 * @param string $output       The desired output format. Accepts 'PNG', 'HTML', or 'SVG'. Default is 'HTML'.
	 *
	 * @return string The generated barcode content in the specified format.
	 */
	public function get_qrcode( $barcode_text, $output = 'HTML' ): string {
		$bobj = $this->get_barcode_object( 'QRCODE', $barcode_text );

		return $this->get_output( $bobj, $output );
	}

	/**
	 * Generate a Code 39 barcode in the specified output format.
	 *
	 * @param string $barcode_text The text to encode into a Code 39 barcode.
	 * @param string $output       The desired output format. Accepts 'PNG', 'HTML', or 'SVG'. Default is 'HTML'.
	 *
	 * @return string The generated barcode content in the specified format.
	 */
	public function get_code_39( $barcode_text, $output = 'HTML' ): string {
		$bobj = $this->get_barcode_object( 'C39', $barcode_text );

		return $this->get_output( $bobj, $output );
	}

	/**
	 * Generate a Code 93 barcode in the specified output format.
	 *
	 * @param string $barcode_text The text to encode into a Code 93 barcode.
	 * @param string $output       The desired output format. Accepts 'PNG', 'HTML', or 'SVG'. Default is 'HTML'.
	 *
	 * @return string The generated barcode content in the specified format.
	 */
	public function get_code_93( $barcode_text, $output = 'HTML' ): string {
		$bobj = $this->get_barcode_object( 'C93', $barcode_text );

		return $this->get_output( $bobj, $output );
	}

	/**
	 * Generate a Code 128 barcode in the specified output format.
	 *
	 * @param string $barcode_text The text to encode into a Code 128 barcode.
	 * @param string $output       The desired output format. Accepts 'PNG', 'HTML', or 'SVG'. Default is 'HTML'.
	 *
	 * @return string The generated barcode content in the specified format.
	 */
	public function get_code_128( $barcode_text, $output = 'HTML' ): string {
		$bobj = $this->get_barcode_object( 'C128', $barcode_text );

		return $this->get_output( $bobj, $output );
	}
}
