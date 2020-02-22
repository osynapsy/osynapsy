<?php
namespace Osynapsy\Mvc\Model;

use Osynapsy\Mvc\ModelField;

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
    }
    
    public function setErrorOnField(ModelField $field, $errorMessage)
    {
        $this->errors[$field->html] = str_replace(
            ['<fieldname>', '<value>'],
            ['<!--'.$field->html.'-->', $field->value],
            $errorMessage
        );
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
