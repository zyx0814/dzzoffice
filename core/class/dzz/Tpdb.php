<?php
namespace core\dzz;
class Tpdb {
    // 数据库类型
    protected $dbType     = null;
    // 是否自动释放查询结果
    protected $autoFree   = false;
    // 当前操作所属的模型名
    protected $model      = '_think_';
    // 是否使用永久连接
    protected $pconnect   = false;
    // 当前SQL指令
    protected $queryStr   = '';
    protected $modelSql   = array();
    // 最后插入ID
    protected $lastInsID  = null;
    // 返回或者影响记录数
    protected $numRows    = 0;
    // 返回字段数
    protected $numCols    = 0;
    // 事务指令数
    protected $transTimes = 0;
    // 错误信息
    protected $error      = '';
    // 数据库连接ID 支持多个连接
    protected $linkID     = array();
    // 当前连接ID
    protected $_linkID    = null;
    // 当前查询ID
    protected $queryID    = null;
    // 是否已经连接数据库
    protected $connected  = false;
    // 数据库连接参数配置
    protected $config     = '';
    // 数据库表达式
    protected $comparison = array('eq'=>'=','neq'=>'<>','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','notlike'=>'NOT LIKE','like'=>'LIKE','in'=>'IN','notin'=>'NOT IN');
    // 查询表达式
    protected $selectSql  = 'SELECT%DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT% %UNION%%COMMENT%';
    // 参数绑定
    protected $bind       = array();

    /**
     * 取得数据库类实例
     * @static
     * @access public
     * @return mixed 返回数据库驱动类
     */
    public static function getInstance($db_config='') { 
		static $_instance	=	array(); 
		$guid	=	to_guid_string($db_config); 
		if(!isset($_instance[$guid])){
			$obj	=	new Tpdb();
			$_instance[$guid]	=	$obj->factory($db_config);
		} 
		return $_instance[$guid];
    }

    /**
     * 加载数据库 支持配置文件或者 DSN
     * @access public
     * @param mixed $db_config 数据库配置信息
     * @return string
     */
    public function factory($db_config='') {
		//$db = DB::object();
		if( function_exists('mysqli_connect') ){
			$db= new Tpsqli( $db_config ); 
		}else{
			$db= new Tpsql( $db_config ); 
		} 
		return $db; 
        /*// 读取数据库配置
        $db_config = $this->parseConfig($db_config);
        if(empty($db_config['dbms']))
            echo "无数据库配置";exit;
			//E(L('_NO_DB_CONFIG_'));
        // 数据库类型
        if(strpos($db_config['dbms'],'\\')){
            $class  =   $db_config['dbms'];
        }else{
            $dbType =   ucwords(strtolower($db_config['dbms']));
            $class  =   'Think\\Db\\Driver\\'. $dbType;            
        }
        // 检查驱动类
        if(class_exists($class)) {
            $db = new $class($db_config);
        }else {
            // 类没有定义
            $this->error="未定义数据库驱动类型";
			//E(L('_NO_DB_DRIVER_').': ' . $class);
        }
        return $db;
        */
    }

    /**
     * 根据DSN获取数据库类型 返回大写
     * @access protected
     * @param string $dsn  dsn字符串
     * @return string
     */
    protected function _getDsnType($dsn) {
        $match  =  explode(':',$dsn);
        $dbType = strtoupper(trim($match[0]));
        return $dbType;
    }

