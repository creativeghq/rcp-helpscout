<?php

/*
 * Registers customers in Help Scout when they register through RCP
 *
 */

if (!class_exists('Curl')) {

    include_once RCP_HELPSCOUT_PLUGIN_DIR . 'includes/src/curl.php';

}

if (!class_exists('CurlResponse')) {

    include_once RCP_HELPSCOUT_PLUGIN_DIR . 'includes/src/curlResponse.php';

}

include_once RCP_HELPSCOUT_PLUGIN_DIR . 'includes/class-rcp-help-scout-api.php';

class PW_RCP_Help_Scout_Signup
{

    private $api_key = '';
    private $secret  = '';
    private $email   = '';

    public function __construct()
    {

        global $rcp_options;

        if (!empty($rcp_options['hs_api_key'])) {
            $this->api_key = trim($rcp_options['hs_api_key']);
        }

        if (!empty($rcp_options['hs_secret_key'])) {
            $this->secret = trim($rcp_options['hs_secret_key']);
        }

        if (!empty($rcp_options['hs_email'])) {
            $this->email = trim($rcp_options['hs_email']);
        }

        add_action('rcp_form_processing', array($this, 'rcp_form_processing'), 10, 3);
    }

    public function rcp_form_processing($postdata, $user_id, $price)
    {
        $this->create_customer($user_id);
    }

    public function create_customer($user_id)
    {

        $objhelp = new PW_RCP_Help_Scout_Api();
        $return  = $objhelp->getAccessToken('https://api.helpscout.net/v2/oauth2/token', $this->api_key, $this->secret);
        if ($return) {
            $token = $return['access_token'];
            $user  = new WP_User($user_id);

            try {
                $objhelp             = new PW_RCP_Help_Scout_Api($token);
                $fields['firstName'] = $user->user_firstname;
                $fields['lastname']  = $user->user_lastname;

                $result = $objhelp->createCustomer($fields);
                if (isset($result['Resource-ID'])) {
                    //adding email to customer
                    $emailfields['type']  = 'work';
                    $emailfields['value'] = $user->user_email;
                    $customerid           = $result['Resource-ID'];
                    $objhelp->createEmail($customerid,$emailfields);
                }

                // $customer = new \HelpScout\model\Customer();
                // $customer->setFirstName( $user->user_firstname );
                // $customer->setLastName( $user->user_lastname );

                // // Emails: at least one email is required
                // $emailWork = new \HelpScout\model\customer\EmailEntry();
                // $emailWork->setValue( $user->user_email );
                // $emailWork->setLocation( "work" );

                // $customer->setEmails( array( $emailWork ) );

                // $client->createCustomer( $customer );

            } catch (Exception $e) {

            }

        } else {

        }

    }

}
new PW_RCP_Help_Scout_Signup;
