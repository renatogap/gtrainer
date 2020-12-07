<?php

class AlunoController extends Zend_Controller_Action
{
	public function init()
    {
    	$auth = Zend_Auth::getInstance();
        if(!$auth->hasIdentity()) {
        	return $this->_helper->redirector('login', 'admin');
        }
    }
	public function indexAction()
	{
		$this->_helper->layout->setLayout('aluno');
		#session_start();
		#Utils_Print::printvardie($_SESSION['usuario']);
	}

	public function treinosAction()
    {
    	$this->_helper->layout->setLayout('aluno');

        $treinos = new Model_Rule_Treinos();

        $this->view->treinos = $treinos->getAllByIdAluno($_SESSION['usuario']->aluno_id);

        $this->_helper->viewRenderer->setNoRender(true);
        echo $this->view->render('aluno/treino/index.phtml');
    }

    public function editExercicioAction()
    {
    	$this->_helper->layout->setLayout('aluno');
        $this->_helper->viewRenderer->setNoRender(true);

        $this->view->id = $this->_request->getParam('id');

        $oTreinoItens = new Model_Rule_TreinoItens();
        $this->view->exercicio = $oTreinoItens->getById($this->view->id);

        $this->view->id_treino = $this->view->exercicio['fk_treino'];

        echo $this->view->render('aluno/treino/edit-exercicio.phtml');
    }

    public function salvarCargaAction()
    {
    	$db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        try {
            $oTreinoItens = new Model_Rule_TreinoItens();
            $oTreinoItens->setParams($this->_request->getParams());
            $oTreinoItens->salvarCarga();

            $db->commit()->closeConnection();

            $this->_helper->json->sendJson(['retorno'=>'sucesso', 'msg'=>'Carga salva com sucesso.']);
        }
        catch(Exception $ex) {
            $db->rollBack();
            $this->_helper->json->sendJson(['retorno' => 'erro', 'msg' => 'Falha ao tentar adicionar a nova carga. Por favor, tente novamente mais tarde. '.$ex->getMessage() ]);
        }
    }

    public function treinoViewAction()
    {
    	$this->_helper->layout->setLayout('aluno');

    	$this->_helper->viewRenderer->setNoRender(true);

        $oAluno = new Model_Rule_Aluno();
        $oTreino = new Model_Rule_Treinos();
        $oItensTreino = new Model_Rule_TreinoItens();
        $oFrequencia = new Model_Dao_Frequencia();

        $id_treino = $this->_request->getParam('id');

        $this->view->treino = $oTreino->getById($id_treino);

        $this->view->validadeTreino = $oTreino->validade($id_treino);

        $aAluno = $oAluno->getAluno($this->view->treino['fk_aluno']);

        $this->view->treino['aluno'] = $aAluno['nome'];
        $id_usuario_aluno = $aAluno['fk_usuario'];

        $treinoItens = $oItensTreino->getByTreinoId($id_treino);

        $this->view->exerciciosDoTreino = [];

        foreach ($treinoItens as $key => $exercicio) {
            $this->view->exerciciosDoTreino[$exercicio['treino']][] = $exercicio;
        }

        $frequencia = $oFrequencia->fetchAll('fk_treino = '.$id_treino);

        $this->view->calendario = [];

        if($frequencia){
            foreach ($frequencia as $key => $value) {
                $this->view->calendario[$value['mes']][] = $value['dia'];
            }
        }

        $this->view->id_treino = $id_treino;

        $oPeriodizacao = new Model_Rule_Periodizacao();
        $this->view->aPeriodizacao = $oPeriodizacao->getPeriodizacao($id_treino);

        #Utils_Print::printvardie($_SESSION['usuario']);

        echo $this->view->render('aluno/treino/treino-view.phtml');
    }

    public function salvarFrequenciaAction()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        try{
            $oFrequencia = new Model_Rule_Frequencia();
            $oFrequencia->setParams($this->_request->getParams());

            $resValida = $oFrequencia->validacao();

            if($resValida['retorno'] == 'falha') {
                $this->_helper->json->sendJson($resValida);
                exit;
            }

            $result = $oFrequencia->salvarFrequencia();

            ($result['retorno'] != 'sucesso')? $db->rollBack() : $db->commit()->closeConnection();

            $this->_helper->json->sendJson($result);
        } catch(Exception $ex) {
            $db->rollBack();
            $this->_helper->json->sendJson(['retorno' => 'erro', 'msg' => 'Falha ao tentar salvar a frequência. Por favor, tente novamente mais tarde.'. $ex->getMessage()]);
        }
    }

	public function verFotoUsuarioAction()
    {
        $dtCurrent = date('Y-m-d');
        header("Cache-Control: max-age=2592000, pre-check=2592000"); //30days
        header("Pragma: public");
        header("Expires: Sat, ".date('d M Y', strtotime("+30 days",strtotime($dtCurrent)))." GMT"); // Date in the past
        header("content-type: image/jpg");
        $id = $this->_request->getParam('id');
        $oUsuario = new Model_Dao_Usuario();
        $usuario = $oUsuario->fetchRow('id = '. $id);
        $binario = ($usuario->foto);
        die($binario);
    }

    public function verFotoExercicioAction()
    {
        header("Cache-Control: max-age=2592000, pre-check=2592000"); //30days
        header("Pragma: public");
        header("Expires: Sat, 26 Dez 2020 05:00:00 GMT"); // Date in the past
        header("content-type: image/jpg"); 
        $id = $this->_request->getParam('id');
        $oExercicio = new Model_Dao_Exercicio();
        $exercicio = $oExercicio->fetchRow('id = '.$id);
        $binario = ($exercicio->foto);
        die($binario);
    }

    public function logoutAction()
    {
        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();

        setcookie('login', null, -1, '/');
        setcookie('senha', null, -1, '/');

        return $this->_helper->redirector('login', 'admin');
    }
}