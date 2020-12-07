<?php

class Model_Rule_Aluno extends Model_Rule_Abstract {

    private $db;
    private $sessao;

    public function __construct()
    {
        $this->db = Zend_Db_Table::getDefaultAdapter();
        $this->sessao = $_SESSION['usuario'];
    }

    public function salvarAluno() {
        try{
            if(!$this->validaLimiteDeAlunos()) {
                return ['retorno' => 'falha', 'msg' => 'O seu plano não permite cadastrar mais que '.$_SESSION['usuario']->limite_alunos.' alunos. Assine um plano superior e cadastre muito mais.'];
            }


            if(!$this->id):
                //cadastrar aluno/usuário
                $id_usuario = $this->inserirAluno();

                //cadastrar perfil do usuário
                $this->inserirPerfilUsuario($id_usuario);
            else:
                //alterar aluno/usuário
                $id_usuario = $this->alterarAluno();
            endif;

            return array('retorno' => 'sucesso', 'msg' => 'Os dados do aluno foram salvos com sucesso.');
        }catch( Exception $e ){
            return array('retorno' => 'falha', 'msg' => 'Falha ao salvar o aluno. '. $e->getMessage());
        }
    }

    public function inserirAluno()
    {
        $oAlunoBD = new Model_Dao_Aluno();
        $oUsuario = new Model_Dao_Usuario();

        //SALVAR USUÁRIO
        $oUsuarioRow = $oUsuario->createRow();
        $oUsuarioRow->nome = utf8_decode($this->nm_aluno);
        $oUsuarioRow->email = $this->email;
        $oUsuarioRow->status = 1;
        if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) $oUsuarioRow->foto = $this->foto;
        if(isset($_FILES['type'])) $oUsuarioRow->type = $this->type;
        $oUsuarioRow->senha = md5($this->senhaCad);
        $oUsuarioRow->created_at = date('Y-m-d H:i:s');

        $id_usuario = $oUsuarioRow->save();


        //SALVAR ALUNO
        $oAlunoRow = $oAlunoBD->createRow();
        $oAlunoRow->nome = utf8_decode($this->nm_aluno);
        $oAlunoRow->fk_usuario = $id_usuario;
        $oAlunoRow->telefone = $this->telefone;
        $oAlunoRow->fk_personal_academia = $this->sessao->academia_id;
        $oAlunoRow->situacao = 1;
        $oAlunoRow->sexo = $this->sexo;
        $oAlunoRow->dt_nascimento = ($this->dtNascimento);
        $oAlunoRow->created_at = date('Y-m-d H:i:s');

        $id_aluno = $oAlunoRow->save();

