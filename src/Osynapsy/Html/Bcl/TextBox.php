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
    const MASK_INT = 1;
    const MASK_FLOAT = 2;
    const MASK_CURRENCY = 10;
    const MASK_CURRENCY_EURO = 11;    
    const MASK_DATETIME = 20;
    const MASK_DATE = 21;
    const MASK_DATE_SQL = 22;
    const MASK_DATE_US = 23;
    const MASK_TIME = 25;
    const MASK_EMAIL = 60;
    const MASK_IP = 70;
    
    private $masks = [
        //convert boolean value true = 1 and false = 0
        self::MASK_INT  => [
            'alias' => 'numeric',            
            'autoGroup'=> 1, 
            'digits' => 0,
            'digitsOptional' => 0, 
            'placeholder' => '0'
        ],
        self::MASK_FLOAT  => [
            'alias' => 'numeric',            
            'autoGroup'=> 1, 
            'digits' => 2,
            'digitsOptional' => 2, 
            'placeholder' => '0'
        ],
        //convert boolean value true = 1 and false = 0
        self::MASK_CURRENCY  => [
            'alias' => 'numeric',            
            'autoGroup'=> 1, 
            'digits' => 2, 
            'digitsOptional' => 0, 
            'placeholder' => '0'
        ],
        self::MASK_CURRENCY_EURO => [
            'alias' => 'numeric',
            'groupSeparator' => ',', 
            'autoGroup' => 1, 
            'digits' => '2', 
            'digitsOptional' => 0, 
            'prefix' => '€ ', 
            'placeholder' => '0'
        ],
        SELF::MASK_DATETIME => [
            'alias' => 'datetime'
        ],        
        SELF::MASK_DATE => [
          'alias' => 'datetime',
           'inputFormat' => 'dd/mm/yyyy',
           'placeholder' => 'dd/mm/yyyy'  
        ],
        SELF::MASK_DATE_SQL => [
          'alias' => 'datetime',
           'inputFormat' => 'yyyy/dd/mm',
           'placeholder' => 'yyyy/dd/mm'  
        ],
        SELF::MASK_DATE_US => [
          'alias' => 'datetime',
           'inputFormat' => 'mm/dd/yyyy',
           'placeholder' => 'mm/dd/yyyy'  
        ],
        SELF::MASK_EMAIL => [
            'alias' => 'email'
        ],        
        SELF::MASK_IP => [
            'alias' => 'ip'
        ],
        self::MASK_TIME => [
            'alias' => 'datetime',
            'inputFormat' => 'HH:MM',
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
            throw new \Exception("TextBox {$this->id} : Mask format {$id} not regnized");            
        }
        $mask = $this->masks[$id];
        $rules = array_map(function($key, $value) {
            return "'{$key}': ".(is_string($value) ? "'$value'" : $value);            
        }, array_keys($mask), $mask);
        $this->setMaskRaw(implode(', ', $rules));
        return $this;
    }
    
    public function setMaskRaw($maskraw)
    {
        $this->requireJs('Lib/inputmask-5.0.0-beta/dist/jquery.inputmask.js');
        $this->requireJsCode("$(':input').inputmask();");
        $this->att('data-inputmask', $maskraw);
        return $this;
    }
}
