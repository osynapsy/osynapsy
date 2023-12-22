<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Http\Response;

/**
 * Implements Json response
 *
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class Json extends AbstractResponse
{
    protected $body;

    public function __construct()
    {
        parent::__construct('application/json; charset=utf-8');
    }
    /**
     * Implements abstract method for build response
     *
     * @return json string
     */
    public function __toString()
    {
        $this->sendHeader();
        return json_encode($this->body);
    }

    /**
     * Dispatch immediatly response
     */
    public function dispatch()
    {
        ob_clean();
        $this->sendHeader();
        die(json_encode($this->body));
    }

    public function add($body)
    {
        $this->body = $body;
    }
}
