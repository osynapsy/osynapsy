<?php
namespace Osynapsy\Html\Bcl\Calendar;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MontlyStrategy
 *
 * @author pietr
 */
class MontlyStrategy 
{
    private $startDate;
    
    public function __construct(\DateTime $initDate)
    {
        $this->startDate = $initDate->format();
    }
    
    private function firstDayOfMonth($date)
    {
        list($aa,$mm,$dd) = explode('-',$date);
        return (($d = jddayofweek(GregorianToJD ($mm,1,$aa),0)) == 0) ? 7 : $d;
    }
}
