<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Http;

class Cookie
{
    protected $expiry;

    public function __construct()
    {
        $this->expiry = time() + (86400 * 365);
    }    

    /**
     * Send cookie
     *
     * @param string $valueId
     * @param string $value
     */
    public function send($valueId, $value, $expiry = null, $excludeThirdLevel = false)
    {
        $domain = $excludeThirdLevel ? $this->getDomain() : $this->getServerName();                
        return headers_sent() ? false : setcookie($valueId, $value, $expiry ?? $this->expiry, "/", $domain);
    }

    private function getDomain()
    {
        $domainPart = explode('.', $this->getServerName());
        if (count($domainPart) > 2){
           unset($domainPart[0]);
        }
        return '.'.implode('.', $domainPart);
    }

    private static function getServerName()
    {
        return filter_input(\INPUT_SERVER, 'SERVER_NAME');
    }
}
