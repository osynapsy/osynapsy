<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Osynapsy\Http\Response;

/**
 * Description of ResponseInterface
 *
 * @author Pietro Celeste <pietro.celeste@gmail.com>
 */
interface ResponseInterface
{
    public function addContent($content, $partId, $checkUnique = false);

    public function __toString();
}
