<?php

class Model_Rule_GerarAvaliacao extends Model_Rule_Abstract {

    public function calcularPercentualDeGorduraHomem($densidade, $idade) {
        //Correção de constantes para a fórmula de Siri (1961), adaptado de Lohman (1986)
        //Equação de Siri (1961)  %G = ((4,95/D) – 4,50) x 100

        $percentGordura = null;

        if( $idade == 7 || $idade == 8 ) {
            $percentGordura = ( ( 538 / $densidade ) - 497 );
        }else

        if( $idade == 9 || $idade == 10 ) {
            $percentGordura = ( ( 530 / $densidade ) - 489 );
        }else

        if( $idade == 11 || $idade == 12 ) {
            $percentGordura = ( ( 523 / $densidade ) - 481 );
        }else

        if( $idade == 13 || $idade == 14 ) {
            $percentGordura = ( ( 507 / $densidade ) - 464 );
        }else

        if( $idade == 15 || $idade == 16 ) {
            $percentGordura = ( ( 503 / $densidade ) - 459 );
        }else

        if( $idade == 17 || $idade == 18 || $idade == 19 ) {
            $percentGordura = ( ( 498 / $densidade ) - 453 );
        }else

        if( $idade >= 20 ) { //Adicionar isso se precisar (&& $idade <= 50)
            $percentGordura = ( ( 495 / $densidade ) - 450 );
        }

        else{
            throw new Exception( "A idade $idade não se aplica na fórmula de Siri (1961)" );
        }

        return $percentGordura;
    }

    public function calcularPercentualDeGorduraMulher($densidade, $idade) {
        //Correção de constantes para a fórmula de Siri (1961), adaptado de Lohman (1986)
        //Equação de Siri (1961)  %G = ((4,95/D) – 4,50) x 100
               
        $percentGordura = null;
       
        if( $idade == 7 || $idade == 8 ) {
            $percentGordura = ( ( 543 / $densidade ) - 503 );
        }else
           
        if( $idade == 9 || $idade == 10 ) {
            $percentGordura = ( ( 535 / $densidade ) - 495 );
        }else
           
        if( $idade == 11 || $idade == 12 ) {
            $percentGordura = ( ( 525 / $densidade ) - 484 );
        }else 
           
        if( $idade == 13 || $idade == 14 ) {
            $percentGordura = ( ( 512 / $densidade ) - 469 );
        }else    
           
        if( $idade == 15 || $idade == 16 ) {
            $percentGordura = ( ( 507 / $densidade ) - 464 );
        }else        
           
        if( $idade == 17 || $idade == 18 || $idade == 19 ) {
            $percentGordura = ( ( 505 / $densidade ) - 462 );
        }else
           
        if( $idade >= 20 ) { //Adicionar iso se precisar (&& $idade <= 50)
            $percentGordura = ( ( 503 / $densidade ) - 459 );
        }
       
        else{
            throw new Exception( "A idade $idade não se aplica na fórmula de Siri (1961)" );
        }  
       
        return $percentGordura;
    }
   
   
    ################################################################################################################################################
    ###################  POLLOCK (1980)  ###########################################################################################################
    ################################################################################################################################################

    /* Homens com idade entre 18 e 61 anos */
    public function protocoloPollock3Dobras($aDobras, $idade, $sexo) {
        //ST = ( Soma das 3 dobras )
        $DC = null;


        if( $sexo == 'M' ) { 
            $X = ( $aDobras['PE'] + $aDobras['AB'] + $aDobras['CX'] );
            $DC = ( 1.1093800 - 0.0008267 * $X + 0.0000016 * ($X * $X) - 0.0002574 * $idade );
        }else

        if( $sexo == 'F' ) {
          $X = ( $aDobras['TR'] + $aDobras['SI'] + $aDobras['CX'] );
            $DC = ( 1.099421 - 0.0009929 * $X + 0.0000023 * ($X * $X) -  0.0001392 * $idade  );
        }

        return $DC;
    }

