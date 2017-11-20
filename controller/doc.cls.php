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

        $sql="SELECT * FROM kf_doc WHERE id=".intval($id);

        $db=new DB();

        $detail=$db->get($sql);

        $data['tab_selected']='doc';

        $data['api_list']=$this->getDocList();

        $data['detail']=$detail;
        $data['current']=$id;

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

        $sql="SELECT * FROM kf_doc WHERE id=".intval($id);

        $db=new DB();

        $detail=$db->get($sql);

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

        $db=new DB();

        if($id){

            $sql="UPDATE kf_doc SET title=?,cat_id=?,content=?,update_date=NOW() WHERE id=?";

            $db->update($sql,'sisi',[$title,$cat_id,$content,$id]);
        }else{

            $sql="INSERT INTO kf_doc (title,cat_id,content,stat,create_date) VALUES (?,?,?,1,NOW())";

            $id=$db->insert($sql,'sis',[$title,$cat_id,$content]);
        }

        header("Location:/doc/detail/".$id);
    }

    protected function actions(){

        return [
            ['name'=>'类别管理','url'=>'/doc/cat','click'=>'redirectPage(this)'],
            ['name'=>'新建文档','url'=>'/doc/add','click'=>'redirectPage(this)'],
        ];
    }
}