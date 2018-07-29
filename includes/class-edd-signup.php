<?php

/*
 * Registers customers in Help Scout when they register through RCP
 *
 */
include(  plugin_dir_path( __FILE__ ).'../HelpScoutCustomApi.php' );

class PW_EDD_Help_Scout_Signup {

	public function __construct() {
		add_action( 'edd_complete_purchase', array( $this, 'edd_create_user_after_payment' ), 10, 3 );
	}

	public function edd_create_user_after_payment( $payment_id ) {
        $user_id    = edd_get_payment_customer_id( $payment_id );
		$this->helpscout( $user_id );
	}

	public function helpscout($user_id) {
		$user = new WP_User( $user_id );

		try {

			$customer = new HelpScoutCustomApi(HELPSCOUT_SUPPORT_API_KEY);
			$fields['firstName'] = $user->user_firstname;
			$fields['lastName'] = $user->user_lastname;
	
			// Emails: at least one email is required
			$emailWork[] = array('value'=>$user->user_email,'location'=>'work');
			$fields['emails'] = $emailWork;
			$customer->createCustomer($fields);
		} catch( Exception $e ) {

		}
	}

}

new PW_EDD_Help_Scout_Signup;