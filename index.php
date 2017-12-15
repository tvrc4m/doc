<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

define('ROOT', __DIR__);
define('DATA', ROOT.'/data/');
define('VIEW', ROOT.'/view/');
define('CORE', ROOT.'/core/');
define('CTL',  ROOT.'/controller/');
define('MODEL',  ROOT.'/model/');

define('BASEURL', 'http://'.$_SERVER['HTTP_HOST']);

session_start(['cookie_lifetime' => 86400]);

include_once(CORE.'config.php');
include_once(CORE.'base.php');
include_once(CORE.'doc.php');
include_once(CORE.'db.php');
include_once(CORE.'model.php');
include_once(CORE.'function.php');

// 模块目录
$module=$_GET['module'];
// 控制器名
$app=$_GET['app'];
// 方法名
$act=$_GET['act'];

empty($app) && $app='api';

empty($act) && $act='index';

if($module){

    $filepath=CTL.$module.'/'.$app.'.cls.php';
}else{
    
    $filepath=CTL.$app.'.cls.php';
}
// echo $filepath;exit;
if(!file_exists($filepath)) exit('未找到指定api');

include_once($filepath);

$class=strtoupper($app).'Controller';

$api=new $class;

call_user_func_array([$api,$act], [$_REQUEST]);


