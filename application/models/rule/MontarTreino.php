<?php

class Model_Rule_MontarTreino extends Model_Rule_Abstract {
	
    private $db;

    public function __construct(){
        $this->db = Zend_Db_Table::getDefaultAdapter();
    }
    
    public function adicionaCarga() {
        $oTreinoCarga = new Model_Dao_TreinoCarga();
        $oTreinoCargaRow = $oTreinoCarga->createRow();
        
        try{
            $oTreinoCargaRow->treino_id = $this->P_treino_id;
            $oTreinoCargaRow->carga = $this->carga;
            $oTreinoCargaRow->save();
        }catch(Exception $ex){
            return array('retorno' => 'erro', 'msg' => 'Erro ao salvar a carga. '.$ex->getMessage());
        }
        
        return array('retorno' => 'sucesso', 'msg' => 'Carga adicionada a ficha.');
    }
    
    public function removerTreino($treino_id) {
        try{
            $oTreino = new Model_Dao_Treino();
            $oTreinoRow = $oTreino->find($treino_id)->current();
            $oTreinoRow->delete();
        }catch(Exception $ex){
            return array('retorno' => 'erro', 'msg' => 'Erro ao remover este exercício da ficha. '.$ex->getMessage());
        }
        
        return array('retorno' => 'sucesso', 'msg' => 'O Exercício foi removido da ficha com sucesso.');
    }
    
    public function removerCarga($carga_id) {
        try{
            $oTreinoCarga = new Model_Dao_TreinoCarga();
            $oTreinoCargaRow = $oTreinoCarga->find($carga_id)->current();
            $oTreinoCargaRow->delete();
        }catch(Exception $ex){
            return array('retorno' => 'erro', 'msg' => 'Erro ao remover a carga. '/*.$ex->getMessage()*/);
        }
        
        return array('retorno' => 'sucesso', 'msg' => 'Carga removida com sucesso.');
    }
    
    public function alunoPossuiFicha($aluno_id) {
        $oTreino = new Model_Dao_Ficha();
        $aFicha = $oTreino->fetchAll($oTreino->select()->where("aluno_id = $aluno_id"));
        if($aFicha->count()) 
            return true;
        else return false;
    }
    
    public function getTreinos($aluno_id) {
        
        $sql = $this->db->select()
                    ->from(array('f' => 'ficha'), 'f.*')
                ->join(array('a' => 'aluno'), 'a.matricula = f.aluno_id', 'a.nome')
                ->joinLeft(array('p' => 'professor'), 'p.id = f.professor_id', 'p.nm_professor')
                ->where('f.aluno_id = '.$aluno_id);
                
        $row = $this->db->fetchRow($sql);
        
        if( !$row['professor_id'] ){
            $sql = $this->db->select()
                    ->from(array('f' => 'ficha'), 'f.*')
                        ->join(array('a' => 'aluno'), 'a.matricula = f.aluno_id', 'a.nome')
                        ->join(array('ac' => 'academia'), 'ac.id = f.academia_id', 'ac.nm_academia as nm_professor')
                    ->where('f.aluno_id = '.$aluno_id);            
        }
                
        #Utils_Print::printvardie($row);
        return $this->db->fetchAll($sql);
    }
    
    public function getTreinosAlunoPorEmail($email) {
        
        $sql = $this->db->select()
                    ->from(array('f' => 'ficha'), 'f.*')
                ->join(array('a' => 'aluno'), 'a.matricula = f.aluno_id', 'a.nome')
                ->joinLeft(array('p' => 'professor'), 'p.id = f.professor_id', 'p.nm_professor')
                ->where("a.email like '".$email."'")
                ->order('f.dt_ini_ficha DESC');
        
        $row = $this->db->fetchRow($sql);
        
        if( !$row['professor_id'] ){
            $sql = $this->db->select()
                    ->from(array('f' => 'ficha'), 'f.*')
                        ->join(array('a' => 'aluno'), 'a.matricula = f.aluno_id', 'a.nome')
                        ->join(array('ac' => 'academia'), 'ac.id = f.academia_id', 'ac.nm_academia as nm_professor')
                    ->where("a.email like '".$email."'")
                ->order('f.dt_ini_ficha DESC');            
        }
                
        #Utils_Print::printvardie($row);
        return $this->db->fetchAll($sql);
    }
    
