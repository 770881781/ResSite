<?php

/**
 * @Author: ispCMS
 * @Date:   2014-06-21 10:00:00
 * @Last Modified by:   ispCMS
 * @Last Modified time: 2016-06-21 12:39:28
 */

namespace Home\Controller;

use Think\PageExtend;

class ResourceController extends HomeCommonController {

    protected $type = array(
        //学生  老师对应的栏目ID
        'teach_cid' => 28 ,  'stude_cid' => 29 ,
        'teach_name' => '名师风采' ,  'stude_name' => '优秀学子' 
    );    
    
    public function picture() {            
        $type = $this->type;        
        $map['school_id'] = $this->school_id();
        //名师风采
        $list_teach = M('picture')->where($map)->where(array('cid'=>$type['teach_cid']))->select();    
        //优秀学子
        $list_stude = M('picture')->where($map)->where(array('cid'=>$type['stude_cid']))->select();      
        $this->assign('list_teach', $list_teach);
        $this->assign('list_stude', $list_stude);
        $this->display();
    }      

    public function picdetail() {   
        $map['id'] = I('id');
        $content = M('picture')->where($map)->find();        
        $pictureurls_arr = empty($content['pictureurls']) ? array() : explode('|||', $content['pictureurls']);
        $pictureurls = array();
        foreach ($pictureurls_arr as $v) {
            $temp_arr = explode('$$$', $v);
            if (!empty($temp_arr[0])) {
                $pictureurls[] = array(
                    'url' => $temp_arr[0],
                    'alt' => $temp_arr[1],
                );
            }
        }           
        $map_where['school_id'] = $this->school_id();
        $map_where['cid'] = $content['cid'];
        //xia一条
        $next = M('picture')->where($map_where)->where(array('id' => array('gt',$map['id'])))->field('id,title')->find();        
        //上一条
        $pro = M('picture')->where($map_where)->where(array('id' => array('lt',$map['id'])))->field('id,title')->find();
        $type = $this->type;
        $content['pictureurls'] = $pictureurls; 
        $username = $this->user_name();        
        $this->assign('content', $content);
        $this->assign('username', $username);
        $this->assign('type', $type);
        $this->assign('next', $next);
        $this->assign('pro', $pro);
        $this->display();
    }
}
