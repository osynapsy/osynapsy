<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc;

interface InterfaceModel
{
    public function init($controller);
    
    public function find();
    
    public function delete();
    
    public function insert();
    
    public function update();
}
