<?php 

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Data\Validator;

class IsDate extends Validator
{
    public function check()
    {
        list($d, $m, $y) = explode('/', $this->field['value']);
        //Se la data è valida la formatto secondo il tipo di db.
        if (!checkdate($m, $d, $y)) {
            return "Il campo {$this->field['label']} contiene una data non valida ($d}/{$m}/{$y}).";
        } else {
            $this->field['value'] = "{$y}-{$m}-{$d}";
        }
        return false;
    }
}