    /* Mulheres com idade entre 18 e 55 anos */
    public function protocoloPollock7Dobras($aDobras, $idade, $sexo) {
        //ST = ( Soma das 7 dobras )
        $ST = ( $aDobras['PE'] + $aDobras['AX'] + $aDobras['TR'] + $aDobras['SB'] + $aDobras['AB'] + $aDobras['SI'] + $aDobras['CX'] );
        $DC = null;

        if( $sexo == 'M' ) {
            $DC = ( 1.11200000 - 0.00043499 * $ST + 0.00000055 * ($ST*$ST)  - 0.0002882 * $idade );
        }else

        if( $sexo == 'F' ) {
            $DC = ( 1.0970 - 0.00046971 * ($ST) + 0.00000056 * ($ST * $ST) - 0.00012828 * $idade );
        }

        return $DC;
    }


    //Homens e Mulheres de 18 a 30 anos
    public function protocoloGuedes($aDobras, $sexo) {
       $DC = null;

       //Homens de 17 a 27 anos
       if( $sexo == 'M' ) {
           $DC = ( 1.17136 - 0.06706 * log($aDobras['TR'] + $aDobras['SI'] + $aDobras['AB']) );
       }else

       //Mulheres de 18 a 30 anos
       if( $sexo == 'F' ) {
           $DC = ( 1.16650 - 0.07063 * log($aDobras['SB'] + $aDobras['SI'] + $aDobras['CX']) );
       }

       return $DC;
    }

    public function protocoloPetroski($aDobras, $sexo, $idade) {
       $X = ( $aDobras['SB'] + $aDobras['TR'] + $aDobras['SI'] + $aDobras['PA'] ); 
       $Y = ( $aDobras['AX'] + $aDobras['SI'] + $aDobras['CX'] + $aDobras['PA'] ); 
       $DC = null;

       //Homens de 18 a 61 anos
       if( $sexo == 'M' ) {
           $DC = ( 1.10726863 - 0.00081201 * $X + 0.00000212 * ($X * $X) - 0.00041761 * $idade );
       }else
       //Mulheres de 18 a 51 anos
       if( $sexo == 'F' ) {
           $DC = ( 1.19547130 - 0.07513507 * log10($Y) - 0.00041072 * $idade );
       }

       return $DC;
    }

    public function protocoloDurnin($aDobras, $sexo, $idade) {
       $X = ( $aDobras['BI'] + $aDobras['TR'] + $aDobras['SB'] + $aDobras['SI'] ); 

       $DC = null;

       if( $sexo == 'M' ) {

           if( $idade >= 16 && $idade <= 19 ){
               $DC = ( 1.1549 - 0.0678 * log10($X) );
           }else
           
           if( $idade >= 20 && $idade <= 29 ){               
               $DC = ( 1.1559 - 0.0717 * log10($X) );
           }else
               
           if( $idade >= 30 && $idade <= 39 ){
               $DC = ( 1.1423 - 0.0632 * log10($X) );
           }else    
            
           if( $idade >= 40 && $idade <= 49 ){
               $DC = ( 1.1333 - 0.0612 * log10($X) );
           }  
           
       }else
           
       //Mulheres de 18 a 30 anos    
       if( $sexo == 'F' ) {
           $DC = ( 1.1581 - 0.0720 * log10($X) );
       }    
       
       return $DC;
    }
    
