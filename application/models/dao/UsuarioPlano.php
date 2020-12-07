<?php

class Model_Dao_UsuarioPlano extends Zend_Db_Table{
	protected $_name = "usuario_plano";
	protected $_primary = 'id';

	public static function historicoDePlanosDoUsuario($id_usuario)
	{
		$data = [
			'p.id',
			'plano' 		=> 'p.nome',
			'limite_dias'	=> 'p.limite_dias',
			'limite_alunos' => 'p.limite_alunos',
			'dt_inicio' 	=> new Zend_Db_Expr("DATE_FORMAT(dt_inicio, '%d/%m/%Y')"), 
			'dt_fim' 		=> new Zend_Db_Expr("DATE_FORMAT(dt_fim, '%d/%m/%Y')"), 
			'status'		=> new Zend_Db_Expr("CASE WHEN dt_fim >= CURRENT_DATE THEN 'Ativo' ELSE 'Expirado' END"),
			'situacao',
			'ds_situacao'	=> new Zend_Db_Expr("CASE WHEN situacao = 'AT' THEN 'Ativo' WHEN situacao = 'AP' THEN 'Aguard. Pagamento' WHEN situacao = 'EX' THEN 'Experimental' WHEN situacao = 'IN' THEN 'Inativo' END"),
		];

		$db = Zend_Db_Table::getDefaultAdapter();
		$sql = $db->select()->from(['up' => 'usuario_plano'], $data)
			->join(['p' => 'plano'], 'p.id = up.fk_plano', 'p.nome as plano')
			->where('up.fk_usuario = ?', $id_usuario)
			->order('up.dt_inicio DESC');

		$historicoPlanosUsuario = $db->fetchAll($sql);
		return $historicoPlanosUsuario;
	}
}