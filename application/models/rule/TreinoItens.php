<?php

class Model_Rule_TreinoItens extends Model_Rule_Abstract
{
    protected $_error = [];

    public function getById($id)
    {
        $db = Zend_Db_Table::getDefaultAdapter();

        $sql = $db->select()
                ->from(['ti' => 'treino_itens'], 'ti.*')
                ->join(['e' => 'exercicio'], 'e.id = ti.fk_exercicio', ['exercicio'=>'e.nome'])
                ->join(['g' => 'grupo'], 'g.id = e.fk_grupo', ['grupo' => 'g.nome'])
                ->where('ti.id = ?', $id)
                ->order('ti.treino');

        return $db->fetchRow($sql);
    }

    public function getByTreinoId($treino_id)
    {
    	$db = Zend_Db_Table::getDefaultAdapter();

    	$sql = $db->select()
                ->from(['ti' => 'treino_itens'], 'ti.*')
                ->join(['e' => 'exercicio'], 'e.id = ti.fk_exercicio', ['exercicio'=>'e.nome', 'e.filename'])
                ->join(['g' => 'grupo'], 'g.id = e.fk_grupo', ['grupo' => 'g.nome'])
                ->where('ti.fk_treino = ?', $treino_id)
                ->order('ti.id');

        return $db->fetchAll($sql);
    }

    public function salvarExercicioTreino()
    {
        $oTreinoItens = new Model_Dao_TreinoItens();
        if($this->id) {
            $oTreinoItensRow = @$oTreinoItens->find($this->id)->current();
            $oTreinoItensRow->updated_at = date('Y-m-d H:i:s');
        }else {
            $oTreinoItensRow = $oTreinoItens->createRow();
            $oTreinoItensRow->created_at = date('Y-m-d H:i:s');
        }

        $oTreinoItensRow->treino = $this->treino;
        $oTreinoItensRow->fk_treino = $this->idTreino;
        $oTreinoItensRow->fk_exercicio = $this->exercicio;
        $oTreinoItensRow->series = $this->series;
        $oTreinoItensRow->repeticoes_tempo = $this->repeticoesTempo;
        $oTreinoItensRow->tipo_repeticoes_tempo = $this->tipoRepeticoesTempo;
        $oTreinoItensRow->descanso = $this->descanso;
        $oTreinoItensRow->carga = ($this->carga)? utf8_decode($this->carga) : null;
        $oTreinoItensRow->detalhes = ($this->detalhes)? utf8_decode($this->detalhes) : null;
        $oTreinoItensRow->save();
    }

    public function salvarCarga()
    {
        $oTreinoItens = new Model_Dao_TreinoItens();
        $oTreinoItensRow = @$oTreinoItens->find($this->id)->current();

        $oTreinoItensRow->carga = utf8_decode($this->carga);

        $oTreinoItensRow->updated_at = date('Y-m-d H:i:s');
        $oTreinoItensRow->save();
    }

    public function remover()
    {
        $oTreinoItem = new Model_Dao_TreinoItens();
        $oTreinoItem->delete('id = '.$this->id);
    }

}