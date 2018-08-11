<?php

/*
 * Registers customers in Help Scout when they register through RCP
 *
 */
include plugin_dir_path(__FILE__) . '../HelpScoutCustomApi.php';

class PW_EDD_Help_Scout_Signup
{

    public function __construct()
    {
        add_action('edd_complete_purchase', array($this, 'edd_create_user_after_payment'), 10, 3);
    }

    public function edd_create_user_after_payment($payment_id)
    {
        $user_id = edd_get_payment_customer_id($payment_id);
        $this->helpscout($user_id);
    }

    public function helpscout($user_id)
    {
        $user = new WP_User($user_id);
        //getting access token
        $objhelp = new HelpScoutCustomApi();
        $return = $objhelp->getAccessToken('https://api.helpscout.net/v2/oauth2/token', HELPSCOUT_SUPPORT_API_KEY, HELPSCOUT_SUPPORT_API_SECRET);

        if ($return) {
            $token = $return['access_token'];
            try {
                $customer = new HelpScoutCustomApi($token);
                $fields['firstName'] = $user->user_firstname;
                $fields['lastName'] = $user->user_lastname;
                $response = $customer->createCustomer($fields);

                if (isset($response['Resource-ID'])) {
                    $customerid = $response['Resource-ID'];
                    $customer['type'] = 'work';
                    $customer['value'] = $user->user_email;
                    $objhelp->createEmail($customerid, $customer);
                }
            } catch (Exception $e) {

            }
        }

    }

}

new PW_EDD_Help_Scout_Signup;
