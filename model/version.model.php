<?php

class VersionModel extends DB{

    public function updateVersion($version_id,$remark){

        $sql="UPDATE kf_app_version SET remark=? WHERE id=?";

        return $this->update($sql,'si',[$remark,$version_id]);
    }
}