<?php

class DB {

    /**
     * mysqli链接句柄
     * @var file resource
     */
    private $_link;

    /**
     * 表名
     * @var string
     */
    protected $table='';

    /**
     * 表前缀
     * @var string
     */
    protected $prefix='kf_';

    /**
     * 字符编码
     * @var string
     */
    protected $charset='utf8';

    /**
     * object
     * @var 静态实例对象
     */
    static $instance=null;

    /**
     * 禁止初始化
     */
    private function __construct(){}

    /**
     * 单例方法
     * @return object
     */
    public static function init(){

        !self::$instance && self::$instance=new self();

        return self::$instance;
    }

    /**
     * 获取多条数据
     * @param  array  $where  查询条件,会转化成参数化sql传入
     * @param  string|array $fields 要返回的数据，为空时返回全部字段
     * @param  string|array $sort   排序条件。array时可['sort1name','sort2name'] 或 ['sort1name'=>'asc','sort2name'=>'desc']
     * @param  string|array $group  分组条件
     * @return array
     */
    public function find($where=[],$fields='*',$sort=null,$limit=null,$group=null){

        list($sql,$strtype,$params)=$this->_build_sql($fields,$join=null,$where,$sort,$limit,$group);

        return $this->_exec($sql,$strtype,$params,$select=1);
    }

    /**
     * 获取单一数据
     * @param  array  $where  查询条件,会转化成参数化sql传入
     * @param  string|array $fields 要返回的数据，为空时返回全部字段
     * @param  string|array $sort   排序条件。array时可['sort1name','sort2name'] 或 ['sort1name'=>'asc','sort2name'=>'desc']
     * @param  string|array $group  分组条件
     * @return array
     */
    public function get($where=[],$fields='*',$sort=null,$group=null){

        list($sql,$strtype,$params)=$this->_build_sql($fields,$join=null,$where,$sort,$limit=1,$group);

        $result=$this->_exec($sql,$strtype,$params,$select=1);

        return $result?$result[0]:[];
    }
    /**
     * 获取个数
     * @param  array  $where 
     * @return int 返回个数
     */
    public function count($where=[]){

        $result=$this->get($where,['count(*) as count']);

        return $result?$result['count']:0;
    }

    /**
     * 表连接查询 
     * $db->join('content',['left'=>'content_article','on'=>['content.id'=>'content_article.cid']],['type'=>2],null,null,10);
     * $db->join('content',[
     * ['left'=>'content_article','on'=>['content.id'=>'content_article.cid']],
     * ['left'=>'channel_content','on'=>['channel_content.cid'=>'content.id']]
     * ],['content.type'=>2,'channel_content.chan_id'=>118],'content.title',null,10);
     * @param  array $join    支持多个表连接查询。可是一维数组或二维数组
     * @param  aray  $where   查询条件
     * @param  mixed $fields  string或array。支持带表名
     * @param  mixed $sort    排序
     * @param  miexed $limit  
     * @param  mixed $group  
     * @return array
     */
    public function join($join,$where=[],$fields='*',$sort=null,$limit=null,$group=null){

        list($sql,$strtype,$params)=$this->_build_sql($fields,$join,$where,$sort,$limit,$group);
        
        $result=$this->_exec($sql,$strtype,$params,$select=1);

        return $result;
    }

    /**
     * 通过id获取数据
     * @param  string $id     自增id
     * @param  string|array $fields 
     * @return array
     */
    public function getById($id,$fields=null){

        return $this->get(['id'=>$id],$fields);
    }

    /**
     * 获取单条数据
     * @param  string $sql 
     * @return array
     */
    public function one($sql){

        $this->ping();

        $result=mysqli_query($this->_link,$sql);

        $data=mysqli_fetch_assoc($result);

        mysqli_free_result($result);

        return $data;
    }

    /**
     * 查询sql
     * @param  string $sql 
     * @return array
     */
    public function query($sql){

        $this->ping();

        $result=mysqli_query($this->_link,$sql);

        if(!$result) throw new Exception(mysqli_error($this->_link));

        $data=[];

        while ($row=mysqli_fetch_array($result,MYSQLI_ASSOC)) {
            
            $data[]=$row;
        }

        mysqli_free_result($result);

        return $data;
    }

