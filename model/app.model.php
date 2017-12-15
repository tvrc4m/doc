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
}