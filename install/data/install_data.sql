--
-- 转存表中的数据 `dzz_app_market`
-- 
INSERT INTO `dzz_app_market` (`appid`,`mid`, `appname`, `appico`, `appdesc`, `appurl`,`appadminurl`, `noticeurl`, `dateline`, `disp`, `vendor`, `haveflash`, `isshow`, `havetask`, `hideInMarket`, `feature`, `fileext`, `group`, `orgid`, `position`, `system`, `notdelete`, `open`, `nodup`, `identifier`, `app_path`, `available`, `version`, `upgrade_version`, `check_upgrade_time`, `extra`) VALUES
(1, 1, '管理', 'appico/201712/21/184312rthhhg9oujti9tuu.png', '管理员应用集合，方便管理员管理各个管理应用', '{dzzscript}?mod=appmanagement', '', '', 0, 1, '乐云网络', 0, 1, 1, 0, '', '', 3, 0, 1, 2, 1, 0, 0, 'appmanagement', 'dzz', 1, '2.0', '', 20171115, ''),
(2, 2, '机构用户', 'appico/201712/21/131016is1wjww2uwvljllw.png', 'Dzz机构用户管理', '{adminscript}?mod=orguser', '', '', 1377753015, 2, '乐云网络', 0, 1, 1, 0, '', '', 3, 0, 0, 2, 1, 0, 0, 'orguser', 'admin', 1, '2.0', '', 20171211, ''),
(3, 3, '系统设置', 'appico/201712/21/160754fwfmziiiift3gwsw.png', '系统基础设置', '{adminscript}?mod=setting', '', '', 1377677273, 3, '乐云网络', 0, 1, 1, 0, '', '', 3, 0, 0, 2, 1, 0, 0, 'setting', 'admin', 1, '2.0', '', 20171115, ''),
(4, 4, '应用市场', 'appico/201712/21/152718k9g2pc6wouwkklwl.png', '应用管理，应用市场，支持应用在线安装，在线升级等', '{adminscript}?mod=appmarket', '', '', 1377674837, 4, '乐云网络', 0, 1, 1, 0, '', '', 3, 0, 0, 2, 1, 0, 0, 'appmarket', 'admin', 1, '2.0', '', 20171115, ''),
(5, 5, '云设置和管理', 'appico/201712/21/171106u1dk40digrrr79ed.png', '设置和管理第三方云盘、云存储等', '{adminscript}?mod=cloud', '', '', 0, 5, '乐云网络', 0, 1, 1, 0, '', '', 3, 0, 0, 2, 1, 0, 0, 'cloud', 'admin', 1, '2.0', '', 20171115, ''),
(6, 6, '文件管理', 'appico/201712/21/175535t47bad99b7sssdwq.png', '管理和查看系统所有文件', '{adminscript}?mod=filemanage', '', '', 0, 6, '乐云网络', 0, 1, 1, 0, '', '', 3, 0, 0, 2, 1, 0, 0, 'filemanage', 'admin', 1, '2.0', '', 20180206, ''),
(7, 7, '分享管理', 'appico/201712/21/165535t47bad99b7qqqdwq.png', '管理和查阅所有分享', '{adminscript}?mod=share', '', '', 0, 7, '乐云网络', 0, 1, 1, 0, '', '', 3, 0, 0, 2, 1, 0, 0, 'share', 'admin', 1, '2.0', '', 20180206, ''),
(8, 8, '系统日志', 'appico/201712/21/113527zz2665xg7d3h2777.png', 'Dzz 日志记录', '{adminscript}?mod=systemlog', '{adminscript}?mod=systemlog&op=admin', '', 0, 8, '乐云网络', 0, 1, 1, 0, '', '', 3, 0, 0, 2, 1, 0, 0, 'systemlog', 'admin', 1, '2.0', '', 20171115, 'a:2:{s:11:\"installfile\";s:11:\"install.php\";s:13:\"uninstallfile\";s:13:\"uninstall.php\";}'),
(9, 9, '系统工具', 'appico/201712/21/160537cikgw2v6s6z4scuv.png', '系统维护相关工具集合，如：更新缓存、数据库备份，计划任务，在线升级等', '{adminscript}?mod=system', '', '', 1377677136, 9, '乐云网络', 0, 1, 1, 0, '', '', 3, 0, 0, 2, 1, 0, 0, 'system', 'admin', 1, '2.0', '', 20171115, ''),
(10, 10, '应用库', 'appico/201712/21/123754pb0s666i6sjws1jc.png', '通过应用库用户选择安装自己需要的应用', '{dzzscript}?mod=market', '', '{dzzscript}?mod=market&op=notice', 1378615073, 10, '乐云网络', 0, 1, 1, 0, '', '', 1, 0, 1, 0, 1, 0, 0, 'market', 'dzz', 0, '2.0', '', 20171115, ''),
(11, 11, '投票', 'appico/201712/21/150002d834yjjqnq82qj8z.png', 'Dzz 内置投票组件，结合其他应用使用，如新闻中用到投票插件，其他开发者也可以为自己的应用调用这个通用评论插件', '{dzzscript}?mod=dzzvote', '', '', 1378615073, 11, '乐云网络', 0, 0, 1, 0, '', '', 3, 0, 0, 2, 1, 0, 0, 'dzzvote', 'dzz', 1, '2.0', '', 20171115, ''), 
(12, 12, '评论', 'appico/201712/21/128754pb0s666i6sjws1jc.png', 'Dzz 系统评论组件，结合在其他应用使用，如新闻。其他开发者也可以为自己的应用调用这个通用评论插件', '{dzzscript}?mod=comment', '', '', 1378615073, 12, '乐云网络', 0, 0, 1, 0, '', '', 3, 0, 0, 2, 1, 0, 0, 'comment', 'dzz', 1, '2.0', '', 20171115, ''),
(13, 37, '用户资料管理', 'appico/201712/21/103805dczcm89b0gi8i9gc.png', '管理用户资料、资料审核、认证等', '{adminscript}?mod=member', '', '', 1378615073, 13, '乐云网络', 0, 1, 1, 0, '', '', 3, 0, 0, 2, 1, 0, 0, 'member', 'admin', 1, '2.0', '', 20171115, '');
--
-- 转存表中的数据 `dzz_app_tag`
--

