<?php
namespace Osynapsy\Mvc\Model\Action;

use Osynapsy\Mvc\Action\Base;
use Osynapsy\Helper\ImageProcessing\Image;

/**
 * Description of ImageDelete
 *
 * @author Pietro Celeste
 */
class ImageCrop extends Base
{
    protected $documentRoot;
    protected $fieldModel;
    protected $fileName;
    protected $imageHandler;
            
    public function execute()
    {        
        try {
            $this->loadDocumentRoot();
            $this->loadModelField();
            $this->loadFileName();
            $this->loadImage();        
            $this->cropImage();
            $this->resizeImage();
            $this->saveImageOnDisk();
            $this->refreshPage();
        } catch (\Exception $e) {
            $this->getResponse()->alertJs($e->getMessage());
        }
    }
    
    protected function loadDocumentRoot()
    {
        $this->documentRoot = filter_input(\INPUT_SERVER, 'DOCUMENT_ROOT');
        if (substr($this->documentRoot, -1) !== '/') {
            $this->documentRoot .= '/';
        }
    }
    
    protected function loadFileName()
    {        
        $fieldDbName = $this->fieldModel->name;
        $this->fileName = $this->documentRoot.$this->getModel()->getRecord()->get($fieldDbName);
    }
    
    protected function loadModelField()
    {
        $this->getModel()->find(); 
        $this->fieldModel = $this->getModel()->getField($this->parameters[4]);
    }
    
    protected function loadImage()
    {            
        $this->imageHandler = new Image($this->fileName);
    }        
    
    protected function cropImage()
    {                                
        $cropWidth = $this->parameters[0];
        $cropHeight = $this->parameters[1];
        $cropX = $this->parameters[2];
        $cropY = $this->parameters[3];                
        $this->imageHandler->crop($cropX, $cropY, $cropWidth, $cropHeight);        
    }
    
    protected function resizeImage()
    {
        if (empty($this->parameters[5]) || empty($this->parameters[6])) {
            return;
        }
        $resizeWidth = $this->parameters[5];
        $resizeHeight = $this->parameters[6];
        $this->imageHandler->resizeAdaptive($resizeWidth, $resizeHeight);
    }
    
    protected function refreshPage()
    {
        $this->getResponse()->clearCache();
        $this->getResponse()->go('refresh');
    }        
    
    protected function saveFileNameOnDb($fileName)
    {        
        $fieldDbName = $this->fieldModel->name;
        $this->getModel()->getRecord()->save([$fieldDbName => $fileName]);
    }
    
    protected function saveImageOnDisk()
    {
        $this->imageHandler->save($this->fileName);
    }
}
