<?php

class Utils_Calendario
{

    public static function MostreSemanas()
    {
        $semanas = "DSTQQSS";
        $diasSemana = "";
        for( $i = 0; $i < 7; $i++ ){
            $diasSemana .= "<td align='center' style='background: #666;color:#fff;'><b>".$semanas{$i}."</b></td>";
        }

        return $diasSemana;
    }

    public static function GetNumeroDias( $mes ) {
        $numero_dias = array(
        '1' => 31, '2' => 28, '3' => 31, '4' =>30, '5' => 31, '6' => 30,
        '7' => 31, '8' =>31, '9' => 30, '10' => 31, '11' => 30, '12' => 31);

        if (((date('Y') % 4) == 0 and (date('Y') % 100)!=0) or (date('Y') % 400)==0){
            $numero_dias['2'] = 29;	// altera o numero de dias de fevereiro se o ano for bissexto
        }

        return $numero_dias[$mes];
    }

    public static function GetNomeMes( $mes ) {
        $meses = array( '1' => "Janeiro", '2' => "Fevereiro", '3' => "Março",
        '4' => "Abril", '5' => "Maio", '6' => "Junho",
        '7' => "Julho", '8' => "Agosto", '9' => "Setembro",
        '10' => "Outubro", '11' => "Novembro", '12' => "Dezembro");

        if( $mes >= 01 && $mes <= 12){
            return $meses[$mes];
        }

        return "Mês deconhecido";

    }



    public static function MostreCalendario( $mes, $aDias ){
        $numero_dias = self::GetNumeroDias( $mes );	// retorna o número de dias que tem o mês desejado
        $nome_mes = self::GetNomeMes( $mes );
        $diacorrente = 0;
        $html = "";

        // função que descobre o dia da semana
        $diasemana = jddayofweek( cal_to_jd(CAL_GREGORIAN, $mes,"01",date('Y')) , 0 );

        $html .= "<table  width='100%' border = '0' cellspacing = '0' align = 'center' style='display: inline; margin-right: 25px;'>";
        $html .= "<tr>";
        $html .= "<td colspan = 7 align='center' style='background: rgb(51, 122, 183);; color: #fff;'><h3>".$nome_mes."</h3></td>";
        $html .= "</tr>";
        $html .= "<tr>";

        // função que mostra as semanas aqui
        $html .= self::MostreSemanas();	

        $html .= "</tr>";

        for( $linha = 0; $linha < 6; $linha++ ){
            $html .= "<tr bgColor='#fff'>";

            for( $coluna = 0; $coluna < 7; $coluna++ ){
                $html .= "<td width='2%' ";

                if( ($diacorrente == ( date('d') - 1) && date('m') == $mes) ){	
                    $html .= " id = 'dia_atual' ";
                }
                else{
                    if(($diacorrente + 1) <= $numero_dias ){

                        if( $coluna < $diasemana && $linha == 0){
                            $html .= " id = 'dia_branco' ";
                        }
                        else{
                            $html .= " id = 'dia_comum' ";
                        }
                    }else{
                        $html .= " colspan = 7";
                    }
                }

                $html .= " valign = 'center' ";


                /* TRECHO IMPORTANTE: A PARTIR DESTE TRECHO É MOSTRADO UM DIA DO CALENDÁRIO (MUITA ATENÇÃO NA HORA DA MANUTENÇÃO) */

                if( $diacorrente + 1 <= $numero_dias ){
                    if( $coluna < $diasemana && $linha == 0){
                        $html .= " ";
                    }else{

                        $dia = $diacorrente+1;

                        if(in_array($dia, $aDias)){
                            $html .= " align = 'center' style='background:yellow; padding: 5px'>";
                            $html .= "<a style='color: green;text-decoration:none;font-weight:bold;' href = ".$_SERVER["PHP_SELF"]."?mes=$mes&dia=".$dia.">".++$diacorrente . "</a>";
                        }else {
                            $html .= " align = 'center' style='padding: 5px'>";
                            $html .= ++$diacorrente ;
                        }
                    }
                }else {
                    break;
                }

                /* FIM DO TRECHO MUITO IMPORTANTE */



                $html .= "</td>";
            }
            $html .= "</tr>";
        }

        $html .= "</table>";

        return $html;
    }

    public static function MostreCalendarioCompleto(){
        $html .= "<table align = 'center'>";
        $cont = 1;

        for( $j = 0; $j < 4; $j++ ){
            $html .= "<tr>";
            for( $i = 0; $i < 3; $i++ ){
                $html .= "<td>";
                self::MostreCalendario( ($cont < 10 ) ? "0".$cont : $cont );

                $cont++;
                $html .= "</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</table>";
    }

}