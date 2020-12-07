<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initAutoload() {

        $moduleLoader = new Zend_Application_Module_Autoloader(array(
                    'namespace' => '',
                    'basePath' => APPLICATION_PATH));


		//Método para add caminhos e paths de classes novas para o autoload do zend definidas no metodo _initPathClassMap()
        $moduleLoader->addResourceTypes($this->_initPathClassMap());
        return $moduleLoader;
    }
    
    private function _initPathClassMap() {
        $pathAutoload = array(
            'rule' => array(
                'namespace' => 'Model_Rule',
                'path' => 'models/rule',
            ),
            'dao' => array(
                'namespace' => 'Model_Dao',
                'path' => 'models/dao',
            )
        );
        return $pathAutoload;
    }
    
    protected function _initRoutes() {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $router->addRoute("home",
            new Zend_Controller_Router_Route("area-aluno/:email",
                    array(
                        "module" => "default",
                        "controller" => "index",
                        "action" => "home"
                    )
            )
        );
        
        $router->addRoute("avaliacoes",
            new Zend_Controller_Router_Route("avaliacoes/:email",
                    array(
                        "module" => "default",
                        "controller" => "index",
                        "action" => "avaliacoes-aluno"
                    )
            )
        );
        
        $router->addRoute("treino",
            new Zend_Controller_Router_Route("treinos/:email",
                    array(
                        "module" => "default",
                        "controller" => "index",
                        "action" => "fichas-treino-aluno"
                    )
            )
        );
    }

}

