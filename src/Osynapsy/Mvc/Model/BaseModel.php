<?php
namespace Osynapsy\Mvc\Model;

use Osynapsy\Mvc\InterfaceController;
use Osynapsy\Mvc\InterfaceModel;
use Osynapsy\Mvc\Helper\UploadManager;
use Osynapsy\Mvc\Model\Field as ModelField;

/**
 * Description of BaseModel
 *
 * @author Pietro
 */
abstract class BaseModel implements InterfaceModel
{
    const ACTION_AFTER_INSERT_HISTORY_PUSH_STATE = 'historyPushState';
    const ACTION_AFTER_EXEC_BACK = 'back';
    const ACTION_AFTER_EXEC_NONE = false;
    const ACTION_AFTER_EXEC_REFRESH = 'refresh';

    private $controller;
    protected $actions = [];
    protected $fields = [];
    protected $errorMessages = [
        'email' => 'Il campo <fieldname> non contiene un indirizzo mail valido.',
        'fixlength' => 'Il campo <fieldname> solo valori con lunghezza pari a ',
        'integer' => 'Il campo <fieldname> accetta solo numeri interi.',
        'maxlength' => 'Il campo <fieldname> accetta massimo ',
        'minlength' => 'Il campo <fieldname> accetta minimo ',
        'notnull' => 'Il campo <fieldname> è obbligatorio.',
        'numeric' => 'Il campo <fieldname> accetta solo valori numerici.',
        'unique' => '<value> è già presente in archivio.'
    ];

    public function __construct(InterfaceController $controller)
    {
        $this->controller = $controller;
        $this->setAfterAction(
            $this->getController()->getRequest()->get('page.url'),
            self::ACTION_AFTER_EXEC_BACK,
            self::ACTION_AFTER_EXEC_BACK
        );
    }

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

    protected function execAfterAction($actionId, $lastId = null)
    {
        switch ($this->actions[$actionId]) {
            case self::ACTION_AFTER_EXEC_NONE:
                break;
            case self::ACTION_AFTER_INSERT_HISTORY_PUSH_STATE:
                $this->getResponse()->js("history.pushState(null,null,'{$lastId}');");
                break;
            case self::ACTION_AFTER_EXEC_BACK:
            case self::ACTION_AFTER_EXEC_REFRESH:
                $this->getResponse()->go($this->actions[$actionId]);
                break;
            default:
                $this->getResponse()->go($this->actions[$actionId].$lastId);
                break;
        }
    }

    public function map($formField, $dbField = null, $defaultValue = null, $type = 'string')
    {
        $formValue = isset($_REQUEST[$formField]) ? $_REQUEST[$formField] : null;
        $modelField = new ModelField($this, $dbField, $formField, $type, isset($_REQUEST[$formField]));
        $modelField->setValue($formValue, $defaultValue);
        $this->fields[$modelField->html] = $modelField;
        return $modelField;
    }

    protected function addFieldError($errorId, $field, $postfix = '')
    {
        $error = str_replace(['<fieldname>', '<value>'], ['<!--'.$field->html.'-->', $field->value], self::ERROR_MESSAGES[$errorId].$postfix);
        $this->addError($error, $field->html);
    }

    protected function addError($errorMessage, $target = 'alert')
    {
        if (!empty($errorMessage)) {
            $this->getResponse()->error($target, $errorMessage);
        }
    }

    /**
     *
     * @return void
     */
    public function save()
    {
        //Recall before exec method with arbirtary code
        $this->addError($this->beforeExec());
        //Init arrays
        $keys = $values = $where = [];
        //skim the field list for check value and build $values, $where and $key list
        foreach ($this->fields as $field) {
            $value = $field->value;
            //Check if value respect rule
            $this->validateFieldValue($field, $value);
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
        $this->afterExec();
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
        $this->actions['after-update'] = 'refresh';
        $this->afterUpload($field->value, $field);
        return $field->value;
    }

    /**
     * This method validate value of field.
     *
     * @param ModelField $field
     * @param type $value
     */
    protected function validateFieldValue(ModelField $field, $value)
    {
        if (!$field->isNullable()) {
            $this->validateNotNullValue($field, $value);
        }
        if ($field->isUnique() && $value) {
            $this->validateUniqueValue($field, $value);
        }
        $this->validateValueLength($field, $value);
        switch ($field->type) {
            case 'float':
            case 'money':
            case 'numeric':
            case 'number':
                $this->validateFloatValue($field, $value);
                break;
            case 'integer':
            case 'int':
                $this->validateIntegerValue($field, $value);
                break;
            case 'email':
                $this->validateEmailAddressValue($field, $value);
                break;
        }
    }

    protected function validateNotNullValue(ModelField $field, $value)
    {
        if ($value !== '0' && empty($value)) {
            $this->addFieldError('notnull', $field);
        }
    }

    protected function validateUniqueValue(ModelField $field, $value)
    {
        $sql = sprintf("SELECT COUNT(*) FROM %s WHERE %s = ?", $this->table, $field->name);
        $nOccurence = $this->getDb()->findOne($sql, [$value]);
        if (!empty($nOccurence)) {
            $this->addFieldError('unique', $field);
        }
    }

    protected function validateValueLength(ModelField $field, $value)
    {
        //Controllo la lunghezza massima della stringa. Se impostata.
        if ($field->maxlength && (strlen($value) > $field->maxlength)) {
            $this->addFieldError('maxlength', $field, $field->maxlength.' caratteri');
        } elseif ($field->minlength && (strlen($value) < $field->minlength)) {
            $this->addFieldError('minlength', $field, $field->minlength.' caratteri');
        } elseif ($field->fixlength && !in_array(strlen($value),$field->fixlength)) {
            $this->addFieldError('fixlength', $field, implode(' o ',$field->fixlength).' caratteri');
        }
    }

    protected function validateFloatValue(ModelField $field, $value)
    {
        if ($value && filter_var($value, \FILTER_VALIDATE_FLOAT) === false) {
            $this->addFieldError('numeric', $field);
        }
    }

    protected function validateIntegerValue(ModelField $field, $value)
    {
        if ($value && filter_var($value, \FILTER_VALIDATE_INT) === false) {
            $this->addFieldError('integer', $field);
        }
    }

    protected function validateEmailAddressValue(ModelField $field, $value)
    {
        if (!empty($value) && filter_var($value, \FILTER_VALIDATE_EMAIL) === false) {
            $this->addFieldError('email', $field);
        }
    }

    protected function setAfterAction($insert, $update, $delete)
    {
        $this->actions = [
            'after-insert' => $insert ?? $this->actions['after-insert'],
            'after-update' => $update ?? $this->actions['after-update'],
            'after-delete' => $delete ?? $this->actions['after-delete']
        ];
    }

    abstract protected function init();

    abstract protected function insert(array $values);

    abstract protected function update(array $values);

    abstract public function find();

    abstract public function delete();

    protected function afterInit()
    {
    }

    protected function afterUpload($filename, $field = null)
    {
    }

    protected function beforeExec()
    {
    }

    protected function beforeInsert()
    {
    }

    protected function beforeUpdate()
    {
    }

    protected function beforeDelete()
    {
    }

    protected function afterExec()
    {
    }

    protected function afterInsert($id)
    {
    }

    protected function afterUpdate()
    {
    }

    protected function afterDelete()
    {
    }
}
