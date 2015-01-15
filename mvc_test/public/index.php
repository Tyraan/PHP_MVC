<?php
/**
 *  一个 简单 的mvc
 */

include_once(dirname(__FILE__).'/../apps/common.php');



// action过滤数组
$allowActions = array('index', 'product', 'category');
// 获取变量
$do = (!empty($_GET['c'])) ? $_GET['c'] : 'index';
$action = (!empty($_GET['a'])) ? $_GET['a']:'main';
// 路由
if(in_array($do, $allowActions)) {
	$class = $do.'Controller';
    // 加载路由

    $controller = loadController($class);
    $controller->$action();
    
}





