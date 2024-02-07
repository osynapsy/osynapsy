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

use Osynapsy\Controller\ControllerInterface;
use Osynapsy\Database\Record\RecordInterface;

abstract class ModelRecord extends AbstractModel
{
    protected $record;
    protected $softDelete = [];

    public function __construct(ControllerInterface $controller, ...$argv)
    {
        parent::__construct($controller);
        $this->record = $this->record();
        if (method_exists($this, 'init')) {
            autowire()->execute($this, 'init');
        }
        $this->mapFactory();
        $this->fillRecord();
    }

    public function getRecord() : RecordInterface
    {
        return $this->record;
    }

    protected function fillRecord()
    {
        $keys = [];
        foreach($this->fields as $field) {
            if ($field->isPkey()) {
                $keys[$field->name] = $field->getDefaultValue();
            }
        }
        if (!empty($keys)) {
            $this->getRecord()->where($keys);
        }
    }

    public function getTable()
    {
        return $this->getRecord()->table();
    }

    public function getValue($key)
    {
        return $this->getRecord()->get($key);
    }

    public function loadValues()
    {
        $this->LoadValuesInRequest();
        return $this->getRecord();
    }

    public function LoadValuesInRequest()
    {
        $values = $this->getRecord()->get() ?? [];
        foreach($this->fields as $field) {
            if (!array_key_exists($field->html, $_REQUEST) && array_key_exists($field->name, $values)) {
                $_REQUEST[$field->html] = $values[$field->name];
            }
        }
    }

    public function save() : bool
    {
        //Recall before exec method with arbirtary code
        $this->addError($this->beforeSave());
        //Init arrays
        $values = $this->valuesFactory();
        //If occurred some error stop db updating
        if ($this->getResponse()->error()) {
            return false;
        }
        //If behavior of the record is insert exec insert
        switch ($this->getRecord()->getBehavior()) {
            case RecordInterface::BEHAVIOR_INSERT:
                $this->insert($values);
                break;
            case RecordInterface::BEHAVIOR_UPDATE:
                $this->update($values);
                break;
        }
        //Recall after exec method with arbirtary code
        $this->afterSave();
        $this->afterExec();
        return true;
    }

    protected function valuesFactory()
    {
        $values = [];
        //skim the field list for check value and build $values, $where and $key list
        $validator = new Field\Validator($this);
        foreach ($this->fields as $field) {
            //Check if value respect rule
            $value = $this->validateFieldValue($field, $validator);
            if (!$field->existInForm() && !$field->getDefaultValue() && $this->getRecord()->getBehavior() != RecordInterface::BEHAVIOR_INSERT) {
                continue;
            }
            //If field isn't in readonly mode assign values to values list for store it in db
            if (!$field->readonly && $field->name) {
                $values[$field->name] = $value;
            }
        }
        return $values;
    }

    protected function insert(array $values)
    {
        if ($this->addError($this->beforeInsert())) {
            return;
        }
        $lastId = $this->getRecord()->save($values);
        $this->afterInsert($lastId);
    }

    protected function update(array $values)
    {
        if ($this->addError($this->beforeUpdate())) {
            return;
        }
        $id = $this->getRecord()->save($values);
        $this->afterUpdate($id);
    }

    public function delete() : bool
    {
        if ($this->addError($this->beforeDelete())) {
            return false;
        }
        if (empty($this->softDelete)) {
            $this->getRecord()->delete();
        } else {
            $this->getRecord()->save($this->softDelete);
        }
        $this->afterDelete();
        $this->afterExec();
        return true;
    }

    public function setValue($fieldName, $value, $defaultValue = null)
    {
        $this->field[$fieldName]->setValue($value, $defaultValue);
    }

    protected function softDelete($field, $value)
    {
        $this->softDelete[$field] = $value;
    }

    abstract protected function record();

    abstract protected function mapFactory();
}
