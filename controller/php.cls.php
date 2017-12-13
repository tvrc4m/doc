<?php

class PhpController extends Api {

    /**
     * app全部的api接口
     * @return 
     */
    public function index(){

        $db=db();

        $cat_list=$db->find('kf_cat',['type'=>self::CAT_TYPE_CLASS_PHP,'stat'=>1]);
        $class_list=$db->find('kf_class',['stat'=>1]);
        $class_method_list=$db->find('kf_class_method',['stat'=>1],['class_id'=>'asc']);        
        $class_method_params_list=$db->find('kf_class_method_params',['stat'=>1],['method_id'=>'asc']);        

        $cat_result=$class_result=$class_method_result=$class_method_params_result=[];

        foreach($cat_list as $cat) $cat_result[$cat['id']]=$cat['name'];
        foreach($class_method_list as $params) $class_method_result[$params['class_id']][]=$params;
        foreach($class_method_params_list as $params) $class_method_params_result[$params['method_id']][]=$params;
        
        foreach ($class_list as $class) {
            // 类别名称
            $cat=$cat_result[$class['cat_id']];
            // // 组装请求参数
            // $class['params']=$params_result[$class['id']];
            // // 组装返回参数
            // $class['return']=$return_result[$class['id']];

            // $example=$this->getclassExample($class['id']);

            // empty($example) && $example=$this->getclassExampleByclass($class);

            // $class['example']=$example;

            // if($action=='class'){

            //     $class['side_url']='/class/app#'.$class['code'];
            // }elseif($action=='http'){
            //     $class['side_url']='/class/http#'.$class['code'];
            // }
            // 按类别分组
            $class_result[$cat][]=$class;
        }
        // print_r($class_result);exit;
        $this->display("php/index.html",['class_result'=>$class_result,'title'=>'PHP类文档','tab_selected'=>'php']);
    }

    /**
     * 指定的某个api接口
     * @param array $params 接收GET请求数组
     * @return 
     */
    public function detail($params){

        $api=$params['api'];

        empty($api) && exit('api接口地址不存在');

        $this->export(self::API_TYPE_APP,$api);
    }

    public function add($params){

        $db=db();

        $cats=$db->find('kf_cat',['type'=>self::CAT_TYPE_CLASS_PHP]);

        $this->display("php/add.html",['tab_selected'=>'php','versions'=>json_encode($versions),'cats'=>json_encode($cats),'title'=>'添加PHP类文档']);
    }

    /**
     * api编辑
     * @param  array $params 
     * @return 
     */
    public function edit($params){

        $code=$params['code'];

        $api=$this->getApi($code);

        $api['cats']=$this->getCatByType(self::CAT_TYPE_CLASS_PHP);

        $api['versions']=$this->getAppVersion();
        // print_r($cat_list);exit;
        $this->display("app/edit.html",['tab_selected'=>'app','api'=>json_encode($api,JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),'title'=>'编辑APP接口文档']);
    }

