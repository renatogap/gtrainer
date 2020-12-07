<?php

class Model_Rule_Avaliacao extends Model_Rule_Abstract {

    private $db;
    private $sessao;

    public function __construct(){
        $this->db = Zend_Db_Table::getDefaultAdapter();
        $this->sessao = $_SESSION['usuario'];
    }

    public function salvarAvaliacao()
    {
        try{
            $oAvaliacao = new Model_Dao_Avaliacao();

            if($this->id){
                $oAvaliacaoRow = $oAvaliacao->find($this->id)->current();
                $oAvaliacaoRow->updated_at = date('Y-m-d H:i:s');
            }else {
                $oAvaliacaoRow = $oAvaliacao->createRow();
                $oAvaliacaoRow->created_at = date('Y-m-d H:i:s');
            }

            // Dobras cutaneas
            $oAvaliacaoRow->fk_personal_academia = $this->sessao->academia_id;
            $oAvaliacaoRow->fk_aluno = $this->aluno;
            $oAvaliacaoRow->data = date('Y-m-d');
            $oAvaliacaoRow->dt_reavaliacao = ($this->dt_reavalicacao);
            $oAvaliacaoRow->altura = $this->altura;
            $oAvaliacaoRow->protocolo = $this->protocolo;
            $oAvaliacaoRow->peso = $this->peso;
            $oAvaliacaoRow->TR = ($this->TR? $this->TR : null);
            $oAvaliacaoRow->SB = ($this->SB? $this->SB : null);
            $oAvaliacaoRow->AX = ($this->AX? $this->AX : null);
            $oAvaliacaoRow->AB = ($this->AB? $this->AB : null);
            $oAvaliacaoRow->CX = ($this->CX? $this->CX : null);
            $oAvaliacaoRow->PA = ($this->PA? $this->PA : null);
            $oAvaliacaoRow->BI = ($this->BI? $this->BI : null);
            $oAvaliacaoRow->PE = ($this->PE? $this->PE : null);
            $oAvaliacaoRow->SI = ($this->SI? $this->SI : null);

            // Medidas Lienares
            $oAvaliacaoRow->torax = ($this->torax? $this->torax : null);
            $oAvaliacaoRow->quadril = ($this->quadril? $this->quadril : null);
            $oAvaliacaoRow->cintura = ($this->cintura? $this->cintura : null);
            $oAvaliacaoRow->abdomen = ($this->abdomen? $this->abdomen : null);
            $oAvaliacaoRow->escapular = ($this->escapular? $this->escapular : null);
            $oAvaliacaoRow->braco_direito_contraido = ($this->braco_direito_contraido? $this->braco_direito_contraido : null);
            $oAvaliacaoRow->braco_esquerdo_contraido = ($this->braco_esquerdo_contraido? $this->braco_esquerdo_contraido : null);
            $oAvaliacaoRow->braco_direito_relaxado = ($this->braco_direito_relaxado? $this->braco_direito_relaxado : null);
            $oAvaliacaoRow->braco_esquerdo_relaxado = ($this->braco_esquerdo_relaxado? $this->braco_esquerdo_relaxado : null);
            $oAvaliacaoRow->antebraco_direito = ($this->antebraco_direito? $this->antebraco_direito : null);
            $oAvaliacaoRow->antebraco_esquerdo = ($this->antebraco_esquerdo? $this->antebraco_esquerdo : null);
            $oAvaliacaoRow->coxa_direita = ($this->coxa_direita? $this->coxa_direita : null);
            $oAvaliacaoRow->coxa_esquerda = ($this->coxa_esquerda? $this->coxa_esquerda : null);
            $oAvaliacaoRow->panturrilha_direita = ($this->panturrilha_direita? $this->panturrilha_direita : null);
            $oAvaliacaoRow->panturrilha_esquerda = ($this->panturrilha_esquerda? $this->panturrilha_esquerda : null);
            $oAvaliacaoRow->ombro = ($this->ombro? $this->ombro : null);
            $oAvaliacaoRow->pescoco = ($this->pescoco? $this->pescoco : null);
            $oAvaliacaoRow->punho = ($this->punho? $this->punho : null);
            $oAvaliacaoRow->joelho = ($this->joelho? $this->joelho : null);
            $oAvaliacaoRow->tornozelo = ($this->tornozelo? $this->tornozelo : null);

            // Medidas Ósseas
            $oAvaliacaoRow->biestiloide = ($this->biestiloide? $this->biestiloide : null);
            $oAvaliacaoRow->biepicondiliano_femur = ($this->biepicondiliano_femur? $this->biepicondiliano_femur : null);
            $oAvaliacaoRow->biacromial = ($this->biacromial? $this->biacromial : null);
            $oAvaliacaoRow->biileocristal = ($this->biileocristal? $this->biileocristal : null);
            $oAvaliacaoRow->bitrocanteriano = ($this->bitrocanteriano? $this->bitrocanteriano : null);
            $oAvaliacaoRow->biepicondiliano_umero = ($this->biepicondiliano_umero? $this->biepicondiliano_umero : null);
            $oAvaliacaoRow->bimaleolar = ($this->bimaleolar? $this->bimaleolar : null);

            $id = $oAvaliacaoRow->save();

            $this->gerarResultadoAvaliacaoFisica($id);

            return ['retorno' => 'sucesso', 'msg' => 'Avaliação salva com sucesso!', 'id' => $id];
        } catch(Exception $e) {
            return ['retorno' => 'error', 'msg' => 'Falha ao salvar a avaliação. '. $e->getMessage()];
        }
    }

