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

/**
 * Description of Event
 *
 * @author Peter
 */
class Event 
{
    private $origin;
    private $eventId;
    
    public function __construct($eventId, $origin)
    {
        $this->origin = $origin;
        $this->eventId = $eventId;
    }
    
    public function getOrigin()
    {
        return $this->origin;
    }
    
    public function getNameSpace()
    {
        return get_class($this->origin).'\\'.$this->eventId;
    }
    
    public function getId()
    {
        return $this->eventId;
    }
    
    public function trigger()
    {        
    }
}
