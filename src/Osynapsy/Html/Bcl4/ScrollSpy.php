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
    private $listIndex;    
    private $spyElementId;
    private $spyOffset;
    
    public function __construct($id, $height = '100vh', $tag = 'div')
    {
        parent::__construct($tag, $id);
        $this->setClass('scrollspy position-relative bg-light d-block border p-5');        
        if (!empty($height)) {
            $this->style = 'overflow-y: scroll;height: '.$height;
        }
        $this->setFormatParagraphFunction(function($rec) {
            return implode('', $rec);
        });
        $this->listIndex = new Tag('div', $this->id.'Index', 'list-group');
        $this->setSpySourceId($this->id, 50);
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
        return $this->listIndex;
    }
    
    public function setFormatParagraphFunction(callable $function)
    {
        $this->paragraphFormatFunction = $function;
    }
    
    public function setTopLeftIndex(int $top, int $left, int $width = 200)
    {
        $this->listIndex->att('class', ' fixed-top', true);
        $this->listIndex->att('style', sprintf('top: %spx; left: %spx; width: %spx;', $top, $left, $width));
    }
    
    public function setTopRightIndex(int $top, int $right, int $width = 200)
    {
        $this->listIndex->att('class', ' fixed-top', true);
        $this->listIndex->att('style', sprintf('top: %spx; right: %spx; width: %spx;', $top, $right, $width));
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
