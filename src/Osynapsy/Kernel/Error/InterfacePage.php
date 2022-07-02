<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Kernel\Error;

/**
 * Description of InterfacePage
 *
 * @author Pietro
 */
interface InterfacePage
{
    public function get() : string;

    public function setComment($comment) : void;

    public function setMessage($message, $submessage = null) : void;

    public function setTrace(array $trace) : void;
}
