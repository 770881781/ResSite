<?php
/**
 * @Author: ispCMS
 * @Date:   2014-06-21 10:00:00
 * @Last Modified by:   ispCMS
 * @Last Modified time: 2016-06-21 12:37:30
 */
namespace Common\Model;

use Think\Model\ViewModel;

//视图模型
class CommentViewModel extends ViewModel
{

    protected $viewFields = array(
        'comment' => array('*',
            '_type' => 'LEFT',
        ),
        'model'   => array(
            'name'      => 'modelname',
            'tablename' => 'tablename',
            '_on'       => 'comment.modelid = model.id', //_on 对应上面LEFT关联条件
            '_type'     => 'LEFT',
        ),
        'member'  => array(
            'face'     => 'face', //显示字段name as model
            'nickname' => 'nickname', //显示字段name as model
            '_on'      => 'comment.userid = member.id', //_on 对应上面LEFT关联条件
        ),

    );
}
