<?php
/*
 * onlyoffice
 * @copyright   https://www.onlyoffice.com
 * @gitee       https://gitee.com/xiaohu2024
 * @author      小胡(3203164629@qq.com)
 */
if (!defined('IN_DZZ') && !defined('IN_ADMIN')) {
    exit('Access Denied');
}
Hook::listen('adminlogin');
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
$op = "admin";
$navtitle = "配置- " . lang('appname');
$extra = unserialize($global_appinfo['extra']);
if ($do == 'save') {
    if (submitcheck('confirmsubmit')) {
        $DocumentUrl = isset($_GET['DocumentUrl']) ? trim($_GET['DocumentUrl']) : '';
        if (empty($DocumentUrl)) {
            exit(json_encode(array('msg' => lang('onlyoffice_url_setfailed'))));
        }
        if (!preg_match('/^(http|https):\/\/[^ "]+$/', $DocumentUrl)) {
            exit(json_encode(array('msg' => 'OnlyOffice 服务地址为无效的 URL 地址')));
        }
        if (preg_match('/localhost(?::\d+)?/', $DocumentUrl)) {
            exit(json_encode(array('msg' => 'OnlyOffice 服务地址不能设置为 localhost')));
        }
        $DzzHost = isset($_GET['DzzHost']) ? trim($_GET['DzzHost']) : '';
        if ($DzzHost) {
            if (substr($DzzHost, -1) !== '/') {
                exit(json_encode(array('msg' => 'DzzOffice 内网地址必须以 "/" 结尾')));
            }
            if (!preg_match('/^(http|https):\/\/[^ "]+$/', $DzzHost)) {
                exit(json_encode(array('msg' => 'DzzOffice 内网地址为无效的 URL 地址')));
            }
            if (preg_match('/localhost(?::\d+)?/', $DzzHost)) {
                exit(json_encode(array('msg' => 'DzzOffice 内网地址不能设置为 localhost')));
            }
        }
        $oolanurl = isset($_GET['oolanurl']) ? trim($_GET['oolanurl']) : '';
        if($oolanurl) {
            if (!preg_match('/^(http|https):\/\/[^ "]+$/', $oolanurl)) {
                exit(json_encode(array('msg' => '使用Office地址为无效的 URL 地址')));
            }
            if (preg_match('/localhost(?::\d+)?/', $oolanurl)) {
                exit(json_encode(array('msg' => '使用Office地址不能设置为 localhost')));
            }
        }
        $extra["DocumentUrl"] = $DocumentUrl;
        $extra["oolanurl"] = $oolanurl;
        $extra["DzzHost"] = $DzzHost;
        $extra["appViewMode"] = isset($_GET['appViewMode']) ? trim($_GET['appViewMode']) : '';
        $extra["exts"] = isset($_GET['exts']) ? trim($_GET['exts']) : '';
        $extra["token"] = isset($_GET['token']) ? trim($_GET['token']) : '';
        $extra["canCoAuthoring"] = isset($_GET['canCoAuthoring']) ? trim($_GET['canCoAuthoring']) : '';
        $extra["mobileEdit"] = isset($_GET['mobileEdit']) ? trim($_GET['mobileEdit']) : '';
        $extra["forcesave"] = isset($_GET['forcesave']) ? $_GET['forcesave'] : '';
        $extra["OnlyRead"] = isset($_GET['OnlyRead']) ? $_GET['OnlyRead'] : '';
        $extra["canCopy"] = isset($_GET['canCopy']) ? $_GET['canCopy'] : '';
        $extra["logo"] = isset($_GET['logo']) ? trim($_GET['logo']) : '';
        $modifyFilter = isset($_GET['modifyFilter']) ? trim($_GET['modifyFilter']) : '';
        if (in_array($modifyFilter, ['1', '2', '3'])) {
            $extra["modifyFilter"] = $modifyFilter;
        } else {
            $extra["modifyFilter"] = 2;
        }
        $extra["autosave"] = isset($_GET['autosave']) ? $_GET['autosave'] : '';
        $extra["chat"] = isset($_GET['chat']) ? $_GET['chat'] : '';
        $extra["compactHeader"] = isset($_GET['compactHeader']) ? $_GET['compactHeader'] : '';
        $extra["compactToolbar"] = isset($_GET['compactToolbar']) ? $_GET['compactToolbar'] : '';
        $extra["comments"] = isset($_GET['comments']) ? $_GET['comments'] : '';
        $extra["macros"] = isset($_GET['macros']) ? $_GET['macros'] : '';
        $extra["debug"] = isset($_GET['debug']) ? $_GET['debug'] : '';
        $extra["macrosMode"] = isset($_GET['macrosMode']) ? $_GET['macrosMode'] : '';
        $extra["plugins"] = isset($_GET['plugins']) ? $_GET['plugins'] : '';
        $extra["toolbarNoTabs"] = isset($_GET['toolbarNoTabs']) ? $_GET['toolbarNoTabs'] : '';
        $extra["coEditing"] = isset($_GET['coEditing']) ? $_GET['coEditing'] : '';
        $extra["coEditingchange"] = isset($_GET['coEditingchange']) ? $_GET['coEditingchange'] : '';
        $extra["feedback"] = isset($_GET['feedback']) ? $_GET['feedback'] : '';
        $extra["watermarkTxt"] = isset($_GET['watermarkTxt']) ? trim($_GET['watermarkTxt']) : '';
        $extra["standardView"] = isset($_GET['standardView']) ? $_GET['standardView'] : '';
        $extra["is"] = 1;
        C::t("app_market")->update($global_appinfo['appid'], array("extra" => serialize($extra)));
        exit(json_encode(array('success' => '保存成功')));
    } else {
        exit(json_encode(array('msg' => '非法操作')));
    }
} elseif ($do == 'check') {
    $navtitle = "检测服务器- " . lang('appname');
    if ($extra["DzzHost"]) {
        $dzzHost = $extra["DzzHost"];
    } else {
        $dzzHost = $_G['siteurl'];
    }
    if ($extra['DocumentUrl']) {
        $oolanurl = $extra['DocumentUrl'];
    }
    if ($extra['DzzHost'] && $extra['oolanurl']) {
        if ($_G['siteurl'] == $extra['DzzHost']) {
            $oolanurl = $extra['oolanurl'];
        }
    }
    $oolanurl = rtrim($oolanurl, '/');
    include template('check');
} elseif ($do == 'fileSave') {
    $oolanurl = get_oo_url($extra);
    $result = curl_file_get_contents($oolanurl . '/sdkjs/common/AllFonts.js', 5);
    if ($result && strlen($result) > 5000) {
        exit('success');
    }
} elseif ($do == 'officeServer') {
    $oolanurl = get_oo_url($extra);
    $result = curl_file_get_contents($oolanurl . '/healthcheck');
    if ($result == 'true') {
        exit('success');
    }
} elseif ($do == 'officeversion') {
    try {
        $oolanurl = get_oo_url($extra);
        $url = $oolanurl . '/coauthoring/CommandService.ashx';
        $data = [
            "c" => 'version'
        ];
        if ($extra['token']) {
            require_once(DZZ_ROOT . MOD_PATH . '/jwt/jwtmanager.php');
            $data["token"] = jwtEncode($data, $extra['token']);
        }
        $data = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response === false) {
            exit('请求ONLYOFFICE服务器失败。');
        }
        $result = json_decode($response, true);
        if (isset($result['error'])) {
            if($result['error'] == 0) {
                if (!$result['version']) {
                    exit('版本未获取。');
                }
                if ($result['version'] > 7.3) {
                    exit('success');
                } elseif ($result['version']) {
                    exit('版本不支持，请使用7.3以上版本，当前版本：' . $result['version']);
                }
            } else {
                $errorMessages = [
                    1 => '文档密钥缺失或找不到具有该密钥的文档',
                    2 => '回调url不正确。',
                    3 => '内部服务器错误',
                    4 => '在收到 forcesave 命令之前，未对文档应用任何更改。',
                    5 => '命令不正确',
                    6 => '令牌无效'
                ];
                $error = $errorMessages[$result['error']] ?? '未知错误';
                exit($error);
            }
        } else {
            exit('未知错误。');
        }
    } catch (Exception $e) {
        exit('无法访问指定的URL。' . $e->getMessage());
    }
} elseif ($do == 'license') {
    try {
        $oolanurl = get_oo_url($extra);
        $url = $oolanurl . '/coauthoring/CommandService.ashx';
        $data = [
            "c" => 'license'
        ];
        if ($extra['token']) {
            require_once(DZZ_ROOT . MOD_PATH . '/jwt/jwtmanager.php');
            $data["token"] = jwtEncode($data, $extra['token']);
        }
        $data = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response === false) {
            exit('<div class="error">请求ONLYOFFICE服务器失败，使用的OnlyOffice 地址是：'.$oolanurl.'</div>');
        }
        
        $result = json_decode($response, true);
        $html = '';
        if (isset($result['error'])) {
            if($result['error'] == 0) {
                if (isset($result['server'])) {
                    $server = $result['server'];
                    $statusMap = [
                        1 => '出现错误',
                        2 => '许可证已过期',
                        3 => '许可证仍有效',
                        6 => '试用许可证已过期'
                    ];
                    $html .= '<tr><td>许可证状态</td><td>' . ($statusMap[$server['resultType']] ?? '未知状态') . '</td></tr>';
                    
                    // 产品版本
                    $packageMap = [
                        0 => '开源产品',
                        1 => '企业版',
                        2 => '开发者版'
                    ];
                    $html .= '<tr><td>产品版本</td><td>' . 
                        ($packageMap[$server['packageType']] ?? '未知版本') . '</td></tr>';
                    
                    $html .= '<tr><td>构建日期</td><td>' . $server['buildDate'] . '</td></tr>';
                    $html .= '<tr><td>构建版本</td><td>' . $server['buildVersion'] . '</td></tr>';
                }
                if (isset($result['license'])) {
                    $license = $result['license'];
                    
                    $html .= '<tr><td>过期日期</td><td>' . ($license['end_date'] ?? '永久有效') . '</td></tr>';
                    $html .= '<tr><td>试用版本</td><td>' . ($license['trial'] ? '是' : '否') . '</td></tr>';
                    $html .= '<tr><td>定制版本</td><td>' . ($license['customization'] ? '是' : '否') . '</td></tr>';
                    
                    if (isset($license['connections'])) {
                        $html .= '<tr><td>最大连接数</td><td>' . $license['connections'] . '</td></tr>';
                    }
                    if (isset($license['connections_view'])) {
                        $html .= '<tr><td>查看器连接数</td><td>' . $license['connections_view'] . '</td></tr>';
                    }
                }
            } else {
                $errorMessages = [
                    1 => '文档密钥缺失或找不到具有该密钥的文档',
                    2 => '回调url不正确。',
                    3 => '内部服务器错误',
                    4 => '在收到 forcesave 命令之前，未对文档应用任何更改。',
                    5 => '命令不正确',
                    6 => '令牌无效'
                ];
                $error = $errorMessages[$result['error']] ?? '未知错误';
            }
        }
        include template('license');
        exit();
    } catch (Exception $e) {
        exit('<div class="error">获取ONLYOFFICE信息时出错: ' . htmlspecialchars($e->getMessage()) . '</div>');
    }
} else {
    if (!$extra["is"]) {
        $extra['canCoAuthoring'] = 1;
        $extra['mobileEdit'] = 1;
        $extra['forcesave'] = 1;
        $extra['autosave'] = 1;
        $extra['chat'] = 3;
        $extra['canCopy'] = 1;
        $extra['plugins'] = 1;
        $extra['comments'] = 3;
        $extra['modifyFilter'] = 2;
    }
    include template('admin');
}
?>