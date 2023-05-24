<?php

namespace handler;

class Token
{
    public function getToken($role)
    {
        $url = "http://172.24.0.4/index/getToken";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "role=$role");
        return curl_exec($ch);
    }
    public function decodeToken($token)
    {
        $url = "http://172.24.0.4/index/decodeToken";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "token=$token");
        return curl_exec($ch);
    }
}
