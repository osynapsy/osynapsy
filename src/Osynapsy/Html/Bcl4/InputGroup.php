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
    protected $prefix;
    
    public function __construct($name, $prefix = null, $postfix = null, $dimension = null)
    {
        parent::__construct('div');
        $this->setClass('input-group');
        $this->prepend($prefix);                
        if (is_object($name)) {
            $this->textBox = $name;
        } else {
            $this->textBox = new TextBox($name);
            $this->textBox->att('aria-describedby', $name.'_prefix');
        }
        $this->append($postfix);
        $this->setDimension($dimension);
    }
    
    public function __build_extra__(): void
    {
        if (!empty($this->prefix)) {
            $this->add($this->prefix);
        }
        $this->add($this->textBox);
        if (!empty($this->postfix)) {
            $this->add($this->postfix);
        }
    }
    
    public function prepend($prefix)
    {
        if (empty($prefix)) {
            return;
        }
        if (is_object($prefix)) {
            return $this->getPrefix()->add($prefix);
        }
        return $this->getPrefix()->add(new Tag('span', null, 'input-group-text'))->add($prefix);
    }
    
    public function append($postfix)
    {
        if (empty($postfix)) {
            return;
        }
         if (is_object($postfix)) {
             return $this->getPostfix()->add($postfix);
         }
        $this->getPostfix()->add(new Tag('span', null, 'input-group-text'))->add($postfix);
    }
   
    public function getTextBox()
    {
        return $this->textBox;
    }
    
    public function getPostfix()
    {
        if (empty($this->postfix)) {
            $this->postfix = new Tag('div', null, 'input-group-append');
        }
        return $this->postfix;
    }
    
    public function getPrefix()
    {
        if (empty($this->prefix)) {
            $this->prefix = new Tag('div', null, 'input-group-prepend');
        }
        return $this->prefix;
    }
    
    public function setDimension($dimension)
    {
        if (empty($dimension)) {
            return;
        }
        $this->setClass('input-group-'.$dimension);
    }
    
    public function setSmallSize()
    {
        $this->setDimension('sm');
    }
}

