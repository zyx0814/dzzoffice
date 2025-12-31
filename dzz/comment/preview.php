<?php
/*
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
    topshowmessage(lang('attachment_nonexistence'));
}
$shareurl = $_G['siteurl'] . 'share.php?a=view&s=' . dzzencode('attach::' . $attach['aid']) . '&n=' . rawurlencode($attach['title']);
dheader('Location:' . outputurl($shareurl));
exit();

