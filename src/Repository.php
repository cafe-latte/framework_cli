<?php

namespace Cafelatte\PackageManager;


class Repository
{
    const URL_DEVELOP = "https://www.dev.cafe-latte.co.kr";
    const URL_PRODUCT = "https://www.cafe-latte.co.kr";
    const TEMP_PATH = "./tmp";



    /**
     * @param $url
     * @param string $method
     * @param $params
     * @return mixed
     */
    public static function doCurl($url, string $method, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
        } else {
            curl_setopt($ch, CURLOPT_POST, false);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $result = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $result;

    }

}
