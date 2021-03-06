<?php

namespace Pronamic\WordPress\Pay\Admin;

use Pronamic\WordPress\Pay\Plugin;

/**
 * Title: WordPress admin notices
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 3.7.0
 * @since 3.7.0
 */
class AdminNotices {
	/**
	 * Constructs and initializes an notices object
	 *
	 * @see https://github.com/woothemes/woocommerce/blob/2.4.3/includes/admin/class-wc-admin-notices.php
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Actions
		add_action( 'admin_notices', array( $this, 'admin_notices' ), 11 );
	}

	//////////////////////////////////////////////////

	/**
	 * Admin notices
	 *
	 * @see https://github.com/WordPress/WordPress/blob/4.3.1/wp-admin/admin-header.php#L245-L250
	 */
	public function admin_notices() {
		$screen = get_current_screen();

		// Show notices only to options managers (administrators)
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Jetpack
		if ( 'jetpack' === $screen->parent_base ) {
			return;
		}

		// License notice
		if ( 'valid' !== get_option( 'pronamic_pay_license_status' ) ) {
			$class = \Pronamic\WordPress\Pay\Plugin::get_number_payments() > 20 ? 'error' : 'updated';

			$license = get_option( 'pronamic_pay_license_key' );

			if ( '' === $license ) {
				$notice = sprintf(
					__( '<strong>Pronamic Pay</strong> — You have not entered a valid <a href="%1$s">support license key</a>, please <a href="%2$s" target="_blank">get your key at pronamic.eu</a>.', 'pronamic_ideal' ),
					add_query_arg( 'page', 'pronamic_pay_settings', get_admin_url( null, 'admin.php' ) ),
					'https://www.pronamic.eu/plugins/pronamic-ideal/'
				);
			} else {
				$notice = sprintf(
					__( '<strong>Pronamic Pay</strong> — You have not entered a valid <a href="%1$s">support license key</a>. Please <a href="%2$s" target="_blank">get your key at pronamic.eu</a> or login to <a href="%3$s" target="_blank">check your license status</a>.', 'pronamic_ideal' ),
					add_query_arg( 'page', 'pronamic_pay_settings', get_admin_url( null, 'admin.php' ) ),
					'https://www.pronamic.eu/plugins/pronamic-ideal/',
					'https://www.pronamic.eu/account/'
				);
			}

			printf( //xss ok
				'<div class="%s"><p>%s</p></div>',
				esc_attr( $class ),
				$notice
			);
		}

		// Stored notices
		$notices = get_option( 'pronamic_pay_admin_notices', array() );

		foreach ( $notices as $name ) {
			$file = plugin_dir_path( \Pronamic\WordPress\Pay\Plugin::$file ) . 'admin/notice-' . $name . '.php';

			if ( is_readable( $file ) ) {
				include $file;
			}
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Add a notice to show
	 *
	 * @param string $name
	 */
	public function add_notice( $name ) {
		$notices = array_unique( array_merge( get_option( 'pronamic_pay_admin_notices', array() ), array( $name ) ) );

		update_option( 'pronamic_pay_admin_notices', $notices );
	}

	/**
	 * Remove a notice from being displayed
	 *
	 * @param string $name
	 */
	public static function remove_notice( $name ) {
		$notices = array_diff( get_option( 'pronamic_pay_admin_notices', array() ), array( $name ) );

		update_option( 'pronamic_pay_admin_notices', $notices );
	}
}
