<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\ViewModel;

use Osynapsy\ViewModel\ModelInterface;
use Osynapsy\Controller\ControllerInterface;
use Osynapsy\Database\Driver\DboInterface;

/**
 * Description of Simple
 *
 * @author Pietro
 */
abstract class Simple implements ModelInterface
{
    protected $controller;

    public function __construct(ControllerInterface $controller)
    {
        $this->controller = $controller;
        if (method_exists($this, 'init')) {
            autowire()->execute($this, 'init');
        }
    }

    public function getController() : ControllerInterface
    {
        return $this->controller;
    }

    public function getDb() : DboInterface
    {
        return $this->getController()->getDb();
    }

    public function loadValues()
    {
    }

    public function save()
    {
    }

    public function delete()
    {
    }

    public function getFieldValue($fieldId)
    {
    }
}