    /**
     * 执行sql,包含SELECT,UPDATE,INSERT,DELETE等语句。支持参数化sql
     * SELECT 返回数组
     * UPDATE 成功时返回true,失败时抛异常
     * INSERT 插入数据，成功时返回对应的id
     * DELETE 删除数据 返回true|false
     * @param  string $sql     
     * @param  string $strtype 参数化类型列表
     * @param  array $params   参数化具体的值
     * @return int|boolean|array
     */
    public function exec($sql,$strtype,$params){

        if(preg_match('/^\s*select/i', $sql)==1) $select=1;        

        $result=$this->_exec($sql,$strtype,$params,$select);

        if(preg_match('/^\s*insert/i', $sql)==1) return mysqli_insert_id($this->_link);

        return $result;
    }

    /**
     * 插入表数据
     * @param  array $data  表数据
     * @return last insert id
     */
    public function insert($data){

        $data['create_date']=date('Y-m-d H:i:s');

        $params=[];
        $sql=$strtype='';

        foreach ($data as $name=>$value) {

            $sql.=$name.',';
            $tmp.="?,";
            
            $this->_bind_params($name,$value,$strtype,$params);
        }

        $sql='INSERT INTO '.$this->prefix.$this->table.'('.substr($sql,0, -1).') VALUES ('.substr($tmp,0, -1).')';
        
        $this->_exec($sql,$strtype,$params);

        return mysqli_insert_id($this->_link);
    }

    /**
     * 更新sql
     * @param  array $type    要更新的值
     * @param  array $where   要过滤的数据，必须传递where,避免误更新所有数据
     * @return 
     */
    public function update($set,$where){

        $set['update_date']=date('Y-m-d H:i:s');

        $params=[];
        $sql=$strtype='';

        foreach ($set as $name=>$value) {

            $sql.="{$name}=?,";
            
            $sqls[]=$this->_bind_params($name,$value,$strtype,$params);
        }

        $sql='UPDATE '.$this->prefix.$this->table.' SET '.substr($sql,0, -1);

        if(empty($where)) throw new Exception('更新未指定where');

        $this->_build_where($sql,$where,$strtype,$params);
        
        return $this->_exec($sql,$strtype,$params);

    }

    /**
     * 删除数据，必须指定where条件，避免误删所有数据
     * @param  array $where 要过滤的条件
     * @return boolean 是否删除成功
     */
    public function delete($where){

        $sql='DELETE FROM '.$this->prefix.$this->table;

        $params=[];
        $strtype='';

        if(empty($where)) throw new Exception('更新未指定where');

        $this->_build_where($sql,$where,$strtype,$params);

        return $this->_exec($sql,$strtype,$params);
    }

    /**
     * 设置表名
     * @param string $table(省略表前缀)
     */
    public function setTable($table){

        $this->table=$table;
    }
    /**
     * 开启事务。!!只支持Innodb引擎!!
     * @return 
     */
    public function start(){
        // 先建立链接
        $this->ping();
        mysqli_autocommit($this->_link,FALSE);
        mysqli_begin_transaction($this->_link);
    }
    /**
     * 提交事务
     * @return
     */
    public function commit(){

        mysqli_commit($this->_link);
        mysqli_autocommit($this->_link,TRUE);
    }
    /**
     * 回滚事务
     * @return
     */
    public function rollback(){

        mysqli_rollback($this->_link);
        mysqli_autocommit($this->_link,TRUE);
    }

    /**
     * 绑定参数化对应的值
     * @param  string $name         字段名
     * @param  string|int $value    字段值
     * @param  string &$strtype     参数化类型
     * @param  array &$params       参数化对应的参数数组
     * @return 
     */
    public function _bind_params($name,$value,&$strtype,&$params){
        if(is_numeric($value)){
            $strtype.=intval($value)==$value?'i':'d';
            $params[]=$value;
        }elseif(is_string($value)){
            $strtype.='s';
            $params[]=$value;
        }elseif(is_array($value)){
            $strtype.='s';
            $params[]=implode(',', $value);
        }else{
            echo $name;
            throw new Exception('不支持这类型:'.var_export($value));
        }
    }

