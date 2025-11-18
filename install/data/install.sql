DROP TABLE IF EXISTS `dzz_admincp_session`;
CREATE TABLE `dzz_admincp_session` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `adminid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `panel` tinyint(1) NOT NULL DEFAULT '0' COMMENT '面板类型',
  `ip` varchar(45) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `errorcount` tinyint(1) NOT NULL DEFAULT '0' COMMENT '错误次数',
  `storage` mediumtext NOT NULL COMMENT '存储数据',
  PRIMARY KEY (`uid`,`panel`)
) ENGINE=InnoDB COMMENT='管理员会话表';

DROP TABLE IF EXISTS `dzz_app_market`;
CREATE TABLE `dzz_app_market` (
  `appid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '应用ID',
  `mid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '云端应用ID',
  `appname` varchar(255) NOT NULL COMMENT '应用名称',
  `appico` varchar(255) NOT NULL COMMENT '应用图标',
  `appdesc` text NOT NULL COMMENT '应用介绍',
  `appurl` varchar(255) NOT NULL COMMENT '应用URL',
  `appadminurl` varchar(255) DEFAULT NULL COMMENT '管理设置地址',
  `noticeurl` varchar(255) NOT NULL DEFAULT '' COMMENT '通知接口地址',
  `dateline` int(10) unsigned NOT NULL COMMENT '创建时间',
  `disp` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '显示顺序',
  `vendor` varchar(255) NOT NULL COMMENT '提供商',
  `haveflash` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '窗口遮罩',
  `isshow` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示应用图标',
  `havetask` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示任务栏',
  `hideInMarket` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '应用市场里不显示',
  `feature` text NOT NULL COMMENT '窗体feature',
  `fileext` text NOT NULL COMMENT '可以打开的文件类型',
  `group` tinyint(1) NOT NULL DEFAULT '1' COMMENT '应用的分组:0:全部；-1:游客可用，3:系统管理员可用;2：部门管理员可用;1:所有成员可用',
  `orgid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可以使用的部门id，为0表示不限制',
  `position` tinyint(1) NOT NULL DEFAULT '0' COMMENT '2：desktop,3：taskbar,1：apparea',
  `system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否系统应用',
  `notdelete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否强制安装禁止删除',
  `open` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否新窗口打开',
  `nodup` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否禁止重复打开',
  `identifier` varchar(40) NOT NULL DEFAULT '' COMMENT '应用标识符',
  `app_path` varchar(50) DEFAULT NULL COMMENT 'APP路劲',
  `available` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否可用',
  `version` varchar(20) NOT NULL DEFAULT '' COMMENT '版本号',
  `upgrade_version` text NOT NULL COMMENT '升级版本',
  `check_upgrade_time` int(11) NOT NULL DEFAULT '0' COMMENT '最近次检测升级时间',
  `extra` text NOT NULL COMMENT '附加信息',
  PRIMARY KEY (`appid`),
  UNIQUE KEY `appurl` (`appurl`),
  KEY `available` (`available`),
  KEY `identifier` (`identifier`)
) ENGINE=InnoDB COMMENT='应用市场表';

DROP TABLE IF EXISTS `dzz_app_open`;
CREATE TABLE `dzz_app_open` (
  `ext` varchar(255) NOT NULL DEFAULT '' COMMENT '文件后缀',
  `appid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '应用ID',
  `disp` smallint(6) NOT NULL DEFAULT '0' COMMENT '排序',
  `extid` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '扩展ID',
  `isdefault` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认',
  PRIMARY KEY (`extid`),
  KEY `appid` (`appid`),
  KEY `ext` (`ext`,`disp`)
) ENGINE=InnoDB COMMENT='应用打开方式表';

DROP TABLE IF EXISTS `dzz_app_open_default`;
CREATE TABLE `dzz_app_open_default` (
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
  `ext` varchar(255) NOT NULL DEFAULT '' COMMENT '文件后缀',
  `extid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '扩展ID',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  UNIQUE KEY `defaultext` (`ext`,`uid`)
) ENGINE=InnoDB COMMENT='用户默认应用打开方式表';

DROP TABLE IF EXISTS `dzz_app_organization`;
CREATE TABLE `dzz_app_organization` (
  `appid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '应用ID',
  `orgid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '组织部门ID',
  `dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  UNIQUE KEY `orgid` (`appid`,`orgid`),
  KEY `appid` (`appid`)
) ENGINE=InnoDB COMMENT='应用与组织关系表';

DROP TABLE IF EXISTS `dzz_app_pic`;
CREATE TABLE `dzz_app_pic` (
  `picid` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '图片ID',
  `appid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '应用ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `username` varchar(15) NOT NULL DEFAULT '' COMMENT '用户名',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `aid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件ID',
  PRIMARY KEY (`picid`),
  KEY `uid` (`uid`),
  KEY `idtype` (`appid`)
) ENGINE=InnoDB COMMENT='应用图片表';

DROP TABLE IF EXISTS `dzz_app_relative`;
CREATE TABLE `dzz_app_relative` (
  `rid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '关联ID',
  `appid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '应用ID',
  `tagid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '标签ID',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `appid` (`appid`,`tagid`)
) ENGINE=InnoDB COMMENT='应用关联表';

DROP TABLE IF EXISTS `dzz_app_tag`;
CREATE TABLE `dzz_app_tag` (
  `tagid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '标签ID',
  `hot` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '热度',
  `tagname` char(15) NOT NULL DEFAULT '0' COMMENT '标签名',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`tagid`),
  KEY `appid` (`hot`),
  KEY `classid` (`tagname`)
) ENGINE=InnoDB COMMENT='应用标签表';

DROP TABLE IF EXISTS `dzz_app_user`;
CREATE TABLE `dzz_app_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `appid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '应用ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `lasttime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后访问时间',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `num` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '使用次数',
  PRIMARY KEY (`id`),
  UNIQUE KEY `appuser` (`appid`,`uid`),
  KEY `appid` (`appid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB COMMENT='应用用户表';

DROP TABLE IF EXISTS `dzz_attach`;
CREATE TABLE `dzz_attach` (
  `qid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '附件ID',
  `uid` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `username` char(30) NOT NULL DEFAULT '' COMMENT '用户名',
  `fid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件ID',
  `aid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件ID',
  `filename` char(80) NOT NULL DEFAULT '' COMMENT '文件名',
  `area` char(15) NOT NULL DEFAULT '' COMMENT '地区',
  `areaid` int(10) NOT NULL DEFAULT '0' COMMENT '地区ID',
  `reversion` smallint(6) NOT NULL DEFAULT '0' COMMENT '版本',
  `downloads` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下载次数',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `deletetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  `deleteuid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除人UID',
  PRIMARY KEY (`qid`),
  KEY `dateline` (`dateline`),
  KEY `tid` (`fid`),
  KEY `area` (`area`),
  KEY `areaid` (`areaid`,`area`)
) ENGINE=InnoDB COMMENT='附件表';

