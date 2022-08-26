<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc\Model;

use Osynapsy\Mvc\Model\Field;

/**
 * Description of ModelErrorException
 *
 * @author Pietro
 */
class ModelErrorException extends \Exception
{
    private $errors = [];

    public function setError($message)
    {
        $this->errors[] = $message;
        $this->appendToMessage($message);
    }

    public function setErrorOnField(Field $field, $rawErrorMessage)
    {
        $errorMessage = str_replace(
            ['<fieldname>', '<value>'],
            ['<!--'.$field->html.'-->', $field->value],
            $rawErrorMessage
        );
        $this->errors[$field->html] = $errorMessage;
        $this->appendToMessage($errorMessage);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function appendToMessage($message)
    {
        $this->message .= PHP_EOL.$message;
    }
}
