<?php


namespace App\Providers;

class CurlServiceProvider
{

    /**
     * @param $url
     * @param $data
     * @return array
     */
    public function curl($url = "", $ssl = true, $data = [], $method = "GET",$headers = []){

        if (!extension_loaded("curl")) {
            return "Curl Extension Not Found!";
        }


        $ssl == true ? '' : $ssl = false;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_PORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $ssl);

        if(strtoupper($method) == "POST"){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            "code" => $httpcode,
            "result" => $result
        ];
    }
}


