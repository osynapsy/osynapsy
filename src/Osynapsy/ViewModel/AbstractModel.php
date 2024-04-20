<?php
namespace Osynapsy\ViewModel;

use Osynapsy\Controller\ControllerInterface;
use Osynapsy\ViewModel\ModelInterface;
use Osynapsy\ViewModel\Field\Field as ModelField;
use Osynapsy\Database\Driver\DboInterface;

/**
 * Description of BaseModel
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
abstract class AbstractModel implements ModelInterface
{
    protected $controller;
    protected $fields = [];
    protected $redirect;
    protected $exception;

    public function __construct(ControllerInterface $controller)
    {
        $this->setController($controller);
        $this->exception = new ModelErrorException;
    }

    protected function addError($errorMessage, $target = null, $postfix = '')
    {
        if (empty($errorMessage)) {
            return;
        }
        if(is_null($target)) {
            $this->exception->addError($errorMessage . $postfix);
        } else {
            $this->exception->addErrorOnField($target, $errorMessage);
        }
    }

    protected function afterDelete()
    {
        $this->redirect = [$this,  'gotoPreviusPage'];
    }

    protected function afterInsert($id)
    {
        $this->redirect = function() use ($id) {
            $this->reloadCurrentPage($id);
        };
    }

    protected function afterUpdate()
    {
        $this->redirect = [$this, 'gotoPreviusPage'];
    }

    protected function afterSave()
    {
    }

    protected function afterExec()
    {
        $redirect = $this->redirect;
        if (!empty($redirect) && is_callable($redirect)) {
            $redirect();
        }
    }

    protected function beforeDelete()
    {
    }

    protected function beforeInsert()
    {
    }

    protected function beforeUpdate()
    {
    }

    protected function beforeSave()
    {
    }

    protected function closeModal(array $parentComponentRefresh = [], $modalId = 'amodal')
    {
        if (!empty($parentComponentRefresh)) {
            $this->getController()->refreshParentComponents($parentComponentRefresh);
        }
        $this->getController()->closeModal();
    }

    abstract public function delete() : bool;

    public function getController() : ControllerInterface
    {
        return $this->controller;
    }

    public function getDb() : DboInterface
    {
        return $this->getController()->getDb();
    }

    public function getField($fieldId)
    {
        if (!array_key_exists($fieldId, $this->fields)) {
            throw new \Exception(sprintf('Field %s do not exists in fieldmap', $fieldId));
        }
        return $this->fields[$fieldId];

    }

    public function getRequest()
    {
        return $this->getController()->getRequest();
    }

    public function getResponse()
    {
        return $this->getController()->getResponse();
    }

    abstract public function getTable();

    protected function gotoPreviusPage()
    {
        redirect('back');
    }

    protected function historyPushState($id)
    {
        $this->getController()->js("history.pushState(null,null,'{$id}');");
    }

    abstract protected function insert(array $values);

    public function map($formField, $dbField = null, $defaultValue = null, $type = 'string', $existInForm = null)
    {
        $fieldExistInForm = is_null($existInForm) ? isset($_REQUEST[$formField]) : true;
        $formValue = isset($_REQUEST[$formField]) ? $_REQUEST[$formField] : null;
        $modelField = new ModelField($this, $dbField, $formField, $type, $fieldExistInForm);
        $modelField->setValue($formValue, $defaultValue);
        $this->fields[$modelField->html] = $modelField;
        return $modelField;
    }

    protected function raiseException($errorMessage, $errorNum = 500)
    {
        throw new ModelException($errorMessage, $errorNum);
    }

    protected function reloadCurrentPage($id = '')
    {
        redirect(request('page.url') . $id);
    }

    protected function refreshCurrentPage()
    {
        redirect('refresh');
    }

    protected function setController(ControllerInterface $controller)
    {
        $this->controller = $controller;
    }

    protected function setFileUploaded($value)
    {
        $this->fileUploaded = $value;
    }

    abstract protected function update(array $values);

    /**
     *
     * @return bool
     */
    public function save() : bool
    {
        //Recall before exec method with arbirtary code
        $this->addError($this->beforeSave());
        //Init arrays
        $keys = $values = $where = [];
        //skim the field list for check value and build $values, $where and $key list
        $validator = new Field\Validator($this);
        foreach ($this->fields as $field) {
            //$value = $field->value;
            //Check if value respect rule
            //$this->validateFieldValue($field, $value);
            $value = $this->validateFieldValue($field, $validator);
            //If field isn't in readonly mode assign values to values list for store it in db
            if (!$field->readonly) {
                $values[$field->name] = $value;
            }
            //If field isn't primary key skip key assignment
            if (!$field->isPkey()) {
                continue;
            }
            //Add field to keys list
            $keys[] = $field->name;
            //If field has value assign field to where condition
            if (!empty($value)) {
                $where[$field->name] = $value;
            }
        }
        //If occurred some error stop db updating
        if ($this->exception->hasErrors()) {
            throw $this->exception;
        }
        //If where condition is empty execute db insert else execute a db update
        if (empty($where)) {
            $this->insert($values, $keys);
        } else {
            $this->update($values, $where);
        }
        //Recall after exec method with arbirtary code
        $this->afterSave();
        $this->afterExec();
        return true;
    }

    protected function validateFieldValue(ModelField $field, Field\Validator $validator)
    {
        try {
            $validator->validate($field);
        } catch (\Exception $e) {
            $this->addError($e->getMessage(), $field->html);
        } finally {
            return $field->value;
        }
    }

    public function getFieldValue($fieldId)
    {
        $valueInRequest = filter_input(\INPUT_POST, $fieldId);
        if ($valueInRequest) {
            return $valueInRequest;
        }
        if (!array_key_exists($fieldId, $this->fields)) {
            return null;
        }
        $dbFieldName = $this->fields[$fieldId]->name;
        return $this->getValue($dbFieldName);
    }
}
