<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\ViewModel\Field;

use Osynapsy\ViewModel\Field\Field;

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
    const ERROR_NOT_UNIQUE = '<value> è già  presente in archivio.';
    const ERROR_LENGTH_EXCEEDS = 'Il campo <fieldname> deve avere una lunghezza massima di %s caratteri';
    const ERROR_LENGTH_MIN = 'Il campo <fieldname> deve avere una lunghezza minima di %s caratteri';
    const ERROR_LENGTH_FIX = 'Il campo <fieldname> accetta solo valori con una lunghezza pari a %s caratteri';

    private $model;
    private $field;

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
            $this->raiseException(self::ERROR_NOT_NULL);
        }
    }

    public function isUnique($field)
    {
        $value = $field->value;
        if (!$field->isUnique() || empty($value)) {
            return;
        }
        $table = $this->getModel()->getTable();
        $numberOfOccurences = $this->getModel()->getDb()->findOne(
            sprintf("SELECT COUNT(*) FROM %s WHERE %s = ?", $table, $field->name),
            [$value]
        );
        if (!empty($numberOfOccurences)) {
            $this->raiseException(self::ERROR_NOT_UNIQUE);
        }
    }

    public function isEmail($value)
    {
        if (!empty($value) && filter_var($value, \FILTER_VALIDATE_EMAIL) === false) {
            $this->raiseException(self::ERROR_NOT_EMAIL);
        }
    }

    public function isFloat($value)
    {
        if ($value && filter_var($value, \FILTER_VALIDATE_FLOAT) === false) {
            $this->raiseException(self::ERROR_NOT_NUMERIC);
        }
    }

    public function isInteger($value)
    {
        if ($value && filter_var($value, \FILTER_VALIDATE_INT) === false) {
            $this->raiseException(self::ERROR_NOT_INTEGER);
        }
    }

    public function validateCharLength($field)
    {
        //Controllo la lunghezza massima della stringa. Se impostata.
        if ($field->maxlength && (strlen($field->value) > $field->maxlength)) {
            $this->raiseException(sprintf(self::ERROR_LENGTH_EXCEEDS, $field->maxlength));
        }
        if ($field->minlength && (strlen($field->value) < $field->minlength)) {
            $this->raiseException(sprintf(self::ERROR_LENGTH_MIN, $field->minlength));
        }
        if ($field->fixlength && !in_array(strlen($field->value), $field->fixlength)) {
            $this->raiseException(sprintf(self::ERROR_LENGTH_FIX, implode(' o ',$field->fixlength)));
        }
    }

    public function validateType(Field $field)
    {
        $value = $field->value;
        switch ($field->type) {
            case Field::TYPE_NUMBER:
                $this->isFloat($value);
                break;
            case Field::TYPE_INTEGER:
                $this->isInteger($value);
                break;
            case Field::TYPE_EMAIL:
                $this->isEmail($value);
                break;
        }
    }

    public function validate(Field $field)
    {
        $this->field = $field;
        $this->isNotNull($field);
        $this->validateCharLength($field);
        $this->isUnique($field);
        $this->validateType($field);
        $this->extraChecks();
    }

    public function extraChecks()
    {
    }

    protected function raiseException($rawErrorMessage)
    {
        throw new \Exception(str_replace(
            ['<fieldname>', '<value>'],
            ['<!--'.$this->field->html.'-->', $this->field->value],
            $rawErrorMessage
        ));
    }
}
