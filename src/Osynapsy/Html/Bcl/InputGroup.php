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

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;

class InputGroup extends Component
{
    protected $textBox;
    protected $postfix;
    protected $prefix;
    
    public function __construct($name, $prefix = '', $postfix = '')
    {
        parent::__construct('div');
        $this->att('class','input-group');
        if (!empty($prefix)) {
            $this->prefix = $this->add($this->getFix($prefix, 'input-group-prepend'));            
        }
        if (is_object($name)) {
            $this->textBox = $this->add($name);
        } else {
            $this->textBox = $this->add(new TextBox($name));
            $this->textBox->att('aria-describedby',$name.'_prefix');
        }
        
        if (!empty($postfix)) {
            $this->postfix = $this->add($this->getFix($postfix, 'input-group-append'));
        }        
    }
    
    public function getFix($value, $addClass)
    {
        $class = 'input-group-text input-group-addon  '.$addClass;
        if (!is_object($value)) {
            $fix = new Tag('span', null, trim($class));        
            $fix->add($value);
        } else {
            $fix = $value->addClass($class);
        }
        return $fix;
    }
            
    public function getPrefix()
    {
        return $this->prefix;
    }
    
    public function getPostfix()
    {
        return $this->postfix;
    }
    
    public function getTextBox()
    {
        return $this->textBox;
    }
}