    /**
     * 分析数据库配置信息，支持数组和DSN
     * @access private
     * @param mixed $db_config 数据库配置信息
     * @return string
     */
    private function parseConfig($db_config='') {
        if ( !empty($db_config) && is_string($db_config)) {
            // 如果DSN字符串则进行解析
            $db_config = $this->parseDSN($db_config);
        }elseif(is_array($db_config)) { // 数组配置
             $db_config =   array_change_key_case($db_config);
             $db_config = array(
                  'dbms'      =>  $db_config['db_type'],
                  'username'  =>  $db_config['db_user'],
                  'password'  =>  $db_config['db_pwd'],
                  'hostname'  =>  $db_config['db_host'],
                  'hostport'  =>  $db_config['db_port'],
                  'database'  =>  $db_config['db_name'],
                  'dsn'       =>  $db_config['db_dsn'],
                  'params'    =>  $db_config['db_params'],
             );
        }elseif(empty($db_config)) {
            // 如果配置为空，读取配置文件设置
           if(0){ //if( C('DB_DSN') && 'pdo' != strtolower(C('DB_TYPE')) ) { // 如果设置了DB_DSN 则优先
                $db_config =  $this->parseDSN(C('DB_DSN'));
            }else{
                $db_config = array (
                    'dbms'      => $db_config['db_type'],// C('DB_TYPE'),
                    'username'  =>  $db_config['db_user'],//C('DB_USER'),
                    'password'  =>  $db_config['db_pwd'],//C('DB_PWD'),
                    'hostname'  =>   $db_config['db_host'],//C('DB_HOST'),
                    'hostport'  =>  $db_config['db_port'], //C('DB_PORT'),
                    'database'  =>  $db_config['db_name'],// C('DB_NAME'),
                    'dsn'       =>  $db_config['db_dsn'],//C('DB_DSN'),
                    'params'    =>  $db_config['db_params'],// C('DB_PARAMS'),
                );
            }
        }
        return $db_config;
    }

    /**
     * 初始化数据库连接
     * @access protected
     * @param boolean $master 主服务器
     * @return void
     */
    protected function initConnect($master=true) { 
		static $_config = array(); 
        if(0) //if(1 == C('DB_DEPLOY_TYPE'))
            // 采用分布式数据库
            $this->_linkID = $this->multiConnect($master);
        else 
            // 默认单数据库
            if ( !$this->connected ) $this->_linkID = $this->connect(); 
    }

    /**
     * 连接分布式服务器
     * @access protected
     * @param boolean $master 主服务器
     * @return void
     */
    protected function multiConnect($master=false) {
        static $_config = array();
        if(empty($_config)) {
            // 缓存分布式数据库配置解析
            foreach ($this->config as $key=>$val){
                $_config[$key]      =   explode(',',$val);
            }
        }
        // 数据库读写是否分离
        if(C('DB_RW_SEPARATE')){
            // 主从式采用读写分离
            if($master)
                // 主服务器写入
                $r  =   floor(mt_rand(0,C('DB_MASTER_NUM')-1));
            else{
                if(is_numeric(C('DB_SLAVE_NO'))) {// 指定服务器读
                    $r = C('DB_SLAVE_NO');
                }else{
                    // 读操作连接从服务器
                    $r = floor(mt_rand(C('DB_MASTER_NUM'),count($_config['hostname'])-1));   // 每次随机连接的数据库
                }
            }
        }else{
            // 读写操作不区分服务器
            $r = floor(mt_rand(0,count($_config['hostname'])-1));   // 每次随机连接的数据库
        }
        $db_config = array(
            'username'  =>  isset($_config['username'][$r])?$_config['username'][$r]:$_config['username'][0],
            'password'  =>  isset($_config['password'][$r])?$_config['password'][$r]:$_config['password'][0],
            'hostname'  =>  isset($_config['hostname'][$r])?$_config['hostname'][$r]:$_config['hostname'][0],
            'hostport'  =>  isset($_config['hostport'][$r])?$_config['hostport'][$r]:$_config['hostport'][0],
            'database'  =>  isset($_config['database'][$r])?$_config['database'][$r]:$_config['database'][0],
            'dsn'       =>  isset($_config['dsn'][$r])?$_config['dsn'][$r]:$_config['dsn'][0],
            'params'    =>  isset($_config['params'][$r])?$_config['params'][$r]:$_config['params'][0],
        );
        return $this->connect($db_config,$r);
    }

    /**
     * DSN解析
     * 格式： mysql://username:passwd@localhost:3306/DbName
     * @static
     * @access public
     * @param string $dsnStr
     * @return array
     */
    public function parseDSN($dsnStr) {
        if( empty($dsnStr) ){return false;}
        $info = parse_url($dsnStr);
        if($info['scheme']){
            $dsn = array(
            'dbms'      =>  $info['scheme'],
            'username'  =>  isset($info['user']) ? $info['user'] : '',
            'password'  =>  isset($info['pass']) ? $info['pass'] : '',
            'hostname'  =>  isset($info['host']) ? $info['host'] : '',
            'hostport'  =>  isset($info['port']) ? $info['port'] : '',
            'database'  =>  isset($info['path']) ? substr($info['path'],1) : ''
            );
        }else {
            preg_match('/^(.*?)\:\/\/(.*?)\:(.*?)\@(.*?)\:([0-9]{1, 6})\/(.*?)$/',trim($dsnStr),$matches);
            $dsn = array (
            'dbms'      =>  $matches[1],
            'username'  =>  $matches[2],
            'password'  =>  $matches[3],
            'hostname'  =>  $matches[4],
            'hostport'  =>  $matches[5],
            'database'  =>  $matches[6]
            );
        }
        $dsn['dsn'] =  ''; // 兼容配置信息数组
        return $dsn;
     }

