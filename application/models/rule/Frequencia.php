<?php

class Model_Rule_Frequencia extends Model_Rule_Abstract {

    private $_sessao;

    public function __construct()
    {
        $this->_sessao = $_SESSION['usuario'];
    }

    public function salvarFrequencia()
    {
        try {
        	$oFrequencia = new Model_Dao_Frequencia();
        	$oFrequenciaRow = $oFrequencia->createRow();

            list($ano,$mes,$dia) = explode('-', $this->dtFrequencia);

            $oFrequenciaRow->fk_personal_academia = $this->_sessao->academia_id;
            $oFrequenciaRow->fk_treino = $this->id_treino;
            $oFrequenciaRow->data = $this->dtFrequencia;
            $oFrequenciaRow->dia = $dia;
            $oFrequenciaRow->mes = $mes;
            $oFrequenciaRow->ano = $ano;
            $oFrequenciaRow->created_at = date('Y-m-d H:i:s');
            $oFrequenciaRow->save();

            $this->iniciarTreino();

            return ['retorno' => 'sucesso', 'msg' => 'Frequência registrada.'];
        } catch(Exception $ex) {
            //throw new Exception($ex->getMessage());
            return ['retorno' => 'falha', 'msg' => $ex->getMessage()];
        }
    }


    public function iniciarTreino() {

        //PEGAR A SESSÃO ATUAL DO TREINO
        $oTreino = new Model_Rule_Treinos();

        $res = $oTreino->validade($this->id_treino);
        
        #Utils_Print::printvardie($res);

        if(!$res){
            throw new Exception('Esta ficha já venceu.');
        }

        $oPeriodizacao = new Model_Dao_Periodizacao();
        $oPeriodizacaoRow = @$oPeriodizacao->find($res['id'])->current();

        $count = ($res['count'] + 1);

        $oPeriodizacaoRow->count = $count;

        if($count == $res['dias']){
            $oPeriodizacaoRow->dt_fim = date('Y-m-d');
        }

        $oPeriodizacaoRow->save();
    }

    public function validacao()
    {
        if(!$this->dtFrequencia){
            return ['retorno' => 'falha', 'msg' => 'Informe a data do treino.'];
        }

        $oPeriodizacao = new Model_Dao_Periodizacao();

        //VERIFICAR SE EXISTE PERIODIZAÇÃO
        $sql = $oPeriodizacao->select()->where('fk_treino = ?', $this->id_treino);
        $periodizacao = $oPeriodizacao->fetchAll($sql);

        if(count($periodizacao) == 0){
            return ['retorno'=>'falha', 'msg'=>'Antes de registrar a frequência, é necessário adicionar a Periodização.'];
        }

        #Utils_Print::printvardie($this->dtFrequencia);

        //VERIFICAR DATA DUPLICADA
        list($vAno, $vMes, $vDia) = @explode('-', $this->dtFrequencia);

        $oFreq = new Model_Dao_Frequencia();

        $vRes = $oFreq->fetchRow($oFreq->select()
                ->where('fk_treino = ?', $this->id_treino)
                ->where('ano = ?', $vAno)
                ->where('mes = ?', $vMes)
                ->where('dia = ?', $vDia));

        if($vRes){
            return ['retorno' => 'falha', 'msg' => 'Já foi registrado um treinou nesta data.'];
        }

        return ['retorno' => 'ok'];
    }
}

