<?php

return array(
	/*
	 * 0:普通模式 (采用传统癿URL参数模式 )
	 * 1:PATHINFO模式(http://<serverName>/appName/module/action/id/1/)
	 * 2:REWRITE模式(PATHINFO模式基础上隐藏index.php)
	 * 3:兼容模式(普通模式和PATHINFO模式, 可以支持任何的运行环境, 如果你的环境不支持PATHINFO 请设置为3)
	 */
	/* 数据库设置 */
	'DB_TYPE'               => 'sqlsrv',     // 数据库类型
	'DB_HOST'               => '192.168.0.63', // 服务器地址:
	'DB_NAME'               => 'CRH380A_YJ',  // 数据库名
	'DB_USER'               => 'sa',  // 用户名
	'DB_PWD'                => '123456789',   // 密码
	'DB_PORT'               => '1433',      // 端口
	'DB_CHARSET' 			=> 'utf8',//数据库编码
	'DB_PREFIX'             => 't_',		// 数据库表前缀
	'DB_SUFFIX'             => '',          // 数据库表后缀
	'DB_FIELDTYPE_CHECK'    => true,        // 是否进行字段类型检查

//	'DB_TYPE'               => 'mysql',     // 数据库类型
//	'DB_HOST'               => '127.0.0.1', // 服务器地址:
//	'DB_NAME'               => 'crh380a_yj',  // 数据库名
//	'DB_USER'               => 'root',  // 用户名
//	'DB_PWD'                => '123456',   // 密码
//	'DB_PORT'               => 3306,      // 端口
//	'DB_CHARSET' 			=> 'utf8',//数据库编码
//	'DB_PREFIX'             => 't_',		// 数据库表前缀
//	'DB_SUFFIX'             => '',          // 数据库表后缀
//	'DB_FIELDTYPE_CHECK'    => true,        // 是否进行字段类型检查



	//URL方面的配置
	'URL_MODEL'				=> 2,  //URL模式
	'URL_PATHINFO_DEPR'     => '/',//URL的界定符
	'URL_CASE_INSENSITIVE'	=> true,//URL是否不敏感大小写

	//大小写检查
	'APP_FILE_CASE'			=> false,
		

	//表单令牌方面的配置
	'TOKEN_ON'				=> true,//是否开启令牌验证
	'TOKEN_NAME'			=> '__hash__',//令牌验证的表单隐藏字段名称
	'TOKEN_TYPE'			=> 'md5',//令牌哈希验证规则 默认为 MD5

	//分组方面的配置
	'APP_GROUP_LIST'		=> 'Admin,Home',//系统分组
	'DEFAULT_GROUP'			=> 'Admin',//默认系统组
	'DEFAULT_MODULE'        => 'Index',  //默认控制器
	'DEFAULT_ACTION'        => 'showAdminLogin', //默认方法

	'LOG_RECORD'			=> false,//日志记录

	'APP_DEBUG'				=> false,		//开启调试模式
	'SHOW_PAGE_TRACE' =>false, //开启页面Trace
	'OUTPUT_ENCODE' => false,

	//规则路由
	'URL_ROUTER_ON'	=> true,
	'URL_ROUTE_RULES'=> array(
	//首页
			'/^register$/'=>'home/index/reg',
			'/^fogetpassword$/'=>'home/index/findPassword',
			'/^result-(\d+)-(\d+)$/'=>'home/index/result?kstime=:1&uid=:2',
	),
		
	'VIDEO_UPLOAD_MAX_SIZE'	=> '10',//单位：M

	//模版方面的配置问题
	'HTML_CACHE_ON' => false,// 默认关闭静态缓存
	'TMPL_CACHE_ON' => false,// 默认开启模板编译缓存 false 的话每次都重新编译模板
	'TMPL_VAR_IDENTIFY'		=> 'obj',//模板内的点输出，现在是对象输出。array为数组方式
	'TMPL_L_DELIM'          => '{%',// 模板引擎普通标签开始标记
	'TMPL_R_DELIM'          => '%}',// 模板引擎普通标签结束标记
	'TAGLIB_BEGIN'          => '{%',// 标签库标签开始标记
	'TAGLIB_END'            => '%}', // 标签库标签结束标记

	//个人配置
	'MD5KEY'		=>	"nihaorailwaylerrywanghahawuhanlaiyouxicompayisverystronger",
	
	'SJTYPEARR'			=> array('1'=>'A卷', '2'=>'B卷', '3'=>'C卷', '4'=>'D卷'),    //试卷类型
	'JOBYPEARR'			=> array('1'=>'动车组机械师', '2'=>'地勤机械师', '3'=>'随车机械师', '4'=>'班长'),    //1司机 2机械师 3乘务员 4电气师
	
		
	'CRHTYPE'			=> array('1'=>'CRH5A', '2'=>'CRH380A', '4'=>'CRH1A', '8'=>'CRH2A', '16'=>'CRH3'),    //车型定义
	'JXTYPE'			=> array('1'=>'一级修', '2'=>'二级修','3'=>'三级修', '4'=>'四级修'),   //检修类别定义
	'BWTYPE'			=> array('1'=>'车顶','2'=>'车底'),		//部位定义
	'TOOLUSETYPE'		=> array('1'=>'特殊工具','8'=>'测量','16'=>'照明','32'=>'清洁'),  //工具用途定义
	"changeType"		=> array('1'=>"A", '2'=>"B", '3'=>"C", '4'=>"D",'5'=>"E",'6'=>"F",'7'=>"G",'8'=>"H",'9'=>"I",'10'=>"J"),
)

?>