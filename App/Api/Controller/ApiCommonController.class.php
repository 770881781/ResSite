<?php
/**
 * 公共验证控制器CommonController
 * @Author: ispCMS
 * @Date:   2014-06-21 10:00:00
 * @Last Modified by:   ispCMS
 * @Last Modified time: 2016-09-26 23:26:32
 */

namespace Api\Controller;

use Think\Controller;


class ApiCommonController extends Controller {


    //用户id
    protected $uid = 0;

    /**
     * 空操作，404页面
     * @return NULL
     */
    public function _empty() {
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        // header("Content-Type: text/html; charset=utf-8");
        // $this->display('Public:404');
        echo '404 Not Found';

    }



}
