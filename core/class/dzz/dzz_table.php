<?php

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
/**
 * DZZ 框架通用数据表操作基类
 * 封装了数据表的增删改查、缓存管理、字段查询等通用操作
 * 核心特性：自动关联主键、缓存自动同步、兼容分表/事务等高级特性
 */
class dzz_table extends dzz_base {

    public $data = [];
    /**
     * 方法钩子存储数组
     * 结构：[方法名 => [前置钩子数组, 后置钩子数组]]，用于扩展方法执行逻辑
     * @var array
     */
    public $methods = [];
    /**
     * 数据表名（不含前缀，由 DB::table() 自动拼接前缀）
     * @var string
     */
    protected $_table;
    /**
     * 数据表主键字段名（如 uid/tid/pid）
     * 必须指定，用于主键查询、缓存索引、排序等核心操作
     * @var string
     */
    protected $_pk;
    /**
     * 缓存前缀键名
     * 用于内存缓存（如memcache/redis）的key前缀，区分不同表的缓存
     * @var string
     */
    protected $_pre_cache_key;
    /**
     * 缓存过期时间（秒）
     * 优先级：全局配置 > 类内定义 > 默认值
     * 0表示永久缓存（需手动清理）
     * @var int
     */
    protected $_cache_ttl;
    /**
     * 是否允许使用内存缓存
     * 由全局内存配置 + 缓存前缀是否存在决定
     * @var bool
     */
    protected $_allowmem;
    /**
     * 微信绑定标识（业务扩展字段，用于微信相关表的特殊处理）
     * @var mixed
     */
    protected $_wxbind;
    /**
     * 是否分表（分表开关，用于大数据量表的分表操作）
     * @var bool
     */
    protected $_split;
    /**
     * 分表数量（分表场景下的表总数，如按uid模10分表则值为10）
     * @var int
     */
    protected $_split_sum;

    /**
     * 构造方法：初始化表名、主键、缓存配置
     * @param array $para 初始化参数
     *        - table: 数据表名（不含前缀）
     *        - pk: 主键字段名
     */
    public function __construct($para = []) {
        if (!empty($para)) {
            $this->_table = $para['table'];
            $this->_pk = $para['pk'];
        }
        // 初始化缓存配置：优先读取全局内存配置，其次类内定义，最后判断缓存是否可用
        if (isset($this->_pre_cache_key) && (($ttl = getglobal('setting/memory/' . $this->_table)) !== null || ($ttl = $this->_cache_ttl) !== null) && memory('check')) {
            $this->_cache_ttl = $ttl;
            $this->_allowmem = true;
        }
        $this->_init_extend();
        parent::__construct();
    }

    /**
     * 获取当前操作的数据表名（不含前缀）
     * @return string 数据表名
     */
    public function getTable() {
        return $this->_table;
    }

    /**
     * 设置当前操作的数据表名（不含前缀）
     * @param string $name 新数据表名
     * @return string 设置后的表名
     */
    public function setTable($name) {
        return $this->_table = $name;
    }

    /**
     * 统计数据表总记录数
     * @return int 记录总数
     */
    public function count() {
        return (int)DB::result_first("SELECT count(*) FROM " . DB::table($this->_table));
    }

    /**
     * 更新数据表记录（封装 DB::update，自动同步缓存）
     * @param mixed $val 主键值（支持单个值或数组，如 1 或 [1,2,3]）
     * @param array $data 要更新的字段-值数组（如 ['credits' => 100]）
     * @param bool $unbuffered 是否非缓冲查询（减少内存占用，无返回值）
     * @param bool $low_priority 低优先级更新（等待读操作完成后执行）
     * @return mixed 成功返回受影响行数，失败返回0/false（取决于unbuffered）
     */
    public function update($val, $data, $unbuffered = false, $low_priority = false) {
        // 校验参数：主键值存在 + 更新数据非空且为数组
        if (isset($val) && !empty($data) && is_array($data)) {
            // 检查主键是否已定义
            $this->checkpk();
            // 执行更新：DB::field() 安全拼接主键条件（防注入）
            $ret = DB::update($this->_table, $data, DB::field($this->_pk, $val), $unbuffered, $low_priority);
            // 遍历主键值，同步更新缓存（合并原有缓存数据）
            foreach ((array)$val as $id) {
                $this->update_cache($id, $data);
            }
            return $ret;
        }
        // 参数不合法时，按unbuffered返回对应默认值
        return !$unbuffered ? 0 : false;
    }

