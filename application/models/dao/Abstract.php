<?php


abstract class Model_Dao_Abstract extends Zend_Db_Table_Row_Abstract {
   
    public function save(){
        $this->beforeSave();

        parent::save();
    }

    public function delete() {
        $this->beforeDelete();

        parent::delete();
    }

    public function beforeSave(){
       
    }

    public function beforeDelete(){
       
    }


}