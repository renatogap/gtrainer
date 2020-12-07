<?php

class Model_Rule_Musculo extends Model_Rule_Abstract {
	
    private $db;
    private $sessao;
    
    public function __construct(){
        $this->db = Zend_Db_Table::getDefaultAdapter();
        $authSession = new Zend_Auth_Storage_Session();
        $this->sessao = $authSession->read();
    }
        
    public function salvarMusculo() {
        try{
            $oMusculo = new Model_Dao_Musculo();
            $oMusculoRow = $oMusculo->createRow();
            
            $deviceType = Utils_File::verificaDispositivo();

	    if($deviceType == 'phone'){	        
	        $oMusculoRow->musculo = utf8_decode($this->ds_musculo);
            }else {
	        $oMusculoRow->musculo = utf8_decode($this->ds_musculo);
            }
            
            $oMusculoRow->academia_id = $this->sessao->academia_id;
            $oMusculoRow->save();
        }catch( Exception $e ){
            return array('retorno' => 'falha', 'msg' => ('Falha ao salvar este músculo.'));
        }
        return array('retorno' => 'sucesso', 'msg' => ('Músculo adicionada com sucesso.'));
    }    
    
    public function alterarMusculo() {
        
        try{
            $oMusculo = new Model_Dao_Musculo();
            $oMusculoRow = $oMusculo->find($this->id)->current();
            
            $deviceType = Utils_File::verificaDispositivo();

	    if($deviceType == 'phone'){	        
	        $oMusculoRow->musculo = utf8_decode($this->ds_musculo);
            }else {
	        $oMusculoRow->musculo = utf8_decode($this->ds_musculo);
            }
            
            
            $oMusculoRow->academia_id = $this->sessao->academia_id;
            $oMusculoRow->save();
            #Utils_Print::printvardie($this->_params);
        }catch( Exception $e ){
            return array('retorno' => 'falha', 'msg' => 'Falha ao alterar este músculo.');
        }
        return array('retorno' => 'sucesso', 'msg' => 'Músculo alterado com sucesso.');
    }
    
    public function removerMusculo() {
        try{
            $oMusculo = new Model_Dao_Musculo();
            $oMusculoRow = $oMusculo->find($this->id)->current();
            $oMusculoRow->delete();
        }catch( Exception $e ){
            return array('retorno' => 'falha', 'msg' => ('Falha ao remover este músculo.') );
        }
        return array('retorno' => 'sucesso', 'msg' => ('Músculo removido com sucesso.') );
    }   
    
    public function getMusculo($id) {
        $oMusculo = new Model_Dao_Musculo();
        $oMusculoRow = $oMusculo->find($id)->current();
        return $oMusculoRow;
    }
    
    public function getMusculos() {
        $oMusculo = new Model_Dao_Musculo();
        $query = $oMusculo->select()->from("musculo")->where("academia_id = ".$this->sessao->academia_id)  ->order("musculo");
        $aMusculos = $oMusculo->fetchAll($query);
        
        $total = $aMusculos->count();
        
        return array('results' => $aMusculos, 'total' => $total);
    }
    
}
