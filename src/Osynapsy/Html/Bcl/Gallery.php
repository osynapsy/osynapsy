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
use Osynapsy\Html\Bcl\Modal;
use Osynapsy\Html\Bcl\Button;
use Osynapsy\Html\Bcl\FileBox;

class Gallery extends Component
{
    protected $cellWidth = 4;
    protected $actions = [];
    protected $defaultPhoto;
    protected $contextMenu;
    protected $modalViewerDimension;
    protected $showCommands = false;

    public function __construct($id, $cellWidth = 4)
    {
        parent::__construct('div', $id);
        $this->requireJs('assets/Bcl/FileBox/script.js');
        $this->add(new Tag('span', null, 'gallery'));
        $this->cellWidth = $cellWidth;
    }

    public function __build_extra__()
    {
        $this->add($this->modalUploadFactory());
        $this->add($this->modalViewerFactory());
        $row = $this->add(new Tag('div', null, 'row mb-2'));
        foreach ($this->data as $photo) {
            $row->add($this->cellFactory($photo));
        }
        if ($this->showCommands) {
            $this->add($this->buttonOpenModalFactory());
        }
    }

    protected function cellFactory($photo)
    {
        $div = new Tag('div', null, sprintf('text-center col-%s', $this->cellWidth));
        $div->add($this->thumbnailFactory($photo));
        $div->add($this->labelFactory($photo));
        return $div;
    }

    protected function thumbnailFactory($photo)
    {
        $img = new Tag('img', null, 'img-thumbnail mt-2');
        $img->att(['data-toggle' => 'modal', 'data-target' => sprintf('#%sModalViewer', $this->id)]);
        $img->att('onclick', sprintf("let src = $(this).attr('src'); $('#%sViewer').attr('src', src); $('#%sDeleteImage').attr('data-action-parameters', src);", $this->id, $this->id));
        $img->att('src', $photo['url']);
        return $img;
    }


    protected function labelFactory($photo)
    {
        if (empty($photo['label'])) {
            return '';
        }
        $div = new Tag('div', null, 'gallery-photo-label');
        $div->add(sprintf('<small>%s</small>', $photo['label']));
        return $div;
    }

    protected function cellAddFactory()
    {
        $div = new Tag('div', null, sprintf('text-center col-%s mt-2', $this->cellWidth));
        $div->add(new Tag('div', null, 'img-thumbnail'))->add(new Tag('span', null, 'fa fa-plus'));
        return $div;
    }

    protected function buttonOpenModalFactory()
    {
        $Button = new Button($this->id.'Add', 'button', 'btn btn-primary btn-block', 'Aggiungi foto');
        $Button->att(['data-toggle' => 'modal', 'data-target' => '#'.$this->id.'ModalUpload']);
        return $Button;
    }

    protected function modalUploadFactory()
    {
        $modalId = $this->id.'ModalUpload';
        $Modal = new Modal($modalId, 'Aggiungi foto');
        $Modal->getPanelBody()->addColumn()->push('Seleziona l\'immagine da aggiungere alla gallery', $this->fileUploadFactory());
        $Modal->getPanelFoot()->add($this->buttonCloseModalFactory($modalId));
        $Modal->getPanelFoot()->add($this->buttonSendPhotoToGalleryFactory());
        return $Modal;
    }

    protected function fileUploadFactory()
    {
        return new FileBox($this->id.'File');
    }

    protected function buttonSendPhotoToGalleryFactory()
    {
        $Button = new Button('btnSendPotoTo'.$this->id, 'button', 'btn btn-primary', 'Invia foto');
        $Button->setAction('addPhotoToGallery', $this->id.'File');
        return $Button;
    }

    protected function modalViewerFactory()
    {
        $modalId = $this->id.'ModalViewer';
        $Modal = new Modal($modalId, 'Foto', $this->modalViewerDimension);
        $Modal->getPanelBody()->addColumn()->setClass('text-center')->add(new Tag('img', $this->id.'Viewer', 'img-thumbnail'));
        $Modal->getPanelFoot()->add($this->buttonCloseModalFactory($modalId));
        if ($this->showCommands) {
            $Modal->getPanelFoot()->add($this->buttonDeletePhotoFactory());
        }
        return $Modal;
    }

    protected function buttonDeletePhotoFactory()
    {
        $Button = new Button($this->id.'DeleteImage', 'button', 'btn btn-danger', 'Elimina foto');
        $Button->setAction('deletePhotoFromGallery', null, 'click-execute', 'Sei sicuro di voler eliminare la foto corrente (L\'operazione non è reversibile)? ');
        return $Button;
    }

    protected function buttonCloseModalFactory($modalId)
    {
        $Button = new Button($modalId.'CloseModal', 'button', 'btn btn-danger', 'Chiudi');
        $Button->att('data-dismiss','modal');
        return $Button;
    }

    public function showCommands($value)
    {
        $this->showCommands = $value;
    }
}
