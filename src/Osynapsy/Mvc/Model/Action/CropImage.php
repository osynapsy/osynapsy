<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Mvc\Model\Action;

use Osynapsy\Mvc\Action\Base;
use Osynapsy\Helper\ImageProcessing\Image;

/**
 * Description of ImageDelete
 *
 * @author Pietro Celeste
 */
class CropImage extends Base
{
    protected $documentRoot;
    protected $fieldModel;
    protected $fileName;
    protected $imageHandler;

    public function execute()
    {
        try {
            $this->loadDocumentRoot();
            $this->loadModelField();
            $this->loadFileName();
            $this->loadImage();
            $this->cropImage();
            $this->resizeImage();
            $this->saveImageOnDisk();
            $this->refreshPage();
        } catch (\Exception $e) {
            $this->getResponse()->alertJs($e->getMessage());
        }
    }

    protected function loadDocumentRoot()
    {
        $this->documentRoot = filter_input(\INPUT_SERVER, 'DOCUMENT_ROOT');
        if (substr($this->documentRoot, -1) !== '/') {
            $this->documentRoot .= '/';
        }
    }

    protected function loadFileName()
    {
        $fieldDbName = $this->fieldModel->name;
        $this->fileName = $this->documentRoot.$this->getModel()->getRecord()->get($fieldDbName);
    }

    protected function loadModelField()
    {
        $this->getModel()->find();
        $this->fieldModel = $this->getModel()->getField($this->getParameter(4));
    }

    protected function loadImage()
    {
        $this->imageHandler = new Image($this->fileName);
    }

    protected function cropImage()
    {
        $cropWidth = $this->getParameter(0);
        $cropHeight = $this->getParameter(1);
        $cropX = $this->getParameter(2);
        $cropY = $this->getParameter(3);
        $this->imageHandler->crop($cropX, $cropY, $cropWidth, $cropHeight);
    }

    protected function resizeImage()
    {
        if (empty($this->getParameter(5)) || empty($this->getParameter(6))) {
            return;
        }
        $resizeWidth = $this->getParameter(5);
        $resizeHeight = $this->getParameter(6);
        $this->imageHandler->resizeAdaptive($resizeWidth, $resizeHeight);
    }

    protected function refreshPage()
    {
        $this->getResponse()->clearCache();
        $this->getResponse()->go('refresh');
    }

    protected function saveFileNameOnDb($fileName)
    {
        $fieldDbName = $this->fieldModel->name;
        $this->getModel()->getRecord()->save([$fieldDbName => $fileName]);
    }

    protected function saveImageOnDisk()
    {
        $this->imageHandler->save($this->fileName);
    }
}
