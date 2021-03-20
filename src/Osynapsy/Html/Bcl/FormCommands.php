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
    public function buttonBackFactory()
    {
        return new Button(
            'btn_back',
            'button',
            'cmd-back btn btn-default btn-secondary',
            '<span class="fa fa-arrow-left"></span> Indietro'
        );
    }

    public function buttonCloseModalFactory()
    {
        $button = new Button(
            'btn_close',
            'button',
            'cmd-close btn btn-default btn-secondary',
            '<span class="fa fa-times"></span> Chiudi'
        );
        return $button->att('onclick', "parent.$('#amodal').modal('hide');");
    }

    public function buttonDeleteFactory()
    {
        $btnDelete = new Button(
            'btn_delete',
            'button',
            'btn-danger',
            '<span class="fa fa-trash-o"></span> Elimina'
        );
        $btnDelete->setAction('delete', null ,'click-execute', 'Sei sicuro di voler procedere con l\'eliminazione ?');
        return $btnDelete;
    }

    public function buttonSaveFactory($label = true)
    {
        if ($label === true) {
            $label = '<span class="fa fa-floppy-o"></span> Salva';
        }
        $btnSave = new Button('btn_save', 'button', 'btn-primary', $label);
        $btnSave->setAction('save');
        return $btnSave;
    }
}
