<?php

class AppModel extends Model{

    /**
     * 获取测试环境
     * @param  int $app_id 
     * @return array
     */
    public function getAppTestEnv($app_id){

        $tests= t('user_app_test_env')->find(['app_id'=>$app_id,'stat'=>1]);

        $result=[];

        foreach ($tests as $test) {
            
            $result[$test['name']]=['url'=>$test['url'],'is_default'=>$test['is_default']];
        }
        
        return $result;
    }

    /**
     * 获取用户或公司的应用
     * @param  int $user_id    用户id
     * @param  int $company_id 公司id
     * @return array
     */
    public function get_user_app($user_id,$company_id){
        // 如果用户有关联公司,则取公司的应用id
        if($company_id) 
            return t('user_app')->get(['company_id'=>$company_id,'stat'=>1]);
        
        return t('user_app')->get(['user_id'=>$user_id,'stat'=>1]);
    }
}