INSERT INTO `dzz_app_tag` VALUES(1, 14, '系统工具', 1377677488);

--
-- 转存表中的数据 `dzz_app_relative`
--

INSERT INTO `dzz_app_relative` VALUES(1, 1, 1);
INSERT INTO `dzz_app_relative` VALUES(2, 2, 1);
INSERT INTO `dzz_app_relative` VALUES(3, 3, 1);
INSERT INTO `dzz_app_relative` VALUES(4, 4, 1);
INSERT INTO `dzz_app_relative` VALUES(5, 5, 1);
INSERT INTO `dzz_app_relative` VALUES(6, 6, 1);
INSERT INTO `dzz_app_relative` VALUES(7, 7, 1);
INSERT INTO `dzz_app_relative` VALUES(8, 8, 1);
INSERT INTO `dzz_app_relative` VALUES(9, 9, 1);
--
-- 转存表中的数据 `dzz_connect`
--
INSERT INTO `dzz_connect` VALUES('百度网盘', '', '', 'pan', 'baiduPCS', '', 0, 'connect_pan', '', 10);
INSERT INTO `dzz_connect` VALUES('阿里云存储', '', '', 'storage', 'ALIOSS', '', 2, 'connect_storage', '', 0);
INSERT INTO `dzz_connect` VALUES('企业盘', '', '', 'local', 'dzz', '', 2, '', '', -2);
INSERT INTO `dzz_connect` VALUES('FTP', '', '', 'ftp', 'ftp', '', 2, 'connect_ftp', '', 0);
INSERT INTO `dzz_connect` VALUES('七牛云存储', '', '', 'storage', 'qiniu', '', 2, 'connect_storage', '', 0);
INSERT INTO `dzz_connect` VALUES('OneDrive', '', '', 'pan', 'OneDrive', '', 0, 'connect_onedrive', '', 0);
INSERT INTO `dzz_connect` VALUES('本地磁盘', '', '', 'disk', 'disk', '', 1, 'connect_disk', '', -1);

--
-- 转存表中的数据 `dzz_local_storage`
--

INSERT INTO `dzz_local_storage` VALUES(1, '服务器磁盘', 'dzz', 1, '', 0, 0, 0, 0, 0, 0);

--
-- 转存表中的数据 `dzz_icon`
--

INSERT INTO `dzz_icon` VALUES(1, 'localhost', '/mod=corpus&op=list&cid=\\d+&fid=\\d+/i', 'corpus', 'icon/201405/30/localhost_g21939b3.png', 1401380014, 1, 0, '', 2, 100);
INSERT INTO `dzz_icon` VALUES(2, 'localhost', 'mod=corpus&op=list&cid=', 'corpus', 'icon/201405/30/localhost_kae23q2i.png', 1401380253, 1, 0, '', 2, 0);
INSERT INTO `dzz_icon` VALUES(3, 'localhost', '/mod=taskboard&op=list&tbid=\\d+&taskid=\\d+/i', 'task', 'icon/201405/30/localhost_ovu7buw7.png', 1401380494, 1, 0, '', 3, 100);
INSERT INTO `dzz_icon` VALUES(4, 'localhost', '?mod=taskboard&op=list', 'task', 'icon/201405/29/localhost_qgiob2bi.png', 1401368704, 1, 0, '', 3, 0);
INSERT INTO `dzz_icon` VALUES(5, 'localhost', 'mod=discuss&op=viewthread', 'discuss', 'icon/201405/30/localhost_g4449m8c.png', 1401380612, 1, 0, '', 1, 50);
INSERT INTO `dzz_icon` VALUES(6, 'localhost', 'mod=discuss', 'discuss', 'icon/201405/30/localhost_kzf18fqm.png', 1401380685, 1, 0, '', 1, 0);

--
-- 转存表中的数据 `dzz_cron`
--

INSERT INTO `dzz_cron` VALUES(1, 1, 'system', '每月通知清理', 'cron_clean_notification_month.php', 1393646860, 0, -1, 1, 5, '0');
INSERT INTO `dzz_cron` VALUES(3, 1, 'system', '每周清理缓存文件', 'cron_cache_cleanup_week.php', 1395635931, 0, 1, -1, 5, '0');
INSERT INTO `dzz_cron` VALUES(4, 0, 'system', '每周清理缓存缩略图', 'cron_imgcache_cleanup_week.php', 1395635931,0, 1, -1, 5, '0');
INSERT INTO `dzz_cron` VALUES(5, 0, 'system', '每月清除未用附件', 'cron_clean_copys0_attachment_by_month.php', 1395388548,0, -1, -1, -1, '5	10	15	20	25	30	35	40	45	50	55');
INSERT INTO `dzz_cron` VALUES(6, 0, 'system', '定时备份数据库', 'cron_database_backup.php', 1460797274, 1460840400, 0, -1, 5, '0');
INSERT INTO `dzz_cron` VALUES(7, 0, 'system', '定时迁移本地文件到存储位置', 'cron_movetospace_attachment.php', 1536458668, 1536459000, -1, -1, -1, '0	10	20	30	40	50');

