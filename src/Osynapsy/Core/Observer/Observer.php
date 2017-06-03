<?php
namespace Osynapsy\Core\Observer;

/**
 * Description of Observer
 *
 * @author Peter
 */
trait Observer 
{
    protected $observers = [];
    //add observer
    public function attach(InterfaceObserver $observer)
    {
        $this->observers[] = $observer;
    }
    
    //remove observer
    public function detach(InterfaceObserver $observer)
    {    
        $key = array_search($observer,$this->observers, true);
        if ($key) {
            unset($this->observers[$key]);
        }
    }
    
    private function loadObserver()
    {
        $observerList = $this->getRequest()->get('observers');
        if (empty($observerList)) {
            return;
        }
        $observers = array_keys($observerList, str_replace('\\', ':', get_class($this)));
        foreach($observers as $observer) {
            $observer = '\\'.trim(str_replace(':','\\',$observer));
            $this->attach(new $observer());
        }
    }
    
    public function notify()
    {
        //var_dump($this->observers);
        foreach ($this->observers as $value) {
            $value->update($this);
        }
    }
    
    private function setState( $state )
    {
        $this->state = $state;
        $this->notify();
    }
}
