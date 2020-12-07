<?php

class Utils_Mail {
   
    public static function enviarEmail($data) {
    	
        $de      = $data['from'];
        $nome    = $data['nome'];
        $para    = $data['to'];
        $assunto = $data['assunto'];        
        $mensagem = str_replace("\n", "<br/>", $data['mensagem']);
        
        $headers = 'Content-type:text/html; charset=UTF-8"' . "\r\n" .
                'From: '.$nome.' <'. $de . '>'. "\r\n".
                'To: <'. $para . '>' . "\r\n";                
        
        
        $send = mail($para, $assunto, $mensagem, $headers);

        if (!$send) {
            return false;
        }else {
	    return true;                
        } 
        

    }
    
}