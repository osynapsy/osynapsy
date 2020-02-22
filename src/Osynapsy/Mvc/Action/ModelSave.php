<?php
namespace Osynapsy\Mvc\Action;

use Osynapsy\Mvc\Model\ModelErrorException;

/**
 * Description of Save
 *
 * @author Pietro
 */
class ModelSave extends Base
{                
    public function execute()
    {        
        try {
            $this->getController()->getModel()->save();
            $this->afterExecute();
        } catch (ModelErrorException $e) {
            $this->sendErrors($e->getErrors());
        } catch (\Exception $e) {
            $this->sendErrors(['alert' => $e->getMessage()]);
        }
    }
            
    protected function afterExecute()
    {
        if ($this->getModel()->uploadOccurred) {
            $this->historyPushState($this->getModel()->getLastId());
            $this->pageRefresh();
            return;
        }
        if ($this->getModel()->behavior === 'insert') {
            $this->afterInsert();
            return;
        }
        $this->afterUpdate();
    }       
    
    protected function afterInsert()
    {
        $this->historyPushState($this->getModel()->getLastId());
        $this->pageRefresh();
    }
    
    protected function afterUpdate()
    {
        $this->pageBack();
    }
    
    protected function pageBack()
    {
        $this->getResponse()->go('back');
    }
    
    protected function pageRefresh()
    {        
        $this->getResponse()->go('refresh');
    }
    
    protected function historyPushState($parameterToUrlAppend)
    {
        if (empty($parameterToUrlAppend)) {
            return;
        }
        $this->getResponse()->js("history.pushState(null,null,'{$parameterToUrlAppend}');");
    }
    
   
    
    private function sendErrors($errors)
    {
        foreach($errors as $fieldHtml => $error) {
            $this->getController()->getResponse()->error($fieldHtml, $error);
        }
    }        
}
