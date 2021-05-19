<?php
class ProcesaAPI
{
    public $service_url;

    public function __construct()
    {
        $this->service_url = "http://localhost";
    }

    public function getService($method, $params = array())
    {
        $this->service_url = $this->service_url . '/' . $method;

        $curl = curl_init($this->service_url);
        $curl_post_data = $params;
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        #verify if array is not empty
        if (!empty($params)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
        }
        $curl_response = curl_exec($curl);

        if ($curl_response === false) {
            $info = curl_getinfo($curl);
            curl_close($curl);
            die('error occured during curl exec. Additioanl info: ' . var_export($info));
        }


        $result = $curl_response;

        return $result;
    }
    public function __destruct() {
        
    }

}