<?php

class VersionModel extends Model{

    public function getApiVersion($app_id){

    	return t('api_version')->find(['stat'=>1,'app_id'=>$app_id]);
    }
}