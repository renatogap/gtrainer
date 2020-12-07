<?php

class Model_Rule_Grupo extends Model_Rule_Abstract
{
    protected $_sessao;
    protected $_dao;

    public function __construct()
    {
        $this->_dao = new Model_Dao_Grupo();
        $this->_sessao = $_SESSION['usuario'];
    }

    public function salvar()
    {
        if(!$this->id){
            $oGrupoRow = $this->_dao->createRow();
            $oGrupoRow->created_at = date('Y-m-d H:i:s');

            //pega o último id_grupo e seta no objeto
            $oGrupoRow->id_grupo = $this->getProximoIdGrupo();
        }else {
            $oGrupoRow = @$this->_dao->find($this->id)->current();
            $oGrupoRow->updated_at = date('Y-m-d H:i:s');
        }

        $oGrupoRow->nome = utf8_decode($this->grupo);
        $oGrupoRow->fk_personal_academia = $this->_sessao->academia_id;
        $oGrupoRow->save();
    }

    public function deletar($id)
    {
        $this->_dao->delete('id = '.$id);
    }
    
    public function getProximoIdGrupo()
    {
        $sql = $this->_dao->select()->from('grupo', ['ultimo_id' => 'max(id_grupo)'])->where("fk_personal_academia = ".$this->_sessao->academia_id);
        $max = $this->_dao->fetchRow($sql);
        return ($max->ultimo_id + 1);
    }

    public function getAll()
    {
        $query = $this->_dao->select()
                        ->where("fk_personal_academia = ".$this->_sessao->academia_id)
                        ->order("nome");

        return $this->_dao->fetchAll($query);
    }

    public function getById($id)
    {
        $sql = $this->_dao->select()->from('grupo', ['id', 'nome'])->where('id = '. $id);
        $grupo = $this->_dao->fetchRow($sql)->toArray();
        $grupo['nome'] = utf8_encode($grupo['nome']);
        return $grupo;
    }

}
