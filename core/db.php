<?php

class DB {

    private $_link;

    /**
     * 构造函数，初始化并链接mysql
     */
    public function __construct(){

        $this->_link=mysqli_connect(MYSQL_HOST,MYSQL_NAME,MYSQL_PWD,MYSQL_DB);

        if(!$this->_link){

            exit(mysqli_connect_error());
        }

        mysqli_set_charset($this->_link,'utf8');
    }

    /**
     * 通过id获取数据
     * @param  string $table  表名
     * @param  string|array $fields 
     * @return array
     */
    public function getById($table,$id,$fields=null){

        return $this->get($table,$fields,['id'=>$id]);
    }

    public function one($sql){

        $result=mysqli_query($this->_link,$sql);

        $data=mysqli_fetch_assoc($result);

        mysqli_free_result($result);

        return $data;
    }

    public function find($table,$where=[],$fileds='*',$sort=null,$limit=null,$group=null){

        list($sql,$strtype,$params)=$this->_build_sql($table,$fields,$where,$sort,$limit,$group);

        return $this->_exec($sql,$strtype,$params,$select=1,$multi=true);
    }

    public function get($table,$where=[],$fileds='*',$sort=[],$group=[]){

        list($sql,$strtype,$params)=$this->_build_sql($table,$fields,$where,$sort,$limit=1,$group);

        $result=$this->_exec($sql,$strtype,$params,$select=1,$multi=false);

        return $result?$result[0]:[];
    }

    public function exec($sql,$strtype,$params){

        $select=0;

        if(preg_match('/^\s*select/i', $sql)==1) $select=1;        

        $result=$this->_exec($sql,$strtype,$params,$select);

        if(preg_match('/^\s*insert/i', $sql)==1) return mysqli_insert_id($this->_link);

        return $result;
    }


    public function insert($table,$data){

        $params=[];
        $sql=$strtype='';

        foreach ($data as $name=>$value) {

            $sql.=$name.',';
            $tmp.="?,";
            
            $this->_bind_params($name,$value,$strtype,$params);
        }

        $sql='INSERT INTO '.$table.'('.substr($sql,0, -1).') VALUES ('.substr($tmp,0, -1).')';
        
        $this->_exec($sql,$strtype,$params);

        return mysqli_insert_id($this->_link);
    }

    /**
     * 更新sql
     * @param  string $sql    更新语句
     * @param  string $type   参数类型列表
     * @param  array $params  参数值
     * @return 
     */
    public function update($table,$set,$where){

        $params=[];
        $sql=$strtype='';

        foreach ($set as $name=>$value) {

            $sql.="{$name}=?,";
            
            $sqls[]=$this->_bind_params($name,$value,$strtype,$params);
        }

        $sql='UPDATE '.$table.' SET '.substr($sql,0, -1);

        $this->_build_where($where,$sql,$strtype,$params);
        echo $sql.PHP_EOL;
        return $this->_exec($sql,$strtype,$params);

    }