    /**
     * 数据库调试 记录当前SQL
     * @access protected
     */
    protected function debug() {
        $this->modelSql[$this->model]   =  $this->queryStr;
        $this->model  =   '_think_';
        // 记录操作结束时间
        /*
		 if (C('DB_SQL_LOG')) {
            G('queryEndTime');
            trace($this->queryStr.' [ RunTime:'.G('queryStartTime','queryEndTime',6).'s ]','','SQL');
        }*/
    }

    /**
     * 设置锁机制
     * @access protected
     * @return string
     */
    protected function parseLock($lock=false) {
        if(!$lock) return '';
        if('ORACLE' == $this->dbType) {
            return ' FOR UPDATE NOWAIT ';
        }
        return ' FOR UPDATE ';
    }

    /**
     * set分析
     * @access protected
     * @param array $data
     * @return string
     */
    protected function parseSet($data) {
        foreach ($data as $key=>$val){
            if(is_array($val) && 'exp' == $val[0]){
                $set[]  =   $this->parseKey($key).'='.$val[1];
            }elseif(is_scalar($val) || is_null($val)) { // 过滤非标量数据
              /*if(C('DB_BIND_PARAM') && 0 !== strpos($val,':')){
                $name   =   md5($key);
                $set[]  =   $this->parseKey($key).'=:'.$name;
                $this->bindParam($name,$val);
              }else{*/
                $set[]  =   $this->parseKey($key).'='.$this->parseValue($val);
              //}
            }
        }
        return ' SET '.implode(',',$set);
    }

     /**
     * 参数绑定
     * @access protected
     * @param string $name 绑定参数名
     * @param mixed $value 绑定值
     * @return void
     */
    protected function bindParam($name,$value){
        $this->bind[':'.$name]  =   $value;
    }

     /**
     * 参数绑定分析
     * @access protected
     * @param array $bind
     * @return array
     */
    protected function parseBind($bind){
        $bind           =   array_merge($this->bind,$bind);
        $this->bind     =   array();
        return $bind;
    }

    /**
     * 字段名分析
     * @access protected
     * @param string $key
     * @return string
     */
    protected function parseKey(&$key) {
        return $key;
    }
    
