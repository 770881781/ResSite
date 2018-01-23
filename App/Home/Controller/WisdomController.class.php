<?php

/**
 * @Author: ispCMS
 * @Date:   2014-06-21 10:00:00
 * @Last Modified by:   ispCMS
 * @Last Modified time: 2016-06-21 12:39:28
 */

namespace Home\Controller;

class WisdomController extends HomeCommonController {

    //方法：index    
    public function index() {
        $prefix = C('DB_PREFIX');
        $user_id = $this->user_id();      
        $school_id = $this->school_id(); 
        if($user_id == '' || $school_id == ''){
            $this->error('请重新登录！','/sns');
        }  
        if($school_id == -1){
            $school_id = 1000;
        }
        //推荐课程
        $map['_string'] =         	
               "ires_lesson.type_id = ires_schooltype.id
            AND ires_lesson.topic_id = ires_topic.id
            AND ires_lesson.ver_id = ires_version.id
            AND ires_lesson.seg_id = ires_gradeseg.id	        
            AND ires_lesson.jur = 3               
            AND ires_lesson.is_del = 0               
            OR	ires_lesson.type_id = ires_schooltype.id
            AND ires_lesson.topic_id = ires_topic.id
            AND ires_lesson.ver_id = ires_version.id
            AND ires_lesson.seg_id = ires_gradeseg.id	
            AND ires_lesson.jur = 2
            AND ires_lesson.is_del = 0 
            AND ires_lesson.school_id = $school_id           
            OR	ires_lesson.type_id = ires_schooltype.id
            AND ires_lesson.topic_id = ires_topic.id
            AND ires_lesson.ver_id = ires_version.id
            AND ires_lesson.seg_id = ires_gradeseg.id	
            AND ires_lesson.is_del = 0 
            AND ires_lesson.jur = 1
            AND ires_lesson.emp_no = $user_id"; 
        $map['ires_lesson.lessonflag'] = 1;
        $model = M('lesson');
        $sort = 'ires_lesson.browsecnt desc,ires_lesson.updatetime desc';
        $nober = 6; //只显示条数
        if (!empty($model)) {
            $les = $prefix . 'lesson';
            $sch = $prefix . 'schooltype';
            $top = $prefix . 'topic';
            $ver = $prefix . 'version';
            $gra = $prefix . 'gradeseg';
            $this->_list_res("$les ires_lesson,$sch ires_schooltype,$top ires_topic,$ver ires_version,$gra ires_gradeseg", "ires_lesson.*,ires_schooltype.type_name,ires_topic.topic_name,ires_version.ver_name,ires_gradeseg.seg_name", $map, $sort, $nober);
        }      
        //微课  
        $prefix = C('DB_PREFIX');
        $map_wei['_string'] =         	
            "ires_lesson.jur = 3               
         AND ires_lesson.is_del = 0               
         OR	ires_lesson.jur = 2
         AND ires_lesson.is_del = 0 
         AND ires_lesson.school_id = $school_id           
         OR	ires_lesson.is_del = 0 
         AND ires_lesson.jur = 1
         AND ires_lesson.emp_no = $user_id"; 
           
        $lessonlist = $model->where($map_wei)->join($prefix . 'lessondesign ON '. $prefix . 'lesson.id  = ' . $prefix . 'lessondesign.lesson_id')->where(['lessonflag'=> 2])->order($sort)->limit(12)->field('ires_lesson.*,ires_lessondesign.res_id')->select();    
        $model_list = M('category')->where('pid = 12')->field('id,name')->select();      
        $this->assign('model_list', $model_list);  
        $this->assign('lessonlist', $lessonlist);
        
        //我的课堂
        $type = session('std_tag'); 
        $this->assign('type', $type);
            //老师
        if($type == 0){
            $map_my['ires_lesson.emp_no'] = $user_id;
            $map_my['ires_lesson.is_del'] = 0;
            $my_lesson = M('lesson')
                ->join($prefix . 'schooltype ON '. $prefix . 'lesson.type_id  = ' . $prefix . 'schooltype.id')
                ->join($prefix . 'topic ON '     . $prefix . 'lesson.topic_id = ' . $prefix . 'topic.id')
                ->join($prefix . 'version ON '   . $prefix . 'lesson.ver_id   = ' . $prefix . 'version.id')
                ->join($prefix . 'gradeseg ON '  . $prefix . 'lesson.seg_id  = ' . $prefix . 'gradeseg.id')
                ->where($map_my)->field('ires_lesson.*,ires_schooltype.type_name,ires_topic.topic_name,ires_version.ver_name,ires_gradeseg.seg_name')->select();
            $this->assign('my_lesson', $my_lesson);            
            //学生    
        }else if($type == 1){
            $map_my['ires_lessonvisitor.emp_no'] = $user_id;
            $map_my['ires_lesson.is_del'] = 0;
            $my_lesson = M('lessonvisitor')
                ->join($prefix . 'lesson ON '    . $prefix . 'lessonvisitor.lesson_id = ' . $prefix . 'lesson.id')
                ->join($prefix . 'schooltype ON '. $prefix . 'lesson.type_id          = ' . $prefix . 'schooltype.id')
                ->join($prefix . 'topic ON '     . $prefix . 'lesson.topic_id         = ' . $prefix . 'topic.id')
                ->join($prefix . 'version ON '   . $prefix . 'lesson.ver_id           = ' . $prefix . 'version.id')
                ->join($prefix . 'gradeseg ON '  . $prefix . 'lesson.seg_id          = ' . $prefix . 'gradeseg.id')
                ->where($map_my)->limit(6)->order('ires_lessonvisitor.starttime desc')->field('ires_lesson.*,ires_schooltype.type_name,ires_topic.topic_name,ires_version.ver_name,ires_gradeseg.seg_name,ires_lessonvisitor.starttime,ires_lessonvisitor.endtime')->select();        
            $this->assign('my_lesson', $my_lesson);                    
        }        
        $this->display();
    }//方法：index    
    
