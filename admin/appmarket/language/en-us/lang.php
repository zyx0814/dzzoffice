<?php
$lang = array(
    'appmarket' => 'App Market',
    'appmarketedit' => 'App Management',
    'installed' => 'Installed',
    'upgrade' => 'Upgrade',
    'open_way' => 'Opening Method',
    'permission_management' => 'Permission Management',
    'system_cant_disable' => 'System applications cannot perform this operation',
    'app_newest' => 'Upgradable Apps',
    'new_edition_function' => 'New Version Features',
    'enable_file_disappear' => 'Execution script file missing for enabling the app',
    'app' => 'App',
    'ge' => 'piece(s)',
    'selected' => 'Selected',
    'update_onekey' => 'One-click Upgrade',
    'install_onekey' => 'One-click Install',
    'appname' => 'App Market',
    'no_need_update_applist' => 'No apps need updating',
    'appInLocal' => 'Local Apps',
    'app_upgrade_uninstall_successful' => 'App uninstalled successfully! {upgradeurl}',
    'app_upgrade_check_need_update2' => 'Check for new version',

    'app_upgrade_check_is_install' => 'Checking if installed...',
    'app_upgrade_check_need_update' => 'Checking for new version...',
    'app_upgrade_to_lastversion' => 'Already the latest version',
    'app_upgrade_installed' => 'This app is already installed',
    'app_upgrade_installed_local' => 'Installed local app conflicts with this app',
    'app_upgrade_identifier_error' => 'App identifier is empty',
    'app_upgrade_phpversion_error' => 'PHP version requirement: {version}',
    'app_upgrade_mysqlversion_error' => 'MySQL version requirement: {version}',
    'app_upgrade_newversion_will_start' => 'Upgrade is about to start',
    'app_upgrade_newversioninfo_error' => 'Version information is empty, please recheck for updates',
    'app_upgrade_newversion_folder_error' => 'New version directory already exists: {path}',
    'app_upgrade_newversion_start' => 'Upgrade started...',
    'app_upgrade_newversion_ing' => 'Upgrading...',

    'app_upgrade_data_error' => 'Data error, please refresh and try again',
    'app_upgrade_none' => 'Installation or update files missing {upgradeurl}', //-1
    'app_upgrade_exchange_none' => 'Verification file does not exist {upgradeurl}',//-9
    'app_upgrade_downloading' => 'Downloading update or installation files...',
    'app_upgrade_downloading_error' => 'Problem downloading file {file}, please ensure network connection and write permissions for data directory {upgradeurl}',//-3
    'app_upgrade_download_complete' => 'Update or installation files downloaded {upgradeurl}',//-2 
    'app_upgrade_download_complete_to_compare' => 'Update or installation files downloaded, will start local file comparison {upgradeurl}',
    'app_upgrade_downloading_file' => 'Downloading update file {file} from official server <br>Completed: {percent} {upgradeurl}',
    'app_upgrade_check_download_complete' => 'Checking app download integrity...',
    'app_upgrade_installing' => 'Installing app files...',
    'app_upgrade_cannot_access_file' => 'No modification permissions for directories and files. Please enter FTP account or modify file permissions to readable and writable and try again {upgradeurl}',//-4
    'app_upgrade_ftp_upload_error' => 'Error uploading file {file} via FTP. Please modify file permissions and re-upload, or reconfigure FTP account {upgradeurl}',//-6
    'app_upgrade_copy_error' => 'Error copying file {file}. Please check if original file exists, re-copy, or upload copied file via FTP {upgradeurl}',//-7
    'app_upgrade_move_success' => 'App files copied successfully... Will proceed to database installation {upgradeurl}',
    'app_upgrade_xmlfile_error' => 'App file {file} missing {upgradeurl}',//-8
    'app_upgrade_install_will_success' => 'Installation is about to complete...',
    'app_upgrade_install_success' => 'Successfully installed. Please go to App Management to launch the app {upgradeurl}',//1
    'app_upgrade_already_downloadfile' => 'Preparing to download update files...',
    'app_upgrade_backuping' => 'Backing up original files... {upgradeurl}', //2
    'app_upgrade_backup_error' => 'Error backing up original files {upgradeurl}',//-5
    'app_upgrade_backup_complete' => 'Backup completed, upgrading... {upgradeurl}',//3
    'app_upgrade_file_success' => 'File upgrade successful, will proceed to database update {upgradeurl}',//4
    'app_upgrade_database_success' => 'Database updated successfully {upgradeurl}',//5
    'app_upgrade_newversion_will_success' => 'Stage upgrade is about to complete...',
    'app_upgrade_newversion_success' => 'Upgrade successful {upgradeurl}',//1

    'application_identifier' => 'App Identifier',
    'app_application_identifier_text' => '<li>Unique app identifier, please enter an English string, usually the directory name of the corresponding app (in English).</li>
                                    <li>If a name conflict occurs during online installation, it will be automatically renamed.</li>',
    'not_empty' => 'Cannot be empty',
    'already_exist' => 'Already exists',
    'application_app_path' => 'App Path',
    'app_application_app_path_text' => '<li>Path relative to the site root directory. For example, if the thame app is located at ./dzz/thame relative to the site root, enter "dzz" in this field</li>
                                        <li>For link-type apps with no path, enter the string "link"</li>',
    'application_appadminurl' => 'Management Settings URL',
    'app_application_appadminurl_text' => '<li>Management settings URL, which can be a relative path (relative to the site root) or a network address</li>
                                        <li>App address can contain parameters such as: {dzzscript}?mod=document&op=textviewer&icoid={icoid}</li>
                                        <li>Parameters: Wrap parameters in the address with "{}". dzzscript: main program (i.e., index.php), adminscript: backend management main program (i.e., admin.php). Using these parameters when adding apps improves compatibility and portability</li>
                                        <li>All fields in the dzz_resources table can be used as parameters</li>',
    'app_delete_confirm' => 'This operation will delete all data in the app: <b>{appname}</b>. Please proceed with caution<br /><br />If you really need to delete, enter "DELETE" below to confirm',
    'app_sure_delete' => 'Confirm uninstallation',
    'app_not_delete' => 'Do not uninstall',
    'installed' => 'Installed',
    'newest' => 'Latest',
    'buy' => 'Buy',
    'view_detail' => 'View Details',
    'buy_contract' => 'Contact for Purchase',
    'manual_install_tip' => 'If online installation fails or you need to install an older version, please download the app installation package from the <a class="num" href="http://www.dzzoffice.com/index.php?mod=dzzmarket" target="_blank">official app market</a> for manual installation'
);
?>