DROP TABLE IF EXISTS `dzz_attachment`;
CREATE TABLE `dzz_attachment` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '附件ID',
  `filename` char(100) NOT NULL DEFAULT '' COMMENT '文件名',
  `filetype` char(15) NOT NULL DEFAULT '' COMMENT '文件类型',
  `filesize` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `attachment` char(60) NOT NULL DEFAULT '' COMMENT '附件路径',
  `remote` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '是否远程',
  `copys` smallint(6) NOT NULL DEFAULT '0' COMMENT '副本数',
  `md5` char(32) NOT NULL COMMENT 'MD5',
  `thumb` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为缩略图',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传时间',
  `unrun` tinyint(1) NOT NULL DEFAULT '0' COMMENT '未运行',
  PRIMARY KEY (`aid`),
  KEY `dateline` (`dateline`),
  KEY `md5` (`md5`,`filesize`),
  KEY `filetype` (`filetype`),
  KEY `unrun` (`unrun`)
) ENGINE=InnoDB COMMENT='系统附件表';

DROP TABLE IF EXISTS `dzz_cache`;
CREATE TABLE `dzz_cache` (
  `cachekey` varchar(255) NOT NULL DEFAULT '' COMMENT '缓存键',
  `cachevalue` mediumblob NOT NULL COMMENT '缓存值',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`cachekey`)
) ENGINE=InnoDB COMMENT='缓存表';

DROP TABLE IF EXISTS `dzz_collect`;
CREATE TABLE `dzz_collect` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '收藏ID',
  `ourl` varchar(255) NOT NULL DEFAULT '' COMMENT '收藏地址',
  `data` text NOT NULL COMMENT '数据',
  `dateline` int(10) unsigned NOT NULL COMMENT '创建时间',
  `copys` int(10) unsigned NOT NULL COMMENT '副本数',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '类型',
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB COMMENT='收藏表';

DROP TABLE IF EXISTS `dzz_comment`;
CREATE TABLE `dzz_comment` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `pcid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父评论ID',
  `rcid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复评论ID',
  `id` varchar(60) NOT NULL DEFAULT '' COMMENT '评论对象ID',
  `idtype` varchar(20) NOT NULL DEFAULT '' COMMENT '评论对象类型',
  `module` varchar(50) NOT NULL DEFAULT '' COMMENT '调用的模块名，通常为应用的目录',
  `authorid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论者UID',
  `author` varchar(15) NOT NULL DEFAULT '' COMMENT '评论者用户名',
  `ip` varchar(20) NOT NULL DEFAULT '' COMMENT '评论者IP',
  `xtllq` tinytext COMMENT '浏览器信息',
  `port` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '端口',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论时间',
  `message` text NOT NULL COMMENT '评论内容',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `edituid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '编辑者UID',
  `edittime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '编辑时间',
  PRIMARY KEY (`cid`),
  KEY `authorid` (`authorid`,`idtype`),
  KEY `id` (`id`,`idtype`,`dateline`)
) ENGINE=InnoDB COMMENT='评论表';

DROP TABLE IF EXISTS `dzz_comment_at`;
CREATE TABLE `dzz_comment_at` (
  `cid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被@的用户ID',
  `dateline` int(10) unsigned NOT NULL COMMENT '创建时间',
  UNIQUE KEY `pid_uid` (`cid`,`uid`),
  KEY `dateline` (`dateline`)
) ENGINE=InnoDB COMMENT='评论@表';

DROP TABLE IF EXISTS `dzz_comment_attach`;
CREATE TABLE `dzz_comment_attach` (
  `qid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `cid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论ID',
  `aid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件ID',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `downloads` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下载次数',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '附件类型',
  `img` varchar(255) NOT NULL COMMENT '图片',
  `url` varchar(255) NOT NULL COMMENT 'URL',
  `ext` varchar(20) NOT NULL DEFAULT '' COMMENT '附件扩展名',
  PRIMARY KEY (`qid`),
  KEY `dateline` (`dateline`),
  KEY `tid` (`cid`)
) ENGINE=InnoDB COMMENT='评论附件表';

DROP TABLE IF EXISTS `dzz_connect`;
CREATE TABLE `dzz_connect` (
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `key` varchar(255) NOT NULL DEFAULT '',
  `secret` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(255) NOT NULL COMMENT 'pan,mail,storage,web',
  `bz` varchar(255) NOT NULL COMMENT 'Dropbox,Box,Google,Aliyun,Grandcloud',
  `root` varchar(255) NOT NULL DEFAULT '' COMMENT '根目录',
  `available` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否可用',
  `dname` varchar(255) NOT NULL COMMENT '数据库名称',
  `curl` varchar(255) NOT NULL COMMENT '授权地址',
  `disp` smallint(6) NOT NULL DEFAULT '0' COMMENT '显示顺序',
  UNIQUE KEY `bz` (`bz`),
  KEY `disp` (`disp`)
) ENGINE=InnoDB COMMENT='连接表';

DROP TABLE IF EXISTS `dzz_connect_disk`;
CREATE TABLE `dzz_connect_disk` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '云盘ID',
  `cloudname` varchar(255) NOT NULL DEFAULT '' COMMENT '云盘名称',
  `attachdir` varchar(255) NOT NULL DEFAULT '' COMMENT '绝对位置',
  `attachurl` varchar(255) NOT NULL DEFAULT '' COMMENT '访问地址（选填）',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `perm` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '权限',
  `bz` varchar(10) NOT NULL DEFAULT 'DISK' COMMENT '云盘标识',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `charset` varchar(30) NOT NULL DEFAULT 'GBK' COMMENT '编码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB COMMENT='磁盘连接表';

DROP TABLE IF EXISTS `dzz_connect_ftp`;
CREATE TABLE `dzz_connect_ftp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `host` varchar(255) NOT NULL DEFAULT '' COMMENT 'FTP地址',
  `cloudname` varchar(255) NOT NULL DEFAULT '' COMMENT '云端名称',
  `username` varchar(255) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `port` smallint(6) NOT NULL DEFAULT '0' COMMENT '端口',
  `timeout` smallint(6) NOT NULL DEFAULT '90' COMMENT '超时时间',
  `ssl` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否SSL',
  `pasv` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否PASV',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `perm` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '权限',
  `bz` varchar(10) NOT NULL DEFAULT 'FTP' COMMENT '标识',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `on` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `charset` varchar(30) NOT NULL DEFAULT 'GBK' COMMENT '编码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB COMMENT='FTP连接表';

DROP TABLE IF EXISTS `dzz_connect_onedrive`;
CREATE TABLE `dzz_connect_onedrive` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `bz` varchar(30) NOT NULL COMMENT '云盘标识',
  `cloudname` varchar(255) NOT NULL DEFAULT '' COMMENT '云端名称',
  `cuid` char(60) NOT NULL DEFAULT '' COMMENT '云端UID',
  `cusername` varchar(255) NOT NULL COMMENT '云名称',
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
  `expires_in` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '过期时间',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `refreshtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '刷新时间',
  `perm` smallint(6) unsigned NOT NULL DEFAULT '29751' COMMENT '权限',
  `refresh_token` text NOT NULL COMMENT '刷新token',
  `access_token` text NOT NULL COMMENT '访问token',
  `scope` varchar(255) NOT NULL COMMENT '权限范围',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuid` (`cuid`,`uid`)
) ENGINE=InnoDB COMMENT='OneDrive连接表';

