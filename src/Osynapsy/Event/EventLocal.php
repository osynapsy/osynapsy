<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Event;

/**
 * Description of EventLocal
 *
 * @author Pietro
 */
class EventLocal extends Event
{
    public function setEventId($eventId)
    {
        parent::setEventId(sprintf('%s\%s',get_class($this->origin), $eventId));
    }
}
