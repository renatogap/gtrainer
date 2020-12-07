<?php

class Model_Seguranca_Auth extends Zend_Db_Table
{
	public function __construct()
	{
		error_reporting(E_ALL);
		ini_set('display_errors',1);
		ini_set('display_startup_erros',1);

		Zend_Session::start();

    	$db = Zend_Db_Table::getDefaultAdapter();
        Utils_Print::printvardie($db);

		// ações liberadas de autenticação
        $acoes_sem_autenticacao = $this->getActionsNoSession();


		if(!$this->isCheckPermissions()) {
	        $auth = Zend_Auth::getInstance();

	        if(!$auth->hasIdentity()) {

	        } else {
	        	$dtIni = date('Y-m-d');

		    	$sql = "select DATEDIFF(pu.dt_fim , '$dtIni') as dias,
		                         pu.fk_plano as plano_id
		                      from usuario_plano pu
		                      where pu.fk_usuario = ".$_SESSION['usuario']->id." and pu.`status` = 1";

	    	    //$results = $db->execfetchAll($sql);
	        	#$oAuth = new Model_Rule_Autenticacao();
                #$acesso = $oAuth->getDiasAcesso($_SESSION['usuario']->id);

	        }

		}
	}

	/*
	* verifica se a ação chamada na url não precisa de permissão
	*/
	private function isCheckPermissions()
	{
		return in_array($this->getActionCalled(), $this->getActionsNoSession());
	}

	private function getActionCalled()
	{
		list($real_path, $action) = explode('admin/', $_SERVER['REQUEST_URI']);
		return $action;
	}

	private function getActionsNoSession()
	{
		return [
            'login',
            'logout',
            'autenticacao',
            'cadastrar-novo-usuario',
            'salvar-solicitacao-plano',
            'cadastro',
            'renovar-plano'
        ];
	}
}