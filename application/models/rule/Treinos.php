<?php

class Model_Rule_Treinos extends Model_Rule_Abstract {

	private $_db;
	private $_dao;
    private $_sessao;

    public function __construct()
    {
        $this->_db = Zend_Db_Table::getDefaultAdapter();

        $this->_dao = new Model_Dao_Treino();

        $this->_sessao =  $_SESSION['usuario'];
    }

    public function salvar()
    {
    	$oTreino = new Model_Dao_Treino();
    	if(!$this->id) {
    		$oTreinoRow = $oTreino->createRow();
    	}else {
    		$oTreinoRow = @$oTreino->find($this->id)->current();
    	}
    	$oTreinoRow->fk_aluno = $this->aluno;
    	$oTreinoRow->fk_personal_academia = $this->_sessao->academia_id;
    	$oTreinoRow->dt_inicio = $this->dtInicio;
    	#$oTreinoRow->dt_termino = $this->dtTermino;
    	$oTreinoRow->objetivo = utf8_decode($this->objetivo);
    	$oTreinoRow->observacao = utf8_decode($this->observacao);
    	$oTreinoRow->created_at = date('Y-m-d H:i:s');
    	$oTreinoRow->save();
        return $oTreinoRow->id;
    }

    public function getAll()
    {
    	$data = [
		    't.id',
		    't.fk_aluno',
		    't.fk_professor',
		    't.fk_personal_academia',
		    'dt_inicio' => new Zend_Db_Expr("DATE_FORMAT(t.dt_inicio, '%d/%m/%Y')"),
		    #'dt_termino' => new Zend_Db_Expr("DATE_FORMAT(t.dt_termino, '%d/%m/%Y')"),
		    't.objetivo',
		    't.observacao',
		    'aluno' => 'a.nome',
		    'id_usuario' => 'u.id',
		    'u.email',
		    //'u.foto',
		    'u.filename'
    	];

    	$sql = $this->_db->select()
				->from(['t' => 'treinos'], $data)
    			->join(['a' => 'aluno'], 'a.id = t.fk_aluno', null)
    			->join(['u' => 'usuario'], 'u.id = a.fk_usuario', null)
    			->where('t.fk_personal_academia = ?', $this->_sessao->academia_id)
    			->order('t.id');

    	return $this->_db->fetchAll($sql);
    }

    public function getById($id)
    {
    	$data = [
		    't.id',
		    't.fk_aluno',
		    't.fk_professor',
		    't.fk_personal_academia',
		    'dt_inicio_us'  => 't.dt_inicio',
		    #'dt_termino_us' => 't.dt_termino',
		    'dt_inicio' => new Zend_Db_Expr("DATE_FORMAT(t.dt_inicio, '%d/%m/%Y')"),
		    'dt_termino' => new Zend_Db_Expr("DATE_FORMAT(t.dt_termino, '%d/%m/%Y')"),
		    't.objetivo',
		    't.observacao',
		    'aluno' => 'a.nome',
		    'id_usuario' => 'u.id',
		    'u.email',
		    //'u.foto',
		    'u.filename'
    	];

    	$sql = $this->_db->select()
				->from(['t' => 'treinos'], $data)
    			->join(['a' => 'aluno'], 'a.id = t.fk_aluno', null)
    			->join(['u' => 'usuario'], 'u.id = a.fk_usuario', null)
    			->where('t.id = ?', $id);

    	return $this->_db->fetchRow($sql);
    }

    public function getAllByIdAluno($id_aluno)
    {
        $data = [
            't.id',
            't.fk_aluno',
            't.fk_professor',
            't.fk_personal_academia',
            'pa.nome as personal_academia',
            'dt_inicio' => new Zend_Db_Expr("DATE_FORMAT(t.dt_inicio, '%d/%m/%Y')"),
            #'dt_termino' => new Zend_Db_Expr("DATE_FORMAT(t.dt_termino, '%d/%m/%Y')"),
            't.objetivo',
            't.observacao',
            'aluno' => 'a.nome',
            'id_usuario' => 'u.id',
            'u.email',
            //'u.foto',
            'u.filename'
        ];

        $sql = $this->_db->select()
                ->from(['t' => 'treinos'], $data)
                ->join(['a' => 'aluno'], 'a.id = t.fk_aluno', null)
                ->join(['u' => 'usuario'], 'u.id = a.fk_usuario', null)
                ->join(['pa' => 'personal_academia'], 'pa.id = t.fk_personal_academia', null)
                ->where('t.fk_aluno = ?', $id_aluno)
                ->order('t.id');

        return $this->_db->fetchAll($sql);
    }
    
    public function existePeriodizacao($id_treino)
    {
        $oPeriodizacao = new Model_Dao_Periodizacao();
        return $oPeriodizacao->fetchRow($oPeriodizacao->select()->where('fk_treino = ?', $id_treino));
    }

    public function validade($id_treino)
    {
        $oPeriodizacao = new Model_Dao_Periodizacao();

        $res = $oPeriodizacao->fetchRow($oPeriodizacao->select()
                        ->where('fk_treino = ?', $id_treino)
                        ->where('dt_fim is null')
                        ->order('secao ASC')
                        ->limit(1));

        if(!$res){
            
            if($this->existePeriodizacao($id_treino)) {
                $res = false;
            }else {
                $res = true;
            }
        }

        return $res;
    }

}