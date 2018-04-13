<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Ocl;

//costruttore del text box
class TextBox extends InputBox
{
    public function __construct($nam, $id = null)
    {
        parent::__construct('text', $nam, $this->nvl($id,$nam));
        $this->setParameter('get-request-value',$nam);
    }

    protected function __build_extra__()
    {
        parent::__build_extra__();
        if ($this->getParameter('field-control') == 'is_number'){
            $this->att('type','number')
                 ->att('class','right osy-number',true);
        }
    }
            
    public function setValue($value)
    {        
        if (!array_key_exists($this->name, $_REQUEST)) {
            $_REQUEST[$this->name] = $value;
        }
        return $this;
    }
}