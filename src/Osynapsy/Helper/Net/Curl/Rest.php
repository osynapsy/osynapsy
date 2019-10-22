<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Helper\Net\Curl;

class Rest
{
    private static $baseurl = '';
    
    private static function init($url, $rawheaders, $proxy = null)
    {
        $ch = curl_init(self::$baseurl.$url);
        if (!empty($proxy)) {
            $proxy = explode(':', $proxy);
            curl_setopt($ch, \CURLOPT_PROXY, $proxy[0]);
            curl_setopt($ch, \CURLOPT_PROXYPORT, $proxy[1]);
        }
        self::appendHeaders($ch, $rawheaders);
        curl_setopt($ch, \CURLOPT_COOKIEFILE, true);
        curl_setopt($ch, \CURLOPT_COOKIEJAR, true);
        curl_setopt($ch, \CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, \CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, \CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.131 Safari/537.36');
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        return $ch;
    }
    
    private function appendHeaders($channel, $rawheaders)
    {
        if (empty($rawheaders)) {
            return;
        }
        $headers = [];
        foreach($rawheaders as $key => $value) {
            $headers[] = strtolower($key).': '.$value; 
        }
        curl_setopt($channel, \CURLOPT_HTTPHEADER, $headers);
    }
    
    public static function get($url, $data = [], $headers = [], $proxy = null)
    {        
        if (!empty($data)) {
            $url .= '?'.http_build_query($data);
        }
        $ch = self::init($url, $headers);
        curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, "GET");        
        return self::getResponse($ch);  
    }
    
    private static function getResponse($channel)
    {
        $resp = curl_exec($channel);
        
        $contentType = curl_getinfo($channel, CURLINFO_CONTENT_TYPE);
        $httpCode    = curl_getinfo($channel, CURLINFO_HTTP_CODE);
        if ($resp === false) {
            $resp = curl_errno($channel);
        }
        curl_close($channel);
        
        return ['http-code' => $httpCode, 'content-type' => $contentType, 'response' => $resp];
    }
        
    public static function post($url, $data, $header = null, array $options = [])
    {
        $ch = self::init($url, $header);        
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        foreach($options as $option => $value) {
            curl_setopt($ch, $option, $value);
        }                               
        return self::getResponse($ch);
    }
    
    public static function postJson($url, $data)
    {
        $json = json_encode($data, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
                        
        return self::post($url, $json, [
            'Content-Type' => 'application/json',
            'Content-Length' => strlen($json)
        ]);
    }
    
    public static function setBaseUrl($url)
    {
        self::$baseurl = $url;
    }
}
