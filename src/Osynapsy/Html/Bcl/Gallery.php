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
use Osynapsy\Html\Bcl\ContextMenu;

class Gallery extends Component
{
    protected $photos;
    protected $dim = array(180,180);
    protected $actions;
    protected $defaultPhoto;
    protected $contextMenu;
    
    public function __construct($id, $dim = array(180,180))
    {
        parent::__construct('div', $id);
        $this->add(new Tag('span'))
             ->att('class','gallery');
        $this->dim = $dim;
    }
    
    public function addAction($label, $action, $actionField='id')
    {
        if (!$this->contextMenu) {
            $this->contextMenu = new ContextMenu($this->id.'ContextMenu');
        }
        $this->contextMenu->addAction($label, $action, $actionField);
        $this->actions[] = array($label, $action, $actionField);
    }
    
    public function setPhotoList($list)
    {
        $this->photos = $list;
    }
    
    public function setDefault($val,$field='id')
    {
        $this->defaultPhoto = array($val,$field);
    }
    
    public function __build_extra__()
    {
        foreach ($this->photos as $key => $photo) {
            $div = $this->add(new Tag('div'))->att('class','col-xs-2 col-md-1');
            $a = $div->add(new Tag('a'))->att('class','thumbnail');
            if ($this->contextMenu) {
                $a->att('class','BclContextMenuOrigin',true)
                  ->att('data-bclcontextmenuid', $this->id.'ContextMenu');
            }
            if ($this->defaultPhoto && $this->defaultPhoto[0] == $photo[$this->defaultPhoto[1]]) {
                $a->att('style','border-color: red;');
            }
            $img = $a->add(new Tag('img'))->att('src',$photo['url']);
            if (!empty($this->dim[0])) {
                $img->att('style','width: '.$this->dim[0].'px');
            }
            if (!empty($this->dim[1])) {
                $img->att('style','height: '.$this->dim[1].'px',';');
            }
            //Create Photo caption
            $caption = $div->add(new Tag('div'))->att('class','caption text-center');
            if ($photo['label']) {
                $caption->add($photo['label']);
            }
            //If delete action is set add button delete
            if (empty($this->actions)) {
                return;
            }
            foreach ($this->actions as $action) {
                $caption->add(new Tag('button'))
                        ->att(array(
                            'class' => 'btn btn-danger cmd-execute',
                            'type' => 'button',
                            'data-action' => $action[1],
                            'data-action-param' => $photo[$action[2]]
                        ))->add($action[0]);
                $a->att('data-action-param' , $photo[$action[2]]);
            }
        }
        
        if ($this->contextMenu) {
            $this->add($this->contextMenu);
        }
    }
}
