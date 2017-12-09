<?php

class VersionModel extends Model{

    public function updateVersion($version_id,$remark){

        $sql="UPDATE kf_app_version SET remark=? WHERE id=?";

        return $this->db->exec($sql,'si',[$remark,$version_id]);
    }
}