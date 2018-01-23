<?php
namespace Osynapsy\Core\Controller;

use Osynapsy\Core\Route;

interface InterfaceApplication
{
    public function __construct($db, Route $route);
    
    public function run();
}
