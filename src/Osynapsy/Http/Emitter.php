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

//use Psr\Http\Message\ResponseInterface;
use Osynapsy\Http\Response\ResponseInterface;

/**
 * Description of Emitter
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class Emitter
{
    protected $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function emit() : string
    {
        $this->emitHeaders($this->response->getHeaders());
        return strval($this->response->getBody());
    }

    protected function emitHeaders($headers) : void
    {
        if (!headers_sent() && !empty($headers)) {
            foreach ($headers as $key => $value) {
               header($key.': '.(is_array($value) ? implode(', ', $value) : $value));
            }
        }
    }
}
