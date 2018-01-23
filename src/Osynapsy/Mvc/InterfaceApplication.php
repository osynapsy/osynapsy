<?php
namespace Osynapsy\Mvc;

use Osynapsy\Core\Route;

interface InterfaceApplication
{
    public function __construct($db, Route $route);
    
    public function run();
}
