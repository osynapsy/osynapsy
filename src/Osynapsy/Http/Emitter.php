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

use Psr\Http\Message\ResponseInterface;

/**
 * Description of Emitter
 *
 * @author pietro
 */
class Emitter
{
    protected $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function get()
    {
        foreach($this->response->getHeaders() as $headerName => $header) {
            header(ucwords($headerName, '-'), implode(',', $header));
        }
        return $this->response->getBody()->getContents();
    }

    public function __toString()
    {
        return $this->get();
    }
}
