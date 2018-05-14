<?php
namespace Osynapsy\Html\Bcl;

use Osynapsy\Html\Component;
use Osynapsy\Html\Tag;

/**
 * Description of Calendar
 *
 * @author Peter
 */
class Calendar extends Component
{
    private $daysOfWeek = array(
        'Luned&igrave;',
        'Marted&igrave;',
        'Mercoled&igrave;',
        'Gioved&igrave;',
        'Venerd&igrave;',
        'Sabato',
        'Domenica'
    );

    private $month = array(
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
    );

    public function __construct($initDate, $type = null)
    {
        $this->addRequire('Ocl/Component/Calendar/style.css');
        $this->addRequire('Ocl/Component/Calendar/controller.js');
        parent::__construct('div');

        try {
            $idat = new \DateTime(empty($_REQUEST['calendar_init_date']) ? date('Y-m-d') : $_REQUEST['calendar_init_date']);
        } catch (\Exception $e){
            $idat = new \DateTime(date('Y-m-d'));
        }

        $this->__par = array(
            'type' => 'mounthly',
            'dimension' => array(
                'width'  => 640, 
                'height' => 480
            ),
            'days' => array(),
            'type' => (empty($_REQUEST['calendar_layout']) ? 'mounthly' : $_REQUEST['calendar_layout']),
            'init-date' => $idat
        );

        $this->att('class',"osy-view-calendar");
          
        switch($this->getParameter('type')) {
            case 'daily': 
                $this->par('period',[$idat->format('Y-m-d'),$idat->format('Y-m-d')]);
                $this->par('build-method','build_daily');
                break;
            case 'weekly': 
                $dw = $idat->format('w');
                $dw = empty($dw) ? 7 : $dw;
                //$ws = $idat->format('d') - ($dw - 1);
                //$we = $idat->format('d') + (7 - $dw);
                $dfws = clone $idat;
                $dfwe = clone $idat;
                $dfws->sub(new \DateInterval('P'.($dw - 1).'D'));
                $dfwe->add(new \DateInterval('P'.(7 - $dw).'D'));
                $this->par('period', [$dfws->format('Y-m-d'), $dfwe->format('Y-m-d')]);
                $this->par('build-method','build_weekly');
                break;
            default     :
                $this->par('period', [$idat->format('Y-m-01'), $idat->format('Y-m-t')]);
                $this->par('build-method', 'build_monthly');
                break;
        }
    }

    private function firstDayOfMonth($date)
    {
        list($aa,$mm,$dd) = explode('-',$date);
        return (($d = jddayofweek(GregorianToJD ($mm,1,$aa),0)) == 0) ? 7 : $d;
    }

    public function build()
    {
        switch($this->getParameter('type')) {
            case 'daily':
                $this->buildDaily();
                break;
            case 'weekly':
                $this->buildWeekly();
                break;
            default:     
                $this->buildMonthly();
                break;
        }
    }
        
    private function buildToolbar($label, $prev, $next, $type = 'monthly')
    {
        $hd = $this->add(new Tag('div'))->att('class','osy-view-calendar-toolbar');
        //Button month navigation
        $nav = $hd->add(new Tag('div'))
                  ->att('class','osy-view-calendar-navigation');
        $nav->add('<input type="button" name="btn_prev" value="&lt;" class="osy-calendar-command" data-date="'.($prev->format('Y-m-d')).'">');
        $nav->add('<input type="button" name="btn_next" value="&gt;" class="osy-calendar-command" data-date="'.($next->format('Y-m-d')).'">');
        //Label current month
        $lbl = $hd->add(new Tag('div'))->att('class','osy-view-calendar-label')->add($label);
        //Button calendar type
        $dty = $hd->add(new Tag('div'))->att('class','osy-view-calendar-type');
        $dty->add('<input type="submit" id="btn_daily" value="Giorno" class="osy-calendar-command'.($type=='daily' ? ' ui-state-active' : '').'">');
        $dty->add('<input type="submit" id="btn_weekly" value="Settimana" class="osy-calendar-command'.($type=='weekly' ? ' ui-state-active' : '').'">');
        $dty->add('<input type="submit" id="btn_monthly" value="Mese" class="osy-calendar-command'.($type=='monthly' ? ' ui-state-active' : '').'">');
        $hd->add('<br style="clear: both;">');
    }
        
