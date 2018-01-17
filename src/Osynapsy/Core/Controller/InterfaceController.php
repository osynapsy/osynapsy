<?php
namespace Osynapsy\Core\Controller;

use Osynapsy\Core\Network\Request;
use Osynapsy\Core\Data\Driver\DbFactory;

interface InterfaceController
{
    public function __construct(Request $request = null, DbFactory $db = null, $appController = null);
    
    public function getResponse();
}