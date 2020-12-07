<?php
class Utils_Print {
    public static function printvar($args) {
        $oRequest = new Zend_Controller_Request_Http();

        if($oRequest->isXmlHttpRequest()) {
            self::printVarAjax($args);
        } else {
            $args = func_get_args();
            $dbt = debug_backtrace();
            $linha = $dbt[0]['line'];
            $arquivo = $dbt[0]['file'];
            echo "<fieldset style='border:1px solid; border-color:#F00;background-color:#FFF000;legend'><b>Arquivo:</b>$arquivo<b><br>Linha:</b><legend><b>Debug On : printvar()</b></legend> $linha</fieldset>";

            foreach($args as $key => $arg) {
                echo "<fieldset style='background-color:#CBA; border:1px solid; border-color:#00F;'><legend><b>ARG[$key]</b><legend>";
                echo "<pre style='background-color:#CBA; width:100%; heigth:100%;'>";
                print_r($arg);
                echo "</pre>";
                echo "</fieldset><br />";
            }
        }
    }
    public static function printvardie($args) {
        $oRequest = new Zend_Controller_Request_Http();

        if($oRequest->isXmlHttpRequest()) {
            self::printVarDieAjax($args);
        } else {
            $args = func_get_args();
            $dbt = debug_backtrace();
            $linha = $dbt[0]['line'];
            $arquivo = $dbt[0]['file'];
            echo "<fieldset style='border:1px solid; border-color:#F00;background-color:#FFF000;legend'><b>Arquivo:</b>$arquivo<b><br>Linha:</b><legend><b>Debug On : printvar()</b></legend> $linha</fieldset>";

            foreach($args as $key => $arg) {
                echo "<fieldset style='background-color:#CBA; border:1px solid; border-color:#00F;'><legend><b>ARG[$key]</b><legend>";
                echo "<pre style='background-color:#CBA; width:100%; heigth:100%;'>";
                print_r($arg);
                echo "</pre>";
                echo "</fieldset><br />";
            }
            exit ();
        }
    }
    /**
     * Mesma funcao do printvar mas não imprime com formatacao html
     * facilitando a exibicao no firebug
     * @param <type> $args
     * @since 27/05/2009
     * @author Philipe Barra
     */
    public static function printVarAjax($args) {
        $args = func_get_args();
        $dbt = debug_backtrace();
        $linha   = $dbt[0]['line'];
        $arquivo = $dbt[0]['file'];
        echo "=================================================================================\n";
        echo "Arquivo:".$arquivo."\nLinha:$linha\nDebug On : printvarajax ( )\n";
        echo "=================================================================================\n";

        foreach($args as $idx=> $arg) {
            echo "-----  ARG[$idx]  -----\n";
            print_r($arg);
            echo "\n \n";
        }
    }

    /**
     * Mesma funcao do printdie mas não imprime com formatacao html
     * facilitando a exibicao no firebug
     * @param <type> $args
     * @since 25/05/2009
     * @author Philipe Barra
     */
    public static function printVarDieAjax($args) {
        $args = func_get_args();
        $dbt = debug_backtrace();
        $linha   = $dbt[0]['line'];
        $arquivo = $dbt[0]['file'];
        echo "=================================================================================\n";
        echo "Arquivo:".$arquivo."\nLinha:$linha\nDebug On : printvardieajax ( )\n";
        echo "=================================================================================\n";

        foreach($args as $idx=> $arg) {
            echo "-----  ARG[$idx]  -----\n";
            print_r($arg);
            echo "\n \n";
        }
        exit ();
    }
}