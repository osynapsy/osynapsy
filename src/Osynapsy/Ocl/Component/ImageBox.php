<?php
namespace Osynapsy\Ocl\Component;

use Osynapsy\Core\Kernel as Kernel;
use Osynapsy\Html\Tag as Tag;

class ImageBox extends Component
{
    public function __construct($id)
    {
        $this->requireCss('/vendor/osynapsy/Bcl/ImageBox/style.css');
        $this->requireCss('/vendor/osynapsy/Bcl/ImageBox/jquery.Jcrop.min.css');
        $this->requireJs('/vendor/osynapsy/Bcl/ImageBox/script.js');
        parent::__construct('div',$id);
        $this->att('class','osy-imagebox')->att('data-action','save');
        $this->add(new HiddenBox($id));
        $file = $this->add(new Tag('input'));
        $file->att('type','file')
             ->att('class','hidden')
             ->att('id',$id.'_file')
             ->name = $id;
    }

    protected function __build_extra__()
    {
        if ($_REQUEST['ajax'] == $this->id) { 
            $this->execCommand($_REQUEST['ajax-cmd']);
            exit;
        }
        $img = '';
        if (!empty($_REQUEST[$this->id])) {
            if ($inblob = $this->get_par('store-in-blob')) {
                $img = '<img src="data:image/png;base64,'.base64_encode($_REQUEST[$this->id]).'">';
            } else {
                if (file_exists($_SERVER['DOCUMENT_ROOT'].$_REQUEST[$this->id])) { 
                    $filename = $_SERVER['DOCUMENT_ROOT'].$_REQUEST[$this->id];
                    $img = '<img src="'.$_REQUEST[$this->id].'">'; 
                }
            }
            if (!empty($img) && $dim_max = $this->get_par('crop-dimension')) {
                $dim_img = getimagesize($filename);
                $dim_max = explode(',',$dim_max);
                
                if ($dim_max[0] < $dim_img[0] &&  $dim_max[1] < $dim_img[1]) {
                    $this->att('class','image-crop',true);
                    $this->add('<input  type="hidden" id="'.$this->id.'_crop" name="'.$this->id.'_crop" class="osy-imagebox-crop">');
                    $prw = $this->add(tag::create('div'))
                                ->att('class','osy-imagebox-previewbox');
                    $prw->add('<div style="width: '.$dim_max[0].'px; height: '.$dim_max[1].'px; overflow: hidden;"><img src="'.$_REQUEST[$this->id].'" class="osy-imagebox-preview"></div>');
                    $prw->add('<span id="'.$this->id.'_get_crop" class="osy-imagebox-cmd-crop btn_cnf w100 center"><span class="fa fa-cut"></span> Taglia</span>');
                    $this->add('<img src="'.$_REQUEST[$this->id].'" class="osy-imagebox-master">');
                    return;
                }
                
                $this->add('<div><img src="'.$_REQUEST[$this->id].'" class="osy-imagebox-master" title="'.$_REQUEST[$this->id].'"></div>',true);
            }
        }
        if ($dim = $this->get_par('max-dimension')) {
                $dim = explode(',',$dim);
                $sty = ' style="width:'.$dim[0].'px; height: '.$dim[1].'px;"';
        }
        $this->add('<label class="osy-imagebox-dummy"'.$sty.' for="'.$this->id.'_file">'.(empty($img) ? '<span class="fa fa-camera glyphicon glyphicon-camera" ></span>' : $img).'</label>');
        if (!empty($img)) {
            $this->add(tag::create('div'))
                 ->att('class','osy-imagebox-cmd center')
                 ->add(tag::create('a'))
                 ->att('href','javascript:void(0);')
                 ->att('onclick',"oimagebox.delete('".$this->id."')")
                 ->att('data-cmd','delete')
                 ->add('Elimina <span class="fa fa-trash"></span>');
        }
        //$this->add(tag::create('label'))->att('class','btn_add center')->att('for',$this->id.'_file')->add('Upload');
    }

    private function execCommand($cmd)
    {
        if (empty($cmd)) {
            die('Command is empty');
        }
        switch ($cmd) {
            case 'crop':    
                list($x, $y, $w, $h) = explode(',', $_REQUEST[$this->id.'_coords']);
                $rsp = $this->imageCrop($_REQUEST[$this->id], $x, $y, $w, $h);
                die($rsp);
                break;
            case 'delete':
                $table = Kernel::get('model.table');
                $field = Kernel::get('model.fields.'.$this->id)->name;
                $where = Kernel::get('model.record.where'); 
                if (!empty($where) || !is_array($where)){
                    die('Delete impossible pk is no.');
                }
                $rsp = 'OK';
                try{
                    Kernel::$dba->update($table,array($field=>null),$where);
                } catch (Exception $e) {
                    $rsp = $e->getMessage();
                }
                die($rsp);
                break;
            default: 
                die('Command '.$cmd.' is unknown');
                break;
        }
    }
    
    protected function imageCrop($file_name,$x,$y,$w,$h)
    {
        $full_path = $_SERVER['DOCUMENT_ROOT'] . $file_name;
        try {
            require_once(SITE_LIB.'phpthu/ThumbLib.inc.php');
            $image = PhpThumbFactory::create($full_path);
            $image->crop($x, $y, $w, $h);
            $image->save($full_path);
            return 'OK';
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    
    public function setAction($action)
    {
        $this->att('data-action', $action);
    }
}
