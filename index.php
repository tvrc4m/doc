<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

define('ROOT', __DIR__);
define('DATA', ROOT.'/data/');
define('VIEW', ROOT.'/view/');
define('CORE', ROOT.'/core/');
define('CTL',  ROOT.'/controller/');

define('BASEURL', 'http://'.$_SERVER['HTTP_HOST']);

session_start(['cookie_lifetime' => 86400]);

include_once(CORE.'config.php');
include_once(CORE.'auth.php');
include_once(CORE.'doc.php');
include_once(CORE.'api.php');
include_once(CORE.'db.php');

$app=$_GET['app'];

$act=$_GET['act'];

empty($app) && $app='app';

empty($act) && $act='index';

$filepath=CTL.$app.'.cls.php';

if(!file_exists($filepath)) exit('未找到指定api');

include_once($filepath);

$class=strtoupper($app).'Controller';

$api=new $class;

call_user_func_array([$api,$act], [$_REQUEST]);


