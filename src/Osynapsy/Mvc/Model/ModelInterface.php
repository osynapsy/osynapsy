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

use Osynapsy\Mvc\Controller\ControllerInterface;

interface ModelInterface
{
    public function __construct(ControllerInterface $controller);

    public function getController() : ControllerInterface;

    public function find();

    public function save();

    public function delete();
}
