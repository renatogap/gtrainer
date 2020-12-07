<?php

class Model_Rule_Usuario extends Model_Rule_Abstract {
	
    private $db;

    public function __construct(){
        $this->db = Zend_Db_Table::getDefaultAdapter();
    }
    
    public function updateUsuario() {        
        if(!$this->nome) 
            return array('retorno' => 'falha', 'msg' => 'O campo "Nome" é obrigatório.'); else
        if(!$this->email) 
            return array('retorno' => 'falha', 'msg' => 'O campo "Email" é obrigatório.');
        
        try{

            //Alterar Usuário...
            $this->alterarUsuario();

            //Alterar Tipo de Usuário (Personal ou Academia)...
            //Caso não seja (2) e (3), é pq é um Instrutor (4) ou Aluno (5), 
            //Logo, pega-se o código da academia do professor na Sessão
            if($this->rdTipo == 2):
                $this->alterarPersonal();
            elseif($this->rdTipo == 3):
                $this->alterarAcademia();
            endif;

        }catch( Exception $e ){
            return array('retorno' => 'erro', 'msg' => $e->getMessage());
        }

        return array('retorno' => 'sucesso', 'msg' => 'Dados do usuário alterados com sucesso.');
    }

    public function cadastrarUsuario() {
        //if(!$this->rdTipo) return array('retorno' => 'falha', 'msg' => 'Informe o Tipo de Usuário.'); else
        if(!$this->nome) return array('retorno' => 'falha', 'msg' => 'O campo "Nome" é obrigatório.'); else
        if(!$this->email) return array('retorno' => 'falha', 'msg' => 'O campo "Email" é obrigatório.'); else

        try{
            $resUsuario = null;
            $oUsuario = new Model_Dao_Usuario(); 
            $oUsuarioExiste = $oUsuario->fetchRow($oUsuario->select()->where("email like '{$this->email}%'"));

            if( $oUsuarioExiste ) {
                if($this->rdTipo != 1 || $this->rdTipo != 2 || $this->rdTipo != 3) {
                    return array('retorno' => 'falha', 'msg' => "O usuário com o Email '{$this->email}' já foi cadastrado.");
                }
            }else {
                $oUsuario = new Model_Dao_Usuario();
                $oUsuarioRow = $oUsuario->createRow();

                $oUsuarioRow->nome = utf8_decode($this->nome);
                $oUsuarioRow->email = $this->email;
                $oUsuarioRow->senha = md5($this->senhaCad);
                $oUsuarioRow->status = 1;
                $oUsuarioRow->created_at = date('Y-m-d H:i:s');

                if(isset($_FILES['foto']) && !empty($_FILES['foto'])){
                    $oUsuarioRow->foto = $this->getBinarioImage();
                }

                $oUsuarioRow->save();

                $id_usuario = $oUsuarioRow->id;

            }


            $dtInicio = new DateTime(date('Y-m-d H:i:s'));
            $dtTermino = clone $dtInicio;
            $dtTermino->add(new DateInterval('P30D'));

            $oPlano = new Model_Dao_UsuarioPlano();
            $oPlanoRow = $oPlano->createRow();
            $oPlanoRow->fk_usuario = $id_usuario;
            $oPlanoRow->fk_plano = $this->cbTipoPlano;
            $oPlanoRow->dt_inicio = $dtInicio->format('Y-m-d');
            $oPlanoRow->dt_fim = $dtTermino->format('Y-m-d');
            $oPlanoRow->status = 1;
            $oPlanoRow->situacao = 'EX';
            $oPlanoRow->save();


            $oPerfil = new Model_Dao_UsuarioPerfil();
            $oPerfilRow = $oPerfil->createRow();
            $oPerfilRow->fk_usuario = $id_usuario;
            $oPerfilRow->fk_perfil = $this->rdTipo; //Personal
            $oPerfilRow->created_at = date('Y-m-d H:i:s');
            $oPerfilRow->save();


            //salvar na tabela personal_academia
            $resTipoUsuario = $this->salvarPersonal($id_usuario);

            if($this->rdTipo != 4 && $this->rdTipo != 5){

                $this->copyImages($resTipoUsuario['id']);

            }

            #Notificação de novo cadastro para o GTrainer
            $dataNotificacao = array(
                'from' => $this->email,
                'nome' => $this->nome,
                'to' => 'contato@gtrainer.com.br',
                'assunto' => "{$this->nome} se cadastrou no GTrainer",
                'mensagem' => "Novo cadastro feito no GTrainer.<br>{$this->nome} solicitou o plano {$this->cbTipoPlano}"
            );

	    #Enviar email de notificação para o GTrainer
            Utils_Mail::enviarEmail($dataNotificacao);

        }catch( Exception $e ){
            return array('retorno' => 'erro', 'msg' => $e->getMessage());
        }

        return array('retorno' => 'sucesso', 'msg' => 'Usuário cadastrado com sucesso.', 'academia_id' => $resTipoUsuario['id'], 'perfil_id' => $this->rdTipo, 'tipo_plano' => $this->cbTipoPlano);
    }