DROP TABLE IF EXISTS `dzz_connect_pan`;
CREATE TABLE `dzz_connect_pan` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `bz` varchar(255) NOT NULL COMMENT '云盘标识',
  `cloudname` varchar(255) NOT NULL DEFAULT '' COMMENT '云盘名称',
  `cuid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '云用户ID',
  `cusername` varchar(255) NOT NULL COMMENT '云用户名',
  `portrait` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
  `expires_in` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '过期时间',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `refreshtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '刷新时间',
  `perm` smallint(6) unsigned NOT NULL DEFAULT '29751' COMMENT '权限',
  `refresh_token` varchar(255) NOT NULL COMMENT '刷新令牌',
  `access_token` varchar(255) NOT NULL COMMENT '访问令牌',
  `scope` varchar(255) NOT NULL COMMENT '权限范围',
  `session_key` varchar(255) NOT NULL COMMENT '会话密钥',
  `session_secret` varchar(255) NOT NULL COMMENT '会话密钥',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuid` (`cuid`,`uid`,`bz`)
) ENGINE=InnoDB COMMENT='网盘连接表';

DROP TABLE IF EXISTS `dzz_connect_storage`;
CREATE TABLE `dzz_connect_storage` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
  `cloudname` varchar(255) NOT NULL DEFAULT '' COMMENT '云盘名称',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `perm` smallint(6) unsigned NOT NULL DEFAULT '29751' COMMENT '权限',
  `access_id` varchar(255) NOT NULL COMMENT '访问ID',
  `access_key` varchar(255) NOT NULL COMMENT '访问密钥',
  `bucket` char(30) NOT NULL DEFAULT '' COMMENT '存储桶',
  `bz` varchar(30) NOT NULL DEFAULT '' COMMENT '云存储标识',
  `hostname` varchar(255) NOT NULL DEFAULT '' COMMENT '主机名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB COMMENT='存储连接表';

DROP TABLE IF EXISTS `dzz_cron`;
CREATE TABLE `dzz_cron` (
  `cronid` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '任务ID',
  `available` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用',
  `type` enum('user','system','app') NOT NULL DEFAULT 'user' COMMENT '任务类型',
  `name` char(50) NOT NULL DEFAULT '' COMMENT '任务名称',
  `filename` char(50) NOT NULL DEFAULT '' COMMENT '任务文件名',
  `lastrun` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上次运行时间',
  `nextrun` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下次运行时间',
  `weekday` tinyint(1) NOT NULL DEFAULT '0' COMMENT '周几运行',
  `day` tinyint(2) NOT NULL DEFAULT '0' COMMENT '几号运行',
  `hour` tinyint(2) NOT NULL DEFAULT '0' COMMENT '几点运行',
  `minute` char(36) NOT NULL DEFAULT '' COMMENT '几分运行',
  PRIMARY KEY (`cronid`),
  KEY `nextrun` (`available`,`nextrun`)
) ENGINE=InnoDB COMMENT='计划任务表';

DROP TABLE IF EXISTS `dzz_district`;
CREATE TABLE `dzz_district` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '地区ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '地区名称',
  `level` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '地区等级',
  `usetype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '地区用途',
  `upid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '上级ID',
  `displayorder` smallint(6) NOT NULL DEFAULT '0' COMMENT '显示顺序',
  PRIMARY KEY (`id`),
  KEY `upid` (`upid`,`displayorder`)
) ENGINE=InnoDB COMMENT='地区表';

DROP TABLE IF EXISTS `dzz_failedlogin`;
CREATE TABLE `dzz_failedlogin` (
  `ip` varchar(45) NOT NULL DEFAULT '' COMMENT '登录IP',
  `username` char(32) NOT NULL DEFAULT '' COMMENT '登录用户名',
  `count` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '登录失败次数',
  `lastupdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`ip`,`username`)
) ENGINE=InnoDB COMMENT='登录失败表';

DROP TABLE IF EXISTS `dzz_folder`;
CREATE TABLE `dzz_folder` (
  `fid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文件夹ID',
  `pfid` int(11) NOT NULL DEFAULT '0' COMMENT '父级目录id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `username` char(30) NOT NULL DEFAULT '' COMMENT '用户名',
  `innav` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否在导航中显示',
  `fname` char(50) NOT NULL DEFAULT '' COMMENT '文件夹名称',
  `perm` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0:继承；1：只读；2：可写',
  `perm_inherit` int(10) NOT NULL DEFAULT '0' COMMENT '继承权限',
  `fsperm` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '超级权限',
  `disp` smallint(6) NOT NULL DEFAULT '0' COMMENT '显示顺序',
  `iconview` tinyint(1) NOT NULL DEFAULT '1' COMMENT '图标视图',
  `display` smallint(6) NOT NULL DEFAULT '0' COMMENT '显示方式',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `gid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '群组ID',
  `flag` char(15) NOT NULL DEFAULT 'folder' COMMENT '文件标识,文件夹为folder,群组机构部门为organization',
  `default` char(15) NOT NULL DEFAULT '' COMMENT '默认文件夹',
  `isdelete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `deldateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`fid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB COMMENT='文件夹表';

DROP TABLE IF EXISTS `dzz_folder_attr`;
CREATE TABLE `dzz_folder_attr` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `fid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '文件夹id',
  `skey` varchar(30) NOT NULL DEFAULT '' COMMENT '属性key',
  `svalue` text NOT NULL COMMENT '属性value',
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`),
  KEY `skey` (`skey`)
) ENGINE=InnoDB COMMENT='文件夹属性表';

DROP TABLE IF EXISTS `dzz_hooks`;
CREATE TABLE `dzz_hooks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_market_id` int(11) NOT NULL COMMENT '应用ID',
  `name` varchar(40) NOT NULL DEFAULT '' COMMENT '钩子名称',
  `description` text NOT NULL COMMENT '描述',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '类型',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `addons` varchar(255) NOT NULL DEFAULT '' COMMENT '钩子对应程序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态1正常;-1删除',
  `priority` smallint(6) NOT NULL DEFAULT '0' COMMENT '运行优先级，挂载点下的钩子按优先级从高到低顺序执行',
  PRIMARY KEY (`id`),
  KEY `app_market_id` (`name`),
  KEY `priority` (`priority`)
) ENGINE=InnoDB COMMENT='钩子表';

DROP TABLE IF EXISTS `dzz_icon`;
CREATE TABLE `dzz_icon` (
  `did` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `domain` varchar(255) NOT NULL COMMENT '域名',
  `reg` varchar(255) NOT NULL DEFAULT '' COMMENT '匹配正则表达式',
  `ext` varchar(30) NOT NULL DEFAULT '' COMMENT '扩展名',
  `pic` varchar(255) NOT NULL COMMENT '图标',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `check` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否审核',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `username` varchar(255) NOT NULL COMMENT '用户名',
  `copys` int(10) NOT NULL DEFAULT '0' COMMENT '复制次数',
  `disp` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`did`),
  KEY `domain` (`domain`),
  KEY `uid` (`uid`),
  KEY `copys` (`copys`),
  KEY `dateline` (`dateline`)
) ENGINE=InnoDB COMMENT='图标表';

DROP TABLE IF EXISTS `dzz_iconview`;
CREATE TABLE `dzz_iconview` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '图标ID',
  `name` varchar(255) NOT NULL COMMENT '图标名称',
  `width` smallint(6) unsigned NOT NULL DEFAULT '64' COMMENT '图标宽度',
  `height` smallint(6) unsigned NOT NULL DEFAULT '64' COMMENT '图标高度',
  `divwidth` smallint(6) unsigned NOT NULL DEFAULT '100' COMMENT '图标容器宽度',
  `divheight` smallint(6) unsigned NOT NULL DEFAULT '100' COMMENT '图标容器高度',
  `paddingtop` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '图标容器padding-top',
  `paddingleft` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '图标容器padding-left',
  `textlength` smallint(6) unsigned NOT NULL DEFAULT '30' COMMENT '图标名称显示长度',
  `align` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '图标排列方式',
  `avaliable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否可用',
  `disp` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `cssname` varchar(60) NOT NULL COMMENT 'css名称',
  PRIMARY KEY (`id`),
  KEY `avaliable` (`avaliable`,`disp`)
) ENGINE=InnoDB COMMENT='图标视图表';

