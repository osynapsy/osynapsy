<?php
namespace Osynapsy\Http\Psr7\Message;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\MessageInterface;

/**
 * Description of Response
 *
 * @author pietro
 */
class Response extends Message implements ResponseInterface, MessageInterface
{
    const STATUS_CODE_WITH_PHRASES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
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
        413 => 'Content Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Content',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        427 => 'Unassigned',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication'
    ];
    private $statusCode;
    private $reasonPhase = '';

    public function __construct(int $statusCode = 200, array $headers = [], $body = null, $protocol = '1.1')
    {
        $this = $this->withProtocolVersion($protocol);
        $this->setStatusCode($statusCode);
        if (!empty($headers)) {
            $this->setHeaders($headers);
        }
        if (!is_null($body)) {
            $this->setBody($body);
        }
    }

    protected function setStatusCode($code, $phrase = null)
    {
        $this->validateStatusCode($code);
        $this->statusCode = (int) $code;
        $this->reasonPhase = $phrase ?? self::STATUS_CODE_WITH_PHRASES[(int) $code] ?? '';
    }

    protected function validateStatusCode($code)
    {
        if (filter_var($code, FILTER_VALIDATE_INT) === false) {
            throw new \InvalidArgumentException('Status code must be an integer value.');
        }
        if ($code < 100 || $code >= 600) {
            throw new \InvalidArgumentException('Status code must be an value between 1xx and 5xx.');
        }
    }

    public function withStatus($code, $reasonPhrase = '') : ResponseInterface
    {
        $this->validateStatusCode($code);
        $result = clone $this;
        $result->setStatusCode($code, empty($reasonPhrase) ? null : $reasonPhrase);
        return $result;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhase;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
