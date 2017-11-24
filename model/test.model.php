<?php

class TestModel extends DB{

    public function getTest($test_id){

        $sql="SELECT * FROM kf_test WHERE id=".intval($test_id);

        return $this->get($sql);
    }

    public function getTestCase($test_id){

        $sql="SELECT * FROM kf_test_case WHERE stat=1 AND test_id=".intval($test_id);

        return $this->find($sql);
    }

    public function getTestCaseDetail($test_cast_id){

        $sql="SELECT * FROM kf_test_case WHERE id=".intval($test_cast_id);

        return $this->get($sql);
    }

    public function addTest($title,$cat_id,$remark=''){

        $sql="INSERT INTO kf_test (title,cat_id,remark,stat,create_date) VALUES (?,?,?,1,NOW())";

        return $this->insert($sql,'sis',[$title,$cat_id,$remark]);
    }

    public function addTestCase($test_id,$content,$api_id=0,$api_params=''){

        $sql="INSERT INTO kf_test_case (test_id,content,api_id,api_params,stat,create_date) VALUES (?,?,?,?,1,NOW())";

        return $this->insert($sql,'isis',[$test_id,$content,$api_id,$api_params]);
    }

    public function updateTest($test_id,$title,$cat_id,$remark){

        $sql="UPDATE kf_test SET title=?,cat_id=?,remark=?,update_date=NOW() WHERE id=?";

        $this->update($sql,'sisi',[$title,$cat_id,$remark,$test_id]);
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

        $sql="UPDATE kf_test_case SET content=?,stat=?,api_id=?,api_params=?,update_date=NOW() WHERE test_id=? AND id=?";

        $this->update($sql,'sissii',[$content,$stat,$api_id,$api_params,$test_id,$case_id]);
    }

    /**
     * 获取所有测试
     * @return array
     */
    public function getAllTest(){

        $sql="SELECT * FROM kf_test WHERE stat=1";

        return $this->find($sql);
    }
}