    /**
     * value分析
     * @access protected
     * @param mixed $value
     * @return string
     */
    protected function parseValue($value) {
        if(is_string($value)) {
            $value =  '\''.$this->escapeString($value).'\'';
        }elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
            $value =  $this->escapeString($value[1]);
        }elseif(is_array($value)) {
            $value =  array_map(array($this, 'parseValue'),$value);
        }elseif(is_bool($value)){
            $value =  $value ? '1' : '0';
        }elseif(is_null($value)){
            $value =  'null';
        }
        return $value;
    }

    /**
     * field分析
     * @access protected
     * @param mixed $fields
     * @return string
     */
    protected function parseField($fields) {
        if(is_string($fields) && strpos($fields,',')) {
            $fields    = explode(',',$fields);
        }
        if(is_array($fields)) {
            // 完善数组方式传字段名的支持
            // 支持 'field1'=>'field2' 这样的字段别名定义
            $array   =  array();
            foreach ($fields as $key=>$field){
                if(!is_numeric($key))
                    $array[] =  $this->parseKey($key).' AS '.$this->parseKey($field);
                else
                    $array[] =  $this->parseKey($field);
            }
            $fieldsStr = implode(',', $array);
        }elseif(is_string($fields) && !empty($fields)) {
            $fieldsStr = $this->parseKey($fields);
        }else{
            $fieldsStr = '*';
        }
        //TODO 如果是查询全部字段，并且是join的方式，那么就把要查的表加个别名，以免字段被覆盖
        return $fieldsStr;
    }

    /**
     * table分析
     * @access protected
     * @param mixed $table
     * @return string
     */
    protected function parseTable($tables) {
        if(is_array($tables)) {// 支持别名定义
            $array   =  array();
            foreach ($tables as $table=>$alias){
                if(!is_numeric($table))
                    $array[] =  $this->parseKey($table).' '.$this->parseKey($alias);
                else
                    $array[] =  $this->parseKey($table);
            }
            $tables  =  $array;
        }elseif(is_string($tables)){
            $tables  =  explode(',',$tables);
            array_walk($tables, array(&$this, 'parseKey'));
        }
        $tables = implode(',',$tables);
        return $tables;
    }

    /**
     * where分析
     * @access protected
     * @param mixed $where
     * @return string
     */
    protected function parseWhere($where) {
        $whereStr = '';
        if(is_string($where)) {
            // 直接使用字符串条件
            $whereStr = $where;
        }else{ // 使用数组表达式
            $operate  = isset($where['_logic'])?strtoupper($where['_logic']):'';
            if(in_array($operate,array('AND','OR','XOR'))){
                // 定义逻辑运算规则 例如 OR XOR AND NOT
                $operate    =   ' '.$operate.' ';
                unset($where['_logic']);
            }else{
                // 默认进行 AND 运算
                $operate    =   ' AND ';
            }
            foreach ($where as $key=>$val){
                $whereStr .= '( ';
                if(is_numeric($key)){
                    $key  = '_complex';
                }                    
                if(0===strpos($key,'_')) {
                    // 解析特殊条件表达式
                    $whereStr   .= $this->parseThinkWhere($key,$val);
                }else{
                    // 查询字段的安全过滤
                    if(!preg_match('/^[A-Z_\|\&\-.a-z0-9\(\)\,]+$/',trim($key))){
                        //E(L('_EXPRESS_ERROR_').':'.$key);
                        echo '表达式错误:'.$key;exit;
                    }
                    // 多条件支持
                    $multi  = is_array($val) &&  isset($val['_multi']);
                    $key    = trim($key);
                    if(strpos($key,'|')) { // 支持 name|title|nickname 方式定义查询字段
                        $array =  explode('|',$key);
                        $str   =  array();
                        foreach ($array as $m=>$k){
                            $v =  $multi?$val[$m]:$val;
                            $str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
                        }
                        $whereStr .= implode(' OR ',$str);
                    }elseif(strpos($key,'&')){
                        $array =  explode('&',$key);
                        $str   =  array();
                        foreach ($array as $m=>$k){
                            $v =  $multi?$val[$m]:$val;
                            $str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
                        }
                        $whereStr .= implode(' AND ',$str);
                    }else{
                        $whereStr .= $this->parseWhereItem($this->parseKey($key),$val);
                    }
                }
                $whereStr .= ' )'.$operate;
            }
            $whereStr = substr($whereStr,0,-strlen($operate));
        }
        return empty($whereStr)?'':' WHERE '.$whereStr;
    }

    // where子单元分析
    protected function parseWhereItem($key,$val) {
        $whereStr = '';
        if(is_array($val)) {
            if(is_string($val[0])) {
                if(preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT)$/i',$val[0])) { // 比较运算
                    $whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' '.$this->parseValue($val[1]);
                }elseif(preg_match('/^(NOTLIKE|LIKE)$/i',$val[0])){// 模糊查找
                    if(is_array($val[1])) {
                        $likeLogic  =   isset($val[2])?strtoupper($val[2]):'OR';
                        if(in_array($likeLogic,array('AND','OR','XOR'))){
                            $likeStr    =   $this->comparison[strtolower($val[0])];
                            $like       =   array();
                            foreach ($val[1] as $item){
                                $like[] = $key.' '.$likeStr.' '.$this->parseValue($item);
                            }
                            $whereStr .= '('.implode(' '.$likeLogic.' ',$like).')';                          
                        }
                    }else{
                        $whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' '.$this->parseValue($val[1]);
                    }
                }elseif('exp'==strtolower($val[0])){ // 使用表达式
                    $whereStr .= ' ('.$key.' '.$val[1].') ';
                }elseif(preg_match('/IN/i',$val[0])){ // IN 运算
                    if(isset($val[2]) && 'exp'==$val[2]) {
                        $whereStr .= $key.' '.strtoupper($val[0]).' '.$val[1];
                    }else{
                        if(is_string($val[1])) {
                             $val[1] =  explode(',',$val[1]);
                        }
                        $zone      =   implode(',',$this->parseValue($val[1]));
                        $whereStr .= $key.' '.strtoupper($val[0]).' ('.$zone.')';
                    }
                }elseif(preg_match('/BETWEEN/i',$val[0])){ // BETWEEN运算
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $whereStr .=  ' ('.$key.' '.strtoupper($val[0]).' '.$this->parseValue($data[0]).' AND '.$this->parseValue($data[1]).' )';
                }else{
                    //E(L('_EXPRESS_ERROR_').':'.$val[0]);
                    echo '表达式错误:'.$key;exit;
                }
            }else {
                $count = count($val);
                $rule  = isset($val[$count-1]) ? (is_array($val[$count-1]) ? strtoupper($val[$count-1][0]) : strtoupper($val[$count-1]) ) : '' ; 
                if(in_array($rule,array('AND','OR','XOR'))) {
                    $count  = $count -1;
                }else{
                    $rule   = 'AND';
                }
                for($i=0;$i<$count;$i++) {
                    $data = is_array($val[$i])?$val[$i][1]:$val[$i];
                    if('exp'==strtolower($val[$i][0])) {
                        $whereStr .= '('.$key.' '.$data.') '.$rule.' ';
                    }else{
                        $whereStr .= '('.$this->parseWhereItem($key,$val[$i]).') '.$rule.' ';
                    }
                }
                $whereStr = substr($whereStr,0,-4);
            }
        }else {
            //对字符串类型字段采用模糊匹配
            if(0){//if(C('DB_LIKE_FIELDS') && preg_match('/('.C('DB_LIKE_FIELDS').')/i',$key)) {
                $val  =  '%'.$val.'%';
                $whereStr .= $key.' LIKE '.$this->parseValue($val);
            }else {
                $whereStr .= $key.' = '.$this->parseValue($val);
            }
        }
        return $whereStr;
    }

    /**
     * 特殊条件分析
     * @access protected
     * @param string $key
     * @param mixed $val
     * @return string
     */
    protected function parseThinkWhere($key,$val) {
        $whereStr   = '';
        switch($key) {
            case '_string':
                // 字符串模式查询条件
                $whereStr = $val;
                break;
            case '_complex':
                // 复合查询条件
                $whereStr   =   is_string($val)? $val : substr($this->parseWhere($val),6);
                break;
            case '_query':
                // 字符串模式查询条件
                parse_str($val,$where);
                if(isset($where['_logic'])) {
                    $op   =  ' '.strtoupper($where['_logic']).' ';
                    unset($where['_logic']);
                }else{
                    $op   =  ' AND ';
                }
                $array   =  array();
                foreach ($where as $field=>$data)
                    $array[] = $this->parseKey($field).' = '.$this->parseValue($data);
                $whereStr   = implode($op,$array);
                break;
        }
        return $whereStr;
    }

    /**
     * limit分析
     * @access protected
     * @param mixed $lmit
     * @return string
     */
    protected function parseLimit($limit) {
        return !empty($limit)?   ' LIMIT '.$limit.' ':'';
    }

    /**
     * join分析
     * @access protected
     * @param array $join
     * @return string
     */
    protected function parseJoin($join) {
        $joinStr = '';
        if(!empty($join)) {
            $joinStr    =   ' '.implode(' ',$join).' ';
        }
        return $joinStr;
    }

    /**
     * order分析
     * @access protected
     * @param mixed $order
     * @return string
     */
    protected function parseOrder($order) {
        if(is_array($order)) {
            $array   =  array();
            foreach ($order as $key=>$val){
                if(is_numeric($key)) {
                    $array[] =  $this->parseKey($val);
                }else{
                    $array[] =  $this->parseKey($key).' '.$val;
                }
            }
            $order   =  implode(',',$array);
        }
        return !empty($order)?  ' ORDER BY '.$order:'';
    }

    /**
     * group分析
     * @access protected
     * @param mixed $group
     * @return string
     */
    protected function parseGroup($group) {
        return !empty($group)? ' GROUP BY '.$group:'';
    }

    /**
     * having分析
     * @access protected
     * @param string $having
     * @return string
     */
    protected function parseHaving($having) {
        return  !empty($having)?   ' HAVING '.$having:'';
    }

    /**
     * comment分析
     * @access protected
     * @param string $comment
     * @return string
     */
    protected function parseComment($comment) {
        return  !empty($comment)?   ' /* '.$comment.' */':'';
    }

    /**
     * distinct分析
     * @access protected
     * @param mixed $distinct
     * @return string
     */
    protected function parseDistinct($distinct) {
        return !empty($distinct)?   ' DISTINCT ' :'';
    }

    /**
     * union分析
     * @access protected
     * @param mixed $union
     * @return string
     */
    protected function parseUnion($union) {
        if(empty($union)) return '';
        if(isset($union['_all'])) {
            $str  =   'UNION ALL ';
            unset($union['_all']);
        }else{
            $str  =   'UNION ';
        }
        foreach ($union as $u){
            $sql[] = $str.(is_array($u)?$this->buildSelectSql($u):$u);
        }
        return implode(' ',$sql);
    }

    /**
     * 插入记录
     * @access public
     * @param mixed $data 数据
     * @param array $options 参数表达式
     * @param boolean $replace 是否replace
     * @return false | integer
     */
    public function insert($data,$options=array(),$replace=false) {
        $values  =  $fields    = array();
        $this->model  =   $options['model'];
        foreach ($data as $key=>$val){
            if(is_array($val) && 'exp' == $val[0]){
                $fields[]   =  $this->parseKey($key);
                $values[]   =  $val[1];
            }elseif(is_scalar($val) || is_null($val)) { // 过滤非标量数据
              $fields[]   =  $this->parseKey($key);
              if(0){//if(C('DB_BIND_PARAM') && 0 !== strpos($val,':')){
                $name       =   md5($key);
                $values[]   =   ':'.$name;
                $this->bindParam($name,$val);
              }else{
                $values[]   =  $this->parseValue($val);
              }                
            }
        } 
        $sql   =  ($replace?'REPLACE':'INSERT').' INTO '.$this->parseTable($options['table']).' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
        $sql   .= $this->parseLock(isset($options['lock'])?$options['lock']:false);
        $sql   .= $this->parseComment(!empty($options['comment'])?$options['comment']:''); 
        return $this->execute($sql,$this->parseBind(!empty($options['bind'])?$options['bind']:array()));
    }

    /**
     * 通过Select方式插入记录
     * @access public
     * @param string $fields 要插入的数据表字段名
     * @param string $table 要插入的数据表名
     * @param array $option  查询数据参数
     * @return false | integer
     */
    public function selectInsert($fields,$table,$options=array()) {
        $this->model  =   $options['model'];
        if(is_string($fields))   $fields    = explode(',',$fields);
        array_walk($fields, array($this, 'parseKey'));
        $sql   =    'INSERT INTO '.$this->parseTable($table).' ('.implode(',', $fields).') ';
        $sql   .= $this->buildSelectSql($options);
        return $this->execute($sql,$this->parseBind(!empty($options['bind'])?$options['bind']:array()));
    }

    /**
     * 更新记录
     * @access public
     * @param mixed $data 数据
     * @param array $options 表达式
     * @return false | integer
     */
    public function update($data,$options) {
        $this->model  =   $options['model'];
        $sql   = 'UPDATE '
            .$this->parseTable($options['table'])
            .$this->parseSet($data)
            .$this->parseWhere(!empty($options['where'])?$options['where']:'')
            .$this->parseOrder(!empty($options['order'])?$options['order']:'')
            .$this->parseLimit(!empty($options['limit'])?$options['limit']:'')
            .$this->parseLock(isset($options['lock'])?$options['lock']:false)
            .$this->parseComment(!empty($options['comment'])?$options['comment']:'');
        return $this->execute($sql,$this->parseBind(!empty($options['bind'])?$options['bind']:array()));
    }

    /**
     * 删除记录
     * @access public
     * @param array $options 表达式
     * @return false | integer
     */
    public function delete($options=array()) {
        $this->model  =   $options['model'];
        $sql   = 'DELETE FROM '
            .$this->parseTable($options['table'])
            .$this->parseWhere(!empty($options['where'])?$options['where']:'')
            .$this->parseOrder(!empty($options['order'])?$options['order']:'')
            .$this->parseLimit(!empty($options['limit'])?$options['limit']:'')
            .$this->parseLock(isset($options['lock'])?$options['lock']:false)
            .$this->parseComment(!empty($options['comment'])?$options['comment']:'');
        return $this->execute($sql,$this->parseBind(!empty($options['bind'])?$options['bind']:array()));
    }

    /**
     * 查找记录
     * @access public
     * @param array $options 表达式
     * @return mixed
     */
    public function select($options=array()) {
        $this->model  =   $options['model'];
        $sql        =   $this->buildSelectSql($options); 
        $result     =   $this->query($sql,$this->parseBind(!empty($options['bind'])?$options['bind']:array())); 
        return $result;
    }

    /**
     * 生成查询SQL
     * @access public
     * @param array $options 表达式
     * @return string
     */
    public function buildSelectSql($options=array()) {
        if(isset($options['page'])) {
            // 根据页数计算limit
            if(strpos($options['page'],',')) {
                list($page,$listRows) =  explode(',',$options['page']);
            }else{
                $page = $options['page'];
            }
            $page    =  $page?$page:1;
            $listRows=  isset($listRows)?$listRows:(is_numeric($options['limit'])?$options['limit']:20);
            $offset  =  $listRows*((int)$page-1);
            $options['limit'] =  $offset.','.$listRows;
        }
        /*if(C('DB_SQL_BUILD_CACHE')) { // SQL创建缓存
            $key    =  md5(serialize($options));
            $value  =  S($key);
            if(false !== $value) {
                return $value;
            }
        }*/
        $sql  =     $this->parseSql($this->selectSql,$options);
        $sql .=     $this->parseLock(isset($options['lock'])?$options['lock']:false);
        /*if(isset($key)) { // 写入SQL创建缓存
            S($key,$sql,array('expire'=>0,'length'=>C('DB_SQL_BUILD_LENGTH'),'queue'=>C('DB_SQL_BUILD_QUEUE')));
        }*/ 
        return $sql;
    }

    /**
     * 替换SQL语句中表达式
     * @access public
     * @param array $options 表达式
     * @return string
     */
    public function parseSql($sql,$options=array()){
        $sql   = str_replace(
            array('%TABLE%','%DISTINCT%','%FIELD%','%JOIN%','%WHERE%','%GROUP%','%HAVING%','%ORDER%','%LIMIT%','%UNION%','%COMMENT%'),
            array(
                $this->parseTable($options['table']),
                $this->parseDistinct(isset($options['distinct'])?$options['distinct']:false),
                $this->parseField(!empty($options['field'])?$options['field']:'*'),
                $this->parseJoin(!empty($options['join'])?$options['join']:''),
                $this->parseWhere(!empty($options['where'])?$options['where']:''),
                $this->parseGroup(!empty($options['group'])?$options['group']:''),
                $this->parseHaving(!empty($options['having'])?$options['having']:''),
                $this->parseOrder(!empty($options['order'])?$options['order']:''),
                $this->parseLimit(!empty($options['limit'])?$options['limit']:''),
                $this->parseUnion(!empty($options['union'])?$options['union']:''),
                $this->parseComment(!empty($options['comment'])?$options['comment']:'')
            ),$sql);
        return $sql;
    }

    /**
     * 获取最近一次查询的sql语句 
     * @param string $model  模型名
     * @access public
     * @return string
     */
    public function getLastSql($model='') {
        return $model?$this->modelSql[$model]:$this->queryStr;
    }

    /**
     * 获取最近插入的ID
     * @access public
     * @return string
     */
    public function getLastInsID() {
        return $this->lastInsID;
    }

    /**
     * 获取最近的错误信息
     * @access public
     * @return string
     */
    public function getError() {
        return $this->error;
    }

    /**
     * SQL指令安全过滤
     * @access public
     * @param string $str  SQL字符串
     * @return string
     */
    public function escapeString($str) {
        return addslashes($str);
    }

    /**
     * 设置当前操作模型
     * @access public
     * @param string $model  模型名
     * @return void
     */
    public function setModel($model){
        $this->model =  $model;
    }

   /**
     * 析构方法
     * @access public
     */
    public function __destruct() {
        // 释放查询
        if ($this->queryID){
            $this->free();
        }
        // 关闭连接
        $this->close();
    }

    // 关闭数据库 由驱动类定义
    public function close(){}
}