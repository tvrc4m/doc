<?php

class AppController extends Api {

    /**
     * app全部的api接口
     * @return 
     */
    public function index(){

        $api_common=$this->getCommonApi();

        $api_list=$this->getApiList('api');
        
        $this->display("app/content.html",['api_list'=>$api_list,'api_common'=>$api_common,'tab_selected'=>'app']);
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

        $cats=$this->getApiCat();

        $versions=$this->getAppVersion();

        $this->display("app/add.html",['tab_selected'=>'app','versions'=>json_encode($versions),'cats'=>json_encode($cats)]);
    }

    /**
     * api编辑
     * @param  array $params 
     * @return 
     */
    public function edit($params){

        $code=$params['code'];

        $api=$this->getApi($code);

        $api['cats']=$this->getApiCat();

        $api['versions']=$this->getAppVersion();
        // print_r($cat_list);exit;
        $this->display("app/edit.html",['tab_selected'=>'app','api'=>json_encode($api,JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)]);
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

            $db=new DB();

            $db->start();

            if($id){

                $sql="UPDATE kf_api SET title=?,url=?,cat_id=?,code=?,version=?,remark=?,update_date=? WHERE id=?";

                $db->update($sql,'ssissssi',[$title,$url,$cat_id,$code,$version,$remark,date('Y-m-d H:i:s'),$id]);
            }elseif($is_add){

                $sql="INSERT kf_api (title,url,cat_id,code,version,remark,create_date,stat) VALUES (?,?,?,?,?,?,?,?)";

                $id=$db->insert($sql,'ssissssi',[$title,$url,$cat_id,$code,$version,$remark,date('Y-m-d H:i:s'),1]);
            }else{

                throw new Exception('不支持的操作');
            }

            // params
            $params_exists=array_filter(array_column($params, 'id'));

            if(!empty($params_exists)){

                $sql="UPDATE kf_api_params SET stat=0 WHERE id NOT IN (".implode(',', $params_exists).") AND api_id=?";

                $db->update($sql,'i',[$id]);
            }

            foreach ($params as $name=>$param) {

                if($param['id']){

                    $sql="UPDATE kf_api_params SET name=?,type=?,must=?,version=?,remark=?,update_date=? WHERE id=?";

                    $db->update($sql,'ssisssi',[$name,$param['type'],intval($param['must']),$param['version'],$param['remark'],date('Y-m-d H:i:s'),$param['id']]);
                }else{

                    $sql="INSERT INTO kf_api_params (name,type,must,version,remark,create_date,api_id) VALUES (?,?,?,?,?,?,?)";

                    $db->insert($sql,'ssisssi',[$name,$param['type'],intval($param['must']),$param['version'],$param['remark'],date('Y-m-d H:i:s'),$id]);
                }
            }

            // return
            $return_exists=array_filter(array_column($return, 'id'));
            
            if(!empty($return_exists)){

                $sql="UPDATE kf_api_return SET stat=0 WHERE id NOT IN (".implode(',', $return_exists).") AND api_id=?";
                
                $db->update($sql,'i',[$id]);
            }

            foreach ($return as $name=>$ret) {

                if($ret['id']){

                    $sql="UPDATE kf_api_return SET name=?,type=?,must=?,version=?,remark=?,update_date=? WHERE id=?";

                    $db->update($sql,'ssisssi',[$name,$ret['type'],intval($ret['must']),$ret['version'],$ret['remark'],date('Y-m-d H:i:s'),$ret['id']]);
                }else{

                    $sql="INSERT INTO kf_api_return (name,type,must,version,remark,create_date,api_id) VALUES (?,?,?,?,?,?,?)";

                    $db->insert($sql,'ssisssi',[$name,$ret['type'],intval($ret['must']),$ret['version'],$ret['remark'],date('Y-m-d H:i:s'),$id]);
                }
            }

            $db->commit();

            exit(json_encode(['errno'=>0,'redirect'=>'/api/app#'.$code]));

        }catch(Exception $e){

            $db->rollback();

            exit(json_encode(['errno'=>-1,'errmsg'=>$e->getMessage()]));
        }
    }

    /**
     * 获取接口关联的测试用例
     * @return 
     */
    public function case($params){

        $api_id=$params['id'];

        $m_api=require_model("api");
        $m_test=require_model("test");

        $api=$m_api->getApi($api_id);

        $tests=$m_test->getApiTestCast($api_id);

        $this->display("app/case.html",['api'=>$api,'tests'=>$tests,'tab_selected'=>'app']);
    }

    /**
     * 保存example
     * @return 
     */
    public function example($params){

        $code=$params['code'];
        $api_id=$params['api_id'];

        $db=new DB();

        // $code=$db->escape($code);
        
        $sql="SELECT id FROM kf_api_example WHERE stat=1 AND api_id=".intval($api_id);

        $example=$db->get($sql);

        if(empty($example)){

            $sql="INSERT INTO kf_api_example (api_id,code,create_date) VALUES (?,?,NOW())";

            $db->insert($sql,'is',[$api_id,$code]);
        }else{

            $sql="UPDATE kf_api_example SET code=?,update_date=NOW() WHERE id=?";

            $db->update($sql,'si',[$code,$example['id']]);
        }

        exit(json_encode(['errno'=>0,'errmsg'=>'']));
    }

    protected function actions(){

        return [
            ['name'=>'类别管理','url'=>'/api/cat','click'=>'redirectPage(this)'],
            ['name'=>'APP版本管理','url'=>'/api/version','click'=>'redirectPage(this)'],
            ['name'=>'新增接口','url'=>'/api/app/add','click'=>'redirectPage(this)']
        ];
    }
}