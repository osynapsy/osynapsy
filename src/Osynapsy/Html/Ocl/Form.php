<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Html\Ocl;

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;

class Form extends Component
{
    private $components = array();
    public  $corner = null;

    public function __construct($nam,$tag='',$rowClass = null, $cellClass = null)
    {
        parent::__construct('form',$nam);
        $this->att('name',$nam)->att('method','post');
        /*
         * Creo una div cornice che conterr� il panel principale in modo da avere
         * un componente a cui poter assegnare un altezza fissa e quindi far comparire
         * le barre di scorrimento
         */
        $this->corner = $this->add(Tag::create('div'))->att('id',$nam.'-main');
        $this->corner->tagdep =& $this->tagdep;
        /*
         * Aggiungere il panel nella posizione 0 serve ad assegnare un panel di default
         * alla form su cui verranno aggiunti tutti i componenti che non hanno un panel-parent 
         * settatto.
         */
        $this->components[0] = $this->corner->add(
            new Panel($nam.'-panel', $tag, $rowClass, $cellClass)
        );
        $this->components[0]->setParameter('label-position','inside');
    }

    public function put($obj,$lbl,$nam,$x=0,$y=0,$par=0)
    {
         //if (!class_exists($typ)) {echo $typ; return;}
         //$obj  = new $typ($nam);
         //$obj->label = $lbl;
         if ($x == -1) //Se l'oggetto non ha position lo aggiungo in testa
         {
             $this->add($obj,'first');
             $par = empty($par) ? -1 : $par; //$par = -1;
         }
         // se il component ha dei childs nella sua posizione li aggiungo al componente
         if (array_key_exists($nam,$this->components) && is_array($this->components[$nam])) 
         {
            foreach($this->components[$nam] as $c)
            {
                $obj->put($c[0],$c[1],$c[2],$c[3]);
            }
         }
         //Aggiungo il componente alla lista dei componenti.
         $this->components[$nam] = $obj;
         //Se il parent del componente esiste lo associo direttamente al suo interno
         if (array_key_exists($par,$this->components) && is_object($this->components[$par]))
         {
            $this->components[$par]->put($lbl,$this->components[$nam],$x,$y);
         }
          else //Altrimenti lo metto nella posizione del parent in attesa che venga creato
         {
            $this->components[$par][] = array($lbl,&$this->components[$nam],$x,$y);
         }
         return $this->components[$nam];
    }

    public function buildPdf($pdf){
        $this->components[0]->build_pdf($pdf);
    }
}