    public function index_old() {
        $prefix = C('DB_PREFIX');
        $user_id = $this->user_id();      
        $school_id = $this->school_id(); 
        if($user_id == '' || $school_id == ''){
            $this->error('请重新登录！');
        }  
        if($school_id == -1){
            $school_id = 1000;
        }
        //名师课堂
        $map['_string'] =         	
           "    ires_lesson.type_id = ires_schooltype.id
            AND ires_lesson.topic_id = ires_topic.id
            AND ires_lesson.ver_id = ires_version.id
            AND ires_lesson.seg_id = ires_gradeseg.id	        
            AND ires_lesson.jur = 3               
            OR	
                ires_lesson.type_id = ires_schooltype.id
            AND ires_lesson.topic_id = ires_topic.id
            AND ires_lesson.ver_id = ires_version.id
            AND ires_lesson.seg_id = ires_gradeseg.id	
            AND ires_lesson.jur = 2
            AND ires_lesson.school_id = $school_id           
            OR	
                ires_lesson.type_id = ires_schooltype.id
            AND ires_lesson.topic_id = ires_topic.id
            AND ires_lesson.ver_id = ires_version.id
            AND ires_lesson.seg_id = ires_gradeseg.id	
            AND ires_lesson.jur = 1
            AND ires_lesson.emp_no = $user_id";
                    
        $model = M('lesson');
        $sort = 'ires_lesson.browsecnt desc,ires_lesson.updatetime desc';
        $nober = 6; //只显示条数
        if (!empty($model)) {
            $les = $prefix . 'lesson';
            $sch = $prefix . 'schooltype';
            $top = $prefix . 'topic';
            $ver = $prefix . 'version';
            $gra = $prefix . 'gradeseg';
            $this->_list_res("$les ires_lesson,$sch ires_schooltype,$top ires_topic,$ver ires_version,$gra ires_gradeseg", "ires_lesson.*,ires_schooltype.type_name,ires_topic.topic_name,ires_version.ver_name,ires_gradeseg.seg_name", $map, $sort, $nober);
        }      
        //正在上课        
        $data_time = time()-21600;        
        $where['starttime'] = array('gt',$data_time);
        $where['status'] = 1;             
        $lessonlist = M('lessonlist')->join($prefix . 'lesson ON ' . $prefix . 'lessonlist.lesson_id = ' . $prefix . 'lesson.id')->field('ires_lesson.title,ires_lesson.litpic,ires_lesson.id')->where($where)->select();        
        $lessonlist_no = M('lessonlist')->where($where)->count();  
        //正在上课数目不够 拿名师课堂来加上        
        if($lessonlist_no < 4){
            $nober = 4 - $lessonlist_no;            
            $where_no['_string'] =         	
           "ires_lesson.lessonflag = 1 
            AND ires_lesson.jur = 3               
            OR	ires_lesson.jur = 2
            AND ires_lesson.school_id = $school_id 
            AND ires_lesson.lessonflag = 1
            OR	ires_lesson.jur = 1
            AND ires_lesson.emp_no = $user_id
            AND ires_lesson.lessonflag = 1";       
            $lessonlist_1 = M('lesson')->where($where_no)->order('ires_lesson.updatetime desc')->limit($nober)->field('ires_lesson.title,ires_lesson.litpic,ires_lesson.id')->where($where)->select();            
            if($nober == 4){
                $lessonlist = $lessonlist_1;                   
            }else{
                $lessonlist = array_merge($lessonlist,$lessonlist_1);     
            }          
        }       
        $this->assign('lessonlist', $lessonlist);
        //我的课堂
        $type = session('std_tag'); 
        $this->assign('type', $type);
            //老师
        if($type == 0){
            $map_my['ires_lesson.emp_no'] = $user_id;
            $map_my['ires_lesson.is_del'] = 0;
            $my_lesson = M('lesson')
                ->join($prefix . 'schooltype ON '. $prefix . 'lesson.type_id  = ' . $prefix . 'schooltype.id')
                ->join($prefix . 'topic ON '     . $prefix . 'lesson.topic_id = ' . $prefix . 'topic.id')
                ->join($prefix . 'version ON '   . $prefix . 'lesson.ver_id   = ' . $prefix . 'version.id')
                ->join($prefix . 'gradeseg ON '  . $prefix . 'lesson.seg_id  = ' . $prefix . 'gradeseg.id')
                ->where($map_my)->field('ires_lesson.*,ires_schooltype.type_name,ires_topic.topic_name,ires_version.ver_name,ires_gradeseg.seg_name')->select();
            $this->assign('my_lesson', $my_lesson);            
            //学生    
        }else if($type == 1){
            $map_my['ires_lessonvisitor.emp_no'] = $user_id;         
            $my_lesson = M('lessonvisitor')
                ->join($prefix . 'lesson ON '    . $prefix . 'lessonvisitor.lesson_id = ' . $prefix . 'lesson.id')
                ->join($prefix . 'schooltype ON '. $prefix . 'lesson.type_id          = ' . $prefix . 'schooltype.id')
                ->join($prefix . 'topic ON '     . $prefix . 'lesson.topic_id         = ' . $prefix . 'topic.id')
                ->join($prefix . 'version ON '   . $prefix . 'lesson.ver_id           = ' . $prefix . 'version.id')
                ->join($prefix . 'gradeseg ON '  . $prefix . 'lesson.seg_id          = ' . $prefix . 'gradeseg.id')
                ->where($map_my)->field('ires_lesson.*,ires_schooltype.type_name,ires_topic.topic_name,ires_version.ver_name,ires_gradeseg.seg_name,ires_lessonvisitor.starttime,ires_lessonvisitor.endtime')->select();        
            $this->assign('my_lesson', $my_lesson);                    
        }        
        $this->display();
    }
    
