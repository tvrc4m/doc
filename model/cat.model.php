<?php

const CAT_TYPE_API=1;
const CAT_TYPE_DOC=2;
const CAT_TYPE_TEST_CASE=3;
const CAT_TYPE_HTTP=4;

class CatModel extends DB{

    public function getCat($cat_id){

        return $this->getById('kf_cat',$cat_id);
    }

    public function getCatsByType($type){

        $sql="SELECT * FROM kf_cat WHERE stat=1 AND type=".intval($type);

        return $this->exec($sql);
    }

    public function getUserCatByType($user_id,$type){

        $sql="SELECT * FROM kf_cat WHERE stat=1 AND user_id=".intval($user_id)." AND type=".intval($type);

        return $this->exec($sql);
    }

    public function addCat($user_id,$name,$type){

        $sql="INSERT INTO kf_cat (user_id,name,type,stat,create_date) VALUES (?,?,?,1,NOW())";

        return $this->exec($sql,'isi',[$user_id,$name,$type]);
    }
}