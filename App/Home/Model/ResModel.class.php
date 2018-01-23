<?php
/*---------------------------------------------------------------------------
  Copyright (c) 2016 iSP All rights reserved.
  Author:  iSP
 -------------------------------------------------------------------------*/
namespace Resource\Model;
use Think\Model;

class  ResModel extends CommonModel {
	// 自动验证设置
	protected $_validate	 =	 array(
		array('title','require','名称必须',1)
		);
}
?>