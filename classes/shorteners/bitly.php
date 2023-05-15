<?php

/**
 * SAP Bitly Class
 * 
 * @package Social Auto Poster
 * @since 1.0.0
 */
class SAP_Bitly_Url {

    public $name, $access_token;

    function __construct($access_token) {
        $this->name = 'bitly';
        $this->access_token = $access_token;
    }

    /**
     * Bitly API
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function shorten($pageurl) {

        $apiv4 = 'https://api-ssl.bitly.com/v4/shorten';
        $data = array(
            'long_url' => $pageurl
        );
        $payload = json_encode($data);

        $header = array(
            'Authorization: Bearer ' . $this->access_token,
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        );

        $ch = curl_init($apiv4);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        $resultToJson = json_decode($result);


        if (isset($resultToJson->link)) {

            return $resultToJson->link;
        }

        return $pageurl;
    }

}
