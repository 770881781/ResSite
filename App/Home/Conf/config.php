<?php

$config_base = array(

	'USER_AUTH_KEY'   => 'uid',			//用户认证识别号
	
	//加载自定义标签
	'TAGLIB_PRE_LOAD'=>'Common\\LibTag\\Yang,Common\\LibTag\\Other',//预加载的tag
	'TAGLIB_BUILD_IN' => 'cx', //内置标签

	//URL模式 ,0普通模式 ,1:PATHINFO模式（默认模式）,2:REWRITE模式,
    'URL_MODEL' => get_meta_value('HOME_URL_MODEL'),
    'URL_PATHINFO_DEPR' => get_meta_value('HOME_URL_PATHINFO_DEPR'),
    
    //路由
    'URL_ROUTER_ON' => get_meta_value('HOME_URL_ROUTER_ON'),
    'URL_ROUTE_RULES' => get_meta_value('HOME_URL_ROUTE_RULES'),

	//开启静态缓存
	'HTML_CACHE_ON' => get_meta_value('HOME_HTML_CACHE_ON'),
	'HTML_CACHE_RULES' => get_meta_value('HTML_CACHE_RULES_COMMON'),



	'VIEW_PATH'=>'./Public/'.MODULE_NAME .'/',
	'DEFAULT_THEME'  => get_cfg_value('CFG_THEMESTYLE'),//默认主题风格
	//'TMPL_DETECT_THEME' => false, // 自动侦测模板主题
	//'THEME_LIST'=>'default,blog',//支持的模板主题项

	'TMPL_PARSE_STRING' => array(
		'__PUBLIC__' => __ROOT__. '/Public/'.MODULE_NAME. '/' . get_cfg_value('CFG_THEMESTYLE'),		
		'__DATA__' => __ROOT__. '/Data',
		'__AVATAR__' => __ROOT__. '/avatar',

	),

);

return array_merge(get_cfg_value(),$config_base);


?>