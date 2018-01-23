<?php
namespace Osynapsy\Mvc;

interface InterfaceModel
{
    public function init($controller);
    
    public function find();
    
    public function delete();
    
    public function insert();
    
    public function update();
}
