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
    protected $rawId;
    protected $cropActive = false;
    protected $dummy;
    protected $fileBox;

    public function __construct($id)
    {
        $this->rawId = $id;
        $this->requireCss('Lib/rcrop/style.css');
        $this->requireJs('Lib/rcrop/script.js');
        $this->requireCss('Bcl/ImageBox/style.css');
        $this->requireJs('Bcl/ImageBox/script.js');
        parent::__construct('div', $id.'_box');
        $this->att('class','osy-imagebox-bcl text-center')->att('data-action', 'upload');
        $this->att('data-preserve-aspect-ratio', 0);
        $this->add(new HiddenBox($id));
        $this->fileBoxFactory();
    }

    protected function __build_extra__()
    {
        try {
            $this->loadImagePaths();
            $this->setImageData();
            $this->setCropState();
            if ($this->cropActive) {
                $this->addClass('crop text-center');
                $this->add($this->imageWithCropActiveFactory());
                $this->add($this->toolbarFactory());
            } else {
                $this->add($this->placeholderImageFactory($this->imageFactory()));
                $this->add($this->buttonDeleteImageFactory('osy-imagebox-bcl-image-delete'));
            }
        } catch (\DomainException $e) {
            $this->add($this->placeholderImageFactory($this->iconCameraFactory()));
        }
    }

    protected function toolbarFactory()
    {
        $toolbar = new Tag('div', null, 'osy-imagebox-bcl-cmd text-center');
        $toolbar->add('<button type="button" class="crop-command btn btn-info btn-sm"><span class="fa fa-crop"></span></button> ');
        $toolbar->add('<button type="button" class="zoomin-command btn btn-info btn-sm"><span class="fa fa-search-plus"></span></button> ');
        $toolbar->add('<button type="button" class="zoomout-command btn btn-info btn-sm mr-5"><span class="fa fa-search-minus"></span></button>');
        $toolbar->add($this->buttonUploadImageFactory());
        if ($this->image['diskPath']) {
            $toolbar->add($this->buttonDeleteImageFactory());
        }
        return $toolbar;
    }

    protected function buttonDeleteImageFactory($class = '')
    {
        $button = new Button($this->id.'DeleteImage', 'button', trim('btn-danger btn-sm '.$class), '<i class="fa fa-trash"></i>');
        $button->setAction(self::ACTION_DELETE_IMAGE, $this->image['webPath'].','.$this->rawId, 'click-execute', 'Sei sicuro di voler eliminare l\'immagine?');
        return $button;
    }

    protected function buttonUploadImageFactory()
    {
        $button = new Tag('label', null,'btn btn-warning btn-sm mt-2');
        $button->att('for', $this->rawId)->add('<i class="fa fa-upload"></i>');
        return $button;
    }

    protected function placeholderImageFactory($content)
    {
        $dummy = (new Tag('label', null, 'osy-imagebox-dummy'))->att('for', $this->rawId);
        $dummy->add($content);
        if ($this->image['maxwidth']) {
            $dummy->att('style', sprintf('max-width : %spx; max-height : %spx;', $this->image['maxwidth'], $this->image['maxheight']));
        }
        return $dummy;
    }

    protected function fileBoxFactory()
    {
        $this->fileBox = $this->add(new Tag('input', $this->rawId));
        $this->fileBox->att([
            'type' => 'file',
            'accept' => 'image/*;',
            'capture' => 'camera',
            'name' => $this->rawId,
            'style' => 'display: none;'
        ]);
    }

    protected function iconCameraFactory()
    {
        return new Tag('span', null, 'fa fa-camera glyphicon glyphicon-camera');
    }

    protected function imageWithCropActiveFactory()
    {
        $img = new Tag('img', null, 'imagebox-main');
        $img->att([
            'src' => $this->image['domain'].$this->image['webPath'],
            'data-action' => self::ACTION_CROP_IMAGE
        ]);
        return $img;
    }

    private function imageFactory()
    {
        $img = new Tag('img');
        $img->att('src', $this->image['domain'].$this->image['webPath']);
        return $img;
    }

    protected function loadImagePaths()
    {
        if (empty($_REQUEST[$this->rawId])) {
            throw new \DomainException('Field is empty', 404);
        }
        $this->image['webPath'] = $_REQUEST[$this->rawId];
        $this->image['diskPath'] = filter_input(\INPUT_SERVER , 'DOCUMENT_ROOT') . $this->image['webPath'];
    }

    protected function setImageData()
    {
        if (!file_exists($this->image['diskPath'])) {
            return;
        }
        $this->image['dimension'] = getimagesize($this->image['diskPath']);
        if (empty($this->image['dimension'])) {
            throw new \Exception('File not found', 404);
        }
        $this->image['width'] = $this->image['dimension'][0];
        $this->image['height'] = $this->image['dimension'][1];
        $this->image['formFactor'] = $this->image['width'] / $this->image['height'];
    }

    protected function setCropState()
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

    public function setPreserveAspectRatio($value)
    {
        $this->att('data-preserve-aspect-ratio', empty($value) ? 0 : 1);
    }
}
