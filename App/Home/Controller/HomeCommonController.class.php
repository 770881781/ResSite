<?php

/**
 * @Author: ispCMS
 * @Date:   2014-06-21 10:00:00
 * @Last Modified by:   ispCMS
 * @Last Modified time: 2016-06-21 12:39:25
 */

namespace Home\Controller;

use Think\Controller;

//公共验证控制器HomeCommonController
class HomeCommonController extends Controller {

    // 空操作，404页面
    public function _empty() {
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        $this->display(get_tpl('404.html'));
    }

    protected function _initialize() {
        $this->assign('version', 'v1.0.0');
        if (C('CFG_WEBSITE_CLOSE') == 1) {
            exit_msg(C('CFG_WEBSITE_CLOSE_INFO'));
        }
    }

    //评论处理
    public function add() {
        header("Content-Type:text/html; charset=utf-8");
        if (!IS_AJAX || !IS_POST) {
            //exit(json_encode( array('status' => 0, 'info' => '非法请求' ) ));
            $this->error('非法请求');
        }
        //M验证
        $data['postid'] = I('post_id', 0, 'intval');
        $data['modelid'] = I('model_id', 0, 'intval');
        $data['pid'] = I('review_id', 0, 'intval');
        $data['title'] = I('title', '');
        $data['username'] = I('username', '');
        $data['content'] = I('content', '');
        $data['posttime'] = time();
        $data['ip'] = get_client_ip(); 
        $data['userid'] = $this->user_id();
        
        $data['agent'] = $_SERVER['HTTP_USER_AGENT'];

//        $verify = I('vcode', '', 'htmlspecialchars,trim');
//        if (C('cfg_verify_review') == 1 && !check_verify($verify)) {
//            $this->error('验证码不正确');
//        }
        
        $gettime =  M('comment') -> where(array('postid'=>$data['postid'],'userid'=>$data['userid'])) ->order('posttime desc')-> Field("posttime")->find();     
        if($gettime['posttime'] > 1){           
            $thetime = time();           
            $artime =$thetime-$gettime['posttime'];              
            if($artime < 60){
                $ftime = 60 - $artime;
                $this->error('评论过频繁，请于'.$ftime.'秒后尝试');                
            }
        }

        $username = $data['username']; //不能用empty(get_cookie('uid')),empty不能用于函数返回值
        if (!empty($username)) {
            $data['username'] = $username;
//            $data['email'] = get_cookie('email');
            /*
              if(get_cookie('nickname') != '') {
              $data['username'] = get_cookie('nickname');
              } else {
              $data['username'] = preg_replace('/(\w+)\@(\w+)\.(\w+)/is',"$1@*.$3",get_cookie('email'));
              }
             */
//            $data['username'] = get_cookie('nickname');
        } else {
            $data['userid'] = 0;
            $data['username'] = I('nickname', '游客');
            $data['email'] = I('email', '', 'htmlspecialchars,trim');
        }
//        if ($data['userid'] == 0 && !C('CFG_FEEDBACK_GUEST')) {
//            //允许匿名评论
//            $this->error('请登录后评论');
//        }

        if (empty($data['postid']) || empty($data['modelid'])) {
            $this->error('参数错误');
        }

        if (empty($data['title'])) {
            $this->error('文章不正确，请刷新再评论');
        }

        if (empty($data['content']) || mb_strlen($data['content'], 'utf-8') < 3) {
            $this->error('请填写评论内容，内容太短');
        }

        if (check_badword($data['content'])) {
            $this->error('评论内容包含非法信息，请认真填写！');
        }

        if ($id = M('comment')->add($data)) {
            //$this->success('添加成功',U(MODULE_NAME. '/Guestbook/index'));
            $list = array(
                //'status' => 1,
                'id' => $id,
                'user_id' => $data['userid'],
                'review_id' => $data['pid'],
                'username' => $data['username'],
                'ico' => '',
                'avatar' => get_avatar(get_cookie('face'), 30),
                'content' => $data['content'],
                'posttime' => date('Y-m-d H:i:s', time()),
            );
            $furl = $_SERVER['HTTP_REFERER'];
            //exit(json_encode($list));
            $this->success('添加成功', $furl, $list);
        } else {
            $this->error('添加失败' . M('comment')->getError());
        }
    }
 //评论处理
    public function add_less() {
        header("Content-Type:text/html; charset=utf-8");
        if (!IS_AJAX || !IS_POST) {
            //exit(json_encode( array('status' => 0, 'info' => '非法请求' ) ));
            $this->error('非法请求');
        }
        //M验证
        $data['lesson_id'] = I('post_id', 0, 'intval');       
        $data['pid'] = I('review_id', 0, 'intval');
        $data['title'] = I('title', '');
        $data['username'] = I('username', '');
        $data['content'] = I('content', '');
        $data['posttime'] = time();
        $data['ip'] = get_client_ip(); 
        $data['userid'] = $this->user_id();        
        $data['school_id'] = $this->school_id(); 
        $data['emp_no'] = session('emp_no');
        $data['type'] = session('user_type');        
        $data['agent'] = $_SERVER['HTTP_USER_AGENT'];

//        $verify = I('vcode', '', 'htmlspecialchars,trim');
//        if (C('cfg_verify_review') == 1 && !check_verify($verify)) {
//            $this->error('验证码不正确');
//        }
        
        $gettime =  M('lessoncomment') -> where(array('lesson_id'=>$data['lesson_id'],'userid'=>$data['userid'])) ->order('posttime desc')-> Field("posttime")->find();     
        if($gettime['posttime'] > 1){           
            $thetime = time();           
            $artime =$thetime-$gettime['posttime'];              
            if($artime < 60){
                $ftime = 60 - $artime;
                $this->error('评论过频繁，请于'.$ftime.'秒后尝试');                
            }
        }

        $username = $data['username']; //不能用empty(get_cookie('uid')),empty不能用于函数返回值
        if (!empty($username)) {
            $data['username'] = $username;
//            $data['email'] = get_cookie('email');
            /*
              if(get_cookie('nickname') != '') {
              $data['username'] = get_cookie('nickname');
              } else {
              $data['username'] = preg_replace('/(\w+)\@(\w+)\.(\w+)/is',"$1@*.$3",get_cookie('email'));
              }
             */
//            $data['username'] = get_cookie('nickname');
        } else {
            $data['userid'] = 0;
            $data['username'] = I('nickname', '游客');
            $data['email'] = I('email', '', 'htmlspecialchars,trim');
        }
//        if ($data['userid'] == 0 && !C('CFG_FEEDBACK_GUEST')) {
//            //允许匿名评论
//            $this->error('请登录后评论');
//        }

        if (empty($data['lesson_id'])) {
            $this->error('参数错误');
        }

        if (empty($data['title'])) {
            $this->error('文章不正确，请刷新再评论');
        }

        if (empty($data['content']) || mb_strlen($data['content'], 'utf-8') < 3) {
            $this->error('请填写评论内容，内容太短');
        }

        if (check_badword($data['content'])) {
            $this->error('评论内容包含非法信息，请认真填写！');
        }

        if ($id = M('lessoncomment')->add($data)) {
            //$this->success('添加成功',U(MODULE_NAME. '/Guestbook/index'));
            $list = array(
                //'status' => 1,
                'id' => $id,
                'user_id' => $data['userid'],
                'review_id' => $data['pid'],
                'username' => $data['username'],
                'ico' => '',
                'avatar' => get_avatar(get_cookie('face'), 30),
                'content' => $data['content'],
                'posttime' => date('Y-m-d H:i:s', time()),
            );
            $furl = $_SERVER['HTTP_REFERER'];
            //exit(json_encode($list));
            $this->success('添加成功', $furl, $list);
        } else {
            $this->error('添加失败' . M('comment')->getError());
        }
    }

