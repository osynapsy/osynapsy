<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\View;

use Osynapsy\ViewModel\ModelInterface;

/**
 * Description of InterfaceView
 *
 * @author pietr
 */
interface ViewInterface
{
    public function factory();

    public function getModel() : ModelInterface;

    public function getTitle() : string;

    public function setModel(ModelInterface $model);
}