    /**
     * 删除数据表记录（封装 DB::delete，自动清理缓存）
     * @param mixed $val 主键值（支持单个值或数组）
     * @param bool $unbuffered 是否非缓冲查询
     * @return bool|int 成功返回受影响行数/true，失败返回false
     */
    public function delete($val, $unbuffered = false) {
        $ret = false;
        if (isset($val)) {
            // 检查主键是否已定义
            $this->checkpk();
            // 执行删除操作
            $ret = DB::delete($this->_table, DB::field($this->_pk, $val), null, $unbuffered);
            // 清理对应主键的缓存
            $this->clear_cache($val);
        }
        return $ret;
    }

    /**
     * 清空数据表（TRUNCATE，不可逆，慎用）
     * 注意：TRUNCATE 会重置自增ID，且不触发触发器
     */
    public function truncate() {
        DB::query("TRUNCATE " . DB::table($this->_table));
    }

    /**
     * 插入数据（封装 DB::insert，自动处理缓存）
     * @param array $data 插入的字段-值数组
     * @param bool $return_insert_id 是否返回自增主键ID
     * @param bool $replace 是否使用 REPLACE INTO（冲突时替换）
     * @param bool $silent 是否静默执行（忽略错误）
     * @return mixed 成功返回自增ID/true，失败返回false
     */
    public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
        // 若使用REPLACE且主键值存在，先清理缓存（避免缓存脏数据）
        if($replace && !empty($data[$this->_pk])) {
			$this->clear_cache($data[$this->_pk]);
		}
        // 执行插入操作
        return DB::insert($this->_table, $data, $return_insert_id, $replace, $silent);
    }

    /**
     * 校验主键是否已定义（未定义则抛出异常）
     * @throws DbException 主键未定义时抛出异常
     */
    public function checkpk() {
        if (!$this->_pk) {
            throw new DbException('Table ' . $this->_table . ' has not PRIMARY KEY defined');
        }
    }

    /**
     * 根据主键获取单条记录（优先读缓存，缓存未命中则读库并更新缓存）
     * @param mixed $id 主键值
     * @param bool $force_from_db 是否强制从数据库读取（忽略缓存）
     * @return array|false 成功返回记录数组，失败返回false
     */
    public function fetch($id, $force_from_db = false) {
        $data = [];
        if (!empty($id)) {
            // 强制读库 或 缓存未命中时，从数据库读取
            if ($force_from_db || ($data = $this->fetch_cache($id)) === false) {
                $data = DB::fetch_first('SELECT * FROM ' . DB::table($this->_table) . ' WHERE ' . DB::field($this->_pk, $id));
                // 读取成功则更新缓存
                if (!empty($data)) $this->store_cache($id, $data);
            }
        }
        return $data;
    }

    /**
     * 根据主键数组批量获取记录（缓存优化，减少数据库查询）
     * @param array $ids 主键值数组（如 [1,2,3]）
     * @param bool $force_from_db 是否强制从数据库读取
     * @return array 以主键为键的记录数组
     */
    public function fetch_all($ids, $force_from_db = false) {
        $data = [];
        if (!empty($ids)) {
            // 强制读库 或 缓存不完整时，补充读取数据库
            if ($force_from_db || ($data = $this->fetch_cache($ids)) === false || count((array)$ids) != count((array)$data)) {
                // 过滤已缓存的主键，仅查询未缓存的部分
                if (is_array($data) && !empty($data)) {
                    $ids = array_diff($ids, array_keys($data));
                }
                // 初始化缓存数据数组
                if ($data === false) $data = [];
                // 查询未缓存的主键记录
                if (!empty($ids)) {
                    $query = DB::query('SELECT * FROM ' . DB::table($this->_table) . ' WHERE ' . DB::field($this->_pk, $ids));
                    while ($value = DB::fetch($query)) {
                        $data[$value[$this->_pk]] = $value;
                        // 逐条更新缓存
                        $this->store_cache($value[$this->_pk], $value);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 获取数据表的字段结构信息
     * @return array|false 成功返回字段信息数组（key=字段名，value=字段属性），失败返回false
     */
    public function fetch_all_field() {
        $data = false;
        // 执行 SHOW FIELDS 查询字段结构，SILENT 静默执行忽略错误
        $query = DB::query('SHOW FIELDS FROM ' . DB::table($this->_table), '', 'SILENT');
        if ($query) {
            $data = [];
            while ($value = DB::fetch($query)) {
                $data[$value['Field']] = $value;
            }
        }
        return $data;
    }

    /**
     * 按范围获取记录（分页/排序）
     * @param int $start 起始偏移量（默认0）
     * @param int $limit 获取条数（默认0=全部）
     * @param string $sort 排序方向（asc/desc，空则不排序）
     * @return array 记录数组（以主键为键，若无主键则数字索引）
     */
    public function range($start = 0, $limit = 0, $sort = '') {
        if ($sort) {
            $this->checkpk();// 排序时必须有主键
        }
        // DB::order() 安全拼接排序语句，DB::limit() 拼接分页语句
        return DB::fetch_all('SELECT * FROM ' . DB::table($this->_table) . ($sort ? ' ORDER BY ' . DB::order($this->_pk, $sort) : '') . DB::limit($start, $limit), null, $this->_pk ?: '');
    }

    /**
     * 优化数据表（整理碎片，提升查询效率）
     * 仅对MyISAM/InnoDB引擎有效，SILENT 忽略优化失败错误
     */
    public function optimize() {
        DB::query('OPTIMIZE TABLE ' . DB::table($this->_table), 'SILENT');
    }

    /**
     * 从内存缓存中读取数据
     * @param mixed $ids 主键值/主键数组（支持批量读取）
     * @param string $pre_cache_key 缓存前缀（默认使用类内定义）
     * @return array|false 成功返回缓存数据，失败返回false
     */
    public function fetch_cache($ids, $pre_cache_key = null) {
        $data = false;
        // 仅当允许缓存时执行
        if ($this->_allowmem) {
            if ($pre_cache_key === null) $pre_cache_key = $this->_pre_cache_key;
            $data = memory('get', $ids, $pre_cache_key);
        }
        return $data;
    }

    /**
     * 将数据存入内存缓存
     * @param mixed $id 主键值（单个）
     * @param array $data 要缓存的记录数组
     * @param int $cache_ttl 缓存过期时间（默认使用类内定义）
     * @param string $pre_cache_key 缓存前缀（默认使用类内定义）
     * @return bool 成功返回true，失败返回false
     */
    public function store_cache($id, $data, $cache_ttl = null, $pre_cache_key = null) {
        $ret = false;
        if ($this->_allowmem) {
            if ($pre_cache_key === null) $pre_cache_key = $this->_pre_cache_key;
            if ($cache_ttl === null) $cache_ttl = $this->_cache_ttl;
            $ret = memory('set', $id, $data, $cache_ttl, $pre_cache_key);
        }
        return $ret;
    }

    /**
     * 清理指定主键的内存缓存
     * @param mixed $ids 主键值/主键数组（支持批量清理）
     * @param string $pre_cache_key 缓存前缀（默认使用类内定义）
     * @return bool 成功返回true，失败返回false
     */
    public function clear_cache($ids, $pre_cache_key = null) {
        $ret = false;
        if ($this->_allowmem) {
            if ($pre_cache_key === null) $pre_cache_key = $this->_pre_cache_key;
            $ret = memory('rm', $ids, $pre_cache_key);
        }
        return $ret;
    }

    /**
     * 更新缓存数据（合并原有缓存与新数据）
     * @param mixed $id 主键值
     * @param array $data 要更新的字段-值数组
     * @param int $cache_ttl 缓存过期时间
     * @param string $pre_cache_key 缓存前缀
     * @return bool 成功返回true，失败返回false
     */
    public function update_cache($id, $data, $cache_ttl = null, $pre_cache_key = null) {
        $ret = false;
        if ($this->_allowmem) {
            if ($pre_cache_key === null) $pre_cache_key = $this->_pre_cache_key;
            if ($cache_ttl === null) $cache_ttl = $this->_cache_ttl;
            // 先读取原有缓存，存在则合并数据后重新存储
            if (($_data = memory('get', $id, $pre_cache_key)) !== false) {
                $ret = $this->store_cache($id, array_merge($_data, $data), $cache_ttl, $pre_cache_key);
            }
        }
        return $ret;
    }

    /**
     * 批量更新缓存数据
     * @param array $ids 主键数组
     * @param array $data 要更新的字段-值数组
     * @param int $cache_ttl 缓存过期时间
     * @param string $pre_cache_key 缓存前缀
     * @return bool 成功返回true，失败返回false
     */
    public function update_batch_cache($ids, $data, $cache_ttl = null, $pre_cache_key = null) {
        $ret = false;
        if ($this->_allowmem) {
            if ($pre_cache_key === null) $pre_cache_key = $this->_pre_cache_key;
            if ($cache_ttl === null) $cache_ttl = $this->_cache_ttl;
            // 批量读取缓存数据
            if (($_data = memory('get', $ids, $pre_cache_key)) !== false) {
                // 逐条合并数据并更新缓存
                foreach ($_data as $id => $value) {
                    $ret = $this->store_cache($id, array_merge($value, $data), $cache_ttl, $pre_cache_key);
                }
            }
        }
        return $ret;
    }

    /**
     * 重置缓存（删除旧缓存，从数据库重新读取并更新）
     * @param array $ids 主键数组
     * @param string $pre_cache_key 缓存前缀
     * @return bool 成功返回true，失败返回false
     */
    public function reset_cache($ids, $pre_cache_key = null) {
        $ret = false;
        if ($this->_allowmem) {
            $keys = [];
            // 获取已缓存的主键
            if (($cache_data = $this->fetch_cache($ids, $pre_cache_key)) !== false) {
                $keys = array_intersect(array_keys($cache_data), $ids);
                unset($cache_data);
            }
            // 对已缓存的主键，强制从数据库读取并更新缓存
            if (!empty($keys)) {
                $this->fetch_all($keys, true);
                $ret = true;
            }
        }
        return $ret;
    }

    /**
     * 增量更新缓存中的数值型字段（如积分、计数）
     * @param array $ids 主键数组
     * @param array $data 增量数据（如 ['credits' => 10] 或 ['count' => [0]]）
     * @param int $cache_ttl 缓存过期时间
     * @param string $pre_cache_key 缓存前缀
     */
    public function increase_cache($ids, $data, $cache_ttl = null, $pre_cache_key = null) {
        if ($this->_allowmem) {
            // 批量读取缓存数据
            if (($cache_data = $this->fetch_cache($ids, $pre_cache_key)) !== false) {
                foreach ($cache_data as $id => $one) {
                    foreach ($data as $key => $value) {
                        // 兼容数组格式的增量值（如 [0] 表示重置为0）
                        if (is_array($value)) {
                            $one[$key] = $value[0];
                        } else {
                            $one[$key] = $one[$key] + ($value);
                        }
                    }
                    // 更新单条缓存
                    $this->store_cache($id, $one, $cache_ttl, $pre_cache_key);
                }
            }
        }
    }

    /**
     * 魔术方法：将对象转为字符串时返回数据表名
     * @return string 数据表名
     */
    public function __toString() {
        return $this->_table;
    }

    /**
     * 子类扩展初始化方法（钩子）
     * 子类可重写此方法实现自定义初始化逻辑
     */
    protected function _init_extend() {}

    /**
     * 绑定方法前置钩子
     * @param string $name 方法名
     * @param callable $fn 钩子函数（可调用对象）
     */
    public function attach_before_method($name, $fn) {
        $this->methods[$name][0][] = $fn;
    }

    /**
     * 绑定方法后置钩子
     * @param string $name 方法名
     * @param callable $fn 钩子函数（可调用对象）
     */
    public function attach_after_method($name, $fn) {
        $this->methods[$name][1][] = $fn;
    }
}

