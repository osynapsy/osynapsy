<?php
namespace Osynapsy\Mvc;

use Osynapsy\Kernel\Route;

interface InterfaceApplication
{
    public function __construct($db, Route $route);
    
    public function run();
}
