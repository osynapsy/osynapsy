<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\ViewModel;

/**
 * Description of ModelErrorException
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class ModelErrorException extends \Exception
{
    protected $errors = [];

    public function addError($message)
    {
        $this->errors[] = $message;
        $this->appendToMessage($message);
    }

    public function addErrorOnField($field, $errorMessage)
    {
        $this->errors[$field] = $errorMessage;
        $this->appendToMessage($errorMessage);
    }

    public function getErrors() : array
    {
        return array_filter($this->errors);
    }

    public function hasErrors() : bool
    {
        return !empty(array_filter($this->errors));
    }

    public function appendToMessage($message) : void
    {
        $this->message .= (!empty($this->message) ? PHP_EOL : '') . $message;
    }
}
