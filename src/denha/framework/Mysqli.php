<?php
namespace denha;

class Mysqli
{

    private static $instance;

    public $dbConfig; //数据库连接信息
    public $tablepre; //表前缀
    public $sqlInfo; //执行sql记录

    public $link;
    public $result;
    public $querystring;
    public $isclose;
    public $safecheck;

    public $table;
    public $join;
    public $field;
    public $where;
    public $order;
    public $limit;
    public $group;
    public $total;
    public $excID; //插入ID
    public $_sql; //最后执行sql

    private function __construct($dbConfig = '')
    {
        if ($dbConfig) {
            $this->dbConfig = $dbConfig;
        } else {
            if (getConfig('db.' . APP_CONFIG)) {
                $this->dbConfig = getConfig('db.' . APP_CONFIG);
            } else {
                $this->dbConfig = getConfig('db');
            }
        }

        $this->tablepre = $this->dbConfig['db_prefix'];

        if ($this->dbConfig['db_host'] == '' || $this->dbConfig['db_user'] == '' || $this->dbConfig['db_name'] == '') {
            throw new Exception('接数据库信息有误！请查看是否配置正确');
        }

        $this->link = $this->openMysql();
        mysqli_query($this->link, 'set names utf8');
        mysqli_query($this->link, 'SET sql_mode =\'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION\'');
    }

    //单例实例化 避免重复New暂用资源
    public static function getInstance($dbConfig = '')
    {
        if (is_null(self::$instance)) {
            self::$instance = new Mysqli($dbConfig);
        }
        return self::$instance;

    }

