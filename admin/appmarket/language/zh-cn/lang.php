<?php
$lang = array (
	'appmarket' => '应用市场',
    'installed' => '已安装',
    'upgrade' => '升级',
    'open_way'=>'打开方式',
    'permission_management'=>'权限管理',
    'system_cant_disable'=>'系统应用不能使用该操作',
    'app_newest'=>'可升级应用',
    'new_edition_function'=>'新版功能',
    'enable_file_disappear'=>'应用开启执行脚本文件丢失',
	'app'=>'应用',
	'ge'=>'个',
	'selected'=>'已选择',
	'update_onekey'=>'一键升级',
	'install_onekey'=>'一键安装',
	'appname'=>'应用市场',
	'no_need_update_applist'=>'没有需要更新的应用',
	'appInLocal'=>'本地应用',
    'app_upgrade_uninstall_successful'=>'应用卸载成功! {upgradeurl}',
    'app_upgrade_check_need_update2' => '检测新版本',
    
    'app_upgrade_check_is_install' => '检测是否已安装...',
    'app_upgrade_check_need_update' => '检测新版本...',
    'app_upgrade_to_lastversion' => '已为最新版本',
    'app_upgrade_installed' => '已安装该应用',
    'app_upgrade_installed_local' => '已安装本地应用与该应用冲突',
    'app_upgrade_identifier_error' => '应用标识为空',
    'app_upgrade_dzzversion_error' => 'Dzzoffice版本要求: {version}',
    'app_upgrade_phpversion_error' => 'php版本要求: {version}',
    'app_upgrade_mysqlversion_error' => 'Mysql版本要求: {version}',
    'app_upgrade_newversion_will_start'=>'升级即将开始',
    'app_upgrade_newversioninfo_error'=>'版本信息为空,请重新检测更新',
    'app_upgrade_newversion_folder_error'=> '新版目录已存在:{path}',
    'app_upgrade_newversion_start'=> '升级开始...',
    'app_upgrade_newversion_ing'=> '正在升级...',
    
    'app_upgrade_data_error' => '数据错误请刷新后再试',
    'app_upgrade_none' => '安装或更新文件丢失 {upgradeurl}', //-1
    'app_upgrade_exchange_none' => '不存在校验文件 {upgradeurl}',//-9
    'app_upgrade_downloading' => '待更新或安装文件下载中...',
    'app_upgrade_downloading_error' => '文件 {file} 下载出现问题,请确保网络连接及data目录写入权限 {upgradeurl}',//-3
    'app_upgrade_download_complete' => '待更新或安装文件下载完成 {upgradeurl}',//-2 
    'app_upgrade_download_complete_to_compare' => '待更新或安装文件下载完成，即将进行本地文件比较 {upgradeurl}', //待修改，，，，
    'app_upgrade_downloading_file' => '正在从官方下载更新文件 {file} <br>已完成{percent} {upgradeurl}',
    'app_upgrade_check_download_complete' => '检查应用下载完整性...',
    'app_upgrade_installing' => '应用文件安装中...', 
    'app_upgrade_cannot_access_file' => '目录及文件无修改权限，请您填写 ftp 账号或修改文件权限为可读可写后重试 {upgradeurl}',//-4
    'app_upgrade_ftp_upload_error' => 'ftp上传文件 {file} 出错， 请修改文件权限后重新上传 或 重新设置ftp账号 {upgradeurl}',//-6
    'app_upgrade_copy_error' => '复制文件 {file} 出错，请检测原始文件是否存在，重新复制 或 通过ftp上传复制文件 {upgradeurl}',//-7
    'app_upgrade_move_success' => '应用文件复制完成...即将进入数据库安装 {upgradeurl}',
    'app_upgrade_xmlfile_error'=> '应用文件 {file} 丢失 {upgradeurl}',//-8
    'app_upgrade_install_will_success' => '安装即将完成...',
    'app_upgrade_install_success' => '已成功安装请到已安装列表启动该应用 {upgradeurl}',//1
    'app_upgrade_already_downloadfile' => '准备下载更新文件...',
    'app_upgrade_backuping' => '正在备份原始文件... {upgradeurl}', //2
    'app_upgrade_backup_error' => '备份原始文件出错 {upgradeurl}',//-5
    'app_upgrade_backup_complete' => '备份完成，正在进行升级... {upgradeurl}',//3
    'app_upgrade_file_success' => '文件升级成功，即将进入更新数据库 {upgradeurl}',//4
    'app_upgrade_database_success' => '更新数据库成功 {upgradeurl}',//5
    'app_upgrade_newversion_will_success' => '阶段升级即将完成...',
    'app_upgrade_newversion_success' => '升级成功 {upgradeurl}',//1
    
    'application_identifier'=>'应用标识',
	'app_application_identifier_text'=>'<li>应用唯一标识,请输入英文字母字符串,一般取用对应应用的目录名称(英文)。</li>
								<li>如通过在线安装碰到重名时会自动重命名。</li>',
    'not_empty'=>'不能为空',
    'already_exist'=>'已存在',
    'application_app_path'=>'应用路径',
	'app_application_app_path_text'=>'<li>相对于站点根目录的路径。如thame应用相对于站点根目录的路径是 ./dzz/thame,则该字段填写 dzz </li>
                                    <li>如应用为链接类型,无路径则填写字符串 link </li>',
    'application_appadminurl'=>'管理设置地址',
	'app_application_appadminurl_text'=>'<li>管理设置地址，可以是相对地址（相对于站点根目录)或网络地址</li>
									<li>应用的地址可以带有参数如：{dzzscript}?mod=document&op=textviewer&icoid={icoid}</li>
									<li>参数：将地址中的参数用"{}"包裹，dzzscript:为主程序（即index.php),adminscript:为后台管理主程序(即admin.php),添加应用时使用此参数可以增加应用的兼容性和移植性</li>
									<li>dzz_resources表中的字段都可以作为参数带入</li>',
    'app_delete_confirm'=>'此操作将删除应用：<b>{appname}</b> &nbsp;内所有数据，请慎<br /><br />如果确实需要删除，请在下面输入 DELETE 字样确认删除',
    'app_sure_delete'=>'确定卸载应用',
    'app_not_delete'=>'不卸载',
    'installed'=>'已安装',
    'newest'=>'最新',
	'buy'=>'购买',
	'view_detail'=>'查看详细',
	'buy_contract'=>'联系购买',
	'manual_install_tip'=>'注：如不能在线安装，请通过 <a class="num" href="http://www.dzzoffice.com/index.php?mod=dzzmarket" target="_blank">官方应用市场</a> 下载应用安装包手动下载'
);
?>