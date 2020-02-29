<?php
namespace Osynapsy\Auth\Jwt;

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
    public function decode($token)
    { 
        if (empty($token)) {
            throw new AuthenticationException('Token is empty.', 404);
        }
        $tokenPart = explode('.', $token);
        //Guard clause token must be composed of three parts
        if (count($tokenPart) !== 3) {
            throw new AuthenticationException('Token is invalid. It is not composed of three parts.', 401);
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
            throw new AuthenticationException('Token is invalid. Received signature is not equal to resulted signature.', 401);            
        }
        //If token is valid decode the fields
        $fields = json_decode(base64_decode($tokenPart[1]), true);
        if (!empty($this->fields) && !empty($this->fields['Exp']) && $this->fields['Exp'] < time()) {
            throw new AuthenticationException('Token is expired', 401);
        }
        return $fields;        
    }              
}
