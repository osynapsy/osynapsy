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
    const BS4_VER = '4';
    const BS3_VER = '3';

    private $datePickerId;
    private $dateComponent;
    private $format = 'DD/MM/YYYY';

    public function __construct($id, $bootstrapVersion = '4')
    {
        $this->datePickerId = $id;
        $this->pushRequirement($bootstrapVersion);
        parent::__construct('div',$id.'_datepicker');
        $this->att('class','input-group');
        $this->dateComponent = $this->add(new TextBox($id))->att('class','date date-picker form-control');
        switch ($bootstrapVersion) {
            case self::BS4_VER:
                $this->add('<div class="input-group-append"><span class="input-group-text glyphicon glyphicon-calendar"></span></div>');
                break;
            default:
                $this->add('<span class="input-group-append"><span class="glyphicon glyphicon-calendar"></span></span>');
                break;
        }
    }

    public static function pushRequirement($bootstrapVersion)
    {
        self::requireJs('Lib/momentjs-2.17.1/moment.js');
        self::requireJs('Lib/bootstrap-datetimejs-4.17.37/bootstrap-datetimejs.js');
        self::requireJs('Bcl/DatePicker/script.js');
        self::requireCss('Lib/bootstrap-datetimejs-4.17.37/bootstrap-datetimejs.css');
        if ($bootstrapVersion !== self::BS3_VER) {
            self::requireCss('Lib/glyphicons-bs-3.3.7/style.css');
        }
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

    public function setAction($action, $parameters = null, $confirmMessage = null, $class = 'change-execute datepicker-change')
    {
        $this->dateComponent->setAction($action, $parameters, $class, $confirmMessage);
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

    public function setPlaceholder($placeholder)
    {
        $this->getTextBox()->setPlaceholder($placeholder);
        return $this;
    }
}