    public function salvarDobras() {
        if(!$this->peso){
            return array('retorno' => 'falha', 'msg' => 'Informe o peso do aluno');
        }
        else if(!$this->altura){
            return array('retorno' => 'falha', 'msg' => 'Informe a altura do aluno');
        }
        else if(!$this->dt_reavaliacao){
            return array('retorno' => 'falha', 'msg' => 'Informe a data da próxima avaliação do aluno');
        }

        $oAvaliacao = new Model_Dao_Avaliacao();
        $oAvaliacaoRow = $oAvaliacao->createRow();

        $oAvaliacaoRow->academia_id = $this->sessao->academia_id;
        $oAvaliacaoRow->aluno_id = $this->aluno_id;
        $oAvaliacaoRow->data = date('Y-m-d');
        $oAvaliacaoRow->dt_reavaliacao = Utils_Date::formataDataToBd($this->dt_reavaliacao);
        $oAvaliacaoRow->altura = $this->altura;
        $oAvaliacaoRow->protocolo = $this->protocolo;
        $oAvaliacaoRow->peso = $this->peso;
        $oAvaliacaoRow->TR = $this->TR;
        $oAvaliacaoRow->SB = $this->SB;
        $oAvaliacaoRow->AX = $this->AX;
        $oAvaliacaoRow->AB = $this->AB;
        $oAvaliacaoRow->CX = $this->CX;
        $oAvaliacaoRow->PA = $this->PA;
        $oAvaliacaoRow->BI = $this->BI;
        $oAvaliacaoRow->PE = $this->PE;
        $oAvaliacaoRow->SI = $this->SI;
        
        try{
            $oAvaliacaoRow->save();
        } catch (Exception $ex) {
            return array('retorno' => 'erro', 'msg' => 'Erro ao salvar as Dobras. '.$ex->getMessage());
        }
        
        return array('retorno' => 'sucesso', 'msg' => 'Passo 1 realizado com sucesso', 'avaliacao_id' => $oAvaliacaoRow->id);
    }
    
