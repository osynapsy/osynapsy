<?php
namespace Osynapsy\Observer;

interface InterfaceObserver
{
    public function update(InterfaceSubject $controller);
}