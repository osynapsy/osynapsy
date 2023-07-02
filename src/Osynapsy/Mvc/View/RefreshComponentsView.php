<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Osynapsy\Mvc\View;

use Osynapsy\Mvc\Controller\ControllerInterface;
use Osynapsy\Html\Tag;
use Osynapsy\Html\DOM;

/**
 * Description of RefreshComponentsView
 *
 * @author Pietro Celeste <pietro.celeste@gmail.com>
 */
class RefreshComponentsView extends AbstractView
{
    protected $componetIDs;

    public function __construct(ControllerInterface $Controller)
    {
        $parameters = func_get_args();
        $this->componentIDs = $parameters[1];
        parent::__construct($Controller);
    }

    public function init()
    {
        $response = new Tag('div', 'response');
        foreach($this->componentIDs as $componentId) {
            $response->add(DOM::getById($componentId));
        }
        return $response;
    }
}
