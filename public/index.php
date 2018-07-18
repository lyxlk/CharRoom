<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// [ 应用入口文件 ]
header("Content-type:text/html;charset=utf-8");
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
define('EXTEND_PATH', __DIR__ .'/../extend/');

//定义配置目录
define('CONF_PATH', APP_PATH.'config/');

//定义环境 0：开发 1：测试 2：正式
defined('ENVID') or define('ENVID', 0);

// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
