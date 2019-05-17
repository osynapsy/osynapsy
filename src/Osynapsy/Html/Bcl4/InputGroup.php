<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;
use Osynapsy\Html\Bcl\TextBox;

/**
 * Description of InputGroup
 *
 * @author Pietro
 */

class InputGroup extends Component
{
    protected $textBox;
    protected $postfix;
    
    public function __construct($name, $prefix = null, $postfix = null, $dimension = null)
    {
        parent::__construct('div');
        $this->setClass('input-group');
        $this->buildPrefix($prefix, $name);
                
        if (is_object($name)) {
            $this->textBox = $this->add($name);
        } else {
            $this->textBox = $this->add(new TextBox($name));
            $this->textBox->att('aria-describedby', $name.'_prefix');
        }
        $this->buildPostfix($postfix);
        $this->setDimension($dimension);
    }
    
    private function buildPrefix($prefix, $name)
    {
        if (empty($prefix)) {
            return;
        }
        $this->add(new Tag('div', $name.'_prefix', 'input-group-prepend'))                 
             ->add($prefix);
    }
    
    private function buildPostfix($postfix)
    {
        if (empty($postfix)) {
            return;
        }
        $this->postfix = $this->add(new Tag('div', null, 'input-group-append'));
        $this->postfix->add($postfix);
    }
    
    public function getTextBox()
    {
        return $this->textBox;
    }
    
    public function getPostfix()
    {
        return $this->postfix;
    }
    
    public function setDimension($dimension)
    {
        $this->setClass('input-group-'.$dimension);
    }
}

