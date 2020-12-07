<?php

class Model_Rule_Periodizacao extends Model_Rule_Abstract {

    private $db;
    private $sessao;

    public function __construct(){
        $this->db = Zend_Db_Table::getDefaultAdapter();
        $this->sessao = $_SESSION['usuario'];
    }

    public function salvarPeriodizacao() {
        try{
            $oPeriodizacao = new Model_Dao_Periodizacao();
            if($this->id){
                $oPeriodizacaoRow = @$oPeriodizacao->find($this->id_treino)->current();
            }else {
                $oPeriodizacaoRow = $oPeriodizacao->createRow();
            }

            $oPeriodizacaoRow->fk_treino = $this->id_treino;
            $oPeriodizacaoRow->secao = $this->secao;
            $oPeriodizacaoRow->descricao = $this->descricao;
            $oPeriodizacaoRow->dias = $this->dias;

            $oPeriodizacaoRow->save();
        }catch( Exception $e ){
            return array('retorno' => 'erro', 'msg' => ('Falha ao salvar a periodização.'));
        }
        return array('retorno' => 'sucesso', 'msg' => ('Periodização salva com sucesso.'));
    }

    public function removerPeriodizacao() {
        try{
            $oPeriodizacao = new Model_Dao_Periodizacao();
            $oPeriodizacaoRow = @$oPeriodizacao->find($this->id)->current();
            $oPeriodizacaoRow->delete();
        }catch( Exception $e ){
            return array('retorno' => 'falha', 'msg' => ('Falha ao remover a periodização.') );
        }
        return array('retorno' => 'sucesso', 'msg' => ('Periodização removida com sucesso.') );
    }

    public function getPeriodizacao($treino) {
        $oPeriodizacao = new Model_Dao_Periodizacao();
        $query = $oPeriodizacao->select()->from("periodizacao")->where("fk_treino = ".$treino)->order("secao");
        $aPeriodizacao = $oPeriodizacao->fetchAll($query);

        $total = $aPeriodizacao->count();

        return array('results' => $aPeriodizacao, 'total' => $total);
    }

    public function getById($id_periodizacao) {
        $oPeriodizacao = new Model_Dao_Periodizacao();
        $aPeriodizacao = @$oPeriodizacao->find($id_periodizacao)->current()->toArray();
        $aPeriodizacao['secao_treino'] = $aPeriodizacao['secao'];
        return $aPeriodizacao;
    }

}
