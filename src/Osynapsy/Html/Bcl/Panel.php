<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Tag as Tag;
use Osynapsy\Html\Component as Component;

/**
 * Build a html panel. Ispirate to thinker python panel
 *
 */
class Panel extends Component
{
    const FORM_TYPE_HORIZONTAL = 'horizontal';

    private $cells = [];
    private $currentRow = null;
    private $tag = ['div' , 'div'];
    private $formType = 'normal';
    private $classes = [
        'main' => 'panel',
        'head' => 'panel-heading',
        'body' => 'panel-body',
        'foot' => 'panel-footer',
        'row'  => 'row',
        'cell' => null
    ];
    private $head;
    private $body;
    private $foot;

    public function __construct($id, $tag = 'fieldset', $rowClass = null, $cellClass = null)
    {
        parent::__construct($tag, $id);
        $this->setParameter('label-position','outside');
        if (!empty($rowClass)) {
            $this->classes['row'] = $rowClass;
        }
        if (!empty($cellClass)) {
            $this->classes['cell'] = $cellClass;
        }
    }

    protected function __build_extra__()
    {
        $this->setClass($this->getClass('main'));
        $this->bodyFactory();
        if ($this->head) {
            $this->add($this->head);
        }
        if ($this->body) {
            $this->add($this->body);
        }
        if ($this->foot) {
            $this->add($this->foot);
        }
    }

    public function append($content)
    {
        if (empty($this->body)) {
            $this->body = new Tag('div', null, $this->classes['body']);
        }
        if ($content) {
            $this->body->add($content);
            return $content;
        }
    }

    private function appendRow()
    {
        $this->currentRow = $this->append(new Tag($this->tag[0]));
        $this->currentRow->att('class', $this->classes['row']);
        return $this->currentRow;
    }

    public function appendToHead($title, $dim = 0)
    {
        if (empty($this->head)) {
            $this->head = new Tag('div');
            $this->head->att('class', $this->classes['head']);
        }
        if ($dim) {
            $this->head->add(new Tag('h'.$dim))->add($title);
        } else {
            $this->head->add($title);
        }
    }

    public function appendToFoot($content)
    {
        if (empty($this->foot)) {
            $this->foot = new Tag('div', null, $this->classes['foot']);
        }
        $this->foot->add($content);
        return $content;
    }

    private function bodyFactory()
    {
        ksort($this->cells);
        foreach($this->cells as $Row) {
            $this->buildRow($Row);
        }
    }

    private function buildRow(array $row)
    {
        $this->appendRow();
        ksort($row);
        foreach ($row as $cells) {
            ksort($cells);
            foreach ($cells as $cellData) {
                $this->currentRow->add($this->buildCell(
                    $cellData,
                    max($cellData['width'], 1)
                ));
            }
        }
    }

    private function buildCell($cellData, $width)
    {
        $label = $cellData['lbl'];
        $content = $cellData['obj'];
        $Cell = new Tag('div', null , $this->buildCellClass($cellData, $width));
        $Cell->add(new Tag('div', null, 'form-group'))->addFromArray(array_merge(
            $label === false ? [] : [$this->buildLabel($label, $content)],
            is_array($content) ? $content : [$content]
        ));
        return $Cell;
    }

    protected function buildCellClass($cell, $width)
    {
        if ($this->formType === self::FORM_TYPE_HORIZONTAL) {
            return 'cell-lg-8 cell-sm-8';
        }
        $class = ['col-sm-'.$width, 'col-lg-'.$width];
        if (!empty($cell['offset'])) {
            $class[] = 'col-lg-offset-'.$cell['offset'];
            $class[] = 'offset-lg-'.$cell['offset'];
        }
        if (!empty($cell['class'])) {
            $class[] =  $cell['class'];
        }
        if (!empty($this->classes['cell'])) {
            $class[] = $this->classes['cell'];
        }
        return implode(' ', $class);
    }

    private function buildLabel($rawCellLabel, $cellContent)
    {
        $cellLabel = is_array($rawCellLabel) ? array_shift($rawCellLabel) : $rawCellLabel;
        if (is_a($cellContent, 'Tag') && ($cellContent->tag == 'button')) {
            $cellLabel = '&nbsp';
        }
        if (empty($cellLabel)) {
            return;
        }
        $container = new Tag('div');
        $label = $container->add(new Tag('label', null, $this->buildLabelClass($cellContent)));
        $label->att('for', is_a($cellContent, 'Tag') ? $cellContent->id : '');
        $label->add(trim($cellLabel));
        if (is_array($rawCellLabel)) {
            $container->addFromArray($rawCellLabel);
        }
        return $container;
    }

    private function buildLabelClass($cellContent)
    {
        $labelClass = [$cellContent instanceof panel ? 'osy-form-panel-label' : 'osy-component-label font-weight-500'];
        if ($this->formType === 'horizontal') {
            $labelClass[] = 'control-label col-sm-2 col-lg-2';
        }
        return implode(' ', $labelClass);
    }

    public function getClass($part = null)
    {
        if (is_null($part)) {
            return $this->classes;
        }
        return $this->classes[$part];
    }

    public function put($lbl, $obj, $row = 0, $col = 0, $width=1, $offset = null, $class = '')
    {
        if ($obj instanceof Tag) {
            $obj->att('data-label', strip_tags(is_array($lbl) ? $lbl[0] : $lbl));
        }
        $this->cells[$row][$col][] = [
            'lbl' => $lbl,
            'obj' => $obj,
            'width' => $width,
            'class' => $class,
            'offset' => $offset
        ];
    }

    public function setBodyClass($class)
    {
        $this->setClassPart('body', $class);
    }

    public function setClassPart($part, $class)
    {
        $this->classes[$part] = $class;
    }

    public function setClasses($main, $head, $body, $foot, $row = 'row', $cell = null)
    {
        $this->setClassPart('main', $main);
        $this->setClassPart('head', $head);
        $this->setClassPart('body', $body);
        $this->setClassPart('foot', $foot);
        $this->setClassPart('row', $row);
        $this->setClassPart('cell', $cell);
    }

    public function setType($type)
    {
        $this->formType = $type;
    }
}