--
-- 转存表中的数据 `dzz_folder_default`
--

--INSERT INTO `dzz_folder_default` VALUES(1, 1, '桌面', 0, 0, 1, 0, 'desktop', '');
--INSERT INTO `dzz_folder_default` VALUES(2, 1, '我的文档', 0, 0, 1, 0, 'document', 'm:desktop');
--INSERT INTO `dzz_folder_default` VALUES(3, 1, '任务栏', 0, 0, 1, 10, 'dock', '');
--INSERT INTO `dzz_folder_default` VALUES(4, 1, '回收站', 0, 0, 1, 9999, 'recycle', 'm:desktop');

--
-- 转存表中的数据 `dzz_hooks`
--
INSERT INTO `dzz_hooks` (`id`, `app_market_id`, `name`, `description`, `type`, `update_time`, `addons`, `status`,`priority`) VALUES
(1, 0, 'check_login', '', 1, 0, 'user\\classes\\checklogin', 1, 0),
(2, 0, 'safe_chk', '', 1, 0, 'user\\classes\\safechk', 1, 0),
(3, 0, 'config_read', '读取配置钩子', 0, 0, 'core\\dzz\\config', 1, 0),
(4, 0, 'dzz_route', '', 1, 0, 'core\\dzz\\route', 1,0),
(5, 0, 'dzz_initbefore', '', 0, 0, 'user\\classes\\init|user', 1, 0),
(6, 0, 'dzz_initbefore', '', 0, 0, 'misc\\classes\\init|misc', 1, 0),
(7, 0, 'dzz_initafter', '', 1, 0, 'user\\classes\\route|user', 1, 0),
(8, 0, 'app_run', '', 1, 0, 'core\\dzz\\apprun', 1, 0),
(9, 0, 'mod_run', '', 1, 0, 'core\\dzz\\modrun', 1,0),
(10, 0, 'adminlogin', '', 1, 0, 'admin\\login\\classes\\adminlogin', 1, 0),
(12, 0, 'mod_start', '', 1, 0, 'core\\dzz\\modroute', 1, 0),
(13, 0, 'login_check', '', 1, 0, 'user\\login\\classes\\logincheck|user', 1, 0),
(14, 0, 'login_valchk', '', 1, 0, 'user\\login\\classes\\loginvalchk|user/login', 1, 0),
(15, 0, 'token_chk', '', 1, 0, 'user\\sso\\classes\\oauth|user/sso', 1, 0),
(16, 0, 'email_chk', '', 1, 0, 'user\\profile\\classes\\emailchk|user', 1, 0),
(17, 0, 'register_before', '', 1, 0, 'user\\register\\classes\\register|user', 1, 0),
(18, 0, 'check_val', '', 1, 0, 'user\\register\\classes\\checkvalue|user', 1, 0),
(19, 0, 'register_common', '', 1, 0, 'user\\register\\classes\\regcommon', 1, 0),
(20, 8, 'systemlog', '', 1, 0, 'admin\\systemlog\\classes\\systemlog', 1, 0);

--
-- 转存表中的数据 `dzz_iconview`
--

--INSERT INTO `dzz_iconview` VALUES(1, '大图标', 100, 100, 155, 160, 30, 30, 50, 0, 1, 1, 'bigicon');
--INSERT INTO `dzz_iconview` VALUES(2, '中图标', 50, 50, 100, 103, 30, 20, 40, 0, 1, 2, 'middleicon');
--INSERT INTO `dzz_iconview` VALUES(3, '中图标列表', 50, 50, 180, 70, 20, 20, 40, 1, 1, 3, 'middlelist');
--INSERT INTO `dzz_iconview` VALUES(4, '小图标列表', 32, 32, 220, 42, 20, 20, 36, 1, 1, 4, 'smalllist');


--
-- 转存表中的数据 `dzz_usergroup`
--

