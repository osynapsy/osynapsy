<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc;

use Osynapsy\Data\Dictionary;
use Osynapsy\Mvc\ModelField;
use Osynapsy\Network\UploadManager;

abstract class ModelRecord
{
    private $repo;
    private $record;
    private $controller = null;    
    protected $db = null;
    protected $errorMessages = array(        
        'email' => 'Il campo <fieldname> non contiene un indirizzo mail valido.',
        'fixlength' => 'Il campo <fieldname> solo valori con lunghezza pari a ',
        'integer' => 'Il campo <fieldname> accetta solo numeri interi.',
        'maxlength' => 'Il campo <fieldname> accetta massimo ',
        'minlength' => 'Il campo <fieldname> accetta minimo ',
        'notnull' => 'Il campo <fieldname> è obbligatorio.',
        'numeric' => 'Il campo <fieldname> accetta solo valori numerici.',
        'unique' => '<value> è già presente in archivio.'
    );    
    
    public function __construct($controller)
    {
        $this->controller = $controller;        
        $this->db = $this->controller->getDb();
        $this->record = $this->record();
        $this->repo = new Dictionary();
        $this->repo->set('actions.after-insert', $this->getController()->getRequest()->get('page.url'))
                   ->set('actions.after-update', 'back')
                   ->set('actions.after-delete', 'back')
                   ->set('fields',[])                   
                   ->set('values',[]);
        $this->init();
        $this->recordFill();        
    }       
    
    public function get($key)
    {
        return $this->repo->get($key);
    }
    
    protected function getController()
    {
        return $this->controller;
    }
    
    public function getField($field)
    {
        return $this->get('fields.'.$field);
    }
    
