<?php

/* ---------------------------------------------------------------------------
  Copyright (c) 2016 iSP All rights reserved.
  Author:  iSP
  ------------------------------------------------------------------------- */

namespace Home\Controller;

class PhoneController extends HomeCommonController {
    
    protected $num = array(
        //分页条数
        num => 20
    ); 
    /*
      资讯列表
     */
    public function news_old() {
        //获取文章栏目下级目录 默认文章在栏目表中id排序第一 不可更改  否则pid应相应变化        
        $list = M('Category') ->where('pid = 1')->select();        
        $this -> assign('list',$list);      
        //轮播图
        $map['aid'] = 3;
        $map['status'] = 0;
        $model_abc = M('abc_detail')->where($map)->order('sort asc')->select();
        $this->assign('model_abc', $model_abc);          
        $this->display();
    }  
    
    public function news_list() {
        $model = M('article');
        $cat_id = I('cat_id');
        $map['cid'] = $cat_id;
        $map_name['id'] = $cat_id;
        $title_name = M('category')->where($map_name)->getField('name');		
        $list = $model->where($map)->field('id,title,updatetime,litpic,author')->select();      
        $this -> assign('title_name',$title_name);      
        $this -> assign('list',$list);   
        $this->display();
    }
	
	/*
      资讯详情
     */
    public function news_detail($id=1) {    
        $id = I('id');
        $model = M('article');
        $map['id'] = $id;
        $new_detail = $model->where($map)->find();               
        $this->assign('new_detail', $new_detail);        
        $this->display();
    }	

