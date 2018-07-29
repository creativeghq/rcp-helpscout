<?php

/*
 * Registers the customer endpoint
 *
 */
class PW_RCP_Help_Scout_Customer {

	public function __construct() {
		include RCP_HELPSCOUT_PLUGIN_DIR . 'includes/class-rcp-help-scout-plugin.php';

		add_action( 'init', array( $this, 'add_rewrite_endpoints' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
	}

	public function add_rewrite_endpoints() {
		add_rewrite_endpoint( 'member', EP_PERMALINK | EP_PAGES );
	}

	public function template_redirect() {
		global $wp_query;

		if ( isset( $wp_query->query_vars[ 'member' ] ) ) {
			$plugin = new PW_RCP_Help_Scout_Plugin_Handler();

			echo json_encode( $plugin->getResponse() );

			exit();
		}
	}

}
new PW_RCP_Help_Scout_Customer;