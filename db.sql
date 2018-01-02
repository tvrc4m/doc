CREATE TABLE kf_api(
    id int AUTO_INCREMENT PRIMARY KEY,
    title varchar(255) NOT NULL COMMENT 'api接口',
    cat_id int NOT NULL COMMENT '类别id',
    app_id int NOT NULL COMMENT '应用id',
    type tinyint default 1 COMMENT '1:post 2:get',
    url varchar(255) NOT NULL COMMENT '接口地址',
    version varchar(10) default '' COMMENT '版本',
    remark varchar(500) default '' COMMENT '接口说明',
    stat boolean default 1 COMMENT '状态,是否删除',
    create_date datetime,
    update_date datetime
) engine=InnoDB;

CREATE TABLE kf_api_params(
    id int AUTO_INCREMENT PRIMARY KEY,
    app_id int NOT NULL COMMENT '应用id',
    api_id int NOT NULL COMMENT 'api id',
    name varchar(50) NOT NULL COMMENT '请求参数名',
    type tinyint NOT NULL COMMENT '类型',
    must boolean default 1 COMMENT '是否必填项 1:必填 0:非',
    version varchar(10) default '' COMMENT '版本',
    remark varchar(500) default '' COMMENT '备注说明',
    stat boolean default 1 COMMENT '状态,是否删除',
    create_date datetime,
    update_date datetime

) engine=InnoDB;

CREATE TABLE kf_api_return(
    id int AUTO_INCREMENT PRIMARY KEY,
    app_id int NOT NULL COMMENT '应用id',
    api_id int NOT NULL COMMENT 'api id',
    name varchar(50) NOT NULL COMMENT '请求参数名',
    type tinyint NOT NULL COMMENT '类型',
    must boolean default 1 COMMENT '是否必填项 1:必填 0:非',
    version varchar(10) default '' COMMENT '版本',
    remark varchar(500) default '' COMMENT '备注说明',
    stat boolean default 1 COMMENT '状态,是否删除',
    create_date datetime,
    update_date datetime
) engine=InnoDB;

CREATE TABLE kf_cat(
    id int AUTO_INCREMENT PRIMARY KEY,
    app_id int NOT NULL COMMENT '应用id',
    name varchar(50) NOT NULL COMMENT '类别名称',
    stat boolean default 1 COMMENT '状态,是否删除',
    type tinyint default 1 COMMENT '1:api类别，2:文档类别',
    user_id int DEFAULT 0 COMMENT '关联的用户id',
    create_date datetime,
    update_date datetime
) engine=InnoDB;

CREATE TABLE kf_api_example(
    id int AUTO_INCREMENT PRIMARY KEY,
    code text default '' COMMENT 'code',
    api_id int NOT NULL,
    stat boolean default 1 COMMENT '状态,是否删除',
    create_date datetime,
    update_date datetime
) engine=InnoDB;

CREATE TABLE kf_doc(
    id int AUTO_INCREMENT PRIMARY KEY,
    app_id int NOT NULL COMMENT '应用id',
    title varchar(255) NOT NULL COMMENT '文档标题',
    content text NOT NULL COMMENT '内容',
    stat boolean default 1 COMMENT '状态,是否删除',
    cat_id int NOT NULL COMMENT '文档类别id',
    create_date datetime,
    update_date datetime
) engine=InnoDB;

CREATE TABLE kf_user(
    id int AUTO_INCREMENT PRIMARY KEY,
    nick varchar(50) NOT NULL COMMENT '昵称',
    realname varchar(50) DEFAULT '' COMMENT '真实姓名',
    email varchar(100) DEFAULT '' COMMENT '邮箱',
    phone varchar(11) default '' COMMENT '手机号',
    pwd varchar(64) NOT NULL COMMENT '密码',
    stat boolean default 0 COMMENT '用户状态',
    token varchar(250) default '' COMMENT 'token',
    is_company boolean default 0 COMMENT '是否是企业帐户',
    company_id int default 0 COMMENT '企业账户id',
    is_admin boolean default 0 COMMENT '是否是企业admin账户',
    card_no varchar(32) default '' COMMENT '身份证号',
    card_no_front varchar(255) COMMENT '身份证正面',
    card_no_back varchar(255) COMMENT '身份证背面',
    cert_status boolean default 0 COMMENT '身份认证状态',
    app_count int default 0 COMMENT '创建的应用数量',
    create_date datetime,
    update_date datetime
) engine=InnoDB;

