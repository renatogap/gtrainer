<?php
class Utils_File {
    
    public static function verificaDispositivo() {
        //Instancia a Classe para detectar acesso via Mobile
        $detect = new Model_Rule_MobileDetect;
        
        //Identifica o tipe de Device acessado (computer, tablet ou phone)
        $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');

        return $deviceType;
    }
    
    public static function tplMessageFile() {
        $_messageTemplates = array(
                Zend_Validate_File_Upload::INI_SIZE            => "O arquivo '%value%' excedeu o tamanho definido no ini size.",
                Zend_Validate_File_Upload::FORM_SIZE           => "O arquivo '%value%' excedeu a definição do tamanho.",
                Zend_Validate_File_Upload::PARTIAL             => "O arquivo '%value%' foi transferido apenas parcialmente.",
                Zend_Validate_File_Upload::NO_FILE             => "O arquivo '%value%' não foi transferido.",
                Zend_Validate_File_Upload::NO_TMP_DIR          => "Diretório temporário não foi encontrado para o arquivo '%value%'.",
                Zend_Validate_File_Upload::CANT_WRITE          => "O arquivo '%value%' não pode ser escrito.",
                Zend_Validate_File_Upload::EXTENSION           => "TA extensão retornou um erro enquanto estava transferindo o arquivo '%value%'.",
                Zend_Validate_File_Upload::ATTACK              => "O arquivo '%value%' estava fazendo transferência ilegal, possível ataque.",
                Zend_Validate_File_Upload::FILE_NOT_FOUND      => "O arquivo '%value%' não encontrado.",
                Zend_Validate_File_Upload::UNKNOWN             => "Erro desconhecido enquanto estava sendo feita a transferência do arquivo '%value%'.",

                Zend_Validate_File_FilesSize::TOO_BIG          => "Todos os arquivos deveriam ter um tamanho Maximo de '%max%' mas foi detectado '%size%'.",
                Zend_Validate_File_FilesSize::TOO_SMALL        => "Todos os arquivos deveriam ter um tamanho mínimo de '%min%' mas foi detectado '%size%'.",
                Zend_Validate_File_FilesSize::NOT_READABLE     => "Um ou mais arquivos não podem ser lidos.",

                Zend_Validate_File_Extension::FALSE_EXTENSION  => "O arquivo '%value%' tem uma extensão incompatível.",
                Zend_Validate_File_Extension::NOT_FOUND        => "O arquivo '%value%' não foi encontrado.",

                Zend_Validate_File_ImageSize::WIDTH_TOO_BIG    => "Largura máxima permitida para imagem '%value%' deveria ser '%maxwidth%', mas '%width%' foi detectado.",
                Zend_Validate_File_ImageSize::WIDTH_TOO_SMALL  => "Largura mínima esperada para imagem '%value%', deveria ser '%minwidth%' mas '%width%' foi detectado.",
                Zend_Validate_File_ImageSize::HEIGHT_TOO_BIG   => "Comprimento máximo permitido para imagem '%value%' deveria ser '%maxheight%' mas '%height%' foi detectado.",
                Zend_Validate_File_ImageSize::HEIGHT_TOO_SMALL => "Comprimento minimo esperado para a imagem '%value%' deveria ser '%minheight%' mas '%height%' foi detectado.",
                Zend_Validate_File_ImageSize::NOT_DETECTED     => "O tamanho da imagem '%value%' não poderia ser detectado.",
                Zend_Validate_File_ImageSize::NOT_READABLE     => "A imagem '%value%' não pode ser lida."
        );

        return $_messageTemplates;
    }
    
    public static function removeAcentos( $texto ) {
        $array1 = array(   "á", "à", "â", "ã", "ä", "é", "è", "ê", "ë", "ẽ", "í", "ì", "î", "ï", "ĩ", "ó", "ò", "ô", "õ", "ö", "ú", "ù", "û", "ü", "ũ", "ç"
                , "Á", "À", "Â", "Ã", "Ä", "É", "È", "Ê", "Ë", "Ẽ", "Í", "Ì", "Î", "Ï", "Ĩ", "Ó", "Ò", "Ô", "Õ", "Ö", "Ú", "Ù", "Û", "Ü", "Ũ", "Ç" );
        $array2 = array(   "a", "a", "a", "a", "a", "e", "e", "e", "e", "e", "i", "i", "i", "i", "i", "o", "o", "o", "o", "o", "u", "u", "u", "u", "u", "c"
                , "A", "A", "A", "A", "A", "E", "E", "E", "E", "E", "I", "I", "I", "I", "I", "O", "O", "O", "O", "O", "U", "U", "U", "U", "U", "C" );
        return str_replace( $array1, $array2, $texto );
    }
    
}