    public function salvarMedidas() {
        $oAvaliacao = new Model_Dao_Avaliacao();        
        //$oAvaliacaoRow = $oAvaliacao->find(1)->current();
        $oAvaliacaoRow = $oAvaliacao->find($this->avaliacao_id)->current();
        
        $oAvaliacaoRow->torax = $this->torax;
        $oAvaliacaoRow->quadril = $this->quadril;
        $oAvaliacaoRow->cintura = $this->cintura;
        $oAvaliacaoRow->abdomen = $this->abdomen;
        $oAvaliacaoRow->escapular = $this->escapular;
        $oAvaliacaoRow->braco_direito_contraido = $this->braco_direito_contraido;
        $oAvaliacaoRow->braco_esquerdo_contraido = $this->braco_esquerdo_contraido;
        $oAvaliacaoRow->braco_direito_relaxado = $this->braco_direito_relaxado;
        $oAvaliacaoRow->braco_esquerdo_relaxado = $this->braco_esquerdo_relaxado;
        $oAvaliacaoRow->antebraco_direito = $this->antebraco_direito;
        $oAvaliacaoRow->antebraco_esquerdo = $this->antebraco_esquerdo;
        $oAvaliacaoRow->coxa_direita = $this->coxa_direita;
        $oAvaliacaoRow->coxa_esquerda = $this->coxa_esquerda;
        $oAvaliacaoRow->panturrilha_direita = $this->panturrilha_direita;
        $oAvaliacaoRow->panturrilha_esquerda = $this->panturrilha_esquerda;
        $oAvaliacaoRow->ombro = $this->ombro;
        $oAvaliacaoRow->pescoco = $this->pescoco;
        $oAvaliacaoRow->punho = $this->punho;
        $oAvaliacaoRow->joelho = $this->joelho;
        $oAvaliacaoRow->tornozelo = $this->tornozelo;
        
        try{
            $oAvaliacaoRow->save();
        } catch (Exception $ex) {
            return array('retorno' => 'erro', 'msg' => 'Erro ao salvar as medias lineares da avaliação física. '.$ex->getMessage());
        }
        
        return array('retorno' => 'sucesso', 'msg' => 'Medidas salvas com sucesso', 'avaliacao_id' => $oAvaliacaoRow->id);
    }
    
    
    public function salvarDiametrosOsseos() {
        $oAvaliacao = new Model_Dao_Avaliacao();        
        $oAvaliacaoRow = $oAvaliacao->find($this->avaliacao_id)->current();
        
        $oAvaliacaoRow->biestiloide = $this->biestiloide;
        $oAvaliacaoRow->biepicondiliano_femur = $this->biepicondiliano_femur;
        $oAvaliacaoRow->biacromial = $this->biacromial;
        $oAvaliacaoRow->biileocristal = $this->biileocristal;
        $oAvaliacaoRow->bitrocanteriano = $this->bitrocanteriano;
        $oAvaliacaoRow->biepicondiliano_umero = $this->biepicondiliano_umero;
        $oAvaliacaoRow->bimaleolar = $this->bimaleolar;
        
        try{
            $oAvaliacaoRow->save();
        } catch (Exception $ex) {
            return array('retorno' => 'erro', 'msg' => 'Erro ao salvar os diêmetros ósseos da avaliação física. '.$ex->getMessage());
        }
        
        return array('retorno' => 'sucesso', 'msg' => 'Diêmetros ósseos salvos com sucesso!', 'avaliacao_id' => $oAvaliacaoRow->id);
    }
    
