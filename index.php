<?php

error_reporting(E_ALL ^ E_NOTICE);

define('ROOT', __DIR__);
define('DATA', ROOT.'/data/');
define('VIEW', ROOT.'/view/');
define('CORE', ROOT.'/core/');
define('CTL',  ROOT.'/controller/');

define('BASEURL', 'http://'.$_SERVER['HTTP_HOST']);

include_once(CORE.'config.php');
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


