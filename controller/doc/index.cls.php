<?php

class IndexController extends BaseAuth {

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

        $detail=t('doc')->get(['stat'=>1,'id'=>$id]);

        $data['tab_selected']='doc';

        $cat_list=$this->_get_doc_cat();

        $doc_list=t('doc')->find(['stat'=>1]);

        $cat_result=$doc_result=[];

        foreach($cat_list as $cat) $cat_result[$cat['id']]=$cat['name'];
        
        foreach ($doc_list as $doc) {
            // 类别名称
            $cat=$cat_result[$doc['cat_id']];

            $doc['side_url']='/doc/detail/'.$doc['id'].'#'.$doc['id'];

            $doc['code']=$doc['id'];
            // 按类别分组
            $doc_result[$cat][]=$doc;
        }

        $data['api_list']=$doc_result;

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

        $data['cats']=$this->_get_doc_cat();

        $this->display("doc/add.html",$data);
    }

    public function edit($params){

        $id=$params['id'];

        $detail=t('doc')->getById($id);

        if(empty($detail)) exit('指定文档不存在');

        $data['tab_selected']='doc';

        $data['detail']=$detail;

        $data['cats']=$this->_get_doc_cat();

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

        $doc_data=['title'=>$title,'cat_id'=>$cat_id,'content'=>$content];

        if($id){

            t('doc')->update($doc_data,['id'=>$id]);
        }else{

            $id=t('doc')->insert($doc_data);
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

        if(empty($id)) $this->error('未指定文档');

        t('doc')->update(['stat'=>0],['id'=>$id]);

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    protected function actions(){

        return [
            ['name'=>'类别管理','url'=>'/app/cat/'.self::CAT_TYPE_DOC,'click'=>'redirectPage(this)'],
            ['name'=>'新建文档','url'=>'/doc/add','click'=>'redirectPage(this)'],
        ];
    }

    /**
     * 获取doc关联的cat
     * @return array
     */
    private function _get_doc_cat(){

        return t('cat')->find(['type'=>self::CAT_TYPE_DOC,'stat'=>1]);
    }
}