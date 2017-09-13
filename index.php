<?php

define('ROOT', __DIR__);
define('DATA', ROOT.'/data/');
define('VIEW', ROOT.'/view/');
define('CORE', ROOT.'/core/');
define('CTL',  ROOT.'/api/');

include_once(CORE.'doc.php');
include_once(CORE.'api.php');

$app=$_GET['app'];

$act=$_GET['act'];

empty($app) && $app='app';

empty($act) && $act='index';

$filepath=CTL.$app.'.api.php';

if(!file_exists($filepath)) exit('未找到指定api');

include_once($filepath);

$class=strtoupper($app).'Api';

$api=new $class;

call_user_func_array([$api,$act], [$_GET]);


