<?php
set_time_limit(0);
require_once 'PagSeguroLibrary/PagSeguroLibrary.php';

class Utils_PagarPlano {

    public static function pagarPlanoPremium($usuario_id, $perfil_id, $tipo_plano) {
		$oPlano = new Model_Rule_Plano();
		$plano = $oPlano->getPlanosPremium($tipo_plano);
		$plano = $plano['result'];

		$valor_plano = $plano['valor'];
        $ds_plano    = "Plano ".$plano['nome']; //($tipo_plano==6)? 'Plano Semestral' : 'Plano Anual';

        try{
            $data = self::getDadosSolicitante($perfil_id, $usuario_id);
        } catch(Exception $e){
            return array('retorno' => 'erro', 'msg' => $e->getMessage());
        }

        // Instantiate a new payment request
        $paymentRequest = new PagSeguroPaymentRequest();


        // Adiciona os itens do pedido (codigo, descrição do produto, quantidade e valor)
        $paymentRequest->addItem('1', $ds_plano, 1, $valor_plano);

        // Seta os dados do Solicitante (Nome da Academia, e-mail, null, null)
        $paymentRequest->setSender(utf8_decode($data['nome']), $data['email'], null, null);


        // Seta a moeda local
        $paymentRequest->setCurrency("BRL");

        /** Informo o Tipo de Frete:
        * 1 => Encomenda normal (PAC)
        * 2 => SEDEX
        * 3 => Tipo de frete não especificado
        */
        $paymentRequest->setShippingType(1);

        $paymentRequest->setReference($usuario_id);

        // Após o pagamento redirecionar
        $paymentRequest->setRedirectUrl($url);

        try {

                /*
                * #### Crendencials ##### 
                * Substitute the parameters below with your credentials (e-mail and token)
                * You can also get your credentails from a config file. See an example:
                * $credentials = PagSeguroConfig::getAccountCredentials();
                */
                
                $credentials = new PagSeguroAccountCredentials("renato.19gp@gmail.com", "80231F7756F54C429059FE09077EDBD9");

                /**
                * Agora vamos adicionar as credenciais informada na classe AccountCredentials
                * Com isso será gerado uma URL para o pagseguro
                *
                */
                $url = $paymentRequest->register($credentials);

                //Agora vamos redirecionar para o PagSeguro
                #header("Location: $url");
                return array('retorno' => 'sucesso', 'msg' => 'Sucesso', 'url' => $url);

        } catch (PagSeguroServiceException $e) {
                return array('retorno' => 'erro', 'msg' => $e->getMessage());
        }
    }

    public static function getDadosSolicitante($perfil_id, $usuario_id) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $sql = "SELECT a.`nome`, u.`email`
                FROM personal_academia a
                  JOIN `usuario` u on u.`id` = a.fk_usuario
                  JOIN usuario_perfil up on up.fk_usuario = u.id
                WHERE a.fk_usuario = {$usuario_id} AND up.fk_perfil = {$perfil_id}";


        try{
            $results = $db->fetchRow($sql);
        } catch(Exception $ex){
            throw new Exception($ex->getMessage());
        }

        return $results;
    }

}