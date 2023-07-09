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
use Osynapsy\Database\Record\Active;

/**
 * The abstract class Model implement base methods for manage mapping
 * of the html fields on db fields.
 *
 * @author Pietro Celeste <p.celete@osynapsy.org>
 */
abstract class Model extends ModelRecord
{
    protected $table;
    protected $sequence;
    protected $softdelete;

    public function __construct(ControllerInterface $controller, ...$args)
    {
        $this->setController($controller);
        autowire()->execute($this, 'init');
        if (empty($this->table)) {
            throw new \Exception('Model table is empty');
        }
        $this->record = $this->record();
        $this->initRecord($this->record);
        $this->recordFill();
        $this->afterInit();
    }

    protected function record()
    {
        return new class ($this->getDb()) extends Active
        {
            protected $table;

            public function setTable($table)
            {
                $this->table = $table;
            }

            public function table()
            {
                return $this->table;
            }

            public function setPrimaryKeys(array $keys)
            {
                $this->keys = $keys;
            }

            public function primaryKey()
            {
                return $this->keys;
            }

            public function fields()
            {
                return $this->fields;
            }

            public function setFields(array $fields)
            {
                $this->fields = $fields;
            }
        };
    }

    protected function initRecord($record)
    {
        $record->setTable($this->getTable());
        $keys = $fields = [];
        foreach($this->fields as $field) {
            $fields[] = $field->name;
            if ($field->isPkey()) {
                $keys[] = $field->name;
            }
        }
        $record->setFields($fields);
        $record->setPrimaryKeys($keys);
        return $record;
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
        $this->softdelete = [$field => $value];
    }
}