DROP TABLE IF EXISTS `dzz_imagetype`;
CREATE TABLE `dzz_imagetype` (
  `typeid` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '图片类型ID',
  `available` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否可用',
  `name` char(20) NOT NULL COMMENT '图片类型名称',
  `type` enum('smiley','icon','avatar') NOT NULL DEFAULT 'smiley' COMMENT '图片类型',
  `displayorder` tinyint(3) NOT NULL DEFAULT '0' COMMENT '图片类型显示顺序',
  `directory` char(100) NOT NULL COMMENT '图片类型目录',
  PRIMARY KEY (`typeid`)
) ENGINE=InnoDB COMMENT='图片类型表';

DROP TABLE IF EXISTS `dzz_local_router`;
CREATE TABLE `dzz_local_router` (
  `routerid` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '路由id',
  `name` char(60) NOT NULL DEFAULT '' COMMENT '路由名称',
  `remoteid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '远程服务器id',
  `router` text NOT NULL COMMENT '路由规则',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `available` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否可用',
  `priority` smallint(6) unsigned NOT NULL DEFAULT '100' COMMENT '优先级',
  PRIMARY KEY (`routerid`),
  KEY `priority` (`priority`)
) ENGINE=InnoDB COMMENT='本地路由表';

DROP TABLE IF EXISTS `dzz_local_storage`;
CREATE TABLE `dzz_local_storage` (
  `remoteid` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '存储ID',
  `name` varchar(255) NOT NULL COMMENT '名称',
  `bz` varchar(255) NOT NULL COMMENT 'Dropbox,Box,Google,Aliyun,Grandcloud',
  `isdefault` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '默认',
  `dname` varchar(255) NOT NULL COMMENT '数据库名称',
  `did` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '目录ID',
  `disp` smallint(6) NOT NULL DEFAULT '0' COMMENT '排序',
  `usesize` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '已用空间',
  `totalsize` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总空间',
  `lastupdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`remoteid`),
  KEY `disp` (`disp`)
) ENGINE=InnoDB COMMENT='本地存储表';

DROP TABLE IF EXISTS `dzz_mailcron`;
CREATE TABLE `dzz_mailcron` (
  `cid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '任务ID',
  `touid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接收用户ID',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '收件人邮箱',
  `sendtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送时间',
  PRIMARY KEY (`cid`),
  KEY `sendtime` (`sendtime`)
) ENGINE=InnoDB COMMENT='邮件计划任务表';

DROP TABLE IF EXISTS `dzz_mailqueue`;
CREATE TABLE `dzz_mailqueue` (
  `qid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '队列ID',
  `cid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID',
  `subject` text NOT NULL COMMENT '邮件主题',
  `message` text NOT NULL COMMENT '邮件内容',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送时间',
  PRIMARY KEY (`qid`),
  KEY `mcid` (`cid`,`dateline`)
) ENGINE=InnoDB COMMENT='邮件发送队列表';

DROP TABLE IF EXISTS `dzz_notification`;
CREATE TABLE `dzz_notification` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '通知ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `type` varchar(60) NOT NULL DEFAULT '' COMMENT '通知类型',
  `new` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否新通知',
  `authorid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '作者ID',
  `author` varchar(30) NOT NULL DEFAULT '' COMMENT '作者',
  `note` text NOT NULL COMMENT '通知内容',
  `wx_note` text NOT NULL COMMENT '微信通知内容',
  `wx_new` tinyint(1) NOT NULL DEFAULT '1' COMMENT '微信通知是否新通知',
  `redirecturl` varchar(255) NOT NULL DEFAULT '' COMMENT '跳转地址',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `from_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '来源ID',
  `from_idtype` varchar(20) NOT NULL DEFAULT '' COMMENT '来源类型',
  `from_num` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '来源数量',
  `category` tinyint(1) NOT NULL DEFAULT '0' COMMENT ' 提醒分类 1系统消息 0应用消息',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`new`),
  KEY `category` (`uid`,`dateline`),
  KEY `by_type` (`uid`,`type`,`dateline`),
  KEY `from_id` (`from_id`,`from_idtype`)
) ENGINE=InnoDB COMMENT='通知表';

DROP TABLE IF EXISTS `dzz_onlinetime`;
CREATE TABLE `dzz_onlinetime` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `thismonth` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '本月在线时间',
  `total` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '总在线时间',
  `lastupdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB COMMENT='在线时间表';

DROP TABLE IF EXISTS `dzz_organization`;
CREATE TABLE `dzz_organization` (
  `orgid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '组织ID',
  `orgname` varchar(255) NOT NULL DEFAULT '' COMMENT '组织名称',
  `forgid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父组织ID',
  `worgid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '顶级组织ID',
  `fid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件夹ID',
  `disp` smallint(6) NOT NULL DEFAULT '0' COMMENT '显示顺序',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `usesize` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '使用空间',
  `maxspacesize` bigint(20) NOT NULL DEFAULT '0' COMMENT '0：不限制，-1表示無空間',
  `indesk` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否创建快捷方式',
  `available` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `pathkey` varchar(255) NOT NULL DEFAULT '' COMMENT '路径键',
  `type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0一般机构，1群组机构',
  `desc` varchar(200) NOT NULL DEFAULT '' COMMENT '群组描述',
  `groupback` int(11) unsigned NOT NULL COMMENT '群组背景图',
  `aid` varchar(30) NOT NULL DEFAULT '' COMMENT '群组缩略图,可以是aid,也可以是颜色值',
  `manageon` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '群組管理员开启关闭0关闭，1开启',
  `syatemon` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '系统管理员开启群组，关闭群组，0关闭，1开启',
  `diron` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '群组管理员共享目录开启，0关闭，1开启',
  `extraspace` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '机构群组额外空间大小',
  `buyspace` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '购买空间',
  `allotspace` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分配空间大小',
  PRIMARY KEY (`orgid`),
  KEY `disp` (`disp`),
  KEY `pathkey` (`pathkey`),
  KEY `dateline` (`dateline`)
) ENGINE=InnoDB COMMENT='组织表';

DROP TABLE IF EXISTS `dzz_organization_admin`;
CREATE TABLE `dzz_organization_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `orgid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '组织ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `opuid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `admintype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0管理员，1群组创始人',
  PRIMARY KEY (`id`),
  UNIQUE KEY `orgid` (`orgid`,`uid`)
) ENGINE=InnoDB COMMENT='组织管理员表';

DROP TABLE IF EXISTS `dzz_organization_job`;
CREATE TABLE `dzz_organization_job` (
  `jobid` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '职位ID',
  `orgid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '组织ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '职位名称',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `opuid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作人UID',
  PRIMARY KEY (`jobid`),
  KEY `orgid` (`orgid`)
) ENGINE=InnoDB COMMENT='组织职位表';

DROP TABLE IF EXISTS `dzz_organization_upjob`;
CREATE TABLE `dzz_organization_upjob` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `jobid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '职位ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `opuid` int(10) NOT NULL DEFAULT '0' COMMENT '操作人UID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB COMMENT='组织用户职位表';

DROP TABLE IF EXISTS `dzz_organization_user`;
CREATE TABLE `dzz_organization_user` (
  `orgid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '组织ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `jobid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '职位ID',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  UNIQUE KEY `orgid` (`orgid`,`uid`)
) ENGINE=InnoDB COMMENT='组织用户表';

