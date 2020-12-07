<?php
class Utils_Date {
    
    public static function validaemail($email){
        //verifica se e-mail esta no formato correto de escrita
        if (!@preg_match('/^([a-zA-Z0-9.-_])*([@])([a-z0-9]).([a-z]{2,3})//',$email)){
            //$mensagem='E-mail Inv&aacute;lido!';
            return false;
        }
        else{
            //Valida o dominio
            $dominio=explode('@',$email);
            if(!checkdnsrr($dominio[1],'A')){
                //$mensagem='E-mail Inv&aacute;lido!';
                return false;
            }
            else{
                return true;
            } // Retorno true para indicar que o e-mail é valido
        }
    }
    
    public static function mesExtensoPortugues($mesNumero) {
        $nomeDosMeses = array(NULL,
                'Janeiro', 'Fevereiro', 'Março',
                'Abril'  , 'Maio'     , 'Junho',
                'Julho'  , 'Agosto'   , 'Setembro',
                'Outubro', 'Novembro' , 'Dezembro');

        if(array_key_exists($mesNumero, $nomeDosMeses)) {
            return $nomeDosMeses[$mesNumero];
        }
        return "Mes Incorreto";
    }
    
    public static function formataMoedaBD($valor) {
        $valor = str_replace(',','.',str_replace('.','',$valor));
        return $valor;
    }


    public static function formataMoeda($valor){        
        $valor = str_replace(',', '.', $valor);
        
        $aPartes = explode(".",$valor);
        // ==== Tratando os Centavos =======
        if (!array_key_exists(1, $aPartes)) // Se o valor = 0
            $aPartes[1] = 0;
        if($aPartes[1] == 0)
            $aPartes[1] = "00";
        if(strlen($aPartes[1]) == 1)
            $aPartes[1] = $aPartes[1]."0";
        // ==== Tratando o Milhar =======
        switch(strlen($aPartes[0])) {
            case 4://0.000
                $milhar = substr($aPartes[0],0,1).".".
                        substr($aPartes[0],1,3);
                break;
            case 5://00.000
                $milhar = substr($aPartes[0],0,2).".".
                        substr($aPartes[0],2,3);
                break;
            case 6://000.000
                $milhar = substr($aPartes[0],0,3).".".
                        substr($aPartes[0],3,3);
                break;
            case 7://0.000.000
                $milhar = substr($aPartes[0],0,1).".".
                        substr($aPartes[0],1,3).".".
                        substr($aPartes[0],4,3);
                break;
            case 8://00.000.000
                $milhar = substr($aPartes[0],0,2).".".
                        substr($aPartes[0],2,3).".".
                        substr($aPartes[0],5,3);
                break;
            case 9://000.000.000
                $milhar = substr($aPartes[0],0,3).".".
                        substr($aPartes[0],3,3).".".
                        substr($aPartes[0],6,3);
                break;
            default:
                $milhar = $aPartes[0];
                break;
        }
        
        return $milhar.",".$aPartes[1];
    }
    
    public static function formataDataToBd($data) {
        $explodeData = explode('/',$data);
        return $explodeData[2].'-'.$explodeData[1].'-'.$explodeData[0];
    }
    
    public static function formataDataToShow($data) {
        $data = explode(' ',$data);
        $explodeData = explode('-',$data[0]);
        return $explodeData[2].'/'.$explodeData[1].'/'.$explodeData[0];
    }
    public static function formataDataSemHoraToShow($data) {
        if ($data=='') return '';
        $time = explode(":",$data);
        $formatTime = explode(" ",$time[0]);
        $dataFormatada = explode("-",$formatTime[0]);
        return $dataFormatada[2]."/".$dataFormatada[1]."/".$dataFormatada[0];
    }
    
}