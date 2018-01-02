<?php

const CAT_TYPE_API=1;
const CAT_TYPE_DOC=2;
const CAT_TYPE_TEST_CASE=3;
const CAT_TYPE_HTTP=4;
const CAT_TYPE_CLASS_PHP=5;

class CatModel extends Model{

    public function getCat($cat_id){

        return t('cat')->getById($cat_id);
    }

    /**
    * 
    */
    public function getTypeCat($app_id,$type){

        return t('cat')->find(['app_id'=>$app_id,'type'=>$type,'stat'=>1]);
    }
}