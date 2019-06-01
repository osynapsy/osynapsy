<?php
namespace Osynapsy\Utils;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TokenJwt
{    
    const HEADER = '{"alg": "HS256", "typ": "JWT"}';                
    
    /**
     * Metodo che genera un nuovo token
     * 
     * @param string $secretKey
     * @param array $fields
     * @return string
     */
    public static function get($secretKey, array $fields = [])
    { 
        $b64Header = base64_encode(self::HEADER); 
        $b64Payload = base64_encode(json_encode($fields)); 
        $headerPayload = $b64Header . '.' . $b64Payload;
        $signature = base64_encode(
            hash_hmac('sha256', $headerPayload, $secretKey, true)
        );
        $token = $headerPayload . '.' . $signature;
        return $token; 
    }
    
    /**
     * Controlla che il token passato sia valido e ritorna i campi inseriti 
     * 
     * @param type $secretKey
     * @param type $token
     * @return boolean
     */
    public static function check($secretKey, $token)
    { 
        $tokenPart = explode('.', $token); 
        if (count($tokenPart) !== 3) {
            return false;
        }
        $recievedSignature = $tokenPart[2]; 
    	$recievedHeaderAndPayload = $tokenPart[0] . '.' . $tokenPart[1];
        $resultedSignature = base64_encode(
            hash_hmac('sha256', $recievedHeaderAndPayload, $secretKey, true)
        ); 
        if ($resultedSignature === $recievedSignature) {
            return json_decode(base64_decode($tokenPart[1]), true);
        }
        return false;
    }
}
