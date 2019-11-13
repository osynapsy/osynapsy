<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Ocl\TextBox as OclTextBox;

class TextBox extends OclTextBox
{
    const MASK_CURRENCY = 1;
    const MASK_EURO = 2;
    const MASK_TIME = 3;
    const MASK_EMAIL = 5;
    const MASK_IP = 10;
    
    private $masks = [
        //convert boolean value true = 1 and false = 0
        self::MASK_CURRENCY  => [
            'alias' => 'numeric',            
            'autoGroup'=> 1, 
            'digits' => 2, 
            'digitsOptional' => 0, 
            'placeholder' => '0'
        ],
        SELF::MASK_EMAIL => [
            'alias' => 'email'
        ],
        self::MASK_EURO => [
            'alias' => 'numeric',
            'groupSeparator' => ',', 
            'autoGroup' => 1, 
            'digits' => '2', 
            'digitsOptional' => 0, 
            'prefix' => '€ ', 
            'placeholder' => '0'
        ],
        SELF::MASK_IP => [
            'alias' => 'ip'
        ],
        self::MASK_TIME => [
            'alias' => 'datetime',
            'format' => 'HH:MM',
            'placeholder' => 'hh:mm'
        ]        
    ];
    
    public function __construct($name, $class = '')
    {
        parent::__construct($name);
        $this->att('class',trim('form-control '.$class),true);
    }
    
    public function setMask($id)
    {
        if (!array_key_exists($id, $this->masks)) {
            throw new \Exception("component {$this->id} : Mask format {$id} not regnized");            
        }
        $mask = $this->masks[$id];
        $rules = array_map(function($key, $value) {
            return "'{$key}': ".(is_string($value) ? "'$value'" : $value);            
        }, array_keys($mask), $mask);
        $this->setMaskRaw(implode(', ', $rules));
    }
    
    public function setMaskRaw($maskraw)
    {
        $this->requireJs('Lib/inputmask-5.0.0-beta/dist/jquery.inputmask.js');
        $this->requireJsCode("$(':input').inputmask();");
        $this->att('data-inputmask', $maskraw);
    }
}
