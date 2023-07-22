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

use Osynapsy\Event\EventLocal;
use Osynapsy\Controller\ControllerInterface;
use Osynapsy\ViewModel\Field\Field;
use Osynapsy\ViewModel\Field\Validator;
use Osynapsy\ViewModel\ModelInterface;
use Osynapsy\Database\Record\RecordInterface;
use Osynapsy\Database\Driver\DboInterface;
use Osynapsy\Html\DOM;

abstract class Record implements ModelInterface
{
    const BEHAVIOR_INSERT = 'insert';
    const BEHAVIOR_UPDATE = 'update';
    const BEHAVIOR_DELETE = 'delete';

    const EVENT_BEFORE_SAVE = 'beforeSave';
    const EVENT_BEFORE_INSERT = 'beforeInsert';
    const EVENT_BEFORE_UPDATE = 'beforeUpdate';
    const EVENT_BEFORE_DELETE = 'beforeDelete';
    const EVENT_AFTER_SAVE = 'afterSave';
    const EVENT_AFTER_INSERT = 'afterInsert';
    const EVENT_AFTER_UPDATE = 'afterUpdate';
    const EVENT_AFTER_DELETE = 'afterDelete';
    const EVENT_AFTER_UPLOAD = 'afterUpload';

    private $fields = [];
    private $record;
    private $exception;
    private $controller;
    private $validator;
    protected $softDelete;
    public $uploadOccurred = false;
    public $behavior;

    public function __construct(ControllerInterface $controller, ...$args)
    {
        $this->controller = $controller;
        $this->record = $this->record();
        $this->initExternalAction();
        if (method_exists($this, 'init')) {
            autowire()->execute($this, 'init');
        }
        $this->mapFactory();
        $this->initRecord();
    }

    protected function dispatchEvent($event)
    {
        $this->getController()->getDispatcher()->dispatch(new EventLocal($event, $this));
    }

    public function addListenerLocal(callable $trigger, array $eventIDs)
    {
        array_walk($eventIDs, function (&$value, $key, $namespace) { $value = $namespace.'\\'.$value;}, get_class($this));
        $this->getController()->getDispatcher()->addListener($trigger, $eventIDs);
    }

    private function initRecord()
    {
        $keys = [];
        foreach($this->fields as $field) {
            if ($field->isPkey()) {
                $keys[$field->name] = $field->getDefaultValue();
            }
        }
        $this->getRecord()->where($keys);
    }

    protected function initExternalAction()
    {
        $this->getController()->setExternalAction(Action\Save::class);
        $this->getController()->setExternalAction(Action\Delete::class);
        $this->getController()->setExternalAction(Action\Upload::class);
        $this->getController()->setExternalAction(Action\DeleteFile::class);
        $this->getController()->setExternalAction(Action\CropImage::class);
    }

    public function getBehavior()
    {
        return $this->getRecord()->getBehavior();
    }

    public function getDb() : DboInterface
    {
        return $this->getController()->getDb();
    }

    public function getController() : ControllerInterface
    {
        return $this->controller;
    }

    public function getException()
    {
        if (empty($this->exception)) {
            $this->exception = new ModelErrorException('Si sono verificati degli errori');
        }
        return $this->exception;
    }

    public function getField($fieldId)
    {
        return $this->fields[$fieldId];
    }

    public function getTable()
    {
        return $this->getRecord()->table();
    }

    public function getLastId()
    {
        return $this->getRecord()->lastAutoincrementId;
    }

    public function getRecord() : RecordInterface
    {
        return $this->record;
    }

    public function getValidator()
    {
        if (empty($this->validator)) {
            $this->validator = new Validator($this);
        }
        return $this->validator;
    }

    public function getValue($key)
    {
        return $this->record->get($key);
    }

    public function loadValues()
    {
        $values = $this->getRecord()->get();
        if (!empty($values)) {
            $this->loadValuesInRequest($values);
        }
        return $this->getRecord();
    }

    public function loadValuesInRequest(array $values)
    {
        foreach($this->fields as $field) {
            if (array_key_exists($field->html, $_REQUEST)) {
                continue;
            }
            if (array_key_exists($field->name, $values)) {
                $_REQUEST[$field->html] = $values[$field->name];
                $this->getController()->getRequest()->set('post.'.$field->html, $values[$field->name]);
            }
        }
    }

