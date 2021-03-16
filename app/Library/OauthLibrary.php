<?php


namespace App\Library;


class OauthLibrary
{
    public static function apiRequest($url, $post=FALSE, $headers=array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $response = curl_exec($ch);

        if($post)
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

        $headers[] = 'Accept: application/json';

//        if(session('access_token'))
//            $headers[] = 'Authorization: Bearer ' . session('access_token');

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

//        dd($ch);

        $response = curl_exec($ch);
        return json_decode($response, true);
    }
}
