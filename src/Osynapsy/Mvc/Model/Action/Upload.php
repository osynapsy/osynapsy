<?php
namespace Osynapsy\Mvc\Model\Action;

use Osynapsy\Helper\Net\UploadManager;
use Osynapsy\Mvc\Action\Base;

/**
 * Description of Upload
 *
 * @author pietr
 */
class Upload extends Base
{    
    protected $uploadSuccessful = 0;
    protected $uploadManager; 


    public function execute()
    {
        if (!is_array($_FILES)) {
            return;
        }
        foreach (array_keys($_FILES) as $fieldName) {
            if (empty($_FILES[$fieldName]) || empty($_FILES[$fieldName])) {
                continue;
            }
            $this->grabFile($fieldName);
        }
        if (empty($this->uploadSuccessful)) {
            return;
        }
        $this->getModel()->save();
        if ($this->getModel()->behavior === 'insert') {
            $this->getResponse()->historyPushState($this->getModel()->getLastId());            
        }
        $this->getResponse()->pageRefresh();
    }    
    
    protected function getUploadManager()
    {
        if (empty($this->uploadManager)) {
            $this->uploadManager = new UploadManager();
        }
        return $this->uploadManager;
    }

    protected function grabFile($fieldName)
    {
        try {
            $field = $this->getModelField($fieldName);            
            $field->value = $this->getUploadManager()->saveFile($field->html, $field->uploadDir);            
            $field->readonly = false;            
            $this->uploadSuccessful++;
        } catch (\Exception $e) {
            if ($e->getCode() != 404) { 
                $this->getResponse()->alertJs($e->getMessage());
            }
        }
    }
    
    protected function getModelField($fieldName)
    {
        $field = $this->getModel()->getField($fieldName);
        if (empty($field)) {
            throw new \Exception('Field not found', 404);
        }
        return $field;
    }
    
    
}