    public function buildDaily()
    {
        $prev = new \DateTime($this->getParameter('init-date')->format('Y-m-d'));
        $next = new \DateTime($this->getParameter('init-date')->format('Y-m-d'));
        $prev->sub(new \DateInterval('P1D'));
        $next->add(new \DateInterval('P1D'));
        $this->__build_toolbar__($this->getParameter('init-date')->format('d F Y'),$prev,$next,'daily');
        $calendar = $this->add(new Tag('div'))->att('class','osy-view-calendar-daily');
        $body_noh = $calendar->add(new Tag('div'))->att('class','daily-events');
        $body = $calendar->add(new Tag('div'))->att('class','timed-events')->add(new Tag('table'));
        $curd = $this->_current_day.'/'.$this->__par__['init-month'].'/'.$this->__par__['init-year'];
        $devt = (!empty($this->items[$curd])) ? $this->items[$curd] : array();
        //var_dump($this->items);
        $raw_items = array_key_exists($this->getParameter('init-date')->format('Y-m-d'),$this->items) ? $this->items[$this->getParameter('init-date')->format('Y-m-d')] : array();
        $items = array();
        foreach($raw_items as $rec) {
            $a = explode(':',$rec['hour']);
            if (intval($a[0]) < 8) {
                $items[0][] = $rec;
            } else {
                $min = empty($a[1]) || intval($a[1]) < 30 ? '00' : '30';
                $items[intval($a[0])][$min][] = $rec;
            }
        }
        
        $format_item = function($items, $class='', $hour='') {
            $td = new Tag('td');
            $td->att('class','event-cont add_event '.$class);
            if (empty($items)){  $td->add('&nbsp;'); return $td; }
            $pkey = $this->getParameter('pkey');
                
            foreach($items as $k => $rec) {
                if (empty($rec)) {
                    continue;
                }
                $div = $td->add( new Tag('div'))
                          ->att('class','event '.(!empty($rec['event_color']) ? $rec['event_color'] : 'osy-event-color-normal'));
                if (!empty($rec['hour'])) {
                    $end = $rec['event_end'] ? " &#8594; ".$rec['event_end'] : '';
                    $div->add("<span class=\"event-time\">{$rec['hour']}{$end}</span>");
                } elseif(!empty($rec['event_duration'])) {
                    $div->add("<span class=\"event-time\">{$rec['event_duration']} min</span>");
                }
                $itm = $div->add(new Tag('div'))->att('class','event-body');
                $itm->add($rec['event']);
                if (is_array($pkey)) {
                    $key = array();
                    foreach($pkey as $k => $fld) {
                        if (array_key_exists($fld,$rec)) $key[] = 'pkey['.$fld.']='.$rec[$fld];
                    }
                    if (count($pkey) == count($key)) {
                        $itm->att('__k',implode('&',$key))->att('class','osy-view-calendar-item',true);
                    }
                }
            }
            return $td;
        };
        
        $table_head = $body_noh->add(new Tag('table'));
        $row = $table_head->add(new Tag('tr'));
        $row->add(new Tag('td'))->att('class', 'dummy-time')->add('<a href="#" class="add_event">+ Evento</a>');
        if (!empty($items[0])){             
            $row->add($format_item($items[0],'event-daily'));
        } else {
            $row->add($format_item(array(array()),'event-daily'));
        }
        foreach(range(0,23) as $v) {
            $row = $body->add(new Tag('tr'));
            $chh = $row->add(new Tag('td'))
                       ->att('class', 'cont-hour')
                       ->att('rowspan','2');
            $hh = str_pad($v,2,'0',STR_PAD_LEFT).':00';
            $chh->add($hh);
            $cnt = $row->add($format_item($items[$v]['00'],'btop-solid')->att('data-hour',$hh));
            if ($v == 8) {
                $chh->att('class','dummy-first',true);
            }
            $row = $body->add(new Tag('tr'));
            $cnt = $row->add($format_item($items[$v]['30'],'btop-dot')->att('data-hour',str_replace(':00',':30',$hh)));
        }
    }

