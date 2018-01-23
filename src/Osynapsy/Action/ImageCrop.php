<?php
namespace Osynapsy\ImageProcessing;

use Osynapsy\ImageProcessing\Image;

/**
 * Description of CropTrait
 *
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class ImageCrop
{
    private $db;
    private $table;
    private $field;
    private $where;
    private $targetFile;
    
    public function __construct($db, $table, $field, array $where)
    {
        $this->db = $db;
        $this->table = $table;
        $this->field = $field;
        $this->where = $where;
        $this->targetFile = $this->db->selectOne($table, $where, [$field], 'NUM');
    }

    public function cropAction($newWidth, $newHeight, $cropX, $cropY, $cropWidth, $cropHeight, $filename)
    {       
        $img = new Image('.'.$this->targetFile);
        $img->resize($newWidth, $newHeight);
        $img->crop($cropX, $cropY, $cropWidth, $cropHeight);
        $img->save('.'.$filename);                
        $this->updateRecord($filename);        
    }
    
    public function deleteImageAction()
    {        
        unlink('.'.$this->targetFile);
        $this->updateRecord(null);
    }
    
    public function updateRecord($filename)
    {        
        $this->db->update(
            $this->table,
            [$this->field => $filename],
            $this->where
        );        
    }
    
    public function getTarget()
    {
        return $this->targetFile;
    }
}
