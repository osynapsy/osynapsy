<?php
namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Component;
use Osynapsy\Html\Tag;

/**
 * Description of ScrollSpy
 *
 * @author Pietro
 */
class ScrollSpy extends Component
{
    private $pages = [];
    private $currentPage = null;
    private $paragraphFormatFunction;
    protected $listIndex;
    protected $panelListIndex;

    public function __construct($id, $height = '100vh', $tag = 'div')
    {
        parent::__construct($tag, $id);
        $this->setClass('scrollspy position-relative bg-light d-block border p-lg-5 p-3');
        if (!empty($height)) {
            $this->style = 'overflow-y: scroll;height: '.$height;
        }
        $this->setFormatParagraphFunction(function($rec) {
            return implode('', $rec);
        });
        $this->initIndex();
        $this->setSpySourceId($this->id, 50);
    }

    protected function initIndex()
    {
        $this->panelListIndex = new Tag('div', null, 'panel-list-group');
        $this->listIndex = $this->panelListIndex->add(new Tag('div', $this->id.'Index', 'list-group'));
    }

    public function addPage($title = null, $pid = null, $command = null)
    {
        $pageId = $this->id . ($pid ?? count($this->pages));
        $this->currentPage = $this->add(new Grid($pageId));
        $this->currentPage->setCellClass('m-1');
        $this->currentPage->setClass('bg-white border rounded mb-5 p-2');
        $this->currentPage->setFormatValue($this->paragraphFormatFunction);
        $this->pages[$pageId] = $this->currentPage;
        $this->listIndex->add(new Tag('a', $this->id.'IndexItem'.$pid, 'list-group-item list-group-item-action'))
                        ->att('href', '#'.$pageId)
                        ->add(strip_tags($title) ?? 'Unamed');
        if (empty($title)) {
            return;
        }
        $cell = $this->currentPage->addCell([$title], $pageId.'Cell');
        if (!empty($command)) {
            $this->currentPage->addCellCommand($cell, $command);
        }
    }

    public function addParagraph($title, $body, $id = null, $command = null)
    {
        if (empty($this->currentPage)) {
            $this->addPage(null,null);
        }
        $cell = $this->currentPage->addCell([$title, $body], $id ?? uniqid());
        if (!empty($command)) {
            $this->currentPage->addCellCommand($cell, $command);
        }
        return $cell;
    }

    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    public function getIndex()
    {
        return $this->panelListIndex;
    }

    public function setFormatParagraphFunction(callable $function)
    {
        $this->paragraphFormatFunction = $function;
    }

    public function setTopLeftIndex(int $top, int $left, int $width = 0, $panelClass = '')
    {
        $this->setTopPosition($top, $width, $left, -1, $panelClass);
    }

    public function setTopRightIndex(int $top, int $right, int $width = 0, $panelClass = '')
    {
        $this->setTopPosition($top, $width, -1, $right, $panelClass);
    }

    protected function setTopPosition(int $top, int $left, int $right, int $width = 0, $panelClass = '')
    {
        $this->panelListIndex->addClass(sprintf('fixed-top %s', $panelClass));
        $this->panelListIndex->att('style', sprintf('top: %spx; ', $top));
        if ($left > -1) {
            $this->panelListIndex->att('style', sprintf(' left: %spx;', $left), true);
        }
        if ($right > -1) {
            $this->panelListIndex->att('style', sprintf(' right: %spx;', $right), true);
        }
        if (!empty($width)) {
            $this->panelListIndex->att('style', sprintf(' width: %spx;', $width), true);
        }
    }

    public function setSpySourceId($jquerySourceId, $offset = 50)
    {
        $jqueryDestinationId = sprintf('#%sIndex', $this->id);
        $this->setJavascript(
            sprintf(
                "$(document).ready(function() { $('%s').scrollspy({target: '%s', offset: %s}); });",
                $jquerySourceId,
                $jqueryDestinationId,
                $offset
            )
        );
    }
}
