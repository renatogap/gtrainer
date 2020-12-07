<?php

class Model_Rule_Autenticacao extends Model_Rule_Abstract {

	private $db;

	public function __construct(){
        $this->db = Zend_Db_Table::getDefaultAdapter();
	}

    public function autenticaUsuario(){

	    try{
            $login = $this->login;
            $senha = $this->senha;

            $dbAdapter = Zend_Db_Table::getDefaultAdapter();


            //Inicia o adaptador Zend_Auth para banco de dados
            $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
            $authAdapter->setTableName('usuario')
                    ->setIdentityColumn('email')
                    ->setCredentialColumn('senha')
                    ->setCredentialTreatment('md5(?)');

            //Define os dados para processar o login
            $authAdapter->setIdentity($login)
                    ->setCredential($senha);

            //Efetua o login
            $auth = Zend_Auth::getInstance();

            $result = $auth->authenticate($authAdapter);

            $acesso = null;

            //Verifica se o login foi efetuado com sucesso
            if ($result->isValid()) {
                //Recupera o objeto do usuário, sem a senha
                $info = $authAdapter->getResultRowObject(null, ['senha', 'foto']);

                if($info->status == 0){
                     return array('retorno' => 'erro', 'msg' => "Seu usuário ainda está inativo. Renove seu plano para poder utilizar o Gtrainer.");
                }

                $userPerfil = new Model_Dao_UsuarioPerfil();
                $sql1 = $userPerfil->select()->where('fk_usuario = ?', $info->id);
                $aPerfilUsuario = $userPerfil->fetchRow($sql1);
                #$aPerfilUsuario = $userPerfil->fetchAll($sql1);

                $info->perfil_id = $aPerfilUsuario->fk_perfil;

                //Se o usuário logando  for  personal ou academia
                if($aPerfilUsuario->fk_perfil == 1 || $aPerfilUsuario->fk_perfil == 2 || $aPerfilUsuario->fk_perfil == 3) {
                	$personalAcademia = new Model_Dao_PersonalAcademia();
	                $sql = $personalAcademia->select()->where('fk_usuario = ?', $info->id);
	                $aPersonalAcademia = $personalAcademia->fetchRow($sql);

	                if(!$aPersonalAcademia) {
	                	return ['retorno'=>'erro', 'msg'=>"Não foi possível encontrar o personal. Tente novamente mais tarde"];
	                }

	                $info->academia_id   = $aPersonalAcademia->id;
					$info->academia_nome = $aPersonalAcademia->nome;
					//$info->academias     = $aAcademias;

					//Se o perfil_id for diferente de Superusuário, Instrutor (Professor) e Aluno
                    //Pegar a qtd de dias de acesso do usuário logado
                    //Caso seja < 0, seu acesso expirou.
					$acesso = $this->getDiasAcesso($info->id);

                    //Se $acesso for vazio, significa que a academia ainda está inativa
                    //Talvez, aguardando pagamento
                    if($acesso){
                    	$info->plano_id = $acesso['plano_id'];

                    	$oPlano    = new Model_Dao_Plano();
                    	$oPlanoRow = @$oPlano->find($acesso['plano_id'])->current();
                    	$info->plano_nome = $oPlanoRow->nome;
			            $info->limite_alunos = $oPlanoRow->limite_alunos;
			            $info->limite_dias = $oPlanoRow->limite_dias;

                        //Se a qtd de Dias de acesso for <= 7 e > 0
                        /*if($acesso['dias'] >= 0 && $acesso['dias'] <= 7) {
                            return array('retorno' => 'info', 'msg' => "Faltam {$acesso['dias']} dias para expirar seu acesso.", 'url' => '../admin/home');

                        }else*/

                        if($acesso['dias'] < 0) {

                            //Na tela de renovar plano é necessário a sessão
                            $_SESSION['usuario'] = $info;
                            setcookie( "login", $login, strtotime( '+365 days' ), '/' );
                            setcookie( "senha", base64_encode($senha), strtotime( '+365 days' ), '/' );
                            setcookie( "usuario_id", $info->id, strtotime( '+365 days' ), '/' );

                            $session = new Zend_Session_Namespace( 'Zend_Auth' );
                            $session->setExpirationSeconds( strtotime( '+1 days' ) );

                            return ['retorno' => 'erro', 'url' => '../admin/renovar-plano'];
                        }
                    }

                }
                else if($aPerfilUsuario->fk_perfil == 5) {
                	$oAluno = new Model_Rule_Aluno();
                    $aAluno = $oAluno->getAlunoPorEmail($info->email);

                    $info->aluno_id = $aAluno['id'];
                    $info->aluno_nome = $aAluno['nome'];
                    $info->academia_id = $aAluno['fk_personal_academia'];

                    $_SESSION['usuario'] = $info;
                    setcookie( "login", $login, strtotime( '+365 days' ), '/' );
                    setcookie( "senha", base64_encode($senha), strtotime( '+365 days' ), '/' );
                    setcookie( "usuario_id", $info->id, strtotime( '+365 days' ), '/' );

                    $session = new Zend_Session_Namespace( 'Zend_Auth' );
                    $session->setExpirationSeconds( strtotime( '+1 days' ) );
                    
                    //Grava o log de acesso para o Aluno
                    $this->saveLogAcesso($info);

                    //return array('retorno' => 'aluno', 'url' => '../area-aluno/'.$info->email);
                    return array('retorno' => 'aluno', 'url' => '../aluno');
                }

                $storage = $auth->getStorage();
                $storage->write($info);

                //Atribui os dados do usuário na Sessão
                $_SESSION['usuario'] = $info;


                $deviceType = Utils_File::verificaDispositivo();
                //Se o acesso for via 'phone' setar login e senha no Cookie
                if($deviceType == 'phone'){
                    // seta o tempo do Cookie
                    setcookie( "login", $login, strtotime( '+365 days' ), '/' );
                    setcookie( "senha", base64_encode($senha), strtotime( '+365 days' ), '/' );
                    setcookie( "usuario_id", $info->id, strtotime( '+365 days' ), '/' );
                }
                ///////////////////////////////////////////////////////////
                //COMENTAR ESSE ELSE QUANDO FOR PARA O PRODUÇÃO
                ///////////////////////////////////////////////////////////
                else {
                    setcookie( "login", $login, strtotime( '+365 days' ), '/' );
                    setcookie( "senha", base64_encode($senha), strtotime( '+365 days' ), '/' );
                    setcookie( "usuario_id", $info->id, strtotime( '+365 days' ), '/' );
                }

                $session = new Zend_Session_Namespace( 'Zend_Auth' );
                $session->setExpirationSeconds( strtotime( '+1 days' ) );
                
                // Grava o Log de acesso para o Personal
                $this->saveLogAcesso($info);

                return ['retorno' => 'sucesso', 'msg' => 'Usuário autenticado com sucesso'];

            }else {
                return ['retorno' => 'erro', 'msg' => 'Usuário ou senha incorreta'];
            }
	    }
	    catch(Exception $ex){
	     	return array('retorno' => 'erro', 'msg' => 'Falha na autenticação. '.$ex->getMessage());
	    }
	}
	
