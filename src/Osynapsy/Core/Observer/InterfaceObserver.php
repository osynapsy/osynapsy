<?php
namespace Osynapsy\Core\Observer;

interface InterfaceObserver
{
    public function update(InterfaceSubject $controller);
}