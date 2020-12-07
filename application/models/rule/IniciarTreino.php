<?php

class Model_Rule_IniciarTreino extends Model_Rule_Abstract {
	
    private $db;
    private $sessao;
    
    public function __construct(){
        $this->db = Zend_Db_Table::getDefaultAdapter();
        $authSession = new Zend_Auth_Storage_Session();
        $this->sessao = $authSession->read();
    }
        
    public function iniciar() {
    	
    	if( !$this->dt_ini_treino ){
    	   return array('retorno' => 'falha', 'msg' => 'Informe a data do treino.');
    	}
    	
    
        try{
        
 	    //VERIFICAR SE EXISTE PERIODIZAÇÃO
 	    $sql = "SELECT p.* FROM `periodizacao` p WHERE p.ficha_id = {$this->ficha_id}";		    
            $aRes = $this->db->fetchAll($sql);       
            if( count($aRes) == 0 ){
            	return array('retorno' => 'falha', 'msg' => 'Antes de Iniciar o Treino, é necessário adicionar a Periodização.');
            }
            //END VERIFICAÇÃO
            
            
            //VALIDAR DATA DO TREINO
            $deviceType = Utils_File::verificaDispositivo();
            
            //Se o acesso for via 'computador' setar login e senha no Cookie
            list($vDia,$vMes,$vAno) = explode('/', $this->dt_ini_treino);
            
            if($deviceType == 'computer'){
                //list($vDia,$vMes,$vAno) = explode('/', $this->dt_ini_treino);
                #list($vAno,$vMes,$vDia) = explode('-', $this->dt_ini_treino); 
            }else{
                #list($vAno,$vMes,$vDia) = explode('-', $this->dt_ini_treino); 
            }
            
            #array('retorno' => 'falha', 'msg' => "$vDia,$vMes,$vAno");
                    
            $vSql = "SELECT f.* 
		     FROM `frequencia` f
		     WHERE f.ficha_id = {$this->ficha_id}
			  and f.ano = {$vAno} 
			  and f.mes = {$vMes} 
			  and f.dia = {$vDia}";
		    
            $vRes = $this->db->fetchRow($vSql);
            if($vRes){
            	return array('retorno' => 'falha', 'msg' => 'O aluno já treinou nesta data.');
            }
            //END VALIDA DATA DO TREINO
            
        
            //PEGAR A SESSÃO ATUAL DO TREINO
            $sql = "SELECT p.* 
		    FROM `periodizacao` p
		    WHERE p.ficha_id = {$this->ficha_id}
			  and p.dt_fim is null
		    ORDER BY p.treino ASC
		    LIMIT 1";
		    
		    
            $res = $this->db->fetchRow($sql);
            
            if($res){
                $oPeriodizacao = new Model_Dao_Periodizacao();                
                $oPeriodizacaoRow = $oPeriodizacao->find($res['id'])->current();
                
                $count = ($res['count'] + 1);
                
                $oPeriodizacaoRow->count = $count;
             
                if($count == $res['dias']){
                    $oPeriodizacaoRow->dt_fim = date('Y-m-d');
                }
                
                $oPeriodizacaoRow->save();
                
                $this->contarFrequencia($this->dt_ini_treino, $this->ficha_id);
                
                return array('retorno' => 'sucesso', 'msg' => 'Treino iniciado e contabilizado.');
            }else {
                return array('retorno' => 'falha', 'msg' => 'Esta ficha já venceu.');
            }
            
        }catch( Exception $e ){
            return array('retorno' => 'erro', 'msg' => 'Erro ao iniciar o treino. '.$e->getMessage());
        }
        
    }    
    
    public function contarFrequencia($data, $ficha_id){
        try{
                 //VALIDAR DATA DO TREINO
	         $deviceType = Utils_File::verificaDispositivo();
	
	         //Se o acesso for via 'computador' setar login e senha no Cookie
	         list($dia,$mes,$ano) = explode('/', $data);
	         if($deviceType == 'computer'){
	                 #list($dia,$mes,$ano) = explode('/', $data);
	                 #list($ano,$mes,$dia) = explode('-', $data); 
	         }else{
	                 #list($ano,$mes,$dia) = explode('-', $data); 
	         }
	            
	    	
	    	 $oFrequencia = new Model_Dao_Frequencia();
	    	 $oFrequenciaRow = $oFrequencia->createRow();
	    	 
	    	 $oFrequenciaRow->ficha_id = $ficha_id;
	    	 $oFrequenciaRow->ano = $ano;
	    	 $oFrequenciaRow->mes = $mes;
	    	 $oFrequenciaRow->dia = $dia;
	    	 
	    	 $oFrequenciaRow->save();
        }catch( Exception $e ){
            return array('retorno' => 'erro', 'msg' => 'Falha ao tentar salvar a frequencia.');
        }    	     	     	  
           	 
    	return true;
    }
    
    public function validarVencimentoFicha($ficha_id){
    	
    	$query = "SELECT p.* 
		FROM `periodizacao` p
		WHERE p.ficha_id = {$ficha_id}";
    
        $resPeriod = $this->db->fetchAll($query);
        
        if( count($resPeriod)> 0){
    
	        //PEGAR A SESSÃO ATUAL DO TREINO
	        $sql = "SELECT p.* 
			FROM `periodizacao` p
			WHERE p.ficha_id = {$ficha_id}
			      and p.dt_fim is null";
			    
			    
	        $res = $this->db->fetchAll($sql);
	        if( count($res) == 1){
	           $res = $res[0];
	           $totDias = ($res['dias'] - $res['count']);
	           
	           /*if($totDias == 0){
	               return array('retorno' => 'falha', 'msg' => "Esta ficha já expirou.");
	           }else*/
	           if($totDias == 1){
	               return array('retorno' => 'falha', 'msg' => "<b>Aviso:</b> Esta ficha expira hoje.");
	           }else
	           if($totDias <= 5 && $totDias != 0){
	               return array('retorno' => 'falha', 'msg' => "<b>Aviso:</b> Faltam {$totDias} treinos para esta ficha expirar.");
	           }
	        }else if( count($res) == 0){
	            return array('retorno' => 'falha', 'msg' => "<b>Aviso:</b> Esta ficha expirou.");
	        }
	}        
           	 
    	return  array('retorno' => 'sucesso');
    }
    
}

