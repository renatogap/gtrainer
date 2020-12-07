<?php

class Model_Rule_Plano extends Model_Rule_Abstract {

    private $db;

    public function __construct(){
        $this->db = Zend_Db_Table::getDefaultAdapter();
    }


    public function salvarPlanoAcademia($usuario_id){

        try{
            $this->db->beginTransaction();

            $oAuth = new Model_Rule_Autenticacao();
            $oPlano = new Model_Dao_UsuarioPlano();

            $oPlanoRow = $oPlano->createRow();

            $oPlanoRow->fk_usuario = $usuario_id;
            $oPlanoRow->fk_plano = $this->tipo_plano;

            //Default 30 dias (Plano Experimental)
            $dias = 30;

            if( $this->tipo_plano == 1 ||
                $this->tipo_plano == 2 ||
                $this->tipo_plano == 3 ||
                $this->tipo_plano == 4){
                $dias = 30;
            }


            //Pega o último plano da academia (o dt_fim maior)
            $planoAtual = $this->getPlanoAtual($usuario_id);

            //Separa a data da hora
            #list($dataFim) = explode(' ', $planoAtual->dt_fim);

            //Diferença em dias do "dt_fim" com a data atual
            $acesso = $oAuth->getDiasAcesso($usuario_id);


            if($acesso['dias'] >= 0){
                //Pega a qtd de dias restantes para vencer e soma com os dias do plano selecionado
                $dias = ($dias + $acesso['dias']);
            }

            if($planoAtual) {
                $dtFim = new DateTime($planoAtual->dt_fim);
                $dtInicioProximoPlano = $dtFim->add(new DateInterval('P1D'));
                $dtFimProximoPlano = clone $dtInicioProximoPlano;
            }else {
                //se não tiver plano Ativo, a data início do próximo plano será a CORRENTE
                $dtInicioProximoPlano = new DateTime(date('Y-m-d'));
                $dtFimProximoPlano = clone $dtInicioProximoPlano;
            }


            $dtFimProximoPlano->add(new DateInterval("P".$dias."D"));

            $oPlanoRow->dt_inicio = $dtInicioProximoPlano->format('Y-m-d');
            $oPlanoRow->dt_fim = $dtFimProximoPlano->format('Y-m-d');
            $oPlanoRow->status = 0;
            $oPlanoRow->situacao = 'AP';
            $oPlanoRow->created_at = date('Y-m-d H:i:s');

            $oPlanoRow->save();
        }catch( Exception $e ){
            $this->db->rollBack();
            throw new Exception('Falha ao salvar a renovação do plano. ');
        }
        
        $this->db->commit()->closeConnection();
        
        return array('retorno' => 'sucesso', 'msg' => 'Plano solicitado com sucesso sucesso.');
    }

    public function getPlanoAtual($usuario_id) {
        $oPlanoAcad = new Model_Dao_UsuarioPlano();

        //Pega o último plano da academia (o dt_fim maior)
        $oPlanoAcadRow = $oPlanoAcad->fetchAll($oPlanoAcad->select()->where("fk_usuario = $usuario_id")->where('status = ?',1))->current();

        return $oPlanoAcadRow;
    }

    public function verificaSolicitacaoPendente($usuario_id, $plano_id) {
        $sql = "SELECT pa.* FROM usuario_plano pa
                WHERE pa.fk_usuario = $usuario_id AND 
                      pa.fk_plano = $plano_id AND
                      pa.situacao = 'AP' /* Aguard. Pagamento */
                ORDER BY pa.dt_fim DESC
                LIMIT 1";

        $result = $this->db->fetchRow($sql);
        if(!$result){
            return false;
        }

        return true;
    }

	public function getPlanosPremium($tipo=null) {
        $sql = "SELECT p.* FROM plano p WHERE p.id <> 1";

		if($tipo != null){
			$sql .= " and p.id = $tipo";
			$result = $this->db->fetchRow($sql);
		}else {
			$result = $this->db->fetchAll($sql);
		}

        if(!$result){
            return array('retorno' => 'erro', 'msg' => 'Nenhum resultado foi encontrado.');
        }

        return array('retorno' => 'sucesso', 'result' => $result);
    }

}