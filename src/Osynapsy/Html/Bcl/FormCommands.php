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
            '<span class="fa fa-arrow-left"></span> Indietro'
        );
    }
    
    public function getCommandClose()
    {
        return new Button(
            'btn_close', 
            'button', 
            'cmd-close btn btn-default btn-secondary',
            '<span class="fa fa-times"></span> Chiudi'
        );
    }
    
    public function getCommandDelete()
    {
        $btnDelete = new Button(
            'btn_delete', 
            'button', 
            'btn-danger', 
            '<span class="fa fa-trash-o"></span> Elimina'
        );
        $btnDelete->setAction('delete', null ,'Sei sicuro di voler eliminare il record corrente?');
        return $btnDelete;
    }
    
    public function getCommandSave($label = true)
    {
        if ($label === true) {
            $label = '<span class="fa fa-floppy-o"></span> Salva';
        }
        $btnSave = new Button('btn_save', 'button', 'btn-primary', $label);
        $btnSave->setAction('save');
        return $btnSave;
    }
}
