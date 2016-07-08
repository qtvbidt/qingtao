<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/28
 * Time: 11:52
 */

namespace Admin\Logic;



/**
 * Description of MySQLLogic
 *
 * @author qingf
 */

class MySQLLogic implements DbMysql{

    /**
     * DB connect
     *
     * @access public
     *
     * @return resource connection link
     */
    public function connect()
    {
        echo __METHOD__;
        dump(func_get_args());
        echo '<hr />';
    }

    /**
     * Disconnect from DB
     *
     * @access public
     *
     * @return viod
     */
    public function disconnect()
    {
        echo __METHOD__;
        dump(func_get_args());
        echo '<hr />';
    }

    /**
     * Free result
     *
     * @access public
     * @param resource $result query resourse
     *
     * @return viod
     */
    public function free($result)
    {
        echo __METHOD__;
        dump(func_get_args());
        echo '<hr />';
    }

    /**
     * Execute simple query
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return resource|bool query result
     */
    public function query($sql, array $args = array())
    {
        //获取所有实参
        $args=func_get_args();
        //获取sql语句
        $sql=array_shift($args);
        //将sql语句分割，preg_split正则分割
        $params = preg_split('/\?[NFT]/', $sql);
        //dump($params);
        //删除最后一个空元素
        array_pop($params);
        //sql变量已经没用了， 我们用来拼凑完整的sql语句
        $sql='';
        foreach ($params as $k=>$v){
            $sql.=$v.$args[$k];
        }
        //dump($sql);
        //执行一个写操作
        return M()->execute($sql);

    }

    /**
     * Insert query method
     *新增一条记录
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return int|false last insert id
     */
    public function insert($sql, array $args = array())
    {
        //获取所有的实参
        $args = func_get_args();
        $sql = $args[0]; // sql语问
        $table_name = $args[1]; // 表名
        $params = $args[2]; //
        //将sql中的?T替换成表名
        $sql = str_replace('?T', $table_name, $sql);
        $tmp=[];
        foreach ($params as $k=>$v){
            $tmp[]=$k.'="'.$v.'"';
        }
        $sql = str_replace('?%',implode(',',$tmp), $sql);
        if(M()->execute($sql)!==false){
            return M()->getLastInsID();
        }else{
            return false;
        }
        //echo $sql;
        //dump($sql);
    }

    /**
     * Update query method
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return int|false affected rows
     */
    public function update($sql, array $args = array())
    {
        echo __METHOD__;
        dump(func_get_args());
        echo '<hr />';
    }

    /**
     * Get all query result rows as associated array
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return array associated data array (two level array)
     */
    public function getAll($sql, array $args = array())
    {
        echo __METHOD__;
        dump(func_get_args());
        echo '<hr />';
    }

    /**
     * Get all query result rows as associated array with first field as row key
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return array associated data array (two level array)
     */
    public function getAssoc($sql, array $args = array())
    {
        echo __METHOD__;
        dump(func_get_args());
        echo '<hr />';
    }

    /**
     * Get only first row from query
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return array associated data array
     */
    public function getRow($sql, array $args = array())
    {
        //获取所有实参
        $args=func_get_args();
        //获取sql语句
        $sql=array_shift($args);
        //将sql语句分割，preg_split正则分割
        $params = preg_split('/\?[NFT]/', $sql);
        //dump($params);
        //删除最后一个空元素
        array_pop($params);
        //sql变量已经没用了， 我们用来拼凑完整的sql语句
        $sql='';
        foreach ($params as $k=>$v){
            $sql.=$v.$args[$k];
        }
        //dump($sql);
        //query返回一个二维数组
        $rows = M()->query($sql);
        //我们只要第一行
        return array_shift($rows);
    }

    /**
     * Get first column of query result
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return array one level data array
     */
    public function getCol($sql, array $args = array())
    {
        echo __METHOD__;
        dump(func_get_args());
        echo '<hr />';
    }

    /**
     * Get one first field value from query result
     *获取第一行的第一个字段值
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return string field value
     */
    public function getOne($sql, array $args = array())
    {
        //获取所有实参
        $args=func_get_args();
        //获取sql语句
        $sql=array_shift($args);
        //将sql语句分割，preg_split正则分割
        $params = preg_split('/\?[NFT]/', $sql);
        //dump($params);
        //删除最后一个空元素
        array_pop($params);
        //sql变量已经没用了， 我们用来拼凑完整的sql语句
        $sql='';
        foreach ($params as $k=>$v){
            $sql.=$v.$args[$k];
        }
        //dump($sql);
        //query返回一个二维数组
        $rows = M()->query($sql);
        //我们只要第一行
        $row=array_shift($rows);
        return array_shift($row);
    }
}