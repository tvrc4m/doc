<?php

const CAT_TYPE_API=1;
const CAT_TYPE_DOC=2;
const CAT_TYPE_TEST_CASE=3;

class CatModel extends DB{

    public function getCat($cat_id){

        $sql="SELECT * FROM kf_cat WHERE id=".intval($cat_id);

        return $this->get($sql);
    }

    public function getCatsByType($type){

        $sql="SELECT * FROM kf_cat WHERE stat=1 AND type=".intval($type);

        return $this->find($sql);
    }

    public function addCat($name,$type){

        $sql="INSERT INTO kf_cat (name,type,stat,create_date) VALUES (?,?,1,NOW())";

        return $this->insert($sql,'si',[$name,$type]);
    }
}