DROP TABLE IF EXISTS `dzz_process`;
CREATE TABLE `dzz_process` (
  `processid` char(32) NOT NULL COMMENT '进程ID',
  `expiry` int(10) DEFAULT NULL COMMENT '过期时间',
  `extra` int(10) DEFAULT NULL COMMENT '附加信息',
  PRIMARY KEY (`processid`),
  KEY `expiry` (`expiry`)
) ENGINE=InnoDB COMMENT='进程表';

DROP TABLE IF EXISTS `dzz_regip`;
CREATE TABLE `dzz_regip` (
  `ip` varchar(45) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `count` smallint(6) NOT NULL DEFAULT '0',
  KEY ip (ip)
) ENGINE=InnoDB COMMENT='注册IP表';

DROP TABLE IF EXISTS `dzz_resources`;
CREATE TABLE `dzz_resources` (
  `rid` char(32) NOT NULL COMMENT '文件唯一标识',
  `vid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '版本id',
  `oid` int(10) unsigned DEFAULT '0',
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `username` char(30) NOT NULL DEFAULT '' COMMENT '用戶名',
  `pfid` int(11) NOT NULL DEFAULT '0' COMMENT '父级目录id',
  `gid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '群组id',
  `name` char(80) NOT NULL DEFAULT '' COMMENT '名称',
  `type` char(15) NOT NULL DEFAULT '' COMMENT '类型',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '时间',
  `ext` char(15) NOT NULL DEFAULT '' COMMENT '后缀',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '大小',
  `sperm` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '超级权限',
  `isdelete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否被删除',
  `deldateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  `flag` char(15) NOT NULL DEFAULT '' COMMENT '文件标识,文件夹为folder,群组机构部门为organization',
  PRIMARY KEY (`rid`),
  KEY `gid` (`gid`),
  KEY `pfid` (`pfid`),
  KEY `uid` (`uid`),
  KEY `isdelete` (`isdelete`)
) ENGINE=InnoDB COMMENT='网盘文件信息表';

DROP TABLE IF EXISTS `dzz_resources_attr`;
CREATE TABLE `dzz_resources_attr` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `rid` char(32) NOT NULL DEFAULT '0' COMMENT '文件唯一标识',
  `skey` varchar(30) NOT NULL COMMENT '属性名',
  `sval` text NOT NULL COMMENT '属性值',
  `vid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '属性值id',
  PRIMARY KEY (`id`),
  KEY `rid_skey_vid` (`rid`,`skey`,`vid`)
) ENGINE=InnoDB COMMENT='网盘文件属性表';

DROP TABLE IF EXISTS `dzz_resources_cat`;
CREATE TABLE `dzz_resources_cat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `catname` varchar(30) NOT NULL DEFAULT '' COMMENT '分类名称',
  `ext` text NOT NULL COMMENT '分类扩展名',
  `tag` text NOT NULL COMMENT '分类标签',
  `keywords` text NOT NULL COMMENT '分类关键字',
  `default` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1,系統默認；0，非系统默认',
  `iconview` tinyint(1) NOT NULL DEFAULT '1' COMMENT '图标显示',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB COMMENT='网盘文件分类表';

DROP TABLE IF EXISTS `dzz_resources_clipboard`;
CREATE TABLE `dzz_resources_clipboard` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int(11) unsigned NOT NULL COMMENT '用户id',
  `username` varchar(120) NOT NULL COMMENT '用户名',
  `dateline` int(11) unsigned NOT NULL COMMENT '创建时间',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1文件，2文本',
  `files` text NOT NULL COMMENT '文件列表',
  `copytype` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1复制，2粘贴',
  `bz` varchar(10) NOT NULL DEFAULT '' COMMENT 'bz',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB COMMENT='网盘文件剪贴板表';

DROP TABLE IF EXISTS `dzz_resources_collect`;
CREATE TABLE `dzz_resources_collect` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `rid` char(32) NOT NULL DEFAULT '' COMMENT '文件唯一标识',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `path` varchar(120) NOT NULL DEFAULT '' COMMENT '路径',
  `username` char(30) NOT NULL DEFAULT '' COMMENT '用户名',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '时间',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '大小',
  `filename` varchar(60) NOT NULL DEFAULT '' COMMENT '文件名',
  `iconview` tinyint(1) NOT NULL DEFAULT '1' COMMENT '展示方式',
  `disp` smallint(6) NOT NULL DEFAULT '0' COMMENT '排序',
  `pfid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级目录id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB COMMENT='网盘文件收藏表';

DROP TABLE IF EXISTS `dzz_resources_event`;
CREATE TABLE `dzz_resources_event` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` char(32) NOT NULL DEFAULT '' COMMENT '文件唯一标识',
  `gid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '群组id',
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `do_obj` varchar(60) NOT NULL DEFAULT '' COMMENT '操作对象',
  `username` char(30) NOT NULL DEFAULT '' COMMENT '用户名',
  `event_body` varchar(60) NOT NULL DEFAULT '' COMMENT '事体',
  `body_data` text NOT NULL COMMENT '事体数据',
  `dateline` int(10) unsigned NOT NULL COMMENT '创建时间',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0操作，1评论',
  `pfid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级目录id',
  `do` varchar(30) NOT NULL DEFAULT '' COMMENT '操作类型',
  PRIMARY KEY (`id`),
  KEY `rid` (`rid`),
  KEY `uid` (`uid`),
  KEY `pfid` (`pfid`),
  KEY `do` (`do`),
  KEY `dateline` (`dateline`)
) ENGINE=InnoDB COMMENT='网盘文件事件表';

DROP TABLE IF EXISTS `dzz_resources_meta`;
CREATE TABLE `dzz_resources_meta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `rid` char(32) NOT NULL DEFAULT '' COMMENT '文件唯一标识',
  `key` varchar(255) NOT NULL COMMENT '存储key',
  `value` text NOT NULL COMMENT '对应值',
  `dateline` int(11) unsigned NOT NULL COMMENT '创建时间',
  `editdateline` int(11) unsigned NOT NULL COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rid_key` (`rid`,`key`(200)),
  KEY `rid` (`rid`),
  KEY `key` (`key`(200))
) ENGINE=InnoDB COMMENT='网盘文件扩展表';

