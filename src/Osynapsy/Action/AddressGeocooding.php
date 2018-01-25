<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Action;

/**
 * Description of Geoocoding
 *
 * @author p.celeste@osynapsy.net
 */
class AddressGeoocoding 
{
    //put your code here
    public static function getLatLng($address)
    {
           $address = trim($address);
           $geourl = "http://maps.googleapis.com/maps/api/geocode/json?address={$address}&sensor=false&region=it";
           
           // Create cUrl object to grab XML content using $geourl
           $c = curl_init();
           curl_setopt($c, CURLOPT_URL, utf8_encode($geourl));
           curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
           curl_setopt($c, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		   //curl_setopt($c, CURLOPT_CONNECTTIMEOUT ,2);
		   //curl_setopt($c, CURLOPT_TIMEOUT, 5);
           $resp = trim(curl_exec($c));
           //$r = curl_getinfo($c);
           curl_close($c);
           // Create SimpleXML object from XML Content           
           $obj = json_decode($resp);           
           // Print out all of the XML Object
           if ($obj->status && $obj->status == 'OK') {               
               return array(
                   $obj->results[0]->geometry->location->lat,
                   $obj->results[0]->geometry->location->lng
               );
           }
           return false;
    }
}

