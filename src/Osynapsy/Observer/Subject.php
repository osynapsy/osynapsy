<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Observer;

/**
 * Description of Observer
 *
 * @author Peter
 */
trait Subject 
{
    private $observers;
    
    //add observer
    public function attach(\SplObserver $observer)
    {
         $this->getObservers()->attach($observer);
    }
    
    //remove observer
    public function detach(\SplObserver $observer)
    {    
        $this->getObservers()->detach($observer);
    }
    
    private function loadObserver()
    {
        $observerList = $this->getRequest()->get('observers');
        if (empty($observerList)) {
            return;
        }
        $observers = array_keys($observerList, str_replace('\\', ':', get_class($this)));
        foreach($observers as $observer) {
            $observerClass = '\\'.trim(str_replace(':','\\',$observer));
            $this->attach(new $observerClass());
        }
    }
    
    public function notify()
    {        
        foreach ($this->getObservers() as $value) {
            $value->update($this);
        }
    }
    
    public function setState( $state )
    {
        $this->state = $state;
        $this->notify();
    }
    
    protected function getObservers()
    {
        if (is_null($this->observers)) {
            $this->observers = new \SplObjectStorage();
        }
        return $this->observers;
    }
}