CREATE TABLE kf_company(
    id int AUTO_INCREMENT PRIMARY KEY,
    name varchar(150) NOT NULL COMMENT '企业名称',
    logo varchar(200) DEFAULT '' COMMENT '企业logo',
    website varchar(50) default '' COMMENT '企业站点域名',
    remark varchar(500) default '' COMMENT '企业简介',
    licence_no varchar(64) NOT NULL COMMENT '营业执照信息',
    licence_no_front varchar(255) NOT NULL COMMENT '营业执照正面',
    create_date datetime,
    update_date datetime
) engine=InnoDB;

CREATE TABLE kf_api_version(
    id int AUTO_INCREMENT PRIMARY KEY,
    app_id int NOT NULL COMMENT '应用id',
    user_id int NOT NULL COMMENT '登录用户id',
    name varchar(50) NOT NULL COMMENT '版本号',
    remark varchar(250) default '' COMMENT '备注',
    stat boolean default 0 COMMENT '状态',
    create_date datetime,
    update_date datetime
) engine=InnoDB;

CREATE TABLE kf_test(
    id int AUTO_INCREMENT PRIMARY KEY,
    title varchar(50) NOT NULL COMMENT '测试用例标题',
    remark varchar(250) NOT NULL COMMENT '备注说明',
    cat_id int NOT NULL COMMENT '类别id',
    stat boolean default 0 COMMENT '状态',
    app_id int NOT NULL COMMENT '应用id',
    create_date datetime,
    update_date datetime
) engine=InnoDB;

-- 真实的测试用例
CREATE TABLE kf_test_case(
    id int AUTO_INCREMENT PRIMARY KEY,
    content varchar(50) NOT NULL COMMENT '测试用例内容',
    test_id int NOT NULL COMMENT '测试id',
    stat boolean default 0 COMMENT '状态',
    api_id int default 0 COMMENT '关联的api',
    api_params varchar(500) default '' COMMENT 'api请求参数',
    create_date datetime,
    update_date datetime
) engine=InnoDB;

CREATE TABLE kf_test_log(
    id int AUTO_INCREMENT PRIMARY KEY,
    test_id int NOT NULL COMMENT '测试id',
    user_id int NOT NULL COMMENT '登录用户id',
    status tinyint DEFAULT 0 COMMENT '执行状态 0:未执行 1:执行中 2:执行成功 -1:有错误',
    create_date datetime,
    update_date datetime
) engine=InnoDB;

CREATE TABLE kf_test_case_log(
    id int AUTO_INCREMENT PRIMARY KEY,
    test_id int NOT NULL,
    test_case_id int NOT NULL COMMENT '测试用例id',
    user_id int NOT NULL COMMENT '登录用户id',
    status tinyint DEFAULT 0 COMMENT '执行状态 0:未执行 1:执行中 2:执行成功 -1:有错误',
    result text NOT NULL COMMENT '返回的结果',
    api_id int NOT NULL COMMENT '请求的接口id',
    api_params varchar(500) COMMENT '请求的参数',
    create_date datetime,
    update_date datetime
) engine=InnoDB;

CREATE TABLE kf_user_http(
    id int AUTO_INCREMENT PRIMARY KEY,
    title varchar(500) NOT NULL COMMENT '标题',
    api_id int NOT NULL COMMENT 'api接口id',
    app_id int NOT NULL COMMENT '应用id',
    api_params varchar(500) DEFAULT '',
    api_return text,
    user_id int NOT NULL COMMENT '关联的用户id',
    cat_id int NOT NULL COMMENT '类别id',
    is_public boolean DEFAULT 1 COMMENT '是否是公开的',
    stat boolean default 0 COMMENT '状态',
    create_date datetime,
    update_date datetime
) engine=InnoDB;

CREATE TABLE kf_class(
    id int AUTO_INCREMENT PRIMARY KEY,
    name varchar(50) NOT NULL COMMENT '类名',
    cat_id int NOT NULL COMMENT '类别id',
    filepath varchar(255) NOT NULL COMMENT '文件路径',
    remark varchar(500) default '' COMMENT '类备注',
    stat boolean default 1 COMMENT '状态,是否删除',
    create_date datetime,
    update_date datetime
) engine=InnoDB;

CREATE TABLE kf_class_method(
    id int AUTO_INCREMENT PRIMARY KEY,
    class_id int NOT NULL COMMENT '关联的类id',
    name varchar(50) NOT NULL COMMENT '方法名',
    remark varchar(500) default '' COMMENT '备注说明',
    stat boolean default 1 COMMENT '状态,是否删除',
    create_date datetime,
    update_date datetime
) engine=InnoDB;