INSERT INTO `dzz_usergroup` VALUES(1, 1, 'system', 'private', '管理员', 0, 0, 9, '', '', 2, 1, 1, 1, 0, 0, 10);
INSERT INTO `dzz_usergroup` VALUES(2, 2, 'system', 'private', '机构和部门管理员', 0, 0, 8, '', '', 1, 1, 1, 1, 0, 0, 10);
INSERT INTO `dzz_usergroup` VALUES(3, 3, 'system', 'private', '部门管理员', 0, 0, 7, '', '', 1, 1, 1, 1, 0, 0, 10);
INSERT INTO `dzz_usergroup` VALUES(4, 0, 'system', 'private', '禁止发言', 0, 0, 0, '', '', 1, 1, 0, 0, 0, 0, 0);
INSERT INTO `dzz_usergroup` VALUES(5, 0, 'system', 'private', '禁止访问', 0, 0, 0, '', '', 0, 1, 0, 0, 0, 0, 0);
INSERT INTO `dzz_usergroup` VALUES(6, 0, 'system', 'private', '禁止 IP', 0, 0, 0, '', '', 0, 1, 0, 0, 0, 0, 0);
INSERT INTO `dzz_usergroup` VALUES(7, 0, 'system', 'private', '游客', 0, 0, 0, '', '', 1, 1, 0, 0, 0, 0, 10);
INSERT INTO `dzz_usergroup` VALUES(8, 0, 'system', 'private', '等待验证成员', 0, 0, 0, '', '', 1, 1, 0, 0, 0, 0, 0);
INSERT INTO `dzz_usergroup` VALUES(9, 0, 'system', 'private', '普通成员', 0, 0, 0, '', '', 1, 1, 0, 0, 0, 0, 0);
INSERT INTO `dzz_usergroup` VALUES(10, 0, 'system', 'private', '信息录入员', 0, 0, 0, '', '', 1, 1, 0, 0, 0, 0, 0);

--
-- 转存表中的数据 `dzz_usergroup_field`
--

INSERT INTO `dzz_usergroup_field` VALUES(1, 0, '', 0, 524287);
INSERT INTO `dzz_usergroup_field` VALUES(2, 0, '', 0, 524287);
INSERT INTO `dzz_usergroup_field` VALUES(3, 0, '', 0, 524287);
INSERT INTO `dzz_usergroup_field` VALUES(4, -1, '', 0, 7);
INSERT INTO `dzz_usergroup_field` VALUES(5, -1, '', 0, 1);
INSERT INTO `dzz_usergroup_field` VALUES(6, -1, '', 0, 1);
INSERT INTO `dzz_usergroup_field` VALUES(7, -1, 'gif, jpg, jpeg, png', 0, 7);
INSERT INTO `dzz_usergroup_field` VALUES(8, -1, '', 0, 7);
INSERT INTO `dzz_usergroup_field` VALUES(9, 10240, '', 0, 524287);
INSERT INTO `dzz_usergroup_field` VALUES(10, 10240, '', 0, 229039);


--
-- 转存表中的数据 `dzz_setting`
--

INSERT INTO `dzz_setting` VALUES('attachdir', './data/attachment');
INSERT INTO `dzz_setting` VALUES('attachurl', 'data/attachment');
INSERT INTO `dzz_setting` VALUES('jspath', 'static/js/');
INSERT INTO `dzz_setting` VALUES('seccodestatus', '5');
INSERT INTO `dzz_setting` VALUES('oltimespan', '15');
INSERT INTO `dzz_setting` VALUES('imgdir', 'static/image/common');
INSERT INTO `dzz_setting` VALUES('avatarmethod', '1');
INSERT INTO `dzz_setting` VALUES('reglinkname', '立即注册');
INSERT INTO `dzz_setting` VALUES('refreshtime', '3');
INSERT INTO `dzz_setting` VALUES('regstatus', '0');
INSERT INTO `dzz_setting` VALUES('regclosemessage', '');
INSERT INTO `dzz_setting` VALUES('regname', 'register');
INSERT INTO `dzz_setting` VALUES('bbrules', '0');
INSERT INTO `dzz_setting` VALUES('bbrulesforce', '0');
INSERT INTO `dzz_setting` VALUES('bbrulestxt', '');
INSERT INTO `dzz_setting` VALUES('seccodedata', 'a:13:{s:4:"type";s:1:"0";s:5:"width";s:3:"150";s:6:"height";s:2:"34";s:7:"scatter";s:1:"0";s:10:"background";s:1:"1";s:10:"adulterate";s:1:"1";s:3:"ttf";s:1:"1";s:5:"angle";s:1:"0";s:7:"warping";s:1:"0";s:5:"color";s:1:"1";s:4:"size";s:1:"0";s:6:"shadow";s:1:"1";s:8:"animator";s:1:"1";}');
INSERT INTO `dzz_setting` VALUES('bbname', 'dzzoffice');
INSERT INTO `dzz_setting` VALUES('pwlength', '0');
INSERT INTO `dzz_setting` VALUES('strongpw', 'a:0:{}');
INSERT INTO `dzz_setting` VALUES('pwdsafety', '0');
INSERT INTO `dzz_setting` VALUES('onlinehold', '60');
INSERT INTO `dzz_setting` VALUES('timeoffset', '8');
INSERT INTO `dzz_setting` VALUES('reginput', 'a:4:{s:8:"username";s:8:"username";s:8:"password";s:8:"password";s:9:"password2";s:9:"password2";s:5:"email";s:5:"email";}');
INSERT INTO `dzz_setting` VALUES('newusergroupid', '9');
INSERT INTO `dzz_setting` VALUES('dateformat', 'Y-n-j');
INSERT INTO `dzz_setting` VALUES('timeformat', 'H:i');
INSERT INTO `dzz_setting` VALUES('userdateformat', '');
INSERT INTO `dzz_setting` VALUES('metakeywords', '');
INSERT INTO `dzz_setting` VALUES('metadescription', '');
INSERT INTO `dzz_setting` VALUES('statcode', '');
INSERT INTO `dzz_setting` VALUES('boardlicensed', '0');
INSERT INTO `dzz_setting` VALUES('leavealert', '0');
INSERT INTO `dzz_setting` VALUES('bbclosed', '0');
INSERT INTO `dzz_setting` VALUES('closedreason', '网站升级中....');
INSERT INTO `dzz_setting` VALUES('sitename', 'dzzoffice');
INSERT INTO `dzz_setting` VALUES('dateconvert', '1');