    public function protocoloMcardle($aDobras, $sexo, $idade) {   
        //Protocolo Mcardle (1992)
        //Homens e Mulheres de 9 a 16 anos 
        
        $DC = null;
        
        if( $idade >= 9 && $idade <= 12 ){
            if( $sexo == 'M' ):
                $DC = (  1.108 - 0.027 * log10($aDobras['TR']) - 0.038 * log10($aDobras['SB'])  ); 
            else:
                $DC = (  1.088 - 0.014 * log10($aDobras['TR']) - 0.036 * log10($aDobras['SB'])  );
            endif;
        }else

        if( $idade >= 13 && $idade <= 16 ){
            if( $sexo == 'M' ):
                $DC = (  1.130 - 0.055 * log10($aDobras['TR']) - 0.026 * log10($aDobras['SB'])  ); 
              else:
                $DC = (  1.114 - 0.031 * log10($aDobras['TR']) - 0.041 * log10($aDobras['SB'])  );
            endif;
        }else

        if( $idade >= 18 && $idade <= 27 ){
            if( $sexo == 'M' ):
                $DC = (  1.0913 - 0.00116 * ($aDobras['TR'] + $aDobras['SB']) ); 
            endif;
        }else

        if( $idade > 27 && $idade <= 34 ){
            if( $sexo == 'M' ):
                $DC = (  1.1610 - 0.0632 * log($aDobras['BI'] + $aDobras['TR'] + $aDobras['SB'] + $aDobras['SI'])  ); 
            endif;
        }else

        if( $idade >= 18 && $idade <= 48 ){
            if( $sexo == 'F' ):
                $DC = (  1.06234 - 0.00068 * ($aDobras['SB']) - 0.00039 * ($aDobras['TR']) - 0.00025 * ($aDobras['CX']) );
            endif;
        }




        
        return $DC;
    }
   
   
    public function calcularDensidadeCorporal($aDobras, $aDado) {       
        $protocolo = $aDado['protocolo'];
        $idade     = $aDado['idade'];
        $sexo      = $aDado['sexo'];
        $densidade = null;
       
        switch ($protocolo){
            case 'pollock3dobras': {
                $densidade = $this->protocoloPollock3Dobras($aDobras, $idade, $sexo);
            }break;
            
            case 'pollock7dobras': {
                $densidade = $this->protocoloPollock7Dobras($aDobras, $idade, $sexo);
            }break;
        
            case 'guedes': {
                $densidade = $this->protocoloGuedes($aDobras, $sexo);
            }break;
            
            case 'petroski': {
                $densidade = $this->protocoloPetroski($aDobras, $sexo, $idade);
            }break;
            
            case 'durnin': {
                $densidade = $this->protocoloDurnin($aDobras, $sexo, $idade);
            }break;
            
            case 'mcardle': {
                $densidade = $this->protocoloMcardle($aDobras, $sexo, $idade);
            }break;
        }
       
        return $densidade;
    }
   
   
   
    public function calcularPercentualDeGordura($densidade, $sexo, $idade) {
        $percentGordura = null;
       
        try {
           
            switch ($sexo) {
                case 'M': $percentGordura = $this->calcularPercentualDeGorduraHomem($densidade, $idade);
                    break;               
                case 'F': $percentGordura = $this->calcularPercentualDeGorduraMulher($densidade, $idade);
                    break;
            }
           
        }
        catch(Exception $e){
            die( $e->getMessage() );
        }   
          
        return number_format($percentGordura, 2);
    }
    
    
    public function calcularIMC($peso, $altura) {
        $metro = substr($altura, 0, 1);
        $centimetro = substr($altura, 1, 2);
        $altura = $metro.'.'.$centimetro;

        $IMC = ( $peso / ($altura * $altura) );
        return $IMC;
    }
    
    public function avaliacaoIMC($imc) {
        $resultado = null;
        
        if( $imc < 18.4 ) {
            $resultado = '<span style="color: red;font-weight: bold;">Abaixo do peso</span>';
        }else
        
        if( $imc >= 18.5 && $imc <= 24.9 ) {
            $resultado = '<span style="color: green;font-weight: bold;">Peso normal</span>';
        }else
        
        if( $imc >= 25 && $imc <= 29.9 ) {
            $resultado = '<span style="color: darkorange;font-weight: bold;">Sobrepeso</span>';
        }else
        
        if( $imc >= 30 && $imc <= 34.9 ) {
            $resultado = '<span style="color: crimson;font-weight: bold;">Obesidade Grau I</span>';
        }else
        
        if( $imc >= 35 && $imc <= 39.9 ) {
            $resultado = '<span style="color: darkred;font-weight: bold;">Obesidade Grau II</span>';
        }else
        
        if( $imc > 40 ) {
            $resultado = '<span style="color: red;font-weight: bold;">Obesidade Grau III</span>';
        }
        
        return $resultado;
    }
    
}