<?php
/**
 * @Author: ispCMS
 * @Date:   2014-06-21 10:00:00
 * @Last Modified by:   ispCMS
 * @Last Modified time: 2016-06-21 12:38:54
 */
namespace Home\Controller;

class AbcController extends HomeCommonController
{
    //shows
    public function shows()
    {

        $id   = I('id', 0, 'intval');
        $flag = I('flag', 0, 'intval');
        if (!empty($id)) {
            echo get_abc($id, $flag);
        } else {
            echo '';
        }

    }
}
