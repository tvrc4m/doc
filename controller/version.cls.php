<?php

class VersionController extends Api {

    /**
     * app版本号列表
     * @return 
     */
    public function index($params){

        $db=DB::init();

        $version_list=$db->find('kf_app_version',['stat'=>1]);

        $tab_selected=$type='app';

        $this->display("app/version.html",['version_list'=>$version_list,'title'=>'APP版本管理','tab_selected'=>$tab_selected]);
    }

    /**
     * api新增类别
     * @param  array $params 
     * @return 
     */
    public function add($params){

        $name=trim($params['name']);
        $remark=trim($params['remark']);

        if(empty($name)) exit(json_encode(['errno'=>-1,'errmsg'=>'版本号不能为空']));

        if(!preg_match('/^v\d+\.\d+\.\d+$/', $name)) exit(json_encode(['errno'=>-1,'errmsg'=>'版本号须以v开头.eg: v1.1.0']));

        $db=db();

        $sql="SELECT 1 FROM kf_app_version WHERE stat=1 AND name='{$name}'";

        $exists=$db->one($sql);

        if(!empty($exists)) exit(json_encode(['errno'=>-1,'errmsg'=>'版本号不能重复']));

        $sql="INSERT INTO kf_app_version (name,remark,stat,create_date) VALUES (?,?,1,NOW())";

        $db->exec($sql,'ss',[$name,$remark]);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    /**
     * version编辑
     * @param  array $params 
     * @return 
     */
    public function edit($params){

        $version_id=$params['id'];
        $remark=trim($params['remark']);

        $m_version=require_model('version');

        $m_version->updateVersion($version_id,$remark);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    public function del($params){

        $id=trim($params['id']);

        if(empty($id)) exit(json_encode(['errno'=>-1,'errmsg'=>'未指定类别']));

        $db=DB::init();

        $detail=$db->getById('kf_app_version',$id);

        $count=$db->count('kf_api',['version'=>$detail['name'],'stat'=>1]);

        if($count>0) exit(json_encode(['errno'=>-1,'errmsg'=>'有关联接口,不能删除']));

        $count=$db->count('kf_api_params',['version'=>$detail['name'],'stat'=>1]);

        if($count>0) exit(json_encode(['errno'=>-1,'errmsg'=>'有关联接口,不能删除']));

        $count=$db->count('kf_api_return',['version'=>$detail['name'],'stat'=>1]);

        if($count>0) exit(json_encode(['errno'=>-1,'errmsg'=>'有关联接口,不能删除']));
    
        $db->update('kf_app_version',['stat'=>0],['id'=>$id]);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    protected function actions(){

        return [
            ['name'=>'新增版本号','url'=>'/api/version/add','click'=>'addVersion()']
        ];
    }
}