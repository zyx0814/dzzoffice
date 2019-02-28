DROP TABLE IF EXISTS dzz_admincp_session;
CREATE TABLE dzz_admincp_session (
  uid int(10) unsigned NOT NULL DEFAULT '0',
  adminid smallint(6) unsigned NOT NULL DEFAULT '0',
  panel tinyint(1) NOT NULL DEFAULT '0',
  ip varchar(15) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  errorcount tinyint(1) NOT NULL DEFAULT '0',
  `storage` mediumtext NOT NULL,
  PRIMARY KEY (uid,panel)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_app_market;
CREATE TABLE dzz_app_market (
  `appid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '云端应用ID',
  `appname` varchar(255) NOT NULL,
  `appico` varchar(255) NOT NULL,
  `appdesc` text NOT NULL,
  `appurl` varchar(255) NOT NULL,
  `appadminurl` VARCHAR(255) NULL COMMENT '管理设置地址',
  `noticeurl` varchar(255) NOT NULL DEFAULT '' COMMENT '通知接口地址',
  `dateline` int(10) UNSIGNED NOT NULL,
  `disp` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `vendor` varchar(255) NOT NULL COMMENT '提供商',
  `haveflash` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `isshow` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否显示应用图标',
  `havetask` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否显示任务栏',
  `hideInMarket` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '应用市场里不显示',
  `feature` text NOT NULL COMMENT '窗体feature',
  `fileext` text NOT NULL COMMENT '可以打开的文件类型',
  `group` tinyint(1) NOT NULL DEFAULT '1' COMMENT '应用的分组:0:全部；''-1'':游客可用，''3'':系统管理员可用;''2''：部门管理员可用;''1'':所有成员可用',
  `orgid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '可以使用的部门id，为0表示不限制',
  `position` tinyint(1) NOT NULL DEFAULT '0' COMMENT '2：''desktop'',3：''taskbar'',1：''apparea''',
  `system` tinyint(1) NOT NULL DEFAULT '0',
  `notdelete` tinyint(1) NOT NULL DEFAULT '0',
  `open` tinyint(1) NOT NULL DEFAULT '0',
  `nodup` tinyint(1) NOT NULL DEFAULT '0',
  `identifier` varchar(40) NOT NULL DEFAULT '',
  `app_path` varchar(50) DEFAULT NULL COMMENT 'APP路劲',
  `available` tinyint(1) NOT NULL DEFAULT '1',
  `version` varchar(20) NOT NULL DEFAULT '',
  `upgrade_version` text NOT NULL COMMENT '升级版本',
  `check_upgrade_time` int(11) NOT NULL DEFAULT '0' COMMENT '最近次检测升级时间',
  `extra` text NOT NULL,
  PRIMARY KEY (appid),
  UNIQUE KEY appurl (appurl),
  KEY available (available),
  KEY identifier (identifier)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS dzz_app_open;
CREATE TABLE dzz_app_open (
  ext varchar(255) NOT NULL DEFAULT '',
  appid int(10) unsigned NOT NULL DEFAULT '0',
  disp smallint(6) NOT NULL DEFAULT '0',
  extid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  isdefault tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (extid),
  KEY appid (appid),
  KEY ext (ext,disp)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS dzz_app_open_default;
CREATE TABLE dzz_app_open_default (
  uid int(10) unsigned NOT NULL,
  ext varchar(255) NOT NULL DEFAULT '',
  extid smallint(6) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY defaultext (ext,uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_app_organization;
CREATE TABLE dzz_app_organization (
  appid int(10) unsigned NOT NULL DEFAULT '0',
  orgid int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) NOT NULL DEFAULT '0',
  UNIQUE KEY orgid (appid,orgid),
  KEY appid (appid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_app_pic;
CREATE TABLE dzz_app_pic (
  picid mediumint(8) NOT NULL AUTO_INCREMENT,
  appid int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username varchar(15) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  aid int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (picid),
  KEY uid (uid),
  KEY idtype (appid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_app_relative;
CREATE TABLE dzz_app_relative (
  rid int(10) unsigned NOT NULL AUTO_INCREMENT,
  appid int(10) unsigned NOT NULL DEFAULT '0',
  tagid int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (rid),
  UNIQUE KEY appid (appid,tagid)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS dzz_app_tag;
CREATE TABLE dzz_app_tag (
  tagid int(10) unsigned NOT NULL AUTO_INCREMENT,
  hot int(10) unsigned NOT NULL DEFAULT '0',
  tagname char(15) NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (tagid),
  KEY appid (hot),
  KEY classid (tagname)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS dzz_app_user;
CREATE TABLE dzz_app_user (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  appid int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  lasttime int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  num smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  UNIQUE KEY appuser (appid,uid),
  KEY appid (appid),
  KEY uid (uid)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS dzz_attach;
CREATE TABLE dzz_attach (
  qid int(10) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) NOT NULL DEFAULT '0',
  username char(30) NOT NULL DEFAULT '',
  fid int(10) unsigned NOT NULL DEFAULT '0',
  aid int(10) unsigned NOT NULL DEFAULT '0',
  filename char(80) NOT NULL DEFAULT '',
  area char(15) NOT NULL DEFAULT '',
  areaid int(10) NOT NULL DEFAULT '0',
  reversion smallint(6) NOT NULL DEFAULT '0',
  downloads int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  deletetime int(10) unsigned NOT NULL DEFAULT '0',
  deleteuid int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (qid),
  KEY dateline (dateline),
  KEY tid (fid),
  KEY area (area),
  KEY areaid (areaid,area)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_attachment;
CREATE TABLE dzz_attachment (
  aid int(10) unsigned NOT NULL AUTO_INCREMENT,
  filename char(100) NOT NULL DEFAULT '',
  filetype char(15) NOT NULL DEFAULT '',
  filesize bigint(20) unsigned NOT NULL DEFAULT '0',
  attachment char(60) NOT NULL DEFAULT '',
  remote smallint(6) unsigned NOT NULL DEFAULT '0',
  copys smallint(6) NOT NULL DEFAULT '0',
  md5 char(32) NOT NULL,
  thumb tinyint(1) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  unrun tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (aid),
  KEY dateline (dateline),
  KEY md5 (md5,filesize),
  KEY filetype (filetype),
  KEY unrun (unrun)
) ENGINE=MyISAM ;


DROP TABLE IF EXISTS dzz_cache;
CREATE TABLE dzz_cache (
  cachekey varchar(255) NOT NULL DEFAULT '',
  cachevalue mediumblob NOT NULL,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (cachekey)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_collect;
CREATE TABLE dzz_collect (
  cid int(10) unsigned NOT NULL AUTO_INCREMENT,
  ourl varchar(255) NOT NULL DEFAULT '',
  `data` text NOT NULL,
  dateline int(10) unsigned NOT NULL,
  copys int(10) unsigned NOT NULL,
  `type` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (cid)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS dzz_comment;
CREATE TABLE dzz_comment (
  cid int(10) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) unsigned NOT NULL DEFAULT '0',
  pcid int(10) unsigned NOT NULL DEFAULT '0',
  rcid int(10) unsigned NOT NULL DEFAULT '0',
  id varchar(60) NOT NULL DEFAULT '',
  idtype varchar(20) NOT NULL DEFAULT '',
  module varchar(50) NOT NULL DEFAULT '' COMMENT '调用的模块名，通常为应用的目录',
  authorid int(10) unsigned NOT NULL DEFAULT '0',
  author varchar(15) NOT NULL DEFAULT '',
  ip varchar(20) NOT NULL DEFAULT '',
  `port` smallint(6) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  message text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  edituid int(10) unsigned NOT NULL DEFAULT '0',
  edittime int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (cid),
  KEY authorid (authorid,idtype),
  KEY id (id,idtype,dateline)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_comment_at;
CREATE TABLE dzz_comment_at (
  cid int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL,
  UNIQUE KEY pid_uid (cid,uid),
  KEY dateline (dateline)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_comment_attach;
CREATE TABLE dzz_comment_attach (
  qid int(10) unsigned NOT NULL AUTO_INCREMENT,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  cid int(10) unsigned NOT NULL DEFAULT '0',
  aid int(10) unsigned NOT NULL DEFAULT '0',
  title varchar(255) NOT NULL,
  downloads int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(30) NOT NULL DEFAULT '',
  img varchar(255) NOT NULL,
  url varchar(255) NOT NULL,
  ext varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (qid),
  KEY dateline (dateline),
  KEY tid (cid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_connect;
CREATE TABLE dzz_connect (
  `name` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL DEFAULT '',
  secret varchar(255) NOT NULL DEFAULT '',
  `type` varchar(255) NOT NULL COMMENT 'pan,mail,storage,web',
  bz varchar(255) NOT NULL COMMENT 'Dropbox,Box,Google,Aliyun,Grandcloud',
  root varchar(255) NOT NULL DEFAULT '',
  available tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否可用',
  dname varchar(255) NOT NULL COMMENT '数据库名称',
  curl varchar(255) NOT NULL COMMENT '授权地址',
  disp smallint(6) NOT NULL DEFAULT '0',
  UNIQUE KEY bz (bz),
  KEY disp (disp)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_connect_disk;
CREATE TABLE dzz_connect_disk (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  cloudname varchar(255) NOT NULL DEFAULT '',
  attachdir varchar(255) NOT NULL DEFAULT '' COMMENT '绝对位置',
  attachurl varchar(255) NOT NULL DEFAULT '' COMMENT '访问地址（选填）',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  perm smallint(6) unsigned NOT NULL DEFAULT '0',
  bz varchar(10) NOT NULL DEFAULT 'DISK',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  `charset` varchar(30) NOT NULL DEFAULT 'GBK',
  PRIMARY KEY (id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_connect_ftp;
CREATE TABLE dzz_connect_ftp (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  `host` varchar(255) NOT NULL DEFAULT '',
  cloudname varchar(255) NOT NULL DEFAULT '',
  username varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `port` smallint(6) NOT NULL DEFAULT '0',
  timeout smallint(6) NOT NULL DEFAULT '90',
  `ssl` tinyint(1) NOT NULL DEFAULT '0',
  pasv tinyint(1) NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  perm smallint(6) unsigned NOT NULL DEFAULT '0',
  bz varchar(10) NOT NULL DEFAULT 'FTP',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  `on` tinyint(1) NOT NULL DEFAULT '1',
  `charset` varchar(30) NOT NULL DEFAULT 'GBK',
  PRIMARY KEY (id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_connect_onedrive;
CREATE TABLE dzz_connect_onedrive (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  bz varchar(30) NOT NULL,
  cloudname varchar(255) NOT NULL DEFAULT '',
  cuid char(60) NOT NULL DEFAULT '',
  cusername varchar(255) NOT NULL,
  uid int(10) unsigned NOT NULL,
  expires_in int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  refreshtime int(10) unsigned NOT NULL DEFAULT '0',
  perm smallint(6) unsigned NOT NULL DEFAULT '29751',
  refresh_token text NOT NULL,
  access_token text NOT NULL,
  scope varchar(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY cuid (cuid,uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_connect_pan;
CREATE TABLE dzz_connect_pan (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  bz varchar(255) NOT NULL,
  cloudname varchar(255) NOT NULL DEFAULT '',
  cuid int(10) unsigned NOT NULL DEFAULT '0',
  cusername varchar(255) NOT NULL,
  portrait varchar(255) NOT NULL DEFAULT '',
  uid int(10) unsigned NOT NULL,
  expires_in int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  refreshtime int(10) unsigned NOT NULL DEFAULT '0',
  perm smallint(6) unsigned NOT NULL DEFAULT '29751',
  refresh_token varchar(255) NOT NULL,
  access_token varchar(255) NOT NULL,
  scope varchar(255) NOT NULL,
  session_key varchar(255) NOT NULL,
  session_secret varchar(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY cuid (cuid,uid,bz)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_connect_storage;
CREATE TABLE dzz_connect_storage (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) unsigned NOT NULL,
  cloudname varchar(255) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  perm smallint(6) unsigned NOT NULL DEFAULT '29751',
  access_id varchar(255) NOT NULL,
  access_key varchar(255) NOT NULL,
  bucket char(30) NOT NULL DEFAULT '',
  bz varchar(30) NOT NULL DEFAULT '',
  hostname varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_cron;
CREATE TABLE dzz_cron (
  cronid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  available tinyint(1) NOT NULL DEFAULT '0',
  `type` enum('user','system','app') NOT NULL DEFAULT 'user',
  `name` char(50) NOT NULL DEFAULT '',
  filename char(50) NOT NULL DEFAULT '',
  lastrun int(10) unsigned NOT NULL DEFAULT '0',
  nextrun int(10) unsigned NOT NULL DEFAULT '0',
  weekday tinyint(1) NOT NULL DEFAULT '0',
  `day` tinyint(2) NOT NULL DEFAULT '0',
  `hour` tinyint(2) NOT NULL DEFAULT '0',
  `minute` char(36) NOT NULL DEFAULT '',
  PRIMARY KEY (cronid),
  KEY nextrun (available,nextrun)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS dzz_district;
CREATE TABLE dzz_district (
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `level` tinyint(4) unsigned NOT NULL DEFAULT '0',
  usetype tinyint(1) unsigned NOT NULL DEFAULT '0',
  upid mediumint(8) unsigned NOT NULL DEFAULT '0',
  displayorder smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY upid (upid,displayorder)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS dzz_document;
CREATE TABLE dzz_document (
  did int(10) unsigned NOT NULL AUTO_INCREMENT,
  tid int(10) unsigned NOT NULL,
  fid smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '文库分类id',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username char(30) NOT NULL DEFAULT '',
  area char(15) NOT NULL DEFAULT '',
  areaid int(10) unsigned NOT NULL DEFAULT '0',
  aid int(10) unsigned NOT NULL DEFAULT '0',
  version smallint(6) unsigned NOT NULL DEFAULT '0',
  isdelete tinyint(10) unsigned NOT NULL DEFAULT '0',
  disp smallint(6) NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (did),
  KEY dateline (dateline),
  KEY disp (disp),
  KEY fid (fid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_document_event;
CREATE TABLE dzz_document_event (
  eid int(10) unsigned NOT NULL AUTO_INCREMENT,
  did int(10) unsigned NOT NULL DEFAULT '0',
  `action` char(15) NOT NULL DEFAULT '',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username char(30) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (eid),
  KEY did (did,uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_document_reversion;
CREATE TABLE dzz_document_reversion (
  did int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  aid int(10) unsigned NOT NULL DEFAULT '0',
  revid int(10) NOT NULL AUTO_INCREMENT,
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username varchar(30) NOT NULL DEFAULT '',
  version smallint(6) unsigned NOT NULL DEFAULT '0',
  attachs text NOT NULL,
  PRIMARY KEY (revid),
  KEY dateline (dateline),
  KEY qid (did),
  KEY uid (uid),
  KEY username (username)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_failedlogin;
CREATE TABLE dzz_failedlogin (
  ip char(15) NOT NULL DEFAULT '',
  username char(32) NOT NULL DEFAULT '',
  count tinyint(1) unsigned NOT NULL DEFAULT '0',
  lastupdate int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (ip,username)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS dzz_folder;
CREATE TABLE dzz_folder (
  fid int(10) unsigned NOT NULL AUTO_INCREMENT,
  pfid int(11) NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username char(30) NOT NULL DEFAULT '',
  innav tinyint(1) NOT NULL DEFAULT '1',
  fname char(50) NOT NULL DEFAULT '',
  perm int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0:继承；1：只读；2：可写',
  perm_inherit int(10) NOT NULL DEFAULT '0',
  fsperm int(10) unsigned NOT NULL DEFAULT '0',
  disp smallint(6) NOT NULL DEFAULT '0',
  iconview tinyint(1) NOT NULL DEFAULT '1',
  display smallint(6) NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  gid int(10) unsigned NOT NULL DEFAULT '0',
  flag char(15) NOT NULL DEFAULT 'folder',
  `default` char(15) NOT NULL DEFAULT '',
  isdelete tinyint(1) NOT NULL DEFAULT '0',
  deldateline int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (fid),
  KEY uid (uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_folder_attr;
CREATE TABLE dzz_folder_attr (
  id int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  fid int(11) unsigned NOT NULL DEFAULT '0' COMMENT '文件夹id',
  skey varchar(30) NOT NULL DEFAULT '',
  svalue text NOT NULL,
  PRIMARY KEY (id),
  KEY fid (fid),
  KEY skey (skey)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS dzz_folder_sub;
CREATE TABLE dzz_folder_sub (
  `subid` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `pflag` varchar(30) NOT NULL DEFAULT '' COMMENT '所属目录标识符',
  `fname` char(50) NOT NULL DEFAULT '' COMMENT '目录名称',
  `flag` varchar(30) NOT NULL DEFAULT 'folder' COMMENT '目录标识符',
  `fsperm` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '目录超级权限',
  `perm` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '目录权限',
  `allow_exts` text NOT NULL COMMENT '允许的文件类型，使用英文逗号隔开',
  `disp` smallint(6) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`subid`),
  KEY `pflag` (`pflag`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_folder_flag;
CREATE TABLE dzz_folder_flag (
  `flag` char(30) NOT NULL DEFAULT '' COMMENT '目录标识符',
  `fsperm` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '目录默认超级权限',
  `perm` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '目录默认权限',
  `iconview` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '目录默认排列方式：4：列表：1：图标',
  `disp` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '目录默认排序字段：0:name;1:size:2:type:3:dateline',
  PRIMARY KEY (`flag`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS dzz_folder_default;
CREATE TABLE dzz_folder_default (
  fid int(10) unsigned NOT NULL AUTO_INCREMENT,
  innav tinyint(1) NOT NULL DEFAULT '1',
  fname varchar(255) NOT NULL DEFAULT '',
  perm smallint(6) unsigned NOT NULL DEFAULT '0',
  disp smallint(6) NOT NULL DEFAULT '0',
  iconview tinyint(1) NOT NULL DEFAULT '0',
  display smallint(6) NOT NULL DEFAULT '10',
  flag varchar(255) NOT NULL DEFAULT 'folder',
  `default` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (fid),
  UNIQUE KEY `type` (flag)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS dzz_folder_event;
CREATE TABLE dzz_folder_event (
  eid int(10) unsigned NOT NULL AUTO_INCREMENT,
  fid int(10) unsigned NOT NULL DEFAULT '0',
  gid int(10) unsigned NOT NULL DEFAULT '0',
  `action` varchar(60) NOT NULL DEFAULT '',
  body_template text NOT NULL,
  body_data text NOT NULL,
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username varchar(30) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (eid),
  KEY fid (fid,dateline)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_folder_perm;
CREATE TABLE dzz_folder_perm (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  permname varchar(60) NOT NULL DEFAULT '',
  perm int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS dzz_icon;
CREATE TABLE dzz_icon (
  did int(10) unsigned NOT NULL AUTO_INCREMENT,
  domain varchar(255) NOT NULL,
  reg varchar(255) NOT NULL DEFAULT '' COMMENT '匹配正则表达式',
  ext varchar(30) NOT NULL DEFAULT '',
  pic varchar(255) NOT NULL,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  `check` tinyint(1) unsigned NOT NULL DEFAULT '1',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username varchar(255) NOT NULL,
  copys int(10) NOT NULL DEFAULT '0',
  disp smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (did),
  KEY domain (domain),
  KEY uid (uid),
  KEY copys (copys),
  KEY dateline (dateline)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS dzz_iconview;
CREATE TABLE dzz_iconview (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  width smallint(6) unsigned NOT NULL DEFAULT '64',
  height smallint(6) unsigned NOT NULL DEFAULT '64',
  divwidth smallint(6) unsigned NOT NULL DEFAULT '100',
  divheight smallint(6) unsigned NOT NULL DEFAULT '100',
  paddingtop smallint(6) unsigned NOT NULL DEFAULT '0',
  paddingleft smallint(6) unsigned NOT NULL DEFAULT '0',
  textlength smallint(6) unsigned NOT NULL DEFAULT '30',
  align tinyint(1) unsigned NOT NULL DEFAULT '0',
  avaliable tinyint(1) unsigned NOT NULL DEFAULT '1',
  disp smallint(6) unsigned NOT NULL DEFAULT '0',
  cssname varchar(60) NOT NULL,
  PRIMARY KEY (id),
  KEY avaliable (avaliable,disp)
) ENGINE=MyISAM ;



DROP TABLE IF EXISTS dzz_imagetype;
CREATE TABLE dzz_imagetype (
  typeid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  available tinyint(1) NOT NULL DEFAULT '0',
  `name` char(20) NOT NULL,
  `type` enum('smiley','icon','avatar') NOT NULL DEFAULT 'smiley',
  displayorder tinyint(3) NOT NULL DEFAULT '0',
  `directory` char(100) NOT NULL,
  PRIMARY KEY (typeid)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS dzz_local_router;
CREATE TABLE dzz_local_router (
  routerid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(60) NOT NULL DEFAULT '',
  remoteid smallint(6) unsigned NOT NULL DEFAULT '0',
  router text NOT NULL,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  available tinyint(1) NOT NULL DEFAULT '0',
  priority smallint(6) unsigned NOT NULL DEFAULT '100',
  PRIMARY KEY (routerid),
  KEY priority (priority)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_local_storage;
CREATE TABLE dzz_local_storage (
  remoteid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  bz varchar(255) NOT NULL COMMENT 'Dropbox,Box,Google,Aliyun,Grandcloud',
  isdefault tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '默认',
  dname varchar(255) NOT NULL COMMENT '数据库名称',
  did int(10) unsigned NOT NULL DEFAULT '0',
  disp smallint(6) NOT NULL DEFAULT '0',
  usesize bigint(20) unsigned NOT NULL DEFAULT '0',
  totalsize bigint(20) unsigned NOT NULL DEFAULT '0',
  lastupdate int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (remoteid),
  KEY disp (disp)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS dzz_mailcron;
CREATE TABLE dzz_mailcron (
  cid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  touid int(10) unsigned NOT NULL DEFAULT '0',
  email varchar(100) NOT NULL DEFAULT '',
  sendtime int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (cid),
  KEY sendtime (sendtime)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS dzz_mailqueue;
CREATE TABLE dzz_mailqueue (
  qid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  cid mediumint(8) unsigned NOT NULL DEFAULT '0',
  `subject` text NOT NULL,
  message text NOT NULL,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (qid),
  KEY mcid (cid,dateline)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS dzz_notification;
CREATE TABLE dzz_notification (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(60) NOT NULL DEFAULT '',
  `new` tinyint(1) NOT NULL DEFAULT '0',
  authorid int(10) unsigned NOT NULL DEFAULT '0',
  author varchar(30) NOT NULL DEFAULT '',
  note text NOT NULL,
  wx_note text NOT NULL,
  wx_new tinyint(1) NOT NULL DEFAULT '1',
  redirecturl varchar(255) NOT NULL DEFAULT '' COMMENT '跳转地址',
  title varchar(255) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  from_id int(10) unsigned NOT NULL DEFAULT '0',
  from_idtype varchar(20) NOT NULL DEFAULT '',
  from_num mediumint(8) unsigned NOT NULL DEFAULT '0',
  category tinyint(1) NOT NULL DEFAULT '0' COMMENT ' 提醒分类 1系统消息 0应用消息',
  PRIMARY KEY (id),
  KEY uid (uid,`new`),
  KEY category (uid,dateline),
  KEY by_type (uid,`type`,dateline),
  KEY from_id (from_id,from_idtype)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_onlinetime;
CREATE TABLE dzz_onlinetime (
  uid int(10) unsigned NOT NULL DEFAULT '0',
  thismonth smallint(6) unsigned NOT NULL DEFAULT '0',
  total mediumint(8) unsigned NOT NULL DEFAULT '0',
  lastupdate int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_organization;
CREATE TABLE dzz_organization (
  orgid int(10) unsigned NOT NULL AUTO_INCREMENT,
  orgname varchar(255) NOT NULL DEFAULT '',
  forgid int(10) unsigned NOT NULL DEFAULT '0',
  worgid int(10) unsigned NOT NULL DEFAULT '0',
  fid int(10) unsigned NOT NULL DEFAULT '0',
  disp smallint(6) NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  usesize bigint(20) unsigned NOT NULL DEFAULT '0',
  maxspacesize bigint(20) NOT NULL DEFAULT '0' COMMENT '0：不限制，-1表示無空間',
  indesk tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否创建快捷方式',
  available tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  pathkey varchar(255) NOT NULL DEFAULT '',
  `type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0一般机构，1群组机构',
  `desc` varchar(200) NOT NULL DEFAULT '' COMMENT '群组描述',
  groupback int(11) unsigned NOT NULL COMMENT '群组背景图',
  aid varchar(30) NOT NULL default '' COMMENT '群组缩略图,可以是aid,也可以是颜色值',
  manageon tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '群組管理员开启关闭0关闭，1开启',
  syatemon tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '系统管理员开启群组，关闭群组，0关闭，1开启',
  diron tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '群组管理员共享目录开启，0关闭，1开启',
  extraspace int(11) unsigned NOT NULL DEFAULT '0' COMMENT '机构群组额外空间大小',
  buyspace int(11) unsigned NOT NULL DEFAULT '0' COMMENT '购买空间',
  `allotspace` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分配空间大小',
  PRIMARY KEY (orgid),
  KEY disp (disp),
  KEY pathkey (pathkey),
  KEY dateline (dateline)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_organization_admin;
CREATE TABLE dzz_organization_admin (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  orgid int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  opuid int(10) unsigned NOT NULL DEFAULT '0',
  admintype tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0管理员，1群组创始人',
  PRIMARY KEY (id),
  UNIQUE KEY orgid (orgid,uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_organization_job;
CREATE TABLE dzz_organization_job (
  jobid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  orgid int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(30) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  opuid int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (jobid),
  KEY orgid (orgid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_organization_upjob;
CREATE TABLE dzz_organization_upjob (
  id smallint(6) NOT NULL AUTO_INCREMENT,
  jobid smallint(6) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) NOT NULL DEFAULT '0',
  opuid int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  UNIQUE KEY uid (uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_organization_user;
CREATE TABLE dzz_organization_user (
  orgid int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  jobid smallint(6) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY orgid (orgid,uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_process;
CREATE TABLE dzz_process (
  processid char(32) NOT NULL,
  expiry int(10) DEFAULT NULL,
  extra int(10) DEFAULT NULL,
  PRIMARY KEY (processid),
  KEY expiry (expiry)
) ENGINE=MEMORY;

DROP TABLE IF EXISTS dzz_resources;
CREATE TABLE dzz_resources (
  rid char(32) NOT NULL COMMENT '主键',
  vid int(10) unsigned NOT NULL DEFAULT '0' COMMENT '版本id',
  oid int(10) unsigned DEFAULT '0',
  uid int(10) unsigned NOT NULL,
  username char(30) NOT NULL DEFAULT '' COMMENT '用戶名',
  pfid int(11)  NOT NULL DEFAULT '0' COMMENT '上級id',
  gid int(10) unsigned NOT NULL DEFAULT '0' COMMENT '群组id',
  `name` char(80) NOT NULL DEFAULT '' COMMENT '名称',
  `type` char(15) NOT NULL DEFAULT '' COMMENT '类型',
  dateline int(10) unsigned NOT NULL DEFAULT '0' COMMENT '时间',
  ext char(15) NOT NULL DEFAULT '' COMMENT '后缀',
  size bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '大小',
  sperm int(10) unsigned NOT NULL DEFAULT '0' COMMENT '权限',
  isdelete tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否被删除',
  deldateline int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  flag char(15) NOT NULL DEFAULT '' COMMENT '标志',
  PRIMARY KEY (rid),
  KEY gid (gid),
  KEY pfid (pfid),
  KEY uid (uid),
  KEY isdelete (isdelete)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_resources_attr;
CREATE TABLE dzz_resources_attr (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  rid char(32) NOT NULL DEFAULT '0' COMMENT '资源id',
  skey varchar(30) NOT NULL,
  sval text NOT NULL COMMENT '属性值',
  vid int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `rid_skey_vid` (`rid`,`skey`,`vid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_resources_cat;
CREATE TABLE dzz_resources_cat (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  catname varchar(30) NOT NULL DEFAULT '',
  ext text NOT NULL,
  tag text NOT NULL,
  keywords text NOT NULL,
  `default` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1,系統默認；0，非系统默认',
  iconview tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dzz_resources_clipboard;
CREATE TABLE dzz_resources_clipboard (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL,
  `username` varchar(120) NOT NULL,
  `dateline` int(11) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1文件，2文本',
  `files` text NOT NULL,
  `copytype` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1复制，2粘贴',
  `bz` varchar(10) NOT NULL DEFAULT '' COMMENT 'bz',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_resources_collect;
CREATE TABLE dzz_resources_collect (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  rid char(32) NOT NULL DEFAULT '',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  path varchar(120) NOT NULL DEFAULT '',
  username char(30) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  size bigint(20) unsigned NOT NULL DEFAULT '0',
  filename varchar(60) NOT NULL DEFAULT '',
  iconview tinyint(1) NOT NULL DEFAULT '1' COMMENT '展示方式',
  disp smallint(6) NOT NULL DEFAULT '0',
  pfid int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_resources_event;
CREATE TABLE dzz_resources_event (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  rid char(32) NOT NULL DEFAULT '',
  gid int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL,
  do_obj varchar(60) NOT NULL DEFAULT '',
  username char(30) NOT NULL DEFAULT '' COMMENT '用户名',
  event_body varchar(60) NOT NULL DEFAULT '' COMMENT '事体',
  body_data text NOT NULL,
  dateline int(10) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0操作，1评论',
  pfid int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级id',
  `do` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY rid (rid),
  KEY uid (uid),
  KEY pfid (pfid),
  KEY `do` (`do`),
  KEY dateline (dateline)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_resources_path;
CREATE TABLE dzz_resources_path (
  fid int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件夹id',
  path varchar(1000) NOT NULL DEFAULT '' COMMENT '路径',
  pathkey varchar(255) NOT NULL DEFAULT '' COMMENT '路径层次关系',
  PRIMARY KEY (fid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_resources_permgroup;
CREATE TABLE dzz_resources_permgroup (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pername` varchar(50) NOT NULL DEFAULT '',
  `perm` int(10) unsigned NOT NULL,
  `off` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `default` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1系统默认权限组，不允许删除;0用户自定义权限组',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_resources_recyle;
CREATE TABLE dzz_resources_recyle (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  rid char(32) NOT NULL DEFAULT '',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username char(30) NOT NULL DEFAULT '',
  gid int(10) NOT NULL DEFAULT '0',
  filename varchar(60) NOT NULL DEFAULT '',
  size bigint(20) NOT NULL DEFAULT '0',
  cid int(10) NOT NULL DEFAULT '0',
  pfid int(10) unsigned NOT NULL DEFAULT '0',
  deldateline int(11) unsigned NOT NULL,
  pathinfo varchar(1000) NOT NULL DEFAULT '' COMMENT '文件路径',
  PRIMARY KEY (id),
  KEY uid (uid),
  KEY gid (gid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_resources_statis;
CREATE TABLE dzz_resources_statis (
  id int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  rid char(32) NOT NULL DEFAULT '',
  edits int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改次数',
  views int(10) unsigned NOT NULL DEFAULT '0' COMMENT '浏览次数',
  downs int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下载次数',
  fid int(10) unsigned NOT NULL COMMENT '文件夹id',
  pfid int(10) unsigned NOT NULL COMMENT '所属目录',
  uid int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  opendateline int(11) unsigned NOT NULL DEFAULT '0' COMMENT '打开时间',
  editdateline int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (id),
  KEY rid (rid),
  KEY fid (fid),
  KEY pfid (pfid,uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_resources_tag;
CREATE TABLE dzz_resources_tag (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  rid char(32) NOT NULL DEFAULT '',
  tid int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_resources_version;
CREATE TABLE dzz_resources_version (
  `vid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '版本id',
  `rid` char(32) NOT NULL DEFAULT '' COMMENT '资源id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` char(30) NOT NULL DEFAULT '' COMMENT '用户名',
  `vname` char(30) NOT NULL DEFAULT '' COMMENT '版本名称',
  `vdesc` varchar(120) NOT NULL DEFAULT '' COMMENT '版本描述',
  `aid` varchar(255) NOT NULL DEFAULT '' COMMENT '文件路径',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '版本文件大小',
  `ext` char(15) NOT NULL DEFAULT '',
  `type` char(15) NOT NULL DEFAULT '' COMMENT '类型',
  `dateline` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '时间',
  PRIMARY KEY (`vid`)
) ENGINE=MyISAM;



DROP TABLE IF EXISTS dzz_session;
CREATE TABLE dzz_session (
  sid char(6) NOT NULL DEFAULT '',
  ip1 tinyint(3) unsigned NOT NULL DEFAULT '0',
  ip2 tinyint(3) unsigned NOT NULL DEFAULT '0',
  ip3 tinyint(3) unsigned NOT NULL DEFAULT '0',
  ip4 tinyint(3) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username char(15) NOT NULL DEFAULT '',
  groupid smallint(6) unsigned NOT NULL DEFAULT '0',
  invisible tinyint(1) NOT NULL DEFAULT '0',
  `action` tinyint(1) unsigned NOT NULL DEFAULT '0',
  lastactivity int(10) unsigned NOT NULL DEFAULT '0',
  lastolupdate int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY sid (sid),
  KEY uid (uid)
) ENGINE=MEMORY;

DROP TABLE IF EXISTS dzz_setting;
CREATE TABLE dzz_setting (
  skey varchar(255) NOT NULL DEFAULT '',
  svalue text NOT NULL,
  PRIMARY KEY (skey)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS dzz_shares;
CREATE TABLE dzz_shares (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(120) NOT NULL DEFAULT '' COMMENT '分享标题',
  filepath text NOT NULL COMMENT '文件',
  dateline int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分享时间',
  times smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '分享总次数，为0则为不限制',
  endtime int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分享到期时间，0为永久有效',
  username varchar(60) NOT NULL DEFAULT '' COMMENT '分享人',
  uid int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分享用户id',
  gid int(11) unsigned NOT NULL DEFAULT '0' COMMENT '群组id',
  pfid int(11) NOT NULL DEFAULT '0',
  `password` varchar(256) NOT NULL DEFAULT '' COMMENT '分享密码，留空为公开分享',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '-4：文件不存在；-3：次数到；-1：已过期；0：正常',
  private tinyint(1) unsigned NOT NULL DEFAULT '1',
  count smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '分享使用次数',
  downs int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下载次数',
  views int(10) unsigned NOT NULL DEFAULT '0' COMMENT '浏览次数',
  `type` char(15) NOT NULL DEFAULT '' COMMENT '文件类型',
  PRIMARY KEY (id),
  KEY gid (gid),
  KEY uid (uid),
  KEY pfid (pfid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_shorturl;
CREATE TABLE dzz_shorturl (
  sid char(10) NOT NULL,
  url text NOT NULL,
  count int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (sid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_smiley;
CREATE TABLE dzz_smiley (
  typeid smallint(6) unsigned NOT NULL,
  displayorder tinyint(1) NOT NULL DEFAULT '0',
  `type` enum('smiley','stamp','stamplist') NOT NULL DEFAULT 'smiley',
  `code` varchar(30) NOT NULL DEFAULT '',
  url varchar(30) NOT NULL DEFAULT '',
  title varchar(30) NOT NULL DEFAULT '',
  id smallint(6) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (id),
  KEY `type` (`type`,displayorder)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS dzz_syscache;
CREATE TABLE dzz_syscache (
  cname varchar(32) NOT NULL,
  ctype tinyint(3) unsigned NOT NULL,
  dateline int(10) unsigned NOT NULL,
  `data` mediumblob NOT NULL,
  PRIMARY KEY (cname)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_tag;
CREATE TABLE dzz_tag (
  tid int(10) unsigned NOT NULL AUTO_INCREMENT,
  tagname varchar(60) NOT NULL DEFAULT '',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username varchar(60) NOT NULL,
  idtype varchar(60) NOT NULL,
  hot int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用热度',
  PRIMARY KEY (tid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_thame;
CREATE TABLE dzz_thame (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  folder varchar(255) NOT NULL DEFAULT 'window_jd',
  backimg varchar(255) NOT NULL,
  thumb varchar(255) NOT NULL,
  btype tinyint(1) NOT NULL DEFAULT '1',
  url varchar(255) NOT NULL,
  `default` tinyint(1) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  disp smallint(6) unsigned NOT NULL DEFAULT '0',
  modules text NOT NULL,
  color varchar(255) NOT NULL DEFAULT '',
  enable_color tinyint(1) NOT NULL DEFAULT '0',
  vendor varchar(255) NOT NULL,
  version varchar(15) NOT NULL DEFAULT '1.0',
  PRIMARY KEY (id),
  KEY disp (disp)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS dzz_user;
CREATE TABLE dzz_user (
  uid int(10) unsigned NOT NULL AUTO_INCREMENT,
  email char(40) NOT NULL DEFAULT '',
  phone varchar(255) NOT NULL DEFAULT '',
  weixinid varchar(255) NOT NULL DEFAULT '' COMMENT '微信号',
  wechat_userid varchar(255) NOT NULL DEFAULT '',
  wechat_status tinyint(1) NOT NULL DEFAULT '4' COMMENT '1:已关注；2：已冻结；4：未关注',
  nickname char(30) NOT NULL DEFAULT '',
  username char(30) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` char(32) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  phonestatus tinyint(1) NOT NULL DEFAULT '0' COMMENT '手机绑定状态',
  emailsenddate varchar(50) NOT NULL DEFAULT '0',
  emailstatus tinyint(1) NOT NULL DEFAULT '0' COMMENT '邮箱绑定状态',
  avatarstatus tinyint(1) NOT NULL DEFAULT '0',
  adminid tinyint(1) NOT NULL DEFAULT '0',
  groupid smallint(6) unsigned NOT NULL DEFAULT '9',
  `language` varchar(12) NOT NULL DEFAULT 'zh-cn' COMMENT '语言',
  regip char(15) NOT NULL,
  regdate int(10) unsigned NOT NULL DEFAULT '0',
  secques char(8) NOT NULL DEFAULT '',
  salt char(6) NOT NULL DEFAULT '',
  authstr char(30) NOT NULL,
  newprompt smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '消息数',
  timeoffset char(4) NOT NULL DEFAULT '9999',
  grid smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (uid),
  UNIQUE KEY email (email),
  KEY groupid (groupid),
  KEY username (username)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS dzz_user_setting;
CREATE TABLE dzz_user_setting (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `skey` varchar(30) NOT NULL DEFAULT '' COMMENT '用户设置选项键',
  `svalue` text COMMENT '用户设置值',
  PRIMARY KEY (`id`),
  UNIQUE KEY `skey` (`skey`,`uid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_usergroup;
CREATE TABLE dzz_usergroup (
  groupid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  radminid tinyint(3) NOT NULL DEFAULT '0',
  `type` enum('system','special','member') NOT NULL DEFAULT 'member',
  `system` varchar(255) NOT NULL DEFAULT 'private',
  grouptitle varchar(255) NOT NULL DEFAULT '',
  creditshigher int(10) NOT NULL DEFAULT '0',
  creditslower int(10) NOT NULL DEFAULT '0',
  stars tinyint(3) NOT NULL DEFAULT '0',
  color varchar(255) NOT NULL DEFAULT '',
  icon varchar(255) NOT NULL DEFAULT '',
  allowvisit tinyint(1) NOT NULL DEFAULT '0',
  allowsendpm tinyint(1) NOT NULL DEFAULT '1',
  allowinvite tinyint(1) NOT NULL DEFAULT '0',
  allowmailinvite tinyint(1) NOT NULL DEFAULT '0',
  maxinvitenum tinyint(3) unsigned NOT NULL DEFAULT '0',
  inviteprice smallint(6) unsigned NOT NULL DEFAULT '0',
  maxinviteday smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (groupid),
  KEY creditsrange (creditshigher,creditslower)
) ENGINE=MyISAM ;

DROP TABLE IF EXISTS dzz_usergroup_field;
CREATE TABLE dzz_usergroup_field (
  groupid smallint(6) unsigned NOT NULL DEFAULT '0',
  maxspacesize int(10) NOT NULL DEFAULT '0',
  attachextensions varchar(255) NOT NULL,
  maxattachsize int(10) unsigned NOT NULL DEFAULT '0',
  perm int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY groupid (groupid)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS dzz_user_field;
CREATE TABLE dzz_user_field (
  uid int(10) unsigned NOT NULL,
  docklist text NOT NULL,
  screenlist text NOT NULL,
  applist text NOT NULL,
  noticebanlist text NOT NULL,
  iconview tinyint(1) NOT NULL DEFAULT '2',
  iconposition tinyint(1) NOT NULL DEFAULT '0',
  direction tinyint(1) NOT NULL DEFAULT '0',
  autolist tinyint(1) NOT NULL DEFAULT '1',
  taskbar enum('bottom','left','top','right') NOT NULL DEFAULT 'bottom',
  dateline int(10) unsigned NOT NULL,
  updatetime int(10) unsigned NOT NULL,
  attachextensions varchar(255) NOT NULL DEFAULT '-1',
  maxattachsize int(10) NOT NULL DEFAULT '-1',
  usesize bigint(20) unsigned NOT NULL DEFAULT '0',
  addsize bigint(20) unsigned NOT NULL DEFAULT '0',
  buysize bigint(20) unsigned NOT NULL DEFAULT '0',
  wins text NOT NULL,
  perm int(10) NOT NULL DEFAULT '0',
  privacy text NOT NULL,
  userspace int(11) NOT NULL DEFAULT '0' COMMENT '用户空间大小，-1表示无空间，0表示不限制',
  UNIQUE KEY uid (uid)
) ENGINE=MyISAM;



DROP TABLE IF EXISTS dzz_user_profile;
CREATE TABLE dzz_user_profile (
  uid int(10) unsigned NOT NULL,
  fieldid varchar(30) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  privacy smallint(3) NOT NULL DEFAULT '0' COMMENT '资料权限',
  PRIMARY KEY (uid,fieldid)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS dzz_user_profile_setting;
CREATE TABLE dzz_user_profile_setting (
  fieldid varchar(255) NOT NULL DEFAULT '',
  available tinyint(1) NOT NULL DEFAULT '0',
  invisible tinyint(1) NOT NULL DEFAULT '0',
  needverify tinyint(1) NOT NULL DEFAULT '0',
  title varchar(255) NOT NULL DEFAULT '',
  description varchar(255) NOT NULL DEFAULT '',
  displayorder smallint(6) unsigned NOT NULL DEFAULT '0',
  required tinyint(1) NOT NULL DEFAULT '0',
  unchangeable tinyint(1) NOT NULL DEFAULT '0',
  showincard tinyint(1) NOT NULL DEFAULT '0',
  showinthread tinyint(1) NOT NULL DEFAULT '0',
  showinregister tinyint(1) NOT NULL DEFAULT '0',
  allowsearch tinyint(1) NOT NULL DEFAULT '0',
  formtype varchar(255) NOT NULL DEFAULT 'text',
  size smallint(6) unsigned NOT NULL DEFAULT '0',
  choices text NOT NULL,
  privacy smallint(3) NOT NULL DEFAULT '0' COMMENT '资料权限',
  validate text NOT NULL,
  customable tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (fieldid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_user_status;
CREATE TABLE dzz_user_status (
  uid int(10) unsigned NOT NULL,
  regip char(15) NOT NULL DEFAULT '',
  lastip char(15) NOT NULL DEFAULT '',
  lastvisit int(10) unsigned NOT NULL DEFAULT '0',
  lastactivity int(10) unsigned NOT NULL DEFAULT '0',
  lastsendmail int(10) unsigned NOT NULL DEFAULT '0',
  invisible tinyint(1) NOT NULL DEFAULT '0',
  profileprogress tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (uid),
  KEY lastactivity (lastactivity,invisible)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS dzz_user_thame;
CREATE TABLE dzz_user_thame (
  uid int(10) unsigned NOT NULL DEFAULT '0',
  custom_backimg varchar(255) NOT NULL,
  custom_url varchar(255) NOT NULL,
  custom_btype tinyint(1) unsigned NOT NULL,
  custom_color varchar(255) NOT NULL DEFAULT '',
  thame smallint(6) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY uid (uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_user_verify;
CREATE TABLE dzz_user_verify (
  uid int(10) unsigned NOT NULL,
  verify1 tinyint(1) NOT NULL DEFAULT '0' COMMENT '-1:已拒绝，0：待审核，1认证通过',
  verify2 tinyint(1) NOT NULL DEFAULT '0',
  verify3 tinyint(1) NOT NULL DEFAULT '0',
  verify4 tinyint(1) NOT NULL DEFAULT '0',
  verify5 tinyint(1) NOT NULL DEFAULT '0',
  verify6 tinyint(1) NOT NULL DEFAULT '0',
  verify7 tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (uid),
  KEY verify1 (verify1),
  KEY verify2 (verify2),
  KEY verify3 (verify3),
  KEY verify4 (verify4),
  KEY verify5 (verify5),
  KEY verify6 (verify6),
  KEY verify7 (verify7)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_user_verify_info;
CREATE TABLE dzz_user_verify_info (
  vid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username varchar(30) NOT NULL DEFAULT '',
  verifytype tinyint(1) NOT NULL DEFAULT '0' COMMENT ' 审核类型0:资料审核, 1:认证1, 2:认证2, 3:认证3, 4:认证4, 5:认证5',
  flag tinyint(1) NOT NULL DEFAULT '0' COMMENT ' -1:被拒绝 0:待审核 1:审核通过',
  field text NOT NULL,
  orgid int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (vid),
  KEY verifytype (verifytype,flag),
  KEY uid (uid,verifytype,dateline)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_user_wechat;
CREATE TABLE dzz_user_wechat (
  uid int(10) unsigned NOT NULL DEFAULT '0',
  openid char(28) NOT NULL DEFAULT '',
  appid char(18) NOT NULL DEFAULT '',
  unionid char(29) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY uid (uid),
  UNIQUE KEY openid (openid,appid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_vote;
CREATE TABLE dzz_vote (
  voteid mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '投票类型：0：文字投票；1：图片投票',
  starttime int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  endtime int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  isvisible tinyint(1) NOT NULL DEFAULT '0' COMMENT '投票结果查看权限，0：所有人可见、1：投票后可见',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态  0：有效 、1：无效、2：结束',
  uid int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发布者UID',
  maxselectnum tinyint(2) NOT NULL DEFAULT '1' COMMENT '最大可选择数',
  module varchar(60) NOT NULL DEFAULT '' COMMENT '模块名称',
  id int(10) unsigned NOT NULL DEFAULT '0' COMMENT '调用者id',
  idtype varchar(30) NOT NULL DEFAULT '' COMMENT '调用者id所在的表名',
  showuser tinyint(1) NOT NULL DEFAULT '0' COMMENT '显示投票用户',
  PRIMARY KEY (voteid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_vote_item;
CREATE TABLE dzz_vote_item (
  itemid mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '投票项id',
  voteid mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '投票id',
  content varchar(255) NOT NULL DEFAULT '' COMMENT '投票项内容',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '投票项类型：1、内容；2、图片',
  number smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '投票数',
  aid int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图片aid',
  disp smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (itemid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_vote_item_count;
CREATE TABLE dzz_vote_item_count (
  itemid mediumint(8) unsigned NOT NULL COMMENT '投票项ID',
  uid int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UID',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY itemid (itemid,uid)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS dzz_wx_app;
CREATE TABLE dzz_wx_app (
  appid int(10) unsigned NOT NULL DEFAULT '0' COMMENT '对应的应用appid',
  `name` varchar(255) NOT NULL DEFAULT '',
  `desc` text NOT NULL,
  icon varchar(255) NOT NULL DEFAULT '' COMMENT '应用图标',
  agentid varchar(20) NOT NULL DEFAULT '0' COMMENT '微信agentid',
  `secret` varchar(255) NULL DEFAULT '' COMMENT '应用secret ',
  `host` varchar(255) NOT NULL DEFAULT '' COMMENT '可信域名',
  callback varchar(255) NOT NULL DEFAULT '' COMMENT '回调地址',
  token varchar(255) NOT NULL DEFAULT '' COMMENT 'token',
  encodingaeskey varchar(255) NOT NULL DEFAULT '' COMMENT 'AESKey',
  menu text NOT NULL COMMENT '菜单设置',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '应用状态',
  `range` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '可见范围:0：全部，>0为机构orgid',
  dateline int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  notify tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户状态变更通知',
  report_msg tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户消息上报',
  report_location tinyint(1) NOT NULL DEFAULT '0' COMMENT '上报地理位置',
  otherpic varchar(255) NOT NULL,
  PRIMARY KEY (appid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_hooks;
CREATE TABLE dzz_hooks (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  app_market_id int(11) NOT NULL COMMENT '应用ID',
  `name` varchar(40) NOT NULL DEFAULT '' COMMENT '钩子名称',
  description text NOT NULL COMMENT '描述',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '类型',
  update_time int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  addons varchar(255) NOT NULL DEFAULT '' COMMENT '钩子对应程序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态1正常;-1删除',
  priority smallint(6) NOT NULL DEFAULT '0' COMMENT '运行优先级，挂载点下的钩子按优先级从高到低顺序执行',
  PRIMARY KEY (id),
  KEY app_market_id (`name`),
  KEY priority (priority)
) ENGINE=MyISAM;
