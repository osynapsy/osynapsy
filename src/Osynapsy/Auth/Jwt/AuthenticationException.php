<?php
namespace Osynapsy\Auth\Jwt;

class AuthenticationException extends \Osynapsy\Kernel\KernelException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
    }
}