    /**
     * 构建sql字符串
     * @param  string|array $fields 要返回的数据，为空时返回全部字段
     * @param  array  $where  查询条件,会转化成参数化sql传入
     * @param  string|array $sort   排序条件。array时可['sort1name','sort2name'] 或 ['sort1name'=>'asc','sort2name'=>'desc']
     * @param  string|int|array $limit  limit条件。可选值有 limit|skip,limit|[limit]|[skip,limit]
     * @param  string|array $group  分组条件
     * @return array
     */
    public function _build_sql($fields='*',$join=null,$where=[],$sort=null,$limit=null,$group=null){

        if(empty($fields) || $fields=='*'){
            $fields='*';    
        }else{

            is_string($fields) && $fields=explode(',', $fields);

            array_walk($fields, function(&$v){
                if(strpos($v, '.')!==false) $v=$this->prefix.$v;
            });

            $fields=implode(',', $fields);
        }

        $sql='SELECT '.$fields.' FROM '.$this->prefix.$this->table.' ';

        $params=[];
        $strtype='';

        $this->_build_join($sql,$join);
        $this->_build_where($sql,$where,$strtype,$params);
        $this->_build_group($sql,$group);
        $this->_build_sort($sql,$sort);
        $this->_build_limit($sql,$limit);

        return [$sql,$strtype,$params];
    }

    private function _build_join(&$sql,$join=[]){

        if(empty($join)) return;
        // 只有一级结构
        if(isset($join['left']) || isset($join['right']) || isset($join['inner'])){

            $this->_build_join_($sql,$join);
        }else{
            // 支持多个链接查询 
            foreach ($join as $v) {
                
                $this->_build_join_($sql,$v);
            }
        }
    }

    private function _build_join_(&$sql,$join){

        if(isset($join['left'])){

            $sql.=' LEFT JOIN '.$this->prefix.$join['left'];
        }elseif(isset($join['right'])){

            $sql.=' RIGHT JOIN '.$this->prefix.$join['right'];
        }elseif(isset($join['inner'])){

            $sql.=' INNER JOIN '.$this->prefix.$join['inner'];
        }else{

            throw new Exception('不支持该join类型:'.var_export($join));
        }
        $tmp='';

        foreach ($join['on'] as $k=>$v) {

            if(strpos($k, '.')!==false) $k=$this->prefix.$k;
            if(strpos($v, '.')!==false) $v=$this->prefix.$v;
            
            $tmp.=$k.'='.$v.' AND ';
        }

        $sql.=' ON '.substr($tmp, 0,-4);
    }

    private function _build_where(&$sql,$where,&$strtype,&$params){

        if($where && is_array($where)){

            $sql.=' WHERE ';
            
            foreach ($where as $name=>$value) {

                if(strpos($name, '.')!==false) $name=$this->prefix.$name;

                if(is_array($value)){

                    $k=key($value);

                    $v=is_array($value[$k])?implode(',', $value[$k]):$value[$k];

                    switch ($k) {
                        case '$gt':$sql.=$name.'>? AND ';break;
                        case '$gte':$sql.=$name.'>=? AND ';break;
                        case '$lt':$sql.=$name.'<? AND ';break;
                        case '$lte':$sql.=$name.'<=? AND ';break;
                        case '$inc':$sql.=$name.'='.$name.'+? AND ';break;
                        case '$like':$sql.=$name.' LIKE ? AND ';$v='%'.$v.'% AND ';break;
                        case '$non':$sql.=$name.' NOT IN (?) AND ';break;
                        case '$not':$sql.=$name.'!=? AND ';break;
                        default:$sql.=$name.' IN (?) AND ';break;
                    }
                    
                    $this->_bind_params($name,$v,$strtype,$params);
                }else{

                    $sql.=$name.'=? AND ';

                    $this->_bind_params($name,$value,$strtype,$params);
                }
            }

            $sql=substr($sql,0, -4);
        }
    }

