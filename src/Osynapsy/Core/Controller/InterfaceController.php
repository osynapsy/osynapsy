<?php
namespace Osynapsy\Core\Controller;

use Osynapsy\Core\Http\Request\Request;
use Osynapsy\Core\Data\Driver\DbFactory;

interface InterfaceController
{
    public function __construct(Request $request = null, DbFactory $db = null, $appController = null);
    
    public function getResponse();
}