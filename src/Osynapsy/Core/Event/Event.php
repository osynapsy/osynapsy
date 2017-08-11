<?php
namespace Osynapsy\Core\Event;

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
