<?php

class DB {

    protected $db;

    public function __construct(){

        $this->db=mysqli_connect(MYSQL_HOST,MYSQL_NAME,MYSQL_PWD,MYSQL_DB);

        if(!$this->db){

            exit(mysqli_connect_error());
        }

        mysqli_set_charset($this->db,'utf8');
    }

    public function get($sql){

        $result=mysqli_query($this->db,$sql);

        $data=mysqli_fetch_assoc($result);

        mysqli_free_result($result);

        return $data;
    }

    public function find($sql){

        $result=mysqli_query($this->db,$sql);

        if(!$result){

            exit(mysqli_error($this->db));
        }

        $data=[];

        while ($row=mysqli_fetch_array($result,MYSQLI_ASSOC)) {
            
            $data[]=$row;
        }

        mysqli_free_result($result);

        return $data;
    }

    public function insert($sql,$type,$params){

        $status=$this->_exec($sql,$type,$params);

        return mysqli_insert_id($this->db);
    }

    /**
     * 更新sql
     * @param  string $sql    更新语句
     * @param  string $type   参数类型列表
     * @param  array $params  参数值
     * @return 
     */
    public function update($sql,$type,$params){

        return $this->_exec($sql,$type,$params);
    }

    public function delete($sql,$type,$params){

        return $this->_exec($sql,$type,$params);
    }

    public function start(){

        mysqli_autocommit($this->db,FALSE);
        mysqli_begin_transaction($this->db);
    }

    public function commit(){

        mysqli_commit($this->db);
        mysqli_autocommit($this->db,TRUE);
    }

    public function rollback(){

        mysqli_rollback($this->db);
        mysqli_autocommit($this->db,TRUE);
    }

    public function _exec($sql,$type,$params){

        if($stmt=mysqli_prepare($this->db,$sql)){
            
            array_unshift($params, $stmt,$type);

            $refs=[];

            foreach ($params as $key=>$param) $refs[]=&$params[$key];

            call_user_func_array('mysqli_stmt_bind_param', $refs);
            
            $status=mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);

            if(!$status) throw new Exception('执行失败:'.$sql.var_export($params,true));

            return true;
        }
        
        throw new Exception('sql解析错误:'.$sql."&nbsp;说明:".mysqli_error($this->db));
    }

    public function escape($str){

        return mysqli_real_escape_string($this->db,$str);
    }

    public function __destruct(){

        $this->db && mysqli_close($this->db);
    }
}