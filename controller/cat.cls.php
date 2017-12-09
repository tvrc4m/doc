<?php

class CatController extends Api {

    /**
     * api类别列表
     * @return 
     */
    public function index($params){

        $type=$params['type'];

        $cat_list=$this->getCatByType($type);

        switch ($type) {
            case self::CAT_TYPE_API:$title='API类别管理';$tab_selected='app';break;
            case self::CAT_TYPE_DOC:$title='文档类别管理';$tab_selected='doc';break;
            case self::CAT_TYPE_TEST_CASE:$title='测试用例类别管理';$tab_selected='test';break;
            case self::CAT_TYPE_HTTP:$title='发起请求例类别管理';$tab_selected='http';break;
            case self::CAT_TYPE_CLASS_PHP:$title='PHP类文档类别管理';$tab_selected='php';break;
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

        if(empty($id)) exit(json_encode(['errno'=>-1,'errmsg'=>'未指定类别']));
        if(empty($name)) exit(json_encode(['errno'=>-1,'errmsg'=>'名称不能为空']));

        $db=new DB();

        $sql="SELECT 1 FROM kf_cat WHERE id!={$id} AND name='{$name}'";

        $exists=$db->one($sql);

        if(!empty($exists)) exit(json_encode(['errno'=>-1,'errmsg'=>'名称不能重复']));

        $sql="UPDATE kf_cat SET name=? WHERE id=?";

        $db->exec($sql,'si',[$name,$id]);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    /**
     * api新增类别
     * @param  array $params 
     * @return 
     */
    public function add($params){

        $name=trim($params['name']);
        $type=$params['type'];

        if(empty($name)) exit(json_encode(['errno'=>-1,'errmsg'=>'名称不能为空']));

        empty($type) && $type=1;

        $db=new DB();

        $sql="SELECT 1 FROM kf_cat WHERE type='{$type}' AND name='{$name}'";

        $exists=$db->one($sql);

        if(!empty($exists)) exit(json_encode(['errno'=>-1,'errmsg'=>'名称不能重复']));

        $m_cat=require_model('cat');

        $m_cat->addCat($this->user_id,$name,$type);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    public function del($params){

        $id=trim($params['id']);

        if(empty($id)) exit(json_encode(['errno'=>-1,'errmsg'=>'未指定类别']));

        $db=new DB();

        $sql="SELECT * FROM kf_cat WHERE id=".intval($id);

        $detail=$db->one($sql);

        if($detail['type']==self::CAT_TYPE_API){

            $sql="SELECT count(*) as count FROM kf_api WHERE cat_id=".$id." AND stat=1";

            $count=$db->one($sql);

            if($count['count']>0) exit(json_encode(['errno'=>-1,'errmsg'=>'有关联接口,不能删除']));
        }elseif($detail['type']==self::CAT_TYPE_DOC){

            $sql="SELECT count(*) as count FROM kf_doc WHERE cat_id=".$id." AND stat=1";

            $count=$db->one($sql);

            if($count['count']>0) exit(json_encode(['errno'=>-1,'errmsg'=>'有关联文档,不能删除']));
        }elseif($detail['type']==self::CAT_TYPE_TEST_CASE){

            $sql="SELECT count(*) as count FROM kf_test WHERE cat_id=".$id." AND stat=1";

            $count=$db->one($sql);

            if($count['count']>0) exit(json_encode(['errno'=>-1,'errmsg'=>'有关联测试用例,不能删除']));
        }elseif($detail['type']==self::CAT_TYPE_HTTP){

            $sql="SELECT count(*) as count FROM kf_user_http WHERE cat_id=".$id." AND stat=1";

            $count=$db->one($sql);

            if($count['count']>0) exit(json_encode(['errno'=>-1,'errmsg'=>'有关联请求,不能删除']));
        }

        $sql="UPDATE kf_cat SET stat=0 WHERE id=?";

        $db->exec($sql,'i',[$id]);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    protected function actions(){

        return [
            ['name'=>'新增类别','url'=>'/cat/add?type='.$_GET['type'],'click'=>'addCat()']
        ];
    }
}