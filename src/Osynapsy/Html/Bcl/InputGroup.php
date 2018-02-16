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
    
    public function __construct($name, $prefix = '', $postfix = '')
    {
        parent::__construct('div');
        $this->att('class','input-group');
        if (!empty($prefix)) {
            $this->add(new Tag('span'))
                 ->att('class', 'input-group-addon')
                 ->att('id',$name.'_prefix')
                 ->add($prefix);
        }
        if (is_object($name)) {
            $this->textBox = $this->add($name);
        } else {
            $this->textBox = $this->add(new TextBox($name));
            $this->textBox->att('aria-describedby',$name.'_prefix');
        }
        
        if ($postfix) {
            $this->postfix = $this->add(new Tag('span'))->att('class', 'input-group-addon');
            $this->postfix->add($postfix);
        }
    }
    
    public function getTextBox()
    {
        return $this->textBox;
    }
    
    public function getPostfix()
    {
        return $this->postfix;
    }
}
