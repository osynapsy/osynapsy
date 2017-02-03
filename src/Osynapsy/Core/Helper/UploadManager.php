<?php
namespace Osynapsy\Core\Helper;

class UploadManager
{
    private $response;
    private $documentRoot;    
    
    public function __construct($response)
    {        
        $this->response = $response;
        $this->documentRoot = filter_input(\INPUT_SERVER, 'DOCUMENT_ROOT');
    }

    public function getUniqueFilename($pathOnDisk)
    {
        if (empty($pathOnDisk)) {
            return false;
        }
        //Se il Path non eiste su disco lo restituisco.
        if (!file_exists($pathOnDisk)) {
            return $pathOnDisk;
        } 
        $pathInfo = pathinfo($pathOnDisk);
        $i = 1;
        while (file_exists($pathOnDisk)) {
            $pathOnDisk = $pathInfo['dirname'].'/'.$pathInfo['filename'].'_'.$i.'.'.$pathInfo['extension'];
            $i++;
        }
        return $pathOnDisk;
    }
    
    private function checkUploadDir($uploadRoot)
    { 
        if (empty($uploadRoot)){
            return 'configuration parameters.path-upload is empty';
        }
        if (!is_dir($this->documentRoot.$uploadRoot)) {
            return 'path-upload '.$this->documentRoot.$uploadRoot.' not exists';
        } 
        if (!is_writeable($this->documentRoot.$uploadRoot)) {
            return $this->documentRoot.$uploadRoot.' is not writeable.';
        }        
    }
    
    public function saveFile($componentName, $uploadRoot='/upload/')
    {
        if (!is_array($_FILES) || !array_key_exists($componentName, $_FILES)){ 
            return; 
        }   
        $fileNameFinal = $_FILES[$componentName]['name'];
        $fileNameTemp = $_FILES[$componentName]['tmp_name'];
        if (empty($fileNameFinal)) { 
            $this->response->error('alert','Filename empty');
        }
        if (empty($fileNameTemp)) {
            $this->response->error('alert','Filename Temp empty');
        }
        $alert = $this->checkUploadDir($uploadRoot);
        if (!empty($alert)) {
            $this->response->error('alert','path-upload '.$this->documentRoot.$uploadRoot.' not exists');
        }        
        if ($this->response->error()) { 
            $this->response->dispatch(); 
            return;
        }        
        $pathOnWeb = $uploadRoot.'/'.$fileNameFinal;
        $pathOnDisk = $this->getUniqueFilename($this->documentRoot.$pathOnWeb);
        $pathOnWeb = str_replace($this->documentRoot,'',$pathOnDisk);
        //Thumbnail path            
        if ($pathOnDisk && move_uploaded_file($fileNameTemp, $pathOnDisk)){                        
            //Inserisco sul db l'immagine
            return $pathOnWeb;           
        }
    }
}
