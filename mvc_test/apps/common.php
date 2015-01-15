<?php
/**
 *  通用包含文件
 */
// 应用程序目录
define('APP_ROOT', dirname(__FILE__).'/');
define('VIEW_PATH', APP_ROOT . 'view');
define('LIB_PATH', APP_ROOT . '../libs');
define('HELPER_PATH', APP_ROOT . 'helper');

// 定义全局变量容器
$G_CONF = $G_VARS = $G_LOADED = array();

// 包含配置文件
$G_CONF = include_once(APP_ROOT . 'config/config.php');

// 常用函数
include_once(APP_ROOT .'functions.php');
// 控制器基类
loadLib('controller');


