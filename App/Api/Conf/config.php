<?php

$config_base = array(

	'TMPL_TEMPLATE_SUFFIX' => '.html',//模板后缀

	//去掉伪静态后缀
	'URL_HTML_SUFFIX' => '',

    'URL_MODEL'            => 3, //URL模式
	//禁止路由
	'URL_ROUTER_ON' => false,
	//禁止静态缓存
	'HTML_CACHE_ON' => false,

	'TMPL_PARSE_STRING' => array(
		'__PUBLIC__' => __ROOT__. ltrim(APP_PATH,'.'). MODULE_NAME . '/View/Public',               
		'__DATA__' => __ROOT__. '/Data',                          
	),
);

return array_merge(get_cfg_value(),$config_base);

