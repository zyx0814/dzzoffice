<?php

namespace Qcloud\Cos;

function region_map($region) {
    $regionmap = array('cn-east'=>'ap-shanghai',
            'cn-south'=>'ap-guangzhou',
            'cn-north'=>'ap-beijing-1',
            'cn-south-2'=>'ap-guangzhou-2',
            'cn-southwest'=>'ap-chengdu',
            'sg'=>'ap-singapore',
            'tj'=>'ap-beijing-1',
            'bj'=>'ap-beijing',
            'sh'=>'ap-shanghai',
            'gz'=>'ap-guangzhou',
            'cd'=>'ap-chengdu',
            'sgp'=>'ap-singapore');
    if (array_key_exists($region, $regionmap)) {
        return $regionmap[$region];
    }
    return $region;
}

function encodeKey($key) {
    return str_replace('%2F', '/', rawurlencode($key));
}

function endWith($haystack, $needle) {
    $length = strlen($needle);
    if($length == 0)
    {
        return true;
    }
    return (substr($haystack, -$length) === $needle);
}
