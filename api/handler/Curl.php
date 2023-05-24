<?php

namespace handler;

class Curl
{
    public function addAction($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        return curl_exec($ch);
    }
    public function updateAction($url, $data)
    {
        $ch = curl_init();
        $data=json_encode($data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'put');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        return curl_exec($ch);
    }

}
