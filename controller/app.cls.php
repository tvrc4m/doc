<?php

class AppController extends Api {

    /**
     * app全部的api接口
     * @return 
     */
    public function index(){

        $api_list=$this->getApiList('api');

        $this->display("app/content.html",['api_list'=>$api_list,'tab_selected'=>'app']);
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

    /**
     * api编辑
     * @param  array $params 
     * @return 
     */
    public function edit($params){

        $code=$params['code'];

        $api_list=$this->getApiList('api');

        $api=$this->getApi($code);

        $api['cats']=$this->getApiCat();
        // print_r($cat_list);exit;
        $this->display("app/edit.html",['tab_selected'=>'app','api_list'=>$api_list,'api'=>json_encode($api,JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)]);
    }

    /**
     * 编辑保存功能
     * @param  array $params 
     * @return 
     */
    public function save($data){

        $id=$data['id'];
        $title=$data['title'];
        $url=$data['url'];
        $cat_id=$data['cat_id'];
        $params=$data['params'];
        $return=$data['return'];
        $version=$data['version'];
        $remark=$data['remark'];

        if(empty($id)) exit(json_encode(['errno'=>-1,'errmsg'=>'未指定接口']));

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

            $sql="UPDATE kf_api SET title=?,url=?,cat_id=?,code=?,version=?,remark=?,update_date=? WHERE id=?";

            $db->update($sql,'ssissssi',[$title,$url,$cat_id,$code,$version,$remark,date('Y-m-d H:i:s'),$id]);

            // params
            $params_exists=array_filter(array_column($params, 'id'));

            if(!empty($params_exists)){

                $sql="DELETE FROM kf_api_params WHERE id NOT IN (?) AND api_id=?";

                $db->delete($sql,'si',[implode(',', $params_exists),$id]);
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

                $sql="DELETE FROM kf_api_return WHERE id NOT IN (?) AND api_id=?";

                $db->delete($sql,'si',[implode(',', $return_exists),$id]);
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
}