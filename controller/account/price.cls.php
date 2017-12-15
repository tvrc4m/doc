<?php

/**
 * 服务价格
 */
class PriceController extends Base{

    /**
     * 不显示header navbar
     * @var boolean
     */
    protected $show_header=true;

    public function index(){

        $services=t('service')->find(['stat'=>1]);

        $result=[];

        foreach ($services as $service) {
            $test_env_count=$service['test_env_count']==-1?"支持创建n个测试环境":"支持创建{$service['test_env_count']}个测试环境";
            $api_count=$service['api_count']==-1?"创建api接口的数量不受限":"支持创建{$service['api_count']}个api接口";
            $user_count=$service['user_count']==-1?"支持n个共享账户":"支持创建{$service['user_count']}个共享账户";
            $http_request_count=$service['http_request_count']==-1?"允许无限次请求api请求":"每月可允许请求api数为{$service['http_request_count']}次";
                        
            $result[$service['title']]=['price'=>$service['price'],'service'=>[$test_env_count,$api_count,$user_count,$http_request_count]];
        }

        $count=count($result);

        if($count<=2) $col_md='col-md-6';
        elseif($count==3) $col_md='col-md-4';
        elseif($count==4) $col_md='col-md-3';

        $this->css[]='/static/css/pricing.css';

        $this->display('common/price.html',['service'=>$result,'col_md'=>$col_md]);
    }

    /**
     * 购买页面
     * @return 
     */
    public function buy(){

        $this->css[]='/static/css/pricing_buy.css';

        $this->display('account/buy.html');
    }
}