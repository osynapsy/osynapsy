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

/**
 * The abstract class Model implement base methods for manage mapping
 * of the html fields on db fields.
 *
 * @author Pietro Celeste <p.celete@osynapsy.org>
 */
abstract class Model extends BaseModel
{
    protected $table;
    protected $sequence;
    protected $values = [];
    protected $softdelete;

    public function __construct(InterfaceController $controller)
    {
        parent::__construct($controller);
        $this->init();
        if (empty($this->table)) {
            throw new \Exception('Model table is empty');
        }
    }

    public function find()
    {
        $where = $this->whereConditionFactory();
        if (!empty($where)) {
            $this->values = $this->getDb()->selectOne($this->table, $where);
            $this->assocData();
        }
    }

    protected function whereConditionFactory()
    {
        $result = [];
        foreach ($this->fields as $field) {
            if ($field->isPkey()) {
                $result[$field->name] = $field->value;
            }
        }
        return array_filter($result , function($value) { return ($value !=='0' && !empty($value)); });
    }

    public function assocData()
    {
        if (is_array($this->values)) {
            foreach ($this->fields as $f) {
               if (!array_key_exists($f->html, $_REQUEST) && array_key_exists($f->name, $this->values)) {
                    $_REQUEST[ $f->html ] = $this->values[ $f->name ];
                }
            }
        }
    }

    protected function insert($values, $where = null)
    {
        $this->addError($this->beforeInsert());
        if ($this->getController()->getResponse()->error()) {
            return;
        }
        $lastId = null;
        if ($this->sequence && is_array($where) && count($where) == 1) {
            $lastId = $values[$where[0]] = $this->getIdFromSequence($this->sequence);
            $this->getDb()->insert($this->table, $values);
        } else {
            $lastId = $this->getDb()->insert($this->table, $values);
        }
        $this->afterInsert($lastId);
        $this->execAfterAction('after-insert', $lastId);
    }

    protected function getIdFromSequence($sequence)
    {
        return is_callable($sequence) ? $sequence() : $this->getDb()->findOne("SELECT {$this->sequence}.nextval FROM DUAL");
    }

    protected function update(array $values, $where)
    {
        $this->addError($this->beforeUpdate());
        if ($this->getController()->getResponse()->error()) {
            return;
        }
        $this->getDb()->update($this->table, $values, $where);
        $this->afterUpdate();
        $this->execAfterAction('after-update');
    }

    public function delete()
    {
        $this->addError($this->beforeDelete());
        $where = $this->whereConditionFactory();
        if (empty($where)) {
            return;
        }
        if (!empty($this->softdelete)) {
            $this->getDb()->update($this->table, $this->softdelete, $where);
        } else {
            $this->getDb()->delete($this->table, $where);
        }
        $this->afterDelete();
        $this->execAfterAction('after-delete');
    }

    public function getValue($key)
    {
        return is_array($this->values) && array_key_exists($key, $this->values) ? $this->values[$key] : null;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }

    public function setTable($table, $sequence = null)
    {
        $this->table = $table;
        $this->sequence = $sequence;
    }

    public function softDelete($field, $value)
    {
        $this->softdelete = array($field => $value);
    }
}
