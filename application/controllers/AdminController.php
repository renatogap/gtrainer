<?php
#ini_set('display_errors',1);
#ini_set('display_startup_erros',1);
#error_reporting(E_ALL);

class AdminController extends Zend_Controller_Action
{
    private $sessao;

    public function init()
    {
        #unset($_COOKIE);
        #unset($_COOKIE['login']);
        #unset($_COOKIE['senha']);

        $front = Zend_Controller_Front::getInstance();
        $request    = $front->getRequest();
        $controller = $request->getControllerName();
        $action     = $request->getActionName();

        // ações liberadas de autenticação
        $acoes_sem_autenticacao = [
            'login',
            'logout',
            'autenticacao',
            'cadastrar-novo-usuario',
            'salvar-solicitacao-plano',
            'cadastro',
            'renovar-plano',
            'solicitar-nova-senha',
        ];

        $auth = Zend_Auth::getInstance();
        $isLogado = $auth->hasIdentity();

        // se a ação atual necessita de autenticação
        if(!in_array($action, $acoes_sem_autenticacao)) {

            // se o usuário não estiver logado
            if(!$isLogado){
                if(isset($_COOKIE['login']) && isset($_COOKIE['senha']) && isset($_COOKIE['usuario_id'])){
                    $oUsuario = new Model_Rule_Autenticacao();
                    $params['login'] = $_COOKIE['login'];
                    $params['senha'] = base64_decode($_COOKIE['senha']);
                    $params['sessao_do_cookie'] = true;

                    $oUsuario->setParams($params);
                    $results = $oUsuario->autenticaUsuario();

                    if($results['retorno'] == 'erro'){
                        return $this->_helper->redirector('login', 'admin');
                    }

                    $oAuth = new Model_Rule_Autenticacao();
                    $acesso = $oAuth->getDiasAcesso($_COOKIE['usuario_id']);

                    if(!$acesso || $acesso['dias'] < 0){
                        return $this->_helper->redirector('renovar-plano', 'admin');
                    }

                }else {
                    return $this->_helper->redirector('login', 'admin');
                }
            }

            // se o usuário estiver logado
            else {
                $this->view->sessao = $_SESSION['usuario'];
                $this->sessao = $_SESSION['usuario'];

                if($this->view->sessao->perfil_id != 5){
                    $oAuth = new Model_Rule_Autenticacao();
                    $acesso = $oAuth->getDiasAcesso($this->sessao->id);
    
                    if(!$acesso || $acesso['dias'] < 0){
                        return $this->_helper->redirector('renovar-plano', 'admin');
                    }
                }
            }
        }
    }

    public function loginAction()
    {
        //Se o usu�rio j� estiver logado, redirecionar para o home
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            #if($_SESSION['usuario']->perfil_id != 5){
            return $this->_helper->redirector('index', 'admin');
            #}
        }

