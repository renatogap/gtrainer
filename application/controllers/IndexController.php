<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        #Utils_Print::printvardie($_COOKIE);
        $deviceType = Utils_File::verificaDispositivo();

        #if($deviceType == 'phone'){
            if(isset($_COOKIE['login']) && isset($_COOKIE['senha'])) {
                return $this->_helper->redirector('index', 'admin');
            }
        #}
    }

    public function homeAction() {
        $this->isLogado();

        $this->_helper->layout->setLayout('ficha-aluno');
        $email = $this->_request->getParam('email');
        $this->view->email = $email;

        $oTreino = new Model_Rule_MontarTreino();
        $oAluno = new Model_Rule_Aluno();

        $this->view->dadoAluno  = $oAluno->getAlunoPorEmail($email);
        #Utils_Print::printvardie($this->view->email);

        $detect = new Model_Rule_MobileDetect;
        $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');

        if($deviceType == 'phone'){
            $html = $this->view->render('index/home-aluno-mobile.phtml');
            die($html);
        }else {
            $this->_helper->viewRenderer->setNoRender();
            $html = $this->view->render('index/home-aluno.phtml');
            echo $html;
        }
    }

    public function indexAction() {
        $deviceType = Utils_File::verificaDispositivo();

        //Se o acesso for via 'phone' setar login e senha no Cookie
        if($deviceType == 'phone'){
            return $this->_helper->redirector('login', 'admin');
        }else {
            //return $this->_helper->redirector('index', 'admin');
            $this->_helper->layout->setLayout('home');
        }
    }

    public function loginAction(){
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()){
            return $this->_helper->redirector('index', 'admin');
        }

        $this->_helper->layout->setLayout('home');
    }

    public function esqueciMinhaSenhaAction(){
        $this->_helper->layout->setLayout('home');
    }
    
    public function cadastroAction(){
        $this->_helper->layout->setLayout('home');        
        $oPlano = new Model_Rule_Plano();
        $aPlanos = $oPlano->getPlanosPremium();
        $this->view->aPlanos = $aPlanos['result'];
    }
    
    public function printscreenAction() {
        $this->_helper->layout->setLayout('mobile');
    }
    
    public function printAlunoAction() {
        $this->_helper->layout->setLayout('mobile');
    }
    
    
    public function fichasTreinoAlunoAction() {
        $this->isLogado();
        
        $this->_helper->layout->setLayout('ficha-aluno');
        
        $email = $this->_request->getParam('email');
        
        $oTreino = new Model_Rule_MontarTreino();
        $oAluno = new Model_Rule_Aluno();
        
        $this->view->dadosFicha = $oTreino->getTreinosAlunoPorEmail($email);        
        $this->view->dadoAluno  = $oAluno->getAlunoPorEmail($email);           
        
        #Utils_Print::printvardie($this->view->dadosFicha);
        
        $this->view->aluno_id   = $this->view->dadoAluno['matricula']; 
        
        //Instancia a Classe para detectar acesso via Mobile
        $detect = new Model_Rule_MobileDetect;
        
        //Identifica o tipe de Device acessado (computer, tablet ou phone)
        $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');

        //Se o acesso for via 'phone' setar login e senha no Cookie
        if($deviceType == 'phone'){			
            $html = $this->view->render('index/fichas-treino-aluno-mobile.phtml');
            die($html);
        }else {
            $this->_helper->viewRenderer->setNoRender();		
            $html = $this->view->render('index/fichas-treino-aluno.phtml');
            echo $html;
        }
        
        
        #Utils_Print::printvardie($this->view->dadoAluno);     
    }
    
    
    public function avaliacoesAlunoAction() {
        $this->isLogado();
        
        $this->_helper->layout->setLayout('ficha-aluno');
        
        $email = $this->_request->getParam('email');
        
        $oAvaliacao = new Model_Rule_Avaliacao();
        $oAluno = new Model_Rule_Aluno();
        
        $this->view->aAvaliacao = $oAvaliacao->getAvaliacoesPorEmail($email); 
        $this->view->dadoAluno  = $oAluno->getAlunoPorEmail($email);           
        
        #Utils_Print::printvardie($this->view->dadosFicha);
        
        $this->view->aluno_id   = $this->view->dadoAluno['matricula']; 
        
        //Instancia a Classe para detectar acesso via Mobile
        $detect = new Model_Rule_MobileDetect;
        
        //Identifica o tipe de Device acessado (computer, tablet ou phone)
        $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');

        //Se o acesso for via 'phone' setar login e senha no Cookie
        if($deviceType == 'phone'){			
            $html = $this->view->render('index/listar-avaliacoes-aluno-mobile.phtml');
            die($html);
        }else {
            $this->_helper->viewRenderer->setNoRender();		
            $html = $this->view->render('index/listar-avaliacoes-aluno.phtml');
            echo $html;
        }
        
        
        #Utils_Print::printvardie($this->view->dadoAluno);     
    }
    
    public function verAvaliacaoFisicaAction(){        
        $this->isLogado();
        
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
            
            echo $this->view->render("index/ver-avaliacao-mobile.phtml");
        }else {        
            $this->_helper->layout->setLayout('ficha-aluno');	
            $this->_helper->viewRenderer->setNoRender();
            $html = $this->view->render('index/ver-avaliacao.phtml');
            echo $html;
        }
    }
    
    
    public function verExercicioAction() {
        $this->isLogado();
        
        //$this->_helper->layout->setLayout('ficha-aluno');
        $this->_helper->layout->setLayout('mobile');
        
        $exercicio = $this->_request->getParam('exercicio_id');
        
        $db = Zend_Db_Table::getDefaultAdapter();
        $sql = "SELECT e.* FROM exercicio e WHERE e.id = {$exercicio}";
        $aExercicio = $db->fetchRow($sql);
        
        #Utils_Print::printvardie($aExercicio);
        
        $this->view->url = $aExercicio['url'];
        $this->view->thumbnail= $aExercicio['thumbnail'];
        $this->view->descricao = $aExercicio['exercicio'];
        #Utils_Print::printvardie($this->view->dadoAluno);     
    }
    
    public function visualizarTreinoAction() {
        $this->isLogado();
        
        $this->_helper->layout->setLayout('ficha-aluno');
        
        $aluno_id = $this->_request->getParam('matricula');
        $ficha_id = $this->_request->getParam('ficha_id');
        
        $oAluno = new Model_Rule_Aluno();
        $resAluno = $oAluno->getAluno($aluno_id);


        #get Dados Treino Ficha
        $oTreino = new Model_Rule_MontarTreino();
        $aTreinoFicha = $oTreino->getDadosTreino($ficha_id);


        $this->view->dadosFicha = $oTreino->getDadosGeraisTreino($ficha_id);

        #$this->view->aPeriodizacao = $aPeriodizacao;
        $this->view->dadosAluno = $resAluno;
        
        #Utils_Print::printvardie($this->view->dadosAluno);
        
        $this->view->cond = $aTreinoFicha['cond'];
        $this->view->aTreinoFicha = $aTreinoFicha['results'];

        $this->view->ficha_id = $ficha_id;
        $this->view->aluno_id = $aluno_id;
        
        $oPeriodizacao = new Model_Rule_Periodizacao();
        $this->view->aPeriodizacao = $oPeriodizacao->getPeriodizacao($ficha_id);
        
        //Instancia a Classe para detectar acesso via Mobile
        $detect = new Model_Rule_MobileDetect;
                        
        //Identifica o tipe de Device acessado (computer, tablet ou phone)
        $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');

        //Se o acesso for via 'phone' setar login e senha no Cookie
        if($deviceType == 'phone'){
            $html = $this->view->render('index/visualizar-treino-mobile.phtml');
            die($html);
        }else {
            $this->_helper->viewRenderer->setNoRender();	
            $html = $this->view->render('index/visualizar-treino.phtml');
            echo $html;
        }
        
        #$nomeArq = 'Treino de '.$resAluno['nome'].'.pdf';
        #$mPDF->WriteHTML(utf8_encode($html));
        #$mPDF->Output($nomeArq, 'I');
    }
    
    public function impressaoAction() {
        $this->isLogado();
        
        #Utils_Print::printvardie();
        
        include $_SERVER['DOCUMENT_ROOT'].'/../library/mpdf/mpdf.php';
        
        $this->getHelper('layout')->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $mPDF = new mPDF('utf-8', 'A4');
        
        $aluno_id = $this->_request->getParam('matricula');
        $ficha_id = $this->_request->getParam('ficha_id');
        
        $oAluno = new Model_Rule_Aluno();
        $resAluno = $oAluno->getAluno($aluno_id);

        
        $oPeriodizacao = new Model_Rule_Periodizacao();
        $this->view->aPeriodizacao = $oPeriodizacao->getPeriodizacao($ficha_id);

        #get Dados Treino Ficha
        $oTreino = new Model_Rule_MontarTreino();
        $aTreinoFicha = $oTreino->getDadosTreino($ficha_id);


        $this->view->dadosFicha = $oTreino->getDadosGeraisTreino($ficha_id);
        


        #$this->view->aPeriodizacao = $aPeriodizacao;
        $this->view->dadosAluno = $resAluno;
        $this->view->cond = $aTreinoFicha['cond'];
        $this->view->aTreinoFicha = $aTreinoFicha['results'];

        $this->view->ficha_id = $ficha_id;
        $this->view->aluno_id = $aluno_id;
        
        //Instancia a Classe para detectar acesso via Mobile
        $detect = new Model_Rule_MobileDetect;
                        
        //Identifica o tipe de Device acessado (computer, tablet ou phone)
        $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');

        //Se o acesso for via 'phone' setar login e senha no Cookie
        $html = $this->view->render('index/imprimir-treino.phtml');
        //Utils_Print::printvardie($html);
        
        /*if($deviceType == 'phone'){			
            $html = $this->view->render('index/imprimir-treino-mobile.phtml');
        }else {
            $html = $this->view->render('index/imprimir-treino.phtml');
        }*/
        
        $nomeArq = 'Treino de '.$resAluno['nome'].'.pdf';
        $mPDF->WriteHTML(utf8_encode($html));
        $mPDF->Output($nomeArq, 'I');
        exit;
    }
    
    public function isLogado(){
        $auth = Zend_Auth::getInstance();
        if(!$auth->hasIdentity()){
            return $this->_helper->redirector('login', 'admin');
        }
    }

    public function renovacaoConcluidaAction()
    {
        $this->_helper->layout->setLayout('login');
    }

}