    public function salvarAvaliacaoMobile() {
        if(!$this->peso){
            return array('retorno' => 'falha', 'msg' => 'Informe o peso do aluno');
        }else        
        if(!$this->altura){
            return array('retorno' => 'falha', 'msg' => 'Informe a altura do aluno');
        }else
        if(!$this->dt_reavaliacao){
            return array('retorno' => 'falha', 'msg' => 'Informe a data da próxima avaliação do aluno');
        }    
        
        $oAvaliacao = new Model_Dao_Avaliacao();
        $oAvaliacaoRow = $oAvaliacao->createRow();
        
        $oAvaliacaoRow->academia_id = $this->sessao->academia_id;
        $oAvaliacaoRow->aluno_id = $this->aluno_id;
        $oAvaliacaoRow->data = date('Y-m-d');
        $oAvaliacaoRow->dt_reavaliacao = Utils_Date::formataDataToBd($this->dt_reavaliacao);
        $oAvaliacaoRow->altura = Utils_Date::formataMoedaBD($this->altura);
        $oAvaliacaoRow->protocolo = $this->protocolo;
        $oAvaliacaoRow->peso = Utils_Date::formataMoedaBD($this->peso);
        $oAvaliacaoRow->TR = Utils_Date::formataMoedaBD($this->TR);
        $oAvaliacaoRow->SB = Utils_Date::formataMoedaBD($this->SB);
        $oAvaliacaoRow->AX = Utils_Date::formataMoedaBD($this->AX);
        $oAvaliacaoRow->AB = Utils_Date::formataMoedaBD($this->AB);
        $oAvaliacaoRow->CX = Utils_Date::formataMoedaBD($this->CX);
        $oAvaliacaoRow->PA = Utils_Date::formataMoedaBD($this->PA);
        $oAvaliacaoRow->BI = Utils_Date::formataMoedaBD($this->BI);
        $oAvaliacaoRow->PE = Utils_Date::formataMoedaBD($this->PE);
        $oAvaliacaoRow->SI = Utils_Date::formataMoedaBD($this->SI);
        
        $oAvaliacaoRow->torax = Utils_Date::formataMoedaBD($this->torax);
        $oAvaliacaoRow->quadril = Utils_Date::formataMoedaBD($this->quadril);
        $oAvaliacaoRow->cintura = Utils_Date::formataMoedaBD($this->cintura);
        $oAvaliacaoRow->abdomen = Utils_Date::formataMoedaBD($this->abdomen);
        $oAvaliacaoRow->escapular = Utils_Date::formataMoedaBD($this->escapular);
        $oAvaliacaoRow->braco_direito_contraido = Utils_Date::formataMoedaBD($this->braco_direito_contraido);
        $oAvaliacaoRow->braco_esquerdo_contraido = Utils_Date::formataMoedaBD($this->braco_esquerdo_contraido);
        $oAvaliacaoRow->braco_direito_relaxado = Utils_Date::formataMoedaBD($this->braco_direito_relaxado);
        $oAvaliacaoRow->braco_esquerdo_relaxado = Utils_Date::formataMoedaBD($this->braco_esquerdo_relaxado);
        $oAvaliacaoRow->antebraco_direito = Utils_Date::formataMoedaBD($this->antebraco_direito);
        $oAvaliacaoRow->antebraco_esquerdo = Utils_Date::formataMoedaBD($this->antebraco_esquerdo);
        $oAvaliacaoRow->coxa_direita = Utils_Date::formataMoedaBD($this->coxa_direita);
        $oAvaliacaoRow->coxa_esquerda = Utils_Date::formataMoedaBD($this->coxa_esquerda);
        $oAvaliacaoRow->panturrilha_direita = Utils_Date::formataMoedaBD($this->panturrilha_direita);
        $oAvaliacaoRow->panturrilha_esquerda = Utils_Date::formataMoedaBD($this->panturrilha_esquerda);
        $oAvaliacaoRow->ombro = Utils_Date::formataMoedaBD($this->ombro);
        $oAvaliacaoRow->pescoco = Utils_Date::formataMoedaBD($this->pescoco);
        $oAvaliacaoRow->punho = Utils_Date::formataMoedaBD($this->punho);
        $oAvaliacaoRow->joelho = Utils_Date::formataMoedaBD($this->joelho);
        $oAvaliacaoRow->tornozelo = Utils_Date::formataMoedaBD($this->tornozelo);
        
        $oAvaliacaoRow->biestiloide = Utils_Date::formataMoedaBD($this->biestiloide);
        $oAvaliacaoRow->biepicondiliano_femur = Utils_Date::formataMoedaBD($this->biepicondiliano_femur);
        $oAvaliacaoRow->biacromial = Utils_Date::formataMoedaBD($this->biacromial);
        $oAvaliacaoRow->biileocristal = Utils_Date::formataMoedaBD($this->biileocristal);
        $oAvaliacaoRow->bitrocanteriano = Utils_Date::formataMoedaBD($this->bitrocanteriano);
        $oAvaliacaoRow->biepicondiliano_umero = Utils_Date::formataMoedaBD($this->biepicondiliano_umero);
        $oAvaliacaoRow->bimaleolar = Utils_Date::formataMoedaBD($this->bimaleolar);
        
        try{
            $oAvaliacaoRow->save();
        } catch (Exception $ex) {
            return array('retorno' => 'erro', 'msg' => 'Erro ao salvar a Avaliação. '.$ex->getMessage());
        }
        
        return array('retorno' => 'sucesso', 'msg' => 'Avaliação cadastrada com sucesso', 'avaliacao_id' => $oAvaliacaoRow->id);
    }
    
    
    public function alterarDobras() {
        if(!$this->peso){
            return array('retorno' => 'falha', 'msg' => 'Informe o peso do aluno');
        }else        
        if(!$this->altura){
            return array('retorno' => 'falha', 'msg' => 'Informe a altura do aluno');
        }else
        if(!$this->dt_reavaliacao){
            return array('retorno' => 'falha', 'msg' => 'Informe a data da próxima avaliação do aluno');
        }    
        
        $oAvaliacao = new Model_Dao_Avaliacao();
        
        $oAvaliacaoRow = $oAvaliacao->find($this->avaliacao_id)->current();
        
        $oAvaliacaoRow->academia_id = $this->sessao->academia_id;
        $oAvaliacaoRow->aluno_id = $this->aluno_id;
        $oAvaliacaoRow->dt_reavaliacao = Utils_Date::formataDataToBd($this->dt_reavaliacao);
        $oAvaliacaoRow->altura = $this->altura;
        $oAvaliacaoRow->protocolo = $this->protocolo;
        $oAvaliacaoRow->peso = $this->peso;
        $oAvaliacaoRow->TR = $this->TR;
        $oAvaliacaoRow->SB = $this->SB;
        $oAvaliacaoRow->AX = $this->AX;
        $oAvaliacaoRow->AB = $this->AB;
        $oAvaliacaoRow->CX = $this->CX;
        $oAvaliacaoRow->PA = $this->PA;
        $oAvaliacaoRow->BI = $this->BI;
        $oAvaliacaoRow->PE = $this->PE;
        $oAvaliacaoRow->SI = $this->SI;
        
        try{
            $oAvaliacaoRow->save();
        } catch (Exception $ex) {
            return array('retorno' => 'erro', 'msg' => 'Erro ao salvar as Dobras. '.$ex->getMessage());
        }
        
        return array('retorno' => 'sucesso', 'msg' => 'Dobras cutâneas alteradas com sucesso.', 'avaliacao_id' => $this->avaliacao_id);
    }
    
    
    public function alterarAvaliacaoMobile() {
        if(!$this->peso){
            return array('retorno' => 'falha', 'msg' => 'Informe o peso do aluno');
        }else        
        if(!$this->altura){
            return array('retorno' => 'falha', 'msg' => 'Informe a altura do aluno');
        }else
        if(!$this->dt_reavaliacao){
            return array('retorno' => 'falha', 'msg' => 'Informe a data da próxima avaliação do aluno');
        }    
        
        $oAvaliacao = new Model_Dao_Avaliacao();
        $oAvaliacaoRow = $oAvaliacao->find($this->avaliacao_id)->current();
        
        $oAvaliacaoRow->data = date('Y-m-d');
        $oAvaliacaoRow->dt_reavaliacao = Utils_Date::formataDataToBd($this->dt_reavaliacao);
        $oAvaliacaoRow->altura = Utils_Date::formataMoedaBD($this->altura);
        $oAvaliacaoRow->protocolo = $this->protocolo;
        $oAvaliacaoRow->peso = Utils_Date::formataMoedaBD($this->peso);
        $oAvaliacaoRow->TR = Utils_Date::formataMoedaBD($this->TR);
        $oAvaliacaoRow->SB = Utils_Date::formataMoedaBD($this->SB);
        $oAvaliacaoRow->AX = Utils_Date::formataMoedaBD($this->AX);
        $oAvaliacaoRow->AB = Utils_Date::formataMoedaBD($this->AB);
        $oAvaliacaoRow->CX = Utils_Date::formataMoedaBD($this->CX);
        $oAvaliacaoRow->PA = Utils_Date::formataMoedaBD($this->PA);
        $oAvaliacaoRow->BI = Utils_Date::formataMoedaBD($this->BI);
        $oAvaliacaoRow->PE = Utils_Date::formataMoedaBD($this->PE);
        $oAvaliacaoRow->SI = Utils_Date::formataMoedaBD($this->SI);
        
        $oAvaliacaoRow->torax = Utils_Date::formataMoedaBD($this->torax);
        $oAvaliacaoRow->quadril = Utils_Date::formataMoedaBD($this->quadril);
        $oAvaliacaoRow->cintura = Utils_Date::formataMoedaBD($this->cintura);
        $oAvaliacaoRow->abdomen = Utils_Date::formataMoedaBD($this->abdomen);
        $oAvaliacaoRow->escapular = Utils_Date::formataMoedaBD($this->escapular);
        $oAvaliacaoRow->braco_direito_contraido = Utils_Date::formataMoedaBD($this->braco_direito_contraido);
        $oAvaliacaoRow->braco_esquerdo_contraido = Utils_Date::formataMoedaBD($this->braco_esquerdo_contraido);
        $oAvaliacaoRow->braco_direito_relaxado = Utils_Date::formataMoedaBD($this->braco_direito_relaxado);
        $oAvaliacaoRow->braco_esquerdo_relaxado = Utils_Date::formataMoedaBD($this->braco_esquerdo_relaxado);
        $oAvaliacaoRow->antebraco_direito = Utils_Date::formataMoedaBD($this->antebraco_direito);
        $oAvaliacaoRow->antebraco_esquerdo = Utils_Date::formataMoedaBD($this->antebraco_esquerdo);
        $oAvaliacaoRow->coxa_direita = Utils_Date::formataMoedaBD($this->coxa_direita);
        $oAvaliacaoRow->coxa_esquerda = Utils_Date::formataMoedaBD($this->coxa_esquerda);
        $oAvaliacaoRow->panturrilha_direita = Utils_Date::formataMoedaBD($this->panturrilha_direita);
        $oAvaliacaoRow->panturrilha_esquerda = Utils_Date::formataMoedaBD($this->panturrilha_esquerda);
        $oAvaliacaoRow->ombro = Utils_Date::formataMoedaBD($this->ombro);
        $oAvaliacaoRow->pescoco = Utils_Date::formataMoedaBD($this->pescoco);
        $oAvaliacaoRow->punho = Utils_Date::formataMoedaBD($this->punho);
        $oAvaliacaoRow->joelho = Utils_Date::formataMoedaBD($this->joelho);
        $oAvaliacaoRow->tornozelo = Utils_Date::formataMoedaBD($this->tornozelo);
        
        $oAvaliacaoRow->biestiloide = Utils_Date::formataMoedaBD($this->biestiloide);
        $oAvaliacaoRow->biepicondiliano_femur = Utils_Date::formataMoedaBD($this->biepicondiliano_femur);
        $oAvaliacaoRow->biacromial = Utils_Date::formataMoedaBD($this->biacromial);
        $oAvaliacaoRow->biileocristal = Utils_Date::formataMoedaBD($this->biileocristal);
        $oAvaliacaoRow->bitrocanteriano = Utils_Date::formataMoedaBD($this->bitrocanteriano);
        $oAvaliacaoRow->biepicondiliano_umero = Utils_Date::formataMoedaBD($this->biepicondiliano_umero);
        $oAvaliacaoRow->bimaleolar = Utils_Date::formataMoedaBD($this->bimaleolar);
        
        try{
            $oAvaliacaoRow->save();
        } catch (Exception $ex) {
            return array('retorno' => 'erro', 'msg' => 'Erro ao tentar alterar aa Avaliação. '.$ex->getMessage());
        }
        
        return array('retorno' => 'sucesso', 'msg' => 'Avaliação salva com sucesso', 'avaliacao_id' => $oAvaliacaoRow->id);
    }
    
    
    
