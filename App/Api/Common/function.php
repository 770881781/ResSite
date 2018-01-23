<?php

/**
 * 返回内容中附件id数组
 * @param string $content 内容 in
 * @param string $firstpic 第一张缩略图 out
 * @param boolean $flag 是否获取第一张缩略图
 * @return mixed
 */
function get_att_content(&$content, &$firstpic = null, $flag = false) {

	//内容中的图片
	$img_arr = array();
	$reg = "/<img[^>]*src=\"((.+)\/(.+)\.(jpg|gif|bmp|png))\"/isU";		
	preg_match_all($reg, $content, $img_arr, PREG_PATTERN_ORDER);
	// 匹配出来的不重复图片
	$img_arr = array_unique($img_arr[1]);
	$attid_array = array();
	
	if (!empty($img_arr)) {

	   
	    $baseurl = get_url_path(get_cfg_value('CFG_UPLOAD_ROOTPATH'), true);
	    $baseurl2 = get_url_path(get_cfg_value('CFG_UPLOAD_ROOTPATH'));//不带域名
	    /*
	    foreach ($img_arr as $k => $v) {
	    	$img_arr[$k] = str_replace(array($baseurl,$baseurl2), array('',''), $v);//清除域名前缀			    	
	    }
	    */
	    $img_arr = str_replace(array($baseurl,$baseurl2), array('',''), $img_arr);//清除域名前缀	

	   
		$attid = M('attachment')->field('id,filepath')->where(array('filepath' => array('in', $img_arr)))->select();
		
		if ($attid) {

			//只有缩略图为空时,才提取第一张图片
			if ($flag && isset($firstpic)) {
				//取出本站内的第一张图
				foreach ($img_arr as $v) {
					foreach ($attid as $v2) {
						if ($v == $v2['filepath']) {
							$imgtbSize = explode(',', get_cfg_value('CFG_IMGTHUMB_SIZE'));//配置缩略图第一个参数
			                $imgTSize = explode('X', $imgtbSize[0]);
			                $firstpic =  get_picture($baseurl2.$v2['filepath'], intval($imgTSize[0]), intval($imgTSize[1]));
							break 2;
						}
					}
				}
			}

			//attid 数组
			foreach ($attid as $v) {
				$attid_array[] = $v['id'];
			}
		}
		
	}

	return $attid_array;
}

/**
 * 返回附件id数组
 * @param string|array $attachment 附件内容
 * @param boolean $flag 是否是缩略图
 * @return mixed
 */
function get_att_attachment($attachment,$flag = false) {

	
	if (empty($attachment)) {
		return array();
	}
	$attid_array = array();
	$baseurl = get_url_path(get_cfg_value('CFG_UPLOAD_ROOTPATH'));

	//清除缩略图的!200X200.jpg后缀
	if ($flag) {
		$attachment = preg_replace(array('#!(\d+)X(\d+)\.jpg$#i','#^'.$baseurl.'#i'), array('',''), $attachment);
	}else {
		$attachment = str_replace($baseurl, '', $attachment);
	}
	
	$attid = M('attachment')->where(array('filepath' => array('IN', $attachment)))->getField('id',true);
	if($attid){
		$attid_array = $attid;
	}

	return $attid_array;
}

/**
 * 返回保存到attachmentindex表
 * @param integer|array $attid 附件id
 * @param integer $attid 附件id
 * @param integer $modelid 模型id 
 * @param string $modelname 模型名称(唯一标志符)
 * @return mixed
 */
function insert_att_index($attid, $arcid, $modelid, $modelname = '') {
	if (empty($attid) || empty($arcid)) {
		return false;
	}
	if (empty($modelid) && $modelname == '') {
		return false;
	}

	if (is_array($attid)) {
		$attid_array = array_unique($attid);
	} else {
		$attid_array = array($attid);
	}

	//mysql,支持addAll
	if (in_array(strtolower(C('DB_TYPE')), array('mysql','mysqli','mongo'))) {
		
		$dataAtt = array();
		foreach ($attid_array as $v) {
			if ($modelid>0) {
				$dataAtt[] = array('attid' => $v,'arcid' => $arcid, 'modelid' => $modelid);
			} else {
				$dataAtt[] = array('attid' => $v,'arcid' => $arcid, 'desc' => $modelname);
			}		
		}
		M('attachmentindex')->addAll($dataAtt);
	} else {

		foreach ($attid_array as $v) {
			if ($modelid>0) {
				M('attachmentindex')->add(array('attid' => $v,'arcid' => $arcid, 'modelid' => $modelid));
			} else {
				M('attachmentindex')->add(array('attid' => $v,'arcid' => $arcid, 'desc' => $modelname));
			}		
		}
	}
		

	return true;
}