        return $id_usuario;
    }

    public function alterarAluno()
    {
        $oAluno = new Model_Rule_Aluno();
        $oAlunoBD = new Model_Dao_Aluno();
        $oUsuario = new Model_Dao_Usuario();

        $aluno = $oAluno->getAluno($this->id);

        //ALTERAR USUÁRIO
        $oUsuarioRow = $oUsuario->find($aluno['id_usuario'])->current();
        $oUsuarioRow->nome = utf8_decode($this->nm_aluno);
        $oUsuarioRow->email = $this->email;
        $oUsuarioRow->status = 1;
        if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $oUsuarioRow->foto = $this->foto;
            $oUsuarioRow->type = $this->type;
            $oUsuarioRow->filename = $this->filename;
        }
        
        if($this->senhaCad) {
            $oUsuarioRow->senha = md5($this->senhaCad);
        }
        
        $oUsuarioRow->updated_at = date('Y-m-d H:i:s');

        $oUsuarioRow->save();
        
        //ALTERAR ALUNO
        $oAlunoRow = $oAlunoBD->find($this->id)->current();
        $oAlunoRow->nome = utf8_decode($this->nm_aluno);
        $oAlunoRow->fk_usuario = $aluno['id_usuario'];
        $oAlunoRow->telefone = $this->telefone;
        $oAlunoRow->fk_personal_academia = $this->sessao->academia_id;
        $oAlunoRow->situacao = $this->situacao;
        $oAlunoRow->sexo = $this->sexo;
        $oAlunoRow->dt_nascimento = ($this->dtNascimento);
        $oAlunoRow->updated_at = date('Y-m-d H:i:s');

        $oAlunoRow->save();

        return $oUsuarioRow->id;
    }

    public function inserirPerfilUsuario($id_usuario)
    {
        //SALVAR PERFÍL DO USUÁRIO
        $oUsuarioPerfilBD = new Model_Dao_UsuarioPerfil();
        $oUsuarioPerfilRow = $oUsuarioPerfilBD->createRow();
        $oUsuarioPerfilRow->fk_usuario = $id_usuario;
        $oUsuarioPerfilRow->fk_perfil = 5; //aluno
        $oUsuarioPerfilRow->created_at = date('Y-m-d H:i:s');
        $oUsuarioPerfilRow->save();
    }

    public function validaLimiteDeAlunos()
    {
        $total_alunos  = $this->getTotalAlunos();
        $limite_alunos = $_SESSION['usuario']->limite_alunos;
        return ($total_alunos < $limite_alunos ? true : false);
    }

    public function removerAluno($aluno_id) {
        try{
            $oAluno = new Model_Dao_Aluno();
            $oAlunoRow = $oAluno->find($aluno_id)->current();
            $oAlunoRow->delete();
            return array('retorno' => 'sucesso', 'msg' => 'Aluno removido com sucesso.');
        }catch( Exception $e ){
            return array('retorno' => 'falha', 'msg' => 'Falha ao remover o Aluno.');
        }
    }

    public function getAll()
    {
        $data = [
            'a.id',
            'a.nome',
            'a.telefone',
            'a.dt_nascimento',
            'a.sexo',
            'a.fk_usuario',
            'a.fk_personal_academia',
            'a.situacao',
            'u.email',
            'u.filename'
        ];

        $sql = $this->db->select()
                    ->from(['a' => 'aluno'], $data)
                        ->join(['u' => 'usuario'], 'u.id = a.fk_usuario', null)
                        ->where("fk_personal_academia = ?", $this->sessao->academia_id)
                        ->order(["a.situacao", "a.nome"]);

        return $this->db->fetchAll($sql);
    }

    public function getAluno($id) {
        $query = $this->db->select()->from(['a' => 'aluno'], ['a.id','a.nome','a.telefone', 'a.dt_nascimento','a.sexo','a.fk_usuario','a.fk_personal_academia', 'a.situacao', 'a.created_at'])
                        ->join(['u' => 'usuario'], 'u.id = a.fk_usuario', ['id_usuario' => 'u.id', 'u.email', 'u.filename', 'u.type'])
                    ->where("a.id = ?", $id);

        #Utils_Print::printvardie($query->__toString());
        return $this->db->fetchRow($query);
    }

    public function getAlunoPorEmail($email) {
        $query = $this->db->select()
                        ->from(['a' => 'aluno'], 'a.*')
                        ->join(['u' => 'usuario'], 'u.id = a.fk_usuario', ['u.email'])
                        ->where("u.email like '$email'");
        return $this->db->fetchRow($query);
    }

    public function getAlunosPC() {
        $query = $this->db->select()->from("aluno")
         		    ->where("fk_personal_academia = {$this->sessao->academia_id}")
        		      ->order("nome");

        return $this->db->fetchAll($query);
    }

    public function getAlunos() {
        $query = $this->db->select()->from(['a' => 'aluno'], ['a.id','a.nome','a.telefone','a.dt_nascimento','a.sexo','a.fk_usuario','a.fk_personal_academia', 'a.situacao'])
                        ->join(['u' => 'usuario'], 'u.id = a.fk_usuario', ['u.email', 'u.type', 'u.filename'])
         		         ->where("fk_personal_academia = {$this->sessao->academia_id}")
        		          ->order(["a.situacao", "a.nome"]);

        $aAlunos = $this->db->fetchAll($query);

        $total = count($aAlunos);

        return array('results' => $aAlunos, 'total' => $total);
    }

    public function validaUsuarioExistente($email) {
        $query = $this->db->select()->from(['a' => 'aluno'], 'a.*')
                        ->join(['u' => 'usuario'], 'u.id = a.fk_usuario', 'u.email')
                         ->where("u.email = '{$email}'");

        return $this->db->fetchRow($query);
    }

    public function getTotalAlunos() {
        $query = $this->db->select()->from("aluno", ['total' => new Zend_db_Expr("count(id)")])
                         ->where("fk_personal_academia = {$this->sessao->academia_id}");

        $result = $this->db->fetchRow($query);

        return $result['total'];
    }
}