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
    public function __construct($name, $id = null)
    {
        parent::__construct('text', $name, $id ?? $name);
        $this->setParameter('get-request-value', $name);
    }

    protected function __build_extra__()
    {
        parent::__build_extra__();
        if ($this->getParameter('field-control') == 'is_number'){
            $this->att('type','number')
                 ->att('class','right osy-number',true);
        }
    }

    public function onTyping($jsCode)
    {
        return $this->setClass('typing-execute')->att('ontyping', $jsCode);
    }

    public function onDblClick($jsCode)
    {
        return $this->att('ondblclick', $jsCode);
    }

    public function setAction($action, $parameters = null, $class = 'change-execute', $confirmMessage = null)
    {
        return parent::setAction($action, $parameters, $class, $confirmMessage);
    }
}