DROP TABLE IF EXISTS `dzz_resources_path`;
CREATE TABLE `dzz_resources_path` (
  `fid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件夹id',
  `path` varchar(1000) NOT NULL DEFAULT '' COMMENT '路径',
  `pathkey` varchar(255) NOT NULL DEFAULT '' COMMENT '路径层次关系',
  PRIMARY KEY (`fid`)
) ENGINE=InnoDB COMMENT='网盘文件路径表';

DROP TABLE IF EXISTS `dzz_resources_permgroup`;
CREATE TABLE `dzz_resources_permgroup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pername` varchar(50) NOT NULL DEFAULT '' COMMENT '权限组名称',
  `perm` int(10) unsigned NOT NULL COMMENT '权限值',
  `off` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否禁用',
  `default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认',
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1系统默认权限组，不允许删除;0用户自定义权限组',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB COMMENT='网盘文件权限组表';

DROP TABLE IF EXISTS `dzz_resources_recyle`;
CREATE TABLE `dzz_resources_recyle` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` char(32) NOT NULL DEFAULT '' COMMENT '文件唯一标识',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` char(30) NOT NULL DEFAULT '' COMMENT '用户名',
  `gid` int(10) NOT NULL DEFAULT '0' COMMENT '群组id',
  `filename` varchar(60) NOT NULL DEFAULT '' COMMENT '文件名',
  `size` bigint(20) NOT NULL DEFAULT '0' COMMENT '文件大小',
  `cid` int(10) NOT NULL DEFAULT '0',
  `pfid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级目录id',
  `deldateline` int(11) unsigned NOT NULL COMMENT '删除时间',
  `pathinfo` varchar(1000) NOT NULL DEFAULT '' COMMENT '文件路径',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `gid` (`gid`)
) ENGINE=InnoDB COMMENT='网盘文件回收站表';

DROP TABLE IF EXISTS `dzz_resources_statis`;
CREATE TABLE `dzz_resources_statis` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `rid` char(32) NOT NULL DEFAULT '' COMMENT '文件唯一标识',
  `edits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改次数',
  `views` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '浏览次数',
  `downs` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下载次数',
  `fid` int(10) unsigned NOT NULL COMMENT '文件夹id',
  `pfid` int(10) unsigned NOT NULL COMMENT '所属目录',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `opendateline` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '打开时间',
  `editdateline` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `edituid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '编辑者UID',
  PRIMARY KEY (`id`),
  KEY `rid` (`rid`),
  KEY `fid` (`fid`),
  KEY `pfid` (`pfid`,`uid`)
) ENGINE=InnoDB COMMENT='网盘文件统计表';

DROP TABLE IF EXISTS `dzz_resources_tag`;
CREATE TABLE `dzz_resources_tag` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rid` char(32) NOT NULL DEFAULT '' COMMENT '文件唯一标识',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '标签id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB COMMENT='网盘文件标签表';

DROP TABLE IF EXISTS `dzz_resources_version`;
CREATE TABLE `dzz_resources_version` (
  `vid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '版本id',
  `rid` char(32) NOT NULL DEFAULT '' COMMENT '文件唯一标识',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` char(30) NOT NULL DEFAULT '' COMMENT '用户名',
  `vname` char(30) NOT NULL DEFAULT '' COMMENT '版本名称',
  `vdesc` varchar(120) NOT NULL DEFAULT '' COMMENT '版本描述',
  `aid` varchar(255) NOT NULL DEFAULT '' COMMENT '文件路径',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '版本文件大小',
  `ext` char(15) NOT NULL DEFAULT '' COMMENT '文件后缀',
  `type` char(15) NOT NULL DEFAULT '' COMMENT '类型',
  `dateline` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '时间',
  PRIMARY KEY (`vid`)
) ENGINE=InnoDB COMMENT='网盘文件版本表';

DROP TABLE IF EXISTS `dzz_seccheck`;
CREATE TABLE `dzz_seccheck` (
  `ssid` int(10) NOT NULL AUTO_INCREMENT,
  `dateline` int(10) NOT NULL,
  `code` char(6) NOT NULL,
  `succeed` tinyint(1) NOT NULL,
  `verified` tinyint(1) NOT NULL,
  PRIMARY KEY (`ssid`),
  KEY dateline (`dateline`),
  KEY succeed (`succeed`),
  KEY verified (`verified`)
) ENGINE=InnoDB COMMENT='安全验证表';

DROP TABLE IF EXISTS `dzz_session`;
CREATE TABLE `dzz_session` (
  `sid` char(6) NOT NULL DEFAULT '' COMMENT '会话ID',
  `ip` varchar(45) NOT NULL DEFAULT '' COMMENT 'IP',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `username` char(15) NOT NULL DEFAULT '' COMMENT '用户名',
  `groupid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '用户组ID',
  `invisible` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否隐身',
  `action` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '动作',
  `lastactivity` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后活动时间',
  `lastolupdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新在线列表时间',
  UNIQUE KEY `sid` (`sid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB COMMENT='在线会话表';

DROP TABLE IF EXISTS `dzz_setting`;
CREATE TABLE `dzz_setting` (
  `skey` varchar(255) NOT NULL DEFAULT '' COMMENT '设置键',
  `svalue` text NOT NULL COMMENT '设置值',
  PRIMARY KEY (`skey`)
) ENGINE=InnoDB COMMENT='系统设置表';

DROP TABLE IF EXISTS `dzz_shares`;
CREATE TABLE `dzz_shares` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(120) NOT NULL DEFAULT '' COMMENT '分享标题',
  `filepath` text NOT NULL COMMENT '文件',
  `dateline` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分享时间',
  `times` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '分享总次数，为0则为不限制',
  `endtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分享到期时间，0为永久有效',
  `username` varchar(60) NOT NULL DEFAULT '' COMMENT '分享人',
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分享用户id',
  `gid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '群组id',
  `pfid` int(11) NOT NULL DEFAULT '0' COMMENT '父级目录id',
  `password` varchar(256) NOT NULL DEFAULT '' COMMENT '分享密码，留空为公开分享',
  `perm` varchar(120) NOT NULL DEFAULT '' COMMENT '分享权限',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '-4：文件不存在；-3：次数到；-1：已过期；0：正常',
  `private` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否私有',
  `count` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '分享使用次数',
  `downs` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下载次数',
  `views` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '浏览次数',
  `type` char(15) NOT NULL DEFAULT '' COMMENT '文件类型',
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`),
  KEY `uid` (`uid`),
  KEY `pfid` (`pfid`)
) ENGINE=InnoDB COMMENT='分享文件表';

DROP TABLE IF EXISTS `dzz_shorturl`;
CREATE TABLE `dzz_shorturl` (
  `sid` char(10) NOT NULL COMMENT '短链接ID',
  `url` text NOT NULL COMMENT '原始URL',
  `count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击次数',
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB COMMENT='短链接表';

DROP TABLE IF EXISTS `dzz_smiley`;
CREATE TABLE `dzz_smiley` (
  `typeid` smallint(6) unsigned NOT NULL COMMENT '表情类别ID',
  `displayorder` tinyint(1) NOT NULL DEFAULT '0' COMMENT '显示顺序',
  `type` enum('smiley','stamp','stamplist') NOT NULL DEFAULT 'smiley' COMMENT '表情类别',
  `code` varchar(30) NOT NULL DEFAULT '' COMMENT '表情代码',
  `url` varchar(30) NOT NULL DEFAULT '' COMMENT '表情路径',
  `title` varchar(30) NOT NULL DEFAULT '' COMMENT '表情标题',
  `id` smallint(6) NOT NULL AUTO_INCREMENT COMMENT '表情ID',
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`displayorder`)
) ENGINE=InnoDB COMMENT='表情表';

DROP TABLE IF EXISTS `dzz_syscache`;
CREATE TABLE `dzz_syscache` (
  `cname` varchar(32) NOT NULL COMMENT '缓存名称',
  `ctype` tinyint(3) unsigned NOT NULL COMMENT '缓存类型',
  `dateline` int(10) unsigned NOT NULL COMMENT '更新时间',
  `data` mediumblob NOT NULL COMMENT '缓存数据',
  PRIMARY KEY (`cname`)
) ENGINE=InnoDB COMMENT='系统缓存表';

DROP TABLE IF EXISTS `dzz_tag`;
CREATE TABLE `dzz_tag` (
  `tid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '标签ID',
  `tagname` varchar(60) NOT NULL DEFAULT '' COMMENT '标签名',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `username` varchar(60) NOT NULL COMMENT '用户名',
  `idtype` varchar(60) NOT NULL COMMENT '标识类型',
  `hot` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用热度',
  PRIMARY KEY (`tid`)
) ENGINE=InnoDB COMMENT='标签表';

DROP TABLE IF EXISTS `dzz_user`;
CREATE TABLE `dzz_user` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户UID',
  `email` char(40) NOT NULL DEFAULT '' COMMENT '用户邮箱',
  `phone` varchar(255) NOT NULL DEFAULT '' COMMENT '手机号码',
  `weixinid` varchar(255) NOT NULL DEFAULT '' COMMENT '微信号',
  `wechat_userid` varchar(255) NOT NULL DEFAULT '' COMMENT '微信用户ID',
  `wechat_status` tinyint(1) NOT NULL DEFAULT '4' COMMENT '1:已关注；2：已冻结；4：未关注',
  `nickname` char(30) NOT NULL DEFAULT '' COMMENT '用户昵称',
  `username` char(30) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '密码',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `phonestatus` tinyint(1) NOT NULL DEFAULT '0' COMMENT '手机绑定状态',
  `emailsenddate` varchar(50) NOT NULL DEFAULT '0' COMMENT '邮件发送时间',
  `emailstatus` tinyint(1) NOT NULL DEFAULT '0' COMMENT '邮箱绑定状态',
  `avatarstatus` tinyint(1) NOT NULL DEFAULT '0' COMMENT '头像绑定状态',
  `adminid` tinyint(1) NOT NULL DEFAULT '0' COMMENT '管理ID',
  `groupid` smallint(6) unsigned NOT NULL DEFAULT '9' COMMENT '用户组ID',
  `language` varchar(12) NOT NULL DEFAULT 'zh-cn' COMMENT '语言',
  `regip` varchar(45) NOT NULL COMMENT '注册IP',
  `regdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  `secques` char(8) NOT NULL DEFAULT '' COMMENT '安全提问',
  `salt` char(6) NOT NULL DEFAULT '' COMMENT '密码加密因子',
  `authstr` char(30) NOT NULL COMMENT '认证信息',
  `newprompt` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '消息数',
  `timeoffset` char(4) NOT NULL DEFAULT '9999' COMMENT '时区',
  `grid` smallint(6) NOT NULL DEFAULT '0' COMMENT '用户组ID',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `email` (`email`),
  KEY `groupid` (`groupid`),
  KEY `username` (`username`)
) ENGINE=InnoDB COMMENT='用户基本信息表';

DROP TABLE IF EXISTS `dzz_user_field`;
CREATE TABLE `dzz_user_field` (
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
  `applist` text NOT NULL COMMENT '应用布局',
  `noticebanlist` text NOT NULL COMMENT '通知屏蔽列表',
  `iconview` tinyint(1) NOT NULL DEFAULT '2' COMMENT '图标视图',
  `direction` tinyint(1) NOT NULL DEFAULT '0' COMMENT '方向',
  `dateline` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL COMMENT '最后更新时间',
  `attachextensions` varchar(255) NOT NULL DEFAULT '-1' COMMENT '允许上传附件类型，留空表示不限制',
  `maxattachsize` int(10) NOT NULL DEFAULT '-1' COMMENT '最大允许使用空间',
  `usesize` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '已使用空间',
  `addsize` bigint(20) unsigned NOT NULL DEFAULT '0',
  `buysize` bigint(20) unsigned NOT NULL DEFAULT '0',
  `perm` int(10) NOT NULL DEFAULT '0' COMMENT '权限',
  `privacy` text NOT NULL COMMENT '隐私设置',
  `userspace` int(11) NOT NULL DEFAULT '0' COMMENT '用户空间大小，-1表示无空间，0表示不限制',
  UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB COMMENT='用户使用信息表';

DROP TABLE IF EXISTS `dzz_user_profile`;
CREATE TABLE `dzz_user_profile` (
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
  `fieldid` varchar(30) NOT NULL DEFAULT '' COMMENT '字段ID',
  `value` text NOT NULL COMMENT '值',
  `privacy` smallint(3) NOT NULL DEFAULT '0' COMMENT '资料权限',
  PRIMARY KEY (`uid`,`fieldid`)
) ENGINE=InnoDB COMMENT='用户资料表';

DROP TABLE IF EXISTS `dzz_user_profile_setting`;
CREATE TABLE `dzz_user_profile_setting` (
  `fieldid` varchar(255) NOT NULL DEFAULT '' COMMENT '字段ID',
  `available` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否可用',
  `invisible` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `needverify` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要审核',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `displayorder` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `required` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否必填',
  `unchangeable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否可修改',
  `showincard` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否在名片中显示',
  `showinthread` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否在帖子中显示',
  `showinregister` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否在注册中显示',
  `allowsearch` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否允许搜索',
  `formtype` varchar(255) NOT NULL DEFAULT 'text' COMMENT '表单类型',
  `size` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '表单大小',
  `choices` text NOT NULL COMMENT '选项',
  `privacy` smallint(3) NOT NULL DEFAULT '0' COMMENT '资料权限',
  `validate` text NOT NULL COMMENT '验证规则',
  `customable` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否允许自定义',
  PRIMARY KEY (`fieldid`)
) ENGINE=InnoDB COMMENT='用户资料字段表';

