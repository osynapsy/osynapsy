<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc\Model;

use Osynapsy\Mvc\Model\InterfaceModel;
use Osynapsy\Mvc\Controller\Controller;

/**
 * Description of Simple
 *
 * @author Pietro
 */
abstract class Simple implements InterfaceModel
{
    protected $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
        $this->init();
    }

    public function getController() : Controller
    {
        return $this->controller;
    }

    public function getDb()
    {
        return $this->getController()->getDb();
    }

    public function find()
    {
    }

    public function save()
    {
    }

    public function init()
    {
    }

    public function delete()
    {
    }
}
