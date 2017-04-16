--
-- 数据库: 'dzzoffice'
--

--
-- 表的结构 'dzz_admincp_session'
--

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


--
-- 表的结构 'dzz_app_market'
--
DROP TABLE IF EXISTS dzz_app_market;
CREATE TABLE dzz_app_market (
  appid int(10) unsigned NOT NULL AUTO_INCREMENT,
  appname varchar(255) NOT NULL,
  appico varchar(255) NOT NULL,
  appdesc text NOT NULL,
  appurl varchar(255) NOT NULL,
  noticeurl varchar(255) NOT NULL DEFAULT '' COMMENT '通知接口地址',
  dateline int(10) unsigned NOT NULL,
  disp int(10) unsigned NOT NULL DEFAULT '0',
  vendor varchar(255) NOT NULL COMMENT '提供商',
  haveflash tinyint(1) unsigned NOT NULL DEFAULT '0',
  isshow tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示应用图标',
  havetask tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示任务栏',
  hideInMarket tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '应用市场里不显示',
  feature text NOT NULL COMMENT '窗体feature',
  fileext text NOT NULL COMMENT '可以打开的文件类型',
  `group` tinyint(1) NOT NULL DEFAULT '1' COMMENT '应用的分组:0:全部；''-1'':游客可用，''3'':系统管理员可用;''2''：部门管理员可用;''1'':所有成员可用',
  orgid int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可以使用的部门id，为0表示不限制',
  position tinyint(1) NOT NULL DEFAULT '0' COMMENT '2：''desktop'',3：''taskbar'',1：''apparea''',
  system tinyint(1) NOT NULL DEFAULT '0',
  notdelete tinyint(1) NOT NULL DEFAULT '0',
  `open` tinyint(1) NOT NULL DEFAULT '0',
  nodup tinyint(1) NOT NULL DEFAULT '0',
  identifier varchar(40) NOT NULL DEFAULT '',
  available tinyint(1) NOT NULL DEFAULT '1',
  version varchar(20) NOT NULL DEFAULT '',
  extra text NOT NULL,
  PRIMARY KEY (appid),
  UNIQUE KEY appurl (appurl),
  KEY available (available),
  KEY identifier (identifier)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_app_open'
--

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
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_app_open_default'
--

DROP TABLE IF EXISTS dzz_app_open_default;
CREATE TABLE dzz_app_open_default (
  uid int(10) unsigned NOT NULL,
  ext varchar(255) NOT NULL DEFAULT '',
  extid smallint(6) unsigned  NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY defaultext (ext,uid)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_app_organization'
--

DROP TABLE IF EXISTS dzz_app_organization;
CREATE TABLE dzz_app_organization (
  appid int(10) unsigned NOT NULL DEFAULT '0',
  orgid int(10) unsigned  NOT NULL DEFAULT '0',
  dateline int(10) NOT NULL DEFAULT '0',
  UNIQUE KEY orgid (appid,orgid),
  KEY appid (appid)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_app_pic'
--

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


--
-- 表的结构 'dzz_app_relative'
--

DROP TABLE IF EXISTS dzz_app_relative;
CREATE TABLE dzz_app_relative (
  rid int(10) unsigned NOT NULL AUTO_INCREMENT,
  appid int(10) unsigned NOT NULL DEFAULT '0',
  tagid int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (rid),
  UNIQUE KEY appid (appid,tagid)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_app_tag'
--

DROP TABLE IF EXISTS dzz_app_tag;
CREATE TABLE dzz_app_tag (
  tagid int(10) unsigned NOT NULL AUTO_INCREMENT,
  hot int(10) unsigned NOT NULL DEFAULT '0',
  tagname char(15) NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (tagid),
  KEY appid (hot),
  KEY classid (tagname)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_app_user'
--

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
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_attachment'
--
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
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_cache'
--

DROP TABLE IF EXISTS dzz_cache;
CREATE TABLE dzz_cache (
  cachekey varchar(255) NOT NULL DEFAULT '',
  cachevalue mediumblob NOT NULL,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (cachekey)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_cai_image'
--

DROP TABLE IF EXISTS dzz_cai_image;
CREATE TABLE dzz_cai_image (
  cid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  aid int(10) unsigned NOT NULL DEFAULT '0',
  copys int(10) unsigned NOT NULL DEFAULT '0',
  ourl varchar(255) NOT NULL,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (cid),
  UNIQUE KEY ourl (ourl)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_cai_link'
--

DROP TABLE IF EXISTS dzz_cai_link;
CREATE TABLE dzz_cai_link (
  cid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  img varchar(255) NOT NULL DEFAULT '',
  url varchar(255) NOT NULL DEFAULT '',
  title varchar(255) NOT NULL DEFAULT '',
  `desc` text NOT NULL,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  copys int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (cid),
  UNIQUE KEY url (url)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_cai_music'
--

DROP TABLE IF EXISTS dzz_cai_music;
CREATE TABLE dzz_cai_music (
  cid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  img varchar(255) NOT NULL DEFAULT '',
  url varchar(255) NOT NULL DEFAULT '',
  title varchar(255) NOT NULL DEFAULT '',
  `desc` text NOT NULL,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  ourl varchar(255) NOT NULL,
  copys int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (cid),
  UNIQUE KEY ourl (ourl)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_cai_video'
--

DROP TABLE IF EXISTS dzz_cai_video;
CREATE TABLE dzz_cai_video (
  cid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  img varchar(255) NOT NULL DEFAULT '',
  url varchar(255) NOT NULL DEFAULT '',
  title varchar(255) NOT NULL DEFAULT '',
  `desc` text NOT NULL,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  ourl varchar(255) NOT NULL,
  copys int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (cid),
  UNIQUE KEY ourl (ourl)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_comment'
--

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


--
-- 表的结构 'dzz_comment_at'
--

DROP TABLE IF EXISTS dzz_comment_at;
CREATE TABLE dzz_comment_at (
  cid int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL,
  UNIQUE KEY pid_uid (cid,uid),
  KEY dateline (dateline)
) ENGINE=MyISAM;

--
-- 表的结构 'dzz_comment_attach'
--
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


--
-- 表的结构 'dzz_connect'
--

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

--
-- 表的结构 'dzz_connect_ftp'
--

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


--
-- 表的结构 'dzz_connect_pan'
--

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


--
-- 表的结构 'dzz_connect_storage'
--

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

DROP TABLE IF EXISTS dzz_connect_disk;
CREATE TABLE IF NOT EXISTS dzz_connect_disk (
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
) ENGINE=MyISAM  DEFAULT;

--
-- 表的结构 'dzz_count'
--

DROP TABLE IF EXISTS dzz_count;
CREATE TABLE dzz_count (
  jid int(10) unsigned NOT NULL AUTO_INCREMENT,
  id mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` enum('document','image','video','music','link','attach','topic','app','folder') NOT NULL,
  viewnum int(10) unsigned NOT NULL DEFAULT '0',
  replynum int(10) unsigned NOT NULL DEFAULT '0',
  downnum int(10) unsigned NOT NULL DEFAULT '0',
  updatetime int(10) unsigned NOT NULL DEFAULT '0',
  star float(2,1) NOT NULL DEFAULT '0.0',
  statnum int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (jid),
  KEY id (id,`type`)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_cron'
--

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
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_district'
--

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
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_document'
--

DROP TABLE IF EXISTS dzz_document;
CREATE TABLE dzz_document (
  did int(10) unsigned NOT NULL AUTO_INCREMENT,
  tid int(10) unsigned NOT NULL,
  fid smallint(6) unsigned  NOT NULL DEFAULT '0' COMMENT '文库分类id',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username char(30) NOT NULL DEFAULT '',
  area char(15) NOT NULL DEFAULT '',
  areaid int(10) unsigned NOT NULL DEFAULT '0',
  aid int(10) unsigned NOT NULL DEFAULT '0',
  version smallint(6) unsigned  NOT NULL DEFAULT '0',
  isdelete tinyint(10) unsigned NOT NULL DEFAULT '0',
  disp smallint(6) NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (did),
  KEY dateline (dateline),
  KEY disp (disp),
  KEY fid (fid)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_document_event'
--

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


--
-- 表的结构 'dzz_document_reversion'
--

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



--
-- 表的结构 'dzz_failedlogin'
--

DROP TABLE IF EXISTS dzz_failedlogin;
CREATE TABLE dzz_failedlogin (
  ip char(15) NOT NULL DEFAULT '',
  username char(32) NOT NULL DEFAULT '',
  count tinyint(1) unsigned NOT NULL DEFAULT '0',
  lastupdate int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (ip,username)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_feed_at'
--

DROP TABLE IF EXISTS dzz_feed_at;
CREATE TABLE dzz_feed_at (
  pid int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  tid int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY pid_uid (pid,uid),
  KEY dateline (dateline)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_feed_attach'
--

DROP TABLE IF EXISTS dzz_feed_attach;
CREATE TABLE dzz_feed_attach (
  qid int(10) unsigned NOT NULL AUTO_INCREMENT,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  pid int(10) unsigned NOT NULL DEFAULT '0',
  tid int(10) unsigned NOT NULL DEFAULT '0',
  aid int(10) unsigned NOT NULL DEFAULT '0',
  title varchar(255) NOT NULL,
  downloads int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(30) NOT NULL DEFAULT '',
  img varchar(255) NOT NULL DEFAULT '',
  url varchar(255) NOT NULL DEFAULT '',
  ext varchar(32) NOT NULL,
  PRIMARY KEY (qid),
  KEY dateline (dateline),
  KEY tid (pid)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_feed_collection'
--

DROP TABLE IF EXISTS dzz_feed_collection;
CREATE TABLE dzz_feed_collection (
  uid int(10) unsigned NOT NULL DEFAULT '0',
  tid int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY uid_tid (uid,tid)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_feed_post'
--

DROP TABLE IF EXISTS dzz_feed_post;
CREATE TABLE dzz_feed_post (
  pid int(10) unsigned NOT NULL AUTO_INCREMENT,
  tid mediumint(8) unsigned NOT NULL DEFAULT '0',
  `first` tinyint(1) NOT NULL DEFAULT '0',
  author varchar(30) NOT NULL DEFAULT '',
  authorid int(10) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(80) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  message mediumtext NOT NULL,
  useip varchar(15) NOT NULL DEFAULT '',
  `status` int(10) unsigned NOT NULL DEFAULT '0',
  attachment tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (pid),
  UNIQUE KEY pid (pid),
  KEY authorid (authorid),
  KEY dateline (dateline),
  KEY displayorder (tid,dateline),
  KEY `first` (tid,`first`)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_feed_reply'
--

DROP TABLE IF EXISTS dzz_feed_reply;
CREATE TABLE dzz_feed_reply (
  pid int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  tid int(10) unsigned NOT NULL DEFAULT '0',
  ruid int(10) unsigned NOT NULL DEFAULT '0',
  rpid int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL,
  UNIQUE KEY pid_uid (pid,rpid),
  KEY ruid (ruid),
  KEY dateline (dateline)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_feed_tag'
--

DROP TABLE IF EXISTS dzz_feed_tag;
CREATE TABLE dzz_feed_tag (
  tagid int(10) unsigned NOT NULL AUTO_INCREMENT,
  hot int(10) unsigned NOT NULL DEFAULT '0',
  tagname varchar(255) NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (tagid),
  KEY appid (hot),
  KEY classid (tagname)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_feed_tagrelative'
--

DROP TABLE IF EXISTS dzz_feed_tagrelative;
CREATE TABLE dzz_feed_tagrelative (
  pid int(10) unsigned NOT NULL DEFAULT '0',
  tagid int(10) unsigned NOT NULL DEFAULT '0',
  tid int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL,
  UNIQUE KEY pid_uid (pid,tagid),
  KEY dateline (dateline)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_feed_thread'
--

DROP TABLE IF EXISTS dzz_feed_thread;
CREATE TABLE dzz_feed_thread (
  tid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  posttableid smallint(6) unsigned NOT NULL DEFAULT '0',
  readperm mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '0:所有人可见；-1：仅@的人可见，>0：部门成员可见',
  author char(30) NOT NULL DEFAULT '',
  authorid int(10) unsigned NOT NULL DEFAULT '0',
  `subject` char(80) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  lastpost int(10) unsigned NOT NULL DEFAULT '0',
  lastposter char(30) NOT NULL DEFAULT '',
  replies mediumint(8) unsigned NOT NULL DEFAULT '0',
  collections mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '收藏数',
  special tinyint(1) NOT NULL DEFAULT '0' COMMENT '特殊主题',
  attachment tinyint(1) NOT NULL DEFAULT '0' COMMENT '附件,0无附件 1普通附件 2有图片附件',
  closed mediumint(8) unsigned NOT NULL DEFAULT '0',
  `status` smallint(6) unsigned NOT NULL DEFAULT '0',
  top tinyint(1) NOT NULL DEFAULT '0',
  votestatus tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (tid),
  KEY displayorder (lastpost),
  KEY authorid (authorid),
  KEY special (special),
  KEY top (lastpost,top)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_folder'
--

DROP TABLE IF EXISTS dzz_folder;
CREATE TABLE dzz_folder (
  fid int(10) unsigned NOT NULL AUTO_INCREMENT,
  pfid int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username char(30) NOT NULL DEFAULT '',
  innav tinyint(1) NOT NULL DEFAULT '1',
  fname char(50) NOT NULL DEFAULT '',
  perm int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0:继承；1：只读；2：可写',
  fsperm smallint(6) unsigned  NOT NULL DEFAULT '0',
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


--
-- 表的结构 'dzz_folder_default'
--

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
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_icon'
--

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
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_iconview'
--

DROP TABLE IF EXISTS dzz_iconview;
CREATE TABLE dzz_iconview (
  id smallint(6) unsigned  NOT NULL AUTO_INCREMENT,
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
) ENGINE=MyISAM;



--
-- 表的结构 'dzz_icos'
--

DROP TABLE IF EXISTS dzz_icos;
CREATE TABLE dzz_icos (
  icoid int(10) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username char(30) NOT NULL DEFAULT '',
  oid int(10) unsigned NOT NULL DEFAULT '0',
  `name` char(80) NOT NULL DEFAULT '',
  `type` char(15) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  pfid int(10) unsigned NOT NULL DEFAULT '0',
  opuid int(10) unsigned NOT NULL DEFAULT '0',
  urlsid int(10) unsigned NOT NULL DEFAULT '0',
  isdelete tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '删除标志',
  deldateline int(10) unsigned NOT NULL DEFAULT '0',
  gid int(10) unsigned NOT NULL DEFAULT '0',
  position char(15) NOT NULL DEFAULT '',
  flag char(15) NOT NULL DEFAULT '',
  sperm int(10) unsigned NOT NULL DEFAULT '0',
  size bigint(20) unsigned NOT NULL DEFAULT '0',
  ext char(15) NOT NULL DEFAULT '',
  PRIMARY KEY (icoid),
  UNIQUE KEY sourceunique (uid,`type`,oid),
  KEY dateline (dateline),
  KEY `type` (`type`),
  KEY uid (uid),
  KEY `name` (`name`),
  KEY pfid (pfid),
  KEY isdelete (isdelete)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_imagetype'
--

DROP TABLE IF EXISTS dzz_imagetype;
CREATE TABLE dzz_imagetype (
  typeid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  available tinyint(1) NOT NULL DEFAULT '0',
  `name` char(20) NOT NULL,
  `type` enum('smiley','icon','avatar') NOT NULL DEFAULT 'smiley',
  displayorder tinyint(3) NOT NULL DEFAULT '0',
  `directory` char(100) NOT NULL,
  PRIMARY KEY (typeid)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_local_router'
--

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


--
-- 表的结构 'dzz_local_storage'
--

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
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_mailcron'
--

DROP TABLE IF EXISTS dzz_mailcron;
CREATE TABLE dzz_mailcron (
  cid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  touid int(10) unsigned NOT NULL DEFAULT '0',
  email varchar(100) NOT NULL DEFAULT '',
  sendtime int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (cid),
  KEY sendtime (sendtime)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_mailqueue'
--

DROP TABLE IF EXISTS dzz_mailqueue;
CREATE TABLE dzz_mailqueue (
  qid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  cid mediumint(8) unsigned NOT NULL DEFAULT '0',
  `subject` text NOT NULL,
  message text NOT NULL,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (qid),
  KEY mcid (cid,dateline)
) ENGINE=MyISAM;



--
-- 表的结构 'dzz_notification'
--

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


--
-- 表的结构 'dzz_onlinetime'
--

DROP TABLE IF EXISTS dzz_onlinetime;
CREATE TABLE dzz_onlinetime (
  uid int(10) unsigned NOT NULL DEFAULT '0',
  thismonth smallint(6) unsigned NOT NULL DEFAULT '0',
  total mediumint(8) unsigned NOT NULL DEFAULT '0',
  lastupdate int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (uid)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_organization'
--

DROP TABLE IF EXISTS dzz_organization;
CREATE TABLE dzz_organization (
  orgid int(10) unsigned  NOT NULL AUTO_INCREMENT,
  orgname varchar(255) NOT NULL DEFAULT '',
  forgid int(10) unsigned  NOT NULL DEFAULT '0',
  worgid int(10) unsigned  NOT NULL DEFAULT '0',
  fid int(10) unsigned NOT NULL DEFAULT '0',
  disp smallint(6) NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  usesize bigint(20) unsigned NOT NULL DEFAULT '0',
  maxspacesize bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '0：不限制',
  indesk tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否创建快捷方式',
  available tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  pathkey varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (orgid),
  KEY disp (disp),
  KEY pathkey (pathkey),
  KEY dateline (dateline)
) ENGINE=MyISAM;

--
-- 表的结构 'dzz_organization_admin'
--

DROP TABLE IF EXISTS dzz_organization_admin;
CREATE TABLE dzz_organization_admin (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  orgid int(10) unsigned  NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  opuid int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  UNIQUE KEY orgid (orgid,uid)
) ENGINE=MyISAM;

--
-- 表的结构 'dzz_organization_job'
--

DROP TABLE IF EXISTS dzz_organization_job;
CREATE TABLE dzz_organization_job (
  jobid smallint(6) unsigned  NOT NULL AUTO_INCREMENT,
  orgid int(10) unsigned  NOT NULL DEFAULT '0',
  `name` varchar(30) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  opuid int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (jobid),
  KEY orgid (orgid)
) ENGINE=MyISAM;

--
-- 表的结构 'dzz_organization_upjob'
--

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


--
-- 表的结构 'dzz_organization_user'
--

DROP TABLE IF EXISTS dzz_organization_user;
CREATE TABLE dzz_organization_user (
  orgid int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  jobid smallint(6) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY orgid (orgid,uid)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_process'
--

DROP TABLE IF EXISTS dzz_process;
CREATE TABLE dzz_process (
  processid char(32) NOT NULL,
  expiry int(10)  DEFAULT NULL,
  extra int(10) DEFAULT NULL,
  PRIMARY KEY (processid),
  KEY expiry (expiry)
) ENGINE=MEMORY;


--
-- 表的结构 'dzz_session'
--

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


--
-- 表的结构 'dzz_setting'
--

DROP TABLE IF EXISTS dzz_setting;
CREATE TABLE dzz_setting (
  skey varchar(255) NOT NULL DEFAULT '',
  svalue text NOT NULL,
  PRIMARY KEY (skey)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_smiley'
--

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
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_source_attach'
--

DROP TABLE IF EXISTS dzz_source_attach;
CREATE TABLE dzz_source_attach (
  qid int(10) unsigned NOT NULL AUTO_INCREMENT,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username varchar(255) NOT NULL,
  aid int(10) unsigned NOT NULL DEFAULT '0',
  title varchar(255) NOT NULL,
  `desc` text NOT NULL,
  gid int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (qid),
  KEY uid (uid),
  KEY dateline (dateline)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_source_document'
--

DROP TABLE IF EXISTS dzz_source_document;
CREATE TABLE dzz_source_document (
  did int(10) unsigned NOT NULL AUTO_INCREMENT,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username varchar(255) NOT NULL,
  aid int(10) unsigned NOT NULL DEFAULT '0',
  title varchar(255) NOT NULL,
  `desc` text NOT NULL,
  gid int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (did),
  KEY uid (uid),
  KEY dateline (dateline)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_source_image'
--

DROP TABLE IF EXISTS dzz_source_image;
CREATE TABLE dzz_source_image (
  picid mediumint(8) NOT NULL AUTO_INCREMENT,
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username varchar(15) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  aid int(10) unsigned NOT NULL DEFAULT '0',
  postip varchar(255) NOT NULL DEFAULT '',
  cid int(10) unsigned NOT NULL DEFAULT '0',
  width int(10) unsigned NOT NULL DEFAULT '0',
  height int(10) unsigned NOT NULL DEFAULT '0',
  gid int(10) NOT NULL DEFAULT '0',
  title varchar(255) NOT NULL,
  `desc` text NOT NULL,
  PRIMARY KEY (picid),
  KEY uid (uid)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_source_link'
--

DROP TABLE IF EXISTS dzz_source_link;
CREATE TABLE dzz_source_link (
  lid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  url varchar(255) NOT NULL DEFAULT '',
  title varchar(255) NOT NULL DEFAULT '',
  `desc` text NOT NULL,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username varchar(60) NOT NULL,
  cid int(10) unsigned NOT NULL DEFAULT '0',
  gid int(10) NOT NULL DEFAULT '0',
  did int(10) unsigned NOT NULL DEFAULT '0',
  icon varchar(255) NOT NULL,
  ext varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (lid),
  KEY dateline (dateline),
  KEY uid (uid)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_source_music'
--

DROP TABLE IF EXISTS dzz_source_music;
CREATE TABLE dzz_source_music (
  mid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  icon varchar(255) NOT NULL,
  url varchar(255) NOT NULL DEFAULT '',
  title varchar(255) NOT NULL DEFAULT '',
  `desc` text NOT NULL,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username varchar(60) NOT NULL,
  cid int(10) unsigned NOT NULL DEFAULT '0',
  gid int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (mid),
  KEY dateline (dateline),
  KEY uid (uid)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_source_shortcut'
--

DROP TABLE IF EXISTS dzz_source_shortcut;
CREATE TABLE dzz_source_shortcut (
  cutid int(10) unsigned NOT NULL AUTO_INCREMENT,
  path text NOT NULL,
  bz varchar(255) NOT NULL,
  `data` mediumtext NOT NULL,
  PRIMARY KEY (cutid)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_source_video'
--

DROP TABLE IF EXISTS dzz_source_video;
CREATE TABLE dzz_source_video (
  vid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  icon varchar(255) NOT NULL,
  url varchar(255) NOT NULL DEFAULT '',
  title varchar(255) NOT NULL DEFAULT '',
  `desc` text NOT NULL,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username varchar(60) NOT NULL,
  cid int(10) unsigned NOT NULL DEFAULT '0',
  gid int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (vid),
  KEY dateline (dateline),
  KEY uid (uid)
) ENGINE=MyISAM;



--
-- 表的结构 'dzz_syscache'
--

DROP TABLE IF EXISTS dzz_syscache;
CREATE TABLE dzz_syscache (
  cname varchar(32) NOT NULL,
  ctype tinyint(3) unsigned NOT NULL,
  dateline int(10) unsigned NOT NULL,
  `data` mediumblob NOT NULL,
  PRIMARY KEY (cname)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_thame'
--

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
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_user'
--

DROP TABLE IF EXISTS dzz_user;
CREATE TABLE dzz_user (
  uid int(10) unsigned NOT NULL AUTO_INCREMENT,
  email char(40) NOT NULL DEFAULT '',
  phone varchar(255) DEFAULT '',
  weixinid varchar(255) NOT NULL DEFAULT '' COMMENT '微信号',
  wechat_userid varchar(255) NOT NULL DEFAULT '',
  wechat_status tinyint(1) NOT NULL DEFAULT '4' COMMENT '1:已关注；2：已冻结；4：未关注',
  nickname char(30) NOT NULL DEFAULT '',
  username char(30) NOT NULL DEFAULT '',
  `password` char(32) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  emailstatus tinyint(1) NOT NULL DEFAULT '0',
  avatarstatus tinyint(1) NOT NULL DEFAULT '0',
  adminid tinyint(1) NOT NULL DEFAULT '0',
  groupid smallint(6) unsigned NOT NULL DEFAULT '9',
  regdate int(10) unsigned NOT NULL DEFAULT '0',
  secques char(8) NOT NULL DEFAULT '',
  salt char(6) NOT NULL DEFAULT '',
  authstr char(30) NOT NULL,
  newprompt smallint(6) unsigned NOT NULL DEFAULT '0',
  timeoffset char(4) NOT NULL DEFAULT '9999',
  language char(10) NOT NULL DEFAULT '',
  grid smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (uid),
  UNIQUE KEY email (email),
  KEY groupid (groupid),
  KEY username (username)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_usergroup'
--

DROP TABLE IF EXISTS dzz_usergroup;
CREATE TABLE dzz_usergroup (
  groupid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  radminid tinyint(3) NOT NULL DEFAULT '0',
  `type` enum('system','special','member') NOT NULL DEFAULT 'member',
  system varchar(255) NOT NULL DEFAULT 'private',
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
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_usergroup_field'
--

DROP TABLE IF EXISTS dzz_usergroup_field;
CREATE TABLE dzz_usergroup_field (
  groupid smallint(6) unsigned NOT NULL DEFAULT '0',
  maxspacesize int(10) NOT NULL DEFAULT '0',
  attachextensions varchar(255) NOT NULL,
  maxattachsize int(10) unsigned NOT NULL DEFAULT '0',
  perm int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY groupid (groupid)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_user_field'
--

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
  UNIQUE KEY uid (uid)
) ENGINE=MyISAM;




--
-- 表的结构 'dzz_user_playlist'
--

DROP TABLE IF EXISTS dzz_user_playlist;
CREATE TABLE dzz_user_playlist (
  playlist text NOT NULL,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  updatetime int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY uid (uid)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_user_profile'
--

DROP TABLE IF EXISTS dzz_user_profile;
CREATE TABLE dzz_user_profile (
  uid int(10) unsigned NOT NULL,
  realname varchar(255) NOT NULL DEFAULT '',
  gender tinyint(1) NOT NULL DEFAULT '0',
  birthyear smallint(6) unsigned NOT NULL DEFAULT '0',
  birthmonth tinyint(3) unsigned NOT NULL DEFAULT '0',
  birthday tinyint(3) unsigned NOT NULL DEFAULT '0',
  constellation varchar(255) NOT NULL DEFAULT '',
  zodiac varchar(255) NOT NULL DEFAULT '',
  telephone varchar(255) NOT NULL DEFAULT '',
  mobile varchar(255) NOT NULL DEFAULT '',
  idcardtype varchar(255) NOT NULL DEFAULT '',
  idcard varchar(255) NOT NULL DEFAULT '',
  address varchar(255) NOT NULL DEFAULT '',
  zipcode varchar(255) NOT NULL DEFAULT '',
  nationality varchar(255) NOT NULL DEFAULT '',
  birthprovince varchar(255) NOT NULL DEFAULT '',
  birthcity varchar(255) NOT NULL DEFAULT '',
  birthdist varchar(20) NOT NULL DEFAULT '',
  birthcommunity varchar(255) NOT NULL DEFAULT '',
  resideprovince varchar(255) NOT NULL DEFAULT '',
  residecity varchar(255) NOT NULL DEFAULT '',
  residedist varchar(20) NOT NULL DEFAULT '',
  residecommunity varchar(255) NOT NULL DEFAULT '',
  residesuite varchar(255) NOT NULL DEFAULT '',
  graduateschool varchar(255) NOT NULL DEFAULT '',
  company varchar(255) NOT NULL DEFAULT '',
  education varchar(255) NOT NULL DEFAULT '',
  occupation varchar(255) NOT NULL DEFAULT '',
  position varchar(255) NOT NULL DEFAULT '',
  revenue varchar(255) NOT NULL DEFAULT '',
  affectivestatus varchar(255) NOT NULL DEFAULT '',
  lookingfor varchar(255) NOT NULL DEFAULT '',
  bloodtype varchar(255) NOT NULL DEFAULT '',
  height varchar(255) NOT NULL DEFAULT '',
  weight varchar(255) NOT NULL DEFAULT '',
  alipay varchar(255) NOT NULL DEFAULT '',
  icq varchar(255) NOT NULL DEFAULT '',
  qq varchar(255) NOT NULL DEFAULT '',
  yahoo varchar(255) NOT NULL DEFAULT '',
  skype varchar(255) NOT NULL DEFAULT '',
  taobao varchar(255) NOT NULL DEFAULT '',
  site varchar(255) NOT NULL DEFAULT '',
  bio text NOT NULL,
  interest text NOT NULL,
  field1 text NOT NULL,
  field2 text NOT NULL,
  field3 text NOT NULL,
  field4 text NOT NULL,
  field5 text NOT NULL,
  field6 text NOT NULL,
  field7 text NOT NULL,
  field8 text NOT NULL,
  UNIQUE KEY uid (uid)
) ENGINE=MyISAM;

--
-- 表的结构 'dzz_user_profile1'
--
DROP TABLE IF EXISTS dzz_user_profile1;
CREATE TABLE dzz_user_profile1 (
  uid int(10) unsigned NOT NULL,
  fieldid varchar(30) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (uid,fieldid)
) ENGINE=MyISAM;

--
-- 表的结构 'dzz_user_profile_setting'
--

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
  validate text NOT NULL,
  customable tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (fieldid)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_user_status'
--

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


--
-- 表的结构 'dzz_user_thame'
--

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


--
-- 表的结构 'dzz_wallpaper'
--

DROP TABLE IF EXISTS dzz_wallpaper;
CREATE TABLE dzz_wallpaper (
  bid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `type` char(30) NOT NULL,
  title varchar(255) NOT NULL,
  val varchar(255) NOT NULL,
  classid smallint(6) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  disp smallint(6) unsigned NOT NULL DEFAULT '0',
  thumb tinyint(1) unsigned NOT NULL DEFAULT '0',
  img varchar(255) NOT NULL,
  PRIMARY KEY (bid),
  KEY classid (classid),
  KEY disp (disp),
  KEY `type` (`type`)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_wallpaper_class'
--

DROP TABLE IF EXISTS dzz_wallpaper_class;
CREATE TABLE dzz_wallpaper_class (
  classid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  classname varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  disp smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (classid),
  KEY disp (disp),
  KEY `type` (`type`)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_vote'
--
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


--
-- 表的结构 'dzz_vote_item'
--
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


--
-- 表的结构 'dzz_vote_item_count'
--
DROP TABLE IF EXISTS dzz_vote_item_count;
CREATE TABLE dzz_vote_item_count (
  itemid mediumint(8) unsigned NOT NULL COMMENT '投票项ID',
  uid int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UID',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY itemid (itemid,uid)
) ENGINE=MyISAM;

--
-- 表的结构 'dzz_folder_event'
--
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

--
-- 表的结构 'dzz_template'
--
DROP TABLE IF EXISTS dzz_template;
CREATE TABLE dzz_template (
  tpid smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '模板id',
  body text NOT NULL COMMENT '模板内容',
  tpname varchar(80) NOT NULL COMMENT '模板名称',
  `type` varchar(30) NOT NULL COMMENT '模板类型',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  available tinyint(1) NOT NULL DEFAULT '0',
  disp smallint(6) NOT NULL DEFAULT '0',
  attachs text NOT NULL,
  PRIMARY KEY (tpid),
  KEY `type` (`type`),
  KEY disp (disp)
) ENGINE=MyISAM;

--
-- 表的结构 'dzz_wx_app'
--
DROP TABLE IF EXISTS dzz_wx_app;
CREATE TABLE dzz_wx_app (
  appid int(10) unsigned NOT NULL DEFAULT '0' COMMENT '对应的应用appid',
  `name` varchar(255) NOT NULL DEFAULT '',
  `desc` text NOT NULL,
  icon varchar(255) NOT NULL DEFAULT '' COMMENT '应用图标',
  agentid smallint(6)  NOT NULL DEFAULT '0' COMMENT '微信agentid',
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

--
-- 表的结构 'dzz_user_verify'
--

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

--
-- 表的结构 'dzz_user_verify_info'
--

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

--
-- 表的结构 'dzz_shorturl'
--

DROP TABLE IF EXISTS dzz_shorturl;
CREATE TABLE dzz_shorturl (
  sid char(10) NOT NULL,
  url text NOT NULL,
  count int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (sid)
) ENGINE=MyISAM;

--
-- 表的结构 'dzz_share'
--

DROP TABLE IF EXISTS dzz_share;
CREATE TABLE dzz_share (
  sid char(6) NOT NULL,
  title varchar(255) NOT NULL DEFAULT '',
  path text NOT NULL,
  `type` char(30) NOT NULL DEFAULT '' COMMENT '文件类型',
  ext char(15) NOT NULL DEFAULT '',
  img varchar(255) NOT NULL DEFAULT '',
  size bigint(20) NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  count smallint(6) unsigned NOT NULL DEFAULT '0',
  times smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '分享次数',
  endtime int(10) unsigned NOT NULL DEFAULT '0' COMMENT '到期时间',
  `password` varchar(256) NOT NULL DEFAULT '' COMMENT '分享密码',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  username char(30) NOT NULL,
  private tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '-4：文件不存在；-3：次数到；-1：已过期；0：正常',
  PRIMARY KEY (sid),
  KEY uid (uid)
) ENGINE=MyISAM;


--
-- 表的结构 'dzz_user_wechat'
--
DROP TABLE IF EXISTS dzz_user_wechat;
CREATE TABLE dzz_user_wechat (
  uid int(10) unsigned NOT NULL DEFAULT '0',
  openid char(28) NOT NULL DEFAULT '',
  appid char(18) NOT NULL DEFAULT '',
  unionid char(29) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY uid (uid),
  UNIQUE KEY openid (openid,appid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- 表的结构 'dzz_user_qqconnect'
--

DROP TABLE IF EXISTS dzz_user_qqconnect;
CREATE TABLE dzz_user_qqconnect (
  openid varchar(255) NOT NULL COMMENT 'Openid',
  uid int(10) unsigned NOT NULL COMMENT '对应UID',
  dateline int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  unbind tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (openid(20)),
  KEY uid (uid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;