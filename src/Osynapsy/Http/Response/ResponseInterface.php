<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Http\Response;

/**
 * Description of ResponseInterface
 *
 * @author Pietro Celeste <pietro.celeste@gmail.com>
 */
interface ResponseInterface
{
    public function add($content);

    public function __toString();
}
