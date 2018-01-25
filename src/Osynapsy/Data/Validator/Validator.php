<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Data\Validator;

abstract class Validator
{    
    protected $field = array();
    
    public function __construct(&$field) 
    {
        $this->field = $field;
        if (!array_key_exists('label', $this->field)) {
            $this->field['label'] = $this->field['name'];
        }
    }
    
    abstract public function check();
}

