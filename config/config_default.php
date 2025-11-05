<?php
$_config = array();

// ----------------------------  CONFIG DB  ----------------------------- //
// ----------------------------  数据库相关设置---------------------------- //

/**
 * 数据库主服务器设置, 支持多组服务器设置, 当设置多组服务器时, 则会根据分布式策略使用某个服务器
 * @example
 * $_config['db']['driver'] = '';// 空(默认)/mysqli/pdo
 * $_config['db']['1']['dbhost'] = 'localhost'; // 服务器地址
 * $_config['db']['1']['dbuser'] = 'root'; // 用户
 * $_config['db']['1']['dbpw'] = '';// 密码
 * $_config['db']['1']['dbcharset'] = 'gbk';// 字符集
 * $_config['db']['1']['pconnect'] = '0';// 是否持续连接
 * $_config['db']['1']['dbname'] = 'x1';// 数据库
 * $_config['db']['1']['tablepre'] = 'pre_';// 表名前缀
 * $_config['db']['1']['dsn'] = 'mysql:host=localhost;dbname=x1';// DSN配置（PDO）
 *
 * $_config['db']['2']['dbhost'] = 'localhost';
 * ...
 */
$_config['db'][1]['dbhost']  		= 'localhost';//支持三种直接加端口如：127.0.0.1:3306或使用UNix socket 如：/tmp/mysql.sock
$_config['db'][1]['dbuser']  		= 'root';
$_config['db'][1]['dbpw'] 	 		= 'root';
$_config['db'][1]['dbcharset'] 		= 'utf8mb4';
$_config['db'][1]['pconnect'] 		= 0;
$_config['db'][1]['dbname']  		= 'dzzoffice';
$_config['db'][1]['tablepre'] 		= 'dzz_';
$_config['db'][1]['unix_socket'] = '';//使用此方式连接时 dbhost设置为localhost

/**
 * 数据库从服务器设置( slave, 只读 ), 支持多组服务器设置, 当设置多组服务器时, 系统根据每次随机使用
 * @example
 * $_config['db']['1']['slave']['1']['dbhost'] = 'localhost';
 * $_config['db']['1']['slave']['1']['dbuser'] = 'root';
 * $_config['db']['1']['slave']['1']['dbpw'] = 'root';
 * $_config['db']['1']['slave']['1']['dbcharset'] = 'gbk';
 * $_config['db']['1']['slave']['1']['pconnect'] = '0';
 * $_config['db']['1']['slave']['1']['dbname'] = 'x1';
 * $_config['db']['1']['slave']['1']['tablepre'] = 'pre_';
 * $_config['db']['1']['slave']['1']['weight'] = '0'; //权重：数据越大权重越高
 *
 * $_config['db']['1']['slave']['2']['dbhost'] = 'localhost';
 * ...
 *
 */
$_config['db']['1']['slave'] = array();
//数据库驱动
$_config['db']['driver'] = 'mysqli';
//启用从服务器的开关
$_config['db']['slave'] = false;
/**
 * 数据库 分布部署策略设置
 *
 * @example 将 user 部署到第二服务器, session 部署在第三服务器, 则设置为
 * $_config['db']['map']['user'] = 2;
 * $_config['db']['map']['session'] = 3;
 *
 * 对于没有明确声明服务器的表, 则一律默认部署在第一服务器上
 *
 */
$_config['db']['map'] = array();

/**
 * 数据库 公共设置, 此类设置通常对针对每个部署的服务器
 */
$_config['db']['common'] = array();

/**
 *  禁用从数据库的数据表, 表名字之间使用逗号分割
 *
 * @example session, user 这两个表仅从主服务器读写, 不使用从服务器
 * $_config['db']['common']['slave_except_table'] = 'session, user';
 *
 */
$_config['db']['common']['slave_except_table'] = '';

/*
 * 数据库引擎，根据自己的数据库引擎进行设置，V2.4.0之后默认为innodb，之前为myisam
 * 对于从低版本升级到V2.4.0及以上，并且没有转换数据库引擎的用户，在此设置为myisam
 */
$_config['db']['common']['engine'] = 'innodb';

/**
 * 内存服务器优化设置
 * 以下设置需要PHP扩展组件支持，其中 memcache 优先于其他设置，
 * 当 memcache 无法启用时，会自动开启另外的两种优化模式
 */

//内存变量前缀, 可更改,避免同服务器中的程序引用错乱
$_config['memory']['prefix'] = 'dzzoffice_';

/* Redis设置, 需要PHP扩展组件支持, timeout参数的作用没有查证 */
$_config['memory']['redis']['server'] = '';
$_config['memory']['redis']['port'] = 6379;
$_config['memory']['redis']['pconnect'] = 1;
$_config['memory']['redis']['timeout'] = 0;
$_config['memory']['redis']['requirepass'] = '';//如果redis需要密码，请填写redis密码
$_config['memory']['redis']['db'] = 0;//这里可以填写0到15的数字，每个站点使用不同的db
/**
 * 是否使用 Redis::SERIALIZER_IGBINARY选项,需要igbinary支持,windows下测试时请关闭，否则会出>现错误Reading from client: Connection reset by peer
 * 支持以下选项，默认使用PHP的serializer
 * Redis::SERIALIZER_IGBINARY =2
 * Redis::SERIALIZER_PHP =1
 * Redis::SERIALIZER_NONE =0 //则不使用serialize,即无法保存array
 */
/**
 * 此配置现在已经取消，默认对array使用php serializer进行编码保存，其它数据直接原样保存 
 */
