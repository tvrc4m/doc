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

        $exists=$db->get($sql);

        if(!empty($exists)) exit(json_encode(['errno'=>-1,'errmsg'=>'名称不能重复']));

        $sql="UPDATE kf_cat SET name=? WHERE id=?";

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
        $type=$params['type'];

        if(empty($name)) exit(json_encode(['errno'=>-1,'errmsg'=>'名称不能为空']));

        empty($type) && $type=1;

        $db=new DB();

        $sql="SELECT 1 FROM kf_cat WHERE type='{$type}' AND name='{$name}'";

        $exists=$db->get($sql);

        if(!empty($exists)) exit(json_encode(['errno'=>-1,'errmsg'=>'名称不能重复']));

        $sql="INSERT INTO kf_cat (name,type,create_date) VALUES (?,?,NOW())";

        $db->insert($sql,'si',[$name,$type]);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    public function del($params){

        $id=trim($params['id']);

        if(empty($id)) exit(json_encode(['errno'=>-1,'errmsg'=>'未指定类别']));

        $db=new DB();

        $sql="SELECT * FROM kf_cat WHERE id=".intval($id);

        $detail=$db->get($sql);

        if($detail['type']==1){

            $sql="SELECT count(*) as count FROM kf_api WHERE cat_id=".$id." AND stat=1";

            $count=$db->get($sql);

            if($count['count']>0) exit(json_encode(['errno'=>-1,'errmsg'=>'有关联接口,不能删除']));
        }elseif($detail['type']==2){

            $sql="SELECT count(*) as count FROM kf_doc WHERE cat_id=".$id." AND stat=1";

            $count=$db->get($sql);

            if($count['count']>0) exit(json_encode(['errno'=>-1,'errmsg'=>'有关联文档,不能删除']));
        }

        $sql="UPDATE kf_cat SET stat=0 WHERE id=?";

        $db->update($sql,'i',[$id]);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    protected function actions(){

        if($_GET['type']==self::CAT_TYPE_API){

            return [
                ['name'=>'新增类别','url'=>'/api/cat/add','click'=>'addCat()']
            ];
        }elseif($_GET['type']==self::CAT_TYPE_DOC){

            return [
                ['name'=>'新增类别','url'=>'/doc/cat/add','click'=>'addCat()']
            ];
        }elseif($_GET['type']==self::CAT_TYPE_TEST_CASE){

            return [
                ['name'=>'新增类别','url'=>'/test/cat/add','click'=>'addCat()']
            ];
        }elseif($_GET['type']==self::CAT_TYPE_HTTP){

            return [
                ['name'=>'新增类别','url'=>'/cat/add?type=4','click'=>'addCat()']
            ];
        }
    }
}