    public function gerarResultadoAvaliacaoFisica($id) {            
        //Instanciando os objetos
        $oAvaliacao = new Model_Dao_Avaliacao();    
        $oAluno = new Model_Dao_Aluno();

        //Pegar dados da Avaliação Física
        $oAvaliacaoRow = $oAvaliacao->find($id)->current();

        //Pegar dados do Aluno
        $oAlunoRow = $oAluno->find($oAvaliacaoRow->fk_aluno)->current();

        //Pegar o ano a partir da data de nascimento
        list($ano) = explode('-', $oAlunoRow->dt_nascimento);   
        $idade = ( date('Y') - $ano );

        $aDado = Array(
            'sexo' => $oAlunoRow->sexo,
            'idade' => $idade,
            'peso' => $oAvaliacaoRow->peso,
            'altura' => $oAvaliacaoRow->altura,
            'protocolo' => $oAvaliacaoRow->protocolo
        );

        $aDobras = Array(
            'PE' => $oAvaliacaoRow->PE,  //Peitoral ou Tórax
            'BI' => $oAvaliacaoRow->BI,  //Bíceps
            'AX' => $oAvaliacaoRow->AX,  //Axilar Média
            'TR' => $oAvaliacaoRow->TR,  //Tríceps
            'SB' => $oAvaliacaoRow->SB, //Subescapular
            'AB' => $oAvaliacaoRow->AB, //Abdomen
            'SI' => $oAvaliacaoRow->SI, //Supra Ilíaca
            'CX' => $oAvaliacaoRow->CX, //Coxa
            'PA' => $oAvaliacaoRow->PA  //Panturrilha
        );

        try{

            $oGerar = new Model_Rule_GerarAvaliacao();

            //IMC
            $IMC = $oGerar->calcularIMC($oAvaliacaoRow->peso, $oAvaliacaoRow->altura);
            $situacao = $oGerar->avaliacaoIMC($IMC);

            //Densidade Corporal
            $densidade = $oGerar->calcularDensidadeCorporal($aDobras, $aDado);

            //Percentual de Gordura
            $percentGordura = $oGerar->calcularPercentualDeGordura($densidade, $oAlunoRow->sexo, $idade);
       
            //Massa Gorda
            $massaGorda = ( $oAvaliacaoRow->peso * ( $percentGordura / 100 ) );
            #$percentMassaGorda = ( ( $massaGorda * 100 ) / $oAvaliacaoRow->peso );
            
            //Massa Magra
            $massaMagra = ( $oAvaliacaoRow->peso - $massaGorda );
            #$percentMassaMagra = ( ( $massaMagra * 100 ) / $oAvaliacaoRow->peso );
            
            $massaResidual = null;
            
            //Massa Residual
            if( $oAlunoRow->sexo == 'M' ){
                $massaResidual = ( $oAvaliacaoRow->peso * 0.241 );
            }else {
                $massaResidual = ( $oAvaliacaoRow->peso * 0.209 );                
            }    
   
            //Percentual Residual
            #$percentResidual = ( ( $massaResidual * 100 ) / $oAvaliacaoRow->peso );
            
            
            //Massa Óssea
            $resOsseo = (($oAvaliacaoRow->biestiloide + $oAvaliacaoRow->biepicondiliano_umero + $oAvaliacaoRow->biepicondiliano_femur + $oAvaliacaoRow->bimaleolar) / 4);
            $massaOssea = (($resOsseo * $resOsseo) * ($oAvaliacaoRow->altura * 0.00092));
            $percentMassaOssea = ( ( $massaOssea * 100 ) / $oAvaliacaoRow->peso );
            
            
            //Massa Muscular            
            $massaMuscular = ( $oAvaliacaoRow->peso - ( $massaGorda + $massaOssea + $massaResidual ) ); // 0 é a Massa Óssea - não sei o valor
            #$percentMassaMuscular = ( ( $massaMuscular * 100 ) / $oAvaliacaoRow->peso );
            
            $alturaCm = str_replace('.', '', $oAvaliacaoRow->altura); //tranforma para centimetros ( 1,80m = 180cm )
            if( $oAlunoRow->sexo == 'M' ){
                $massaIdeal = ( $alturaCm - 100 - ( ($alturaCm-150)/4 ) );
            }else {
                $massaIdeal = ( $alturaCm - 100 - ( ($alturaCm-150)/2 ) );               
            }
            
            $massaEmExcesso = ( $oAvaliacaoRow->peso - $massaIdeal );
            
            
            //Update das informações
            $oAvaliacaoRow->imc = $IMC;
            $oAvaliacaoRow->densidade = $densidade;
            $oAvaliacaoRow->situacao = $situacao;
            
            $oAvaliacaoRow->percentual_gordura = $percentGordura;
            $oAvaliacaoRow->massa_gorda = number_format($massaGorda, 2);
            $oAvaliacaoRow->massa_magra = number_format($massaMagra, 2);            
            $oAvaliacaoRow->massa_muscular = number_format($massaMuscular, 2);
            $oAvaliacaoRow->massa_ossea = number_format($massaOssea, 2);
            $oAvaliacaoRow->massa_residual = number_format($massaResidual, 2);
            $oAvaliacaoRow->peso_ideal = number_format($massaIdeal, 2);
            $oAvaliacaoRow->peso_excesso = number_format($massaEmExcesso, 2);
            
            $oAvaliacaoRow->save();
            
        } catch (Exception $ex) {
            return array('retorno' => 'erro', 'msg' => 'Erro ao gerar a avaliacao fisica do aluno. ');
        }
        
        return array('retorno' => 'sucesso', 'msg' => 'Avaliação Física criada com sucesso', 'avaliacao_id' => $oAvaliacaoRow->id);
    }

