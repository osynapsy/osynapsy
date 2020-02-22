<?php
namespace Osynapsy\Mvc\Model;

use Osynapsy\Mvc\ModelField;

/**
 * Description of ModelFieldCheck
 *
 * @author pietr
 */
class Validator
{    
    const ERROR_NOT_EMAIL = 'Il campo <fieldname> non contiene un indirizzo mail valido.';
    const ERROR_NOT_NULL = 'Il campo <fieldname> è obbligatorio.';
    const ERROR_NOT_NUMERIC = 'Il campo <fieldname> accetta solo valori numerici.';
    const ERROR_NOT_INTEGER = 'Il campo <fieldname> accetta solo numeri interi.';
    const ERROR_NOT_UNIQUE = '<value> è già presente in archivio.';
    const ERROR_LENGTH_EXCEEDS = 'Il campo <fieldname> accetta massimo ';
    const ERROR_LENGTH_MIN = 'Il campo <fieldname> accetta minimo';
    const ERROR_LENGTH_FIX = 'Il campo <fieldname> solo valori con lunghezza pari a ';
     
    private $model;
    
    public function __construct($model)
    {
        $this->model = $model;
    }
    
    public function getModel()
    {
        return $this->model;
    }
    
    public function isNotNull($field)
    {
        $value = $field->value;
        if (!$field->isNullable() && $value !== '0' && empty($value)) {
            throw new \Exception(self::ERROR_NOT_NULL);            
        }
    }
   
    public function isUnique($field)
    {
        $value = $field->value;
        if (!$field->isUnique() || empty($value)) {
            return;
        }
        $numberOfOccurences = $this->getModel()->getDb()->execUnique(
            "SELECT COUNT(*) FROM {$this->getRecord()->table()} WHERE {$field->name} = ?",
            [$value]
        );
        if (!empty($numberOfOccurences)) {
            throw new \Exception(self::ERROR_NOT_UNIQUE);
        }
    }
    
    public function isEmail($value)
    {
        if (!empty($value) && filter_var($value, \FILTER_VALIDATE_EMAIL) === false) {
            throw new \Exception(self::ERROR_NOT_EMAIL);
        }
    }
    
    public function isFloat($value)
    {
        if ($value && filter_var($value, \FILTER_VALIDATE_FLOAT) === false) {
            throw new \Exception(self::ERROR_NOT_NUMERIC);
        }
    }
    
    public function isInteger($value)
    {
        if ($value && filter_var($value, \FILTER_VALIDATE_INT) === false) {
            throw new \Exception(self::ERROR_NOT_INTEGER);                    
        }
    }
    
    public function validateCharLength($field)
    {
        //Controllo la lunghezza massima della stringa. Se impostata.
        if ($field->maxlength && (strlen($field->value) > $field->maxlength)) {
            throw new \Exception(self::ERROR_LENGTH_EXCEEDS . $field->maxlength . ' caratteri');
        }
        if ($field->minlength && (strlen($field->value) < $field->minlength)) {
            throw new \Exception(self::ERROR_LENGTH_MIN . $field->minlength . ' caratteri');            
        }
        if ($field->fixlength && !in_array(strlen($field->value), $field->fixlength)) {
            throw new \Exception(self::ERROR_LENGTH_FIX . implode(' o ',$field->fixlength).' caratteri');                    
        }
    }
    
    public function validateType(ModelField $field)
    {   
        $value = $field->value;
        switch ($field->type) {
            case ModelField::TYPE_NUMBER:
                $this->isFloat($value);
                break;
            case ModelField::TYPE_INTEGER:            
                $this->isInteger($value);
                break;
            case ModelField::TYPE_EMAIL:
                $this->isEmail($value);
                break;
        }
    }
    
    public function validate(ModelField $field)
    {
        $this->isNotNull($field);
        $this->validateCharLength($field);
        $this->isUnique($field);
        $this->validateType($field);
    }
}
