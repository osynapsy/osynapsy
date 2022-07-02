<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc\Action;

use Osynapsy\Mvc\Controller;

/**
 * Description of Action
 *
 * @author pietr
 */
interface InterfaceAction
{
    public function execute();

    public function setController(Controller $controller);

    public function setParameters(array $parameters);
}