        $this->_helper->layout->setLayout('home');
    }

    public function autenticacaoAction()
    {
        $oUsuario = new Model_Rule_Autenticacao();
        $oUsuario->setParams($this->_request->getParams());
        $results = $oUsuario->autenticaUsuario();
        $this->_helper->json->sendJson($results);
    }

    public function logoutAction()
    {
        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();

        setcookie('login', null, -1, '/');
        setcookie('senha', null, -1, '/');

        return $this->_helper->redirector('login', 'admin');
    }

    public function indexAction()
    {
        $this->_helper->layout->setLayout('admin');
        $auth = new Zend_Auth_Storage_Session();
        $this->view->sessao = $auth->read();
    }

    public function homeAction()
    {
        $this->_helper->layout->disableLayout();
    }

    public function cadastrarNovoUsuarioAction()
    {
        $oUsuario = new Model_Rule_Usuario();

        $aParams = $this->_request->getParams();
        $aParams['rdTipo'] = 2; //Personal

        $oUsuario->setParams($aParams);

        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        $resUsuario = $oUsuario->cadastrarUsuario();
        if($resUsuario['retorno'] != 'sucesso'){
            $db->rollBack();
            $this->_helper->json->sendJson($resUsuario);
        }

        $db->commit()->closeConnection();

        $this->_helper->json->sendJson($resUsuario);
    }

    public function salvarSolicitacaoPlanoAction()
    {
        try{
            $oSolicitacao = new Model_Rule_Plano();
            $aParams = $this->_request->getParams();
            $oSolicitacao->setParams($aParams);

            if( !$oSolicitacao->verificaSolicitacaoPendente($aParams['usuario_id'], $aParams['tipo_plano']) ) {

                //Salvar Solicita��o da Renova��o
                $data = $oSolicitacao->salvarPlanoAcademia($aParams['usuario_id']);

            }

            $result = Utils_PagarPlano::pagarPlanoPremium($aParams['usuario_id'], $aParams['perfil_id'], $aParams['tipo_plano']);
        } catch(Exception $ex) {
            $this->_helper->json->sendJson(array('success' => false, 'msg' => $ex->getMessage()));
        }

        $this->_helper->json->sendJson($result);
    }


    public function renovarPlanoAction()
    {
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            $id = $_SESSION['usuario']->id;
            $this->view->historicoPlanosUsuario = Model_Dao_UsuarioPlano::historicoDePlanosDoUsuario($id);

            $oPlano = new Model_Rule_Plano();
            $this->view->aPlanos = $oPlano->getPlanosPremium()['result'];
        }
    }

    public function aguardarPagamentoAction() {
        $this->_helper->layout->setLayout('login');
    }

    public function modalConfiguracoesAction(){
        $this->_helper->layout->disableLayout();
    }

    public function modalTrocarSenhaAction(){
        $this->_helper->layout->disableLayout();
    }

    public function trocarSenhaAction()
    {

    }

    public function salvarTrocaSenhaAction()
    {
        $oUsuario = new Model_Rule_Usuario();
        $oUsuario->setParams($this->_request->getParams());
        $resUsuario = $oUsuario->alterarSenha();
        $this->_helper->json->sendJson($resUsuario);
    }

    public function alunoAction()
    {
        $oAluno = new Model_Rule_Aluno();
        $this->view->alunos = $oAluno->getAlunos();

        $this->view->habilitarBtnCadastro = $oAluno->validaLimiteDeAlunos();

        $this->_helper->layout->setLayout('admin');
        $this->_helper->viewRenderer->setNoRender(true);
        echo $this->view->render('admin/aluno/aluno.phtml');
    }

    public function alunoCreateAction()
    {
        $oAluno = new Model_Rule_Aluno();
        $this->view->habilitarBtnSalvar = $oAluno->validaLimiteDeAlunos();

        $this->view->title = "Cadastrar aluno";

        $this->_helper->layout->setLayout('admin');
        $this->_helper->viewRenderer->setNoRender(true);
        echo $this->view->render('admin/aluno/create.phtml');
    }

    public function alunoEditAction()
    {
        $id = $this->_request->getParam('id');

        $oAluno = new Model_Rule_Aluno();
        $this->view->aluno = $oAluno->getAluno($id);
        
        #Utils_Print::printvardie($this->view->aluno);

        $this->view->habilitarBtnSalvar = $oAluno->validaLimiteDeAlunos();

        $this->view->title = "Editar aluno";

        $this->_helper->layout->setLayout('admin');
        $this->_helper->viewRenderer->setNoRender(true);
        echo $this->view->render('admin/aluno/create.phtml');
    }

    public function getBinarioImage()
    {
        $binario = null;
        $maxSizeImage = 5;

        $file = $_FILES['foto'];

        // Verificando se selecionou alguma imagem
        if (isset($file) && $file['error'] == 0){

            // Constantes
            define('TAMANHO_MAXIMO', ($maxSizeImage * 1024 * 1024));

            // Validar formato do arquivo
            if(!preg_match('/^image\/(pjpeg|jpeg|jpg|png|gif|bmp)$/', $file['type'])){
                throw('Formato do arquivo inválido.');
            }

            // Tamanho
            if ($file['size'] > TAMANHO_MAXIMO){
                throw('A imagem deve possuir no máximo 5 MB');
            }

            // Transformando foto em dados (binário)
            $binario = (file_get_contents($file['tmp_name']));
        }

        return $binario;
    }

    public function validacao($aParams)
    {
        //Validar nome
        if( !$aParams['nm_aluno'] ){
            $this->_helper->json->sendJson(['retorno' => 'falha', 'msg' => 'Por favor, informe o nome do aluno']);
        }

        //Validar email
        if(!isset($aParams['email']) || empty($aParams['email'])){
            $this->_helper->json->sendJson(['retorno' => 'falha', 'msg' => 'Por favor, informe o email do aluno']);
        }

        //Validar telfone
        if( !$aParams['telefone'] ){
            $this->_helper->json->sendJson(['retorno' => 'falha', 'msg' => 'Por favor, informe o telefone do aluno']);
        }

        if(empty($aParams['id'])) {
            //Pega o Adapter do Zend e armazena � vari�vel $db
            $db = Zend_Db_Table::getDefaultAdapter();

            $sql = "SELECT a.* 
                    FROM aluno a
                    JOIN usuario u on u.id = a.fk_usuario
                    WHERE u.`email` LIKE '".$aParams['email']."' AND a.`fk_personal_academia` = {$this->sessao->academia_id}";

            #Utils_Print::printvardie($sql);
            $alunoExisteNestaAcademia = $db->fetchRow($sql);

            if(!empty($alunoExisteNestaAcademia) && $alunoExisteNestaAcademia['email'] != $this->sessao->email) {
                $this->_helper->json->sendJson(['retorno' => 'cancelar', 'msg' => 'Este aluno já foi cadastrado por você.']);
            }
        }

        //Instanciar a classe usu�rio
        $oUsuarioDao = new Model_Dao_Usuario(); 

        //Verifica se o usuario já possui cadastro
        $oUsuarioExiste = $oUsuarioDao->fetchAll($oUsuarioDao->select()->where("email like '".$aParams['email']."%'"))->current();

        //Se o usuário não estiver cadastrado, ativar a validação de senha provis�ria
        if( !$oUsuarioExiste ) {
            if(empty($aParams['id']) &&  !$this->_request->getParam('senhaCad') ){
                $this->_helper->json->sendJson(['retorno' => 'falha', 'msg' => 'Por favor, informe uma senha provisória para o aluno']);
            }
        }
    }

    public function salvarAlunoAction()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        //error_reporting(E_ALL);

        $aParams = $this->_request->getParams();

        $this->validacao($aParams);

        $oAluno = new Model_Rule_Aluno();

        $aParams['foto'] = $this->getBinarioImage(); //seta a imagem binária
        
        if(isset($_FILES['foto']['type'])){
            $aParams['type'] = $_FILES['foto']['type'];
            $aParams['filename'] = $_FILES['foto']['name'];
        }

        $oAluno->setParams($aParams);

        $results = $oAluno->salvarAluno();

        if($results['retorno'] == 'sucesso'){
            $db->commit()->closeConnection();
        }else {
            $db->rollBack();
        }

        $this->_helper->json->sendJson($results);
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

    public function verificaUsuarioExistenteAction() {
        //Pega o Adapter do Zend e armazena � vari�vel $db
        $db = Zend_Db_Table::getDefaultAdapter();
        
        $sql = "select a.nome, u.email
                from aluno a
                join usuario u on u.id = a.fk_usuario
                where u.`email` like '".$this->_request->getParam('email')."' 
                          and a.`fk_personal_academia` = {$_SESSION['usuario']->academia_id}";

        
        $alunoExisteNestaAcademia = $db->fetchRow($sql);
        
        #Utils_Print::printvardie($alunoExisteNestaAcademia);

        if(!$this->_request->getParam('id')){
            if($alunoExisteNestaAcademia) {
                $this->_helper->json->sendJson(array('retorno' => 'cancelar', 'msg' => 'Este aluno já foi cadastrado por você.', 'dados' => $alunoExisteNestaAcademia));
            }
        

            //Instanciar a classe usu�rio
            $oUsuarioDao = new Model_Dao_Usuario(); 
    
            //Verifica se o usuario j� possui cadastro
            $oUsuarioExiste = $oUsuarioDao->fetchAll($oUsuarioDao->select()->where("email like '".$this->_request->getParam('email')."%'"))->current();
    
            if( $oUsuarioExiste && $oUsuarioExiste->email != $this->sessao->email ) {
                $this->_helper->json->sendJson(array('retorno' => 'cancelar', 'msg' => 'Este aluno já possui um cadastro no sistema. Não é necessário informar uma senha, ao menos que você queira.'));
            }
        }

        $this->_helper->json->sendJson(array('retorno' => 'success'));
    }


    public function removerAlunoAction() {
        $oAluno = new Model_Rule_Aluno();
        $results = $oAluno->removerAluno($this->_request->getParam('id'));
        $this->_helper->json->sendJson($results);
    }


     public function exercicioAction()
    {
        $this->_helper->layout->setLayout('admin');
        $this->_helper->viewRenderer->setNoRender(true);

        $oGrupo = new Model_Rule_Grupo();
        $this->view->grupo = $oGrupo->getAll();

        $oExercicio = new Model_Rule_Exercicio();
        $this->view->results = $oExercicio->getAll();

        echo $this->view->render("admin/exercicio/index.phtml");
    }

    public function exercicioCreateAction()
    {
        $this->_helper->layout->setLayout('admin');
        $this->_helper->viewRenderer->setNoRender(true);

        $oGrupo                         = new Model_Rule_Grupo();
        $oGrupoMuscular                 = new Model_Rule_GrupoMuscular();
        $this->view->grupo              = $oGrupo->getAll();
        $this->view->grupos_musculares  = $oGrupoMuscular->getAll();

        $this->view->titulo = 'Cadastrar exercício';

        if($this->_request->getParam('id')){
            $oExercicio = new Model_Rule_Exercicio();
            $this->view->exercicio = $oExercicio->getById($this->_request->getParam('id'));
            $this->view->titulo = 'Editar exercícios';
        }

        echo $this->view->render("admin/exercicio/create.phtml");
    }

    public function exercicioDeleteAction() {
        $oExercicio = new Model_Rule_Exercicio();
        $oExercicio->setParams($this->_request->getParams());
        $results = $oExercicio->removerExercicio();
        $this->_helper->json->sendJson($results);
    }

    public function salvarExercicioAction()
    {
        $aParams = $this->_request->getParams();

        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        try{
            $oExercicio = new Model_Rule_Exercicio();
            $aParams['foto'] = $this->getBinarioImage(); //seta a imagem binária
            $oExercicio->setParams($aParams);
            $results = $oExercicio->salvarExercicio();

            if($results['retorno'] == 'sucesso'){
                $db->commit()->closeConnection();
            }else {
                $db->rollBack();
            }

            $this->_helper->json->sendJson($results);
        } catch(Exception $ex) {
            $db->rollBack();
            $this->_helper->json->sendJson(['retorno' => 'erro', 'msg' => $ex->getMessage()]);
        }
    }

    //Grupo Muscular
    public function grupoMuscularAction()
    {
        $oGrupo = new Model_Rule_Grupo();
        $this->view->grupo = $oGrupo->getAll();

        $oGrupoMuscular = new Model_Rule_GrupoMuscular();
        $this->view->results = $oGrupoMuscular->getAll();

        $this->_helper->viewRenderer->setNoRender(true);
        echo $this->view->render('admin/grupo-muscular/index.phtml');
    }

    public function editarGrupoMuscularAction()
    {
        $oGrupo = new Model_Rule_GrupoMuscular();
        $result = $oGrupo->getById($this->_request->getParam('id'));
        $this->_helper->json->sendJson($result);
    }

    public function salvarGrupoMuscularAction()
    {
        try{
            $oGrupo = new Model_Rule_GrupoMuscular();
            $oGrupo->setParams($this->_request->getParams());
            $oGrupo->salvar();
            $this->_helper->json->sendJson(['retorno' => 'sucesso', 'msg' => 'Grupo muscular salvo com sucesso!']);
        } catch(Exception $ex) {
            $this->_helper->json->sendJson(['retorno' => 'erro', 'msg' => 'Erro ao tentar salvar o grupo muscular. Tente novamente mais tarde']);
        }
    }

    public function deletarGrupoMuscularAction()
    {
        try{
            $oGrupo = new Model_Rule_GrupoMuscular();
            $oGrupo->deletar($this->_request->getParam('id'));
            $this->_helper->json->sendJson(['retorno' => 'sucesso', 'msg' => 'Grupo muscular removido com sucesso!']);
        } catch(Exception $ex) {
            $this->_helper->json->sendJson(['retorno' => 'erro', 'msg' => 'Erro ao tentar deletar o grupo muscular. Tente novamente mais tarde']);
        }
    }

    //End Grupo Muscular

    public function modalidadeAction()
    {
        $oGrupo = new Model_Rule_Grupo();
        $this->view->results = $oGrupo->getAll();

        $this->_helper->viewRenderer->setNoRender(true);
        echo $this->view->render('admin/grupo/index.phtml');
    }

    public function editarModalidadeAction()
    {
        $oGrupo = new Model_Rule_Grupo();
        $result = $oGrupo->getById($this->_request->getParam('id'));
        $this->_helper->json->sendJson($result);
    }

    public function salvarModalidadeAction()
    {
        try{
            $oGrupo = new Model_Rule_Grupo();
            $oGrupo->setParams($this->_request->getParams());
            $oGrupo->salvar();
            $this->_helper->json->sendJson(['retorno' => 'sucesso', 'msg' => 'Modalidade salva com sucesso!']);
        } catch(Exception $ex) {
            $this->_helper->json->sendJson(['retorno' => 'erro', 'msg' => 'Erro ao tentar salvar a modalidade. Tente novamente mais tarde']);
        }
    }

    public function deletarModalidadeAction()
    {
        try{
            $oGrupo = new Model_Rule_Grupo();
            $oGrupo->deletar($this->_request->getParam('id'));
            $this->_helper->json->sendJson(['retorno' => 'sucesso', 'msg' => 'Modalidade removida com sucesso!']);
        } catch(Exception $ex) {
            $this->_helper->json->sendJson(['retorno' => 'erro', 'msg' => 'Erro ao tentar deletar a modalidade. Tente novamente mais tarde']);
        }
    }

    public function treinosAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $treinos = new Model_Rule_Treinos();
        $this->view->treinos = $treinos->getAll();

    	if(isset($_SESSION['GroupTreino'])) {
    	    $this->view->aAlunosFavoritos = array_column($_SESSION['GroupTreino'], 'id_aluno');
    	}

        #Utils_Print::printvardie($this->view->treinos);
        echo $this->view->render('admin/treino/index.phtml');
    }

    public function treinoAlunoAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $oAluno = new Model_Rule_Aluno();
        $oTreino = new Model_Rule_Treinos();
        $oItensTreino = new Model_Rule_TreinoItens();
        $oFrequencia = new Model_Dao_Frequencia();

        $id_treino = $this->_request->getParam('id');
        $this->view->id_treino = $id_treino;

        $this->view->validadeTreino = $oTreino->validade($id_treino);

        $this->view->treino = $oTreino->getById($id_treino);

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

        $this->view->alunos = $oAluno->getAll();

        $aNome = explode(' ', $this->view->treino['aluno']);
        $nomeAluno = $aNome[0];

        $res = false;

        #unset($_SESSION['GroupTreino']);

    	$favorito = $this->_request->getParam('favoritar');
    	$desfavoritar = $this->_request->getParam('desfavoritar');

    	if(isset($desfavoritar)){
    	    if(isset($_SESSION['GroupTreino'])) {
        		foreach ($_SESSION['GroupTreino'] as $key => $s) {
        		    if($s['id_treino'] == $id_treino){
        			unset($_SESSION['GroupTreino'][$key]);
        		    }
        		}
    	    }
    	}

    	if(isset($_SESSION['GroupTreino'])) {
    	    $this->view->aAlunosFavoritos = array_column($_SESSION['GroupTreino'], 'id_aluno');

    	    if(in_array($aAluno['id'], $this->view->aAlunosFavoritos)){
    		    $this->view->treinoFavorito = true;
    	    }

    	    foreach ($_SESSION['GroupTreino'] as $key => $s) {
        		if($s['id_treino'] == $id_treino){
        		    $res = true;
        		}
    	    }
    	}

        if(isset($favorito)){
            if(!$res) {
                $_SESSION['GroupTreino'][] = [
                    'id_treino' => $id_treino,
                    'id_usuario' => $id_usuario_aluno,
		            'id_aluno' => $aAluno['id'],
                    'nome_aluno' => utf8_encode($nomeAluno)
                ];

	            $this->view->treinoFavorito = true;
            }
        }
        
        $oPeriodizacao = new Model_Rule_Treinos();
        $this->view->existePeriodizacao = $oPeriodizacao->existePeriodizacao($id_treino);

        echo $this->view->render('admin/treino/treino-aluno.phtml');
    }

    public function listaPeriodizacaoAction()
    {
        $this->_helper->layout->disableLayout();
        $oPeriodizacao = new Model_Rule_Periodizacao();
        $this->view->aPeriodizacao = $oPeriodizacao->getPeriodizacao($this->_request->getParam('id_treino'));

        $this->_helper->viewRenderer->setNoRender(true);
        echo $this->view->render('admin/treino/lista-periodizacao.phtml');
    }

    public function salvarPeriodizacaoAction()
    {
        $oPeriodizacao = new Model_Rule_Periodizacao();
        $oPeriodizacao->setParams($this->_request->getParams());
        $resPeriodizacao = $oPeriodizacao->salvarPeriodizacao();
        $this->_helper->json->sendJson($resPeriodizacao);
    }

    public function editaPeriodizacaoAction()
    {
        $oPeriodizacao = new Model_Rule_Periodizacao();
        $result = $oPeriodizacao->getById($this->_request->getParam('id'));
        $this->_helper->json->sendJson($result);
    }

    public function excluirPeriodizacaoAction()
    {
        $oPeriodizacao = new Model_Rule_Periodizacao();
        $oPeriodizacao->setParams($this->_request->getParams());
        $resPeriodizacao = $oPeriodizacao->removerPeriodizacao();
        $this->_helper->json->sendJson($resPeriodizacao);
    }

    public function createFichaTreinoAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $oAluno = new Model_Rule_Aluno();
        $this->view->alunos = $oAluno->getAll();

        echo $this->view->render('admin/treino/create-ficha-treino.phtml');
    }

    public function salvarFichaTreinoAction()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        try {
            $oTreino = new Model_Rule_Treinos();
            $oTreino->setParams($this->_request->getParams());
            $id_treino = $oTreino->salvar();
            $db->commit()->closeConnection();
            $this->_helper->json->sendJson(['retorno'=>'sucesso', 'msg'=>'Os dados do treino foram salvos com sucesso', 'id' => $id_treino]);
        } catch(Exception $ex){
            $db->rollBack();
            $this->_helper->json->sendJson(['retorno'=>'erro', 'msg'=>'Falha ao tentar salvar a ficha de treino. Por favor, tente novamente mais tarde.']);
        }
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

    public function addExerciciosAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        
        $oGrupo = new Model_Rule_Grupo();
        $this->view->grupo = $oGrupo->getAll();

        $oExercicio = new Model_Rule_Exercicio();
        $this->view->listaDeExercicios = $oExercicio->getAll();

        $this->view->id_treino = $this->_request->getParam('id');

        echo $this->view->render('admin/treino/add-exercicios.phtml');
    }

    public function editExercicioAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        
        $oGrupo = new Model_Rule_Grupo();
        $this->view->grupo = $oGrupo->getAll();

        $oExercicio = new Model_Rule_Exercicio();
        $this->view->listaDeExercicios = $oExercicio->getAll();

        $this->view->id = $this->_request->getParam('id');

        $oTreinoItens = new Model_Rule_TreinoItens();
        $this->view->exercicio = $oTreinoItens->getById($this->view->id);

        $this->view->id_treino = $this->view->exercicio['fk_treino'];

        echo $this->view->render('admin/treino/add-exercicios.phtml');
    }

    public function salvarExercicioTreinoAction()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        try {
            $oTreinoItens = new Model_Rule_TreinoItens();
            $oTreinoItens->setParams($this->_request->getParams());
            $oTreinoItens->salvarExercicioTreino();

            $db->commit()->closeConnection();

            $this->_helper->json->sendJson(['retorno'=>'sucesso', 'msg'=>'Exercício salvo com sucesso.']);
        }
        catch(Exception $ex) {
            $db->rollBack();
            $this->_helper->json->sendJson(['retorno' => 'erro', 'msg' => 'Falha ao tentar adicionar o exercicio. Por favor, tente novamente mais tarde. '.$ex->getMessage() ]);
        }
    }

    public function removerExercicioTreinoAction()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        try {
            $oTreinoItens = new Model_Rule_TreinoItens();
            $oTreinoItens->setParams($this->_request->getParams());
            $oTreinoItens->remover();

            $db->commit()->closeConnection();

            $this->_helper->json->sendJson(['retorno'=>'sucesso', 'msg'=>'O exercício foi removido.']);
        }
        catch(Exception $ex) {
            $db->rollBack();
            $this->_helper->json->sendJson(['retorno' => 'erro', 'msg' => 'Falha ao tentar remover o exercicio. Por favor, tente novamente mais tarde.']);
        }
    }

    public function solicitarNovaSenhaAction()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        try{
            $params = $this->_request->getParams();
            $usuario = new Model_Rule_Usuario();
            $usuario->setParams($params);
            $usuario->salvarSolicitacaoSenha();

            $db->commit()->closeConnection();

            $this->_helper->json->sendJson(['retorno'=>'sucesso', 'msg'=>'Solicitação concluída. Uma mensagem foi encaminhada para o email informado com as instruções de redefinição de senha.']);
        }
        catch(Exception $ex) {
            $db->rollBack();
            $this->_helper->json->sendJson(['retorno' => 'erro', 'msg' => $ex->getMessage()]);
        }
    }


    /*
     * INI M�SCULO
     */
    public function musculoAction() {
	$deviceType = Utils_File::verificaDispositivo();

        //Se o acesso for via 'phone' setar login e senha no Cookie
        if($deviceType == 'phone'){
            $this->_helper->layout->setLayout('mobile');
            $this->_helper->viewRenderer->setNoRender(true);
            
            $oMusculo = new Model_Rule_Musculo();
	    $this->view->results = $oMusculo->getMusculos();
            echo $this->view->render("admin/musculo-mobile.phtml");
        }else {        
            $this->_helper->layout->disableLayout();
        }
		
        
    }
    
    public function modalMusculoAction() {
        if($this->_request->getParam('id')){
            $oMusculo = new Model_Rule_Musculo();
            $this->view->aMusculo = $oMusculo->getMusculo($this->_request->getParam('id'));
        }
		
		$deviceType = Utils_File::verificaDispositivo();

        //Se o acesso for via 'phone' setar login e senha no Cookie
        if($deviceType == 'phone'){
            $this->_helper->layout->setLayout('mobile');
            $this->_helper->viewRenderer->setNoRender(true);
                  
            echo $this->view->render("admin/modal-musculo-mobile.phtml");
        }else {        
			die( $this->view->render('admin/modal-musculo.phtml') );
        }
        
        
    }
    
    public function gridMusculosAction() {
        $oMusculo = new Model_Rule_Musculo();
        $this->view->results = $oMusculo->getMusculos();
        die( $this->view->render("admin/gridListMusculos.phtml") );
    }
    
    public function removerMusculoAction() {
        $oMusculo = new Model_Rule_Musculo();
        $oMusculo->setParams($this->_request->getParams());
        $results = $oMusculo->removerMusculo();
        $this->_helper->json->sendJson($results);
    }
    
    public function salvarMusculoAction() {
        $oMusculo = new Model_Rule_Musculo();
        $oMusculo->setParams($this->_request->getParams());
        $results = $oMusculo->salvarMusculo();
        $this->_helper->json->sendJson($results);
    }

    public function alterarMusculoAction() {
        $oMusculo = new Model_Rule_Musculo();
        $oMusculo->setParams($this->_request->getParams());
        $results = $oMusculo->alterarMusculo();
        $this->_helper->json->sendJson($results);
    }
    /*
     * END M�SCULO
     */




    public function gridAlunoAvaliacaoAction() {
        $this->_helper->layout->disableLayout();
        $oAluno = new Model_Rule_Aluno();
        $aAlunos = $oAluno->getAlunos();        
        $this->view->results = $aAlunos;        
        //die( $this->view->render("admin/grid-list-alunos.phtml") );
    }
    
    
    
    public function carregarTreinoFichaAction() {
        $this->aluno_id = $this->_request->getParam('aluno_id');
        
        $oTreino = new Model_Rule_MontarTreino();
        $aTreinoFicha = $oTreino->getDadosTreino($this->_request->getParam('aluno_id'));
        #Utils_Print::printvardie($aTreinoFicha);
        $this->view->cond = $aTreinoFicha['cond'];
        $this->view->aTreinoFicha = $aTreinoFicha['results'];
        die($this->view->render('admin/gridTreino.phtml'));
    }
    
    public function removerTreinoAction() {
//        Utils_Print::printvardie('passou');
        $oTreino = new Model_Rule_MontarTreino();
        $results = $oTreino->removerTreino($this->_request->getParam('treino_id'));
        $this->_helper->json->sendJson($results);
    }
    
    public function salvarNovoTreinoAction() {
        $this->db = Zend_Db_Table::getDefaultAdapter();
        $this->db->beginTransaction();
        
        $aluno_id = $this->_request->getParam('aluno_id');
        
        $authSession = new Zend_Auth_Storage_Session();
        $read = $authSession->read();
        
        
        $dataFicha = array(
            'academia_id'  => $read->academia_id,
            'ds_ficha'     => utf8_decode($this->_request->getParam('descricao')),
            'aluno_id'     => $aluno_id,
            'professor_id' => ($this->_request->getParam('cbProfessor'))? $this->_request->getParam('cbProfessor') : null,
            'objetivo'     => ($this->_request->getParam('objetivo')? utf8_decode($this->_request->getParam('objetivo')) : null),
            'observacao' => ($this->_request->getParam('observacao'))? utf8_decode($this->_request->getParam('observacao')) : null,
        );

        try{
            $this->db->insert("ficha", $dataFicha);
        }catch(Exception $e){
            $this->db->rollBack();
            $this->_helper->json->sendJson(array('retorno' => 'erro', 'msg' => 'Erro ao salvar a Ficha do aluno. '.$e->getMessage()));
        }

        $fichaId = $this->db->fetchAll("SELECT max(id) as id FROM ficha");
        $fichaId = $fichaId[0]['id'];
        
        $this->db->commit();
		
		$deviceType = Utils_File::verificaDispositivo();

        //Se o acesso for via 'phone' setar login e senha no Cookie
        if($deviceType == 'phone'){
            $this->_helper->layout->setLayout('mobile');
            $this->_helper->viewRenderer->setNoRender(true);
            
            $this->_helper->json->sendJson(array('retorno' => 'sucesso', 'msg' => 'Ficha criada com sucesso.', 'ficha_id' => $fichaId));
        }else {        
			$this->_helper->json->sendJson(array('retorno' => 'sucesso', 'ficha_id' => $fichaId));
        }
        
    }
    
    
    public function alterarFichaTreinoAction() {
        $this->db = Zend_Db_Table::getDefaultAdapter();
        $this->db->beginTransaction();
        
        #Utils_Print::printvardie($this->sessao);
        $ficha_id = $this->_request->getParam('ficha_id');
		$aluno_id = $this->_request->getParam('aluno_id');
        
        $data = array(
            'academia_id'  => $this->sessao->academia_id,
            'ds_ficha'     => utf8_decode( $this->_request->getParam('descricao') ),
            'aluno_id'     => $aluno_id,
            'professor_id' => ($this->_request->getParam('cbProfessor'))? $this->_request->getParam('cbProfessor') : null,
            'objetivo'     => ($this->_request->getParam('objetivo')? utf8_decode($this->_request->getParam('objetivo')) : null),
            'observacao' => ($this->_request->getParam('observacao'))? utf8_decode($this->_request->getParam('observacao')) : null,
        );

        try{
            $this->db->update("ficha", $data, "id = {$ficha_id}");
        }catch(Exception $e){
            $this->db->rollBack();
            $this->_helper->json->sendJson(array('retorno' => 'erro', 'msg' => 'Erro ao salvar a Ficha do aluno. '.$e->getMessage()));
        }
        
        $this->db->commit();
        
		$deviceType = Utils_File::verificaDispositivo();

        //Se o acesso for via 'phone' setar login e senha no Cookie
        if($deviceType == 'phone'){
            $this->_helper->layout->setLayout('mobile');
            $this->_helper->viewRenderer->setNoRender(true);
			$this->_helper->json->sendJson(array('retorno' => 'sucesso', 'msg' => 'Ficha atualizada com sucesso.', 'ficha_id' => $ficha_id));
        }else {        
			$this->_helper->json->sendJson(array('retorno' => 'sucesso', 'msg' => 'A ficha foi atualizada com sucesso.', 'ficha_id' => $ficha_id));
        }
        
    }
    
    
    public function salvarExercicioFichaAction() {
        $this->db = Zend_Db_Table::getDefaultAdapter();
        $this->db->beginTransaction();
        
        if(!$this->_request->getParam('treino')) 
            $this->_helper->json->sendJson(array('retorno' => 'falha', 'msg' => 'Por favor, selecione o treino.'));
        
        if(!$this->_request->getParam('exercicio_id')) 
            $this->_helper->json->sendJson(array('retorno' => 'falha', 'msg' => 'Por favor, selecione o exercicio.'));
        
        /*
        if(!$this->_request->getParam('carga')) 
            $this->_helper->json->sendJson(array('retorno' => 'falha', 'msg' => 'Por favor, informe a carga.'));
        
        if(!$this->_request->getParam('serie')) 
            $this->_helper->json->sendJson(array('retorno' => 'falha', 'msg' => 'Por favor, informe a seria.'));
        
        if(!$this->_request->getParam('repeticao')) 
            $this->_helper->json->sendJson(array('retorno' => 'falha', 'msg' => 'Por favor, informe o numero de repetcoes.'));
        
        if(!$this->_request->getParam('intervalo')) 
            $this->_helper->json->sendJson(array('retorno' => 'falha', 'msg' => 'Por favor, informe o intervalo.'));
        */
        
        
        $fichaId = $this->_request->getParam('ficha_id');
        
        
        $date = array(
            'ficha_id' => ($fichaId),
            'treino' => $this->_request->getParam('treino'),
            'exercicio_id' => $this->_request->getParam('exercicio_id'),
            'carga1' => $this->_request->getParam('carga'),
            'repeticoes' => $this->_request->getParam('repeticao'),
            'series' => $this->_request->getParam('serie'),
            'intervalo' => $this->_request->getParam('intervalo'),
            'detalhes' => $this->_request->getParam('detalhes')
        );
        
        
        try{
        
            $this->db->insert("treino", $date);
            
        }catch(Exception $e){
            $this->db->rollBack();
            $this->_helper->json->sendJson(array('retorno' => 'erro', 'msg' => $e->getMessage()));
        }
        
        $this->db->commit();
        
        $this->_helper->json->sendJson(array('retorno' => 'sucesso', 'msg' => 'Exercício adicionado a ficha.'));
    }
    
    public function alterarTreinoAction() {
        $oExercicio = new Model_Rule_Exercicio();
        $oExercicio->setParams($this->_request->getParams());
        $results = $oExercicio->alterarExercicio();
        $this->_helper->json->sendJson($results);
    }
    /* 
     * END EXERC�CIO 
     */
     
     
     
    public function trocarAcademiaAction(){
    	$index = $this->_request->getParam('index');
    	$authSession = new Zend_Auth_Storage_Session();
        $read = $authSession->read();
        
        $read->academia_id = $read->academias[$index]['id'];
        $read->academia_nome = $read->academias[$index]['nm_academia']; 
        $read->foto = $read->academias[$index]['logo']; 
        
        exit;       
    } 
    
    
    /* INI ACADEMIA */
    
    public function academiaAction(){
    	$this->_helper->layout->disableLayout();
    }
    
    public function gridAcademiasAction()
    {
        $oAluno = new Model_Rule_Aluno();
        $this->view->results = $oAluno->getAlunos();
        echo $this->view->render("admin/grid-alunos.phtml");
        exit;
    }
    /* END ACADEMIA */

    public function gridPeriodizacaoFichaAction()
    {
        $oPeriodizacao = new Model_Rule_Periodizacao();
        $this->view->aPeriodizacao = $oPeriodizacao->getPeriodizacao($this->_request->getParam('ficha_id'));
        die( $this->view->render('admin/grid-periodizacao.phtml') );
    }
    
    public function gridPeriodizacaoPreviewAction(){
        $oPeriodizacao = new Model_Rule_Periodizacao();
        $this->view->aPeriodizacao = $oPeriodizacao->getPeriodizacao($this->_request->getParam('ficha_id'));
        die( $this->view->render('admin/grid-periodizacao-preview.phtml') );        
    }
    
    public function changeComboTreinoAction(){
        $oPeriodizacao = new Model_Rule_Periodizacao();
        $this->view->aPeriod = $oPeriodizacao->getPeriodizacao($this->_request->getParam('ficha_id'));
        die( $this->view->render('admin/comboTreinoPeriodizacao.phtml') );
    }
    
    public function iniciarTreinoAction(){
        $oIniciarTreino = new Model_Rule_IniciarTreino();
        $oIniciarTreino->setParams($this->_request->getParams());
        $result = $oIniciarTreino->iniciar();
        $this->_helper->json->sendJson($result);
    }
    
    public function avaliacaoFisicaAction()
    {
        $this->_helper->layout->setLayout('admin');
        $this->_helper->viewRenderer->setNoRender(true);

        $oAvaliacao = new Model_Rule_Avaliacao();
        $this->view->aAvaliacoes = $oAvaliacao->getAll();

        echo $this->view->render("admin/avaliacao-fisica/index.phtml");
    }

    public function avaliacaoFisicaCreateAction()
    {
        $this->_helper->layout->setLayout('admin');
        $this->_helper->viewRenderer->setNoRender(true);

        $oAluno = new Model_Rule_Aluno();
        $this->view->alunos = $oAluno->getAll();

        echo $this->view->render("admin/avaliacao-fisica/create.phtml");
    }

    public function avaliacaoFisicaEditAction()
    {
        $this->_helper->layout->setLayout('admin');
        $this->_helper->viewRenderer->setNoRender(true);

        $oAluno = new Model_Rule_Aluno();
        $this->view->alunos = $oAluno->getAll();

        $oAvaliacao = new Model_Rule_Avaliacao();
        $this->view->avaliacao = $oAvaliacao->getById($this->_request->getParam('id'));

        $oAluno = new Model_Rule_Aluno();
        $this->view->avaliacao['aluno'] = $oAluno->getAluno($this->view->avaliacao['fk_aluno'])['nome'];

        $this->view->editar = true;
        #Utils_Print::printvardie($this->view->avaliacao);

        echo $this->view->render("admin/avaliacao-fisica/create.phtml");
    }

    public function avaliacaoFisicaViewAction()
    {
        $this->_helper->layout->setLayout('admin');
        $this->_helper->viewRenderer->setNoRender(true);

        $oAvaliacao = new Model_Rule_Avaliacao();
        $avaliacao = $oAvaliacao->getById($this->_request->getParam('id'));

        list($ano) = explode('-', $avaliacao['dt_nascimento']);
        $avaliacao['idade'] = ( date('Y') - $ano );


        $aDado = Array(
            'sexo' => $avaliacao['sexo'],
            'idade' => $avaliacao['idade'],
            'peso' => $avaliacao['peso'],
            'altura' => $avaliacao['altura'],
            'protocolo' => $avaliacao['protocolo']
        );

        $aDobras = Array(
            'PE' => $avaliacao['PE'],  //Peitoral ou Tórax
            'BI' => $avaliacao['BI'],  //Bíceps
            'AX' => $avaliacao['AX'],  //Axilar Média
            'TR' => $avaliacao['TR'],  //Tríceps
            'SB' => $avaliacao['SB'], //Subescapular
            'AB' => $avaliacao['AB'], //Abdomen
            'SI' => $avaliacao['SI'], //Supra Ilíaca
            'CX' => $avaliacao['CX'], //Coxa
            'PA' => $avaliacao['PA']  //Panturrilha
        );


            $oGerar = new Model_Rule_GerarAvaliacao();

            //IMC
            $IMC = $oGerar->calcularIMC($avaliacao['peso'], $avaliacao['altura']);
            $situacao = $oGerar->avaliacaoIMC($IMC);
            
            #Utils_Print::printvardie($IMC);

            //Densidade Corporal
            $densidade = $oGerar->calcularDensidadeCorporal($aDobras, $aDado);

            //Percentual de Gordura
            $percentGordura = $oGerar->calcularPercentualDeGordura($densidade, $avaliacao['sexo'], $avaliacao['idade']);
       
            //Massa Gorda
            $massaGorda = ( $avaliacao['peso'] *  $percentGordura / 100 );
            #$percentMassaGorda = ( ( $massaGorda * 100 ) / $avaliacao['peso'] );
            
            //Massa Magra
            $massaMagra = ( $avaliacao['peso'] - $massaGorda );
            #$percentMassaMagra = ( ( $massaMagra * 100 ) / $avaliacao['peso'] );
            
            $massaResidual = null;
            
            //Massa Residual
            if( $avaliacao['sexo'] == 'M' ){
                $massaResidual = ( $avaliacao['peso'] * 0.241 );
            }else {
                $massaResidual = ( $avaliacao['peso'] * 0.209 );                
            }    

            //Percentual Residual
            #$percentResidual = ( ( $massaResidual * 100 ) / $avaliacao['peso'] );

            //Massa Óssea
            $resOsseo = (($avaliacao['biestiloide'] + $avaliacao['biepicondiliano_umero'] + $avaliacao['biepicondiliano_femur'] + $avaliacao['bimaleolar']) / 4);
            $massaOssea = (($resOsseo * $resOsseo) * ($avaliacao['altura'] * 0.00092));
            $percentMassaOssea = ( ( $massaOssea * 100 ) / $avaliacao['peso'] );

            //Massa Muscular
            $massaMuscular = ( $avaliacao['peso'] - ( $massaGorda + $massaOssea + $massaResidual ) ); // 0 é a Massa Óssea - não sei o valor
            #$percentMassaMuscular = ( ( $massaMuscular * 100 ) / $avaliacao['peso'] );

            #$alturaCm = str_replace('.', '', $avaliacao['altura']); //tranforma para centimetros ( 1,80m = 180cm )
            $alturaCm = $avaliacao['altura'];
            if( $avaliacao['sexo'] == 'M' ){
                $massaIdeal = ( $alturaCm - 100 - ( ($alturaCm-150)/4 ) );
            }else {
                $massaIdeal = ( $alturaCm - 100 - ( ($alturaCm-150)/2 ) );
            }

            $massaEmExcesso = ( $avaliacao['peso'] - $massaIdeal );

            //Update das informações
            $avaliacao['imc'] = $IMC;
            $avaliacao['densidade'] = $densidade;
            $avaliacao['situacao'] = $situacao;

            $avaliacao['percentual_gordura'] = $percentGordura;
            $avaliacao['massa_gorda'] = number_format($massaGorda, 2);
            $avaliacao['massa_magra'] = number_format($massaMagra, 2);
            $avaliacao['massa_muscular'] = number_format($massaMuscular, 2);
            $avaliacao['massa_ossea'] = number_format($massaOssea, 2);
            $avaliacao['massa_residual'] = number_format($massaResidual, 2);
            $avaliacao['peso_ideal'] = number_format($massaIdeal, 2);
            $avaliacao['peso_excesso'] = number_format($massaEmExcesso, 2);

            $this->view->avaliacao = $avaliacao;

            #Utils_Print::printvardie($avaliacao);

        echo $this->view->render("admin/avaliacao-fisica/view.phtml");
    }

    public function salvarAvaliacaoFisicaAction()
    {
        $oAvaliacao = new Model_Rule_Avaliacao();
        $oAvaliacao->setParams($this->_request->getParams());
        $result = $oAvaliacao->salvarAvaliacao();
        $this->_helper->json->sendJson($result);
    }
    
    public function avaliacaoAction(){
        $deviceType = Utils_File::verificaDispositivo();

        //Se o acesso for via 'phone' setar login e senha no Cookie
        if($deviceType == 'phone'){
            $this->_helper->layout->setLayout('mobile');
            $this->_helper->viewRenderer->setNoRender(true);
                 
            $oAluno = new Model_Rule_Aluno();
            $aAlunos = $oAluno->getAlunos();			
            $this->view->results = $aAlunos;
            echo $this->view->render("admin/avaliacao-mobile.phtml");
        }else {        
            $this->_helper->layout->disableLayout();
            $this->view->sessao = $_SESSION['usuario'];
        }
    }
    
    public function listarAvaliacoesAlunoAction() {
        $aluno_id = $this->_request->getParam('matricula');
        $this->view->aluno_id = $aluno_id;
        
        $oAvaliacao = new Model_Rule_Avaliacao();
        $this->view->aAvaliacao = $oAvaliacao->getAvaliacoes($aluno_id);        
                
        $oAluno = new Model_Rule_Aluno();
        $this->view->dadoAluno = $oAluno->getAluno($aluno_id);
		
        $deviceType = Utils_File::verificaDispositivo();

        //Se o acesso for via 'phone' setar login e senha no Cookie
        if($deviceType == 'phone'){
            $this->_helper->layout->setLayout('mobile');
            $this->_helper->viewRenderer->setNoRender(true);
            
            echo $this->view->render("admin/listar-avaliacoes-aluno-mobile.phtml");
        }else {
            $this->_helper->layout->disableLayout();
        }
    }
    
    public function modalAvaliacaoDobrasAction() {
        $matriculaAluno = $this->_request->getParam('aluno_id');
        $this->view->aluno_id = $matriculaAluno;
        
                
        $oAluno = new Model_Rule_Aluno();
        $this->view->dadosAluno = $oAluno->getAluno($matriculaAluno);
        
        
        $deviceType = Utils_File::verificaDispositivo();

        //Se o acesso for via 'phone' setar login e senha no Cookie
        if($deviceType == 'phone'){
            $this->_helper->layout->setLayout('mobile');
            $this->_helper->viewRenderer->setNoRender(true);
            
            echo $this->view->render("admin/modal-avaliacao-dobras-mobile.phtml");
        }else {        
            $this->_helper->layout->disableLayout();
        }
    }
    
    public function modalAvaliacaoDobrasEditAction() {
        $avaliacao_id = $this->_request->getParam('avaliacao_id');
        $this->view->avaliacao_id = $avaliacao_id;
        
        #Utils_Print::printvardie($_REQUEST);
                
        $oAvaliacao = new Model_Rule_Avaliacao();
        $this->view->dadosAvaliacao = $oAvaliacao->getAvaliacaoAluno($avaliacao_id);
        
        
        $deviceType = Utils_File::verificaDispositivo();

        //Se o acesso for via 'phone' setar login e senha no Cookie
        if($deviceType == 'phone'){
            $this->_helper->layout->setLayout('mobile');
            $this->_helper->viewRenderer->setNoRender(true);
            
            echo $this->view->render("admin/modal-avaliacao-dobras-mobile.phtml");
        }else {        
            $this->_helper->layout->disableLayout();
        }
    }
    
    public function modalAvaliacaoMedidasAction() {
        $aluno_id = $this->_request->getParam('aluno_id');        
        $this->view->aluno_id = $aluno_id;
        
        $avaliacao_id = $this->_request->getParam('avaliacao_id');
        $this->view->avaliacao_id = $avaliacao_id;
                
        $oAluno = new Model_Rule_Aluno();
        $this->view->dadosAluno = $oAluno->getAluno($aluno_id);
        
        
        $deviceType = Utils_File::verificaDispositivo();

        //Se o acesso for via 'phone' setar login e senha no Cookie
        if($deviceType == 'phone'){
            $this->_helper->layout->setLayout('mobile');
            $this->_helper->viewRenderer->setNoRender(true);
            
            echo $this->view->render("admin/modal-avaliacao-dobras-mobile.phtml");
        }else {        
            $this->_helper->layout->disableLayout();
        }
    }
    
    public function modalAvaliacaoMedidasEditAction() {        
        $avaliacao_id = $this->_request->getParam('avaliacao_id');
        $this->view->avaliacao_id = $avaliacao_id;
        
        $oAvaliacao = new Model_Rule_Avaliacao();
        $this->view->dadosAvaliacao = $oAvaliacao->getAvaliacaoAluno($avaliacao_id);
                
        $oAluno = new Model_Rule_Aluno();
        $this->view->dadosAluno = $oAluno->getAluno($this->view->dadosAvaliacao['aluno_id']);
        
        $this->view->aluno_id = $this->view->dadosAvaliacao['aluno_id'];
        
        $deviceType = Utils_File::verificaDispositivo();

        //Se o acesso for via 'phone' setar login e senha no Cookie
        if($deviceType == 'phone'){
            $this->_helper->layout->setLayout('mobile');
            $this->_helper->viewRenderer->setNoRender(true);
            
            echo $this->view->render("admin/modal-avaliacao-dobras-mobile.phtml");
        }else {        
            $this->_helper->layout->disableLayout();
        }
    }
    
    public function modalAvaliacaoDiametrosOsseosAction() {
        $aluno_id = $this->_request->getParam('aluno_id');        
        $this->view->aluno_id = $aluno_id;
        
        $avaliacao_id = $this->_request->getParam('avaliacao_id');
        $this->view->avaliacao_id = $avaliacao_id;
                
        $oAluno = new Model_Rule_Aluno();
        $this->view->dadosAluno = $oAluno->getAluno($aluno_id);
        
        
        $deviceType = Utils_File::verificaDispositivo();

        //Se o acesso for via 'phone' setar login e senha no Cookie
        if($deviceType == 'phone'){
            $this->_helper->layout->setLayout('mobile');
            $this->_helper->viewRenderer->setNoRender(true);
            
            echo $this->view->render("admin/modal-avaliacao-diametros-osseos-mobile.phtml");
        }else {        
            $this->_helper->layout->disableLayout();
        }
    }
    
    public function modalAvaliacaoDiametrosOsseosEditAction() {
        $avaliacao_id = $this->_request->getParam('avaliacao_id');
        $this->view->avaliacao_id = $avaliacao_id;
        
        $oAvaliacao = new Model_Rule_Avaliacao();
        $this->view->dadosAvaliacao = $oAvaliacao->getAvaliacaoAluno($avaliacao_id);
                
        $oAluno = new Model_Rule_Aluno();
        $this->view->dadosAluno = $oAluno->getAluno($this->view->dadosAvaliacao['aluno_id']);
        
        $this->view->aluno_id = $this->view->dadosAvaliacao['aluno_id'];
        
        $deviceType = Utils_File::verificaDispositivo();

        //Se o acesso for via 'phone' setar login e senha no Cookie
        if($deviceType == 'phone'){
            $this->_helper->layout->setLayout('mobile');
            $this->_helper->viewRenderer->setNoRender(true);
            
            echo $this->view->render("admin/modal-avaliacao-diametros-osseos-mobile.phtml");
        }else {        
            $this->_helper->layout->disableLayout();
        }
    }
    
    public function salvarAvaliacaoPasso1Action() {
        $oAvaliacao = new Model_Rule_Avaliacao();
        $oAvaliacao->setParams($this->_request->getParams());
        
        try{
            $result = $oAvaliacao->salvarDobras();
            $this->_helper->json->sendJson($result);
        } catch(Exception $e){
            $this->_helper->json->sendJson(array('retorno' => 'erro', 'msg' => $e->getMessage()));
        }
    }
    
    public function alterarDobrasAction() {
        $oAvaliacao = new Model_Rule_Avaliacao();
        $oAvaliacao->setParams($this->_request->getParams());
        
        try{
            $result = $oAvaliacao->alterarDobras();
            $this->_helper->json->sendJson($result);
        } catch(Exception $e){
            $this->_helper->json->sendJson(array('retorno' => 'erro', 'msg' => $e->getMessage()));
        }
    }
    
    public function salvarAvaliacaoPasso2Action() {
        $oAvaliacao = new Model_Rule_Avaliacao();
        $oAvaliacao->setParams($this->_request->getParams());
        
        try{
            $result = $oAvaliacao->salvarMedidas();
            $this->_helper->json->sendJson($result);
        } catch(Exception $e){
            $this->_helper->json->sendJson(array('retorno' => 'erro', 'msg' => $e->getMessage()));
        }
    }
    
    public function alterarMedidasAction() {
        $oAvaliacao = new Model_Rule_Avaliacao();
        $oAvaliacao->setParams($this->_request->getParams());
        
        try{
            $result = $oAvaliacao->salvarMedidas();
            $this->_helper->json->sendJson($result);
        } catch(Exception $e){
            $this->_helper->json->sendJson(array('retorno' => 'erro', 'msg' => $e->getMessage()));
        }
    }
    
    public function salvarAvaliacaoPasso3Action() {
        $oAvaliacao = new Model_Rule_Avaliacao();
        $oAvaliacao->setParams($this->_request->getParams());
        
        try{
            $result = $oAvaliacao->salvarDiametrosOsseos();
            $this->_helper->json->sendJson($result);
        } catch(Exception $e){
            $this->_helper->json->sendJson(array('retorno' => 'erro', 'msg' => $e->getMessage()));
        }
    }

    public function salvarAvaliacaoMobileAction() {
        $oAvaliacao = new Model_Rule_Avaliacao();
        $oAvaliacao->setParams($this->_request->getParams());
        
        try{
            $result = $oAvaliacao->salvarAvaliacaoMobile();
            $this->_helper->json->sendJson($result);
        } catch(Exception $e){
            $this->_helper->json->sendJson(array('retorno' => 'erro', 'msg' => $e->getMessage()));
        }
    }


    public function alterarAvaliacaoMobileAction() {
        $oAvaliacao = new Model_Rule_Avaliacao();
        $oAvaliacao->setParams($this->_request->getParams());
        
        try{
            $result = $oAvaliacao->alterarAvaliacaoMobile();
            $this->_helper->json->sendJson($result);
        } catch(Exception $e){
            $this->_helper->json->sendJson(array('retorno' => 'erro', 'msg' => $e->getMessage()));
        }
    }


    
    public function alterarMedidasOsseasAction() {
        $oAvaliacao = new Model_Rule_Avaliacao();
        $oAvaliacao->setParams($this->_request->getParams());
        
        try{
            $result = $oAvaliacao->salvarDiametrosOsseos();
            $this->_helper->json->sendJson($result);
        } catch(Exception $e){
            $this->_helper->json->sendJson(array('retorno' => 'erro', 'msg' => $e->getMessage()));
        }
    }
    
    public function gerarResultadoAvaliacaoAction() {
        $oAvaliacao = new Model_Rule_Avaliacao();
        $oAvaliacao->setParams($this->_request->getParams());
        
        try{
            $result = $oAvaliacao->gerarResultadoAvaliacaoFisica();
            $this->_helper->json->sendJson($result);
        } catch(Exception $e){
            $this->_helper->json->sendJson(array('retorno' => 'erro', 'msg' => $e->getMessage()));
        }
    }

    
    public function editAvaliacaoFisicaAction(){        
        $avaliacao_id = $this->_request->getParam('avaliacao_id');
        $this->view->avaliacao_id = $avaliacao_id;
        
        $oAvaliacao = new Model_Dao_Avaliacao();
        $oAvaliacaoRow = $oAvaliacao->find($avaliacao_id)->current();
        
        
        $oAluno = new Model_Dao_Aluno();
        $aAluno = $oAluno->find($oAvaliacaoRow->aluno_id)->current()->toArray();
        
        $this->view->dadosAluno = $aAluno;
        $this->view->aAvaliacao = $oAvaliacaoRow->toArray();
        
        
        
        $deviceType = Utils_File::verificaDispositivo();

        //Se o acesso for via 'phone' setar login e senha no Cookie
        if($deviceType == 'phone'){
            $this->_helper->layout->setLayout('mobile');
            $this->_helper->viewRenderer->setNoRender(true);
            
            echo $this->view->render("admin/edit-avaliacao-fisica-mobile.phtml");
        }else {        
            $this->_helper->layout->disableLayout();
            $html = $this->view->render('admin/edit-avaliacao-fisica.phtml');
            $results = array('retorno' => 'sucesso', 'html' => utf8_encode($html));
            $this->_helper->json->sendJson($results); 
        }
    }
    
    public function removerAvaliacaoFisicaAction(){        
        $avaliacao_id = $this->_request->getParam('avaliacao_id');
        
        try{
            $oAvaliacao = new Model_Dao_Avaliacao();
            $oAvaliacaoRow = $oAvaliacao->find($avaliacao_id)->current();
            $academia_id = $oAvaliacaoRow['academia_id'];
            #Utils_Print::printvardie($oAvaliacaoRow);
            $oAvaliacaoRow->delete();            
            $this->_helper->json->sendJson(array('retorno' => 'sucesso', 'msg' => 'A Avaliacao foi removida!')); 
        } catch(Exception $ex){
            $this->_helper->json->sendJson(array('retorno' => 'erro', 'msg' => $ex->getMessage())); 
        }
    }
    
    public function loadAvaliacaoFisicaAction(){        
        $avaliacao_id = $this->_request->getParam('avaliacao_id');
        $this->view->avaliacao_id = $avaliacao_id;
        
        $oAvaliacao = new Model_Dao_Avaliacao();
        $oAvaliacaoRow = $oAvaliacao->find($avaliacao_id)->current();
        
        
        $oAluno = new Model_Dao_Aluno();
        $aAluno = $oAluno->find($oAvaliacaoRow->aluno_id)->current()->toArray();
        
        $this->view->dadosAluno = $aAluno;
        $this->view->aAvaliacao = $oAvaliacaoRow->toArray();
        
        
        
        $deviceType = Utils_File::verificaDispositivo();

        //Se o acesso for via 'phone' setar login e senha no Cookie
        if($deviceType == 'phone'){
            $this->_helper->layout->setLayout('mobile');
            $this->_helper->viewRenderer->setNoRender(true);
            
            echo $this->view->render("admin/modal-avaliacao-edit-mobile.phtml");
        }else {        
            $this->_helper->layout->disableLayout();
            $html = $this->view->render('admin/load-avaliacao-fisica.phtml');
            #$results = array('retorno' => 'sucesso', 'html' => utf8_encode($html));
            #$this->_helper->json->sendJson($results); 
            die( $html );
        }
    }


     public function verAvaliacaoFisicaAction(){        
        
        $avaliacao_id = $this->_request->getParam('avaliacao_id');
                
        $this->view->avaliacao_id = $avaliacao_id;
        
        $oAvaliacao = new Model_Dao_Avaliacao();
        $oAvaliacaoRow = $oAvaliacao->find($avaliacao_id)->current();
        
        $oAluno = new Model_Dao_Aluno();
        $aAluno = $oAluno->find($oAvaliacaoRow->aluno_id)->current()->toArray();
        
        $this->view->dadosAluno = $aAluno;
        $this->view->aAvaliacao = $oAvaliacaoRow->toArray();
        
        $this->_helper->layout->setLayout('mobile');
        $this->_helper->viewRenderer->setNoRender(true);
           
        echo $this->view->render("admin/ver-avaliacao-mobile.phtml");
    }

    
}