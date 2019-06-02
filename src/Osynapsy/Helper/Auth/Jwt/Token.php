<?php
namespace Osynapsy\Helper\Auth\Jwt;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Token
{    
    const HEADER = '{"alg": "HS256", "typ": "JWT"}';                
    
    private $secretKey;
    private $fields;
    
    public function __construct($secretKey)
    {
        $this->secretKey = $secretKey;
    }
    
    /**
     * Metodo che genera un nuovo token
     * 
     * 
     * @param array $fields
     * @param int $expiry unixtimestamp expiry
     * @return string
     */
    public function generate(array $fields = [], $expiry = null)
    { 
        if (!empty($expiry)) {
            $fields['tokenExpiry'] = $expiry;
        }
        $b64Header = base64_encode(self::HEADER); 
        $b64Payload = base64_encode(json_encode($fields)); 
        $headerPayload = $b64Header . '.' . $b64Payload;
        $signature = base64_encode(hash_hmac(
            'sha256', 
            $headerPayload, 
            $this->secretKey, 
            true
        ));
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
    public function check($token)
    { 
        $tokenPart = explode('.', $token);
        //Guard clause token must be composed of three parts
        if (count($tokenPart) !== 3) {
            return false;
        }
        //Last part of token is the sign of token
        $recievedSignature = $tokenPart[2]; 
        //Part one and part two form the payload
    	$recievedHeaderAndPayload = $tokenPart[0] . '.' . $tokenPart[1];        
        //Sign part one and part two with secret key;
        $resultedSignature = base64_encode(
            hash_hmac('sha256', $recievedHeaderAndPayload, $this->secretKey, true)
        );
        //Token is not valid if received signature is not equal to resulted signature        
        if ($resultedSignature !== $recievedSignature) {
            return false;
        }
        //If token is valid decode the fields
        $this->fields = json_decode(base64_decode($tokenPart[1]), true);
        //Return true for cofirm which token is valid.
        return true;
    }
    
    public function getFields($token)
    {
        if ($this->check($token)) {
            return $this->fields;
        }
        throw new AuthenticationException('Token is invalid');
    }        
}
