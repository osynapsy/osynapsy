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

class FileBox extends Component
{
    protected $fileBox;
    public $showImage = false;
    public $span;    
    
    public function __construct($name, $postfix=false, $prefix=true)
    {
         /* 
            http://www.abeautifulsite.net/whipping-file-inputs-into-shape-with-bootstrap-3/
            <span class="input-group-btn">
                <span class="btn btn-primary btn-file">
                    Browse&hellip; <input type="file" multiple>
                </span>
            </span>
        */
        $this->requireJs('Bcl/FileBox/script.js');
        
        parent::__construct('dummy',$name);
        $this->span = $this->add(new Tag('span'));
        $div = $this->add(new Tag('div'));
        $div->att('class','input-group')
                    ->add(new Tag('span'))
                    ->att('class', 'input-group-btn')
                    ->add(new Tag('span'))
                    ->att('class','btn btn-primary btn-file')
                    ->add('<input type="file" name="'.$name.'"><span class="fa fa-folder-open"></span>');
        $div->add('<input type="text" class="form-control" readonly>');
        if (!$postfix) {
            return;
        }
        $div->add(new Tag('span'))
             ->att('class', 'input-group-btn')
             ->add(new Tag('button'))
             ->att('class','btn btn-primary')
             ->att('type','submit')
             ->add('Send');        
    }
    
    protected function __build_extra__()
    {
        if (empty($_REQUEST[$this->id])) {
            return;
        }
        if ($this->showImage) {
            $this->span->add(new Tag('img'))->att('src',$_REQUEST[$this->id]);
            return;
        } 
        $pathinfo = pathinfo($_REQUEST[$this->id]);            
        $filename = $pathinfo['filename'].(!empty($pathinfo['extension']) ? '.'.$pathinfo['extension'] : '');
        $download = new Tag('a');
        $download->att('target','_blank')->att('href',$_REQUEST[$this->id])->add($filename.' <span class="fa fa-download"></span>');
        $label = $this->span->add(new LabelBox('donwload_'.$this->id));
        $label->att('style','padding: 10px; background-color: #ddd; margin-bottom: 10px;');
        $label->setLabel($download);
        $this->span->add($label);        
    }   
}