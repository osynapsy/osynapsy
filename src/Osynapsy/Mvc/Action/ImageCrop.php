<?php
namespace Osynapsy\Mvc\Action;

use Osynapsy\Helper\ImageProcessing\Image;

/**
 * Description of CropImage
 *
 * @author Pietro
 */
class ImageCrop implements Base
{    
    protected $field; 
    private $targetFile;
    private $pathinfo = [];
    
    public function execute()
    {        
        if (empty($this->field)) {
            throw new Exception("Property \$field isn't set.");
        }
        $this->init();
        $this->crop(
            $this->parameters[0],
            $this->parameters[1],
            $this->parameters[2],
            $this->parameters[3],
            $this->parameters[4]
        );
    }
    
    private function init()
    {        
        $this->targetFile = $this->getModel()->getRecord()->get($this->field);
        if (empty($this->targetFile)) {
            return;
        }
        $this->pathinfo = pathinfo($this->targetFile);         
    }
            
    public function crop($cropWidth, $cropHeight, $cropX, $cropY, $filename, $newWidth = null, $newHeight = null)
    {       
        $img = new Image('.'.$this->targetFile);        
        $img->crop($cropX, $cropY, $cropWidth, $cropHeight);
        if (!empty($filename) && $filename[0] !== '/') {
            $filename = $this->pathinfo['dirname'].'/'.$filename.'.'.$this->pathinfo['extension'];
        }
        if (!empty($newWidth) && !empty($newHeight)) {
            $img->resize($newWidth, $newHeight);
        }
        $img->save('.'.$filename);                
        $this->getModel()->getRecord()->save([$this->field => $filename]);
    }    
        
    public function getTarget()
    {
        return $this->targetFile;
    }
    
    public function getInfo($key)
    {
        if (array_key_exists($key, $this->pathinfo)) {
            return $this->pathinfo[$key];
        }
    }
}