    //评论条数前台显示
    public function getlist() {
        header("Content-Type:text/html; charset=utf-8"); //不然返回中文乱码
        if (!IS_AJAX) {
            //exit('非法请求');
        }

        $postid = I('post_id', 0, 'intval');
        $modelid = I('model_id', 0, 'intval');
        $pageSize = I('num', 2, 'intval');
        $page = I('page', 1, 'intval');
        $avatar = I('avatar', 'middle');
        $userid = get_cookie('uid');
        $userid = empty($userid) ? '0' : get_cookie('uid');

        $count = D('CommentView')->where(array('pid' => 0, 'postid' => $postid, 'modelid' => $modelid))->count();
        if ($count % $pageSize) {
            $pageCount = (int) ($count / $pageSize) + 1; //如果有余数，则页数等于总数据量除以每页数的结果取整再加一
        } else {
            $pageCount = $count / $pageSize;
        }
        $page = $page > $pageCount ? $pageCount : $page;
        $page = $page < 1 ? 1 : $page;

        $data = D('CommentView')->where(array('pid' => 0, 'postid' => $postid, 'modelid' => $modelid))->order('comment.id DESC')->limit(($page - 1) * $pageSize, $pageSize)->select();
        if (empty($data)) {
            $data = array();
        }
        $list = array(
            'count' => $count,
            'avatar' => get_avatar(get_cookie('face'), 30),
            'user_id' => $userid,
            'guest' => intval(C('CFG_FEEDBACK_GUEST')),
                //'sql' => M('comment')->getlastsql(),
                //'review' => ''
        );
        $list['list'] = array();
        $ids = array(); //所有id为下面的查询的pid

        foreach ($data as $k => $v) {
            $list['list'][] = array(
                'id' => $v['id'],
                'user_id' => $v['userid'],
                'username' => $v['username'],
                'ico' => '',
                'avatar' => get_avatar($v['face'], 30),
                'content' => $v['content'],
                'posttime' => date('Y-m-d H:i:s', $v['posttime']),
                'child' => array(), //后面就不用初始化
            );
            $ids[] = $v['id'];
        }

        //评论回复

        if (!empty($ids)) {
            $data = D('CommentView')->where(array('pid' => array('in', $ids), 'postid' => $postid, 'modelid' => $modelid))->order('comment.id')->select();

            if (!empty($data)) {
                foreach ($list['list'] as $k => $v) {
                    foreach ($data as $k2 => $v2) {
                        if ($v['id'] == $v2['pid']) {
                            $list['list'][$k]['child'][] = array(
                                'id' => $v2['id'],
                                'user_id' => $v2['userid'],
                                'review_id' => $v2['pid'],
                                'username' => $v2['username'],
                                'ico' => '',
                                'avatar' => get_avatar($v2['face'], 30),
                                'content' => $v2['content'],
                                'posttime' => date('Y-m-d H:i:s', $v2['posttime']),
                            );

                            unset($data[$k2]); //删除已经认领元素,减少内循环
                        }
                    }
                }
            }
        }
        unset($data);
        exit(json_encode($list));
    } //评论条数前台显示
    
