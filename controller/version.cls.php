<?php

class VersionController extends Api {

    /**
     * app版本号列表
     * @return 
     */
    public function index($params){

        $db=new DB();

        $sql="SELECT * FROM kf_app_version WHERE stat=1";

        $version_list=$db->find($sql);

        $tab_selected=$type='app';

        $this->display("app/version.html",['version_list'=>$version_list,'tab_selected'=>$tab_selected]);
    }

    /**
     * api新增类别
     * @param  array $params 
     * @return 
     */
    public function add($params){

        $name=trim($params['name']);

        if(empty($name)) exit(json_encode(['errno'=>-1,'errmsg'=>'版本号不能为空']));

        if(!preg_match('/^v\d+\.\d+\.\d+$/', $name)) exit(json_encode(['errno'=>-1,'errmsg'=>'版本号须以v开头.eg: v1.1.0']));

        $db=new DB();

        $sql="SELECT 1 FROM kf_app_version WHERE stat=1 AND name='{$name}'";

        $exists=$db->get($sql);

        if(!empty($exists)) exit(json_encode(['errno'=>-1,'errmsg'=>'版本号不能重复']));

        $sql="INSERT INTO kf_app_version (name,stat,create_date) VALUES (?,1,NOW())";

        $db->insert($sql,'s',[$name]);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    public function del($params){

        $id=trim($params['id']);

        if(empty($id)) exit(json_encode(['errno'=>-1,'errmsg'=>'未指定类别']));

        $db=new DB();

        $sql="SELECT * FROM kf_app_version WHERE id=".intval($id);

        $detail=$db->get($sql);

        $sql="SELECT count(*) as count FROM kf_api WHERE version='{$detail['name']}' AND stat=1";

        $count=$db->get($sql);

        if($count['count']>0) exit(json_encode(['errno'=>-1,'errmsg'=>'有关联接口,不能删除']));

        $sql="SELECT count(*) as count FROM kf_api_params WHERE version='{$detail['name']}' AND stat=1";

        $count=$db->get($sql);

        if($count['count']>0) exit(json_encode(['errno'=>-1,'errmsg'=>'有关联接口,不能删除']));

        $sql="SELECT count(*) as count FROM kf_api_return WHERE version='{$detail['name']}' AND stat=1";

        $count=$db->get($sql);

        if($count['count']>0) exit(json_encode(['errno'=>-1,'errmsg'=>'有关联接口,不能删除']));
    
        $sql="UPDATE kf_app_version SET stat=0 WHERE id=?";

        $db->update($sql,'i',[$id]);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    protected function actions(){

        return [
            ['name'=>'新增版本号','url'=>'/api/version/add','click'=>'addVersion()']
        ];
    }
}