DROP TABLE IF EXISTS `dzz_user_setting`;
CREATE TABLE `dzz_user_setting` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `skey` varchar(30) NOT NULL DEFAULT '' COMMENT '用户设置选项键',
  `svalue` text COMMENT '用户设置值',
  PRIMARY KEY (`id`),
  UNIQUE KEY `skey` (`skey`,`uid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB COMMENT='用户设置表';

DROP TABLE IF EXISTS `dzz_user_status`;
CREATE TABLE `dzz_user_status` (
  `uid` int(10) unsigned NOT NULL,
  `regip` varchar(45) NOT NULL DEFAULT '',
  `lastip` varchar(45) NOT NULL DEFAULT '',
  `lastvisit` int(10) unsigned NOT NULL DEFAULT '0',
  `lastactivity` int(10) unsigned NOT NULL DEFAULT '0',
  `lastsendmail` int(10) unsigned NOT NULL DEFAULT '0',
  `invisible` tinyint(1) NOT NULL DEFAULT '0',
  `profileprogress` tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `lastactivity` (`lastactivity`,`invisible`)
) ENGINE=InnoDB COMMENT='用户状态表';

DROP TABLE IF EXISTS `dzz_user_verify`;
CREATE TABLE `dzz_user_verify` (
  `uid` int(10) unsigned NOT NULL,
  `verify1` tinyint(1) NOT NULL DEFAULT '0' COMMENT '-1:已拒绝，0：待审核，1认证通过',
  `verify2` tinyint(1) NOT NULL DEFAULT '0',
  `verify3` tinyint(1) NOT NULL DEFAULT '0',
  `verify4` tinyint(1) NOT NULL DEFAULT '0',
  `verify5` tinyint(1) NOT NULL DEFAULT '0',
  `verify6` tinyint(1) NOT NULL DEFAULT '0',
  `verify7` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `verify1` (`verify1`),
  KEY `verify2` (`verify2`),
  KEY `verify3` (`verify3`),
  KEY `verify4` (`verify4`),
  KEY `verify5` (`verify5`),
  KEY `verify6` (`verify6`),
  KEY `verify7` (`verify7`)
) ENGINE=InnoDB COMMENT='用户认证表';

DROP TABLE IF EXISTS `dzz_user_verify_info`;
CREATE TABLE `dzz_user_verify_info` (
  `vid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `username` varchar(30) NOT NULL DEFAULT '',
  `verifytype` tinyint(1) NOT NULL DEFAULT '0' COMMENT ' 审核类型0:资料审核, 1:认证1, 2:认证2, 3:认证3, 4:认证4, 5:认证5',
  `flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT ' -1:被拒绝 0:待审核 1:审核通过',
  `field` text NOT NULL COMMENT '审核字段',
  `orgid` int(10) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`vid`),
  KEY `verifytype` (`verifytype`,`flag`),
  KEY `uid` (`uid`,`verifytype`,`dateline`)
) ENGINE=InnoDB COMMENT='用户认证信息表';

DROP TABLE IF EXISTS `dzz_user_wechat`;
CREATE TABLE `dzz_user_wechat` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户UID',
  `openid` char(28) NOT NULL DEFAULT '' COMMENT '微信openid',
  `appid` char(18) NOT NULL DEFAULT '' COMMENT '公众号appid',
  `unionid` char(29) NOT NULL DEFAULT '' COMMENT '微信unionid',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  UNIQUE KEY `uid` (`uid`),
  UNIQUE KEY `openid` (`openid`,`appid`)
) ENGINE=InnoDB COMMENT='微信用户信息表';

DROP TABLE IF EXISTS `dzz_usergroup`;
CREATE TABLE `dzz_usergroup` (
  `groupid` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户组id',
  `radminid` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否系统组',
  `type` enum('system','special','member') NOT NULL DEFAULT 'member' COMMENT '用户组类型',
  `system` varchar(255) NOT NULL DEFAULT 'private' COMMENT '系统类型',
  `grouptitle` varchar(255) NOT NULL DEFAULT '' COMMENT '用户组名称',
  `stars` tinyint(3) NOT NULL DEFAULT '0',
  `color` varchar(255) NOT NULL DEFAULT '' COMMENT '颜色',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  `extra` text NOT NULL COMMENT '附加信息',
  PRIMARY KEY (`groupid`)
) ENGINE=InnoDB COMMENT='用户组表';

DROP TABLE IF EXISTS `dzz_usergroup_field`;
CREATE TABLE `dzz_usergroup_field` (
  `groupid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '用户组ID',
  `maxspacesize` int(10) NOT NULL DEFAULT '0' COMMENT '用户组最大空间',
  `attachextensions` varchar(255) NOT NULL COMMENT '用户组允许上传附件类型，留空表示不限制',
  `maxattachsize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户组附件大小',
  `perm` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户组权限',
  UNIQUE KEY `groupid` (`groupid`)
) ENGINE=InnoDB COMMENT='用户组属性表';

DROP TABLE IF EXISTS `dzz_vote`;
CREATE TABLE `dzz_vote` (
  `voteid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '投票类型：0：文字投票；1：图片投票',
  `starttime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `isvisible` tinyint(1) NOT NULL DEFAULT '0' COMMENT '投票结果查看权限，0：所有人可见、1：投票后可见',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态  0：有效 、1：无效、2：结束',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发布者UID',
  `maxselectnum` tinyint(2) NOT NULL DEFAULT '1' COMMENT '最大可选择数',
  `module` varchar(60) NOT NULL DEFAULT '' COMMENT '模块名称',
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '调用者id',
  `idtype` varchar(30) NOT NULL DEFAULT '' COMMENT '调用者id所在的表名',
  `showuser` tinyint(1) NOT NULL DEFAULT '0' COMMENT '显示投票用户',
  PRIMARY KEY (`voteid`)
) ENGINE=InnoDB COMMENT='投票表';

DROP TABLE IF EXISTS `dzz_vote_item`;
CREATE TABLE `dzz_vote_item` (
  `itemid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '投票项id',
  `voteid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '投票id',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '投票项内容',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '投票项类型：1、内容；2、图片',
  `number` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '投票数',
  `aid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图片aid',
  `disp` smallint(6) NOT NULL DEFAULT '0' COMMENT '显示顺序',
  PRIMARY KEY (`itemid`)
) ENGINE=InnoDB COMMENT='投票项表';

