<?php
/*
Plugin Name: EDD Recuirring Payments for Helpscout
Plugin URI: https://creativeg.gr
Description: View Easy Digital Download Recuirring Payments Subscription Status to Helpscout
Version: 1.0.0
Author: Basilis Kanonidis
Author URI: https://creativeg.gr
Requires at least: 3.9.1
Tested up to: 4.1
Text Domain: edd-helpscout
Domain Path: /languages
*/

class RCP_Help_Scout {

	public $plugin_dir;
	public $plugin_url;

	public function __construct() {
		$this->file         = __FILE__;

		$this->basename     = plugin_basename( $this->file );
		$this->plugin_dir   = plugin_dir_path( $this->file );
		$this->plugin_url   = plugin_dir_url ( $this->file );

		$this->lang_dir     = trailingslashit( $this->plugin_dir . 'languages' );
		$this->domain       = 'edd-helpscout';

		include( 'includes/class-edd-signup.php' );
		include( 'includes/class-edd-helpscout-customer.php' );

	}

}

new RCP_Help_Scout;