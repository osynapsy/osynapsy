<?php
namespace Osynapsy\Mvc\Application\RequestHandler;

use Osynapsy\Http\Response\JsonOsynapsy as JsonOsynapsyResponse;
use Osynapsy\Http\Response\Html as HtmlResponse;
use Osynapsy\Http\Response\Xml as XmlResponse;

/**
 * Description of RequestHandler
 *
 * @author Pietro
 */
class RequestHandler
{
    protected $serverRequest;

    public function __construct($serverRequest)
    {
        $this->serverRequest = $serverRequest;
    }

    public function handle($request)
    {
        $accept = $request->getAcceptedContentType();
        if (empty($accept)) {
            $accept = ['text/html'];
        }
        switch($accept[0]) {
            case 'text/json':
            case 'application/json':
            case 'application/json-osynapsy':
                ini_set("xdebug.overload_var_dump", "off");
                return new JsonOsynapsyResponse();
            case 'application/xml':
                ini_set("xdebug.overload_var_dump", "off");
                return new XmlResponse();
            default:
                return new HtmlResponse();
        }
    }
}
