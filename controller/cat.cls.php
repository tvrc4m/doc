<?php

class CatController extends Api {

    /**
     * api类别列表
     * @return 
     */
    public function index(){

        $cat_list=$this->getApiCat();

        $this->display("app/cat.html",['cat_list'=>$cat_list,'tab_selected'=>'app']);
    }

    /**
     * api编辑
     * @param  array $params 
     * @return 
     */
    public function edit($params){

        $id=$params['id'];
        $name=trim($params['name']);

        if(empty($id)) exit(json_encode(['errno'=>-1,'errmsg'=>'未指定类别']));
        if(empty($name)) exit(json_encode(['errno'=>-1,'errmsg'=>'名称不能为空']));

        $db=new DB();

        $sql="SELECT 1 FROM kf_api_cat WHERE id!={$id} AND name='{$name}'";

        $exists=$db->get($sql);

        if(!empty($exists)) exit(json_encode(['errno'=>-1,'errmsg'=>'名称不能重复']));

        $sql="UPDATE kf_api_cat SET name=? WHERE id=?";

        $db->update($sql,'si',[$name,$id]);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    /**
     * api新增类别
     * @param  array $params 
     * @return 
     */
    public function add($params){

        $name=trim($params['name']);

        if(empty($name)) exit(json_encode(['errno'=>-1,'errmsg'=>'名称不能为空']));

        $db=new DB();

        $sql="SELECT 1 FROM kf_api_cat WHERE name='{$name}'";

        $exists=$db->get($sql);

        if(!empty($exists)) exit(json_encode(['errno'=>-1,'errmsg'=>'名称不能重复']));

        $sql="INSERT INTO kf_api_cat (name,create_date) VALUES (?,NOW())";

        $db->insert($sql,'s',[$name]);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    public function del($params){

        $id=trim($params['id']);

        if(empty($id)) exit(json_encode(['errno'=>-1,'errmsg'=>'未指定类别']));

        $db=new DB();

        $sql="SELECT count(*) as count FROM kf_api WHERE cat_id=".$id." AND stat=1";

        $count=$db->get($sql);

        if($count['count']>0) exit(json_encode(['errno'=>-1,'errmsg'=>'有关联接口,不能删除']));

        $sql="UPDATE kf_api_cat SET stat=0 WHERE id=?";

        $db->update($sql,'i',[$id]);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    public function actions(){

        return [
            ['name'=>'新增类别','url'=>'/api/cat/add','click'=>'addCat()']
        ];
    }
}