    /**
     * 编辑保存功能
     * @param  array $params 
     * @return 
     */
    public function save($data){

        $id=$data['id'];
        $is_add=$data['add'];
        $title=$data['title'];
        $url=$data['url'];
        $cat_id=$data['cat_id'];
        $params=$data['params'];
        $return=$data['return'];
        $version=$data['version'];
        $remark=$data['remark'];

        if(!$is_add && empty($id)) exit(json_encode(['errno'=>-1,'errmsg'=>'未指定接口']));

        if(empty($title)) exit(json_encode(['errno'=>-1,'errmsg'=>'接口标题不能为空']));
        if(empty($url)) exit(json_encode(['errno'=>-1,'errmsg'=>'接口地址不能为空']));
        if(empty($cat_id)) exit(json_encode(['errno'=>-1,'errmsg'=>'接口类型不能为空']));
        
        foreach ($params as $name=>$param) {
            if(empty($name) || is_numeric($name))  exit(json_encode(['errno'=>-1,'errmsg'=>'请求参数名不能为空或为数字']));
            if(empty($param['type']))  exit(json_encode(['errno'=>-1,'errmsg'=>'请求参数类型不能为空']));
        }

        // 返回参数检测
        foreach ($return as $name=>$param) {
            if(empty($name) || is_numeric($name))  exit(json_encode(['errno'=>-1,'errmsg'=>'返回参数名不能为空或为数字']));
            if(empty($param['type']))  exit(json_encode(['errno'=>-1,'errmsg'=>'返回参数类型不能为空']));
        }

        list($app,$controller,$action)=explode('/', $url);

        $code=strtolower($controller.'_'.$action);

        try{

            $db=db();

            $db->start();

            if($id){

                $sql="UPDATE kf_api SET title=?,url=?,cat_id=?,code=?,version=?,remark=?,update_date=? WHERE id=?";

                $db->exec($sql,'ssissssi',[$title,$url,$cat_id,$code,$version,$remark,date('Y-m-d H:i:s'),$id]);
            }elseif($is_add){

                $sql="INSERT kf_api (title,url,cat_id,code,version,remark,create_date,stat) VALUES (?,?,?,?,?,?,?,?)";

                $id=$db->exec($sql,'ssissssi',[$title,$url,$cat_id,$code,$version,$remark,date('Y-m-d H:i:s'),1]);
            }else{

                throw new Exception('不支持的操作');
            }

            // params
            $params_exists=array_filter(array_column($params, 'id'));

            if(!empty($params_exists)){

                $sql="UPDATE kf_api_params SET stat=0 WHERE id NOT IN (".implode(',', $params_exists).") AND api_id=?";

                $db->exec($sql,'i',[$id]);
            }

            foreach ($params as $name=>$param) {

                if($param['id']){

                    $sql="UPDATE kf_api_params SET name=?,type=?,must=?,version=?,remark=?,update_date=? WHERE id=?";

                    $db->exec($sql,'ssisssi',[$name,$param['type'],intval($param['must']),$param['version'],$param['remark'],date('Y-m-d H:i:s'),$param['id']]);
                }else{

                    $sql="INSERT INTO kf_api_params (name,type,must,version,remark,create_date,api_id) VALUES (?,?,?,?,?,?,?)";

                    $db->exec($sql,'ssisssi',[$name,$param['type'],intval($param['must']),$param['version'],$param['remark'],date('Y-m-d H:i:s'),$id]);
                }
            }

            // return
            $return_exists=array_filter(array_column($return, 'id'));
            
            if(!empty($return_exists)){

                $sql="UPDATE kf_api_return SET stat=0 WHERE id NOT IN (".implode(',', $return_exists).") AND api_id=?";
                
                $db->exec($sql,'i',[$id]);
            }

            foreach ($return as $name=>$ret) {

                if($ret['id']){

                    $sql="UPDATE kf_api_return SET name=?,type=?,must=?,version=?,remark=?,update_date=? WHERE id=?";

                    $db->exec($sql,'ssisssi',[$name,$ret['type'],intval($ret['must']),$ret['version'],$ret['remark'],date('Y-m-d H:i:s'),$ret['id']]);
                }else{

                    $sql="INSERT INTO kf_api_return (name,type,must,version,remark,create_date,api_id) VALUES (?,?,?,?,?,?,?)";

                    $db->exec($sql,'ssisssi',[$name,$ret['type'],intval($ret['must']),$ret['version'],$ret['remark'],date('Y-m-d H:i:s'),$id]);
                }
            }

            $db->commit();

            exit(json_encode(['errno'=>0,'redirect'=>'/api/app#'.$code]));

        }catch(Exception $e){

            $db->rollback();

            exit(json_encode(['errno'=>-1,'errmsg'=>$e->getMessage()]));
        }
    }

    protected function actions(){

        return [
            ['name'=>'类别管理','url'=>'/php/cat','click'=>'redirectPage(this)'],
            ['name'=>'新增类文档','url'=>'/php/add','click'=>'redirectPage(this)']
        ];
    }
}