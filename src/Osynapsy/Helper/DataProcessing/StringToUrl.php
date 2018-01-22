<?php
namespace Osynapsy\Core\Helper;

class StringToUrl
{
    public static function sanitize($string)
    {
        $string = preg_replace("`\[.*\]`U","",$string);
        $string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i','-',$string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","", $string );
        $string = preg_replace( array("`[^a-z0-9]`i","`[-]+`") , "-", $string);
        return strtolower(trim($string, '-'));
    }
}