<?php

class ApiModel extends DB{

    public function getApi($api_id){

        return $this->getById('kf_api',$api_id);
    }

    public function getApiParams($api_id){

        $api=$this->getApi($api_id);

        $api['params']=$this->getApiRequestParams($api_id);

        return $api;
    }

    public function addApi($title,$cat_id,$remark=''){

        $sql="INSERT INTO kf_test (title,cat_id,remark,stat,create_date) VALUES (?,?,?,1,NOW())";

        return $this->exec($sql,'sis',[$title,$cat_id,$remark]);
    }

    /**
     * 更新api接口
     */
    public function updateApi($test_id,$title,$cat_id,$remark){

        $sql="UPDATE kf_test SET title=?,cat_id=?,remark=?,update_date=NOW() WHERE id=?";

        $this->exec($sql,'sisi',[$title,$cat_id,$remark,$test_id]);
    }

    /**
     * 获取api请求参数
     * @param  int $api_id 
     * @return array
     */
    public function getApiRequestParams($api_id){

        return $this->exec("SELECT * FROM kf_api_params WHERE stat=1 AND api_id=".intval($api_id));
    }

    /**
     * 获取所有api接口
     * @return array
     */
    public function getAllApi(){

        $sql="SELECT * FROM kf_api WHERE stat=1";

        return $this->exec($sql);
    }

    public function getAllCatApi(){

        $api_list=$this->exec("SELECT * FROM kf_api WHERE stat=1");

        $cat_list=$this->exec("SELECT id,name FROM kf_cat WHERE type=1 AND stat=1");

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