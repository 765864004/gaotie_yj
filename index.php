<?php
ini_set("memory_limit","3000M");
define('APP_DEBUG',true);
//是否开始调试的按钮

define('APP_NAME','Project');
//定义项目的名称,项目后台就是后台的名称


//定义一个网站的硬盘根目录
define('WEBSITE_DISK_PATH',str_replace("\\","/",dirname(__FILE__)));



define('APP_PATH',dirname(__FILE__).'/Project/');
//这个前台和后台是都是使用Project项目文件夹来存放实际的项目


//加载thinkphp的框架，框架用dirname(__file__)连接字符串获得
require(dirname(__FILE__)."/ThinkPHP/ThinkPHP.php")
//前台和后台共用thinkphp的框架，但是前台和后台是两个不同的项目
?>