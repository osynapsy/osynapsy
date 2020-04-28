<?php
namespace Osynapsy\Kernel\Error\Page;

/**
 * Description of Html
 *
 * @author Pietro
 */
class Html
{
    public $code;
    public $message;
    
    public function __construct($code, $message)
    {
        $this->code = $code;
        $this->message = $message;
    }
    
    public function factory()
    {
        
    }
}
