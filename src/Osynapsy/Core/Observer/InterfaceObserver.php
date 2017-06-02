<?php
namespace Osynapsy\Core\Observer;

interface InterfaceObserver
{
    public function addObserver(InterfaceSubject $subject);
}