    private function buildWeekly()
    {
        $start_day = $this->getParameter('init-date')->format('Y-m-d');
        $intv = new \DateInterval('P1W');
        $prev = new \DateTime($this->getParameter('init-date')->format('Y-m-d'));
        $prev->sub($intv);
        $next = new \DateTime($this->getParameter('init-date')->format('Y-m-d'));
        $next->add($intv);

        //Calcolo primo giorno della settimana.
        $week_day = $prev->format('w') == '0' ? 7 : $prev->format('w');
        $current_day = new \DateTime($this->getParameter('init-date')->format('Y-m-d'));
        $current_day->sub(new \DateInterval('P'.($week_day-1).'D'));
        $last_day = new \DateTime($current_day->format('Y-m-d'));
        $last_day->add(new \DateInterval('P7D'));
        $label = $current_day->format('d') . ' - ' . $last_day->format('d M Y');
        
        if ($current_day->format('Y') < $last_day->format('Y')){
            $label = $current_day->format('d M Y') . ' - ' . $last_day->format('d M Y');
        } elseif ($current_day->format('d') > $last_day->format('d')){
            $label = $current_day->format('d M') . ' - ' . $last_day->format('d M Y');
        } else {
            $label = $current_day->format('d') . ' - ' . $last_day->format('d M Y');
        }
        
        $this->buildToolbar($label,$prev,$next,'weekly');
        
        $calendar = $this->add(new Tag('div'))->att('class','osy-view-calendar-weekly');
        //HEAD
        $head = $calendar->add(new Tag('div'))
                         ->att('id','calendar-head')
                         ->att('class','osy-view-calendar-head');
        $rw1 = $head->add(new Tag('div'))->att('class','day-label');
        $rw1->add(new Tag('div'))->att('id','dummy-event')->add('<span>&nbsp;</span>');
        $rw2 = $head->add(new Tag('div'))->att('class','day-event');
        $rw2->add(new Tag('div'))->att('id','dummy-event')->add('<span>&nbsp;</span>');
        $items = array();
        
        foreach($this->items as $day => $events) {
            foreach($events as $k => $rec) {
                $a = explode(':',$rec['hour']);
                //var_dump($rec);
                if (intval($a[0]) < 8){
                    $items[$day][0][] = $rec;
                } else {
                    $min = empty($a[1]) || intval($a[1]) < 30 ? '00' : '30';
                    $items[$day][intval($a[0])][$min][] = $rec;
                }
            }
        }
        foreach (range(1,7) as $k => $v) {
            $rw1->add(new Tag('div'))
                ->att('class','day-num')
                ->att('data-date',$current_day->format('Y-m-d'))
                ->add($current_day->format('D d/m'));
            $div = $rw2->add(new Tag('div'));
            if (!empty($items[$current_day->format('Y-m-d')][0])) {
                $this->__make_item__($div,$items[$current_day->format('Y-m-d')][0]);
            } 
            $current_day->add(new \DateInterval('P1D'));
        }
        //BODY
        $body = $calendar->add(new Tag('div'))
                         ->att('id','calendar-body')
                         ->add(new Tag('table'))
                         ->att('class','osy-view-calendar-body')
                         ->add(new Tag('tbody'));
        $cgr = $body->add(new Tag('colgroup'));
        $cgr->add(new Tag('col'))->att('class','col-hour');
        $cgr->add(new Tag('col'))->att('span',7)->att('class','col-event');
        foreach(range(0,23) as $v) {
            $hh = str_pad($v,2,'0',STR_PAD_LEFT);
            $row_1 = $body->add(new Tag('tr'));
            $row_2 = $body->add(new Tag('tr'));
            $chh = $row_1->add(new Tag('td'))
                         ->att('class', 'cont-hour btop-solid')
                         ->att('rowspan','2');
            $chh->add($hh.':00');
            if ($v == 8){ $chh->att('class','dummy-first',true); }
            $current_day = new \DateTime($this->getParameter('init-date')->format('Y-m-d'));
            $current_day->sub(new \DateInterval('P'.($week_day-1).'D'));
            foreach(range(1,7) as $v){
                $cel_1 = $row_1->add(new Tag('td'))->att('class','btop-solid add_event')->att('data-hour',$hh.':00');
                $cel_2 = $row_2->add(new Tag('td'))->att('class','btop-dot add_event')->att('data-hour',$hh.':30');
                $day = $current_day->format('Y-m-d');
                if ($items[$day] && !empty($items[$day][intval($hh)])){
                    foreach(['00','30'] as $half) {
                        if($half == '00') {
                          $cel =& $cel_1;
                        } else {
                          $cel =& $cel_2;
                        }
                        if (!empty($items[$day][intval($hh)][$half])) {
                            $this->__make_item__($cel,$items[$day][intval($hh)][$half]);
                        } else  {
                            $cel->add('&nbsp;');
                        }
                    }
                } else {
                    $cel_1->add('&nbsp;');
                    $cel_2->add('&nbsp;');
                }
                $current_day->add(new \DateInterval('P1D'));
            }
         }
    }
    
