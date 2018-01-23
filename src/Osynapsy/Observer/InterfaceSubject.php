<?php
namespace Osynapsy\Observer;

/**
 * Description of InterfaceSubject
 *
 * @author Peter
 */
interface InterfaceSubject extends \SplSubject
{        
    public function getState();
}
