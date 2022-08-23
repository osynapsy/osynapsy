<?php
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
