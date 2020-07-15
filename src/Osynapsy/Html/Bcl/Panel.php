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

//Costruttore del pannello html
class Panel extends Component
{
    private $cells = array();
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

    private function buildRow(array $Row)
    {
        ksort($Row);
        $this->appendRow();
        foreach ($Row as $Column) {
            $this->buildColumn($Column);
        }
    }

    private function buildColumn(array $Column)
    {
        foreach ($Column as $Cell) {
            $width = max($Cell['width'],1);
            $this->buildLabel($Cell);
            switch ($this->formType) {
                case 'horizontal':
                    $div = new Tag('div', null, 'col-sm-' . $width.' col-lg-'.$width);
                    $div->add($Cell['obj']);
                    $Cell['obj'] = $div;
                    break;
            }
            $this->buildCell($Cell, $width);
            break;
        }
    }

    private function buildCell($cell = null, $width = null)
    {
        if (is_null($cell)) {
            return;
        }
        $cel = $this->currentRow->add(new Tag('div'));
        if ($this->formType === 'horizontal') {
            $width += 4;
        }
        $class = ['col-sm-'.$width, 'col-lg-'.$width];
        if (!empty($cell['offset'])) {
            $class[] = 'col-lg-offset-'.$cell['offset'];
            $class[] = 'offset-lg-'.$cell['offset'];
        }
        if (!empty($cell['class'])) {
            $class[] =  $cell['class'];
        }
        $formGroup = $cel->att('class', implode(' ',$class))
                         ->add(new Tag('div', null, 'form-group'));
        if (!empty($this->classes['cell'])) {
            $cel->att('class', $this->classes['cell'], true);
        }
        unset($cell['width'], $cell['class'], $cell['offset']);
        $formGroup->addFromArray($cell);
        return $cel;
    }

    public function buildLabel(&$obj)
    {
        $style = '';
        if ($obj['lbl'] === false) {
            return;
        }
        if (is_object($obj['obj']) && ($obj['obj']->tag == 'button')) {
            $obj['lbl'] = '&nbsp';
            $style = 'display: block';
        } elseif (is_object($obj['obj']) && strpos($obj['obj']->class, 'label-block') !== false) {
            $style = 'display: block';
        }
        if (empty($obj['lbl'])) {
            return;
        }
        $labelText = $obj['lbl'];
        $label = new Tag('label', null, ($obj['obj'] instanceof panel ? 'osy-form-panel-label' : 'osy-component-label font-weight-500'));
        $label->att('for',is_object($obj['obj']) ? $obj['obj']->id : '')->add(trim($labelText));
        if (!empty($style)) {
            $label->att('style',$style);
        }
        if ($this->formType === 'horizontal') {
            $label->att('class','control-label col-sm-2 col-lg-2',true);
        }
        $obj['lbl'] = $label;
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
            $obj->att('data-label', strip_tags($lbl));
        }
        $this->cells[$row][$col][] = array(
            'lbl' => $lbl,
            'obj' => $obj,
            'width' => $width,
            'class' => $class,
            'offset' => $offset
        );
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
