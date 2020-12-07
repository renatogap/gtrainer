<?php

class Model_Rule_Exercicio extends Model_Rule_Abstract {

    private $db;
    private $sessao;

    public function __construct(){
        $this->db = Zend_Db_Table::getDefaultAdapter();
        $this->sessao = $_SESSION['usuario'];
    }

    public function salvarExercicio() {
    	if(!$this->nome){
    	   return array('retorno' => 'falha', 'msg' => 'Informe o nome do exercício.');
    	}

        if(!$this->grupo){
           return array('retorno' => 'falha', 'msg' => 'Selecione um grupo.');
        }


        try{
            $oExercicio = new Model_Dao_Exercicio();

             if(!$this->id){
                $oExercicioRow = $oExercicio->createRow();
                $oExercicioRow->created_at = date('Y-m-d H:i:s');
                $oExercicioRow->fk_personal_academia = $this->sessao->academia_id;
             }else {
                $oExercicioRow = @$oExercicio->find($this->id)->current();
                $oExercicioRow->updated_at = date('Y-m-d H:i:s');
             }

            $oExercicioRow->nome = utf8_decode($this->nome);
            $oExercicioRow->fk_grupo = $this->grupo;

            #if(isset($this->grupo_muscular) && !empty($this->grupo_muscular)){
            $oExercicioRow->fk_grupo_muscular = $this->grupo_muscular;
            #}

            if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $oExercicioRow->foto = $this->foto;
            }

            $oExercicioRow->save();

            return array('retorno' => 'sucesso', 'msg' => 'Exercício cadastrado com sucesso.');
        }catch( Exception $e ){
            return array('retorno' => 'falha', 'msg' => 'Falha ao salvar este exercício.'.$e->getMessage());
        }
    }


    public function removerExercicio()
    {
        try{
            $oExercicio = new Model_Dao_Exercicio();
            $oExercicioRow = @$oExercicio->find($this->id)->current();
            $oExercicioRow->delete();

            return array('retorno' => 'sucesso', 'msg' => 'Exercício removido com sucesso. ');
        }catch(Exception $e ){
            return array('retorno' => 'falha', 'msg' => 'Falha ao remover este exercício. ');
        }
    }

    public function getById($id)
    {
        $oExercicio = new Model_Dao_Exercicio();
        $oExercicioRow = @$oExercicio->find($id)->current();
        return $oExercicioRow;
    }

    public function getAll()
    {  
        $query = $this->db->select()->distinct()
                    ->from(["e" => "exercicio"], ["e.id", "e.nome", "e.type", "e.filename"])
                       ->joinLeft(["g" => "grupo"], "g.id_grupo = e.fk_grupo", "g.nome as grupo")
                       ->joinLeft(["gm" => "grupo_muscular"], "gm.id_grupo_muscular = e.fk_grupo_muscular", "gm.nome as grupo_muscular")
                    ->where("e.fk_personal_academia = ".$this->sessao->academia_id)
                    ->order(["g.nome", "e.nome", "gm.nome"]);
                    
        #Utils_Print::printvardie($this->db->fetchAll($query));         

        return $this->db->fetchAll($query);
    }
    
    public function getByGrupo($grupo)
    {  
        $query = $this->db->select()
                    ->from(["e" => "exercicio"], ["e.id", "e.nome", "e.type", "e.filename"])
                       ->joinLeft(["g" => "grupo"], "g.id_grupo = e.fk_grupo", "g.nome as grupo")
                       ->joinLeft(["gm" => "grupo_muscular"], "gm.id_grupo_muscular = e.fk_grupo_muscular", "gm.nome as grupo_muscular")
                    ->where("e.fk_personal_academia = ".$this->sessao->academia_id)
                    ->where("g.nome like '$grupo'")
                    ->order(["g.nome", "e.nome", "gm.nome"]);
                    
        #Utils_Print::printvardie($query->__toString());            

        return $this->db->fetchAll($query);
    }
}