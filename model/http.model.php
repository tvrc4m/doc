<?php

class HttpModel extends DB{

    public function getUserHttp($user_id){

        $sql="SELECT * FROM kf_user_http WHERE stat=1 AND user_id=".intval($user_id);

        return $this->find($sql);
    }

    public function addUserHttp($title,$cat_id,$api_id,$api_params='',$api_return='',$is_public=1){

        $sql="INSERT INTO kf_user_http (title,cat_id,api_id,api_params,api_return,is_public,stat,create_date) VALUES (?,?,?,?,?,?,1,NOW())";

        return $this->insert($sql,'siissi',[$title,$cat_id,$api_id,$api_params,$api_return,$is_public]);
    }

    /**
     * 更新用户的http请求
     */
    public function updateUserHttp($user_http_id,$user_id,$title,$api_id,$api_params='',$api_return='',$is_public=1){

        $sql="UPDATE kf_user_http SET title=?,api_id=?,api_params=?,api_return=?,is_public=?,update_date=NOW() WHERE id=? AND user_id=?";

        $this->update($sql,'sissiii',[$title,$api_id,$api_params,$api_return,$is_public,$user_http_id,$user_id]);
    }

    public function updateUserHttpParamsAndReturn($user_http_id,$user_id,$api_params,$api_return){

        $sql="UPDATE kf_user_http SET api_params=?,api_return=?,update_date=NOW() WHERE id=? AND user_id=?";

        $this->update($sql,'ssii',[$api_params,$api_return,$user_http_id,$user_id]);
    }

    public function getUserHttpDetail($user_http_id){

        return $this->get("SELECT * FROM kf_user_http WHERE stat=1 AND id=".intval($user_http_id));
    }
}