    //资源列表
    public function news(){
        $user_id = $this->user_id();
        $school_id = $this->school_id();
        if($user_id == '' || $school_id== ''){
            $this->error('登录信息丢失,请重新登录！');            
        }
        //栏目
        $model_list = M('category')->where('pid = 1')->field('id,name')->select();        
        $this -> assign('model_list',$model_list);  
        
        $num = $this->num['num']; 
        $map['_string'] = "jur = 3 AND status = 0 OR jur = 2 AND school_id = $school_id AND status = 0 OR jur = 1 AND userid = $user_id AND status = 0";        
        $res_list = M('article')->where($map)->order('publishtime desc')->limit($num)->select();       
        foreach ($res_list as $key => &$vo) {            
            $res_usr_list[$vo['userid']] = $vo['userid'];          
        }
        if(!empty($res_usr_list)){			
            $where['id'] = array('in', $res_usr_list);		         
            $users = M('user',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where($where)->getField('id,name');         
            foreach ($res_list as $key => &$vo) {
                $vo['username'] = empty($users[$vo['userid']])?'管理员':$users[$vo['userid']];	                  
            }
        }      
        $this->assign('reslist', $res_list);        
        $this->display();
    }       
  
    public function cid_list(){
        $num = $this->num['num'];   
        $cid = I('cid');        
        $user_id = $this->user_id();
        $school_id = $this->school_id();
        if($user_id == '' || $school_id== ''){
            $this->error('登录信息丢失,请重新登录！');            
        }  
        if($cid == 0){
            $map['_string'] = "jur = 3 AND status = 0 OR jur = 2 AND school_id = $school_id  AND status = 0 OR  jur = 1 AND userid = $user_id AND status = 0";          
        }else{
            $map['_string'] = "cid = $cid AND jur = 3 AND status = 0 OR jur = 2 AND school_id = $school_id AND cid = $cid  AND status = 0 OR cid = $cid AND jur = 1 AND userid = $user_id AND status = 0";            
        }
        $res_list = M('article')->where($map)->order('publishtime desc')->limit($num)->select();          
        foreach ($res_list as $key => &$vo) {            
            $res_usr_list[$vo['userid']] = $vo['userid'];          
        }
        if(!empty($res_usr_list)){			
            $where['id'] = array('in', $res_usr_list);		         
            $users = M('user',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where($where)->getField('id,name');         
            foreach ($res_list as $key => &$vo) {
                $vo['username'] = empty($users[$vo['userid']])?'管理员':$users[$vo['userid']];	                  
            }
        }     
        if ($res_list) {
                $return['data'] = $res_list;             
                $return['status'] = 1;
                $return['info'] = "读取成功";
                $this -> ajaxReturn($return);
        } else {
                $return['status'] = 2;
                $return['info'] = "没有更多数据了";
                $this -> ajaxReturn($return);
        }
    }
    
    /*资源首页*/
    public function resindex(){
        $map['pid'] = 12;//写死
        $list = M('category')->where($map)->select(); 
        $res_list = M('detail')->order('publishtime desc')->limit(6)->select();
        foreach ($res_list as $key => $vo) { 
            $savename  =  trim($vo['savename']);            
            $ext       =  pathinfo($savename)['extension'];            
            $res_list[$key]['ext'] = $ext;
            $title = substr($vo['title'],strpos($vo['title'],'.')+1);
            if(isset($title{3})){
               $res_list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/chm.gif';
            }else{
               $res_list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title.'.gif';
            }
        }        
        //print_r($res_list);
        $map_1['aid'] = 1;
        $map_1['status'] = 0;
        $model_abc = M('abc_detail')->where($map_1)->select();
        $this->assign('model_abc', $model_abc);         
        
        $this->assign('list', $list); 
        $this->assign('reslist', $res_list); 
        $this->display();
    }
    
    public function reslist(){
        $user_id = $this->user_id();
        $school_id = $this->school_id();
        if($user_id == '' || $school_id== ''){
            $this->error('登录信息丢失,请登录！');            
        }
        $chap_id = I('chap_id');
        $cid = I('id');
        if($cid){
            $num = $this->num['num'];                
            $map_cat['id'] = $cid;        
            $uid_name = M('category')->where($map_cat)->getField('name');          
            $map['_string'] = "cid = $cid AND jur = 3 AND is_del = 0 OR jur = 2 AND school_id = $school_id AND cid = $cid  AND is_del = 0 OR cid = $cid AND jur = 1 AND emp_no = $user_id AND is_del = 0";        
            $res_list = M('detail')->where($map)->order('publishtime desc')->limit($num)->select();        
            $this->assign('uidname', $uid_name);
            $this->assign('cid', $cid);              
        }
        if($chap_id){
            $resid_list = M('forchapter')->where(["chap_id"=>$chap_id])->field('ires_id')->select();
            $uid_name = M('chapter')->where(["id"=>$chap_id])->getField('chap_name');
            foreach ($resid_list as $key => $value) {
                $map['id'] = $value['ires_id'];
                $res_list[] = M('detail')->where($map)->order('publishtime desc')->find();                     
            }            
            $this->assign('uidname', $uid_name);
        }      
        foreach ($res_list as $key => $vo) {   
			$savename  =  trim($vo['savename']);            
            $ext       =  pathinfo($savename)['extension'];            
            $res_list[$key]['ext'] = $ext;        
            $title = substr($vo['title'],strpos($vo['title'],'.')+1);
            if(isset($title{3})){
               $res_list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/chm.gif';
            }else{
               $res_list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title.'.gif';
            }
        }               
        $this->assign('reslist', $res_list);  
        $this->display();
    }
    
    public function ressearch(){
        $list_schooltype = D("schooltype") -> where(["is_del"=>0]) -> order('sort asc') -> Field('id,type_name')->select();
        $this -> assign('list_schooltype', $list_schooltype); 
        
        $type_id = I('type_id');
        if($type_id){
            $type_name = D('schooltype')->where(array('id' => $type_id ))->Field('id,type_name')->find();   
            $list_topic = D("topic") -> where(["is_del"=>0,'type_id' => $type_id]) -> order('sort asc') -> Field('id,topic_name')->select();
            $this -> assign('list_topic', $list_topic); 
            $this->assign('type_name', $type_name); 
        }  
        $topic_id = I('topic_id');
        if($topic_id){
            $topic_name = D('topic')->where(array('id' => $topic_id ))->Field('id,topic_name')->find(); 
            $list_ver = D("version") -> where(["is_del"=>0,'topic_id' => $topic_id]) -> order('sort asc') -> Field('id,ver_name')->select();
            $this -> assign('list_ver', $list_ver); 
            $this->assign('topic_name', $topic_name); 
        }  
        $ver_id = I('ver_id');
        if($ver_id){           
            $ver_name = D('version')->where(array('id' => $ver_id ))->Field('id,ver_name')->find();  
            $list_seg = D("gradeseg") -> where(["is_del"=>0,'ver_id' => $ver_id]) -> order('sort asc') -> Field('id,seg_name')->select();
            $this -> assign('list_seg', $list_seg); 
            $this->assign('ver_name', $ver_name); 
        } 
        $seg_id = I('seg_id');
        if($seg_id){
            $seg_name = D('gradeseg')->where(array('id' => $seg_id ))->Field('id,seg_name')->find();      
            $this->assign('seg_name', $seg_name); 
        }                
        $seg_id = I('seg_id', 0, 'intval');
        $model_cat = M("chapter");
        $map_cat['seg_id'] = $seg_id;
        $list = $model_cat->field('id,pid,chap_name,is_del')->order('sort asc')->where($map_cat)->select();      
        $chaps = list_to_tree($list);
        $chaps = $chaps[0];              
        $this->assign('chaps', $chaps); 
        $this->display();
    }
    
    public function morelist(){
        $num = $this->num['num'];
        $number= I('post.number');     
        $number_id = strstr($number, '|', ture);
        $cid = substr($number,strpos($number,'|')+1);
        $user_id = $this->user_id();
        $school_id = $this->school_id();  
        if($user_id == '' || $school_id== ''){
            $this->error('登录信息丢失,请重新登录！');            
        }  
        if($cid == 0){
            $map['_string'] = "jur = 3 AND status = 0 OR jur = 2 AND school_id = $school_id  AND status = 0 OR  jur = 1 AND userid = $user_id AND status = 0";          
        }else{
            $map['_string'] = "cid = $cid AND jur = 3 AND status = 0 OR jur = 2 AND school_id = $school_id AND cid = $cid  AND status = 0 OR cid = $cid AND jur = 1 AND userid = $user_id AND status = 0";            
        }
        $limit = $number_id*$num.','.$num;
        $list = M('article')->where($map)->order('publishtime desc')->limit($limit)->select();    
        foreach ($list as $key => &$vo) {            
            $res_usr_list[$vo['userid']] = $vo['userid'];          
        }
        if(!empty($res_usr_list)){			
            $where['id'] = array('in', $res_usr_list);		         
            $users = M('user',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where($where)->getField('id,name');         
            foreach ($list as $key => &$vo) {
                $vo['username'] = empty($users[$vo['userid']])?'管理员':$users[$vo['userid']];	                  
            }
        }     
        if ($list) {
                $return['data'] = $list;
                $return['number'] = $number_id+1;
                $return['status'] = 1;
                $return['info'] = "读取成功";
                $this -> ajaxReturn($return);
        } else {
                $return['status'] = 2;
                $return['info'] = "没有更多数据了";
                $this -> ajaxReturn($return);
        }
    }  
    
    public function orlist(){
        $num = $this->num['num'];
        $number= I('post.number');     
        $number_id = strstr($number, '|', ture);
        $cid = substr($number,strpos($number,'|')+1);
        $user_id = $this->user_id();
        $school_id = $this->school_id();      
        $map['_string'] = "cid = $cid AND jur = 3 AND is_del = 0 OR jur = 2 AND school_id = $school_id AND cid = $cid  AND is_del = 0 OR cid = $cid AND jur = 1 AND emp_no = $user_id AND is_del = 0";          
        $limit = $number_id*$num.','.$num;
        $list = M('detail')->where($map)->order('publishtime desc')->limit($limit)->select();    
        foreach ($list as $key => $vo) { 
            $savename  =  trim($vo['savename']);            
            $ext       =  pathinfo($savename)['extension'];            
            $list[$key]['ext'] = $ext; 
            $title = substr($vo['title'],strpos($vo['title'],'.')+1);
            if(isset($title{3})){
               $list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/chm.gif';
            }else{
               $list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title.'.gif';
            }
        }        
        if ($list) {
                $return['data'] = $list;
                $return['number'] = $number_id+1;
                $return['status'] = 1;
                $return['info'] = "读取成功";
                $this -> ajaxReturn($return);
        } else {
                $return['status'] = 2;
                $return['info'] = "没有更多数据了";
                $this -> ajaxReturn($return);
        }
    }    
    
    public function resdetail(){
        $resid = I('resid');        
        $map['id'] = $resid;
        $map['converflag'] = 1;
        $model = M('detail');
        $res_info = $model ->where($map)->find();  
//      $res_info['down'] = $res_info['downlink']; 
//      $res_info['down'] = explode('$$$', $res_info['down']);
//      $res_info['down'] =  $res_info['down'][1];       
//      $model_ver = M('version');
//      $map_ver['id'] =  $res_info['ver_id'];
//      $ver_name      =  $model_ver ->where($map_ver) -> find();
//      $ver_name      =  $ver_name['ver_name'];		
        $downlink_arr  =  empty($res_info['downlink']) ? array() : explode('|||', $res_info['downlink']);
        $downlink      =  array();
      //  $baseurl       =  !empty($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : rtrim("http://" . $_SERVER['SERVER_NAME'], '/');
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
                    $fileurl    =   $baseurl.(!empty($savename)?$savename:$temp_arr[1]);                    
                }                     
                $phone_type = get_device_type();
                if($phone_type == 'ios'){
                    $fileurl = changeFileExt($fileurl);
                }                
                $title		=	$temp_arr[0];
                if(empty($ext))	
                    $ext = pathinfo($temp_arr[1])['extension'];
                if(in_array($ext, $exts_video)){
                    $ext = $phone_type == 'ios'?'mp4':'flv';
                    $res['type'] = 1;
                    $res['file'] = json_encode(['type'	=>	$ext,'url' 	=> $fileurl]);
                }else{
                    $ext = 'pdf';
                    $res['type'] = 2;
                    $res['file'] = $fileurl;				
                }
                $resdata[] = array(
                    'type' 	=> $ext,				
                    'title' => $title,
                    'url' 	=> $fileurl,
                );
            }
        }
        $username = $this->user_name();        
        $this -> assign('username', $username); 
        $this -> assign('res_li', $res);
        $this -> assign('res', $res_info);       
//      $this -> assign('ver_name', $ver_name);	
        $this -> assign('ext', $ext); 
        $this -> assign('resdata_s', $resdata);    	
        $this->display();
    }   
     public function resselect(){
        $user_id = $this->user_id();
        $school_id = $this->school_id();
        if($user_id == '' || $school_id== ''){
            $this->error('登录信息丢失,请登录！');            
        }
        $map_cat['pid'] = 12;//写死
        $list = M('category')->where($map_cat)->select(); 
        $cid = I('cid');
        $title = I('title');
        if(!($cid == '')){
            $map['cid'] = $cid;
        }
        $map['title'] = array('like', "%" . $title . "%");        
        $num = $this->num['num']; 
        $map['_string'] = "jur = 3 AND is_del = 0 OR jur = 2 AND school_id = $school_id AND is_del = 0 OR jur = 1 AND emp_no = $user_id AND is_del = 0";        
        $reslist = M('detail')->where($map)->order('publishtime desc')->limit($num)->select();         
        foreach ($reslist as $key => $value) {
            $savename =  trim($value['savename']);
            $ext  =  pathinfo($savename)['extension'];
            $reslist[$key]['ext'] = $ext;
            $titles = substr($value['title'],strpos($value['title'],'.')+1);
            if(isset($titles{3})){
               $reslist[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/chm.gif';
            }else{
               $reslist[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$titles.'.gif';
            }
        }
        $this -> assign('list', $list);
        $this -> assign('cid', $cid);
        $this -> assign('title', $title);
        $this -> assign('reslist', $reslist); 
        $this->display();
     }
     
     public function orlist_select(){
        $maps = I('post.');
        $num = $this->num['num'];
        $number= $maps['number'];        
        $cid = $maps['cid']; 
        if(!($cid == '')){
            $map_se['cid'] = $cid;
        }
        $title = $maps['title'];
        if(!($title == '')){
            $map_se['title'] = array('like', "%" . $title . "%");  
        }
        $user_id = $this->user_id();
        $school_id = $this->school_id();      
        $map['_string'] = "jur = 3 AND is_del = 0 OR jur = 2 AND school_id = $school_id AND is_del = 0 OR jur = 1 AND emp_no = $user_id AND is_del = 0";        
        $limit = $number*$num.','.$num;
        $list = M('detail')->where($map)->where($map_se)->order('publishtime desc')->limit($limit)->select();       
        foreach ($list as $key => $vo) { 
            $savename  =  trim($vo['savename']);            
            $ext       =  pathinfo($savename)['extension'];            
            $list[$key]['ext'] = $ext;      
            $titles = substr($vo['title'],strpos($vo['title'],'.')+1);
            if(isset($titles{3})){
               $list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/chm.gif';
            }else{
               $list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$titles.'.gif';
            }
        }        
        if ($list) {
                $return['data'] = $list;
                $return['number'] = $number+1;
                $return['status'] = 1;
                $return['info'] = "读取成功";
                $this -> ajaxReturn($return);
        } else {
                $return['status'] = 2;
                $return['info'] = "没有更多数据了";
                $this -> ajaxReturn($return);
        }
    }    

 }
?>