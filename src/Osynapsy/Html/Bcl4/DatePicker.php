<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Bcl4;

use Osynapsy\Html\Component;
use Osynapsy\Html\Bcl\TextBox;

class DatePicker extends Component
{
    const FORMAT_DATE_IT = 'DD/MM/YYYY';
    const FORMAT_DATETIME_IT = 'DD/MM/YYYY HH:mm';

    private $datePickerId;
    private $dateComponent;
    protected $format = 'DD/MM/YYYY';

    public function __construct($id)
    {
        $this->datePickerId = $id;
        $this->pushRequirement($this);
        parent::__construct('div', $id.'_datepicker');
        $this->att(['class' => 'input-group date date-picker' , 'data-target-input'=> 'nearest']);
        $this->fieldDateBoxFactory();
        $this->fieldInputGruopAppendFactory();
    }

    protected function fieldInputGruopAppendFactory()
    {
        $this->add('<div class="input-group-append" data-target="#'.$this->id.'" data-toggle="datetimepicker"><div class="input-group-text"><i class="fa fa-calendar"></i></div></div>');
    }

    protected function fieldDateBoxFactory()
    {
        $this->dateComponent = $this->add(new TextBox($this->datePickerId));

        $this->dateComponent->att([
            'class' => 'form-control datetimepicker-input text-center',
            'data-toggle' => 'datetimepicker',
            'data-target' => sprintf('#%s',$this->id)
        ]);
    }

    public static function pushRequirement($object)
    {
        self::requireFile($object, 'assets/Lib/tempusdominus-5.38.0/style.css', 'css');
        self::requireFile($object, 'assets/Lib/momentjs-2.17.1/moment.js', 'js');
        self::requireFile($object, 'assets/Lib/tempusdominus-5.38.0/script.js', 'js');
        self::requireFile($object, 'assets/Bcl4/DatePicker/script.js', 'js');
    }

    protected function __build_extra__()
    {
        $this->att('data-date-format', $this->format);
        if (!empty($_REQUEST[$this->datePickerId])) {
            $dateArray = explode(' ', $_REQUEST[$this->datePickerId]);
            $data = explode('-', $dateArray[0]);
            if (count($data) >= 3 && strlen($data[0]) == 4) {
                $_REQUEST[$this->datePickerId] = $data[2].'/'.$data[1].'/'.$data[0].(empty($dateArray[1]) ? '' : " {$dateArray[1]}");
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

    public function onChange($code)
    {
        $this->att('onchangedate', $code);
    }

    public function setAction($action, $parameters = null, $confirmMessage = null, $class = 'change-execute')
    {
        parent::setAction($action, $parameters, $class, $confirmMessage);
    }

    public function setDisabled($condition)
    {
        $this->dateComponent->setDisabled($condition);
    }

    public function setReadOnly($condition)
    {
        $this->dateComponent->setReadOnly($condition);
    }
}