    public function map($fieldNameOnForm, $fieldNameOnRecord = null, $defaultValue = null, $type = 'string')
    {
        $formValue = isset($_REQUEST[$fieldNameOnForm]) ? $_REQUEST[$fieldNameOnForm] : null;
        $field = new Field($this, $fieldNameOnRecord, $fieldNameOnForm, $type, isset($_REQUEST[$fieldNameOnForm]));
        $field->setValue($formValue, $defaultValue);
        $this->fields[$field->html] = $field;
        return $field;
    }

    /**
     * Save values into ActiveRecord
     *
     * @return void
     */
    public function save()
    {
        //Recall before exec method with arbirtary code
        $this->dispatchEvent(self::EVENT_BEFORE_SAVE);
        //Fill Record with values from html form
        $this->fillRecord();
        //If occurred some error stop db updating and return exception
        if (!empty($this->exception) && !empty($this->exception->getErrors())) {
            throw $this->exception;
        }
        //If where list is empty execute db insert else execute a db update
        if ($this->getRecord()->getBehavior() == 'insert') {
            $this->insert();
        } else {
            $this->update();
        }
        //Recall after exec method with arbirtary code
        $this->dispatchEvent(self::EVENT_AFTER_SAVE);
    }

    private function fillRecord()
    {
        //skim the field list for check value and build $values, $where and $key list
        foreach ($this->fields as $field) {
            //Check if value respect rule
            if ($field->existInForm()) {
                $this->validateField($field);
            }
            //if field is readonly or don't have db field name skip other checks.
            if ($field->readonly || !$field->name) {
                 //If field isn't in readonly mode assign values to values list for store it in db
                continue;
            }
            if (!$field->existInForm() && !$field->getDefaultValue() && $this->getRecord()->getState() != 'insert') {
                //If field isn't in form and it isn't a insert operation and it have not a default value
                continue;
            }
            //Set value in record
            $this->getRecord()->setValue($field->name, $field->value);
        }
    }

    private function validateField($field)
    {
        try {
            $this->getValidator()->validate($field);
        } catch(\Exception $e) {
            $this->getException()->setErrorOnField($field, $e->getMessage());
        }
    }

    public function insert()
    {
        $this->setBehavior(self::BEHAVIOR_INSERT);
        $this->dispatchEvent(self::EVENT_BEFORE_INSERT);
        $this->getRecord()->save();
        $this->dispatchEvent(self::EVENT_AFTER_INSERT);
    }

    public function update()
    {
        $this->setBehavior(self::BEHAVIOR_UPDATE);
        $this->dispatchEvent(self::EVENT_BEFORE_UPDATE);
        $this->getRecord()->save();
        $this->dispatchEvent(self::EVENT_AFTER_UPDATE);
    }

    public function delete()
    {
        $this->setBehavior(self::BEHAVIOR_DELETE);
        $this->dispatchEvent(self::EVENT_BEFORE_DELETE);
        if (empty($this->softDelete)) {
            $this->getRecord()->delete();
        } else {
            $this->getRecord()->save($this->softDelete);
        }
        $this->dispatchEvent(self::EVENT_AFTER_DELETE);
    }

    public function setBehavior($behavior)
    {
        $this->behavior = $behavior;
    }

    public function setValue($fieldId, $value, $defaultValue = null)
    {
        $this->fields[$fieldId]->setValue($value, $defaultValue);
    }

    public function softDelete($field, $value)
    {
        $this->softDelete = [$field => $value];
    }

    abstract protected function record();
    
    abstract protected function mapFactory();

    public function getFieldValue($fieldId)
    {
        $valueInRequest = filter_input(\INPUT_POST, $fieldId);
        if ($valueInRequest) {
            return $valueInRequest;
        }
        if (!array_key_exists($fieldId, $this->fields)) {
            return null;
        }
        $dbFieldName = $this->fields[$fieldId]->name;
        return $dbFieldName ? $this->getValue($dbFieldName) : null;
    }
}
