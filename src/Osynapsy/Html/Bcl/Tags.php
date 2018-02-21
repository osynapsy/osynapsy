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
use Osynapsy\Html\Component;
use Osynapsy\Html\Ocl\HiddenBox;

class Tags extends Component
{
    private $labelClass;
    private $modal;
    private $dropdown;
    private $hidden;
    private $autocomplete;
    
    public function __construct($name, $class="label-info")
    {
        parent::__construct('div', $name);
        $this->hidden = $this->add(new HiddenBox($name));        
        $this->requireJs('Bcl/Tags/script.js');
        $this->requireCss('Bcl/Tags/style.css');
        $this->labelClass = $class;
    }
    
    public function __build_extra__()
    {
        $this->att('class','bclTags');
        $cont = $this->add(new Tag('h4'))
                     ->att(['class' => 'bclTags-container', 'style' => 'margin: 0px 0px 5px 0px']);
        if (!empty($_REQUEST[$this->id])) {
            $list = explode('][',$_REQUEST[$this->id]);
            foreach($list as $item) {
                if (!$item) {
                    continue;
                }
                $cont->add('<span class="label label-lg '.$this->labelClass.'" data-parent="#'.$this->id.'">'.str_replace(['[',']'], '', $item).' <span class="fa fa-close bclTags-delete"></span></span>');
            }
        }
        if (!empty($this->autocomplete)) {
            $this->add($this->autocomplete);
        }
        if (!empty($this->modal)) {
            $buttonAdd = $this->add(new Button('btn'.$this->id));
            $buttonAdd->att('class','btn-info btn-xs',true)
                      ->att('data-toggle','modal')
                      ->att('data-target','#modal'.$this->id)
                      ->add('<span class="fa fa-plus"></span>');
        }
        if (!empty($this->dropdown)) {
            $this->add($this->dropdown);
        }
        
    }
    
    public function addModal($title, $body, $buttonAdd)
    {
        $this->modal = $this->add(new Modal('modal'.$this->id, $title));
        $this->modal->addBody($body);
        $buttonCls = new Button('clsModal'.$this->id);
        $buttonCls->att('class','btn-default pull-left',true)
                  ->att('data-dismiss','modal')              
                  ->add('Annulla');
        $this->modal->addFooter($buttonCls);
        if (is_object($buttonAdd)) {
            $buttonAdd->att('class', 'bclTags-add', true)
                      ->att('data-parent', '#'.$this->id);
        }
        $this->modal->addFooter($buttonAdd);
    }
    
    public function addDropDown($label, $data)
    {
        $this->dropdown = new Dropdown($this->id.'_list', $label, 'span');       
        $this->dropdown->setData($data);
    }
    
    public function addAutoComplete(array $data = [])
    {
        $ajax = filter_input(\INPUT_POST, 'ajax');
        if (empty($ajax)) {
            $this->autocomplete = new Autocomplete($this->id.'_auto','div');      
            $this->autocomplete->att([
                'style' =>'width: 250px; margin-top: 3px;',
                'class' => 'pull-left'
            ]);
            $this->autocomplete->setSelected("\$('#{$this->id} span.fa-plus').click()");
            $this->autocomplete->setIco('<span class="fa fa-plus tag-append" onclick="BclTags.addTag(\'#'.$this->id.'\');"></span>');           
            return $this->autocomplete;
        }
        if ($ajax != $this->id.'_auto') {
            return;
        }                  
        $Autocomplete = new Autocomplete($this->id.'_auto');
        $Autocomplete->setData($data);        
        die($Autocomplete);
    }
}
