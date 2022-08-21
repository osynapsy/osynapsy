<?php
namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Component;
use Osynapsy\Html\Tag;
use Osynapsy\Html\Ocl\HiddenBox;
use Osynapsy\Html\Bcl\Button;

/**
 * Description of Calendar
 *
 * @author Peter
 */
class Calendar extends Component
{
    private $daysOfWeek = [
        'LUN',
        'MAR',
        'MER',
        'GIO',
        'VEN',
        'SAB',
        'DOM'
    ];

    private $months = [
        '01' => 'Gennaio',
        '02' => 'Febbraio',
        '03' => 'Marzo',
        '04' => 'Aprile',
        '05' => 'Maggio',
        '06' => 'Giugno',
        '07' => 'Luglio',
        '08' => 'Agosto',
        '09' => 'Settembre',
        '10' => 'Ottobre',
        '11' => 'Novembre',
        '12' => 'Dicembre'
    ];
    private $monthCurrent;
    private $monthStart;
    private $monthEnd;
    private $currentDay;
    private $today;
    private $title;

    public function __construct($title = 'Calendar', $id = 'calendar')
    {
        parent::__construct('div', $id);
        $this->setClass('card border-0');
        $this->requireJsNew('assets/Bcl/Calendar/script.js');
        $this->requireCss('Bcl/Calendar/style.css');
        $this->title = $title;
        $this->init();
    }

    protected function init()
    {
        $initDate = filter_input(\INPUT_POST, "{$this->id}_date") ?? date('Y-m-1');
        $this->monthStart = (new \DateTime($initDate))->modify('first day of this month');
        $this->monthEnd = (new \DateTime($initDate))->modify('last day of this month');
        $this->monthCurrent = $this->monthStart->format('m');
        $this->today = new \DateTime('today');
    }

    protected function __build_extra__(): void
    {
        $this->add(new HiddenBox("{$this->id}_date"));
        $this->buildHead();
        $this->buildBody();
    }

    protected function buildHead()
    {
        $head = $this->add(new Tag('div', null, 'card-header bg-white border-0 px-0'));
        $row = $head->add(new Tag('div', null, 'row'));
        $row->add(new Tag('div', null, 'col-4 text-nowrap text-truncate'))->add($this->titleFactory($this->title));
        $row->add(new Tag('div', null, 'col-4 text-center text-nowrap'))->add($this->shiftCalendarCommandsFactory());
        $row->add(new Tag('div', null, 'col-4 text-right'))->add($this->buildButtonToday());
    }

    protected function titleFactory($title)
    {
        $result = new Tag('h5', null, 'font-weight-normal');
        $result->add('&nbsp;<i class="fa fa-calendar"></i> '.$title);
        return $result;
    }

    protected function shiftCalendarCommandsFactory()
    {
        $dummy = new Tag('dummy');
        $dummy->add($this->buildButtonPrev());
        $dummy->add(new Tag('h5', null, 'font-weight-normal d-inline-block mt-1'));
        $dummy->add($this->monthStart->format('F Y'));
        $dummy->add($this->buildButtonNext());
        return $dummy;
    }

    protected function buildButtonNext()
    {
        $date = clone $this->monthEnd;
        $btn = new Button('btn_next', 'button', 'btn-sm', '<i class="fa fa-arrow-right"></i>');
        $btn->att('style', 'margin-top: -10px;');
        $btn->att('onclick',"$('#{$this->id}_date').val('".($date->modify('+1 day')->format('Y-m-1'))."'); Osynapsy.refreshComponents(['{$this->id}']);");
        return $btn;
    }

    protected function buildButtonPrev()
    {
        $date = clone $this->monthStart;
        $btn = new Button('btn_prev', 'button', 'btn-sm', '<i class="fa fa-arrow-left"></i>');
        $btn->att('style', 'margin-top: -10px;');
        $btn->att('onclick',"$('#{$this->id}_date').val('".($date->modify('-1 day')->format('Y-m-1'))."'); Osynapsy.refreshComponents(['{$this->id}']);");
        return $btn;
    }

    protected function buildButtonToday()
    {
        $btn = new Button('btn_today', 'button', 'border btn-sm', 'Oggi');
        $btn->att('style', 'margin-top: -10px;');
        $btn->att('onclick',"$('#{$this->id}_date').val('".($this->today->format('Y-m-1'))."'); Osynapsy.refreshComponents(['{$this->id}']);");
        return $btn;
    }

    protected function buildBody()
    {
        $body = $this->add(new Tag('table', null, 'card-body p-0'));
        $body->att('style',' table-layout: fixed; word-wrap: break-word;');
        $row = $body->add(new Tag('thead'))->add(new Tag('tr', null, 'm-0'));
        for ($i = 0; $i < 7; $i++) {
            $row->add(new Tag('th', null, 'border text-center'))
                ->att('style', 'width: 14.28%')
                ->add(new Tag('small'))
                ->add($this->daysOfWeek[$i]);
        }
        $this->currentDay = $this->initCurrentDay();
        for ($i = 0; $i < 6; $i++) {
            $this->buildBodyRow($body, $i);
        }
    }

    protected function buildBodyRow($body)
    {
        $row = $body->add(new Tag('tr', null, 'm-0'));
        for ($i = 0; $i < 7; $i++) {
            $isToday = $this->currentDay == $this->today;
            $cell = $row->add(
                $this->buildBodyRowCell(
                    $this->currentDay->format('d'),
                    $isToday ? 'bg-primary p-1 text-white rounded-pill font-weight-normal' : 'font-weight-normal'
                )
            );
            if ($this->getCurrentMonth() !== $this->currentDay->format('m')) {
                $cell->addClass('bg-light');
            }
            if (empty($this->currentDay->format('w'))){
                $cell->addClass('text-danger');
            }
            $this->currentDay->modify('+1 day');
        }
    }

    protected function buildBodyRowCell($j, $classText = '')
    {
        $cell = new Tag('td', null, 'border');
        $cell->add(new Tag('div', null, 'cell-head text-center p-1'))
             ->add(new Tag('small', null, $classText))->att('style', 'font-size: 0.8em')
             ->add($j);
        $cell->add(new Tag('div', null, 'cell-body'))->add('&nbsp;');
        $cell->add(new Tag('div', null, 'cell-body'))->add('&nbsp;');
        $cell->add(new Tag('div', null, 'cell-body'))->add('&nbsp;');
        return $cell;
    }

    public function getCurrentMonth()
    {
        return $this->monthCurrent;
    }

    private function initCurrentDay()
    {
        $dayOfWeek = $this->getDayOfWeek($this->monthStart) - 1;
        $currentDay = clone $this->monthStart;
        return $currentDay->modify("- $dayOfWeek day");
    }

    private function getDayOfWeek(\DateTime $datetime)
    {
        $dayOfWeek = $datetime->format('w');
        return empty($dayOfWeek) ? 7 : $dayOfWeek;
    }
}