    public function getlist_lesson() {
        header("Content-Type:text/html; charset=utf-8"); //不然返回中文乱码
        if (!IS_AJAX) {
            //exit('非法请求');
        }

        $postid = I('post_id', 0, 'intval');     
        $pageSize = I('num', 2, 'intval');
        $page = I('page', 1, 'intval');
        $avatar = I('avatar', 'middle');
        $userid = get_cookie('uid');
        $userid = empty($userid) ? '0' : get_cookie('uid');

        $count = M('lessoncomment')->where(array('pid' => 0, 'lesson_id' => $postid))->count();
        if ($count % $pageSize) {
            $pageCount = (int) ($count / $pageSize) + 1; //如果有余数，则页数等于总数据量除以每页数的结果取整再加一
        } else {
            $pageCount = $count / $pageSize;
        }
        $page = $page > $pageCount ? $pageCount : $page;
        $page = $page < 1 ? 1 : $page;

        $data = M('lessoncomment')->where(array('pid' => 0, 'lesson_id' => $postid))->order('id DESC')->limit(($page - 1) * $pageSize, $pageSize)->select();
        if (empty($data)) {
            $data = array();
        }
        $list = array(
            'count' => $count,
            'avatar' => get_avatar(get_cookie('face'), 30),
            'user_id' => $userid,
            'guest' => intval(C('CFG_FEEDBACK_GUEST')),
                //'sql' => M('comment')->getlastsql(),
                //'review' => ''
        );
        $list['list'] = array();
        $ids = array(); //所有id为下面的查询的pid

        foreach ($data as $k => $v) {
            $list['list'][] = array(
                'id' => $v['id'],
                'user_id' => $v['userid'],
                'username' => $v['username'],
                'ico' => '',
                'avatar' => get_avatar($v['face'], 30),
                'content' => $v['content'],
                'posttime' => date('Y-m-d H:i:s', $v['posttime']),
                'child' => array(), //后面就不用初始化
            );
            $ids[] = $v['id'];
        }

        //评论回复

        if (!empty($ids)) {
            $data = M('lessoncomment')->where(array('pid' => array('in', $ids), 'lesson_id' => $postid))->order('id desc')->select();

            if (!empty($data)) {
                foreach ($list['list'] as $k => $v) {
                    foreach ($data as $k2 => $v2) {
                        if ($v['id'] == $v2['pid']) {
                            $list['list'][$k]['child'][] = array(
                                'id' => $v2['id'],
                                'user_id' => $v2['userid'],
                                'review_id' => $v2['pid'],
                                'username' => $v2['username'],
                                'ico' => '',
                                'avatar' => get_avatar($v2['face'], 30),
                                'content' => $v2['content'],
                                'posttime' => date('Y-m-d H:i:s', $v2['posttime']),
                            );

                            unset($data[$k2]); //删除已经认领元素,减少内循环
                        }
                    }
                }
            }
        }
        unset($data);
        exit(json_encode($list));
    }

    public function school_id($school_id = null) {
        if (empty($school_id)) {
            return session('school_id');
        } else {
            $this->error('获取学校ID出错');
        }
    }

    public function user_id($user_id = null) {
        if (empty($user_id)) {
            return session('user_id');
        } else {
            $this->error('获取用户ID出错');
        }
    }