    public function delete($table,$where){

        $sql='DELETE FROM '.$table;

        $this->_build_where($where,$sql,$strtype='',$params=[]);

        return $this->_exec($sql,$strtype,$params);
    }
    /**
     * 开启事务
     * @return 
     */
    public function start(){

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


    public function _query($sql,$type='',$params=[],$multi=1){

        $res=[];

        if($stmt=mysqli_prepare($this->_link,$sql)){
            
            $refs=[];

            if(empty($type) && $params){

                array_unshift($params, $stmt,$type);

                foreach ($params as $key=>$param) $refs[]=&$params[$key];

                call_user_func_array('mysqli_stmt_bind_param', $refs);
            }
            
            
            if(mysqli_stmt_execute($stmt)){

                $result=mysqli_stmt_get_result($stmt);

                if($multi){

                    while ($row=mysqli_fetch_array($result,MYSQL_ASSOC)) {
                    
                        $res[]=$row;
                    }
                }else{

                    $res=mysqli_fetch_array($result,MYSQL_ASSOC);
                }

                mysqli_stmt_close($stmt);

                return $res;
            }

            throw new Exception('执行失败:'.$sql.var_export($params,true));
        }
        
        throw new Exception('sql解析错误:'.$sql."&nbsp;说明:".mysqli_error($this->_link));
    }

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
            throw new Exception('不支持这类型:'.var_export($params['where']));
        }
        return $sqls;
    }


    public function _build_sql($table,$fileds='*',$where=[],$sort=null,$limit=null,$group=null){

        empty($fileds) && $fileds='*';

        is_array($fileds) && $fileds=implode(',', $fileds);

        $sql='SELECT '.$fileds.' FROM '.$table.' ';

        $params=[];
        $strtype='';

        $this->_build_where($where,$sql,$strtype,$params);
        $this->_build_group($sql,$group);
        $this->_build_sort($sql,$sort);
        $this->_build_limit($sql,$limit);

        return [$sql,$strtype,$params];
    }

    public function _build_where($where,&$sql,&$strtype,&$params){

        if($where && is_array($where)){

            $sql.=' WHERE ';
            
            foreach ($where as $name=>$value) {

                if(is_array($value)){

                    if(count($value)!=count($value,1)){

                        foreach ($value as $k=>$v) {

                            if(is_array($v)){

                                if($k=='$non'){

                                    $sql.=$name.' NOT IN ('.implode(',', $v).') AND '; 
                                    
                                }else{

                                    $sql.=$name.' IN ('.implode(',', $v).') AND ';   
                                }
                            }elseif($k=='$or'){

                                $sql.=$name.'=? OR  '; // 要预留两个空格
                            }
                        }
                    }else{
                    
                        $sql.=$name.' IN ('.implode(',', $value).') AND ';
                    }
                    
                }else{

                    $sql.=$name.'=? AND ';

                    $this->_bind_params($name,$value,$strtype,$params);
                }
            }

            $sql=substr($sql,0, -4);
        }
    }

    public function _build_sort(&$sql,$sort=[]){

        if(!$sort) return;

        $sql.=' ORDER BY ';

        if(is_string($sort)){

            $sql.=$sort;
        }elseif(is_array($sort)){

            foreach ($sort as $k=>$v) $sql.=is_numeric($k)?$sql.=$v.',':$sql.=$k.' '.$v.',';

            $sql=substr($sql, 0,-1);
        }else{

            throw new Exception('sort数据格式不对:'.var_export($sort,true));
        }
    }

    public function _build_group(&$sql,$group=null){

        if(!$group) return;

        $sql.=' GROUP BY ';

        if(is_string($group)){

            $sql.=$group;
        }elseif(is_array($group)){

            $sql.=implode(',', $group);
        }
    }

    public function _build_limit(&$sql,$limit=null){

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
     * @param  string $sql    
     * @param  string $type   
     * @param  string $params 
     * @return 
     */
    public function _exec($sql,$type,$params,$select=false,$multi=true){
        
        if($stmt=mysqli_prepare($this->_link,$sql)){
            
            array_unshift($params, $stmt,$type);

            $refs=[];

            foreach ($params as $key=>$param) $refs[]=&$params[$key];

            call_user_func_array('mysqli_stmt_bind_param', $refs);
            
            $status=mysqli_stmt_execute($stmt);

            if($status && $select){

                $meta=mysqli_stmt_result_metadata($stmt);

                $fields=mysqli_fetch_fields($meta);

                $bind=[];

                foreach ($fields as $field) $bind[$field->name]=&${$field->name};

                $vals=array_merge([$stmt],$bind);

                call_user_func_array('mysqli_stmt_bind_result', $vals);
                    
                $result=[];

                $i=0;

                while(mysqli_stmt_fetch($stmt)){

                    foreach ($bind as $k=>$v) $result[$i][$k]=$v;
                    $i++;
                    if(!$multi) break;
                }
            }

            mysqli_stmt_close($stmt);

            if(!$status) throw new Exception('执行失败:'.$sql.var_export($params,true));

            return $select?$result:true;
        }
        
        throw new Exception('sql解析错误:'.$sql."&nbsp;说明:".mysqli_error($this->_link));
    }

    public function escape($str){

        return mysqli_real_escape_string($this->_link,$str);
    }

    public function __destruct(){

        $this->_link && mysqli_close($this->_link);
    }
}