INSERT INTO `dzz_setting` VALUES('smcols', '8');
INSERT INTO `dzz_setting` VALUES('smrows', '5');
INSERT INTO `dzz_setting` VALUES('smthumb', '24');

INSERT INTO `dzz_setting` VALUES('unRunExts', 'a:16:{i:0;s:3:"exe";i:1;s:3:"bat";i:2;s:2:"sh";i:3;s:3:"dll";i:4;s:3:"php";i:5;s:4:"php4";i:6;s:4:"php5";i:7;s:4:"php3";i:8;s:3:"jsp";i:9;s:3:"asp";i:10;s:4:"aspx";i:11;s:2:"vs";i:12;s:2:"js";i:13;s:3:"htm";i:14;s:4:"html";i:15;s:3:"xml";}');
INSERT INTO `dzz_setting` VALUES('maxChunkSize', '2048000');
INSERT INTO `dzz_setting` VALUES('feed_at_depart_title', '部门');
INSERT INTO `dzz_setting` VALUES('feed_at_user_title', '同事');
INSERT INTO `dzz_setting` VALUES('feed_at_range', 'a:3:{i:9;s:1:"1";i:2;s:1:"2";i:1;s:1:"3";}');
INSERT INTO `dzz_setting` VALUES('at_range', 'a:3:{i:9;s:1:"1";i:2;s:1:"2";i:1;s:1:"3";}');
--INSERT INTO `dzz_setting` VALUES('sitecopyright', '<img alt="dzzoffice" src="dzz/images/logo.png" width="263" height="82"><div style="font-size: 16px;font-weight:bold;text-align:center;padding: 20px 0 10px 0;text-shadow: 1px 1px 1px #FFF;">dzzoffice</div><div style="font-size: 16px;font-weight:bold;text-align:center;padding: 0 0 25px 0;text-shadow:1px 1px 1px #fff">协同办公平台</div><div style="font-size: 12px;text-align:center;padding: 0 0 10px 0;text-shadow:1px 1px 1px #fff">©2012-2017 DzzOffice</div><div style="font-size: 12px;text-align:center;text-shadow:1px 1px 1px #fff">备案信息</div>');
INSERT INTO `dzz_setting` VALUES('loginset', 'a:5:{s:5:"title";s:9:"DzzOffice";s:8:"subtitle";s:18:"协同办公平台";s:10:"background";s:0:"";s:8:"template";s:1:"1";s:6:"bcolor";s:17:"rgb(58, 110, 165)";}');
INSERT INTO `dzz_setting` VALUES('privacy', 'a:1:{s:7:"profile";a:17:{s:9:"education";i:1;s:8:"realname";i:-1;s:7:"address";i:0;s:9:"telephone";i:0;s:15:"affectivestatus";i:0;s:10:"department";i:0;s:8:"birthday";i:0;s:13:"constellation";i:0;s:9:"bloodtype";i:0;s:6:"gender";i:0;s:6:"mobile";i:0;s:2:"qq";i:0;s:7:"zipcode";i:0;s:11:"nationality";i:0;s:14:"graduateschool";i:0;s:8:"interest";i:0;s:3:"bio";i:0;}}');
INSERT INTO `dzz_setting` VALUES('thumbsize', 'a:3:{s:5:"small";a:2:{s:5:"width";i:256;s:6:"height";i:256;}s:6:"middle";a:2:{s:5:"width";i:800;s:6:"height";i:600;}s:5:"large";a:2:{s:5:"width";i:1440;s:6:"height";i:900;}}');
INSERT INTO `dzz_setting` VALUES('verify', 'a:8:{i:1;a:9:{s:4:"desc";s:0:"";s:9:"available";s:1:"1";s:8:"showicon";s:1:"0";s:5:"field";a:1:{s:8:"realname";s:8:"realname";}s:8:"readonly";i:1;s:5:"title";s:12:"实名认证";s:4:"icon";s:31:"common/verify/1/verify_icon.jpg";s:12:"unverifyicon";s:0:"";s:7:"groupid";a:0:{}}i:2;a:8:{s:5:"title";s:0:"";s:4:"desc";s:0:"";s:9:"available";s:1:"0";s:8:"showicon";s:1:"0";s:8:"readonly";N;s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:7:"groupid";a:0:{}}i:3;a:8:{s:5:"title";s:0:"";s:4:"desc";s:0:"";s:9:"available";s:1:"0";s:8:"showicon";s:1:"0";s:8:"readonly";N;s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:7:"groupid";a:0:{}}i:4;a:4:{s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:9:"available";i:0;s:5:"title";s:0:"";}i:5;a:4:{s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:9:"available";i:0;s:5:"title";s:0:"";}i:6;a:4:{s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:9:"available";i:0;s:5:"title";s:0:"";}i:7;a:4:{s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:9:"available";i:0;s:5:"title";s:0:"";}s:7:"enabled";b:1;}');
INSERT INTO `dzz_setting` VALUES('systemlog_open', '1');
INSERT INTO `dzz_setting` VALUES('systemlog_setting','a:7:{s:8:"errorlog";a:3:{s:5:"title";s:12:"系统错误";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:5:"cplog";a:3:{s:5:"title";s:12:"后台访问";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:9:"deletelog";a:3:{s:5:"title";s:12:"数据删除";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:9:"updatelog";a:3:{s:5:"title";s:12:"数据更新";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:8:"loginlog";a:3:{s:5:"title";s:12:"用户登录";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:8:"sendmail";a:3:{s:5:"title";s:12:"邮件发送";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:8:"otherlog";a:3:{s:5:"title";s:12:"其他信息";s:7:"is_open";i:1;s:8:"issystem";i:1;}}');

