<?php

class DocController extends Api {

    public function index($params){

        $this->detail(['id'=>2]);
    }

    /**
     * 获取文档详情
     * @param  array $params 
     * @return 
     */
    public function detail($params){

        $id=$params['id'];

        if(empty($id)) exit('文档不存在');

        $db=DB::init();

        $detail=$db->get('kf_doc',['stat'=>1,'id'=>$id]);

        $data['tab_selected']='doc';

        $data['api_list']=$this->getDocList();

        $data['detail']=$detail;
        $data['current']=$id;
        $data['title']=$detail['title'];

        $this->display('doc/detail.html',$data);
    }

    /**
     * 新建文档
     * @param 
     */
    public function add($params){

        $data['tab_selected']='doc';

        $data['cats']=$this->getCatByType(self::CAT_TYPE_DOC);

        $this->display("doc/add.html",$data);
    }

    public function edit($params){

        $id=$params['id'];

        $db=DB::init();

        $detail=$db->getById('kf_doc',$id);

        if(empty($detail)) exit('指定文档不存在');

        $data['tab_selected']='doc';

        $data['detail']=$detail;

        $data['cats']=$this->getCatByType(self::CAT_TYPE_DOC);

        $this->display("doc/edit.html",$data);
    }

    /**
     * 保存文档
     * @param  array $params 
     * @return 
     */
    public function save($params){

        $title=$params['title'];
        $content=$params['content'];
        $cat_id=$params['cat_id'];
        $id=$params['id'];

        if(empty($title)) exit('标题不能为空');
        if(empty($content)) exit('内容不能为空');

        $db=DB::init();

        if($id){

            $db->update('kf_doc',['title'=>$title,'cat_id'=>$cat_id,'content'=>$content,'update_date'=>date('Y-m-d H:i:s')],['id'=>$id]);
        }else{

            $id=$db->insert('kf_doc',['title'=>$title,'cat_id'=>$cat_id,'content'=>$content,'update_date'=>date('Y-m-d H:i:s')]);
        }

        header("Location:/doc/detail/".$id);
    }

    /**
     * 删除指定文档
     * @param  array $params 
     * @return 
     */
    public function del($params){

        $id=$params['id'];

        if(empty($id)) exit(json_encode(['errno'=>-1,'errmsg'=>'未指定文档']));

        $db=DB::init();

        $db->update('kf_doc',['stat'=>0],['id'=>$id]);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    protected function actions(){

        return [
            ['name'=>'类别管理','url'=>'/doc/cat','click'=>'redirectPage(this)'],
            ['name'=>'新建文档','url'=>'/doc/add','click'=>'redirectPage(this)'],
        ];
    }
}