    public function getDadosGeraisTreino($ficha_id) {
        
        //Caso a ficha tenha sido montada por um Professor, entra nesse select
        $sql = $this->db->select()
                    ->from(array('f' => 'ficha'), 'f.*')
                ->join(array('a' => 'aluno'), 'a.matricula = f.aluno_id', 'a.nome')
                ->joinLeft(array('p' => 'professor'), 'p.id = f.professor_id', 'p.nm_professor')
                ->join(array('ac' => 'academia'), 'ac.id = f.academia_id', array('ac.nm_academia', 'logo as foto'))
                ->where('f.id = '.$ficha_id);
        
        $row = $this->db->fetchRow($sql);
        
        
        
        //Se a ficha não foi montada por um professor, entra neste if
        if( !$row['professor_id'] ){
            
            //Verifica se a ficha foi montada por uma academia ou por um personal
            //Caso a Ficha foi montada pela Academia, retorna a logo da academia
            //Senão, significa que foi montada pelo Personal, e retorna a foto do Aluno.
            $sql = "SELECT DISTINCT 
                            f.*, 
                            a.nome,      
                            ac.nm_academia AS nm_professor, 
                            ac.nm_academia,        
                            CASE
                              WHEN u1.perfil_id = 3 THEN ac.logo
                              ELSE a.foto
                            END AS foto
                    FROM ficha AS f
                        JOIN aluno AS a ON a.matricula = f.aluno_id    
                        JOIN academia AS ac ON ac.id = f.academia_id
                        JOIN academia_usuario au1 ON au1.academia_id = ac.id
                        JOIN usuario u1 ON u1.id = au1.usuario_id
                    WHERE (f.id = {$ficha_id}) AND u1.perfil_id = ( SELECT u.perfil_id
                                                          FROM academia_usuario au
                                                            JOIN usuario u ON u.id = au.usuario_id
                                                          WHERE au.academia_id = ac.id AND u.perfil_id <> 4 AND u.perfil_id <> 5)";
            
//            $sql = $this->db->select()
//                    ->from(array('f' => 'ficha'), 'f.*')
//                ->join(array('a' => 'aluno'), 'a.matricula = f.aluno_id', array('a.nome', 'a.thumbnail as foto'))
//                ->join(array('ac' => 'academia'), 'ac.id = f.academia_id', array('ac.nm_academia as nm_professor'))
//                ->where('f.id = '.$ficha_id);          
        }
                
        
        #Utils_Print::printvardie($sql->__toString());
        return $this->db->fetchRow($sql);
    }
    
    public function getDadosFormTreino($aluno_id) {
        
        $sql = "SELECT m.musculo, e.id, e.exercicio 
                FROM musculo m
                        JOIN exercicio e ON (e.musculo_id = m.id) 
                ORDER BY m.id, e.exercicio";
		
        $aExercicios = $this->db->fetchAll($sql);
        
        
        $aGroupExercicios = array();
        $aData = array();
        
        foreach ($aExercicios as $row){
            $aData[] = array(
                    'musculo' => ($row['musculo']),
                    'exercicio_id' => $row['id'],
                    'exercicio' => ($row['exercicio'])
            );
        }
        
        
        foreach($aData as $i => $data){
            $aGroupExercicios[$data['musculo']][] = $data;
        }
        
        
        return $aGroupExercicios;
    }
    
    public function getDadosTreino($ficha_id) {
        
        $sql = "SELECT e.exercicio, t.* 
                FROM treino t 
                    JOIN ficha f ON (f.id = t.ficha_id)    
                    JOIN exercicio e ON (e.id = t.exercicio_id)
                WHERE f.id = $ficha_id
                ORDER BY t.treino";

        $aExerciciosFicha = $this->db->fetchAll($sql);    

        $cond = null;
        $aTreinoFicha = array();

        //if($aExerciciosFicha){

        $ficha = array();

        foreach ($aExerciciosFicha as $ficha){
            $aTreinoFicha[$ficha['treino']][] = $ficha;
        }


        foreach($aTreinoFicha as $treino => $aTreinos){

            foreach($aTreinos as $i => $aTreino){

                $sql2 = "SELECT tc.carga FROM treino_carga tc WHERE tc.treino_id = {$aTreino['id']}";

                $aCargas = $this->db->fetchAll($sql2);
                $totCarga = count($aCargas);

                if($aCargas > 0){

                    foreach ($aCargas as $rowCarga){
                        if( !isset($cond[$treino]) ){
                            $cond[$treino] = ($totCarga>0)? $totCarga : 0;
                        }else {
                            $cond[$treino] = ($cond[$treino] > $totCarga)? $cond[$treino] : $totCarga;
                        }

                        //$ficha['carga2'][] = $rowCarga;
                        $aTreinoFicha[$treino][$i]['carga2'][] = $rowCarga;
                    }

                }

            }

        }
        
        return array('results' => $aTreinoFicha, 'cond' => $cond);
    }
    
    
    public function getExercicioFicha($treino_id) {
        $oTreino = new Model_Dao_Treino();
        $oTreinoRow = $oTreino->find($treino_id)->current();
        return $oTreinoRow;
    }
        
    
    public function getAlunosComESemFicha() {
        $sql = "SELECT a.matricula, a.nome, a.foto,
                        CASE 
                           WHEN f.aluno_id is not null
                              THEN 'S'
                           ELSE 'N'
                        END AS ficha  
                 FROM aluno a
                  LEFT JOIN ficha f ON f.aluno_id = a.matricula";
        
        $aAlunos = $this->db->fetchAll($sql);
        
        $total = count($aAlunos);
        
        return array('results' => $aAlunos, 'total' => $total);
    }
    
    public function getCargasFicha($treino_id) {
        $query1 = "SELECT tc.*
                   FROM treino t
                    JOIN treino_carga tc ON (tc.treino_id= t.id) 
                   WHERE t.id = $treino_id";
                
        $aCargas['cargas'] = $this->db->fetchAll($query1);
                
        $query2 = "SELECT t.*, e.exercicio
                   FROM treino t
                    JOIN exercicio e ON (e.id = t.exercicio_id) 
                   WHERE t.id = $treino_id";
        
        $aTreino = $this->db->fetchRow($query2);
        
        $results = array_merge($aTreino, $aCargas);
        #Utils_Print::printvardie($results);
        
        return $results;
    }
    
    public function getDadosFichaParaCarga($exercicio_id) {
                
        $query2 = "SELECT t.*, e.exercicio
                   FROM treino t
                    JOIN exercicio e ON (e.id = t.exercicio_id) 
                   WHERE t.id = $exercicio_id";
        
        $results = $this->db->fetchAll($query2);
        #Utils_Print::printvardie($results);
        
        return $results;
    }
    
}
