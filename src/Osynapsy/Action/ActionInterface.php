<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Action;

use Osynapsy\Controller\ControllerInterface;

/**
 * Description of Action
 *
 * @author Pietro Celeste
 */
interface ActionInterface
{
    public function getApp();

    public function setController(ControllerInterface $controller);

    public function setParameters(array $parameters);
}
