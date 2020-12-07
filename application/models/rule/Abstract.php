<?php

abstract class Model_Rule_Abstract implements Model_Rule_Interface {
    protected $_params = array();
    protected $_msg = "";

    public function  __construct() {
    }

    public function __get($key){
        return $this->_params[$key];
    }

    public function __set($key, $value){
        $this->_params[$key] = $value;
    }

    public function setParams($params){
        $this->_params = $params;
    }

    public function getParams(){
        return $this->_params;
    }

    public function setMsg($msg){
        $this->_msg = $msg;
    }

    public function getMsg(){
        return $this->_msg;
    }

}

