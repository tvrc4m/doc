<?php

class CatController extends BaseAuth {

    /**
     * api类别列表
     * @return 
     */
    public function index($params){

        $type=$params['id'];
        $cat_list=t('cat')->find(['type'=>$type,'app_id'=>$this->app_id,'stat'=>1]);
        
        switch ($type) {
            case self::CAT_TYPE_API:$title='API类别管理';$this->bar_api=true;break;
            case self::CAT_TYPE_DOC:$title='文档类别管理';$this->bar_doc=true;break;
            case self::CAT_TYPE_TEST_CASE:$title='测试用例类别管理';$this->bar_test=true;break;
            case self::CAT_TYPE_HTTP:$title='发起请求例类别管理';$this->bar_http=true;break;
            case self::CAT_TYPE_CLASS_PHP:$title='PHP类文档类别管理';$this->bar_php=true;break;
        }

        $this->display("app/cat.html",['cat_list'=>$cat_list,'tab_selected'=>$tab_selected,'title'=>$title,'type'=>$type]);
    }

    /**
     * api编辑
     * @param  array $params 
     * @return 
     */
    public function edit($params){

        $id=$params['id'];
        $name=trim($params['name']);

        if(empty($id)) $this->error('未指定类别');
        if(empty($name)) $this->error('名称不能为空');

        $exists=t('cat')->get(['id'=>['$not'=>$id],'app_id'=>$this->app_id,'name'=>$name]);

        if(!empty($exists)) $this->error('名称不能重复');

        t('cat')->update(['name'=>$name],['id'=>$id]);

        $this->ok();
    }

    /**
     * api新增类别
     * @param  array $params 
     * @return 
     */
    public function add($params){

        $name=trim($params['name']);
        $type=$params['type'];

        if(empty($name)) $this->error('名称不能为空');

        empty($type) && $type=1;

        $exists=t('cat')->get(['type'=>$type,'name'=>$name,'app_id'=>$this->app_id],$fields='1');

        if(!empty($exists)) $this->error('名称不能重复');

        t('cat')->insert(['user_id'=>$this->user_id,'app_id'=>$this->app_id,'name'=>$name,'type'=>$type,'stat'=>1]);

        $this->ok();
    }

    public function del($params){

        $id=trim($params['id']);

        if(empty($id)) $this->error('未指定类别');

        $detail=t('cat')->getById($id);

        switch ($detail['type']) {
            case self::CAT_TYPE_API:$table='api';break;
            case self::CAT_TYPE_DOC:$table='doc';break;
            case self::CAT_TYPE_TEST_CASE:$table='test';break;
            case self::CAT_TYPE_HTTP:$table='user_http';break;
        }

        $count=t($table)->count(['cat_id'=>$id,'stat'=>1]);

        if($count) $this->error('有关联数据,不能删除');

        t('cat')->update(['stat'=>0],['id'=>$id]);

        $this->ok();
    }

    protected function actions(){

        return [
            ['name'=>'新增类别','url'=>'/app/cat/add?type='.$_GET['type'],'click'=>'addCat()']
        ];
    }
}