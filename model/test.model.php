<?php

class TestModel extends Model{

    public function getApiTestCast($api_id){

        $cases=t('test_case')->find(['stat'=>1,'api_id'=>$api_id]);

        $result=[];

        if($cases){

            $test_id_list=array_column($cases, 'test_id');
            
            $tests=t('test')->find(['id'=>$test_id_list]);

            foreach ($tests as $test) {
                
                foreach ($cases as $case) {
                    
                    if($test['id']==$case['test_id']){

                        $test['cases'][]=$case;
                    }
                }

                $result[]=$test;
            }
        }
        // print_r($result);exit;
        return $result;
    }
}