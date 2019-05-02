<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Kernel;

/**
 * Description of ErrorDispatcher
 *
 * Class responsible for dispatching and rendering html of Osynapsy Kernel exception.  
 * 
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class ErrorDispatcher
{
    private $httpStatusCodes = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        419 => 'Authentication Timeout',
        420 => 'Enhance Your Calm',
        420 => 'Method Failure',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        424 => 'Method Failure',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'No Response',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        451 => 'Redirect',
        451 => 'Unavailable For Legal Reasons',
        494 => 'Request Header Too Large',
        495 => 'Cert Error',
        496 => 'No Cert',
        497 => 'HTTP to HTTPS',
        499 => 'Client Closed Request',
        500 => 'Internal Server Error', //Server Error
        501 => 'Not Implemented', //Server Error
        502 => 'Bad Gateway', //Server Error
        503 => 'Service Unavailable', //Server Error
        504 => 'Gateway Timeout', //Server Error
        505 => 'HTTP Version Not Supported', //Server Error
        506 => 'Variant Also Negotiates', //Server Error
        507 => 'Insufficient Storage', //Server Error
        508 => 'Loop Detected', //Server Error
        509 => 'Bandwidth Limit Exceeded', //Server Error
        510 => 'Not Extended', //Server Error
        511 => 'Network Authentication Required', //Server Error
        598 => 'Network read timeout error', //Server Error
        599 => 'Network connect timeout error' //Server Error
    ];
    private $request;
    private $response;
        
    public function __construct($request)
    {
        $this->request = $request;                
    }
    
    public function dispatchException(\Exception $e)
    {
        switch($e->getCode()) {
            case '403':
            case '404':
                $this->response = $this->pageHttpError($e->getCode(), $e->getMessage());
                break;
            case '501':
                $this->response = $this->pageTraceError($e->getMessage());
                break;
            default :
                $this->response = $this->pageTraceError($e->getMessage(), $e->getTrace());
                break;
        }
        return $this->get();
    }
    
    public function dispatchError(\Error $e)
    {
        $this->response = $this->pageTraceError($e->getMessage(), $e->getTrace());
        return $this->get();
    }
    
    public function pageHttpError($errorCode, $message = 'Page not found')
    {
        ob_clean();
        header("HTTP/1.1 {$errorCode} {$this->httpStatusCodes[$errorCode]}");
        return $message;
    }
    
    public function pageTraceError($message, $trace = [])
    {
        ob_clean();
        if (filter_input(\INPUT_SERVER, 'HTTP_OSYNAPSY_ACTION')) {
            return $this->pageTraceErrorText($message, $trace);
        }
        return $this->pageTraceErrorHtml($message, $trace);
    }
    
    private function pageTraceErrorHtml($message, $trace)
    {
        $body = '';
        if (!empty($trace)) {
            $body .= '<table style="border-collapse: collapse;">';
            $body .= '<tr>';
            $body .= '<th>Class</th>';
            $body .= '<th>Function</th>';
            $body .= '<th>File</th>';
            $body .= '<th>Line</th>';
            $body .= '</tr>';
            foreach ($trace as $step) {
                $body .= '<tr>';
                $body .= '<td>'.(!empty($step['class']) ? $step['class'] : '&nbsp;').'</td>';
                $body .= '<td>'.(!empty($step['function']) ? $step['function'] : '&nbsp;').'</td>';
                $body .= '<td>'.(!empty($step['file']) ? $step['file'] : '&nbsp;').'</td>';
                $body .= '<td>'.(!empty($step['line']) ? $step['line'] : '&nbsp;').'</td>';            
                $body .= '</tr>';            
            }
            $body .= '</table>';
        }
        return <<<PAGE
            <div class="container">       
                <div class="message">{$message}</div>
                {$body}
            </div>
            <style>
                * {font-family: Arial;}
                body {margin: 0px;}
                div.container {margin: 0px; max-width: 1024px; margin: auto;}
                table {width: 100%; margin-top: 20px;}
                .message {background-color: #B0413E; color: white; padding: 10px; font-weight: bold;}
                td,th {font-size: 12px; font-family: Arial; padding: 3px; border: 0.5px solid silver}
            </style>
PAGE;
                    
    }
    
    private function pageTraceErrorText($message, $trace = [])
    {
        $message .= PHP_EOL;
        foreach($trace as $step) {
            if (empty($step['file'])) {
                continue;
            }
            $message .= $step['line'].' - ';
            $message .= $step['file'].PHP_EOL;
        }
        return $message;
    }
    
    public function get()
    {
        return $this->response;
    }
    
    public function __toString()
    {
        return $this->get();
    }
}
