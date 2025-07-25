<?php

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class helper_page {


    public static function multi($num, $perpage, $curpage, $mpurl, $classname = '', $maxpages = 0, $page = 5, $autogoto = FALSE, $simple = FALSE, $jsfunc = FALSE) {
        global $_G;
        $num = max(0, intval($num));
        $perpage = max(1, intval($perpage));
        $curpage = max(1, intval($curpage));
        $ajaxtarget = !empty($_GET['ajaxtarget']) ? " ajaxtarget=\"" . dhtmlspecialchars($_GET['ajaxtarget']) . "\" " : '';

        $a_name = '';
        if (strpos($mpurl, '#') !== FALSE) {
            $a_strs = explode('#', $mpurl);
            $mpurl = $a_strs[0];
            $a_name = '#' . $a_strs[1];
        }
        if ($jsfunc !== FALSE) {
            $mpurl = 'javascript:' . $mpurl;
            $a_name = $jsfunc;
            $pagevar = '';
        } else {
            $pagevar = 'page=';
        }

        $shownum = $showkbd = TRUE;
        $showpagejump = TRUE;
//			$lang['prev'] = '&lsaquo;&lsaquo;';
//			$lang['next'] = '&rsaquo;&rsaquo;';
        $shownum = true;
        $showkbd = FALSE;

        if (defined('IN_MOBILE') && !defined('TPL_DEFAULT')) {
            $dot = '..';
            $page = intval($page) < 10 && intval($page) > 0 ? $page : 4;
        } else {
            $dot = '...';
        }
        $multipage = '';
        if ($jsfunc === FALSE) {
            $mpurl .= strpos($mpurl, '?') !== FALSE ? '&' : '?';
        }

        $realpages = 1;
        $_G['page_next'] = 0;
        $page -= strlen($curpage) - 1;
        if ($page <= 0) {
            $page = 1;
        }
        if ($num > $perpage) {

            $offset = floor($page * 0.5);

            $realpages = @ceil($num / $perpage);
            $curpage = $curpage > $realpages ? $realpages : $curpage;
            $pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;

            if ($page > $pages) {
                $from = 1;
                $to = $pages;
            } else {
                $from = $curpage - $offset;
                $to = $from + $page - 1;
                if ($from < 1) {
                    $to = $curpage + 1 - $from;
                    $from = 1;
                    if ($to - $from < $page) {
                        $to = $page;
                    }
                } elseif ($to > $pages) {
                    $from = $pages - $page + 1;
                    $to = $pages;
                }
            }
            $_G['page_next'] = $to;
            $multipage = ($curpage - $offset > 1 && $pages > $page ? '<li class="page-item"><a href="' . (self::mpurl($mpurl, $pagevar, 1)) . ($ajaxtarget && $autogoto ? '#' : $a_name) . '" title="第一页" class="page-link first"' . $ajaxtarget . '>1 ' . $dot . '</a></li>' : '') .
                ($curpage > 1 && !$simple ? '<li class="page-item"><a href="' . (self::mpurl($mpurl, $pagevar, $curpage - 1)) . ($ajaxtarget && $autogoto ? '#' : $a_name) . '" class="page-link"' . $ajaxtarget . ' title="上一页">«</a></li>' : '');
            for ($i = $from; $i <= $to; $i++) {
                $multipage .= $i == $curpage ? '<li class="page-item active"><a class="page-link" title="第' . $i . '页">' . $i . '</strong></a>' :
                    '<li class="page-item"><a href="' . (self::mpurl($mpurl, $pagevar, $i)) . ($ajaxtarget && $autogoto ? '#' : $a_name) . '"' . $ajaxtarget . ' class="page-link" title="第' . $i . '页">' . $i . '</a></li>';
            }

            $wml = defined('IN_MOBILE') && IN_MOBILE == 3;
            $jsurl = '';
            if (($showpagejump || $showkbd) && !$simple && !$ajaxtarget && !$wml) {
                $jsurl = $mpurl . (strpos($mpurl, '{page}') !== false ? '\'.replace(\'{page}\', this.value == 1 ? \'\' : this.value)' : $pagevar . '\'+this.value;') . '; doane(event);';
            }

            $multipage .= ($to < $pages ? '<li class="page-item"><a href="' . (self::mpurl($mpurl, $pagevar, $pages)) . $a_name . '" title="最后一页" class="page-link last"' . $ajaxtarget . '>' . $dot . ' ' . $realpages . '</a></li>' : '') .
                ($showpagejump && !$simple && !$ajaxtarget && !$wml ? '<li class="page-item"><a class="page-link"><div class="input-group" title="跳转页数，共' . $lang['total'] . ' ' . $pages . ' ' . $lang['pageunit'] . '页"><input  type="text" name="custompage"  class="form-control height-24" style="width: 45px;" title="' . $lang['pagejumptip'] . '" value="' . $curpage . '" onkeydown="if(event.keyCode==13) {window.location=\'' . $jsurl . '}" /><span class="input-group-text"> / ' . $pages . ' ' . $lang['pageunit'] . '</span></div></a></li>' : '') .
                ($curpage < $pages && !$simple ? '<li><a href="' . (self::mpurl($mpurl, $pagevar, $curpage + 1)) . ($ajaxtarget && $autogoto ? '#' : $a_name) . '" class="page-link
"' . $ajaxtarget . ' title="下一页">»</a></li>' : '') .
                ($showkbd && !$simple && $pages > $page && !$ajaxtarget && !$wml ? '<li><kbd><input type="text" name="custompage" size="3" onkeydown="if(event.keyCode==13) {window.location=\'' . $jsurl . '}" /></kbd></li>' : '');

            $multipage = $multipage ? '<ul class="pagination ' . ($classname ? $classname : '') . '">' . $multipage . ($shownum && !$simple ? '<li class="disable"><a class="page-link" title="共有' . $num . '条记录">' . $num . '</a></li>' : '') . '</ul>' : '';
        }
        $maxpage = $realpages;
        return $multipage;
    }

    public static function mpurl($mpurl, $pagevar, $page) {
        if (strpos($mpurl, '{page}') !== false) {
            return trim(str_replace('{page}', ($page == 1 ? '' : $page), $mpurl), '?');
        } else {
            $separator = '';
            if ($pagevar[0] !== '&' && $pagevar[0] !== '?') {
                if (strpos($mpurl, '?') !== FALSE) {
                    $separator = '';
                } else {
                    $separator = '?';
                }
            }
            return $mpurl . $separator . $pagevar . $page;
        }
    }

    public static function simplepage($num, $perpage, $curpage, $mpurl) {
        $return = '';
        $lang['next'] = lang('nextpage');
        $lang['prev'] = lang('prevpage');
        $next = $num == $perpage ? '<a href="' . (self::mpurl($mpurl, '&page=', $curpage + 1)) . '" class="nxt">' . $lang['next'] . '</a>' : '';
        $prev = $curpage > 1 ? '<span class="pgb"><a href="' . (self::mpurl($mpurl, '&page=', $curpage - 1)) . '">' . $lang['prev'] . '</a></span>' : '';
        if ($next || $prev) {
            $return = '<div class="pg">' . $prev . $next . '</div>';
        }
        return $return;
    }
}

?>