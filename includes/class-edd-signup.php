<?php

/*
 * Registers customers in Help Scout when they register through RCP
 *
 */

include_once( 'src/HelpScout/ApiClient.php' );

use HelpScout\ApiClient;

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
			$client = ApiClient::getInstance();
			$client->setKey( HELPSCOUT_SUPPORT_API_KEY );

			$customer = new \HelpScout\model\Customer();
			$customer->setFirstName( $user->user_firstname );
			$customer->setLastName( $user->user_lastname );

			// Emails: at least one email is required
			$emailWork = new \HelpScout\model\customer\EmailEntry();
			$emailWork->setValue( $user->user_email );
			$emailWork->setLocation("work");

			$customer->setEmails(array($emailWork));

			$client->createCustomer($customer);
		} catch( Exception $e ) {

		}
	}

}

new PW_EDD_Help_Scout_Signup;