<?php
class HelpScoutCustomApi
{
    public function __construct($api_key = '')
    {
        $this->api_key = $api_key;
    }

    public function createCustomer($fields)
    {
        return $this->sendRequest('https://api.helpscout.net/v2/customers', 'POST', '', $fields);
    }

    public function createEmail($customerid, $fields)
    {
        return $this->sendRequest('https://api.helpscout.net/v2/customers/' . $customerid . '/emails', 'POST', '', $fields);
    }

    private function sendRequest($url, $method = 'GET', $page = 1, $fields = null)
    {
        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-type' => 'application/json; charset=UTF-8',
            ),
        );
        $args['body'] = json_encode($fields);
        $args['timeout'] = 40;
        $response = wp_remote_request($url, $args);
        if ($response['response']['code'] == '401') {
            return false; // Bail early
        }

       $headers = wp_remote_retrieve_headers($response)->getAll();
       return $headers;
    }

    private function request($url, $method = 'GET', $page = 1, $fields = null)
    {
        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-type' => 'application/json; charset=UTF-8',
            ),
            'body' => array(
                'page' => $page,
            ),
        );
        if ($fields) {
            foreach ($fields as $key => $field) {
                $args['body'][$key] = $field;
            }
        }

        $args['timeout'] = 40;
        $response = wp_remote_request($url, $args);
        if ($response['response']['code'] == '401') {
            return false; // Bail early
        }

        $data = json_decode($response['body'], true);
        return $data;
    }

    public function getAccessToken($url, $clientId, $clientSecret)
    {
        $args = array(
            'method' => 'POST',
            'body' => array(
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ),
        );
        $response = wp_remote_post($url, $args);
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            return false;

        } else {
            return json_decode($response['body'], true);

        }
    }
}