    public function res() {          
        $aArray = I('aArray');        
        $page = I('page');
        $pageSize = I('pageSize');
        $offset = ($page - 1) * $pageSize;      
        $prefix = C('DB_PREFIX');        
        $user_id = $this->user_id();      
        $school_id = $this->school_id(); 
        if($user_id == '' || $school_id == ''){
            $this->error('请重新登录！');
        }  
        if($school_id == -1){
            $school_id = 1000;
        }
        if(!($aArray['type_id'] == '')){            
            $map['ires_lesson.type_id'] = $aArray['type_id'];         
        }
        if(!($aArray['topic_id'] == '')){            
            $map['ires_lesson.topic_id'] = $aArray['topic_id'];         
        }  
        if(!($aArray['ver_id'] == '')){            
            $map['ires_lesson.ver_id'] = $aArray['ver_id'];         
        }  
        if(!($aArray['seg_id'] == '')){            
            $map['ires_lesson.seg_id'] = $aArray['seg_id'];         
        }  
        if(!($aArray['lessonflag'] == '')){            
            $map['ires_lesson.lessonflag'] = $aArray['lessonflag'];         
        }  
        if(!($aArray['title'] == '')){            
            $map['ires_lesson.title'] = array('like', "%" . $aArray['title'] . "%");    ;    
        }  
        $map['ires_lesson.is_del'] = 0;        
        $where['_string'] ="
        ires_lesson.jur = 3 AND
        ires_lesson.is_del = 0
        OR ires_lesson.jur = 2
	AND ires_lesson.school_id = $school_id AND
        ires_lesson.is_del = 0
	OR ires_lesson.jur = 1
        AND ires_lesson.is_del = 0
	AND ires_lesson.emp_no = $user_id"; 
        $res = M('lesson')->where($map)->where($where)->join($prefix . 'schooltype ON '. $prefix . 'lesson.type_id          = ' . $prefix . 'schooltype.id')
            ->join($prefix . 'topic ON '     . $prefix . 'lesson.topic_id         = ' . $prefix . 'topic.id')
            ->join($prefix . 'version ON '   . $prefix . 'lesson.ver_id           = ' . $prefix . 'version.id')           
            ->join($prefix . 'gradeseg ON '  . $prefix . 'lesson.seg_id = ' . $prefix . 'gradeseg.id')->limit($offset,$pageSize)->field('ires_lesson.*,ires_schooltype.type_name,ires_topic.topic_name,ires_version.ver_name,ires_gradeseg.seg_name')->select();   
        foreach ($res as $key => &$value) {
            if($value['lessonflag'] == 2){
                $value['res_id'] = M('lessondesign')->where(['lesson_id'=>$value['id']])->getField('res_id');                
            }
        }
        $total = M('lesson')->where($map)->count();        
        $rows = $res;                  
        $this->ajaxReturn(compact('total','rows'),'JSON');        
    } 
    
    public function search() {            
        $prefix = C('DB_PREFIX');
        $user_id = $this->user_id();      
        $school_id = $this->school_id();  
        $type_id = I('type_id');
        if(!($type_id == '')){            
            $map['ires_lesson.type_id'] = $type_id;               
            $topic_list = M('topic')->where(['type_id' =>$type_id])->select(); 
            $this->assign('type_id', $type_id);
            $this->assign('topic_list', $topic_list);
        } 
        $topic_id = I('topic_id');
        if(!($topic_id == '')){            
            $map['ires_lesson.topic_id'] = $topic_id;                    
            $ver_list = M('version')->where(['topic_id' =>$topic_id])->select(); 
            $this->assign('topic_id', $topic_id);
            $this->assign('ver_list', $ver_list);
        }
        $ver_id = I('ver_id');
        if(!($ver_id == '')){            
            $map['ires_lesson.ver_id'] = $ver_id;   
            $seg_list = M('gradeseg')->where(['ver_id' =>$ver_id])->select(); 
            $this->assign('ver_id', $ver_id);
            $this->assign('seg_list', $seg_list);           
        } 
        $seg_id = I('seg_id');
        if(!($seg_id == '')){            
            $map['ires_lesson.seg_id'] = $seg_id;  
            $this->assign('seg_id', $seg_id);
        }  
        $title = I('title');
        if(!($title == '')){            
            $map['ires_lesson.title'] = array('like', "%" . $title . "%");    ;    
        }       
        $where['_string'] ="
        ires_lesson.jur = 3 AND
        ires_lesson.is_del = 0
        OR ires_lesson.jur = 2
	AND ires_lesson.school_id = $school_id AND
        ires_lesson.is_del = 0
	OR ires_lesson.jur = 1
        AND ires_lesson.is_del = 0
	AND ires_lesson.emp_no = $user_id";                          
        $count = M('lesson')->where($map)->where($where)->count();   
        $page  = new \Common\Lib\Page($count, 8);
        $page->rollPage = 7;
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow . ',' . $page->listRows;
        $list  = M('lesson') ->join($prefix . 'schooltype ON '. $prefix . 'lesson.type_id          = ' . $prefix . 'schooltype.id')
            ->join($prefix . 'topic ON '     . $prefix . 'lesson.topic_id         = ' . $prefix . 'topic.id')
            ->join($prefix . 'version ON '   . $prefix . 'lesson.ver_id           = ' . $prefix . 'version.id')
            ->join($prefix . 'gradeseg ON '  . $prefix . 'lesson.seg_id          = ' . $prefix . 'gradeseg.id')
            ->where($map)->where($where)->limit($limit)->field('ires_lesson.*,ires_schooltype.type_name,ires_topic.topic_name,ires_version.ver_name,ires_gradeseg.seg_name')->select();       
        $this->assign('page', $page->show());
        $this->assign('vlist', $list);        
        $this->assign('pid', $pid);      
        
       
        // 最新
        $model = M('lesson');      
        $where_map['lessonflag'] = array('neq',2);
        $del_list = $model->limit(5)->order('publishtime desc')->where($where)->where($where_map)->field('id,title,publishtime')->select(); 
        foreach ($del_list as $key => $vo) {         
            $del_list[$key]['imgs'] = '/ResSite/Public/Home/default/images/teach/kecheng.png';         
        }   
        
        $del_list_dow = $model->limit(5)->where($where_map)->where($where)->order('browsecnt desc')->field('id,title,publishtime')->select();
        foreach ($del_list_dow as $key => $vo) {            
            $del_list_dow[$key]['imgs'] = '/ResSite/Public/Home/default/images/teach/kecheng.png';          
        }    
        
        $type_list = M('schooltype')->select();        
        $this->assign('type_list', $type_list);
        $this->assign('del_list', $del_list);
        $this->assign('del_list_coll', $del_list_coll);
        $this->assign('del_list_dow', $del_list_dow);
        $this->display();
    }

