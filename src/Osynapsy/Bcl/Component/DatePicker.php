<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\Component;

class DatePicker extends Component
{
    private $text;
    private $datePickerId;
    private $dateContainer;
    
    public function __construct($id)
    {
        $this->datePickerId = $id;        
        $this->requireJs('/__assets/osynapsy/Lib/momentjs-2.17.1/moment.js');
        $this->requireJs('/__assets/osynapsy/Lib/bootstrap-datetimejs-4.17.37/bootstrap-datetimejs.js');
        $this->requireJs('/__assets/osynapsy/Bcl/DatePicker/script.js');
        $this->requireCss('/__assets/osynapsy/Lib/bootstrap-datetimejs-4.17.37/bootstrap-datetimejs.css');
        
        parent::__construct('div',$id.'_datepicker');
        $this->att('class','input-group');
        $this->dateContainer = $this->add(new TextBox($id))->att('class','date date-picker form-control');
        $this->add('<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>');
    }
    
    protected function __build_extra__()
    {
        if (!empty($_REQUEST[$this->datePickerId])) {
            $data = $_REQUEST[$this->datePickerId];
            $data = explode('-',$data);
            if (count($data) >= 3 && strlen($data[0]) == 4) {
                $_REQUEST[$this->datePickerId] = $data[2].'/'.$data[1].'/'.$data[0];
            }
        }
    }
    
    /**
     * 
     * @param type $min accepted mixed input (ISO DATE : YYYY-MM-DD or name of other component date #name)
     * @param type $max accepted mixed input (ISO DATE : YYYY-MM-DD or name of other component date #name)
     */
    public function setDateLimit($min, $max)
    {
        $this->setDateMin($min);
        $this->setDateMax($max);
    }
    
    /**
     * 
     * @param type $date accepted mixed input (ISO DATE : YYYY-MM-DD or name of other component date #name)
     */
    public function setDateMax($date)
    {
        $this->dateContainer->att('data-max', $date);
    }
    /**
     * 
     * @param type $date accepted mixed input (ISO DATE : YYYY-MM-DD or name of other component date #name)
     */
    public function setDateMin($date)
    {
        $this->dateContainer->att('data-min', $date);
    }
}