//$_config['memory']['redis']['serializer'] = 1;

$_config['memory']['memcache']['server'] = ''; // memcache 服务器地址
$_config['memory']['memcache']['port'] = 11211;			// memcache 服务器端口
$_config['memory']['memcache']['pconnect'] = 1;			// memcache 是否长久连接
$_config['memory']['memcache']['timeout'] = 1;			// memcache 服务器连接超时

$_config['memory']['memcached']['server'] = ''; // memcached 服务器地址
$_config['memory']['memcached']['port'] = 11211;		// memcached 服务器端口
$_config['memory']['memcached']['pconnect'] = 1;		// memcached 是否长久连接
$_config['memory']['memcached']['timeout'] = 1;			// memcached 服务器连接超时

$_config['memory']['apc'] = 0;							// 启动对 APC 的支持
$_config['memory']['apcu'] = 0;							// 启动对 APCu 的支持
$_config['memory']['xcache'] = 0;						// 启动对 xcache 的支持
$_config['memory']['eaccelerator'] = 0;					// 启动对 eaccelerator 的支持
$_config['memory']['wincache'] = 0;						// 启动对 wincache 的支持
$_config['memory']['yac'] = 0;     						//启动对 YAC 的支持
$_config['memory']['file']['server'] = '';				// File 缓存存放目录，如设置为 data/cache/filecache ，设置后启动 File 缓存


// 服务器相关设置
$_config['server']['id']		= 1;			// 服务器编号，多webserver的时候，用于标识当前服务器的ID

//计划任务设置
$_config['remote']['on']=0; //1：设定计划任务由外部触发；0：通过用户访问触发
$_config['remote']['cron']=0; //1：设定计划任务由外部触发；0：通过用户访问触发
//  CONFIG CACHE
$_config['cache']['type'] 			= 'sql';	// 缓存类型 file=文件缓存, sql=数据库缓存

// 页面输出设置
$_config['output']['charset'] 			= 'utf-8';	// 页面字符集
$_config['output']['forceheader']		= 1;		// 强制输出页面字符集，用于避免某些环境乱码
$_config['output']['gzip'] 			    = 0;		// 是否采用 Gzip 压缩输出
$_config['output']['tplrefresh'] 		= 1;		// 模板自动刷新开关 0=关闭, 1=打开


$_config['output']['language'] 			= 'zh-cn';	// 页面语言 zh-cn/zh-tw
$_config['output']['language_list']['zh-cn']='简体中文';	// 页面语言 zh-CN/en-US
$_config['output']['language_list']['en-us']='English';

$_config['output']['staticurl'] 		= 'static/';	// 站点静态文件路径，“/”结尾
$_config['output']['ajaxvalidate']		= 0;		// 是否严格验证 Ajax 页面的真实性 0=关闭，1=打开
$_config['output']['iecompatible']		= 0;		// 页面 IE 兼容模式

// COOKIE 设置
$_config['cookie']['cookiepre'] 		= 'dzzoffice_'; 	// COOKIE前缀
$_config['cookie']['cookiedomain'] 		= ''; 		// COOKIE作用域
$_config['cookie']['cookiepath'] 		= '/'; 		// COOKIE作用路径

// 站点安全设置
$_config['security']['authkey']	            = 'dzzoffice';	// 站点加密密钥
$_config['security']['urlxssdefend']		= true;		// 自身 URL XSS 防御
$_config['security']['attackevasive']		= 0;		// CC 攻击防御 1|2|4|8

$_config['security']['querysafe']['status']	= 1;		// 是否开启SQL安全检测，可自动预防SQL注入攻击
$_config['security']['querysafe']['dfunction']	= array('load_file','hex','substring','if','ord','char');
$_config['security']['querysafe']['daction']	= array('@','intooutfile','intodumpfile','unionselect','(select', 'unionall', 'uniondistinct');
$_config['security']['querysafe']['dnote']	= array('/*','*/','#','--','"');
$_config['security']['querysafe']['dlikehex']	= 1;
$_config['security']['querysafe']['afullnote']	= 0;

$_config['admincp']['founder']			= '1';		// 站点创始人：拥有站点管理后台的最高权限，每个站点可以设置 1名或多名创始人
													// 可以使用uid，也可以使用用户名；多个创始人之间请使用逗号“,”分开;
$_config['admincp']['checkip']			= 1;		// 后台管理操作是否验证管理员的 IP, 1=是[安全], 0=否。仅在管理员无法登录后台时设置 0。
$_config['admincp']['runquery']			= 0;		// 是否允许后台运行 SQL 语句 1=是 0=否[安全]
$_config['admincp']['dbimport']			= 0;		// 是否允许后台恢复网站数据  1=是 0=否[安全]
$_config['userlogin']['checkip']		= 1; 		//用户登录错误验证ip，对于同一ip同时使用时建议设置为0,否则当有一位用户登录错误次数超过5次，该ip被锁定15分钟，导致其他的同IP用户无法登录;
$_config['debug'] = 0; //调试模式 2：全部错误都显示，1：只显示致命的错误，0：关闭调试。打开后可以在页面底部显示程序运行时间和内存开销等信息
$_config['sqllog'] = 0; //sql日志， // 1: 仅记录SQL, 2: 记录SQL和调用栈。在系统日志中查看

//$_config['system_os']	= 'linux';		//windows,linux,mac,系统会自动判断
//$_config['system_charset']='utf-8';	//操作系统编码，不设置系统将根据操作系统类型来判断linux:utf-8;windows:gbk;
return $_config;
