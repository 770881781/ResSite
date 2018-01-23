<?php
/**
 * @Author: ispCMS
 * @Date:   2014-06-21 10:00:00
 * @Last Modified by:   ispCMS
 * @Last Modified time: 2016-06-21 12:37:42
 */
namespace Common\Model;

//视图模型
class MemberViewModel extends ExViewModel
{

    protected $viewFields = array(
        'member'       => array('*', '_type' => 'LEFT'),
        'membergroup'  => array(
            'name'  => 'groupname',
            '_on'   => 'member.groupid = membergroup.id', //_on 对应上面LEFT关联条件
            '_type' => 'LEFT',
        ),
        'memberdetail' => array(
            '*', //显示字段name as model
            '_on' => 'member.id = memberdetail.userid', //_on 对应上面LEFT关联条件
        ),
    );
}