    /**
     * 连接数据库
     * @date   2017-03-19T16:18:28+0800
     * @author ChenMingjiang
     */
    public function openMysql()
    {
        try {
            $res = mysqli_connect($this->dbConfig['db_host'], $this->dbConfig['db_user'], $this->dbConfig['db_pwd'], $this->dbConfig['db_name']);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        if (!$res) {
            if (!$this->link) {
                throw new Exception('连接数据库失败，可能数据库密码不对或数据库服务器出错！' . print_r($this->dbConfig));
            }

        }

        return $res;
    }

    public function getSql()
    {
        return $this->_sql;
    }

    /**
     * 数据表
     * @date   2017-03-19T16:18:23+0800
     * @author ChenMingjiang
     * @param  [type]                   $table  [description]
     * @param  string                   $table2 [description]
     * @return [type]                           [description]
     */
    public function table($table, $isTablepre = true)
    {
        $this->table = parseName($table);
        if ($isTablepre) {
            $this->tablepre != '' ? $this->table = $this->tablepre . $this->table : '';
        }

        $this->where = '';
        $this->field = '*';
        $this->limit = '';
        $this->group = '';
        $this->order = '';
        $this->join  = '';
        return $this;
    }

    /**
     * 获取表名称
     * @date   2017-06-10T22:56:01+0800
     * @author ChenMingjiang
     * @param  [type]                   $table [description]
     * @return [type]                          [description]
     */
    public function tableName()
    {
        return $this->table;
    }

    /**
     * 判断是否存在该表
     * @date   2017-09-20T09:57:21+0800
     * @author ChenMingjiang
     * @return boolean                  [description]
     */
    public function isTable()
    {
        $this->_sql = 'SHOW TABLES LIKE \'dh_banner\'';
        $result     = (bool) mysqli_num_rows($this->query());
        return $result;
    }

    /**
     * 查询条件
     * @date   2017-03-19T16:18:18+0800
     * @author ChenMingjiang
     * @param  [type]                   $where [description]
     * @return [type]                          [description]
     */
    public function where($where, $value = '')
    {
        if ($value !== '' && !is_array($where)) {
            $this->where = ' WHERE ' . $where . ' = \'' . $value . '\'';
        } else {
            if ($where) {
                if (is_array($where)) {
                    $newWhere = '';
                    foreach ($where as $k => $v) {
                        if (is_array($v)) {
                            if ($v[0] == '>' || $v[0] == '<' || $v[0] == '>=' || $v[0] == '<=' || $v[0] == '!=' || $v[0] == 'like') {
                                $newWhere .= $k . '  ' . $v[0] . ' \'' . $v[1] . '\' AND ';
                            } elseif ($v[0] == 'in' || $v['0'] == 'not in') {
                                if (!$v[1]) {
                                    $newWhere .= $k . '  ' . $v[0] . ' (\'\') AND ';
                                } else {
                                    $v[1] = is_array($v[1]) ? implode(',', $v[1]) : $v[1];
                                    $newWhere .= $k . '  ' . $v[0] . ' (' . $v[1] . ') AND ';
                                }
                            } elseif ($v[0] == 'instr') {
                                $newWhere .= $v[0] . '(`' . $k . '`,\'' . $v[1] . '\') AND ';
                            } elseif ($v[0] == 'between') {
                                $newWhere .= $k . '  ' . $v[0] . ' \'' . $v[1] . '\' AND \'' . $v[2] . '\' AND ';
                            } elseif ($v[0] == 'or') {
                                $newWhere .= $k . ' = \'' . $v[1] . '\' OR ';
                            }
                        } elseif ($k == '_string') {
                            $newWhere .= $v . ' AND ';
                        } else {
                            $newWhere .= $k . ' = \'' . $v . '\' AND ';
                        }
                    }
                } else {
                    $newWhere = $where;
                }
                $this->where = ' WHERE ' . substr($newWhere, 0, -4);
            }
        }

        return $this;
    }

    /**
     * 关联查询
     * @date   2017-06-10T22:52:34+0800
     * @author ChenMingjiang
     * @param  [type]                   $table [description]
     * @param  [type]                   $where [description]
     * @param  string                   $float [description]
     * @return [type]                          [description]
     */
    public function join($table, $where, $float = 'left')
    {
        $this->join = ' ' . $float . ' JOIN ' . $table . ' ON ' . $where;
        return $this;
    }

    /**
     * 查询数量
     * @date   2017-03-19T16:18:13+0800
     * @author ChenMingjiang
     * @param  [type]                   $limit [description]
     * @return [type]                          [description]
     */
    public function limit($limit, $pageSize = '')
    {
        $this->limit = ' LIMIT ' . $limit;
        if ($pageSize) {
            $this->limit = ' LIMIT ' . $limit . ',' . $pageSize;
        }

        return $this;
    }

    /**
     * 查询字段
     * @date   2017-03-19T16:18:09+0800
     * @author ChenMingjiang
     * @param  [type]                   $field [description]
     * @return [type]                          [description]
     */
    public function field($field = '*')
    {
        $newField = '';
        $field    = is_array($field) ? $field : explode(',', $field);
        foreach ($field as $k => $v) {
            if (stripos($v, 'as') === false && stripos($v, '*') === false && stripos($v, '`') === false && stripos($v, '.') === false && stripos($v, 'concat') === false) {
                $newField .= '`' . $v . '`,';
            } else {
                $newField .= $v . ',';
            }

        }

        $newField = substr($newField, 0, -1);

        $this->field = $newField;

        return $this;
    }

    public function group($value = '')
    {
        if (is_array($value)) {
            $i = 0;
            foreach ($value as $k => $v) {
                if ($i == 0) {
                    $newGroup .= $v;
                } else {
                    $newGroup .= "," . $v;
                }

                $i++;
            }
        } else {
            $newGroup = $value;
        }
        $this->group = ' GROUP BY ' . $newGroup;
        return $this;
    }

    public function order($value)
    {
        if (is_array($value)) {
            $i = 0;
            foreach ($value as $k => $v) {
                if ($i == 0) {
                    $newValue .= $v;
                } else {
                    $newValue .= "," . $v;
                }

                $i++;
            }
        } else {
            $newValue = $value;
        }
        $this->order = ' ORDER BY ' . $newValue;
        return $this;
    }

    /**
     * 判断是否存在该数据表
     * @date   2017-03-19T16:18:01+0800
     * @author ChenMingjiang
     * @param  string                   $table [description]
     * @return [type]                          [description]
     */
    public function existsTbale($table = '')
    {
        if ($table == '') {$table = $this->table;}
        $sql                      = "SELECT COUNT(*) as total  FROM information_schema.TABLES WHERE TABLE_NAME='$table'";
        $t                        = mysqli_fetch_array(mysqli_query($this->link, $sql));
        if ($t['total'] == 0) {return false;}
        return true;
    }

    /**
     * 查询表字段名
     * @date   2017-03-19T16:14:45+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function getField()
    {
        $this->where = 'table_name = ' . "'" . $this->table . "'";
        $this->field = 'column_name';
        $this->table = 'information_schema.columns';
        $this->limit = '99';

        $sql    = "select " . $this->field . " from " . $this->table;
        $result = $this->query($sql);

        $data = mysqli_fetch_array($result, MYSQLI_ASSOC);

        foreach ($data as $key => $value) {
            $varField[$key] = $value;
        }

        return $varField;
    }

    /**
     * 统计总数
     * @date   2017-06-14T11:09:55+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function count($field = 't')
    {

        $this->limit(1);

        $sql = 'SELECT  COUNT(*) AS ' . $field . '  FROM ' . $this->table;

        if ($this->join) {
            $sql .= $this->join;
        }

        if ($this->where != '') {
            $sql .= $this->where;
        }
        if ($this->group != '') {
            $sql .= $this->group;
        }

        $sql .= $this->limit;

        $result = $this->query($sql);
        $data   = mysqli_fetch_array($result, MYSQLI_NUM);

        return $data[0];
    }

    /**
     * 查询单条/多条信息
     * @date   2017-03-19T16:18:52+0800
     * @author ChenMingjiang
     * @param  string                   $value [array:查询数据 one:查询单条单个字段内容]
     * @return [type]                          [description]
     */
    public function find($value = '', $isArray = false)
    {

        if (!$this->table) {
            throw new Exception('请选择数据表');
        }

        if (!$this->limit && $value != 'array' && !$isArray) {
            $this->limit(1);
        }

        if ($value == 'array' || ($value == 'one' && $isArray)) {
            if ($this->limit == '') {$this->limit(1000);}
        }

        $this->_sql = 'SELECT ' . $this->field . ' FROM ' . $this->table;

        empty($this->join) ?: $this->_sql .= $this->join;
        empty($this->where) ?: $this->_sql .= $this->where;
        empty($this->group) ?: $this->_sql .= $this->group;
        empty($this->order) ?: $this->_sql .= $this->order;
        empty($this->limit) ?: $this->_sql .= $this->limit;

        $result = $this->query();

        //获取记录条数
        $this->total = mysqli_num_rows($result);

        if ($this->total == 0) {return false;}
        //单个字段模式
        if ($value == 'one' && !$isArray) {
            $row = mysqli_fetch_array($result, MYSQLI_NUM);
            if (empty($row)) {
                return false;
            }

            if (count($row) > 1) {
                throw new Exception('sql模块中one只能查询单个字段内容请设置field函数');
            }

            return $row[0];
        }
        //单字段数组模式
        elseif ($value == 'one' && $isArray) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                $this->field = str_replace('`', '', $this->field);
                if (count($row) > 1) {
                    throw new Exception('sql模块中one只能查询单个字段内容请设置field函数');
                }
                $data[] = $row[$this->field];
            }

            if (empty($data)) {
                return false;
            }
            return $data;
        }
        //三维数组模式
        elseif ($this->total > 1 || $value == 'array') {

            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                $data[] = $row;
            }

