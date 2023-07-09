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
use Osynapsy\Html\DOM;

abstract class ModelRecord extends AbstractModel
{
    protected $record;
    protected $softDelete = [];

    public function __construct(ControllerInterface $controller, ...$argv)
    {
        parent::__construct($controller, $argv);
        $this->record = $this->record();
        autowire()->execute($this, 'init');
        $this->recordFill();
        $this->afterInit();
    }

    public function getRecord() : RecordInterface
    {
        return $this->record;
    }

    public function getTable()
    {
        return $this->getRecord()->table();
    }

    public function getValue($key)
    {
        return $this->getRecord()->get($key);
    }

    protected function recordFill()
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
            if (array_key_exists($field->name, $values)) {
                DOM::setValue($field->html, $values[$field->name]);
            }
        }
    }

    public function save()
    {
        //Recall before exec method with arbirtary code
        $this->addError($this->beforeSave());
        //Init arrays
        $values = $this->valuesFactory();
        //If occurred some error stop db updating
        if ($this->getResponse()->error()) {
            return;
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
    }

    protected function valuesFactory()
    {
        $values = [];
        //skim the field list for check value and build $values, $where and $key list
        $validator = new Field\Validator($this);
        foreach ($this->fields as $field) {
            //Check if value respect rule
            $value = $this->validateFieldValue($field, $validator);
            if (in_array($field->type, ['file', 'image'])) {
                $value = $this->grabUploadedFile($field);
            }
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

    public function delete()
    {
        if ($this->addError($this->beforeDelete())) {
            return;
        }
        if (empty($this->softDelete)) {
            $this->getRecord()->delete();
        } else {
            $this->getRecord()->save($this->softDelete);
        }
        $this->afterDelete();
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
}
