<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Event;

use Osynapsy\Mvc\Controller;

/**
 * Description of Listener
 *
 * @author pietr
 */
interface InterfaceListener 
{
    public function __construct(Controller $controller);
    
    public function trigger();
}
