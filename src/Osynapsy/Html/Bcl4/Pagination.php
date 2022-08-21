<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Component;
use Osynapsy\Html\Ocl\HiddenBox;
use Osynapsy\Html\Tag;
use Osynapsy\Html\Bcl\Link;
use Osynapsy\Html\Bcl\ComboBox;
use Osynapsy\Db\Paginator;
use Osynapsy\Html\Bcl\IPagination;

/**
 * Description of Pagination
 *
 * @author Pietro Celeste
 */
class Pagination extends Component implements IPagination
{
    private $entity = 'record';
    protected $data = [];
    protected $errors = [];
    protected $pageDimensionPalceholder = '- Dimensione pagina -';
    private $fields = [];
    private $parentComponent;
    private $paginator;
    private $position = 'center';
    private $pageDimensions = [
        1 => ['10', '10'],
        2 => ['20', '20'],
        5 => ['50', '50'],
        10 => ['100', '100'],
        20 => ['200', '200']
    ];
    protected $rawPagination;

    /**
     * Costructor of pager component.
     *
     * @param string $id Identify of component
     * @param DbPagination $paginator DbPagination injection
     * @param type $tag Tag of container
     * @param type $infiniteContainer Enable infinite scroll?
     */
    public function __construct($id, Paginator $paginator, $tag = 'div')
    {
        parent::__construct($tag, $id);
        $this->setClass('BclPagination');
        $this->requireJs('assets/Bcl/Pagination/script.js');
        $this->setPaginator($paginator);
        if ($tag == 'form') {
            $this->att('method','post');
        }
    }

    public function __build_extra__()
    {
        $this->extraFieldsFactory();
        $this->add($this->fieldCurrentPageFactory());
        $this->add($this->fieldOrderByFactory());
        $ul = $this->add(new Tag('ul', null, 'pagination pagination-sm justify-content-'.$this->position));
        $liFirst = $ul->add($this->liPageItemFactory($this->getMeta(Paginator::META_PAGE_CUR) < 2 ? 'disabled' : ''));
        $liFirst->add($this->linkPageItemFactory('first', '&laquo;'));
        for ($i = $this->getMeta(Paginator::META_PAGE_MIN); $i <= $this->getMeta(Paginator::META_PAGE_MAX); $i++) {
            $liCurrent = $ul->add($this->liPageItemFactory($i == $this->getMeta('pageCurrent') ? 'active' : ''));
            $liCurrent->add($this->linkPageItemFactory($i, $i));
        }
        $liLastClass =$this->getMeta(Paginator::META_PAGE_CUR) >= $this->getMeta(Paginator::META_PAGE_TOT) ? 'disabled' : '';
        $liLast = $ul->add($this->liPageItemFactory($liLastClass));
        $liLast->add($this->linkPageItemFactory('last', '&raquo;', 'page-link'));
    }

    protected function extraFieldsFactory()
    {
        foreach($this->fields as $field) {
            $this->add(new HiddenBox($field, $field.'_hidden'));
        }
    }

    protected function fieldCurrentPageFactory()
    {
        $hidden = new HiddenBox($this->id);
        $hidden->setClass('BclPaginationCurrentPage');
        return $hidden;
    }

    protected function fieldOrderByFactory()
    {
        $hidden = new HiddenBox($this->id.'OrderBy');
        $hidden->setClass('BclPaginationOrderBy');
        return $hidden;
    }

    protected function liPageItemFactory($class = '')
    {
        return new Tag('li', null, sprintf('page-item %s', $class));
    }

    protected function linkPageItemFactory($index, $label)
    {
        $link = new Link(sprintf('%sPage%s', $this->id, $index), '#', $label, 'page-link');
        $link->att('data-value', $index);
        return $link;
    }

    public function addField($field)
    {
        $this->fields[] = $field;
    }

    public function getDataPaginator()
    {
        return $this->paginator;
    }

    public function getOrderBy()
    {
        return $this->getDataPaginator()->getSort();
    }

    public function getPageDimensionsCombo()
    {
        $Combo = new ComboBox($this->id.(strpos($this->id, '_') ? '_page_dimension' : 'PageDimension'));
        $Combo->setPlaceholder(false);
        $Combo->att('onchange',"Osynapsy.refreshComponents(['{$this->parentComponent}'])")
              ->setData($this->pageDimensions);
        return $Combo;
    }

    public function loadData($requestPage, $pageOnError = false)
    {
        return $this->getDataPaginator()->get(filter_input(\INPUT_POST, $this->id), true);
    }

    public function getInfo()
    {
        $end = min($this->getMeta('pageCurrent') * $this->getMeta('pageDimension'), $this->getMeta('rowsTotal'));
        $start = ($this->getMeta('pageCurrent') - 1) * $this->getMeta('pageDimension') + 1;
        return sprintf('%s - %s di %s %s', $start, $end, $this->getMeta('rowsTotal'), $this->entity);
    }

    public function getTotal($key)
    {
        return $this->getMeta('total'.ucfirst($key));
    }

    public function getMeta($key)
    {
        return $this->getDataPaginator()->getMeta($key);
    }

    public function getStatistic($key)
    {
        return $this->getMeta($key);
    }

    public function setInfiniteScroll($container)
    {
        $this->requireJs('Lib/imagesLoaded-4.1.1/imagesloaded.js');
        $this->requireJs('Lib/wookmark-2.1.2/wookmark.js');
        $this->att('class','infinitescroll',true)->att('style','display: none');
        if ($container[0] != '#' ||  $container[0] != '#') {
            $container = '#'.$container;
        }
        return $this->att('data-container',$container);
    }

    public function setPageDimension($pageDimension)
    {
        $this->getDbPagination()->setPageDimension($pageDimension);
        foreach(array_keys($pageDimension) as $key) {
            $dimension = $pageDimension * $key;
            $this->pageDimensions[$key] = [$dimension, "{$dimension} righe"];
        }
    }

    public function setPageDimensionPlaceholder($label)
    {
        $this->pageDimensionPalceholder = $label;
    }

    public function setParentComponent($componentId)
    {
        $this->parentComponent = $componentId;
        $this->att('data-parent', $componentId);
        return $this;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getErrors()
    {
        return implode(PHP_EOL, $this->errors);
    }

    protected function setPaginator(Paginator $paginator)
    {
        $postOrderByField = str_replace(['][','[',']'],[',',''], filter_input(\INPUT_POST, $this->id.'OrderBy'));
        $this->paginator = $paginator;
        if (!empty($postOrderByField)) {
            $this->paginator->setSort($postOrderByField);
        }
    }
}
