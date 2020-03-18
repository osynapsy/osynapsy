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

use Osynapsy\Helper\ImageProcessing\Image;
use Osynapsy\Html\Component;
use Osynapsy\Html\Tag;
use Osynapsy\Html\Bcl\Button;
use Osynapsy\Html\Ocl\HiddenBox;

class ImageBox extends Component
{
    const ACTION_CROP_IMAGE = 'cropImage';
    const ACTION_DELETE_IMAGE = 'deleteFile';
    
    private $image = [
        'object' => null,
        'webPath' => null,
        'diskPath' => null,
        'dimension' => null,
        'width' => null,
        'height' => null,
        'maxwidth' => 0,
        'maxheight' => 0,
        'domain' => ''
    ];
    private $cropActive = false;
    private $debug = false;
    private $dummy;    
    private $fileBox;    
    private $toolbar;
    
    public function __construct($id)
    {
        $this->requireCss('Lib/rcrop/style.css');
        $this->requireJs('Lib/rcrop/script.js');
        $this->requireCss('Bcl/ImageBox/style.css');        
        $this->requireJs('Bcl/ImageBox/script.js');
        parent::__construct('div', $id.'_box');
        $this->att('class','osy-imagebox-bcl')->att('data-action','upload');
        $this->att('data-preserve-aspect-ratio', 0);
        $this->add(new HiddenBox($id));
        $this->dummy = $this->add(new Tag('label', null, 'osy-imagebox-dummy'))->att('for', $id);
        $this->fileBox = $this->add(new Tag('input', $id, 'hidden'));
        $this->fileBox->att('type','file')->att('style','display: none;')->name = $id;        
        $this->toolbar = new Tag('div', null, 'osy-imagebox-bcl-cmd');        
    }

    protected function __build_extra__()
    {
        $this->getImage();
        $this->checkCrop();
        $this->imageFactory();
        $this->toolbar->add($this->buttonDeleteImageFactory());   
        if (empty($this->image['object'])) {
            $this->dummyEmptyFactory();
            return;
        }        
        $this->add($this->toolbar);        
    }
    
    protected function buttonDeleteImageFactory()
    {
        $button = new Button($this->id.'_delete_image', 'button', 'btn-danger pull-right osy-imagebox-bcl-image-delete', '<i class="fa fa-trash"></i>');
        $button->setAction(self::ACTION_DELETE_IMAGE, $this->image['webPath'].','.$this->fileBox->id, 'click-execute', 'Sei sicuro di voler eliminare l\'immagine?');
        return $button;
    }

    protected function iconCameraFactory()
    {
        return new Tag('span', null, 'fa fa-camera glyphicon glyphicon-camera');
    }

    protected function dummyEmptyFactory()
    {
        $this->dummy->add($this->iconCameraFactory());
        if ($this->image['maxwidth']) {
            $this->dummy->att('style', sprintf('width : %spx; height : %spx;', $this->image['maxwidth'], $this->image['maxheight']));
        }
    }
    
    private function getImage()
    {
        if (empty($_REQUEST[$this->dummy->for])) {
            return;
        }
        $this->image['webPath'] = $_REQUEST[$this->dummy->for];
        $this->image['diskPath'] = $_SERVER['DOCUMENT_ROOT'].$this->image['webPath'];
        if (file_exists($this->image['diskPath'])) {
            $this->image['dimension'] = getimagesize($this->image['diskPath']);
        }
        if (empty($this->image['dimension'])) {
            return;
        }
        $this->image['width'] = $this->image['dimension'][0];
        $this->image['height'] = $this->image['dimension'][1];
        $this->image['formFactor'] = $this->image['width'] / $this->image['height'];
    }
    
    private function imageFactory()
    {
        if (!file_exists($this->image['diskPath'])) { 
            return;
        }
        if ($this->cropActive) {
            $this->image['object'] = $this->add(new Tag('img', null, 'imagebox-main'))->att([
                'src' => $this->image['domain'].$this->image['webPath'],
                'data-action' => self::ACTION_CROP_IMAGE
            ]);                                          
        } else {
            $this->image['object'] = $this->dummy->add(new Tag('img'))->att('src', $this->image['domain'].$this->image['webPath']);
        }
        $width = $this->image['width'];
        $height = $this->image['height'];
        if ($this->image['height'] > $this->image['maxheight']) {
            $height = $this->image['maxheight'];
            $width  = ceil($this->image['width'] * ($this->image['maxheight'] / $this->image['height']));
        } elseif ($this->image['width'] > $this->image['maxwidth']) {
            $width  = $this->image['width'];
            $height = ceil($this->image['height'] * ($this->image['maxwidth'] / $this->image['width']));
        }
        $this->image['object'];
    }
    
    private function checkCrop()
    {    
        if (empty($this->image['maxwidth'])){
            return;
        }
        if ($this->image['width'] <= $this->image['maxwidth'] && $this->image['height'] <= $this->image['maxheight']) {                        
            return;
        }
        $this->cropActive = true;
        $this->att('data-max-width', $this->image['maxwidth']);
        $this->att('data-max-height', $this->image['maxheight']);
        $this->att('data-img-width', $this->image['width']);
        $this->att('data-img-height', $this->image['height']);
        $this->att('data-zoom','1');
        $this->setClass('crop');
        $this->toolbar->add('<button type="button" class="crop-command btn btn-info btn-sm"><span class="fa fa-crop"></span></button> ');
        $this->toolbar->add('<button type="button" class="zoomin-command btn btn-info btn-sm"><span class="fa fa-search-plus"></span></button> ');
        $this->toolbar->add('<button type="button" class="zoomout-command btn btn-info btn-sm"><span class="fa fa-search-minus"></span></button> ');
        if ($this->debug) {
            $this->setClass('debug');
            $this->toolbar->add('<input type="text" name="'.$this->id.'_debug" class="debug" value="">');
        }
    }        
        
    public function setDomain($domain)
    {
        $this->image['domain'] = $domain;
    }
    
    public function setMaxDimension($width, $height)
    {
        $this->image['maxwidth'] = $width;
        $this->image['maxheight'] = $height;
        $this->image['formFactorIdeal'] = $width / $height;
        return $this;
    }        
            
    public function activeDebug()
    {
        $this->debug = true;
    }
    
    public function setPreserveAspectRatio($value)
    {
        $this->att('data-preserve-aspect-ratio', empty($value) ? 0 : 1);
    }
}
