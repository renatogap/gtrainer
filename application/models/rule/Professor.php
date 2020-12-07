<?php

class Model_Rule_Professor extends Model_Rule_Abstract {
	
    private $db;
    private $sessao;

    public function __construct(){
        $this->db = Zend_Db_Table::getDefaultAdapter();
        
        $authSession = new Zend_Auth_Storage_Session();
        $this->sessao = $authSession->read();
    }
    
    public function salvarProfessor($resUpload=null) {
        $oProfessorBD = new Model_Dao_Professor();
        $oProfessorRow = $oProfessorBD->createRow();
        
        $oProfessorRow->academia_id = $this->sessao->academia_id;
        
        $deviceType = Utils_File::verificaDispositivo();

	if($deviceType == 'phone'){	        
	     $oProfessorRow->nm_professor = utf8_decode($this->nm_professor);
        }else {
	     $oProfessorRow->nm_professor = $this->nm_professor;
        }
        
        $oProfessorRow->email = $this->email;
        
        if($resUpload != null) {
            $oProfessorRow->url = $resUpload['url'];
            $oProfessorRow->thumbnail = $resUpload['thumbnail'];
        }
        
        try{
            $oProfessorRow->save();
        }catch( Exception $e ){
            throw new Exception('Falha ao salvar o professor. '.$e->getMessage());
        }
        return array('retorno' => 'sucesso', 'msg' => 'Professor cadastrado com sucesso.');
    }
    
    public function alterarProfessor($resUploadAlterar=null) {
        $oProfessorBD = new Model_Dao_Professor();
        $oProfessorRow = $oProfessorBD->find($this->id)->current();
        
        $oProfessorRow->academia_id = $this->sessao->academia_id;
        $oProfessorRow->nm_professor = $this->nm_professor;
        $oProfessorRow->email = $this->email;
        
        if($resUploadAlterar != null) {
            $oProfessorRow->url = $resUploadAlterar['url'];
            $oProfessorRow->thumbnail = $resUploadAlterar['thumbnail'];
        }
        
        try{
            $oProfessorRow->save();
            #Utils_Print::printvardie($this->_params);
        }catch( Exception $e ){
            return array('retorno' => 'falha', 'msg' => 'Falha ao alterar o professor.');
        }
        return array('retorno' => 'sucesso', 'msg' => 'Professor alterado com sucesso.');
    }
    
    public function removerProfessor() {
        $resObjProfessor = null;
        
        try{
            $oProfessorBD = new Model_Dao_Professor();
            $oProfessorRow = $oProfessorBD->find($this->id)->current();
            
            if($oProfessorRow->url){
                $resObjProfessor['url'] = $oProfessorRow->url;
                $resObjProfessor['thumbnail'] = $oProfessorRow->thumbnail;
            }
            
            
            /*if(count($results) > 0 ){
                throw new Exception('O professor '.$oProfessorRow->nm_professor.' nao pode ser removido. Ele ainda esta na programacao de aulas.');
            }*/
            
            
            $oProfessorRow->delete();
        }catch( Exception $e ){
            throw new Exception('Falha ao remover o professor. '.$e->getMessage());
        }
        
        #return array('retorno' => 'sucesso', 'msg' => 'Professor removido com sucesso.');
        return $resObjProfessor;
    }   
        
    public function getProfessor() {
        $oProfessorBD = new Model_Dao_Professor();
        $oProfessorRow = $oProfessorBD->find($this->id)->current();
        $total = count($oProfessorRow);
        return array('results' => $oProfessorRow, 'total' => $total);
    }
    
    public function getProfessores() {
        $query = $this->db->select()
                    ->from(array('p' => 'professor'), 'p.*')
                        ->where("p.academia_id = {$this->sessao->academia_id}")
                            ->order(array('p.nm_professor'));
        
        $aProfessores = $this->db->fetchAll($query);
        #Utils_Print::printvardie($aAulas);
        $total = count($aProfessores);
        return array('results' => $aProfessores, 'total' => $total);
    }
}