    public function user_name($user_id = null) {
        if (empty($user_id)) {
            return session('user_name');
        } else {
            $this->error('获取用户名出错');
        }
    }
    
    //去除二维数组重复元素
    function unique_arr($array2D, $stkeep = false, $ndformat = true) {
        // 判断是否保留一级数组键 (一级数组键可以为非数字)
        if ($stkeep)
            $stArr = array_keys($array2D);
        // 判断是否保留二级数组键 (所有二级数组键必须相同)
        if ($ndformat)
            $ndArr = array_keys(end($array2D));
        //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
        foreach ($array2D as $v) {
            $v = join(",", $v);
            $temp[] = $v;
        }
        //去掉重复的字符串,也就是重复的一维数组
        $temp = array_unique($temp);
        //再将拆开的数组重新组装
        foreach ($temp as $k => $v) {
            if ($stkeep)
                $k = $stArr[$k];
            if ($ndformat) {
                $tempArr = explode(",", $v);
                foreach ($tempArr as $ndkey => $ndval)
                    $output[$k][$ndArr[$ndkey]] = $ndval;
            } else
                $output[$k] = explode(",", $v);
        }
        return $output;
    }

    /*
      另外一种绑定数据的方式，通过传入表名，条件，字段，排序，每页显示条数来构造列表数据，分页
     */

    protected function _list_res($table, $fields, $map, $sort = '', $nuber = 10) {
        if (empty($table) || empty($fields))
            return FALSE;
        //排序字段 默认为主键名
        $model = M('', C('DB_RES.DB_PREFIX'), C('DB_RES'));
        //取得满足条件的记录数
        $count = $model->table($table)->where($map)->count();
        // echo $model->getlastSql();

        if ($count > 0) {
            //创建分页对象        
            $p = new \Common\Lib\Page($count, $nuber);
            //分页查询数据
            $p->rollPage = 6;
            $p->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            $limit = $p->firstRow . ',' . $p->listRows;
            $vo_list = $model->table($table)->where($map)->field($fields)->order($sort)->limit($p->firstRow . ',' . $p->listRows)->select();
            //echo $model->getlastSql();
            // $p->parameter = $this->_search();
            //分页显示
            $page = $p->show();
            if ($vo_list) {
                $this->assign('list', $vo_list);
                $this->assign('sort', $sort);
                $this->assign("page", $page);
                return $vo_list;
            }
        }
        return FALSE;
    }

    public function get_topic_list() {
        $map = array();
        $map['type_id'] = I('type_id');
        $list = D("Topic")->where($map)->order('sort asc')->getField('id,topic_name as name');
        foreach ($list as $key => $val) {
            $options[] = ['id' => $key, 'name' => $val];
        }
        if (!empty($options)) {
            $return['status'] = 1;
            $return['items'] = $options;
        } else {
            $return['status'] = 0;
            $return['items'] = null;
        }
        $this->ajaxReturn($return);
    }

    public function get_ver_list() {
        $map = array();
        $map['topic_id'] = I('topic_id');
        $list = D("version")->where($map)->order('sort asc')->getField('id,ver_name as name');
        foreach ($list as $key => $val) {
            $options[] = ['id' => $key, 'name' => $val];
        }
        if (!empty($options)) {
            $return['status'] = 1;
            $return['items'] = $options;
        } else {
            $return['status'] = 0;
            $return['items'] = null;
        }
        $this->ajaxReturn($return);
    }

    public function get_seg_list() {
        $map = array();
        $map['ver_id'] = I('ver_id');
        $list = D("gradeseg")->where($map)->order('sort asc')->getField('id,seg_name as name');
        foreach ($list as $key => $val) {
            $options[] = ['id' => $key, 'name' => $val];
        }
        if (!empty($options)) {
            $return['status'] = 1;
            $return['items'] = $options;
        } else {
            $return['status'] = 0;
            $return['items'] = null;
        }
        $this->ajaxReturn($return);
    }

    public function upload($img_flag = 0) {
        header("Content-Type:text/html; charset=utf-8"); //不然返回中文乱码
        $result = array('state' => '失败', 'url' => '', 'name' => '', 'original' => '');
        $sub_path = I('post.sfile', '', 'trim,htmlspecialchars'); //判断其他子目录

        $img_flag = empty($img_flag) ? 0 : 1;

        $yun_upload = new \Common\Lib\YunUpload($img_flag, $sub_path);
        $upload_result = $yun_upload->upload();

        if ($upload_result['status']) {
            $result['state'] = 'SUCCESS';
            $result['info'] = $upload_result['data'];
        } else {
            $result['state'] = $upload_result['info'];
        }
        echo json_encode($result);
    }

}
