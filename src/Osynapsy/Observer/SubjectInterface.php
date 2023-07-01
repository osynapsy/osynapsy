<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Observer;

/**
 * Description of InterfaceSubject
 *
 * @author Peter
 */
interface SubjectInterface extends \SplSubject
{
    public function getState();
}
