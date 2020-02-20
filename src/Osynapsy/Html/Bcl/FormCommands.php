<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Osynapsy\Html\Bcl;

/**
 * Description of FormCommands
 *
 * @author Pietro
 */
trait FormCommands
{        
    public function getCommandBack()
    {
        return new Button(
            'btn_back', 
            'button', 
            'cmd-back btn btn-default btn-secondary',
            '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-left"></span> Indietro&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
        );
    }
    
    public function getCommandClose()
    {
        $button = new Button(
            'btn_close', 
            'button', 
            'cmd-close btn btn-default btn-secondary',
            '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-times"></span> Chiudi&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
        );
        return $button->att('onclick', "parent.$('#amodal').modal('hide');");
    }
    
    public function getCommandDelete()
    {
        $btnDelete = new Button(
            'btn_delete', 
            'button', 
            'btn-danger', 
            '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-trash-o"></span> Elimina&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
        );
        $btnDelete->setAction('delete', null ,'click-execute', 'Sei sicuro di voler procedere con l\'eliminazione ?');
        return $btnDelete;
    }
    
    public function getCommandSave($label = true)
    {
        if ($label === true) {
            $label = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-floppy-o"></span> Salva&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        $btnSave = new Button('btn_save', 'button', 'btn-primary', $label);
        $btnSave->setAction('save');
        return $btnSave;
    }
}
