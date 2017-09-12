<?php

define('ROOT', __DIR__);

include_once(ROOT.'/core/doc.php');

$app='app';

$action='index';

include_once(ROOT.'/api/app.api.php');

$api=new AppApi;

$params=$api->export();

print_r($params);