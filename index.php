<?php
/**
 * @copyright   Copyright (c) 2014-2016 ispCMS.com All rights reserved.
 * @license     http://ispCMS/help/1.html
 * @link        http://ispCMS
 * @author      ispCMS
 */

// 检测PHP环境
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
	die('require PHP > 5.3.0 !');
}

define('APP_DEBUG', true); //是否调试//部署阶段注释或者设为false
define('APP_PATH', "./App/"); //项目路径
define('RUNTIME_PATH', './Runtime/' );
define('THINK_PATH', "./Include/");

require THINK_PATH . 'ThinkPHP.php'; //加载ThinkPHP框架
