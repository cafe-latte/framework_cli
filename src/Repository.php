<?php

namespace Cafelatte\PackageManager;


class Repository
{
    CONST URL_DEVELOP = "http://dev.www.cafe-latte.co.kr";
    CONST URL_PRODUCT = "http://www.cafe-latte.co.kr";
    CONST TEMP_PATH = "./tmp";




    public static function doCurl($url, $method = 'POST', $params){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
        } else {
            curl_setopt($ch, CURLOPT_POST, false);
        }

        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $result = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $result;

    }

}
