<?php

/*
 * Handles the response to Help Scout queries
 *
 */
class PW_EDD_Help_Scout_Plugin_Handler {

	private $input = false;

	/**
	 * Returns the requested HTTP header.
	 *
	 * @param string $header
	 * @return bool|string
	 */
	private function getHeader( $header ) {
		if ( isset( $_SERVER[$header] ) ) {
			return $_SERVER[$header];
		}
		return false;
	}

	/**
	 * Retrieve the JSON input
	 *
	 * @return bool|string
	 */
	private function getJsonString() {
		if ( $this->input === false ) {
			$this->input = @file_get_contents( 'php://input' );
		}
		return $this->input;
	}

	/**
	 * Generate the signature based on the secret key, to compare in isSignatureValid
	 *
	 * @return bool|string
	 */
	private function generateSignature() {
		$str = $this->getJsonString();
		if ( $str ) {
			return base64_encode( hash_hmac( 'sha1', $str, 'rSPqYHmN4KsGxmY4roNKAEttDz9ZaHBfv8Ln4t1v', true ) );
		}
		return false;
	}

	/**
	 * Returns true if the current request is a valid webhook issued from Help Scout, false otherwise.
	 *
	 * @return boolean
	 */
	private function isSignatureValid() {
		$signature = $this->generateSignature();

		if ( !$signature || !$this->getHeader( 'HTTP_X_HELPSCOUT_SIGNATURE' ) )
			return false;

		return $signature == $this->getHeader( 'HTTP_X_HELPSCOUT_SIGNATURE' );
	}

	/**
	 * Create a response.
	 *
	 * @return array
	 */
	public function getResponse() {
		$ret = array( 'html' => '' );

		if ( !$this->isSignatureValid() ) {
			return array( 'html' => 'Invalid signature' );
		}
		$data = json_decode( $this->input, true );

		// do some stuff
		$ret['html'] = $this->fetchHtml( $data );

		// Used for debugging
		// $ret['html'] = '<pre>'.print_r($data,1).'</pre>' . $ret['html'];

		return $ret;
	}
	/**
	 * Generate output for the response.
	 *
	 * @param $data
	 * @return string
	 */
	private function fetchHtml( $data ) {
		global $wpdb;

		if ( isset( $data['customer']['emails'] ) && is_array( $data['customer']['emails'] ) ) {

			if(($key = array_search(HELPSCOUT_EMAIL, $messages)) !== false) {
			    unset($data['customer']['emails'][$key]);
			}

		} else {

			if ( $data['customer']['email'] == HELPSCOUT_EMAIL ) {
				return 'Cannot query customer licenses.  E-mail from ' . HELPSCOUT_EMAIL;
			}

		}

		$email = $data[ 'customer' ][ 'email' ];
		$user = get_user_by( 'email', $email );
        $the_user_id = $user->ID;

		if( ! $user ) {

			return 'No customer found for ' . $email;

		}
        
		$eddinfo = array();
        
        $subscriber    = new EDD_Recurring_Subscriber( $user->ID, true );
	    $subscriptions = $subscriber->get_subscriptions( 0, array( 'active', 'expired', 'cancelled', 'failing', 'trialling' ) );

	    if ( $subscriptions ) :
        foreach ( $subscriptions as $subscription ) :
        $frequency    = EDD_Recurring()->get_pretty_subscription_frequency( $subscription->period );
		$renewal_date = ! empty( $subscription->expiration ) ? date_i18n( get_option( 'date_format' ), strtotime( $subscription->expiration ) ) : __( 'N/A', 'edd-recurring' );
        
        $eddinfo[] = sprintf( '
                                <strong>Subscription: </strong> <br>'.$subscription->get_status_label().'<br>
		                        <strong>Subscription Level: </strong> <br>'.get_the_title( $subscription->product_id ).' <br> '.edd_currency_filter( edd_format_amount( $subscription->recurring_amount ), edd_get_payment_currency_code( $subscription->parent_payment_id ) ) . ' / ' . $frequency.'<br>
                                <strong>Subscription Expiring: </strong><br>'.$renewal_date.'<br>
                                <strong>Times Billed:</strong><br>'.$subscription->get_times_billed() . ' / ' . ( ( $subscription->bill_times == 0 ) ? __( 'Until cancelled', 'edd-recurring' ) : $subscription->bill_times ).'
                               ');
        endforeach;
        endif;
        
        //$html .= '<div class="toggleGroup"><h4><a href="#" class="toggleBtn"><i class="icon-tag"></i><strong>Profile</strong></a> <!-- <a class="toggleBtn"><i class="icon-arrow"></i></a> --> </h4> <div //
        //class="toggle indent">';
        $html .=  '<h4><i class="icon-tag"></i><strong>Profile</strong></h4>';
		$html .= '<ul class="unstyled"><li>';
		$html .= implode( '</li><li>', $eddinfo );
		$html .= '</li></ul>';

		return $html;
	}
}