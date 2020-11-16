<?php
namespace Osynapsy\Mvc\Model\Action;

use Osynapsy\Mvc\Action\Base;

/**
 * Description of ImageDelete
 *
 * @author Pietro Celeste
 */
class DeleteFile extends Base
{
    private $documentRoot;
    private $fieldModel;
    private $fieldDbName;
    private $fileWebPath;

    public function execute()
    {
        try {
            $this->loadDocumentRootPath();
            $this->loadFieldModel();
            $this->loadFileWebPath();
            $this->deleteFileFromDisk();
            $this->deleteFileReferenceFromDb();
            $this->refreshPage();
        } catch (\Exception $e) {
            $this->getResponse()->alertJs($e->getMessage());
        }
    }

    protected function loadDocumentRootPath()
    {
        $this->documentRoot = filter_input(\INPUT_SERVER, 'DOCUMENT_ROOT');
        if (substr($this->documentRoot, -1) !== '/') {
            $this->documentRoot .= '/';
        }
    }

    private function loadFieldModel()
    {
         $this->fieldModel = $this->getModel()->getField($this->getParameter(1));
         $this->fieldDbName = $this->fieldModel->name;
    }

    protected function loadFileWebPath()
    {
        $this->fileWebPath = $this->getModel()->getRecord()->get($this->fieldDbName);
    }

    public function deleteFileFromDisk()
    {
        $filePath = $this->documentRoot.$this->fileWebPath;
        if (!is_file($filePath)) {
            throw new \Exception(sprintf('Il file %s non esiste. Impossibile eliminarlo', $filePath));
        }
        @unlink($filePath);
    }

    public function deleteFileReferenceFromDb()
    {
        $this->getModel()->getRecord()->save([$this->fieldDbName => null]);
    }

    public function refreshPage()
    {
        $this->getResponse()->go('refresh');
    }
}
