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

/**
 * Description of RequestRaw
 *
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class RequestRaw
{
    public function get()
    {
        $srv = $_SERVER ?? ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/', 'SERVER_PROTOCOL' => 'HTTP/1.0'];
        $req = sprintf("%s %s %s\n", $srv['REQUEST_METHOD'], $srv['REQUEST_URI'], $srv['SERVER_PROTOCOL']);
        $req .= $this->httpBuildHeader($srv)."\n";
        $req .= !empty($_POST) ? http_build_query($_POST) : '';
        return $req;
    }

    private function httpBuildHeader(array $server)
    {
        $headerList = [];
        foreach ($server as $key => $value) {
            if (preg_match('/^HTTP_/', $key)) {
                $headerList[] = sprintf('%s: %s', $this->convertHeaderKey($key), $value);
            }
        }
        return implode("\n", $headerList) . "\n";
    }

    /**
     * convert HTTP_HEADER_NAME to Header-Name
     *
     * @param string $key of php $_SERVER array
     * @return string
     */
    private function convertHeaderKey($key)
    {
        $httpHeaderKey = strtr(substr($key,5),'_',' ');
        $httpHeaderKey = ucwords(strtolower($httpHeaderKey));
        return strtr($httpHeaderKey,' ','-');
    }

    public function __toString()
    {
        return $this->get();
    }
}