    public function detail() { 
        $id = I('id');
        $inc_ = M('lesson')->where(['id'=>$id])->setInc('browsecnt');
        $type = session('std_tag');   
        $user_id = $this->user_id();            
        $school_id = $this->school_id();         
        $prefix = C('DB_PREFIX');
        $map['ires_lesson.id'] = $id;
        $lesson = M('lesson')
            ->join($prefix . 'schooltype ON '. $prefix . 'lesson.type_id  = ' . $prefix . 'schooltype.id')
            ->join($prefix . 'topic ON '     . $prefix . 'lesson.topic_id = ' . $prefix . 'topic.id')
            ->join($prefix . 'version ON '   . $prefix . 'lesson.ver_id   = ' . $prefix . 'version.id')
            ->join($prefix . 'gradeseg ON '  . $prefix . 'lesson.seg_id  = ' . $prefix . 'gradeseg.id')
            ->where($map)->field('ires_lesson.*,ires_schooltype.type_name,ires_topic.topic_name,ires_version.ver_name,ires_gradeseg.seg_name')->find();  

        $where_res['ires_lessondesign.lesson_id'] = $id;
        $where_res['ires_lessondesign.cid'] = 0;        
        $res_list = M('lessondesign')->where($where_res)->join($prefix . 'detail ON '. $prefix . 'lessondesign.res_id  = ' . $prefix . 'detail.id')->order('ires_lessondesign.step asc')->group('res_id')->field('ires_detail.*')->select();   
        foreach ($res_list as $key => $vo) {           
            $title = explode(".",$vo['downlink']);         
            $res_list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title[1].'.gif';        
        }     
        $where_form['ires_lessondesign.lesson_id'] = $id;
        $where_form['ires_lessondesign.cid'] = 1;        
        $form_list = M('lessondesign')->where($where_form)->join($prefix . 'exam_form ON '. $prefix . 'lessondesign.form_id  = ' . $prefix . 'exam_form.id')->order('ires_lessondesign.step asc')->group('form_id')->field('ires_exam_form.*')->select();                   
        //判断是否正在上课
//        $map_is['lesson_id'] = $id; 
//        $map_is['status'] = 1;
//        $is_step = M('lessonlist')->where($map_is)->find();
//        if($is_step){
//            $time = time();
//            //上课时长直接下课
//            if($is_step['starttime'] + 21600 < $time){
//                $end_time = $is_step['starttime'] + 21601;
//                M('lessonlist')->where($map_is)->setField('endtime',$end_time);
//                M('lessonlist')->where($map_is)->setField('status',0);                
//            }       
//            $is_step = 1;            
//        }else{
//            $is_step = 0;
//        }
        //推荐微课列表
         $map_wei['_string'] =         	
               "ires_lesson.jur = 3               
            AND ires_lesson.is_del = 0               
            OR	ires_lesson.jur = 2
            AND ires_lesson.is_del = 0 
            AND ires_lesson.school_id = $school_id           
            OR	ires_lesson.is_del = 0 
            AND ires_lesson.jur = 1
            AND ires_lesson.emp_no = $user_id"; 
        $thi_list = M('lesson')->where($map_wei)->where(['lessonflag'=> 2])->join($prefix . 'lessondesign ON '. $prefix . 'lesson.id  = ' . $prefix . 'lessondesign.lesson_id')->order('ires_lesson.browsecnt desc,ires_lesson.updatetime desc')->field('ires_lesson.*,ires_lessondesign.res_id')->limit(12)->select();   
        //$a = M("lessonlist")->getLastSql();        
        $this->assign('id', $id);       
        $this->assign('thi_list', $thi_list); 
        $this->assign('form_list', $form_list);
        $this->assign('res_list', $res_list); 
        $this->assign('lesson', $lesson); 
        $this->assign('type', $type);  
        $this->assign('is_step', $is_step);   
        $this->display();
    }

