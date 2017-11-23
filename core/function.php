<?php

function require_model($class){

    $filepath=MODEL.$class.'.model.php';

    if(!file_exists($filepath)) exit('文件不存在:'.$filepath);

    include_once $filepath;

    $classname=ucfirst($class).'Model';

    return new $classname();
}