    public function getAll()
    {
        $sql = $this->db->select()
                    ->from(array('av' => 'avaliacao_fisica'), 'av.*')
                    ->join(array('a' => 'aluno'), 'a.id = av.fk_aluno', ['a.nome', 'a.fk_usuario'])
                    ->join(array('u' => 'usuario'), 'u.id = a.fk_usuario', ['u.filename'])
                ->where('av.fk_personal_academia = '. $this->sessao->academia_id);

        #Utils_Print::printvardie($sql->__toString());
        return $this->db->fetchAll($sql);
    }

    public function getAvaliacoes($aluno_id) {
        $sql = $this->db->select()
                    ->from(array('av' => 'avaliacao_fisica'), 'av.*')
                ->join(array('a' => 'aluno'), 'a.matricula = av.aluno_id', 'a.nome')
                ->where('av.aluno_id = '.$aluno_id)
                ->where('av.academia_id = '. $this->sessao->academia_id);

        #Utils_Print::printvardie($sql->__toString());
        return $this->db->fetchAll($sql);
    }

    public function getAvaliacoesPorEmail($email) {
        $sql = $this->db->select()
                    ->from(array('av' => 'avaliacao_fisica'), 'av.*')
                ->join(array('a' => 'aluno'), 'a.matricula = av.aluno_id', 'a.nome')
                ->where("a.email like '".$email."'");

        #Utils_Print::printvardie($sql->__toString());
        return $this->db->fetchAll($sql);
    }

    public function getById($avaliacao_id)
    {
        $sql = $this->db->select()
                    ->from(array('av' => 'avaliacao_fisica'), 'av.*')
                    ->join(array('a' => 'aluno'), 'a.id= av.fk_aluno', ['a.nome','a.dt_nascimento','a.sexo'])
                    ->where('av.id = '.$avaliacao_id);

        #Utils_Print::printvardie($sql->__toString());
        return $this->db->fetchRow($sql);
    }

}