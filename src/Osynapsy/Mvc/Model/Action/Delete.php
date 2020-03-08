<?php
namespace Osynapsy\Mvc\Action;

use Osynapsy\Mvc\Action\Base;

/**
 * Description of ModelDelete action;
 *
 * @author Pietro
 */
class Delete extends Base
{        
    public function execute()
    {        
        try {
            $this->controller->getModel()->delete();
            $this->afterDelete();
        } catch(\Exception $e) {
            $this->controller->getResponse()->alertJs($e->getMessage());
        }        
    }
    
    public function afterDelete()
    {
        $this->controller->getResponse()->go('back');
    }        
}
