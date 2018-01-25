<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Http;

/**
 * Implements Json response
 * 
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class ResponseJson extends Response 
{
    public function __construct()
    {
        parent::__construct('text/json; charset=utf-8');
    }
    /**
     * Implements abstract method for build response
     * 
     * @return json string
     */
    public function __toString()
    {
        $this->sendHeader();
        return json_encode($this->repo['content']);
    }
    
    public function debug($msg)
    {
        $this->message('errors','alert',$msg);
        $this->dispatch();
    }
    
    /**
     * Dispatch immediatly response
     */
    public function dispatch()
    {
        ob_clean();
        $this->sendHeader();
        die(json_encode($this->repo['content']));
    }
    
    /**
     * Store a error message
     * 
     * If recall without parameter return if errors exists.
     * If recall with only $oid parameter return if error $oid exists
     * If recall it with $oid e $err parameter set error $err on key $oid.
     * 
     * @param string $oid
     * @param string $err
     * @return type
     */
    public function error($oid=null, $err=null)
    {
        if (is_null($oid) && is_null($err)){
            return array_key_exists('errors',$this->repo['content']);
        }
        if (!is_null($oid) && is_null($err)){
            return array_key_exists('errors', $this->repo['content']) && array_key_exists($oid, $this->repo['content']['errors']);
        }         
        if (function_exists('mb_detect_encoding') && !mb_detect_encoding($err, 'UTF-8', true)) {        
            $err = \utf8_encode($err);
        }
        $this->message('errors', $oid, $err);
    }
    
    /**
     * Prepare a goto message for FormController.js
     * 
     * If $immediate = true dispatch of the response is immediate     
     * 
     * @param string $url
     * @param bool $immediate
     */
    public function go($url, $immediate = true)
    {
        $this->message('command', 'goto', $url);
        if ($immediate) { 
            $this->dispatch(); 
        }
    }

    /**
     * Append a generic messagge to the response
     * 
     * @param string $typ
     * @param string $act
     * @param string $val
     */
    public function message($typ, $act, $val)
    {
        if (!array_key_exists($typ, $this->repo['content'])){
            $this->repo['content'][$typ] = array();
        }
        $this->repo['content'][$typ][] = array($act,$val);
    }
    
    public function js($cmd)
    {
        $this->message('command','execCode', str_replace(PHP_EOL,'\n',$cmd));
    }
}