	public function saveLogAcesso($p)
	{
	    $log = new Model_Dao_LogAcesso();
	    $logRow = $log->createRow();
	    $logRow->fk_personal_academia =  $p->academia_id;
		$logRow->usuario =  $p->nome;
		$logRow->perfil =  ($p->perfil_id == 2 ? 'Personal' : ($p->perfil_id == 5 ? 'Aluno' : 'Desconhecido'));
		$logRow->pagina = 'Login';
		$logRow->dt_acesso = date('Y-m-d H:i:s');
		$logRow->save();
	    
	}

    public function getDiasAcesso($usuario_id) {
        $dtAtual = date('Y-m-d');

    	$query = "SELECT DATEDIFF(pu.dt_fim , '$dtAtual') as dias,
                         pu.fk_plano as plano_id
                    FROM usuario_plano pu
                    WHERE pu.`fk_usuario` = $usuario_id
                          and pu.`status` = 1
                    ORDER BY pu.dt_fim DESC
                    LIMIT 1";


    	try{
    	    $results = $this->db->fetchRow($query);
    	} catch(Exception $e){
    	    return false;
    	}
    	return $results;
    }

        public function getAcademiasUsuario($usuario_id=null) {
        	$query = "SELECT DISTINCT a.id, a.nm_academia, a.logo
        		  FROM usuario u
        		  JOIN academia_usuario au ON au.usuario_id = u.id
        		  JOIN academia a ON au.academia_id = a.id ";
        		  
        	if($usuario_id != null){
        	    $query .= " WHERE au.usuario_id = {$usuario_id} ";
        	}
        	
        	$query .= " ORDER BY a.nm_academia";
        		  
        	try{	  
        	    $aAcademias = $this->db->fetchAll($query);   
        	     	    
        	} catch(Exception $e){
        	    return false;
        	}
        	
        	return $aAcademias;
        }
                
	
	public function save(){
		
		try{
			$usuario = $this->_entity->createRow();
			
			$usuario->login = $this->login;
			$usuario->email = $this->email;
			$usuario->senha = sha1($this->senha);
			$usuario->save();
		}
		catch(Exception $e){
			throw new Exception($e);
		}

		return array("success" => true, "id" => $usuario->id, "msg" => "Usuário cadastrado com sucesso!");
	}

        public function getDadosUsuario() {
            $oUsuario = new Model_Dao_Usuario();
            $sql = $oUsuario->select()->where('login like ?', $this->login);
            $dados = $oUsuario->fetchAll($sql)->toArray();
            return $dados;
        }

}