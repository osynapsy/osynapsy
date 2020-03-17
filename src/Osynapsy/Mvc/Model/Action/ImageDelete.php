<?php
namespace Osynapsy\Mvc\Model\Action;

use Osynapsy\Mvc\Action\Base;

/**
 * Description of ImageDelete
 *
 * @author Pietro Celeste
 */
class ImageDelete extends Base
{
    private $fieldModel;
    
    public function execute()
    {                            
        $this->loadFieldModel();
        $this->deleteImageFromDb();        
        $this->deleteImageFromDisk();
        $this->refreshPage();
    }
    
    private function loadFieldModel()
    {
         $this->fieldModel = $this->getModel()->getField($this->parameters[1]);
    }
    
    public function deleteImageFromDisk()
    {
        $webPathImage = $this->getModel()->getRecord()->get($this->fieldModel->name);
        $documentRoot = filter_input(\INPUT_SERVER, 'DOCUMENT_ROOT');
        if (is_file($documentRoot.'/'.$webPathImage)) {
            @unlink($documentRoot.'/'.$webPathImage);
        }
    }
    
    public function deleteImageFromDb()
    {        
        $fieldDbName = $this->fieldModel->name;        
        $this->getModel()->getRecord()->save([$fieldDbName => null]);        
    }
    
    public function refreshPage()
    {
        $this->getResponse()->go('refresh');
    }
}
