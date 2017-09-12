<?php

class AppApi extends Doc {

    public function export(){

        $json=file_get_contents(ROOT.'/data/app.api.json');

        $params=json_decode($json,true);

        $header=file_get_contents(ROOT.'/view/api/header.html');
        $footer=file_get_contents(ROOT.'/view/api/footer.html');
        // $content=file_get_contents(ROOT.'/view/api/content.html');
        $content=include_once(ROOT.'/view/api/content.html');

        echo $content;

        // echo $content;
    }
}