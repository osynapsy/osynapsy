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

abstract class Model
{
    private $repo;
    protected $controller = null;
    protected $sequence = null;
    protected $table = null;
    protected $db = null;   
    protected $values = array();    
    protected $softdelete;
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
        $this->repo = new Dictionary();
        $this->repo->set('actions.after-insert', $this->controller->request->get('page.url'))
                   ->set('actions.after-update', 'back')
                   ->set('actions.after-delete', 'back')
                   ->set('fields',array());
        $this->init();
        if (empty($this->table)) {
            throw new \Exception('Model table is empty');
        }
        $this->repo->set('table', $this->table);
    }
    
    public function get($key)
    {
        return $this->repo->get($key);
    }
    
    public function set($key, $value, $append=false)
    {
        $this->repo->set($key, $value);
        return $this;
    }
    
    public function setSequence($seq)
    {
        $this->sequence = $seq;
    }
    
    public function setTable($table, $sequence = null)
    {
        $this->table = $table;
        $this->sequence = $sequence;
    }
    
    public function delete()
    {
        $this->beforeDelete();
        if ($this->controller->response->error()){ 
            return; 
        }
        $where = array();
        foreach ($this->repo->get('fields') as $i => $field) {
            if ($field->isPkey()) { 
                $where[$field->name] =  $field->value;
            }
        }
        if (empty($where)) { 
            return; 
        }
        if (!empty($this->softdelete)) {
            $this->db->update(
                $this->repo->get('table'),
                $this->softdelete,
                $where
            );
        } else {
            $this->db->delete(
                $this->repo->get('table'),
                $where
            );
        }
        $this->afterDelete();
        if ($this->repo->get('actions.after-delete') === false) {
            return;
        }
        $this->controller->response->go($this->repo->get('actions.after-delete'));
    }

    public function insert($values, $where=null)
    {
        $this->beforeInsert();        
        if ($this->controller->response->error()) {
            return;
        }        
        $lastId = null;
         
        if ($this->sequence && is_array($where) && count($where) == 1) {
            $lastId = $values[$where[0]] = $this->db->execUnique("SELECT {$this->sequence}.nextval FROM DUAL",'NUM');
            $this->db->insert($this->repo->get('table'), $values);
        } else {
            $lastId = $this->db->insert($this->repo->get('table'), $values);
        }        
        $this->afterInsert($lastId);
        if ($this->repo->get('actions.after-insert') === false) {
            return;
        }
        switch ($this->repo->get('actions.after-insert')) {
            case 'back':
            case 'refresh':
                $this->controller->response->go($this->repo->get('actions.after-insert'));                
                break;
            default: 
                $this->controller->response->go($this->repo->get('actions.after-insert').$lastId);                
                break;
        }
    }

    public function update($values, $where)
    {
        $this->beforeUpdate();
        if ($this->controller->response->error()) {
            return;
        }
        $this->db->update($this->repo->get('table'), $values, $where);
        $this->afterUpdate();
        if ($this->repo->get('actions.after-update') === false) {
            return;
        }
        $this->controller->response->go($this->repo->get('actions.after-update'), false);        
    }    

    public function find()
    {
        $sqlField = $sqlWhere = $sqlParam = array();
        $fields = $this->repo->get('fields');
        
        $k=0;
        foreach ($fields as $field) {
            if ($field->isPkey()) {
                $sqlWhere[] = $field->name . ' = '.($this->db->getType() == 'oracle' ? ':'.$k : '?');
                $sqlParam[] = $field->value;
                $k++;
            } 
            $sqlField[] = $field->name;
        }
        
        if (empty($sqlWhere)){ 
            return; 
        }
        
        $sql  = " SELECT *".PHP_EOL;        
        $sql .= " FROM  ".$this->repo->get('table')." ".PHP_EOL;
        $sql .= " WHERE ".implode(' AND ',$sqlWhere);
        try {
            $rec = $this->db->execUnique($sql, $sqlParam, 'ASSOC');
            if (!empty($rec)) {
                $this->values = $rec;
            }
        } catch (\Exception $e) {
            $this->controller->response->addContent('MODEL FIND ERROR: <pre>'.$e->getMessage()."\n".$sql.'</pre>');
        }
        $this->assocData();
    }
    
    protected function addError($errorId, $field, $postfix = '')
    {
        $error = str_replace(
            array('<fieldname>', '<value>'),
            array('<!--'.$field->html.'-->', $field->value),            
            $this->errorMessages[$errorId].$postfix
        );
        $this->controller->response->error($field->html, $error);
    }
    
    public function assocData()
    {
        if (!is_array($this->values)) {
            return;
        }
        foreach ($this->repo->get('fields') as $f) {
            if (!array_key_exists($f->html, $_REQUEST) && array_key_exists($f->name, $this->values)) {
                $_REQUEST[ $f->html ] = $this->values[ $f->name ];
            }
        }
    }
    
    public function getValue($key)
    {
        return array_key_exists($key, $this->values) ? $this->values[$key] : null;
    }
    
    public function getController()
    {
        return $this->controller;
    }
    
    public function map($htmlField, $dbField = null, $value = null, $type = 'string')
    {
        $modelField = new ModelField($this, $dbField, $htmlField, $type);
        $modelField->setValue(
            isset($_REQUEST[$modelField->html]) ? $_REQUEST[$modelField->html] : null, 
            $value
        );
        $this->repo->set('fields.'.$modelField->html, $modelField);
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
        
        //Init arrays
        $values = array();
        $where = array();
        $keys = array();
        
        //skim the field list for check value and build $values, $where and $key list
        foreach ($this->repo->get('fields') as $field) {
            //Check if value respect rule
            $value = $this->sanitizeFieldValue($field);
            //If field isn't in readonly mode assign values to values list for store it in db
            if (!$field->readonly) {
                $values[$field->name] = $value; 
            }
            //If field isn't primary key skip key assignment
            if (!$field->isPkey()) {
                continue;
            }
            //Add field to keys list
            $keys[] = $field->name;
            //If field has value assign field to where condition
            if (!empty($value)) {
                $where[$field->name] = $value;
            }
        }
        //If occurred some error stop db updating
        if ($this->controller->response->error()) { 
            return; 
        }
        //If where list is empty execute db insert else execute a db update
        if (empty($where)) {
            $this->insert($values, $keys);
        } else {
            $this->update($values, $where);
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
                "SELECT COUNT(*) FROM {$this->table} WHERE {$field->name} = ?",
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
            return $field->value;
        }
                
        $upload = new UploadManager();
        try {
            $field->value = $upload->saveFile($field->html, $field->uploadDir);
        } catch(\Exception $e) {
            $this->controller->response->error('alert', $e->getMessage());
            $field->readonly = true;            
        }
        $this->set('actions.after-update','refresh');
        $this->set('actions.after-insert','refresh');
        $this->afterUpload($field->value, $field);
        return $field->value;
    }
    
    protected function afterUpload($filename, $field = null)
    {        
    }
    
    public function softDelete($field, $value)
    {
        $this->softdelete = array($field => $value);
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
}