INSERT INTO `dzz_setting` VALUES('fileVersion', '1');
INSERT INTO `dzz_setting` VALUES('fileVersionNumber', '50');
--
-- 转存表中的数据 `dzz_imagetype`
--

INSERT INTO `dzz_imagetype` VALUES(1, 1, '默认', 'smiley', 0, 'dzz');


--
-- 转存表中的数据 `dzz_smiley`
--
INSERT INTO `dzz_smiley` (`typeid`, `displayorder`, `type`, `code`, `url`, `title`, `id`) VALUES
(1, 39, 'smiley', '{hot}', 'dzz-em0039.png', '', 1),
(1, 14, 'smiley', '{gl}', 'dzz-em0014.png', '', 2),
(1, 92, 'smiley', '{coffee}', 'dzz-em0092.png', '', 3),
(1, 85, 'smiley', '{cake}', 'dzz-em0085.png', '', 4),
(1, 29, 'smiley', '{by}', 'dzz-em0029.png', '', 5),
(1, 73, 'smiley', '{bomb}', 'dzz-em0073.png', '', 6),
(1, 24, 'smiley', '{jt}', 'dzz-em0044.png', '', 7),
(1, 18, 'smiley', '{jiong}', 'dzz-em0018.png', '', 8),
(1, 3, 'smiley', '{xk}', 'dzz-em0003.png', '', 9),
(1, 81, 'smiley', '{diamond}', 'dzz-em0081.png', '', 10),
(1, 8, 'smiley', '{ts}', 'dzz-em0008.png', '', 11),
(1, 91, 'smiley', '{countdown}', 'dzz-em0091.png', '', 12),
(1, 25, 'smiley', '{dax}', 'dzz-em0025.png', '', 13),
(1, 12, 'smiley', '{lzx}', 'dzz-em0012.png', '', 14),
(1, 80, 'smiley', '{bug}', 'dzz-em0080.png', '', 15),
(1, 69, 'smiley', '{clap}', 'dzz-em0069.png', '', 16),
(1, 98, 'smiley', '{rainbow}', 'dzz-em0098.png', '', 17),
(1, 56, 'smiley', '{devil}', 'dzz-em0056.png', '', 18),
(1, 66, 'smiley', '{great}', 'dzz-em0066.png', '', 19),
(1, 88, 'smiley', '{pig}', 'dzz-em0088.png', '', 20),
(1, 28, 'smiley', '{bz}', 'dzz-em0028.png', '', 21),
(1, 6, 'smiley', '{xh}', 'dzz-em0006.png', '', 22),
(1, 19, 'smiley', '{ks}', 'dzz-em0019.png', '', 23),
(1, 16, 'smiley', '{bygl}', 'dzz-em0016.png', '', 24),
(1, 87, 'smiley', '{dog}', 'dzz-em0087.png', '', 25),
(1, 36, 'smiley', '{hok}', 'dzz-em0036.png', '', 26),
(1, 15, 'smiley', '{dgl}', 'dzz-em0015.png', '', 27),
(1, 32, 'smiley', '{qsj}', 'dzz-em0032.png', '', 28),
(1, 9, 'smiley', '{hp}', 'dzz-em0009.png', '', 29),
(1, 86, 'smiley', '{hamburger}', 'dzz-em0086.png', '', 30),
(1, 65, 'smiley', '{pray}', 'dzz-em0065.png', '', 31),
(1, 70, 'smiley', '{well}', 'dzz-em0070.png', '', 32),
(1, 99, 'smiley', '{SOS}', 'dzz-em0099.png', '', 33),
(1, 76, 'smiley', '{lollipop}', 'dzz-em0076.png', '', 34),
(1, 48, 'smiley', '{horrible}', 'dzz-em0048.png', '', 35),
(1, 47, 'smiley', '{kss}', 'dzz-em0047.png', '', 36),
(1, 45, 'smiley', '{ill}', 'dzz-em0045.png', '', 37),
(1, 62, 'smiley', '{mke}', 'dzz-em0062.png', '', 38),
(1, 50, 'smiley', '{ttt}', 'dzz-em0050.png', '', 39),
(1, 17, 'smiley', '{wdj}', 'dzz-em0017.png', '', 40),
(1, 53, 'smiley', '{eye}', 'dzz-em0053.png', '', 41),
(1, 22, 'smiley', '{cry}', 'dzz-em0022.png', '', 42),
(1, 100, 'smiley', '{ban}', 'dzz-em0100.png', '', 43),
(1, 31, 'smiley', '{re}', 'dzz-em0031.png', '', 44),
(1, 49, 'smiley', '{xx}', 'dzz-em0049.png', '', 45),
(1, 1, 'smiley', '{wx}', 'dzz-em0001.png', '', 46),
(1, 54, 'smiley', '{mask}', 'dzz-em0054.png', '', 47),
(1, 95, 'smiley', '{flower}', 'dzz-em0095.png', '', 48),
(1, 40, 'smiley', '{jq}', 'dzz-em0040.png', '', 49),
(1, 37, 'smiley', '{kiss}', 'dzz-em0037.png', '', 50),
(1, 23, 'smiley', '{oh}', 'dzz-em0023.png', '', 51),
(1, 46, 'smiley', '{kun}', 'dzz-em0046.png', '', 52),
(1, 5, 'smiley', '{kh}', 'dzz-em0005.png', '', 53),
(1, 41, 'smiley', '{breath}', 'dzz-em0041.png', '', 54),
(1, 33, 'smiley', '{aom}', 'dzz-em0033.png', '', 55),
(1, 94, 'smiley', '{heart}', 'dzz-em0094.png', '', 56),
(1, 11, 'smiley', '{cool}', 'dzz-em0011.png', '', 57),
(1, 78, 'smiley', '{baby}', 'dzz-em0078.png', '', 58),
(1, 90, 'smiley', '{chick}', 'dzz-em0090.png', '', 59),
(1, 38, 'smiley', '{bl}', 'dzz-em0038.png', '', 60),
(1, 51, 'smiley', '{sleep}', 'dzz-em0051.png', '', 61),
(1, 71, 'smiley', '{come on}', 'dzz-em0071.png', '', 62),
(1, 74, 'smiley', '{champion}', 'dzz-em0074.png', '', 63),
(1, 68, 'smiley', '{ok}', 'dzz-em0068.png', '', 64),
(1, 30, 'smiley', '{yme}', 'dzz-em0030.png', '', 65),
(1, 55, 'smiley', '{haha}', 'dzz-em0055.png', '', 66),
(1, 27, 'smiley', '{dle}', 'dzz-em0027.png', '', 67),
(1, 55, 'smiley', '{happy}', 'dzz-em0007.png', '', 68),
(1, 72, 'smiley', '{sunflower}', 'dzz-em0072.png', '', 69),
(1, 10, 'smiley', '{love}', 'dzz-em0010.png', '', 70),
(1, 63, 'smiley', '{mke}', 'dzz-em0063.png', '', 71),
(1, 2, 'smiley', '{hx}', 'dzz-em0002.png', '', 72),
(1, 21, 'smiley', '{yt}', 'dzz-em0021.png', '', 73),
(1, 67, 'smiley', '{vv}', 'dzz-em0067.png', '', 74),
(1, 75, 'smiley', '{crown}', 'dzz-em0075.png', '', 75),
(1, 57, 'smiley', '{anger}', 'dzz-em0057.png', '', 76),
(1, 4, 'smiley', '{dx}', 'dzz-em0004.png', '', 77),
(1, 26, 'smiley', '{yun}', 'dzz-em0026.png', '', 78),
(1, 84, 'smiley', '{Halloween}', 'dzz-em0084.png', '', 79),
(1, 42, 'smiley', '{sweat}', 'dzz-em0042.png', '', 80),
(1, 97, 'smiley', '{rose}', 'dzz-em0097.png', '', 81),
(1, 13, 'smiley', '{shy}', 'dzz-em0013.png', '', 82),
(1, 20, 'smiley', '{sq}', 'dzz-em0020.png', '', 83),
(1, 52, 'smiley', '{qxx}', 'dzz-em0052.png', '', 84),
(1, 83, 'smiley', '{gift}', 'dzz-em0083.png', '', 85),
(1, 34, 'smiley', '{fd}', 'dzz-em0034.png', '', 86),
(1, 43, 'smiley', '{wjs}', 'dzz-em0043.png', '', 87),
(1, 24, 'smiley', '{jy}', 'dzz-em0024.png', '', 88),
(1, 89, 'smiley', '{skull}', 'dzz-em0089.png', '', 89),
(1, 77, 'smiley', '{beer}', 'dzz-em0077.png', '', 90),
(1, 96, 'smiley', '{tortoise}', 'dzz-em0096.png', '', 91),
(1, 61, 'smiley', '{mkm}', 'dzz-em0061.png', '', 92),
(1, 60, 'smiley', '{xmas}', 'dzz-em0060.png', '', 93),
(1, 35, 'smiley', '{un}', 'dzz-em0035.png', '', 94),
(1, 58, 'smiley', '{broken}', 'dzz-em0058.png', '', 95),
(1, 79, 'smiley', '{ghost}', 'dzz-em0079.png', '', 96),
(1, 82, 'smiley', '{cheer}', 'dzz-em0082.png', '', 97),
(1, 59, 'smiley', '{sun}', 'dzz-em0059.png', '', 98),
(1, 93, 'smiley', '{qq}', 'dzz-em0093.png', '', 99),
(1, 64, 'smiley', '{angel}', 'dzz-em0064.png', '', 100);

