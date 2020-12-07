<?php
/**
 * Interface para definir o contrato que as classe Model_Business terão que seguir
 *
 * @author paulo
 */
interface Model_Rule_Interface {
    public function __get($key);
    public function __set($key, $value);
    public function setParams($params);
    public function getParams();
    public function setMsg($msg);
    public function getMsg();
}
