<?php
/**
 * @Author: ispCMS
 * @Date:   2014-06-21 10:00:00
 * @Last Modified by:   ispCMS
 * @Last Modified time: 2016-06-21 12:39:28
 */

namespace Home\Controller;

class IndexController extends HomeCommonController
{
    //方法：index
    public function index()
    {

        go_mobile();
        $this->assign('title', C('CFG_WEBNAME'));
        $this->display();

    }
}
