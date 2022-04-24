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

use Osynapsy\Html\Tag;
use Osynapsy\Html\Bcl\PanelNew;

/**
 * Description of Adressbook
 *
 * @author Peter
 */
class Addressbook extends PanelNew
{
    protected $columns = 4;
    protected $foot;
    protected $emptyMessage;
    protected $itemSelected;
    protected $paginator;
    protected $showPaginationPageDimension;
    protected $showPaginationPageInfo;

    public function __construct($id, $emptyMessage = 'Addressbook is empty', $columns = 4)
    {
        parent::__construct($id);
        $this->setClass('','','','osy-addressbook');
        $this->columns = $columns;
        $this->emptyMessage = $emptyMessage;
        $this->requireCss('Bcl/Addressbook/style.css');
        $this->requireJs('Bcl/Addressbook/script.js');
    }

    protected function __build_extra__()
    {
        if (!empty($this->paginator)) {
            try {
                $this->setData($this->paginator->loadData(null, true));
                $this->buildPagination($this->paginator);
            } catch (\Exception $e) {
                $this->emptyMessage = $e->getMessage();
            }
        }
        if (empty($this->data)) {
            $this->addColumn(12)->add($this->emptyMessageFactory($this->emptyMessage));
            parent::__build_extra__();
            return;
        }
        $this->itemSelected = empty($_REQUEST[$this->id.'_chk']) ? [] : $_REQUEST[$this->id.'_chk'];
        $this->bodyFactory();
        if ($this->foot) {
            $this->addColumn(12)->add($this->foot);
        }
        parent::__build_extra__();
    }

    protected function emptyMessageFactory($emptyMessage)
    {
        return sprintf('<div class="osy-addressbook-empty mt-5 mb-5"><span>%s</span></div>', $emptyMessage);
    }

    protected function bodyFactory()
    {
        $columnLength = floor(12 / $this->columns);
        foreach($this->data as $i => $rec) {
            $column = $this->addColumn($columnLength)->setXs(6);
            $a = $column->add(new Tag('div', null, 'osy-addressbook-item'));
            $p0 = $a->add(new Tag('div', null, 'p0'));
            $p1 = $a->add(new Tag('div', null, 'p1'));
            $p2 = $a->add(new Tag('div', null, 'p2'));
            $p2->add('&nbsp;');
            foreach($rec as $field => $value) {
				$this->cellFactory($field, $value, $a, $p0, $p1, $p2);
            }
            if (($i+1) % $this->columns === 0) {
                $this->addRow();
            }
        }
    }

    protected function cellFactory($k, $v, $a, $p0, $p1, $p2)
    {
        if ($k[0] === '_') {
            return;
        }
        switch($k) {
            case 'checkbox':
                $checked = '';
                if (!empty($this->itemSelected[$v])) {
                    $a->att('class','osy-addressbook-item-selected',true);
                    $checked=' checked="checked"';
                }
                $a->add('<span class="fa fa-check"></span>');
                $a->add('<input type="checkbox" name="'.$this->id.'_chk['.$v.']" value="'.$v.'"'.$checked.' class="osy-addressbook-checkbox">');
                break;
            case 'href':
                $a->add(new Tag('a', null, 'osy-addressbook-link save-history fa fa-pencil'))
                  ->att('href',$v);
                break;
            case 'hrefModal':
                $a->add(new Tag('a', null, 'osy-addressbook-link fa fa-pencil fa-pencil-alt open-modal'))
                  ->att(['href' => $v, 'modal-width' => '640px', 'modal-height' => '480px']);
                break;
            case 'class':
                $a->att('class',$v,true);
                break;
            case 'img':
                if (!empty($v)) {
                    $v = '<img src="'.$v.'" class="osy-addressbook-img">';
                } else {
                    $v = '<span class="fa fa-user fa-2x osy-addressbook-img text-center" style="padding-top: 3px"></span>';
                }
                $p0->add($v);
                break;
            case 'tag':
                $p2->add('<span>'.$v.'</span><br>');
                break;
            case 'title':
                $v = '<strong>'.$v.'</strong>';
            default:
                $p1->add('<div class="p1-row">'.$v.'</div>');
                break;
        }
    }

    /**
     * Build Datagrid pagination
     *
     * @return Tag
     */
    private function buildPagination($pagination)
    {
        if ($this->showPaginationPageDimension) {
            $this->addToFoot('<div class="p-2">Elementi per pagina</div>');
            $this->addToFoot('<div class="px-2 py-1">'.$pagination->getPageDimensionsCombo()->addClass('form-control-sm').'</div>');
        }
        if ($this->showPaginationPageInfo) {
            $this->addToFoot(new Tag('div', null, 'p-2'))->add($pagination->getInfo());
        }
        $this->addToFoot(new Tag('div', null, 'pt-1 pl-2'))->add($pagination)->setPosition('end');
    }

    public function addToFoot($content)
    {
        if (!$this->foot) {
            $this->foot = new Tag('div', null, 'd-flex justify-content-end mt1');
        }
        $this->foot->add($content);
        return $content;
    }

    public function setPaginator($paginator, $showPageDimension = true, $showPageInfo = true)
    {
        $this->paginator = $paginator;
        $this->paginator->setParentComponent($this->id);
        $this->showPaginationPageDimension = $showPageDimension;
        $this->showPaginationPageInfo = $showPageInfo;
        return $this->paginator;
    }
}
