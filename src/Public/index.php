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

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');

define('PATH_SELF',__DIR__ . '/application/');
//define("FILE_PATH", __DIR__ .'/public/DownloadUrl');
define("FILE_PATH", __DIR__ .'/PjrFiles/');
//define("SET_URL","http://120.25.74.178");
define("SET_URL","http://120.25.74.178");
define("SET_URLS","http://120.25.74.178");
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
