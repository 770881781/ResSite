<?php

/**
 * @Author: ispCMS
 * @Date:   2014-06-21 10:00:00
 * @Last Modified by:   ispCMS
 * @Last Modified time: 2016-06-21 12:39:28
 */

namespace Home\Controller;

use Think\PageExtend;

class ResController extends HomeCommonController {

    //方法：index
    public function index() {      
        $school_id = $this->school_id();        
        $user_id = $this->user_id(); 
        if($user_id == '' || $school_id == ''){
            $this->error('请重新登录！','/sns');
        }  
        $prefix = C('DB_PREFIX');
        $sql = "select ires_topic.id,ires_schooltype.type_name,ires_schooltype.id as type_id,ires_topic.topic_name from ires_schooltype LEFT OUTER JOIN ires_topic on ires_topic.type_id = ires_schooltype.id";
        $list = M()->query($sql);
        //print_r($list);
        foreach ($list as $key => $item) {
            $topic = [
                'id' => $item['id'],
                'topic_name' => $item['topic_name']
            ];
            $type_list[$item['type_name'].'|'.$item['type_id']][] = $topic;
        }     
        $ver_list = M('version')->where(['topic_id'=>1])->select();//默认选择的学科  小学语文
        $this->assign('ver_list', $ver_list); 
        
        $gra_list = M('gradeseg')->where(['ver_id'=>1])->select();//默认选择的学科  小学语文
        $this->assign('gra_list', $gra_list);        
        
        // 资源列表,最新资源
        $model = M('detail');
        $map_new['is_del'] = 0;
        $map_new['privilege'] = 0;        
        $del_list = $model->limit(5)->where($map_new)->order('publishtime desc')->field('id,title,publishtime,downlink')->select();             
        foreach ($del_list as $key => $vo) {  
            $title = explode(".",$vo['downlink']);         
            $del_list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title[1].'.gif';          
        }            
        //推荐资源
        $del_list_coll = $model->limit(5)->where($map_new)->order('collection asc')->field('id,title,publishtime,downlink')->select(); 
        foreach ($del_list_coll as $key => $vo) {           
            $title = explode(".",$vo['downlink']);         
            $del_list_coll[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title[1].'.gif';         
        }            
        //热门资源
        $del_list_dow = $model->limit(5)->where($map_new)->order('downcnt asc')->field('id,title,publishtime,downlink')->select();
        foreach ($del_list_dow as $key => $vo) {           
            $title = explode(".",$vo['downlink']);         
            $del_list_dow[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title[1].'.gif';         
        }           
        //资源总数
        $map_con['is_del'] = array('neq',1);
        $count_all = $model->where($map_con)->count();
        //视频数
        $count_all_mov = $model->where('cid=18')->where($map_con)->count();
        //课件数
        $count_all_ppt = $model->where('cid=17')->where($map_con)->count();        
     
      	//轮播图管理
        $map_1['aid'] = 1;
        $map_1['status'] = 0;
        $model_abc = M('abc_detail')->where($map_1)->select();
        $this->assign('model_abc', $model_abc);  

        //栏目
        $model_list = M('category')->where('pid = 12')->field('id,name')->select();
        $this->assign('res_list', $res_list);
        $this->assign('model_list', $model_list);
        
        $this->assign('count_all', $count_all);
        $this->assign('count_mov', $count_all_mov);
        $this->assign('count_ppt', $count_all_ppt);
        
        $this->assign('del_list', $del_list);
        $this->assign('del_list_coll', $del_list_coll);
        $this->assign('del_list_dow', $del_list_dow);
     
        $this->assign('type_list', $type_list);
        $this->assign('title', C('CFG_WEBNAME'));  
        $this->assign('name',$name);
        $this->display();
    }

    public function get_ver() {
        $topic_id = I('topic_id', 0, 'intval');
        $model_ver = M("version");
        $model_know = M("knowledge");
        //获取知识点
        $map_know['topic_id'] = $topic_id;
        $list_know = $model_know->field('id,pid,know_name,is_del')->order('sort asc')->where($map_know)->select();
        $knows = list_to_tree($list_know);
        //获取版本	
        $map_ver['topic_id'] = $topic_id;
        $vo_ver = $model_ver->where($map_ver)->order('sort asc')->select();
        if ($vo_ver !== false) {// 读取成功
            $return['data_know'] = $knows;
            $return['data_ver'] = $vo_ver;
            $return['status'] = 1;
            $return['info'] = "读取成功";
        } else {
            $return['status'] = 0;
            $return['info'] = "读取错误";
        }
        $this->ajaxReturn($return);
    }

    public function get_seg() {
        $ver_id = I('ver_id', 0, 'intval');
        $model_seg = M("gradeseg");
        $map_ver['topic_id'] = $topic_id;
        //获取年级段
        $map_seg['ver_id'] = $ver_id;
        $vo_seg = $model_seg->where($map_seg)->order('sort asc')->select();
        if ($vo_ver !== false) {// 读取成功
            $return['data_seg'] = $vo_seg;
            $return['status'] = 1;
            $return['info'] = "读取成功";
        } else {
            $return['status'] = 0;
            $return['info'] = "读取错误";
        }
        $this->ajaxReturn($return);
    }

    public function get_chapter() {
        $seg_id = I('seg_id', 0, 'intval');
        $model_cat = M("chapter");
        $map_cat['seg_id'] = $seg_id;
        $list = $model_cat->field('id,pid,chap_name,is_del')->order('sort asc')->where($map_cat)->select();
        $chaps = list_to_tree($list);
        if ($vo_ver !== false) {// 读取成功
            $return['data_seg'] = $chaps;
            $return['status'] = 1;
            $return['info'] = "读取成功";
            $this->ajaxReturn($return);
        } else {
            $return['status'] = 0;
            $return['info'] = "读取错误";
            $this->ajaxReturn($return);
        }
    }

    public function get_forchapter() {
        $chap_id = I('chap_id');
        $model = M("detail");
        $model_forcat = M("forchapter");
        $map_forcat['chap_id'] = $chap_id;
        $del_list = $model_forcat->where($map_forcat)->select();         
        $school_id = $this->school_id();        
        if($school_id == ''){
            $school_id = 0;
        }    
        $user_id = $this->user_id();           
        if($user_id == ''){
            $user_id = 0;
        }     
        $map['_string'] = "ires_detail.school_id = $school_id AND ires_detail.jur = 2 AND ires_detail.is_del = 0 AND ires_detail.privilege = 0 OR ires_detail.userid = $user_id  AND ires_detail.jur = 1 AND ires_detail.is_del = 0 AND ires_detail.privilege = 0 OR ires_detail.jur = 3 AND ires_detail.is_del = 0 AND ires_detail.privilege = 0 ";
        foreach ($del_list as $key => $value) {            
            $map['id'] = $value['ires_id'];           
            $res_list[] = $model->where($map)->find();           
        }       
        $res_list = array_filter($res_list);
        foreach ($res_list as $key => $vo) {           
            $title = explode(".",$vo['downlink']);         
            $res_list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title[1].'.png';         
        }      
        if ($det !== false) {// 读取成功            
            $return['data_det'] = $res_list;         
            $return['status'] = 1;
            $return['info'] = "读取成功";
            $this->ajaxReturn($return);
        } else {
            $return['status'] = 0;
            $return['info'] = "读取错误";
            $this->ajaxReturn($return);
        }
    } 
    
    public function get_forchapter_res_list() {
        $seg_id = I('seg_id', 0, 'intval');
        $model_cat = M("chapter");
        $map_cat['seg_id'] = $seg_id;
        $list = $model_cat->field('id,pid,chap_name,is_del')->order('sort asc')->where($map_cat)->select();
        $model = M("detail");
        $model_forcat = M("forchapter");
        $school_id = $this->school_id(); 
        $user_id = $this->user_id();
        if($school_id == ''){
            $school_id = 1000;
        }                           
        if($user_id == ''){
            $user_id = 0;
        }           
        foreach ($list as $key => $value){
            $chap_id = $value['id'];          
            $map_forcat['chap_id'] = $chap_id;
            $del_list = $model_forcat->where($map_forcat)->select();                            
           
            $map['_string'] = "ires_detail.school_id = $school_id AND ires_detail.jur = 2 AND ires_detail.is_del = 0 AND ires_detail.privilege = 0 OR ires_detail.userid = $user_id  AND ires_detail.jur = 1 AND ires_detail.is_del = 0 AND ires_detail.privilege = 0 OR ires_detail.jur = 3 AND ires_detail.is_del = 0 AND ires_detail.privilege = 0 ";
            foreach ($del_list as $key => $value) {            
                $map['id'] = $value['ires_id'];           
                $res_list[] = $model->where($map)->find();           
            }                                       
        }
        $res_list = array_filter($res_list); 
        foreach ($res_list as $key => $vo) {           
            $title = explode(".",$vo['downlink']);         
            $res_list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title[1].'.png';         
        }         
        if ($res_list !== false) {// 读取成功            
            $return['data_det'] = $res_list;         
            $return['status'] = 1;
            $return['info'] = "读取成功";
            $this->ajaxReturn($return);
        } else {
            $return['status'] = 0;
            $return['info'] = "读取错误";
            $this->ajaxReturn($return);
        }
    }
    
    public function get_forknowledge() {
        $know_id = I('know_id');
        $model = M("detail");
        $model_forknow = M("forknowledge");
        $map_forknow['know_id'] = $know_id;
        $know_list = $model_forknow->where($map_forknow)->select();
         $school_id = $this->school_id();        
        $user_id = $this->user_id();           
        if($user_id == ''){
            $user_id = 0;
        }     
        $map['_string'] = "ires_detail.school_id = $school_id  AND ires_detail.jur = 2 AND ires_detail.is_del = 0 AND ires_detail.privilege = 0 OR ires_detail.userid = $user_id  AND ires_detail.jur = 1 AND ires_detail.is_del = 0 AND ires_detail.privilege = 0 OR ires_detail.jur = 3 AND ires_detail.is_del = 0 AND ires_detail.privilege = 0";
        foreach ($know_list as $key => $value) {
            $map['id'] = $value['ires_id'];          
            $res_list[] = $model->where($map)->find();          
        }
        $res_list = array_filter($res_list);
        foreach ($res_list as $key => $vo) {           
            $title = explode(".",$vo['downlink']);         
            $res_list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title[1].'.png';  
        }      
        if ($det !== false) {// 读取成功           
            $return['data_know'] = $res_list;         
            $return['status'] = 1;
            $return['info'] = "读取成功";
            $this->ajaxReturn($return);
        } else {
            $return['status'] = 0;
            $return['info'] = "读取错误";
            $this->ajaxReturn($return);
        }
    }    
	
	//方法：show_res
    public function show_res(){        
        $resid = I('resid');        
        $map['id'] = $resid;
        //点击次数加一
        $list = M('detail')->where($map)->setInc('click');        
        $map['converflag'] = 1;
        $model = M('detail');
        $res_info = $model ->where($map)->find();        
        $model_ver = M('version');
        $map_ver['id'] =  $res_info['ver_id'];
        $ver_name      =  $model_ver ->where($map_ver) -> find();
        $ver_name      =  $ver_name['ver_name'];		
        $downlink_arr  =  empty($res_info['downlink']) ? array() : explode('|||', $res_info['downlink']);        
        $downlink      =  array();        
//      $baseurl       =  !empty($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : rtrim("http://" . $_SERVER['SERVER_NAME'], '/');
        $baseurl = C('ISPOA_RESROOT');
        $exts_video    =  array('flv','rmvb','mp4');
        $exts_file     =  array('pdf','doc','docx','xls','xlsx');
        $savename      =  trim($res_info['savename']);        
        $ext	       =  pathinfo($savename)['extension'];
        foreach ($downlink_arr as $v) {
            $temp_arr = explode('$$$', $v);    
            if($temp_arr[1] == ''){
                $temp_arr[1] = $temp_arr[0];
            }                              
            if (!empty($temp_arr[1])) {
                $temp_arr[1]	=	trim($temp_arr[1]);
                $rest = substr($savename, 0, 7);                
                if($rest == 'http://'){
                    $fileurl =  $temp_arr[1];                    
                }else{                
                    $fileurl	=	$baseurl.(!empty($savename)?$savename:$temp_arr[1]);                    
                }      
                $res_info['down']	=  $baseurl.$temp_arr[1];
                $title		=	$temp_arr[0];
                if(empty($ext))	
                    $ext = pathinfo($temp_arr[1])['extension'];               
                if(in_array($ext, $exts_video)){
                    $ext = 'flv';
                    $res['type'] = 1;
                    $res['file'] = json_encode(['type'	=>	$ext,'url' 	=> $fileurl]);
                }else{
                    $ext = 'pdf';
                    $res['type'] = 2;
                    $res['file'] = $fileurl;				
                }
                $resdata[] = array(
                    'type' 	=> $ext,				
                    'title'     => $title,
                    'url' 	=> $fileurl,
                );
            }
        }
        //判断用户是否收藏过该资源
        $map_collect['postid'] = $resid;
        $map_collect['emp_no'] = $this->user_id();
        $is_collect = M('collection')->where($map_collect)->find();
        if($is_collect){
            $this -> assign('is_collect', '已收藏');
        }else{
            $this -> assign('is_collect', '收藏');
        }
        $username = $this->user_name();        
        $this -> assign('username', $username); 
        $this -> assign('res_li', $res);
        $this -> assign('res', $res_info);       
        $this -> assign('ver_name', $ver_name);	
        $this -> assign('resdata_s', $resdata);               
        $this -> display();
    }
    
    public function get_res_list(){
        $res_id = I('res_cid');
        $res_id = explode('|', $res_id);
        $cid = array_pop($res_id);
        $model = M('detail');
        $map['cid'] = $cid;
        foreach ($res_id as $value){          
            $map['id'] = $value;
            $res_list[] = $model->where($map)->select();           
        }
        foreach ($res_list as $key => $va){
            foreach ($va as $val){
                $res_li[] = $val;
            }
        }
        foreach ($res_li as $key => $vo) {           
            $title = explode(".",$vo['downlink']);         
            $res_li[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title[1].'.png';  
        }      
        if ($res_li !== false) {// 读取成功
            $return['res_list'] = $res_li;
            $return['cid'] = $cid;         
            $return['status'] = 1;
            $return['info'] = "读取成功";
            $this->ajaxReturn($return);
        } else {
            $return['status'] = 0;
            $return['info'] = "读取错误";
            $this->ajaxReturn($return);
        }    
    }    

    //用户收藏资源
    public function get_is_collect(){
        $res_id = I('res_id');
        $data['postid'] =  $res_id;
        $data['emp_no'] =  $this->user_id();  //获取当前登录用户id  暂时写死模拟
        $list = M('collection')->where($data)->find();
        if ($list) {// 读取成功   
            $return['status'] = 1;
            $return['info'] = "您已经收藏过啦，无需重复点击啦！";
            $this->ajaxReturn($return);        
        } else {
            //数据库下载次数加一
            $map['id'] = $res_id;                    
            $result = M('detail') ->where($map) ->field('collection,cid')->select();;
            $a = (int)$result[0]['collection'] + 1;
            $result_a = M('detail') ->where($map)  ->setField('collection',$a);          
            
            $data['cid'] = $result[0]['cid'];
            $data['opertime'] = time();
            $data['username'] = $this->user_name(); //获取当前登录用户姓名  暂时写死模拟
            $list_add = M('collection')->add($data);            
            if($list_add){
            $return['status'] = 2;
            $return['info'] = "收藏成功";
            $this->ajaxReturn($return);
            }
        }       
    }

    //用户下载资源
    public function get_is_download(){
        $res_id = I('res_id');
        $data['postid'] =  $res_id;
        $data['emp_no'] =  $this->user_id();  //获取当前登录用户id  暂时写死模拟
        $list = M('download')->where($data)->find(); 
        if ($list) {// 读取成功   
            $return['status'] = 1;
            $return['info'] = "您曾经下载过！";
            $this->ajaxReturn($return);        
        } else {
            //数据库下载次数加一
            $map['id'] = $res_id;                    
            $result = M('detail') ->where($map) ->field('downcnt,cid')->select();;
            $a = (int)$result[0]['downcnt'] + 1;
            $result_a = M('detail') ->where($map)  ->setField('downcnt',$a);          
            
            $data['cid'] = $result[0]['cid'];
            $data['opertime'] = time();
            $data['username'] = $this->user_name(); //获取当前登录用户姓名  暂时写死模拟
            $list_add = M('download')->add($data);            
            if($list_add){
            $return['status'] = 2;
            $return['info'] = "下载数据入库成功";
            $this->ajaxReturn($return);
            }
        }    
    }   

    //用户下载资源首页
    public function download(){
        $map['emp_no'] =  $this->user_id();  //获取当前登录用户id  暂时写死模拟
        $res_id = M('download')->where($map)->order('opertime desc')->field('postid')->select();         
        foreach ($res_id as $value) {
            $map_res[] = (int)$value['postid'];          
        }  
        $map_res[] = 0;         
        $map_res[] = 'or';         
        $where['id'] = $map_res;      
        $count       = M('detail')->where($where)->count();   
        $page        = new \Common\Lib\Page($count, 6);
        $page->rollPage = 7;
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow . ',' . $page->listRows;
        $list  = M('detail')->where($where)->limit($limit)->select(); 
        
        $model_list = M('category')->where('pid = 12')->field('id,name')->select();
        $this->assign('model_list', $model_list);
        
        $this->assign('page', $page->show());
        $this->assign('vlist', $list);        
        $this->assign('pid', $pid);        
        $this -> display();
    }  

    //删除用户下载记录
    public function del_download(){
        $res_id = I('res_id');
        $map['postid'] = $res_id;
        $map['emp_no'] =  $this->user_id();  //获取当前登录用户id  暂时写死模拟
        $list = M('download')->where($map)->delete(); 
        if ($list) {   
            $return['status'] = 1;            
            $this->ajaxReturn($return);        
        } else {           
            $return['status'] = 2;            
            $this->ajaxReturn($return);            
        }    
    }   

    //用户收藏首页
    public function collection(){
        $map['emp_no'] =  $this->user_id();  //获取当前登录用户id  暂时写死模拟
        $res_id = M('collection')->where($map)->order('opertime desc')->field('postid')->select();         
        foreach ($res_id as $value) {
            $map_res[] = (int)$value['postid'];          
        }    
        $map_res[] = 0;         
        $map_res[] = 'or';         
        $where['id'] = $map_res;      
        $count       = M('detail')->where($where)->count();   
        $page        = new \Common\Lib\Page($count, 6);
        $page->rollPage = 7;
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow . ',' . $page->listRows;
        $list  = M('detail')->where($where)->limit($limit)->select(); 
        
        $model_list = M('category')->where('pid = 12')->field('id,name')->select();
        $this->assign('model_list', $model_list);
        
        $this->assign('page', $page->show());
        $this->assign('vlist', $list);        
        $this->assign('pid', $pid);        
        $this -> display();
    }  

    //用户收藏记录删除
    public function del_coll(){
        $res_id = I('res_id');
        $map['postid'] = $res_id;
        $map['emp_no'] =  $this->user_id();  //获取当前登录用户id  暂时写死模拟
        $list = M('collection')->where($map)->delete(); 
        if ($list) {   
            $return['status'] = 1;            
            $this->ajaxReturn($return);        
        } else {           
            $return['status'] = 2;            
            $this->ajaxReturn($return);            
        }    
    }      
    
    //用户上传页面
   public function myreslist(){
        $map['userid'] =  $this->user_id();  //获取当前登录用户id  暂时写死模拟         
        $count       = M('detail')->where($map)->count();   
        $page        = new \Common\Lib\Page($count, 6);
        $page->rollPage = 7;
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow . ',' . $page->listRows;
        $list  = M('detail')->where($map)->limit($limit)->select();         
        $model_list = M('category')->where('pid = 12')->field('id,name')->select();
        $this->assign('model_list', $model_list);        
        $this->assign('page', $page->show());
        $this->assign('vlist', $list);        
        $this->assign('pid', $pid);        
        $this -> display();
    }   
    //学校资源页面
    public function schoolres(){
        $map['school_id'] =  $this->school_id();  //获取当前登录用户id  暂时写死模拟 
        $map['privilege'] = 0;
        $map['jur'] = array('in','2,3');
        $count       = M('detail')->where($map)->count();   
        $page        = new \Common\Lib\Page($count, 6);
        $page->rollPage = 7;
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow . ',' . $page->listRows;
        $list  = M('detail')->where($map)->limit($limit)->select();         
        $model_list = M('category')->where('pid = 12')->field('id,name')->select();
        $this->assign('model_list', $model_list);        
        $this->assign('page', $page->show());
        $this->assign('vlist', $list);        
        $this->assign('pid', $pid);        
        $this -> display();
    }  
    //资源搜索页面
    public function search() {            
        $prefix = C('DB_PREFIX');
        $user_id = $this->user_id();      
        $school_id = $this->school_id();  
        $type_id = I('type_id');
        if(!($type_id == '')){              
            $map['ires_detail.type_id'] = $type_id;               
            $topic_list = M('topic')->where(['type_id' =>$type_id])->select(); 
            $this->assign('type_id', $type_id);
            $this->assign('topic_list', $topic_list);         
        }                 
//        $where['_string'] ="
//        ires_detail.jur = 3 AND
//        ires_detail.is_del = 0
//        OR ires_detail.jur = 2
//	AND ires_detail.school_id = $school_id AND
//        ires_detail.is_del = 0
//	OR ires_detail.jur = 1
//        AND ires_detail.is_del = 0
//	AND ires_detail.emp_no = $user_id";                          
//        $count = M('detail')->where($map)->where($where)->count();   
//        $page  = new \Common\Lib\Page($count, 8);
//        $page->rollPage = 7;
//        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
//        $limit = $page->firstRow . ',' . $page->listRows;
//        $list  = M('detail') ->join($prefix . 'schooltype ON '. $prefix . 'detail.type_id          = ' . $prefix . 'schooltype.id')
//            ->join($prefix . 'topic ON '     . $prefix . 'detail.topic_id         = ' . $prefix . 'topic.id')
//            ->join($prefix . 'version ON '   . $prefix . 'detail.ver_id           = ' . $prefix . 'version.id')
//            ->join($prefix . 'gradeseg ON '  . $prefix . 'detail.seg_id          = ' . $prefix . 'gradeseg.id')
//            ->where($map)->where($where)->limit($limit)->field('ires_detail.*,ires_schooltype.type_name,ires_topic.topic_name,ires_version.ver_name,ires_gradeseg.seg_name')->select();       
//        $this->assign('page', $page->show());
//        $this->assign('vlist', $list);        
//        $this->assign('pid', $pid);      
        
         // 资源列表,最新资源
        $model = M('detail');
        $map_new['is_del'] = 0;
        $map_new['privilege'] = 0;    
        $del_list = $model->limit(5)->order('publishtime desc')->where($map_new)->field('id,title,publishtime,downlink')->select(); 
        foreach ($del_list as $key => $vo) {           
            $title = explode(".",$vo['downlink']);         
            $del_list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title[1].'.gif';  
        }            
        //推荐资源
        $del_list_coll = $model->limit(5)->order('collection asc')->where($map_new)->field('id,title,publishtime,downlink')->select(); 
        foreach ($del_list_coll as $key => $vo) {           
            $title = explode(".",$vo['downlink']);         
            $del_list_coll[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title[1].'.gif';  
        }            
        //热门资源
        $del_list_dow = $model->limit(5)->order('downcnt asc')->where($map_new)->field('id,title,publishtime,downlink')->select();
        foreach ($del_list_dow as $key => $vo) {           
            $title = explode(".",$vo['downlink']);         
            $del_list_dow[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title[1].'.gif';
        }           
        
        $type_list = M('schooltype')->select();        
        $this->assign('type_list', $type_list);
        $this->assign('del_list', $del_list);
        $this->assign('del_list_coll', $del_list_coll);
        $this->assign('del_list_dow', $del_list_dow);
        $this->display();
    }
    
    public function res() {          
        $aArray = I('aArray');        
        $page = I('page');
        $pageSize = I('pageSize');
        $offset = ($page - 1) * $pageSize;      
        $prefix = C('DB_PREFIX');        
        if($aArray['type_id'] != ''){            
            $map['ires_detail.type_id'] = $aArray['type_id'];         
        }
        if($aArray['topic_id'] != ''){            
            $map['ires_detail.topic_id'] = $aArray['topic_id'];         
        }  
        if($aArray['ver_id'] != ''){            
            $map['ires_detail.ver_id'] = $aArray['ver_id'];         
        }  
        if($aArray['seg_id'] != ''){            
            $map['ires_detail.seg_id'] = $aArray['seg_id'];         
        }  
        if($aArray['title'] != ''){            
            $map['ires_detail.title'] = array('like', "%" . $aArray['title'] . "%");    ;    
        }    
        $map['ires_detail.privilege'] = 0; 
        $res_list = M('detail')->where($map)->join($prefix . 'schooltype ON '. $prefix . 'detail.type_id          = ' . $prefix . 'schooltype.id')
            ->join($prefix . 'topic ON '     . $prefix . 'detail.topic_id         = ' . $prefix . 'topic.id')
            ->join($prefix . 'version ON '   . $prefix . 'detail.ver_id           = ' . $prefix . 'version.id')
            ->join($prefix . 'gradeseg ON '  . $prefix . 'detail.seg_id          = ' . $prefix . 'gradeseg.id')->limit($offset,$pageSize)->field('ires_detail.*,ires_schooltype.type_name,ires_topic.topic_name,ires_version.ver_name,ires_gradeseg.seg_name')->select();   
        foreach ($res_list as $key => $vo) {           
            $title = explode(".",$vo['downlink']);         
            $res_list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title[1].'.png';            
            if(!($vo['userid'] == 6)){
                $username = M('user',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where(['id'=>$vo['userid']])->getField('name');             
                $res_list[$key]['username'] = $username;
            }
        }              
        $total = M('detail')->where($map)->count();        
        $rows = $res_list;                  
        $this->ajaxReturn(compact('total','rows'),'JSON');        
    }  
    
    public function res_new() {          
        $aArray = I('aArray');        
        $page = I('page');
        $pageSize = I('pageSize');
        $offset = ($page - 1) * $pageSize;              
        if($aArray['type_id'] != ''){            
            $map['ires_detail.type_id'] = $aArray['type_id'];         
        }
        if($aArray['topic_id'] != ''){            
            $map['ires_detail.topic_id'] = $aArray['topic_id'];         
        }  
        if($aArray['ver_id'] != ''){            
            $map['ires_detail.ver_id'] = $aArray['ver_id'];         
        }  
        if($aArray['seg_id'] != ''){            
            $map['ires_detail.seg_id'] = $aArray['seg_id'];         
        }  
        if($aArray['cid'] != ''){            
            $map['ires_detail.cid'] = $aArray['cid'];        
        }    
        if($aArray['chap_id'] != ''){     
            $chap_id = $aArray['chap_id'];                      
            $map_forchap['chap_id'] = $chap_id;
            $chap_id_list = M('forchapter')->field('ires_id')->where($map_forchap)->select();     
            foreach ($chap_id_list as $value) {
                $chap_list[] =  $value['ires_id'];
            }            
            $map['ires_detail.id'] = array('like',$chap_list);     
        }             
        if($aArray['know_id'] != ''){     
            $know_id = $aArray['know_id'];                      
            $map_forknow['know_id'] = $know_id;
            $know_id_list = M('forknowledge')->field('ires_id')->where($map_forknow)->select();     
            foreach ($know_id_list as $value) {
                $know_list[] =  $value['ires_id'];
            }            
            $map['ires_detail.id'] = array('like',$know_list);     
        }         
        $school_id = $this->school_id();        
        $user_id = $this->user_id(); 
        $map['_string'] = "ires_detail.school_id = $school_id AND ires_detail.jur = 2 AND ires_detail.is_del = 0 AND ires_detail.privilege = 0 OR ires_detail.userid = $user_id  AND ires_detail.jur = 1 AND ires_detail.is_del = 0 AND ires_detail.privilege = 0 OR ires_detail.jur = 3 AND ires_detail.is_del = 0 AND ires_detail.privilege = 0 ";  
        $res_list = M('detail')->where($map)->order('ires_detail.publishtime desc')->limit($offset,$pageSize)->select();   
        foreach ($res_list as $key => $vo) {           
            $title = explode(".",$vo['downlink']);         
            $res_list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title[1].'.png';            
            if(!($vo['userid'] == 6)){
                $username = M('user',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where(['id'=>$vo['userid']])->getField('name');             
                $res_list[$key]['username'] = $username;
            }
        }              
        $total = M('detail')->where($map)->count();        
        $rows = $res_list;                  
        $this->ajaxReturn(compact('total','rows'),'JSON');        
    } 

}
