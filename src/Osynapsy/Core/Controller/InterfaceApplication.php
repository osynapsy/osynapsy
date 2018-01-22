<?php
namespace Osynapsy\Core\Controller;

use Osynapsy\Core\Kernel\Route;

interface InterfaceApplication
{
    public function __construct($db, Route $route);
    
    public function run();
}
