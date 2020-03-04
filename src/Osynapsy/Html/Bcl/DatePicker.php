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

class DatePicker extends Component
{
    private $text;
    private $datePickerId;
    private $dateComponent;
    private $format = 'DD/MM/YYYY';
    
    public function __construct($id)
    {
        $this->datePickerId = $id;        
        $this->pushRequirement();
        
        parent::__construct('div',$id.'_datepicker');
        $this->att('class','input-group');
        $this->dateComponent = $this->add(new TextBox($id))->att('class','date date-picker form-control');
        $this->add('<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>');
    }
    
    public static function pushRequirement()
    {
        self::requireJs('Lib/momentjs-2.17.1/moment.js');
        self::requireJs('Lib/bootstrap-datetimejs-4.17.37/bootstrap-datetimejs.js');
        self::requireJs('Bcl/DatePicker/script.js');
        self::requireCss('Lib/bootstrap-datetimejs-4.17.37/bootstrap-datetimejs.css');
    }
    
    protected function __build_extra__()
    {
        $this->dateComponent->att('data-format', $this->format);
        if (!empty($_REQUEST[$this->datePickerId])) {            
            $data = explode('-', $_REQUEST[$this->datePickerId]);
            if (count($data) >= 3 && strlen($data[0]) == 4) {
                $_REQUEST[$this->datePickerId] = $data[2].'/'.$data[1].'/'.$data[0];
            }
        }
    }
    
    public function setAction($action, $parameters = null, $confirmMessage = null, $class = 'change-execute')
    {
        $this->dateComponent->setAction($action, $parameters, $class, $confirmMessage);
        $this->dateComponent->setClass('datepicker-change');        
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
        $this->dateComponent->att('data-max', $date);
    }
    /**
     * 
     * @param type $date accepted mixed input (ISO DATE : YYYY-MM-DD or name of other component date #name)
     */
    public function setDateMin($date)
    {
        $this->dateComponent->att('data-min', $date);
    }
    
    public function setFormat($format)
    {
        $this->format = $format;
    }
    
    public function setDefaultDate($date = null)
    {
        if (!empty($_REQUEST[$this->datePickerId])) {
            return;
        }
        $_REQUEST[$this->datePickerId] = empty($date) ? date('d/m/Y') : $date;        
    }
    
    public function setDisabled($condition)
    {
        $this->dateComponent->setDisabled($condition);
    }
    
    public function onChange($code)
    {
        $this->dateComponent->setClass('datepicker-change')->att('onchange', $code);
    }
    
    public function getTextBox()
    {
        return $this->dateComponent;
    }
}