--
-- 转存表中的数据 `dzz_user_profile_setting`
--

INSERT INTO dzz_user_profile_setting VALUES('realname', 1, 0, 0, '真实姓名', '', 1, 0, 1, 0, 0, 0, 1, 'text', 0, '',0, '', 0);
INSERT INTO dzz_user_profile_setting VALUES('gender', 1, 0, 0, '性别', '', 5, 0, 0, 0, 0, 0, 0, 'select', 0, '', 0,'', 0);
INSERT INTO dzz_user_profile_setting VALUES('birthyear', 1, 0, 0, '出生年份', '', 2, 0, 0, 0, 0, 0, 0, 'select', 0, '',0, '', 0);
INSERT INTO dzz_user_profile_setting VALUES('birthmonth', 1, 0, 0, '出生月份', '', 2, 0, 0, 0, 0, 0, 0, 'select', 0, '',0, '', 0);
INSERT INTO dzz_user_profile_setting VALUES('birthday', 1, 0, 0, '生日', '', 2, 0, 0, 0, 0, 0, 0, 'select', 0, '', 0,'', 0);
INSERT INTO dzz_user_profile_setting VALUES('constellation', 0, 0, 0, '星座', '星座(根据生日自动计算)', 2, 0, 0, 0, 0, 0, 0, 'text', 0, '', 0,'', 0);
INSERT INTO dzz_user_profile_setting VALUES('zodiac', 0, 0, 0, '生肖', '生肖(根据生日自动计算)', 3, 0, 0, 0, 0, 0, 0, 'text', 0, '',0, '', 0);
INSERT INTO dzz_user_profile_setting VALUES('telephone', 0, 0, 0, '固定电话', '', 11, 0, 0, 0, 0, 0, 0, 'text', 0, '',0, '', 0);
INSERT INTO dzz_user_profile_setting VALUES('mobile', 0, 0, 0, '手机', '', 7, 0, 0, 0, 0, 0, 0, 'text', 0, '', 0,'', 0);
INSERT INTO dzz_user_profile_setting VALUES('address', 1, 0, 0, '地址', '', 11, 0, 0, 0, 0, 0, 0, 'text', 0, '',0, '', 0);
INSERT INTO dzz_user_profile_setting VALUES('zipcode', 0, 0, 0, '邮编', '', 12, 0, 0, 0, 0, 0, 0, 'text', 0, '',0, '', 0);
INSERT INTO dzz_user_profile_setting VALUES('nationality', 0, 0, 0, '国籍', '', 13, 0, 0, 0, 0, 0, 0, 'text', 0, '',0, '', 0);
INSERT INTO dzz_user_profile_setting VALUES('graduateschool', 1, 0, 0, '毕业学校', '', 15, 0, 0, 0, 0, 0, 0, 'text', 0, '', 0,'', 0);
INSERT INTO dzz_user_profile_setting VALUES('education', 1, 0, 0, '学历', '', 14, 0, 0, 0, 0, 0, 0, 'select', 0, '博士\n硕士\n本科\n专科\n中学\n小学\n其它',0, '', 0);
INSERT INTO dzz_user_profile_setting VALUES('affectivestatus', 1, 0, 0, '婚姻状况', '', 6, 0, 0, 0, 0, 0, 0, 'select', 0, '保密\n未婚\n已婚',0, '', 0);
INSERT INTO dzz_user_profile_setting VALUES('bloodtype', 0, 0, 0, '血型', '', 4, 0, 0, 0, 0, 0, 0, 'select', 0, 'A\nB\nAB\nO\n其它',0, '', 0);
INSERT INTO dzz_user_profile_setting VALUES('department', 1, 0, 1, '所属部门', '', 0, 0, 1, 0, 0, 0, 0, 'department', 0, '', 0,'', 0);
INSERT INTO dzz_user_profile_setting VALUES('qq', 1, 0, 0, 'QQ', '', 9, 0, 0, 0, 0, 0, 0, 'text', 0, '', 0,'', 0);
INSERT INTO dzz_user_profile_setting VALUES('skype', 1, 0, 0, 'skype', '', 10, 0, 0, 0, 0, 0, 0, 'text', 0, '', 0,'', 0);
INSERT INTO dzz_user_profile_setting VALUES('bio', 1, 0, 0, '自我介绍', '', 17, 0, 0, 0, 0, 0, 0, 'textarea', 0, '',0, '', 0);
INSERT INTO dzz_user_profile_setting VALUES('interest', 0, 0, 0, '兴趣爱好', '', 16, 0, 0, 0, 0, 0, 0, 'textarea', 0, '',0, '', 0);