    private function _build_sort(&$sql,$sort=[]){

        if(!$sort) return;

        $sql.=' ORDER BY ';

        if(is_string($sort)){

            $sql.=$sort;
        }elseif(is_array($sort)){

            $tmp='';

            foreach ($sort as $k=>$v) $tmp.=is_numeric($k)?$v.',':$k.' '.$v.',';

            $sql.=substr($tmp, 0,-1);
        }else{

            throw new Exception('sort数据格式不对:'.var_export($sort,true));
        }
    }

    private function _build_group(&$sql,$group=null){

        if(!$group) return;

        $sql.=' GROUP BY ';

        if(is_string($group)){

            $sql.=$group;
        }elseif(is_array($group)){

            $sql.=implode(',', $group);
        }
    }

    private function _build_limit(&$sql,$limit=null){

        if(!$limit) return;

        $sql.=' LIMIT ';

        if(is_array($limit)){

            $sql.=implode(',', $limit);
        }else{

            $sql.=$limit;
        }
    }

    /**
     * 执行sql,主要用于insert,update,deletet等DML语句
     * @param  string $sql    sql语句
     * @param  string $type   参数化类型
     * @param  string $params 参数化对应的参数值数组
     * @param  boolean $select 指定是否是select查询  
     * @return 
     */
    public function _exec($sql,$type,$params,$select=false){

        $this->ping();

        if($stmt=mysqli_prepare($this->_link,$sql)){
            
            if($type && $params){

                $refs=[];

                foreach ($params as $key=>$param) $refs[]=&$params[$key];

                array_unshift($refs, $stmt,$type);
                
                call_user_func_array('mysqli_stmt_bind_param', $refs);
            }

            $status=mysqli_stmt_execute($stmt);

            if($status && $select){

                $meta=mysqli_stmt_result_metadata($stmt);

                $fields=mysqli_fetch_fields($meta);
                // print_r($fields);
                $bind=[];

                foreach ($fields as $field){

                    if(isset($bind[$field->name])){
                        $bind[$field->table.'.'.$field->name]=&${$field->table.'_'.$field->name};
                        ${$field->table.'_'.$field->name}=true;
                    }else{
                        $bind[$field->name]=&${$field->name};
                        ${$field->name}=true;
                    }
                } 

                $vals=array_merge([$stmt],$bind);
                // print_r(count($vals));exit;
                // print_r($vals);exit;
                call_user_func_array('mysqli_stmt_bind_result', $vals);
                    
                $result=[];

                $i=0;

                while(mysqli_stmt_fetch($stmt)){

                    foreach ($bind as $k=>$v) $result[$i][$k]=$v;
                    $i++;
                }
            }

            mysqli_stmt_close($stmt);

            if(!$status) throw new Exception('执行失败:'.$sql.var_export($params,true));

            return $select?$result:true;
        }
        
        throw new Exception('sql解析错误:'.$sql."&nbsp;说明:".mysqli_error($this->_link));
    }

    /**
     * 建立链接,如若无法成功建立，则抛异常
     * @return 
     */
    private function _connect(){

        $this->_link=mysqli_connect(MYSQL_HOST,MYSQL_NAME,MYSQL_PWD,MYSQL_DB);

        if(!$this->_link) throw new Exception(mysqli_connect_error());

        !empty(MYSQL_CHARSET) && $this->charset=MYSQL_CHARSET;

        mysqli_set_charset($this->_link,$this->charset);
    }

    /**
     * ping链接是否还有效
     * @return boolean
     */
    public function ping(){

        if(!$this->_link || !mysqli_ping($this->_link)) $this->_connect();

        return true;
    }
    /**
     * 对特殊字符转义
     * @param  string $str 
     * @return string
     */
    public function escape($str){

        return mysqli_real_escape_string($this->_link,$str);
    }

    public function __destruct(){

        $this->_link && mysqli_close($this->_link);
    }
}

/**
 * 初始化db实现并指定表
 * @param  string $table 表名(省略表前缀)
 * @return object
 */
function t($table){
    // 初始化，单例模式
    $db=DB::init();
    // 指定表名
    $db->setTable($table);

    return $db;
}