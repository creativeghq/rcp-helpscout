<?php
/*
Plugin Name: Restrict Content Pro - Help Scout
Plugin URI: https://restrictcontentpro.com/
Description: View RCP subscriber status of a customer in Help Scout
Version: 1.0.0
Author: Pippin Williamson
Author URI: http://restrictcontentpro.com
Requires at least: 4.4
Tested up to: 4.5
Text Domain: rcp-help-scout
Domain Path: /languages
*/

define( 'RCP_HELPSCOUT_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );

class RCP_Help_Scout {

	public function __construct() {
		$this->init();

	}

	/**
	 * Get things started
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function init() {

		$this->includes();
		$this->load_textdomain();
		add_action( 'rcp_misc_settings', array( $this, 'settings' ) );

	}

	/**
	 * Includes
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function includes() {
		include RCP_HELPSCOUT_PLUGIN_DIR . 'includes/class-rcp-help-scout-api.php';
		include RCP_HELPSCOUT_PLUGIN_DIR . 'includes/class-rcp-signup.php';
		include RCP_HELPSCOUT_PLUGIN_DIR . 'includes/class-rcp-help-scout-customer.php';

	}

	/**
	 * Plugin Updater
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function updater() {

		if( class_exists( 'RCP_Add_On_Updater' ) ) {
			$updater = new RCP_Add_On_Updater( 481, __FILE__, '1.0' );
		}
	}

	/**
	 * Internationalization
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function load_textdomain() {
		// Set filter for language directory
		$lang_dir = RCP_HELPSCOUT_PLUGIN_DIR . 'languages/';
		$lang_dir = apply_filters( 'rcp_helpscout_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale', get_locale(), 'rcp-help-scout' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'rcp-help-scout', $locale );

		// Setup paths to current locale file
		$mofile_local  = $lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/rcp-help-scout/' . $mofile;

		if( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/rcp-help-scout/ folder
			load_textdomain( 'rcp-help-scout', $mofile_global );
		} elseif( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/rcp-help-scout/languages/ folder
			load_textdomain( 'rcp-help-scout', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'rcp-help-scout', false, $lang_dir );
		}
	}

	/**
	 * Add our API key settings
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function settings( $rcp_options ) {
		echo 23423;die;
		?>
		<table class="form-table">
			<tr valign="top">
				<th>
					<label for="rcp_settings[hs_secret_key]"><?php _e( 'Help Scout Secret Key', 'rcp-help-scout' ); ?></label>
				</th>
				<td>
					<input id="rcp_settings[hs_secret_key]" style="width: 300px;" name="rcp_settings[hs_secret_key]" type="text" value="<?php if( isset( $rcp_options['hs_secret_key'] ) ) echo esc_attr( $rcp_options['hs_secret_key'] ); ?>" />
					<div class="description"><?php _e( 'Enter your Help Scout Secret Key. This is defined when creating your custom application.', 'rcp-help-scout' ); ?></div>
				</td>
			</tr>
			<tr valign="top">
				<th>
					<label for="rcp_settings[hs_api_key]"><?php _e( 'Help Scout API Key', 'rcp-help-scout' ); ?></label>
				</th>
				<td>
					<input id="rcp_settings[hs_api_key]" style="width: 300px;" name="rcp_settings[hs_api_key]" type="text" value="<?php if( isset( $rcp_options['hs_api_key'] ) ) echo esc_attr( $rcp_options['hs_api_key'] ); ?>" />
					<div class="description"><?php _e( 'Enter your Help Scout API Key.', 'rcp-help-scout' ); ?></div>
				</td>
			</tr>
			<tr valign="top">
				<th>
					<label for="rcp_settings[hs_email]"><?php _e( 'Help Scout Email Address', 'rcp-help-scout' ); ?></label>
				</th>
				<td>
					<input id="rcp_settings[hs_email]" style="width: 300px;" name="rcp_settings[hs_email]" type="text" value="<?php if( isset( $rcp_options['hs_email'] ) ) echo esc_attr( $rcp_options['hs_email'] ); ?>" />
					<div class="description"><?php _e( 'Enter the email address used by your Help Scout inbox.', 'rcp-help-scout' ); ?></div>
				</td>
			</tr>
		</table>
		<?php
	}

}

function rcp_load_help_scout() {
	$plugin = new RCP_Help_Scout;
	unset( $plugin );
}
add_action( 'plugins_loaded', 'rcp_load_help_scout', 9 );