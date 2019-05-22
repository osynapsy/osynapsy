<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Network;

class Rest
{
    public static function get($url, $proxy = null)
    {
        $ch = curl_init($url);
        if (!empty($proxy)) {
            $proxy = explode(':', $proxy);
            curl_setopt($ch, CURLOPT_PROXY, $proxy[0]);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy[1]);
        }
        curl_setopt($ch, \CURLOPT_COOKIEFILE, true);
        curl_setopt($ch, \CURLOPT_COOKIEJAR, true);
               curl_setopt($ch, \CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, \CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, \CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.131 Safari/537.36');
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, "GET");
        //curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
       return curl_exec($ch);  
    }
    
    public static function post($url, $data, $header = null, array $options = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_HEADER,true);
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, \CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, \CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, \CURLOPT_COOKIEFILE, true);
        curl_setopt($ch, \CURLOPT_COOKIEJAR, true);
        curl_setopt($ch, \CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.131 Safari/537.36');
        //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        foreach($options as $option => $value) {
            curl_setopt($ch, $option, $value);
        }
        $resp = curl_exec($ch);
        
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        
        if ($resp === false) {
            $resp = curl_errno($ch);
        }
        curl_close($ch);
        
        return array($contentType, $resp);
    }
    
    public static function postJson($url, $data)
    {
        $json = json_encode($data, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
        
        $header = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json)
        );
        
        return self::post($url, $json, $header);
    }
}
