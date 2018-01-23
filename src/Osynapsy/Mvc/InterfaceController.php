<?php
namespace Osynapsy\Mvc;

use Osynapsy\Http\Request;
use Osynapsy\Db\DbFactory;

interface InterfaceController
{
    public function __construct(Request $request = null, DbFactory $db = null, $appController = null);
    
    public function getResponse();
}