    public function attendlesson($id,$res_id = '') {            
        //课堂信息
        $inc = M('lesson')->where(['id'=>$id])->setInc('visitorcnt');
        if(!($res_id == '')){
            $inc_ = M('lesson')->where(['id'=>$id])->setInc('browsecnt');
        }        
        $prefix = C('DB_PREFIX');
        $where['ires_lesson.id'] = $id;
        $lesson = M('lesson')
            ->join($prefix . 'schooltype ON '. $prefix . 'lesson.type_id  = ' . $prefix . 'schooltype.id')
            ->join($prefix . 'topic ON '     . $prefix . 'lesson.topic_id = ' . $prefix . 'topic.id')
            ->join($prefix . 'version ON '   . $prefix . 'lesson.ver_id   = ' . $prefix . 'version.id')
            ->join($prefix . 'gradeseg ON '  . $prefix . 'lesson.seg_id  = ' . $prefix . 'gradeseg.id')
            ->where($where)->field('ires_lesson.*,ires_schooltype.type_name,ires_topic.topic_name,ires_version.ver_name,ires_gradeseg.seg_name')->find(); 
        //资源播放
        $resid = $res_id;        
        $map['id'] = $resid;
        $map['converflag'] = 1;
        $model = M('detail');
        $res_info = $model ->where($map)->find();  
        $res_info['down'] = $res_info['downlink']; 
        $res_info['down'] = explode('$$$', $res_info['down']);
        $res_info['down'] =  $res_info['down'][1];       
        $model_ver = M('version');
        $map_ver['id'] =  $res_info['ver_id'];
        $ver_name      =  $model_ver ->where($map_ver) -> find();
        $ver_name      =  $ver_name['ver_name'];		
        $downlink_arr  =  empty($res_info['downlink']) ? array() : explode('|||', $res_info['downlink']);
        $downlink      =  array();
       // $baseurl       =  !empty($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : rtrim("http://" . $_SERVER['SERVER_NAME'], '/');
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
                    'title' => $title,
                    'url' 	=> $fileurl,
                );
            }
        }          
        //上课步骤列表 
        $map_desgin['lesson_id'] = $id;
        $des_list = M('lessondesign')->where($map_desgin)->order('step asc')->select();
        foreach ($des_list as $key => $value) {
            $type = $value['cid'];            
            if($type == 0){
                $map_res['id'] = $value['res_id'];
                $res_name = M('detail')->where($map_res)->Field('title,downlink')->find();               
                $des_list[$key]['res_name'] = $res_name['title'];
                $title = explode(".",$res_name['downlink']);                 
                $des_list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title[1].'.gif';               
            }else{
                $map_form['id'] = $value['form_id'];
                $form_name = M('exam_form')->where($map_form)-> getField('name');  
                $des_list[$key]['form_name'] = $form_name;
                $des_list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/doc.gif';
            }            
        }      
        $type = session('std_tag');        
        if($type == 1){
            $user_id = $this->user_id();
            $school_id = $this->school_id();
            $map_sit['emp_no'] = $user_id;
            $map_sit['lesson_id'] = $id;
            $is_sit = M('lessonvisitor')->where($map_sit)->find();
            if(!($is_sit)){
                $date['emp_no'] = $user_id; 
                $date['lesson_id'] = $id; 
                $date['starttime'] = time();  
                $date['endtime'] = time()+7200;
                $date['school_id'] = $school_id;                
                $is_ = M('lessonvisitor')->add($date);
            }
        }
        $username = $this->user_name();        
        $this -> assign('username', $username); 
        $this->assign('id', $id);
        $this->assign('res_id', $res_id);        
        $this->assign('des_list', $des_list);
        $this -> assign('resdata_s', $resdata);       
        $this -> assign('res_li', $res);
        $this->assign('lesson', $lesson); 
        $this->assign('res', $lesson);          
        $this->display();
    }

    public function prepare() {
        $type_list = M('schooltype')->select();
        $this->assign('type_list', $type_list);   
        $model_list = M('category')->where('pid = 12')->field('id,name')->select();      
        $this->assign('model_list', $model_list);        
        
        $this->display();
    }

    public function updatelesson($id) {
        $prefix = C('DB_PREFIX');
        $map['ires_lesson.id'] = $id;
        $lesson = M('lesson')
            ->join($prefix . 'schooltype ON '. $prefix . 'lesson.type_id  = ' . $prefix . 'schooltype.id')
            ->join($prefix . 'topic ON '     . $prefix . 'lesson.topic_id = ' . $prefix . 'topic.id')
            ->join($prefix . 'version ON '   . $prefix . 'lesson.ver_id   = ' . $prefix . 'version.id')
            ->join($prefix . 'gradeseg ON '  . $prefix . 'lesson.seg_id  = ' . $prefix . 'gradeseg.id')
            ->where($map)->field('ires_lesson.*,ires_schooltype.type_name,ires_topic.topic_name,ires_version.ver_name,ires_gradeseg.seg_name')->find();        
        $type_list = M('schooltype')->select();
        $this->assign('type_list', $type_list);
        $this->assign('lesson', $lesson);    
        $this->display();
    }

    public function inster_(){
        $date = I('post.');   
        $lessonflag = $date['lessonflag'];
        $model = M('lesson'); 
        $date['school_id'] = $this->school_id();
        $date['emp_no'] = $this->user_id();
        $date['username'] = $this->user_name();
        $date['publishtime'] = time();
        $date['updatetime'] = time();               
        if($lessonflag == 1){                
            if (false === $model->create($date)) {
                $this->error($model->getError());
            }
            $list = $model->add($date);                         
            $this->redirect('design',array('title'=>$date['title'],'less_id'=>$list));   
        }else{
            $type = $date['type'];
            $desige['res_id'] = $date['res_id'];             
            $desige['publishtime'] = time();
            $desige['step'] = 1;
            $desige['cid'] = 0; 
            $desige['design'] = $date['remark'];            
            if($type == 2){         
                $data_detail['title'] = $date['res_new'];
                $data_detail['keywords'] = $date['res_new'];
                $data_detail['content'] = $date['res_new'];
                $data_detail['downlink'] = '本地下载$$$'.$date['downlink'];
                $data_detail['cid'] = $date['cid'];         
                $data_detail['jur'] = $date['jur'];
                $data_detail['type_id'] = $date['type_id'];
                $data_detail['topic_id'] = $date['topic_id'];
                $data_detail['ver_id'] = $date['ver_id'];
                $data_detail['seg_id'] = $date['seg_id'];
                $data_detail['filesize'] = $date['filesize'].'KB';
                $data_detail['publishtime'] = time(); 
                $data_detail['updatetime'] = time();
                $data_detail['click'] = rand(10,95);            
                $data_detail['commentflag'] = 0;
                $data_detail['privilege'] = 1;
                $data_detail['emp_no'] = session('emp_no');
                $data_detail['userid'] = session('user_id');
                $data_detail['aid'] = session('user_id');           
                $data_detail['school_id'] = session('school_id');
                $detail_id = M('detail')->add($data_detail);     
                $desige['res_id'] =  $detail_id;          
            }      
            if (false === $model->create($date)) {
                $this->error($model->getError());
            }
            $list_id = $model->add($date);  
            $desige['lesson_id'] = $list_id;         
            if (false === M('lessondesign')->create($desige)) {
                $this->error(M('lessondesign')->getError());
            }  
            $desige_id = M('lessondesign')->add($desige); 
            if($desige_id){                    
                $this->success('微课创建成功',U('index'),2);     
            }else{
                $this->error('微课创建失败',U('index'),2);            
            }                          
        }
    }   
    
    public function update_(){
        $date = I('post.');     
        $model = M('lesson');            
        $date['updatetime'] = time();       
        if (false === $model->create($date)) {
            $this->error($model->getError());
        }
        $list = $model->save($date);  
        if($list){
            $this->success('更新成功',U('index'),2);  
        }else{
            $this->error('更新失败',U('index'),2);  
        }        
    }   
    
    public function add_design(){
        $date = I('post.');        
        $type = $date['type'];       
        $model = M('lessondesign');  
        if($type == 3 || $type == 4){
            $lession_ = M('lesson')->where(['id' => $date['lesson_id']])->find();
            $data_detail['title'] = $date['res_new'];
            $data_detail['keywords'] = $date['res_new'];
            $data_detail['content'] = $date['res_new'];
            $data_detail['downlink'] = '本地下载$$$'.$date['downlink'];
            $data_detail['cid'] = $date['cid'];         
            $data_detail['jur'] = $lession_['jur'];
            $data_detail['type_id'] = $lession_['type_id'];
            $data_detail['topic_id'] = $lession_['topic_id'];
            $data_detail['ver_id'] = $lession_['ver_id'];
            $data_detail['seg_id'] = $lession_['seg_id'];
            $data_detail['filesize'] = $date['filesize'].'KB';
            $data_detail['publishtime'] = time(); 
            $data_detail['updatetime'] = time();
            $data_detail['click'] = rand(10,95);            
            $data_detail['commentflag'] = 0;
            $data_detail['privilege'] = 1;
            $data_detail['emp_no'] = session('emp_no');
            $data_detail['userid'] = session('user_id');
            $data_detail['aid'] = session('user_id');           
            $data_detail['school_id'] = session('school_id');
            $detail_id = M('detail')->add($data_detail);     
            $date['res_id'] = $detail_id;
        }        
        if($date['form_id'] == ''){
            $date['cid'] = 0;
        }else{
            $date['cid'] = 1;
        }     
        $date['publishtime'] = time();
        $date['publishtime'] = time();       
        if (false === $model->create($date)) {
            $this->error($model->getError());
        }
        $list = $model->add($date);        
        if($type == 1 || $type == 3){
            $this->redirect('design',array('title'=>$date['title'],'less_id'=>$date['lesson_id'],'nober'=> $date['step'])); 
        }else{
            $this->success('备课成功',U('index'),2);            
        }              
    }   
    
    public function design() {           
        $model_list = M('category')->where('pid = 12')->field('id,name')->select();      
        $this->assign('model_list', $model_list);        
        $nober = I('nober');
        if($nober == ''){
           $nober = 1;            
        }else{
           $nober += 1;
        }
        $title = I('title');
        $less_id = I('less_id');        
        $this->assign('nober', $nober);   
        $this->assign('title', $title);    
        $this->assign('less_id', $less_id);    
        $this->display();
    }
    
    public function updatedesign($id){
        $prefix = C('DB_PREFIX');
        $map['ires_lesson.id'] = $id;  
        $map['ires_lesson.is_del'] = 0; 
        $map['ires_lessondesign.lesson_id'] = $id;
        $map['ires_lessondesign.is_del'] = 0;
        $my_lesson = M('lessondesign')
            ->join($prefix . 'lesson ON '    . $prefix . 'lessondesign.lesson_id = ' . $prefix . 'lesson.id')         
            ->where($map)->order('ires_lessondesign.step asc')
            ->field('ires_lessondesign.cid,ires_lessondesign.step,ires_lessondesign.design,ires_lessondesign.res_id,ires_lessondesign.form_id,ires_lessondesign.id as des_id,ires_lesson.*')->select();        
        foreach ($my_lesson as $key => $vo) {         
            $cid = $vo['cid'];
            if($cid == 0){
                $map_res['id'] = $vo['res_id'];                
                $res_name = M('detail')->where($map_res)->getField('title');               
                $my_lesson[$key]['res_name'] = $res_name;               
            }else{
                $map_form['id'] = $vo['form_id'];
                $form_name = M('exam_form')->where($map_form)->getField('name');      
                $my_lesson[$key]['res_name'] = $form_name;                
            }                   
        }    
        end($my_lesson);    
        $end_key = key($my_lesson);
        $my_lesson[$end_key]['end'] = 1;      
       // print_r($my_lesson);
        //echo M('lessondesign')->getlastSql();
        $this->assign('my_lesson', $my_lesson);      
        $this->display();
    }
    
    public function del_less() {
        $id = I('less_id');
        if (empty($id)) {
            $this->error('请选择需要删除的项!');
        }       
        $where['id'] = $id;
        $map['lesson_id'] = $id;
        $model = M('lesson');            
        $result = M('lessondesign')->where($map)->setField('is_del',1);
        $result = $model->where($where)->setField('is_del',1);
        if (!empty($result)) {
            $return['status'] = 1;
            $return['info'] = "操作成功!";
        } else {
            $return['info'] = '操作失败';
        }
        $this->ajaxReturn($return);       
    } 
    
    public function ret_less() {
        $id = I('less_id');       
        $map['lesson_id'] = $id;     
        $result = M('lessonlist')->where($map)->setField('status',0);
        if (!empty($result)) {
            $return['status'] = 1;
            $return['info'] = "操作成功!";
        } else {
            $return['info'] = '操作失败';
        }
        $this->ajaxReturn($return);       
    }  
    
    public function is_step_te() {
        $id = I('id');            
        $user_id = $this->user_id();
        $where['lesson_id'] = $id;
        $where['is_del'] = 0;      
        $result = M('lessondesign')->where($where)->Field('step')->select();
        $result_1 = $this->unique_arr($result);     
        $map['id'] = $id; 
        $list = M('lesson')->where($map)->Field('emp_no')->find();        
        if ($result == $result_1) {                 
            if($list['emp_no'] == $user_id){
                $data['school_id'] = $this->school_id();
                $data['starttime'] = time();
                $data['lesson_id'] = $id;
                $data['emp_no'] = $user_id;
                $data['status'] = 1;
                $list = M('lessonlist')->add($data);    
            }
            $return['status'] = 1;            
        } else {   
            if($list['emp_no'] == $user_id){
                $return['status'] = 2; 
                $return['info'] = '上课步骤不合理，请前往课程设计页面修改';
            }else{                
                $return['status'] = 3; 
                $return['info'] = '上课步骤不合理，暂时无法上课，需等待课程设计者修改';
            }
        }
        $this->ajaxReturn($return);       
    } 
    
    public function is_step_st() {
        $id = I('id');            
        $where['lesson_id'] = $id;
        $where['is_del'] = 0;      
        $result = M('lessondesign')->where($where)->Field('step')->select();
        $result_1 = $this->unique_arr($result);       
        if ($result == $result_1) {                    
            $return['status'] = 1;            
        } else {           
            $return['info'] = '上课步骤不合理，暂时不开放';
        }
        $this->ajaxReturn($return);       
    }    
    
    public function del_des() {
        $id = I('des_id');
        if (empty($id)) {
            $this->error('请选择需要删除的项!');
        }       
        $where['id'] = $id;   
        $result = M('lessondesign')->where($where)->setField('is_del',1);
        if (!empty($result)) {
            $return['status'] = 1;
            $return['info'] = "已成功删除该步骤!";
        } else {
            $return['info'] = '操作失败';
        }
        $this->ajaxReturn($return);       
    } 
    
    public function winpop_res() {             
        $less_id = I('less_id');        
        $where['id'] = $less_id;        
        $less_list = M('lesson')->where($where)->find();        
        $user_id = $this->user_id();      
        $school_id = $this->school_id();  
        if($user_id == '' || $school_id == ''){
           print_r('登陆信息丢失，请重新登陆');
           exit;
        }
        $type_id  = $less_list['type_id']?$less_list['type_id']:I('type_id');
        $topic_id = $less_list['topic_id']?$less_list['topic_id']:I('topic_id'); 
        $ver_id   = $less_list['ver_id']?$less_list['ver_id']:I('ver_id'); 
        $seg_id   = $less_list['seg_id']?$less_list['seg_id']:I('seg_id');  
        $map['_string'] =         	
           "ires_detail.type_id = $type_id
            AND ires_detail.topic_id = $topic_id
            AND ires_detail.ver_id = $ver_id
            AND ires_detail.seg_id = $seg_id	
            AND ires_detail.is_del = 0 
            AND ires_detail.jur = 3               
            OR	
            ires_detail.type_id = $type_id
            AND ires_detail.topic_id = $topic_id
            AND ires_detail.ver_id = $ver_id
            AND ires_detail.seg_id = $seg_id	
            AND ires_detail.is_del = 0 
            AND ires_detail.school_id = $school_id 
            AND ires_detail.jur = 2      
            OR	
            ires_detail.type_id = $type_id
            AND ires_detail.topic_id = $topic_id
            AND ires_detail.ver_id = $ver_id
            AND ires_detail.seg_id = $seg_id	
            AND ires_detail.is_del = 0
            AND ires_detail.emp_no = $user_id 
            AND ires_detail.jur = 1";            
        $pid = I('pid', 0, 'intval');        
        $count          = M('detail')->where(array('pid' => $pid))->where($map)->count();
        $page           = new \Common\Lib\Page($count, 10);
        $page->rollPage = 7;
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow . ',' . $page->listRows;
        $list  = M('detail')->where(array('pid' => $pid))->where($map)->order('publishtime desc')->limit($limit)->select();         
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->assign('pid', $pid);    
        $this->display();
    }     
    public function winpop_update_res() {  
        $less_id = I('less_id');
        $less_id_l = strstr($less_id, '|', ture);
        $less_id_r = substr($less_id,strpos($less_id,'|')+1);
        $where['id'] = $less_id_l;          
        $less_list = M('lesson')->where($where)->find();       
        $user_id = $this->user_id();      
        $school_id = $this->school_id();  
        if($user_id == '' || $school_id == ''){
           print_r('登陆信息丢失，请重新登陆');
           exit;
        }
        $type_id  = $less_list['type_id'];
        $topic_id = $less_list['topic_id']; 
        $ver_id   = $less_list['ver_id']; 
        $seg_id   = $less_list['seg_id'];  
        $map['_string'] =         	
           "ires_detail.type_id = $type_id
            AND ires_detail.topic_id = $topic_id
            AND ires_detail.ver_id = $ver_id
            AND ires_detail.seg_id = $seg_id	
            AND ires_detail.is_del = 0 
            AND ires_detail.jur = 3               
            OR	
            ires_detail.type_id = $type_id
            AND ires_detail.topic_id = $topic_id
            AND ires_detail.ver_id = $ver_id
            AND ires_detail.seg_id = $seg_id	
            AND ires_detail.is_del = 0 
            AND ires_detail.school_id = $school_id 
            AND ires_detail.jur = 2      
            OR	
            ires_detail.type_id = $type_id
            AND ires_detail.topic_id = $topic_id
            AND ires_detail.ver_id = $ver_id
            AND ires_detail.seg_id = $seg_id	
            AND ires_detail.is_del = 0
            AND ires_detail.emp_no = $user_id 
            AND ires_detail.jur = 1";            
        $pid = I('pid', 0, 'intval');        
        $count          = M('detail')->where(array('pid' => $pid))->where($map)->count();
        $page           = new \Common\Lib\Page($count, 10);
        $page->rollPage = 7;
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow . ',' . $page->listRows;
        $list  = M('detail')->where(array('pid' => $pid))->where($map)->order('publishtime desc')->limit($limit)->select();         
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->assign('pid', $pid); 
        $this->assign('less_id_r', $less_id_r);    
        $this->display();
    } 
    
    public function winpop_form() {             
        $less_id = I('less_id');
        $where['id'] = $less_id;        
        $less_list = M('lesson')->where($where)->find();      
        $user_id = $this->user_id();      
        $school_id = $this->school_id();  
        $type_id  = $less_list['type_id']?$less_list['type_id']:I('type_id');
        $topic_id = $less_list['topic_id']?$less_list['topic_id']:I('topic_id');          
        $map['_string'] =         	
           "ires_exam_form.schooltype_id = $type_id
            AND ires_exam_form.topic_id = $topic_id           
            AND ires_exam_form.is_del = 0 
            AND ires_exam_form.jur = 3               
            OR	
            ires_exam_form.schooltype_id = $type_id
            AND ires_exam_form.topic_id = $topic_id
            AND ires_exam_form.is_del = 0 
            AND ires_exam_form.school_id = $school_id 
            AND ires_exam_form.jur = 2      
            OR	
            ires_exam_form.schooltype_id = $type_id
            AND ires_exam_form.topic_id = $topic_id          
            AND ires_exam_form.is_del = 0
            AND ires_exam_form.uid = $user_id 
            AND ires_exam_form.jur = 1";            
        $pid = I('pid', 0, 'intval');        
        $count          = M('exam_form')->where(array('pid' => $pid))->where($map)->count();
        $page           = new \Common\Lib\Page($count, 10);
        $page->rollPage = 7;
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow . ',' . $page->listRows;
        $list  = M('exam_form')->where(array('pid' => $pid))->where($map)->order('creattime desc')->limit($limit)->select();       
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->assign('pid', $pid);    
        $this->display();
    }
    
    public function winpop_update_form() {             
        $less_id = I('less_id');
        $less_id_l = strstr($less_id, '|', ture);
        $less_id_r = substr($less_id,strpos($less_id,'|')+1);
        $where['id'] = $less_id_l;     
        $less_list = M('lesson')->where($where)->find();      
        $user_id = $this->user_id();      
        $school_id = $this->school_id();  
        $type_id  = $less_list['type_id'];
        $topic_id = $less_list['topic_id'];       
        $map['_string'] =         	
           "ires_exam_form.schooltype_id = $type_id
            AND ires_exam_form.topic_id = $topic_id           
            AND ires_exam_form.is_del = 0 
            AND ires_exam_form.jur = 3               
            OR	
            ires_exam_form.schooltype_id = $type_id
            AND ires_exam_form.topic_id = $topic_id
            AND ires_exam_form.is_del = 0 
            AND ires_exam_form.school_id = $school_id 
            AND ires_exam_form.jur = 2      
            OR	
            ires_exam_form.schooltype_id = $type_id
            AND ires_exam_form.topic_id = $topic_id          
            AND ires_exam_form.is_del = 0
            AND ires_exam_form.uid = $user_id 
            AND ires_exam_form.jur = 1";            
        $pid = I('pid', 0, 'intval');        
        $count          = M('exam_form')->where(array('pid' => $pid))->where($map)->count();
        $page           = new \Common\Lib\Page($count, 10);
        $page->rollPage = 7;
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow . ',' . $page->listRows;
        $list  = M('exam_form')->where(array('pid' => $pid))->where($map)->order('creattime desc')->limit($limit)->select();       
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->assign('pid', $pid);    
        $this->assign('less_id_r', $less_id_r); 
        $this->display();
    } 
    
    public function update_desgin() {           
        $date = I('post.');     
        $model = M('lessondesign');            
        $date['updatetime'] = time();       
        if (false === $model->create($date)) {
            $this->error($model->getError());
        }
        $list = $model->save($date);  
        if ($list) {
            $return['status'] = 1;
            $return['info'] = "修改成功!";
        } else {
            $return['info'] = '操作失败';
        }
        $this->ajaxReturn($return);  
    }

}