    public function copyImages($personal_academia_id) 
    {
        #Salvar o Grupo
        $oGrupo = new Model_Dao_Grupo();
        $aGrupo = $oGrupo->fetchAll($oGrupo->select()->where('fk_personal_academia = ?', 1));

        if(count($aGrupo) > 0) {
            foreach ($aGrupo as $key => $grupo) {
                $oGrupoRow = $oGrupo->createRow();
                $oGrupoRow->id_grupo = $grupo['id_grupo'];
                $oGrupoRow->nome = $grupo['nome'];
                $oGrupoRow->fk_personal_academia = $personal_academia_id;
                $oGrupoRow->created_at = date('Y-m-d H:i:s');
                $oGrupoRow->save();
            }
        }

        #Salvar o Grupo Muscular
        $oGrupoMuscular = new Model_Dao_GrupoMuscular();
        $aGrupoMuscular = $oGrupoMuscular->fetchAll($oGrupoMuscular->select()->where('fk_personal_academia = ?', 1));

        if(count($aGrupoMuscular) > 0) {
            foreach ($aGrupoMuscular as $key => $grupoMusc) {
                $oGrupoMuscularRow = $oGrupoMuscular->createRow();
                $oGrupoMuscularRow->id_grupo_muscular = $grupoMusc['id_grupo_muscular'];
                $oGrupoMuscularRow->nome = $grupoMusc['nome'];
                $oGrupoMuscularRow->fk_personal_academia = $personal_academia_id;
                $oGrupoMuscularRow->created_at = date('Y-m-d H:i:s');
                $oGrupoMuscularRow->save();
            }
        }

        #Salvar Exercícios
        $oExercicio = new Model_Dao_Exercicio();
        $sql = $oExercicio->select()->where('fk_personal_academia = ?', 1)->order('id');
        $aExercicios = $oExercicio->fetchAll($sql);

        if(count($aExercicios) > 0) {
            #$grupoRow = $oGrupo->fetchRow($oGrupo->select()
            #                                    ->where('fk_personal_academia = ?', $personal_academia_id)
            #                                    ->where('nome like ?', 'Muscula%'));

            foreach ($aExercicios as $key => $ex) {
                $newExercicio = $oExercicio->createRow();
                $newExercicio->nome = $ex['nome'];
                $newExercicio->fk_grupo = $ex['fk_grupo'];
                #$newExercicio->fk_grupo = $grupoRow->id;
                $newExercicio->fk_grupo_muscular = $ex['fk_grupo_muscular'];
                $newExercicio->fk_personal_academia = $personal_academia_id;
                $newExercicio->foto = $ex['foto'];
                $newExercicio->created_at = date('Y-m-d H:i:s');
                $newExercicio->save();
            }
        }
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

    public function salvarUsuario() {
        try{
            $oUsuario = new Model_Dao_Usuario();
            $oUsuarioRow = $oUsuario->createRow();

            $oUsuarioRow->nome = utf8_decode($this->nome);
            $oUsuarioRow->email = $this->email;
            $oUsuarioRow->senha = md5($this->senhaCad);
            $oUsuarioRow->status = 1;
            $oUsuarioRow->created_at = date('Y-m-d H:i:s');

            if(isset($_FILES['foto']) && !empty($_FILES['foto'])){
                $oUsuarioRow->foto = $this->getBinarioImage();
            }

            $oUsuarioRow->save();


            if($this->cbTipoPlano){
                $oPlano = new Model_Dao_UsuarioPlano();
                $oPlanoRow = $oPlano->createRow();
                $oPlanoRow->fk_usuario = $usuario_id;
                $oPlanoRow->fk_plano = $this->cbTipoPlano;
                $oPlanoRow->save();
            }


            $oPerfil = new Model_Dao_UsuarioPerfil();
            $oPerfilRow = $oPerfil->createRow();
            $oPerfilRow->fk_usuario = $usuario_id;
            $oPerfilRow->fk_perfil = $this->rdTipo; //Personal
            $oPerfilRow->save();

            return array('retorno' => 'sucesso', 'id' => $oUsuarioRow->id, 'msg' => 'Usuário cadastrado com sucesso.');
        }catch( Exception $e ){
            throw new Exception('Falha ao salvar o usuário. '.$e->getMessage());
        }
    }

    public function alterarSenha()
    {
        try{
            $oUsuario = new Model_Dao_Usuario();
            $oUsuarioRow = @$oUsuario->find($_SESSION['usuario']->id)->current();

            if(!$this->senhaAtual){
                return array('retorno' => 'falha', 'msg' => 'Informe a senha atual.');
            }

            if( $oUsuarioRow->senha != md5($this->senhaAtual) ){
                return array('retorno' => 'falha', 'msg' => 'A senha atual está inorreta.');
            }

            if(!$this->novaSenha){
                return array('retorno' => 'falha', 'msg' => 'Informe a nova senha.');
            }

            if( strlen($this->novaSenha) < 6 ){
                return array('retorno' => 'falha', 'msg' => 'A "Nova Senha" deve conter no mínimo 6 caracteres.');
            }

            $espacamento = count(explode(" ", $this->novaSenha));
            if( $espacamento > 1 ){
                return array('retorno' => 'falha', 'msg' => 'A "Nova Senha" não pode ter espaços em branco.');
            }

            if(!$this->confirmaNovaSenha){
                return array('retorno' => 'falha', 'msg' => 'Confirme a nova senha.');
            }

            if( $this->confirmaNovaSenha != $this->novaSenha ){
                return array('retorno' => 'falha', 'msg' => 'A confirmação da senha está difernte da nova senha informada.');
            }

            $oUsuarioRow->senha = md5($this->novaSenha);

            $oUsuarioRow->save();

            return array('retorno' => 'sucesso', 'msg' => 'A sua senha foi alterada com sucesso.');

        }catch( Exception $e ){
            return array('retorno' => 'erro', 'msg' => 'Falha ao trocar a senha. '.$e->getMessage());
        }
    }

    public function salvarSolicitacaoSenha()
    {
        $oUsuario = new Model_Rule_Usuario();
        $usuario = $oUsuario->getUsuariosPorEmail($this->email);

        if(!$usuario){
            throw new Exception("O endereço de e-mail informado não existe no sistema.\nVerifique se o e-mail está correto.");
        }

        $oUsuarioDao = new Model_Dao_Usuario();
        $oUsuarioDao->update(['redefinir_senha' => md5($usuario->id)], 'id='.$usuario->id);

        $user = @$oUsuarioDao->find($usuario->id)->current();

        $data = [
            'from'    => 'contato@gtrainer.com.br',
            'to'      => $this->email,
            'nome'    => utf8_encode($usuario->nome),
            'assunto' => 'Gtrainer - Redefinir senha',
            'mensagem' => 'http://localhost/gtrainer/public/admin/trocar-senha?key='.$user->redefinir_senha
        ];

        Utils_Mail::enviarEmail($data);
    }

    public function salvarPlanoAcademia($academia_id){
        try{
            $oPlano = new Model_Dao_PlanoAcademia();
            $oPlanoRow = $oPlano->createRow();
            
            $oPlanoRow->academia_id = $academia_id;
            $oPlanoRow->plano_id = $this->cbTipoPlano;
            
            //Default 30 dias (Plano Experimental)
            $dias = 30;
            
            if( $this->cbTipoPlano == 6 ): 
                $dias = 180; 
            elseif( $this->cbTipoPlano == 12 ): 
                $dias = 365;
            endif;
            
            $dt_fim = date('Y-m-d', mktime(date('H'), date('i'), date('s'), date('m'), (date('d') + $dias ), date('Y')));
            
            $oPlanoRow->dt_fim = $dt_fim;
            
            //Se Tipo de Plano escolhido for 1 (Plano Experimental)
            //Deixar ativo para login
            if( $this->cbTipoPlano == 1 ){
                $oPlanoRow->status = 1;
                $oPlanoRow->situacao = 'EX';
            }
            
            //Senão, deixar inativo e só ativar depois do pagamento
            else {
                $oPlanoRow->status = 0;
                $oPlanoRow->situacao = 'AP';
            }
            
            $oPlanoRow->save();
        }catch( Exception $e ){
            throw new Exception('Falha ao salvar o plano do usuário. '.$e->getMessage());
        }
        
        return array('retorno' => 'sucesso', 'msg' => 'Plano cadastrado com sucesso.');
    }
    
    public function salvarAcademia(){
        try{
            $oAcademia = new Model_Dao_Academia();
            $oAcademiaRow = $oAcademia->createRow();
            
            $deviceType = Utils_File::verificaDispositivo();

            //Se o acesso for via 'phone' setar login e senha no Cookie
            if($deviceType == 'phone'){
                $oAcademiaRow->nm_academia = utf8_decode($this->nome);
            }else {
                $oAcademiaRow->nm_academia = utf8_decode($this->nome);
            }

            $oAcademiaRow->save();
        }catch( Exception $e ){
            throw new Exception('Falha ao salvar a academia. '.$e->getMessage() );
        }

        return array('retorno' => 'sucesso', 'id' => $oAcademiaRow->id, 'msg' => 'Academia cadastrada com sucesso.');
    }

    public function salvarPersonal($usuario_id){
        try{
            $oPersonal = new Model_Dao_PersonalAcademia();
            $oPersonalRow = $oPersonal->createRow();
            $oPersonalRow->nome = utf8_decode($this->nome);
            $oPersonalRow->fk_usuario = $usuario_id;
            $oPersonalRow->created_at = date('Y-m-d H:i:s');
            $oPersonalRow->save();

            return array('retorno' => 'sucesso', 'id' => $oPersonalRow->id, 'msg' => 'Personal cadastrado com sucesso.');
        }catch( Exception $e ){
            throw new Exception('Falha ao salvar o Personal. '.$e->getMessage());
        }
    }

    public function salvarAcademiaUsuario($usuario_id, $academia_id) {
        try{
            $oAcadUser = new Model_Dao_AcademiaUsuario();
            $oAcadUserRow = $oAcadUser->createRow();
            
            $oAcadUserRow->academia_id = $academia_id;
            $oAcadUserRow->usuario_id = $usuario_id;            
            $oAcadUserRow->save();
        }catch( Exception $e ){
            throw new Exception('Falha ao salvar. '.$e->getMessage());
        }
        
        return $oAcadUserRow->academia_id;        
    }
    
    
    public function alterarUsuario() {
        
        try{
            $oUsuario = new Model_Dao_Usuario();
            
            $oUsuarioRow = $oUsuario->fetchAll($oUsuario->select()->where("email like '{$this->emailCompara}%'"))->current();                        
            #Utils_Print::printvardie($oUsuarioRow);
            
            #$oUsuarioRow = $oUsuario->find($_SESSION['usuario']->id)->current();
            
            $oUsuarioRow->nome = utf8_decode($this->nome);
            $oUsuarioRow->email = $this->email;
	    $oUsuarioRow->foto = (isset($this->foto))? $this->foto: null;
            
            $oUsuarioRow->save();
            
        }catch( Exception $e ){
            throw new Exception('Falha ao alterar os dados do usuário.');
        }
        return array('retorno' => 'sucesso', 'msg' => 'Dados do usuário alterado com sucesso.');
    }
    
    public function alterarAcademia(){
        try{
            $oAcademia = new Model_Dao_Academia();
            $oAcademiaRow = $oAcademia->find($_SESSION['usuario']->academia_id)->current();            
            
            $oAcademiaRow->nm_academia = ($this->nome);
            if(isset($this->foto)){
				if($this->foto){
					$oAcademiaRow->logo = $this->foto;
				}
            }
            
            $oAcademiaRow->save();
        }catch( Exception $e ){
            throw new Exception('Falha ao alterar os dados da academia. '.$e->getMessage() );
        }
        
        return array('retorno' => 'sucesso', 'id' => $oAcademiaRow->id, 'msg' => 'Dados da Academia alterado com sucesso.');
    }
    
    public function alterarPersonal(){
        try{
            $oAcademia = new Model_Dao_Academia();
            $oAcademiaRow = $oAcademia->find($_SESSION['usuario']->academia_id)->current();   
            
            $oAcademiaRow->nm_academia = ($this->nome);
            if(isset($this->foto)){
				if($this->foto){
					$oAcademiaRow->logo = $this->foto;
				}
            }
            
            $oAcademiaRow->save();
        }catch( Exception $e ){
            throw new Exception('Falha ao salvar o Personal. '.$e->getMessage());
        }
        
        return array('retorno' => 'sucesso', 'id' => $oAcademiaRow->id, 'msg' => 'Personal cadastrada com sucesso.');
    }


    public function removerUsuario() {
        try{
            $oUsuario = new Model_Dao_Usuario();
            $oUsuarioRow = $oUsuario->fetchAll($oUsuario->select()->where("email like '{$this->email}%'"))->current(); 
            #$oUsuarioRow = $oUsuario->find($this->id)->current();

            $oUsuarioRow->delete();
        }catch( Exception $e ){
            throw new Exception('Falha ao remover este professor. '.$e->getMessage());
        }
        return array('retorno' => 'sucesso', 'msg' => ('Músculo removido com sucesso.') );
    }

    public function getUsuario($id) {
        $oUsuario = new Model_Dao_Usuario();
        $oUsuarioRow = $oUsuario->find($id)->current();
        return $oUsuarioRow;
    }

    public function getUsuarios() {
        $oUsuario = new Model_Dao_Usuario();
        $query = $oUsuario->select()->from("musculo")->order("musculo");
        $aUsuarios = $oUsuario->fetchAll($query);

        $total = $aUsuarios->count();

        return array('results' => $aUsuarios, 'total' => $total);
    }


    public function getUsuariosPorEmail($email) {
        $oUsuario = new Model_Dao_Usuario();
        $query = $oUsuario->select()
                    ->from("usuario", ['id', 'nome', 'email', 'filename', 'type'])
                        ->where("email like '$email'");
        $result = $oUsuario->fetchRow($query);
        return $result;

    }
}