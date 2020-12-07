<?php

class Model_Rule_GrupoMuscular extends Model_Rule_Abstract
{
    protected $_sessao;
    protected $_dao;

    public function __construct()
    {
        $this->_dao = new Model_Dao_GrupoMuscular();
        $this->_sessao = $_SESSION['usuario'];
    }

    public function salvar()
    {
        if(!$this->id){
            $oGrupoRow = $this->_dao->createRow();
            $oGrupoRow->created_at = date('Y-m-d H:i:s');
            
            //pega o último id_grupo e seta no objeto
            $oGrupoRow->id_grupo_muscular = $this->getProximoIdGrupoMuscular();
        }else {
            $oGrupoRow = @$this->_dao->find($this->id)->current();
            $oGrupoRow->updated_at = date('Y-m-d H:i:s');
        }

        $oGrupoRow->nome = utf8_decode($this->nome);
        $oGrupoRow->fk_personal_academia = $this->_sessao->academia_id;
        #$oGrupoRow->fk_grupo = $this->grupo;
        $oGrupoRow->save();
    }

    public function deletar($id)
    {
        $this->_dao->delete('id = '.$id);
    }
    
    public function getProximoIdGrupoMuscular()
    {
        $sql = $this->_dao->select()->from('grupo_muscular', ['ultimo_id' => 'max(id_grupo_muscular)'])->where("fk_personal_academia = ".$this->_sessao->academia_id);
        $max = $this->_dao->fetchRow($sql);
        return ($max->ultimo_id + 1);
    }

    public function getAll()
    {
        $db = Zend_db_Table::getDefaultAdapter();
        $query = $db->select()
                        ->from(['gm' => 'grupo_muscular'], ['gm.*'])
                        //->join(['g' => 'grupo'], 'g.id_grupo = gm.fk_grupo', 'g.nome as grupo')
                        ->where("gm.fk_personal_academia = ".$this->_sessao->academia_id)
                        ->order(['gm.nome']);
        #Utils_Print::printvardie($query->__toString());

        return $db->fetchAll($query);
    }

    public function getById($id)
    {
        $sql = $this->_dao->select()->from('grupo_muscular', ['*'])->where('id = '. $id);
        $grupo = $this->_dao->fetchRow($sql)->toArray();
        $grupo['nome'] = utf8_encode($grupo['nome']);
        return $grupo;
    }

}
