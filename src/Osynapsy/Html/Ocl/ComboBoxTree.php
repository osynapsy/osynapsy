<?php
/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Ocl;

/**
 * Description of ComboBoxTree
 *
 * @author Pietro Celeste
 */
class ComboBoxTree extends ComboBox
{
    public $placeholder = ['', 'Seleziona .....', null];
    private $dataGroup = [];

    protected function __build_extra__()
    {
        $this->addClass('form-control');
        $this->requestValue = $this->getRequestValue();
        if (!$this->getParameter('option-select-disable')){
            array_unshift($this->data, $this->placeholder);
        }
        $this->treeFactory();
    }

    protected function getRequestValue()
    {
        $fieldName = $this->multiple ? str_replace('[]','',$this->name) : $this->name;
        return $this->getGlobal($fieldName, $_REQUEST);
    }

    protected function treeFactory()
    {
        $dataRoot = [];
        foreach ($this->data as $raw) {
            $record = array_values($raw);
            if (empty($record[2])) {
                $dataRoot[] = $record;
                continue;
            }
            if (array_key_exists($record[2], $this->dataGroup)) {
                $this->dataGroup[$record[2]][] = $record;
                continue;
            }
            $this->dataGroup[$record[2]] = array($record);
        }
        $this->branchFactory($dataRoot);
    }

    protected function branchFactory(array $data, $level = 0)
    {
        if (empty($data)) {
            return;
        }
        foreach ($data as $rec) {
            list($value, $label, ) = array_slice($rec, 0, 3);
            $label = str_repeat('&nbsp;', $level * 5) . $this->nvl($label, $value);
            $this->optionFactory($value, $label, 0);
            if (array_key_exists($value, $this->dataGroup)) {
                $this->branchFactory($this->dataGroup[$value], $level+1);
            }
        }
    }
}