DROP TABLE IF EXISTS `dzz_vote_item_count`;
CREATE TABLE `dzz_vote_item_count` (
  `itemid` mediumint(8) unsigned NOT NULL COMMENT '投票项ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UID',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投票时间',
  UNIQUE KEY `itemid` (`itemid`,`uid`)
) ENGINE=InnoDB COMMENT='投票项统计表';

DROP TABLE IF EXISTS `dzz_wx_app`;
CREATE TABLE `dzz_wx_app` (
  `appid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '对应的应用appid',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '应用名称',
  `desc` text NOT NULL COMMENT '应用描述',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '应用图标',
  `agentid` varchar(20) NOT NULL DEFAULT '0' COMMENT '微信agentid',
  `secret` varchar(255) DEFAULT '' COMMENT '应用secret ',
  `host` varchar(255) NOT NULL DEFAULT '' COMMENT '可信域名',
  `callback` varchar(255) NOT NULL DEFAULT '' COMMENT '回调地址',
  `token` varchar(255) NOT NULL DEFAULT '' COMMENT 'token',
  `encodingaeskey` varchar(255) NOT NULL DEFAULT '' COMMENT 'AESKey',
  `menu` text NOT NULL COMMENT '菜单设置',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '应用状态',
  `range` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '可见范围:0：全部，>0为机构orgid',
  `dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `notify` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户状态变更通知',
  `report_msg` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户消息上报',
  `report_location` tinyint(1) NOT NULL DEFAULT '0' COMMENT '上报地理位置',
  `otherpic` varchar(255) NOT NULL,
  PRIMARY KEY (`appid`)
) ENGINE=InnoDB COMMENT='微信应用表';