    public function getRecord()
    {
        return $this->record;
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
       
    public function delete()
    {
        $this->beforeDelete();
        if ($this->getController()->getResponse()->error()){ 
            return; 
        }
        $this->getRecord()->delete();
        $this->afterDelete();
        if ($this->repo->get('actions.after-delete') === false) {
            return;
        }
        $this->getController()->getResponse()->go($this->repo->get('actions.after-delete'));
    }

    private function recordFill()
    {
        $keys = [];
        foreach($this->get('fields') as $field) {
            if ($field->isPkey()) {
                $keys[$field->name] = $field->getDefaultValue();
            }            
        }
        try {
            $this->getRecord()->findByAttributes($keys);
        } catch (\Exception $e) {            
        }
    }
    
    public function find()
    {        
        $this->loadRecordInRequest();
    }
    
    public function loadRecordInRequest()
    {
        $values = $this->getRecord()->get();
        if (empty($values)) {
            return;
        }
        foreach($this->get('fields') as $field) {
            if (array_key_exists($field->html, $_REQUEST) || !array_key_exists($field->name, $values)) {
                continue;
            }
            $_REQUEST[$field->html] = $values[$field->name];
        }
    }
    
    public function insert()
    {
        $this->beforeInsert();        

        if ($this->getController()->getResponse()->error()) {
            return;
        }        

        $lastId = $this->getRecord()->save();        

        $this->afterInsert($lastId);
        
        switch ($this->get('actions.after-insert')) {
            case false:
                return;
            case 'back':
            case 'refresh':
                $this->controller->getResponse()->go($this->get('actions.after-insert'));                
                break;
            default: 
                $this->controller->getResponse()->go($this->get('actions.after-insert').$lastId);                
                break;
        }
    }

    public function update()
    {
        $this->beforeUpdate();
        if ($this->getController()->getResponse()->error()) {
            return;
        }
        $id = $this->getRecord()->save();   
        $this->afterUpdate($id);
        if ($this->repo->get('actions.after-update') === false) {
            return;
        }
        $this->getController()->getResponse()->go($this->repo->get('actions.after-update'), false);        
    }    
        
    protected function addError($errorId, $field, $postfix = '')
    {
        $error = str_replace(
            array('<fieldname>', '<value>'),
            array('<!--'.$field->html.'-->', $field->value),            
            $this->errorMessages[$errorId].$postfix
        );
        $this->getController()->getResponse()->error($field->html, $error);
    }        
    
    public function map($htmlField, $dbField = null, $value = null, $type = 'string')
    {
        $modelField = new ModelField($this, $dbField, $htmlField, $type);
        $modelField->setValue(
            isset($_REQUEST[$modelField->html]) ? $_REQUEST[$modelField->html] : null, 
            $value
        );        
        $this->set('fields.'.$modelField->html, $modelField);        
        return $modelField;
    }
    
    /**
     * 
     * @return void
     */
    public function save()
    {
        //Recall before exec method with arbirtary code
        $this->beforeExec();
                                     
        //skim the field list for check value and build $values, $where and $key list
        foreach ($this->repo->get('fields') as $field) {
            //Check if value respect rule
            $value = $this->sanitizeFieldValue($field);
            //If field isn't in readonly mode assign values to values list for store it in db
            if (!$field->readonly && $field->name) {
                $this->getRecord()->setValue($field->name, $value);
            }                        
        }
        //If occurred some error stop db updating
        if ($this->getController()->getResponse()->error()) { 
            return; 
        }
        //If where list is empty execute db insert else execute a db update
        if ($this->getRecord()->getState() == 'insert') {
            $this->insert();
        } else {
            $this->update();
        }
        //Recall after exec method with arbirtary code
        $this->afterExec();
    }
    
    private function sanitizeFieldValue(&$field)
    {
        $value = $field->value;
        if (!$field->isNullable() && $value !== '0' && empty($value)) {
            $this->addError('notnull', $field);            
        }
        if ($field->isUnique() && $value) {
            $nOccurence = $this->db->execUnique(
                "SELECT COUNT(*) FROM {$this->getRecord()->table()} WHERE {$field->name} = ?",
                array($value)
            );
            if (!empty($nOccurence)) {
                $this->addError('unique', $field);
            }
        }
        //Controllo la lunghezza massima della stringa. Se impostata.
        if ($field->maxlength && (strlen($value) > $field->maxlength)) {
            $this->addError('maxlength', $field, $field->maxlength.' caratteri');           
        } elseif ($field->minlength && (strlen($value) < $field->minlength)) {
            $this->addError('minlength', $field, $field->minlength.' caratteri');
        } elseif ($field->fixlength && !in_array(strlen($value),$field->fixlength)) {
            $this->addError('fixlength', $field, implode(' o ',$field->fixlength).' caratteri');            
        }
        switch ($field->type) {
            case 'float':
            case 'money':
            case 'numeric':
            case 'number':
                if ($value && filter_var($value, \FILTER_VALIDATE_FLOAT) === false) {
                    $this->addError('numeric', $field);                    
                }
                break;
            case 'integer':
            case 'int':
                if ($value && filter_var($value, \FILTER_VALIDATE_INT) === false) {
                    $this->addError('integer', $field);                    
                }
                break;
            case 'email':
                if (!empty($value) && filter_var($value, \FILTER_VALIDATE_EMAIL) === false) {
                    $this->addError('email', $field);                    
                }
                break;
            case 'file':
            case 'image':                
                $value = $this->grabUploadedFile($field);                
                break;
        }
        return $value;
    }
    
    private function grabUploadedFile(&$field)
    {
        if (
            !is_array($_FILES) 
            || !array_key_exists($field->html, $_FILES)
            || empty($_FILES[$field->html]['name'])
        ) {
            $field->readonly = true;            
            return $this->getRecord()->get($field->name);
        }
                
        $upload = new UploadManager();
        try {
            $field->value = $upload->saveFile($field->html, $field->uploadDir);
        } catch(\Exception $e) {
            $this->getController()->getResponse()->error('alert', $e->getMessage());
            $field->readonly = true;            
        }
        $this->afterUpload($field->value, $field);
        $this->set('actions.after-update','refresh');
        $this->set('actions.after-insert','refresh');
        return $field->value;
    }
    
    protected function setAfterAction($insert, $update, $delete)
    {
        $this->repo->set('actions.after-insert', $insert)
                   ->set('actions.after-update', $update)
                   ->set('actions.after-delete', $delete);        
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
