<?php

class TestModel extends DB{

    protected $table='kf_test';

    public function getTest($test_id){

        return $this->getById('kf_test',$test_id);
    }

    public function getTestCase($test_id){

        return $this->find('kf_test_case',['stat'=>1,'test_id'=>$test_id]);

        // $sql="SELECT * FROM kf_test_case WHERE stat=1 AND test_id=".intval($test_id);

        // return $this->exec($sql);
    }

    public function getTestCaseDetail($test_cast_id){

        return $this->getById('kf_test_case',$test_cast_id);
    }

    public function addTest($title,$cat_id,$remark=''){

        return $this->insert('kf_test',['title'=>$title,'cat_id'=>$cat_id,'remark'=>$remark,'stat'=>1,'create_date'=>'NOW()']);
    }

    public function addTestCase($test_id,$content,$api_id=0,$api_params=''){

        return $this->insert('kf_test_case',
            ['test_id'=>$test_id,'content'=>$content,'api_id'=>$api_id,'api_params'=>$api_params,'stat'=>1,'create_date'=>'NOW()']
        );
    }

    public function updateTest($test_id,$title,$cat_id,$remark){

        return $this->update(
            'kf_test',
            ['title'=>$title,'cat_id'=>$cat_id,'remark'=>$remark,'update_date'=>date('Y-m-d H:i:s')],
            ['id'=>$test_id]
        );
    }

    /**
     * 更新测试用例
     * @param  int  $test_id    
     * @param  int  $case_id    
     * @param  string  $content    
     * @param  int  $stat       
     * @param  int $api_id     
     * @param  string  $api_params 
     * @return 
     */
    public function updateTestCase($test_id,$case_id,$content,$api_id=0,$api_params='',$stat){

        return $this->update('kf_test_case',
            ['content'=>$content,'stat'=>$stat,'api_id'=>$api_id,'api_params'=>$api_params,'update_date'=>date('Y-m-d H:i:s')],
            ['test_id'=>$test_id,'id'=>$case_id]
        );
    }

    /**
     * 获取所有测试
     * @return array
     */
    public function getAllTest(){

        return $this->exec("SELECT * FROM kf_test WHERE stat=1");
    }

    public function getApiTestCast($api_id){

        $cases=$this->exec("SELECT * FROM kf_test_case WHERE stat=1 AND api_id=".intval($api_id));

        $result=[];

        if($cases){

            $test_id_list=array_column($cases, 'test_id');
            
            $tests=$this->exec("SELECT * FROM kf_test WHERE id IN (".implode(',', $test_id_list).")");

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