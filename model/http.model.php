<?php

class HttpModel extends Model{

    public function getUserAndPublicHttp($user_id){

        $sql="SELECT * FROM kf_user_http WHERE stat=1 AND (user_id={$user_id} OR is_public=1)";

        return $this->db->exec($sql);
    }
}