--
-- 转存表中的数据 `dzz_user_field`
--

INSERT INTO `dzz_user_field` (`uid`, `docklist`, `screenlist`, `applist`, `noticebanlist`, `iconview`, `iconposition`, `direction`, `autolist`, `taskbar`, `dateline`, `updatetime`, `attachextensions`, `maxattachsize`, `usesize`, `addsize`, `buysize`, `wins`, `perm`, `privacy`) VALUES
(1, '', '', '1,10', '', 2, 0, 0, 1, 'bottom', 0, 0, '-1', -1, 0, 0, 0, '', 0, '');

INSERT INTO `dzz_resources_permgroup` (`id`, `pername`, `perm`, `off`, `default`, `system`) VALUES
(1, '只读', 7, 0, 1, 1),
(2, '只写', 10243, 0, 0, 1),
(3, '完全控制', 798719, 0, 0, 1),
(4, '仅下载', 1927, 0, 0, 1),
(5, '读写1', 10927, 0, 0, 1),
(6, '读写2', 12271, 0, 0, 1),
(7, '读写3', 12263, 0, 0, 1);

INSERT INTO `dzz_folder_flag` (`flag`, `fsperm`, `perm`, `iconview`, `disp`) VALUES
('home',	0,	0,	1,	0),
('folder',	0,	0,	1,	0),
('app',	0,	7,	1,	0),
('organization',	0,	7,	1,	0);
