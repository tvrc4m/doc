<?php

class ApiModel extends Model{

    public function getApi($api_id){

        return t('api')->getById($api_id);
    }

    public function getApiParams($api_id){

        $api=$this->getApi($api_id);

        $api['params']=t('api_params')->find(['stat'=>1,'api_id'=>$api_id]);

        return $api;
    }

    
    public function getAllCatApi(){

        $api_list=t('api')->find(['stat'=>1]);

        $cat_list=t('cat')->find(['type'=>1,'stat'=>1],['id','name']);

        $cat_result=$api_result=[];

        foreach($cat_list as $cat) $cat_result[$cat['id']]=$cat['name'];

        foreach ($api_list as $api) {
            // 类别名称
            $cat=$cat_result[$api['cat_id']];
            // 按类别分组
            $api_result[$cat][]=$api;
        }
        // print_r($api_result);exit;
        return $api_result;
    }
}