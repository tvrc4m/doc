<?php

class VersionController extends BaseAuth {

    /**
     * 选中bar
     * @var boolean
     */
    protected $bar_api=true;

    /**
     * app版本号列表
     * @return 
     */
    public function index($params){

        $version_list=t('app_version')->find(['stat'=>1]);

        $tab_selected=$type='api';

        $this->display("api/version.html",['version_list'=>$version_list,'title'=>'APP版本管理','tab_selected'=>$tab_selected]);
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

        $exists=t('app_version')->get(['stat'=>1,'name'=>$name],'1');

        if(!empty($exists)) exit(json_encode(['errno'=>-1,'errmsg'=>'版本号不能重复']));

        t('app_version')->insert(['name'=>$name,'remark'=>$remark,'stat'=>1]);

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

        t('app_version')->update(['remark'=>$remark],['id'=>$version_id]);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    public function del($params){

        $id=trim($params['id']);

        if(empty($id)) exit(json_encode(['errno'=>-1,'errmsg'=>'未指定类别']));

        $detail=t('app_version')->getById($id);

        $filter=['version'=>$detail['name'],'stat'=>1];

        $count=t('api')->count($filter);

        if($count) exit(json_encode(['errno'=>-1,'errmsg'=>'有关联接口,不能删除']));

        $count=t('api_params')->count($filter);

        if($count) exit(json_encode(['errno'=>-1,'errmsg'=>'有关联接口,不能删除']));

        $count=t('api_return')->count($filter);

        if($count) exit(json_encode(['errno'=>-1,'errmsg'=>'有关联接口,不能删除']));

        t('app_version')->update(['stat'=>0],['id'=>$id]);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    protected function actions(){

        return [
            ['name'=>'新增版本号','url'=>'/api/version/add','click'=>'addVersion()']
        ];
    }
}