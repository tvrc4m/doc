<?php

class HttpController extends Api {

    public function index($params){

        $api_list=$this->getApiList();

        $this->display('http/index.html',['api_list'=>$api_list,'tab_selected'=>'http']);
    }

    public function get($params){

        $key=$params['key'];

        $api=$this->getJsonByKey('app',$key);

        exit(json_encode($api));
    }

    /**
     * 执行
     * @return 
     */
    public function run($params){

        $env=$params['env'];

        empty($env) && $env='dev';

        $url=$params['url'];

        $environment=[
            'test'=>'http://test.vrcdkj.cn/',
            'dev'=>'http://dev.vrcdkj.cn/',
            'staging'=>'http://staging.vrcdkj.cn/',
            // 'live'=>'https://www.kanfanews.com/',
            'live'=>'https://www.vrcdkj.cn/',
        ];

        $domain=$environment[$env];

        if($env=='live') $params['test_code']='fdj837fb&30*83b*&73hf_kgjjg&hhf';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $domain.$url);
        // echo $domain.$url;exit;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        
        $result = curl_exec($ch);
        
        curl_close($ch);

        echo $result;

        exit;
    }
}