<?php
require_once APPLICATION_PATH.'/../library/Utils/WideImage/WideImage.php';
require_once APPLICATION_PATH.'/../library/Utils/phpthumb/ThumbLib.inc.php';

class Model_Rule_UploadExercicio extends Zend_File_Transfer_Adapter_Http {
    
    const DIR_IMAGE     = '/../public_html/images/exercicio/';
    const DIR_THUMBNAIL = '/../public_html/images/exercicio/thumbnail/';
    const EXTENSION     = 'jpg,jpeg,png,gif,JPG,PNG,GIF';
    const FILE_ARQ_MIN  = 1;
    const FILE_ARQ_MAX  = 1;
    const FILE_SIZE_MIN = '1K';
    const FILE_SIZE_MAX = '10MB';
    const LARGURA_FILE  = 1024; // Largura máxima (pixels)
    const ALTURA_FILE   = 414;  // Altura máxima (pixels)

    private $nomeArquivo        = '';
    private $url                = '';
    private $thumbnail          = '';
    private $publicPath         = '';
    
    private $sessao             = null;
    
    public function  __construct() {
        parent::__construct();
        
        $auth = new Zend_Auth_Storage_Session();
        $this->sessao = $auth->read();
        
        $this->publicPath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        $translate = new Zend_Translate('array', Utils_File::tplMessageFile(), 'pt_BR');
        $this->setTranslator($translate);
    }
    
    public function getExtension() {
        return $this->extension;
    }

    private function setExtension($info) {
        list($tipo,$ext) = explode(".", $info['name']);
        return $this->extension = strtolower($ext);
    }

    public function setNomeArquivo() {
        $this->nomeArquivo = md5(uniqid(time())) . '.jpg';
        $this->addFilter('Rename', $this->nomeArquivo);
    }
    
    public function upload() {
        
        try{
            
            if($this->isUploaded()) {
                
                // seta o destino da imagem
                $this->setDestination(APPLICATION_PATH.self::DIR_IMAGE);
                
                // adiciona validação da extensão do arquivo (jpg,gif,bmp)
                $this->addValidator('Extension', false, self::EXTENSION);
                
                // minimo 1 máximo 1 arquivos
                $this->addValidator('Count', false, array('min' => self::FILE_ARQ_MIN, 'max' => self::FILE_ARQ_MAX));

                // limit to 5M
               $this->addValidator('Size', false, array('min' => self::FILE_SIZE_MIN, 'max' => self::FILE_SIZE_MAX));

                // pega as informações do arquivo
                $files = $this->getFileInfo();
                
                // varre os arquivos adicionados
                foreach($files as $file => $info) {
                    
                    // seta a extensão do arquivo
                    $this->setExtension($info);

                    // seta o nome do arquivo
                    $this->setNomeArquivo();

                    // valida o arquivo
                    if (!$this->isValid($file)) {
                        
                        
                        $aError = $this->getMessages();
                        
                        foreach($aError as $erro => $msg){
                            $msgErro[] = $msg;                            
                        }
                        
                        return array('retorno' => 'erro', 'msg' => 'Erro no upload. '. join(',', $msgErro));
                        
                    }
                    else {
                        
                        // criar Thumbnail
                        $this->criarThumbnail($info['tmp_name']);

                        
                        // salva imagem com a dimensão tratada
                        $this->criarImagem($info['tmp_name']);

                    }

                }
                
            }else {
                return array('retorno' => 'erro', 'msg' => "Falha: verifique se os parametros de upload estao corretos");
            }
        }
        catch(Exception $ex) {
            return array('retorno' => 'erro', 'msg' => $ex->getMessage());
        }
        
        return array('retorno' => 'sucesso', 'msg' => "Upload completo", 'url' => $this->url, 'thumbnail' => $this->thumbnail);
    }
    

    public function criarImagem($filename) {
        try{
            #$logo     = "{$_SERVER['DOCUMENT_ROOT']}{$this->publicPath}/images/cb-marca-dagua.png"; 
            $imagem   = WideImage::load($filename);
            $tamanhos = getimagesize($filename);

            if($tamanhos[0] > self::LARGURA_FILE || $tamanhos[1] > self::ALTURA_FILE) {
                $imagem = $imagem->resize(self::LARGURA_FILE, self::ALTURA_FILE);
            }

            #$imagem_logo  = WideImage::loadFromFile($logo);
            #$tamanho_logo = getimagesize($logo);

            #$imagem = $imagem->merge($imagem_logo, 'right', 'bottom', 65);

            $newPath = $this->criarPastaFotos();
            
            #$this->url = $this->publicPath.'/images/fotos/'.$this->nomeArquivo;
            $this->url = '/images/exercicio/'.$this->sessao->email."/".$this->nomeArquivo;
            
            $imagem->saveToFile($newPath.'/'.$this->nomeArquivo);
        }
        catch(Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function criarThumbnail($filename) {
        try{
            $thumb = PhpThumbFactory::create($filename);
            $thumb->resize(100, 90);

            $newPath = $this->criarPastaThumbnail();
            
            #$this->thumbnail = $this->publicPath.'/images/fotos/thumbnail/'.$this->nomeArquivo;
            $this->thumbnail = '/images/exercicio/thumbnail/'.$this->sessao->email."/".$this->nomeArquivo;
            
            $thumb->save($newPath.'/'.$this->nomeArquivo);
        }
        catch(Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function criarPastaFotos() {
        $path_user   = APPLICATION_PATH.self::DIR_IMAGE.$this->sessao->email."/";
        
        if(!is_dir($path_user)) {
            @mkdir ($path_user, 0777, true);
        }
        
        $path_album = "{$path_user}";
        
        if(!is_dir($path_album)) {
            
            if($handle = opendir($path)) {
                while(($file = readdir($handle)) !== false) {
                        if($file != '.' && $file != '..') {
                            unlink($path."/".$file);
                        }
                }
            }
            
            @mkdir ($path_album, 0777, true);
        }
              
        return $path_album;
    }
    
    public function criarPastaThumbnail() {
        $path   = APPLICATION_PATH.self::DIR_THUMBNAIL.$this->sessao->email."/";
        
        if(!is_dir($path)) {
            @mkdir ($path, 0777, true);
        }
        
        $path = "{$path}";
        
        
        if(!is_dir($path)) {
            
            if($handle = opendir($path)) {
                while(($file = readdir($handle)) !== false) {
                        if($file != '.' && $file != '..') {
                            unlink($path."/".$file);
                        }
                }
            }
            
            
            @mkdir ($path, 0777, true);
        }
        
        return $path;
    }
    
}