CREATE TABLE kf_class_method_params(
    id int AUTO_INCREMENT PRIMARY KEY,
    method_id int NOT NULL,
    name varchar(50) NOT NULL COMMENT '请求参数名',
    type tinyint NOT NULL COMMENT '类型',
    must boolean default 1 COMMENT '是否必填项 1:必填 0:非',
    version varchar(10) default '' COMMENT '版本',
    remark varchar(500) default '' COMMENT '备注说明',
    stat boolean default 1 COMMENT '状态,是否删除',
    create_date datetime,
    update_date datetime
) engine=InnoDB;


CREATE TABLE kf_user_setting(
    id int AUTO_INCREMENT PRIMARY KEY,
    user_id int NOT NULL COMMENT '用户id',
    company_id int default 0 COMMENT '公司id',
    app_count int default 0 COMMENT '支持的应用个数',
    test_env_count int default 0 COMMENT '支持的测试环境个数',
    api_count int default 0 COMMENT '支持的api接口数量',
    user_count int default 0 COMMENT '支持的成员上限人数',
    http_request_count int default 0 COMMENT '支持调用的api接口次数(每月)',
    start_date datetime COMMENT '开始计费日期',
    valid_day int default 0 COMMENT '有效期(天数)',
    create_date datetime,
    update_date datetime
) engine=InnoDB;


CREATE TABLE kf_service(
    id int AUTO_INCREMENT PRIMARY KEY,
    title varchar(50) NOT NULL COMMENT '服务名称',
    type tinyint default 0 COMMENT '服务类型',
    price float default 0 COMMENT '价格。为0代表免费',
    app_count int default 1 COMMENT '允许创建的应用数量,-1代表不受限',
    test_env_count int default 0 COMMENT '-1代表不受限',
    api_count int default 0 COMMENT '-1代表不受限',
    user_count int default 0 COMMENT '-1代表不受限',
    http_request_count int default 0 COMMENT '-1代表不受限',
    stat boolean default 1 COMMENT '状态,是否删除',
    create_date datetime,
    update_date datetime
) engine=InnoDB;


INSERT INTO kf_service (id,title,type,price,test_env_count,api_count,user_count,http_request_count,stat,create_date) VALUES (1,'个人免费版',1,0,2,50,1,1000,1,NOW()),(2,'个人付费版',2,9.9,4,100,1,5000,1,NOW()),(3,'小型企业版',3,29.9,3,100,50,50000,1,NOW()),(4,'大型公司版',4,49.9,5,100,1,100000,1,NOW());

CREATE TABLE kf_user_pay(
    id int AUTO_INCREMENT PRIMARY KEY,
    user_id int NOT COMMENT '用户id',
    company_id int default 0 COMMENT '公司id',
    price int default 0 COMMENT '价格',
    month int default 0 COMMENT '购买的月份',
    stat boolean default COMMENT '状态，是否删除',
    pay_status tinyint default 0 COMMENT '支付状态',
    pay_date datetime COMMENT '创建支付时间',
    paid_date datetime COMMENT '已支付时间',
    create_date datetime,
    update_date datetime
) engine=InnoDB;

CREATE TABLE kf_user_app(
    id int AUTO_INCREMENT PRIMARY KEY,
    user_id int NOT NULL COMMENT '用户id',
    company_id int DEFAULT 0 COMMENT '公司id',
    name varchar(50) NOT NULL COMMENT 'app名字',
    remark varchar(500) default '' COMMENT '备注说明',
    stat boolean DEFAULT 1 COMMENT '状态，是否删除',
    create_date datetime,
    update_date datetime
) engine=InnoDB;

CREATE TABLE kf_user_app_test_env(
    id int AUTO_INCREMENT PRIMARY KEY,
    name varchar(50) NOT NULL COMMENT '测试环境名称',
    url varchar(50) NOT NULL COMMENT '测试环境url地址',
    is_default boolean default 0 COMMENT '是否是默认测试环境',
    user_id int NOT NULL COMMENT '登陆用户id',
    company_id int default 0 COMMENT '公司id',
    app_id int COMMENT '关联的应用id',
    stat boolean default 1 COMMENT '状态，是否删除',
    create_date datetime,
    update_date datetime
) engine=InnoDB;

