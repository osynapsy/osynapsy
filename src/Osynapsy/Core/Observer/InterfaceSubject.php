<?php
namespace Osynapsy\Core\Observer;

/**
 * Description of InterfaceSubject
 *
 * @author Peter
 */
interface InterfaceSubject
{
    public function attach ( InterfaceObserver $observer);
    
    public function detach ( InterfaceObserver $observer);
    
    public function notify ();
    
    public function getState();
}