    private function buildMonthly()
    {
        $days = array_pad(array(),43,"&nbsp;");
        $month_len = $this->getParameter('init-date')->format('t');
        $start_day = $this->firstDayOfMonth($this->getParameter('init-date')->format('Y-m-d'));
        //var_dump($start_day);
        for ($i = 0; $i < $month_len; $i++) {
            $days[$start_day + $i] = $i + 1;
        }
        $cellHeight = floor(($this->__par['dimension']['height'] - 120) / 6);
        $cellWidth  = floor($this->__par['dimension']['width'] / 7)-2;
        $intv = new \DateInterval('P1M');
        $prev = new \DateTime($this->getParameter('init-date')->format('Y-m-01'));
        $prev->sub($intv);
        $next = new \DateTime($this->getParameter('init-date')->format('Y-m-01'));
        $next->add($intv);
        //Build toolbar;
        $this->buildToolbar($this->getParameter('init-date')->format('F Y'), $prev, $next);
        //Build body;
        $body = $this->add(new Tag('table'))->att('class','osy-view-calendar-monthly osy-maximize');
        $row = $body->add(new Tag('thead'))->add(new Tag('tr'));
        foreach($this->days_of_week as $day) {
            $row->add(new Tag('th'))->add($day);
        }
        $k        = $h = 1;
        $data     = $this->getParameter('init-date')->format('Y-m-');
        $tbody    = $body->add(new Tag('tbody'));
        $init_day     = $this->getParameter('init-date')->format('Y-m-d');
        for ($j = 0; $j < 6; $j++) {
            $row = $tbody->add(new Tag('tr'));
            for ($i = 0; $i < 7; $i++) {
                $cel = $row->add(new Tag('td'))
                           ->att('class','day')
                           ->att('valign','top')
                           ->att('style',"height: {$cell_hgt}px; width: {$cell_wdt}px;");
                switch($i) {
                    case 6:
                            $cel->style .= 'color: red;';
                            break;
                }
                $k++;
                if ($days[$k] == "&nbsp;") {
                    $cel->att('class','dummy')->add('&nbsp');
                    continue;
                } 
                $dateCicle = $data.str_pad($days[$k],2,'0',STR_PAD_LEFT);
                $cel->att('data-date',$dateCicle)
                    ->att('onmousedown',"return false")
                    ->att('onselectstart',"return false");
                if ($dateCicle == date('Y-m-d')) {
                    $cel->att('class','today',true);
                }
                if ($dateCicle == $init_day) {
                    $cel->att('class','selected',true);
                }
                //Num day
                $cel->add(new Tag('div'))->att('class',"day-num")->add($days[$k]);
                if (!array_key_exists($dateCicle,$this->items)) {
                    continue;
                } 
                if (empty($this->items[$dateCicle])) {
                    continue;    
                }
                $cnt = $cel->add(new Tag('div'))->att('class','cell-cont '.($dateCicle < date('Y-m-d') ? 'day-past' : 'day-future'),true);
                $cnt->att('style','width: '.$cell_wdt.'px;');
                $ext_evt = 0;
                for ($t = 0; $t < count($this->items[$dateCicle]); $t++) {
                    if ($t > 1) { 
                       $ext_evt++; 
                       continue; 
                    }
                    if (!empty($this->items[$dateCicle][$t])) {
                        $item = $this->items[$dateCicle][$t];
                        $cnt->add(new Tag('div'))
                            ->add('<span>'.$item['hour'].'</span>'.$item['event.short']);
                    }
                }
                if (!empty($ext_evt)) {
                    $cnt->add(new Tag('div'))->add(($ext_evt == 1 ? '+ un altro evento' : '+ altri '.$ext_evt.' eventi.'));
                }
            }
        }
    }

    private function makeItem($par, $items, $class = '', $hour = '')
    {
        $pkey = $this->getParameter('pkey');
        foreach($items as $k => $rec) {
            if (empty($rec)) continue;
            $div = $par->add(new Tag('div'))
                      ->att('class','event '.(!empty($rec['event_color']) ? $rec['event_color'] : 'osy-event-color-normal'));
            if (!empty($rec['hour'])) {
                $end = $rec['event_end'] ? " &#8594; ".$rec['event_end'] : '';
                $div->add("<span class=\"event-time\">{$rec['hour']}{$end}</span>");
            } elseif(!empty($rec['event_duration'])) {
                $div->add("<span class=\"event-time\">{$rec['event_duration']} min</span>");
            }
            $itm = $div->add(new Tag('div'))
                       ->att('class','event-body');
            $itm->add($rec['event']);
            if (is_array($pkey)) {
                $key = array();
                foreach($pkey as $k => $fld) {
                    if (array_key_exists($fld,$rec)) $key[] = 'pkey['.$fld.']='.$rec[$fld];
                }
                if (count($pkey) == count($key)) {
                    $itm->att('__k',implode('&',$key))->att('class','osy-view-calendar-item',true);
                }
            }
        }
        return $itm;
    }
     
    public function getEvent($date)
    {
        return $this->items[$date];
    }

    public function setDimension($width, $height)
    {
        if (!empty($w)){
            $this->__par['dimension']['width'] = $width;
        }
        if (!empty($height)){
            $this->__par['dimension']['height'] = $height;
        }
    }

    public function pushEvent($rec)
    {
        $par = ['day', 'hour', 'event'];
        foreach($par as $key) {
            if (!array_key_exists($key, $rec)) {
                die("La query di estrazione dati non contiene il campo ".$key);
            }
        }
        $rec['event.short'] = strlen($rec['event']) > 18 ? substr($rec['event'],0,16).'..' : $rec['event'];
        $this->items[$rec['day']][] = $rec;
    }
}
