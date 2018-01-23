<?php
/**
 * @Author: ispCMS
 * @Date:   2014-06-21 10:00:00
 * @Last Modified by:   ispCMS
 * @Last Modified time: 2016-06-21 12:37:45
 */
namespace Common\Model;

//视图模型
class SpecialViewModel extends ExViewModel
{

    protected $viewFields = array(
        'special'  => array('*', '_type' => 'LEFT'),
        'category' => array(
            'name'    => 'catename',
            'ename'   => 'ename',
            'modelid' => 'modelid',
            '_on'     => 'special.cid = category.id', //_on 对应上面LEFT关联条件
        ),

    );
}
