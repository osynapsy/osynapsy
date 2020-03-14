<?php
namespace Osynapsy\Mvc\Model\Action;

use Osynapsy\Mvc\Model\ModelErrorException;
use Osynapsy\Mvc\Action\Base;

/**
 * Description of Save
 *
 * @author Pietro
 */
class Save extends Base
{                
    protected $events = [
        'beforeExecute' => null,
        'beforeInsert' => null,    
        'beforeUpdate' => null,    
        'beforeUpload' => null,
        'afterExecute' => null,    
        'afterInsert'  => null,
        'afterUpdate'  => null,
        'afterUpload'  => null
    ];
    
    public function __construct()
    {
        $this->setTrigger('afterExecute', [$this, 'afterExecute']);
        $this->setTrigger('afterInsert', [$this, 'afterInsert']);
        $this->setTrigger('afterUpdate', [$this, 'afterUpdate']);
        $this->setTrigger('afterUpload', [$this, 'afterUpload']);
    }
    
    public function execute()
    {        
        try {
            $this->executeTrigger('beforeExecute');
            $this->getController()->getModel()->save();
            $this->executeTrigger('afterExecute');
        } catch (ModelErrorException $e) {
            $this->sendErrors($e->getErrors());
        } catch (\Exception $e) {
            $this->sendErrors(['alert' => $e->getMessage()]);
        }
    }
    
    protected function executeTrigger($eventId)
    {
        if (empty($this->events[$eventId])) {
            return;
        }        
        call_user_func($this->events[$eventId], $this);
    }

    public function afterExecute()
    {
        if ($this->getModel()->uploadOccurred) {
            $this->afterUpload();
            return;
        }
        if ($this->getModel()->behavior === 'insert') {
            $this->afterInsert();
            return;
        }
        $this->afterUpdate();
    }       

    public function afterInsert()
    {
        $this->historyPushState($this->getModel()->getLastId());
        $this->pageRefresh();
    }

    public function afterUpdate()
    {
        $this->pageBack();
    }
    
    public function afterUpload()
    {
        $this->historyPushState($this->getModel()->getLastId());
        $this->pageRefresh();
    }
    
    public function pageBack()
    {
        $this->getResponse()->go('back');
    }
    
    public function pageRefresh()
    {        
        $this->getResponse()->go('refresh');
    }
    
    public function historyPushState($parameterToUrlAppend)
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

    public function setTrigger($event, callable $function)
    {
        $this->events[$event] = $function;
    }
}
