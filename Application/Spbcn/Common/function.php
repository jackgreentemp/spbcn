<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 后台公共文件
 * 主要定义后台公共函数库
 */

/* 解析列表定义规则*/

function mb_unserialize($serial_str) {
    $serial_str= preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $serial_str );
    $serial_str= str_replace("\r", "", $serial_str);
    return unserialize($serial_str);
}

// ascii
function asc_unserialize($serial_str) {
    $serial_str = preg_replace('!s:(\d+):"(.*?)";!se', '"s:".strlen("$2").":\"$2\";"', $serial_str );
    $serial_str= str_replace("\r", "", $serial_str);
    return unserialize($serial_str);
}

function get_allfiles($path,&$files) {
    if(is_dir($path)){
        $dp = dir($path);
        while ($file = $dp ->read()){
            if($file !="." && $file !=".."){
                get_allfiles($path."/".$file, $files);
            }
        }
        $dp ->close();
    }
    if(is_file($path)){
        $files[] =  $path;
    }
}
function get_allDirs($path,&$files) {
    if(is_dir($path)){
        $files[] =  $path;
        $dp = dir($path);
        while ($file = $dp ->read()){
            if($file !="." && $file !=".."){
                get_allDirs($path."/".$file, $files);
            }
        }
        $dp ->close();
    }
}

function get_filenamesbydir($dir){
    $files =  array();
    get_allfiles($dir,$files);
    return $files;
}
function get_filesDir($dir){
    $files =  array();
    get_allDirs($dir,$files);
    return $files;
}
