<?php

/**
 * ランダム文字列生成 (英数字)
 * @param $len: 生成する文字数
 */
function makeRandStr($length) {
    $str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
    $r_str = null;
    for ($i = 0; $i < $length; $i++) {
        $r_str .= $str[rand(0, count($str) - 1)];
    }
    return $r_str;
}

?>