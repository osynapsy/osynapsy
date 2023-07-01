<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc\View;

use Osynapsy\Mvc\Controller\ControllerInterface;

/**
 * Description of InterfaceView
 *
 * @author pietr
 */
interface ViewInterface
{
    public function __construct(ControllerInterface $controller, $title = '');

    public function init();

    public function get();

    public function getController() : ControllerInterface;
}
