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

use Osynapsy\Mvc\Controller\InterfaceController;
use Osynapsy\Mvc\Model\BaseModel;

abstract class ModelRecord extends BaseModel
{
    private $record;

    public function __construct(InterfaceController $controller)
    {
        parent::__construct($controller);
        $this->record = $this->record();
        $this->init();
        $this->recordFill();
        $this->afterInit();
    }

    public function getRecord()
    {
        return $this->record;
    }

    public function getTable()
    {
        return $this->getRecord()->table();
    }

    public function getValue($key)
    {
        return $this->getRecord()->getValue($key);
    }

    private function recordFill()
    {
        $keys = [];
        foreach($this->fields as $field) {
            if ($field->isPkey()) {
                $keys[$field->name] = $field->getDefaultValue();
            }
        }
        if (!empty($keys)) {
            $this->getRecord()->findByAttributes($keys);
        }
    }

    public function find()
    {
        $this->loadRecordValuesInRequest();
        return $this->getRecord();
    }

    public function loadRecordValuesInRequest()
    {
        $values = $this->getRecord()->get();
        if (empty($values)) {
            return;
        }
        foreach($this->fields as $field) {
            if (!array_key_exists($field->html, $_REQUEST) && array_key_exists($field->name, $values)) {
                $_REQUEST[$field->html] = $values[$field->name];
            }
        }
    }

    protected function insert(array $values, $keys)
    {
        if ($this->addError($this->beforeInsert())) {
            return;
        }
        $lastId = $this->getRecord()->save($values);
        $this->afterInsert($lastId);
    }

    protected function update(array $values, $where)
    {
        $this->addError($this->beforeUpdate());
        $id = $this->getRecord()->save($values);
        $this->afterUpdate($id);
    }

    public function delete()
    {
        if ($this->addError($this->beforeDelete())) {
            return;
        }
        $this->getRecord()->delete();
        $this->afterDelete();
    }

    public function setValue($fieldName, $value, $defaultValue = null)
    {
        $this->field[$fieldName]->setValue($value, $defaultValue);
    }

    abstract protected function record();
}
