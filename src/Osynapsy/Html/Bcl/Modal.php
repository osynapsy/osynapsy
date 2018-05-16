<?php
namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Component;
use Osynapsy\Html\Tag;
use Osynapsy\Html\PanelNew;

class Modal extends Component
{
    public $content;
    public $header;
    public $title;
    public $body;
    public $panel;
    public $footer;    
    
    public function __construct($id, $title='', $type='')
    {
        parent::__construct('div',$id);
        
        $this->att('class','modal fade')->att('tabindex','-1')->att('role','dialog');
        
        $this->content = $this->add(new Tag('div'))->att('class',trim('modal-dialog '.$type))
                              ->add(new Tag('div'))->att('class','modal-content');
        $this->header = $this->content->add(new Tag('div'))->att('class','modal-header');
        $this->header->add('<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>');
        
        $this->title = $this->header->add(new Tag('h4'))->att('class','modal-title');
        $this->title->add($title);
        
        $this->body = $this->content->add(new Tag('div'))->att('class','modal-body');
        
        $this->footer = $this->content->add(new Tag('div'))->att('class','modal-footer');
    }
    
    public function addFooter($content)
    {
        $this->footer->add($content);
        return $content;
    }
    
    public function addBody($content)
    {
        $this->body->add($content);
        return $content;
    }
    
    public function getPanel()
    {
        if (empty($this->panel)){
            $this->panel = $this->addBody(new Panel($this->id.'Panel'));
            $this->panel->resetClass();
        }
        return $this->panel;
    }
}
