<?php

// 定义别名
class_alias('Enum','E');

class Enum {

	const API_PARAMS_TYPE_INT=1;
    const API_PARAMS_TYPE_STRING=2;
    const API_PARAMS_TYPE_BOOL=3;
    const API_PARAMS_TYPE_OBJECT=4;
    const API_PARAMS_TYPE_ARRAY=5;

    public static function getApiParamsType($type){

    	return [
	        self::API_PARAMS_TYPE_INT=>'int',
	        self::API_PARAMS_TYPE_STRING=>'string',
	        self::API_PARAMS_TYPE_BOOLEAN=>'boolean',
	        self::API_PARAMS_TYPE_OBJECT=>'object',
	        self::API_PARAMS_TYPE_ARRAY=>'array',
	    ];
    }
}

