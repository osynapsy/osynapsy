<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc\Model;

use Osynapsy\Data\Dictionary;
use Osynapsy\Event\EventLocal;
use Osynapsy\Mvc\Controller;
use Osynapsy\Mvc\ModelField;
use Osynapsy\Helper\Net\UploadManager;

abstract class Record
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
    
    private $repo;    
    private $record;
    private $exception;
    private $controller;
    private $validator;
    public $uploadOccurred = false;
    public $behavior;
    
    public function __construct($controller)
    {
        $this->controller = $controller;
        $this->controller->setExternalAction('save', new \Osynapsy\Mvc\Model\Action\Save());
        $this->controller->setExternalAction('delete', new \Osynapsy\Mvc\Model\Action\Delete());
        $this->record = $this->record();        
        $this->repo = new Dictionary();
        $this->repo->set('fields',[])                   
                   ->set('values',[]);
        $this->init();
        $this->initRecord();
        $this->afterInit();
    }       
    
    public function addListenerLocal(callable $trigger, array $eventIDs)
    {        
        array_walk($eventIDs, function (&$value, $key, $namespace) { $value = $namespace.'\\'.$value;}, get_class($this));
        $this->getController()->getDispatcher()->addListener($trigger, $eventIDs);
    }
    
    protected function afterInit()
    {        
    }
    
    private function initRecord()
    {
        $keys = [];
        foreach($this->get('fields') as $field) {
            if ($field->isPkey()) {
                $keys[$field->name] = $field->getDefaultValue();
            }            
        }
        $this->getRecord()->findByAttributes($keys);        
    }
    
    public function get($key)
    {
        return $this->repo->get($key);
    }
    
    public function getDb()
    {
        return $this->getController()->getDb();
    }
    
    protected function getController() : Controller
    {
        return $this->controller;
    }
    
    protected function dispatchEvent($event)
    {
        $this->getController()->getDispatcher()->dispatch(new EventLocal($event, $this));
    }
    
    public function getException()
    {
        if (empty($this->exception)) {
            $this->exception = new ModelErrorException('Si sono verificati degli errori');
        }
        return $this->exception;
    }
    
    public function getField($field)
    {
        return $this->get('fields.'.$field);
    }
    
    public function getLastId()
    {
        return $this->getRecord()->lastAutoincrementId;
    }
    
    public function getRecord()
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
        return $this->getRecord()->getValue($key);
    }
    
    public function set($key, $value)
    {
        $this->repo->set($key, $value);
        return $this;
    }              
    
    public function find()
    {        
        $values = $this->getRecord()->get();
        $this->loadValuesInRequest($values);
        return $this->getRecord();
    }
    
    public function loadValuesInRequest($values)
    {        
        if (empty($values)) {
            return;
        }
        foreach($this->get('fields') as $field) {
            if (array_key_exists($field->html, $_REQUEST)) {
                continue;
            }
            if (array_key_exists($field->name, $values)) {
                $_REQUEST[$field->html] = $values[$field->name];
                $this->getController()->getRequest()->set('post.'.$field->html, $values[$field->name]);
            }
        }
    }
    
    public function map($formField, $dbField = null, $defaultValue = null, $type = 'string')
    {        
        $formValue = isset($_REQUEST[$formField]) ? $_REQUEST[$formField] : null;
        $modelField = new ModelField($this, $dbField, $formField, $type, isset($_REQUEST[$formField]));
        $modelField->setValue($formValue, $defaultValue);        
        $this->set('fields.'.$modelField->html, $modelField);
        return $modelField;
    }
    
    /**
     * Save values into ActiveRecord
     * 
     * @return void
     */
    public function save()
    {                
        //Recall before exec method with arbirtary code
        $this->beforeExec();
        $this->dispatchEvent(self::EVENT_BEFORE_SAVE);
        //Fill Record with values from html form
        $this->fillRecord();        
        //If occurred some error stop db updating and return exception
        if (!empty($this->exception) && !empty($this->exception->getErrors())) {            
            throw $this->exception;
        }
        //If where list is empty execute db insert else execute a db update
        if ($this->getRecord()->getState() == 'insert') {
            $this->insert();
        } else {
            $this->update();
        }
        //Recall after exec method with arbirtary code
        $this->afterExec();
        $this->dispatchEvent(self::EVENT_AFTER_SAVE);
    }
    
    private function fillRecord()
    {
        //skim the field list for check value and build $values, $where and $key list
        foreach ($this->repo->get('fields') as $field) {            
            //Check if value respect rule
            if ($field->existInForm()) {
                $this->validateField($field);
            }            
            //If field is file or image grab upload
            if (in_array($field->type, ['image', 'file'])) {
                $this->grabUploadedFile($field);
            }
            //if field is readonly or don't have db field name skip other checks.
            if (!$field->readonly && !$field->name) {
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
    
    private function grabUploadedFile(&$field)
    {
        if (!is_array($_FILES) || !array_key_exists($field->html, $_FILES) || empty($_FILES[$field->html]['name'])) {
            $field->readonly = true;            
            $field->value = $this->getRecord()->get($field->name);
            return;
        }                
        $upload = new UploadManager();
        try {
            $field->value = $upload->saveFile($field->html, $field->uploadDir);
            $this->uploadOccurred = true;
        } catch(\Exception $e) {
            $this->getController()->getResponse()->error('alert', $e->getMessage());
            $field->readonly = true;            
        }        
        $this->set('actions.after-update','refresh');
        $this->afterUpload($field->value, $field);
    }
    
    public function insert()
    {                
        $this->behavior = self::BEHAVIOR_INSERT;
        $this->beforeInsert();        
        $lastId = $this->getRecord()->save();
        $this->afterInsert($lastId);
    }

    public function update()
    {     
        $this->behavior = self::BEHAVIOR_UPDATE;
        $this->beforeUpdate();  
        $id = $this->getRecord()->save();   
        $this->afterUpdate($id);        
    }
    
    public function delete()
    {    
        $this->behavior = self::BEHAVIOR_DELETE;
        $this->beforeDelete();        
        $this->getRecord()->delete();
        $this->afterDelete();
    }
    
    public function setValue($field, $value, $defaultValue = null)
    {
        $this->repo->get('fields.'.$field)->setValue($value, $defaultValue);
    }
    
    protected function afterUpload($filename, $field = null)
    {        
    }
    
    protected function beforeExec()
    {
    }
    
    protected function beforeInsert()
    {
    }
    
    protected function beforeUpdate()
    {
    }
    
    protected function beforeDelete()
    {
    }
    
    protected function afterExec()
    {
    }
    
    protected function afterInsert($id)
    {
    }
    
    protected function afterUpdate()
    {
    }
    
    protected function afterDelete()
    {
    }
    
    abstract protected function init();  
    
    abstract protected function record();        
}
