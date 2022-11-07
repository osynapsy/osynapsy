<?php
namespace Osynapsy\Mvc\Model;

use Osynapsy\Mvc\Controller\InterfaceController;
use Osynapsy\Mvc\Model\InterfaceModel;
use Osynapsy\Mvc\Helper\UploadManager;
use Osynapsy\Mvc\Model\Field\Field as ModelField;

/**
 * Description of BaseModel
 *
 * @author Pietro
 */
abstract class BaseModel implements InterfaceModel
{
    protected $controller;
    protected $fields = [];
    protected $fieldUploaded = false;

    public function __construct(InterfaceController $controller)
    {
        $this->setController($controller);
    }

    protected function addError($errorMessage, $target = 'alert', $postfix = '')
    {
        if (!empty($errorMessage)) {
            $this->getResponse()->error($target, $errorMessage . $postfix);
        }
    }

    protected function afterInit()
    {
    }

    protected function afterDelete()
    {
        $this->gotoPreviusPage();
    }

    protected function afterInsert($id)
    {
        $this->reloadCurrentPage($id);
    }

    protected function afterUpdate()
    {
        if ($this->fieldUploaded) {
            $this->refreshCurrentPage();
        } else {
            $this->gotoPreviusPage();
        }
    }

    protected function afterSave()
    {
    }

    protected function afterUpload($filename, $field = null)
    {
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
            $this->getResponse()->js("parent.Osynpasy.refreshComponents(['%']);", implode("','",$parentComponentRefresh));
        }
        $this->getResponse()->js("parent.$('#".$modalId.").modal('hide')");
    }

    abstract public function delete();

    abstract public function find();

    public function getController() : InterfaceController
    {
        return $this->controller;
    }

    public function getDb()
    {
        return $this->getController()->getDb();
    }

    public function getField($fieldId)
    {
        return $this->fields[$fieldId];
    }

    public function getResponse()
    {
        return $this->getController()->getResponse();
    }

    abstract public function getTable();

    protected function gotoPreviusPage()
    {
        $this->getResponse()->go('back');
    }

    protected function historyPushState($id)
    {
        $this->getResponse()->js("history.pushState(null,null,'{$id}');");
    }

    abstract protected function init();

    abstract protected function insert(array $values, $keys);

    public function map($formField, $dbField = null, $defaultValue = null, $type = 'string')
    {
        $formValue = isset($_REQUEST[$formField]) ? $_REQUEST[$formField] : null;
        $modelField = new ModelField($this, $dbField, $formField, $type, isset($_REQUEST[$formField]));
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
        $currentPageUrl = $this->getController()->getRequest()->get('page.url');
        $this->getResponse()->go($currentPageUrl.$id);
    }

    protected function refreshCurrentPage()
    {
        $this->getResponse()->go('refresh');
    }

    protected function setAfterAction($insert, $update, $delete)
    {
        $this->actions = [
            'after-insert' => $insert ?? $this->actions['after-insert'],
            'after-update' => $update ?? $this->actions['after-update'],
            'after-delete' => $delete ?? $this->actions['after-delete']
        ];
    }

    protected function setController(InterfaceController $controller)
    {
        $this->controller = $controller;
    }

    protected function setFileUploaded($value)
    {
        $this->fileUploaded = $value;
    }

    abstract protected function update(array $values, $where);

    /**
     *
     * @return void
     */
    public function save()
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
            if (in_array($field->type, ['file', 'image'])) {
                $value = $this->grabUploadedFile($field);
            }
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
        if ($this->getResponse()->error()) {
            return;
        }
        //If where condition is empty execute db insert else execute a db update
        if (empty($where)) {
            $this->insert($values, $keys);
        } else {
            $this->update($values, $where);
        }
        //Recall after exec method with arbirtary code
        $this->afterSave();
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

    private function grabUploadedFile(&$field)
    {
        if (!is_array($_FILES) || !array_key_exists($field->html, $_FILES) || empty($_FILES[$field->html]['name'])) {
            $field->readonly = true;
            return $field->value;
        }
        $upload = new UploadManager();
        try {
            $field->value = $upload->saveFile($field->html, $field->uploadDir);
            $field->readonly = false;
        } catch(\Exception $e) {
            $this->addError($e->getMessage());
            $field->readonly = true;
        }
        $this->setFileUploaded(true);
        $this->afterUpload($field->value, $field);
        return $field->value;
    }
}
