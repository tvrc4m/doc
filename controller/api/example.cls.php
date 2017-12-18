<?php

class ExampleController extends BaseAuth{

    /**
     * 保存事例代码
     * @return 
     */
    public function save($params){
        // 事例代码
        $code=$params['code'];
        $api_id=$params['api_id'];

        $example=t('api_example')->get(['stat'=>1,'api_id'=>$api_id]);

        if(empty($example)){

            t('api_example')->insert(['api_id'=>$api_id,'code'=>$code,'stat'=>1]);
        }else{

            t('api_example')->update(['code'=>$code],['id'=>$example['id']]);
        }
        
        $this->ok(['message'=>'保存成功']);
    }
}