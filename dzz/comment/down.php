<?php
/*
 * 下载
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$qid = intval($_GET['qid']);
if (!$qid) {
    topshowmessage(lang('parameter_error'));
}
$attach = C::t('comment_attach')->fetch($qid);
if (!$attach || !$attach['aid']) {
    topshowmessage(lang('attachment_not_exist'));
}
//更新下载数量
C::t('comment_attach')->update($attach['qid'], ['downloads' => $attach['downloads'] + 1]);
IO::download('attach::' . $attach['aid'], $attach['title']);