            for ($i = 0, $n = count($data); $i < $n; $i++) {
                if (is_array($data[$i])) {
                    foreach ($data[$i] as $key => $value) {
                        $datas[$i][$key] = $value;
                    }
                }
            }

            return $datas;
        }
        //二维数组模式
        else {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

            foreach ($row as $key => $value) {
                $data[$key] = $value;
            }

            return $data;
        }
    }

    /**
     * 添加
     * @date   2017-03-19T16:19:43+0800
     * @author ChenMingjiang
     * @param  string                   $data [description]
     */
    public function add($data = '')
    {
        $newField = '';
        if (is_array($data)) {
            $i = 0;
            foreach ($data as $k => $v) {
                if ($i == 0) {
                    $newField .= '`' . $k . '` = \'' . $v . '\'';
                } else {
                    $newField .= ",`" . $k . '`=\'' . $v . '\'';
                }
                $i++;
            }
        } else {
            $newField = $field;
        }
        $this->field = $newField;

        $sql = 'INSERT INTO `' . $this->table . '` SET ' . $this->field;
        $sql .= $this->where ? $this->where : '';
        $result = $this->query($sql);
        $result = mysqli_insert_id($this->link);
        return $result;
    }

    /**
     * 添加多条信息
     * @date   2017-09-19T15:45:40+0800
     * @author ChenMingjiang
     */
    public function addAll($data = array())
    {
        foreach ($data as $key => $value) {
            $result = $this->add($value);
        }
        return $result;
    }

    /**
     * 修改保存
     * @date   2017-03-19T16:20:24+0800
     * @author ChenMingjiang
     * @param  string                   $data [description]
     * @return [type]                         [description]
     */
    public function save($data = '', $value = '')
    {
        if (!$this->where) {
            return false;
        }

        $newField = '';
        if ($value !== '' && !is_array($data)) {
            $newField = '`' . $data . '`=\'' . $value . '\'';
        } else {
            if (is_array($data)) {
                foreach ($data as $k => $v) {
                    if (is_array($v)) {
                        $v[0] = strtolower($v[0]);
                        if ($v[0] == 'add') {
                            $newField .= '`' . $k . '`  = `' . $k . '` + ' . $v[1] . ',';
                        } elseif ($v[0] == 'less') {
                            $newField .= '`' . $k . '`  = `' . $k . '` - ' . $v[1] . ',';
                        } elseif ($v[0] == 'concat') {
                            $newField .= '`' . $k . '`  = CONCAT(`' . $k . '`,\'\',\'' . $v[1] . '\'),';
                        }
                    } else {
                        $newField .= '`' . $k . '`=\'' . $v . '\',';
                    }
                }
                $newField = substr($newField, 0, -1);
            } else {
                $newField = $field;
            }
        }

        $this->field = $newField;

        $this->_sql = 'UPDATE ' . $this->table . ' SET ' . $this->field;
        $this->_sql .= $this->where ? $this->where : '';

        $result = $this->query();
        return $result;
    }

    /**
     * 删除数据
     * @date   2017-03-19T16:20:32+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function delete()
    {
        if (!$this->where) {
            return false;
        }
        $this->_sql = 'DELETE FROM ' . $this->table . $this->where;
        $result     = $this->query();
        return $result;
    }

    //开启事务
    public function startTrans()
    {
        mysqli_query($this->link, 'begin');
        /*$this->query('begin');*/
        return true;
    }

    //回滚事务
    public function rollback()
    {
        mysqli_query($this->link, 'rollback');
        /*$this->query('rollback');*/
        return true;
    }

    //提交事务
    public function commit()
    {
        mysqli_query($this->link, 'commit');
        /* $this->query('commit');*/
        return true;
    }

    /**
     * 执行
     * @date   2017-03-19T16:20:36+0800
     * @author ChenMingjiang
     * @param  [type]                   $sql [description]
     * @return [type]                        [description]
     */
    public function query($sql = '')
    {
        !$sql ?: $this->_sql = $sql;
        $_beginTime          = microtime(true);
        $result              = mysqli_query($this->link, $this->_sql);
        $_endTime            = microtime(true);

        $this->sqlInfo['time'] = $_endTime - $_beginTime; //获取执行时间
        $this->sqlInfo['sql']  = $this->_sql;

        Trace::addSqlInfo($this->sqlInfo);
        $this->sqlLog(); //记录sql

        if ($result) {
            return $result;
        } else {
            throw new Exception('SQL ERROR :' . $this->_sql);
        }

    }

    /**
     * 保存sql记录
     * @date   2017-10-18T13:45:16+0800
     * @author ChenMingjiang
     * @return [type]                   [description]
     */
    public function sqlLog()
    {
        if ($this->sqlInfo && $this->dbConfig['db_sqlLog']) {
            $path = DATA_PATH . 'sql_log' . DS . $this->dbConfig['db_name'] . DS;
            is_dir($path) ? '' : mkdir($path, 0077, true);
            if (stripos($this->sqlInfo['sql'], 'select') === 0) {
                $path .= date('Y_m_d_H', TIME) . '_select.text';
                $content = $this->sqlInfo['sql'] . '|' . $this->sqlInfo['time'];
            } elseif (stripos($this->sqlInfo['sql'], 'update') === 0) {
                $path .= date('Y_m_d_H', TIME) . '_update.text';
                $content = $this->sqlInfo['sql'] . ';';
            } elseif (stripos($this->sqlInfo['sql'], 'delete') === 0) {
                $path .= date('Y_m_d_H', TIME) . '_delete.text';
                $content = $this->sqlInfo['sql'] . ';';
            } elseif (stripos($this->sqlInfo['sql'], 'insert') === 0) {
                $path .= date('Y_m_d_H', TIME) . '_add.text';
                $content = $this->sqlInfo['sql'] . ';';
            }

            $file = fopen($path, 'a');
            fwrite($file, $content . PHP_EOL);
            fclose($file);
        }
    }
}
