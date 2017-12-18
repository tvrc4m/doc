<?php

class HttpModel extends Model{

    public function getUserAndPublicHttp($user_id){

        // $sql="SELECT * FROM kf_user_http WHERE stat=1 AND (user_id={$user_id} OR is_public=1)";

        // return $this->db->exec($sql);

        return t('user_http')->find(['user_id'=>$user_id,'stat'=>1]);
    }

    /**
     * 获取某个测试环境的域名
     * @param  int $app_id 应用id
     * @param  string $env    测试环境名称
     * @return string 域名链接
     */
    public function getTestEnvUrl($app_id,$env){

        $test=t('user_app_test_env')->get(['app_id'=>$app_id,'name'=>$env,'stat'=>1]);

        return rtrim($test['url'],'/');
    }
}