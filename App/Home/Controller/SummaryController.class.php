<?php
/**
 * @Author: ispCMS
 * @Date:   2014-06-21 10:00:00
 * @Last Modified by:   ispCMS
 * @Last Modified time: 2016-06-21 12:39:28
 */

namespace Home\Controller;

class SummaryController extends HomeCommonController
{
    //方法：index
    public function index()
    {
        $user_type = session('user_type');
        $school_id = $this->school_id();       
        if($school_id == ''){
            $this->error('无访问权限','/sns');            
        }
        //获取年级列表
        $model = M('grade',C('DB_OA.DB_PREFIX'),C('DB_OA'));
        $grade_list = $model->where(['school_id'=>$school_id,'is_del'=>0])->order('sort asc,id asc')->Field('id,grade_name')->select();
        
        //学校Logo
        $map_school['id'] = $school_id;              
        $school_list = M('school',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where($map_school)->find();             
        if($school_list['logourl'] == ''){
            $map_school_admin['id'] = 1000;//默认学校图片 
            $school_list_admin = M('school',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where($map_school_admin)->find();
            $school_list['logourl'] = $school_list_admin['logourl'];
        }    
        $this->assign('user_type', $user_type); 
        $this->assign('school_list', $school_list);       
        $this->assign('grade_list',$grade_list);
        $this->display();
    }
    
    public function get_school_item(){     
        $school_id = $this->school_id();        
        $item_mc = M('position',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where(['school_id'=>$school_id,'is_del'=>0])->order('sort asc')->Field('id,name')->select();
        foreach ($item_mc as $key => $value) {           
            $map_form['position_id'] = $value['id'];
            $map_form['school_id'] = $school_id;            
            $number = M('user',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where(['is_del'=>0])->where($map_form)->count();
            $fen_data[$key]['value'] = $number;
            $fen_data[$key]['name'] = $item_mc[$key]['name'];             
        }                              
        if ($fen_data) {  
            $return['status'] = 1; 
            $return['list'] = $fen_data;         
        } else {
            $return['status'] = 2;
            $return['info'] = null;   
        }
        $this -> ajaxReturn($return);
    } 
    
    public function get_res_item(){     
        $school_id = $this->school_id();        
        $item_mc = M('category')->where(['pid'=>12,'status'=>0])->order('sort asc')->Field('id,name')->select();
        foreach ($item_mc as $key => $value) {           
            $map_form['cid'] = $value['id'];
            $map_form['school_id'] = $school_id;            
            $number = M('detail')->where(['is_del'=>0])->where($map_form)->count();
            $fen_data[$key]['value'] = $number;
            $fen_data[$key]['name'] = $item_mc[$key]['name'];             
        }                              
        if ($fen_data) {  
            $return['status'] = 1; 
            $return['list'] = $fen_data;         
        } else {
            $return['status'] = 2;
            $return['info'] = null;   
        }
        $this -> ajaxReturn($return);
    } 
    
    public function get_wis_item(){     
        $school_id = $this->school_id();        
        $item_mc = array(1=>'同步课堂',2=>'微课');  
        foreach ($item_mc as $key => $value) {           
            $map_form['lessonflag'] = $key;
            $map_form['school_id'] = $school_id;
            $number = M('lesson')->where(['is_del'=>0])->where($map_form)->count();
            $fen_data[$key]['value'] = $number;
            $fen_data[$key]['name'] = $item_mc[$key];        
        }                              
        if ($fen_data) {  
            $return['status'] = 1; 
            $return['list'] = $fen_data;         
        } else {
            $return['status'] = 2;
            $return['info'] = null;   
        }
        $this -> ajaxReturn($return);
    }  
    //资源相关统计
    public function res_list(){        
        $school_id = $this->school_id();        
        $time_fw = I('time_fw');   
        $date_first = $time_fw['time_st'];
        $date_last =  $time_fw['time_no'];
        if(!($date_first == '') && !($date_last == '')){    
            $date_first = strtotime($date_first);
            $date_last  = strtotime($date_last);    
            $map_time['publishtime'] = array('between', array($date_first, $date_last));         
        }
        $page = I('page');     
        $pageSize = I('pageSize');      
        $offset = ($page - 1) * $pageSize;      
        $prefix = C('DB_PREFIX');       
        //学校老师数目
        $teach_list = M('user',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where(['school_id'=>$school_id])->Field('id,name,emp_no')->limit($offset,$pageSize)->select();
        $total = M('user',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where(['school_id'=>$school_id])->count();  
        $model = M('detail');         
        foreach ($teach_list as $key => $value) {
            $tlist[$value['id']] = $model->where($map_time)->where(['userid'=>$value['id'],'is_del'=>0])->Field('jur,cid')->select();
            $teach_list_is[$value['id']] = $value;
        }     
        unset($teach_list);
        foreach ($tlist as $key => $value) {   
            $number_1 = 0;$number_2 = 0;$number_3 = 0;$number_4 = 0;$number_5 = 0;$number_6 = 0;$number_7 = 0;$number_8 = 0;$number_9 = 0; 
            if(!($tlist[$key] == '')){
                foreach ($tlist[$key] as $ke => $val) {                   
                    if($val['jur'] == 3 && $val['cid'] == 17){
                        $number_1 = $number_1 +1;                                                
                    }  
                    if($val['jur'] == 2 && $val['cid'] == 17){
                        $number_2 = $number_2 + 1;                                                
                    }  
                    if($val['jur'] == 1 && $val['cid'] == 17){
                        $number_3 = $number_3 + 1;                                                
                    } 
                    if($val['jur'] == 3 && $val['cid'] == 18){
                        $number_4 = $number_4 + 1;                                                
                    }  
                    if($val['jur'] == 2 && $val['cid'] == 18){
                        $number_5 = $number_5 + 1;                                                
                    }  
                    if($val['jur'] == 1 && $val['cid'] == 18){
                        $number_6 = $number_6 + 1;                                                
                    }
                    if($val['jur'] == 3 && $val['cid'] == 16){
                        $number_7 = $number_7 + 1;                                                
                    }  
                    if($val['jur'] == 2 && $val['cid'] == 16){
                        $number_8 = $number_8 + 1;                                                
                    }  
                    if($val['jur'] == 1 && $val['cid'] == 16){
                        $number_9 = $number_9 + 1;                                                
                    }                    
                }         
            }   
            $teach_list_is[$key]['number_1'] = $number_1;
            $teach_list_is[$key]['number_2'] = $number_2;
            $teach_list_is[$key]['number_3'] = $number_3;
            $teach_list_is[$key]['number_4'] = $number_4; 
            $teach_list_is[$key]['number_5'] = $number_5;
            $teach_list_is[$key]['number_6'] = $number_6;
            $teach_list_is[$key]['number_7'] = $number_7;
            $teach_list_is[$key]['number_8'] = $number_8;
            $teach_list_is[$key]['number_9'] = $number_9;             
        }                 
        $teach_list_is = array_values($teach_list_is);
        $rows = $teach_list_is;                  
        $this->ajaxReturn(compact('total','rows'),'JSON'); 
    }  

    //课程相关统计  
    public function less_list(){        
        $school_id = $this->school_id();        
        $time_fw = I('time_fw');   
        $date_first = $time_fw['time_st'];
        $date_last =  $time_fw['time_no'];
        if(!($date_first == '') && !($date_last == '')){    
            $date_first = strtotime($date_first);
            $date_last  = strtotime($date_last);    
            $map_time['publishtime'] = array('between', array($date_first, $date_last));         
        }
        $page = I('page');     
        $pageSize = I('pageSize');      
        $offset = ($page - 1) * $pageSize;      
        $prefix = C('DB_PREFIX');       
        //学校老师数目
        $teach_list = M('user',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where(['school_id'=>$school_id])->Field('id,name,emp_no')->limit($offset,$pageSize)->select();
        $total = M('user',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where(['school_id'=>$school_id])->count();  
        $model = M('lesson');         
        foreach ($teach_list as $key => $value) {
            $tlist[$value['id']] = $model->where($map_time)->where(['emp_no'=>$value['id'],'is_del'=>0])->Field('jur,lessonflag')->select();
            $teach_list_is[$value['id']] = $value;
        }     
        unset($teach_list);
        foreach ($tlist as $key => $value) {   
            $number_1 = 0;$number_2 = 0;$number_3 = 0;$number_4 = 0;$number_5 = 0;$number_6 = 0;
            if(!($tlist[$key] == '')){
                foreach ($tlist[$key] as $ke => $val) {                   
                    if($val['jur'] == 3 && $val['lessonflag'] == 1){
                        $number_1 = $number_1 +1;                                                
                    }  
                    if($val['jur'] == 2 && $val['lessonflag'] == 1){
                        $number_2 = $number_2 + 1;                                                
                    }  
                    if($val['jur'] == 1 && $val['lessonflag'] == 1){
                        $number_3 = $number_3 + 1;                                                
                    } 
                    if($val['jur'] == 3 && $val['lessonflag'] == 2){
                        $number_4 = $number_4 + 1;                                                
                    }  
                    if($val['jur'] == 2 && $val['lessonflag'] == 2){
                        $number_5 = $number_5 + 1;                                                
                    }  
                    if($val['jur'] == 1 && $val['lessonflag'] == 2){
                        $number_6 = $number_6 + 1;                                                
                    }
                }                 
            }        
            $teach_list_is[$key]['number_1'] = $number_1;
            $teach_list_is[$key]['number_2'] = $number_2;
            $teach_list_is[$key]['number_3'] = $number_3;
            $teach_list_is[$key]['number_4'] = $number_4; 
            $teach_list_is[$key]['number_5'] = $number_5;
            $teach_list_is[$key]['number_6'] = $number_6;     
        }             
        
        $teach_list_is = array_values($teach_list_is);
        $rows = $teach_list_is;                  
        $this->ajaxReturn(compact('total','rows'),'JSON'); 
    } 
    
   //试卷习题相关统计  
    public function title_list(){        
        $school_id = $this->school_id();        
        $time_fw = I('time_fw');   
        $date_first = $time_fw['time_st'];
        $date_last =  $time_fw['time_no'];
        if(!($date_first == '') && !($date_last == '')){    
            $date_first = strtotime($date_first);
            $date_last  = strtotime($date_last);    
            $map_time['creattime'] = array('between', array($date_first, $date_last));         
        }
        $page = I('page');     
        $pageSize = I('pageSize');      
        $offset = ($page - 1) * $pageSize;      
        $prefix = C('DB_PREFIX');       
        //学校老师数目
        $teach_list = M('user',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where(['school_id'=>$school_id])->Field('id,name,emp_no')->limit($offset,$pageSize)->select();
        $total = M('user',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where(['school_id'=>$school_id])->count();  
        $model = M('exam_form');         
        foreach ($teach_list as $key => $value) {
            $tlist[$value['id']]['exam'] = $model->where($map_time)->where(['uid'=>$value['id'],'is_del'=>0])->Field('jur as exam_jur')->select();
            $tlist[$value['id']]['title'] = M('exam_title')->where($map_time)->where(['uid'=>$value['id'],'is_del'=>0])->Field('jur as title_jur')->select();
            $teach_list_is[$value['id']] = $value;
        }             
        unset($teach_list);
        foreach ($tlist as $key => $value) {   
            $number_1 = 0;$number_2 = 0;$number_3 = 0;$number_4 = 0;$number_5 = 0;$number_6 = 0;
            if(!($tlist[$key]['title'] == '')){
                foreach ($tlist[$key]['title'] as $ke => $val) {                   
                    if($val['title_jur'] == 3){
                        $number_1 = $number_1 +1;                                                
                    }  
                    if($val['title_jur'] == 2){
                        $number_2 = $number_2 + 1;                                                
                    }  
                    if($val['title_jur'] == 1){
                        $number_3 = $number_3 + 1;                                                
                    }
                }         
            }
            if(!($tlist[$key]['exam'] == '')){
                foreach ($tlist[$key]['exam'] as $ke => $val) {          
                    if($val['exam_jur'] == 3){
                        $number_4 = $number_4 + 1;                                                
                    }  
                    if($val['exam_jur'] == 2){
                        $number_5 = $number_5 + 1;                                                
                    }  
                    if($val['exam_jur'] == 1){
                        $number_6 = $number_6 + 1;                                                
                    }
                }                        
            }    
            $teach_list_is[$key]['number_1'] = $number_1;
            $teach_list_is[$key]['number_2'] = $number_2;
            $teach_list_is[$key]['number_3'] = $number_3;
            $teach_list_is[$key]['number_4'] = $number_4; 
            $teach_list_is[$key]['number_5'] = $number_5;
            $teach_list_is[$key]['number_6'] = $number_6;          
        }                 
        $teach_list_is = array_values($teach_list_is);
        $rows = $teach_list_is;                  
        $this->ajaxReturn(compact('total','rows'),'JSON'); 
    }       
    
    public function get_class_st(){             
        $grade_id = I('grade_id');       
        $school_id = $this->school_id();
        $class_list = M('class',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where(['grade_id'=>$grade_id,'is_del'=>0])->order('sort asc,id asc')->Field('id,class_name as name')->select();
        $bignaber = 0;
        foreach ($class_list as $key => $value) {      
            $map_st['class_id'] = $value['id'];
            $b_number = M('student',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where(['is_del'=>0,'sex'=>'男'])->where($map_st)->count();
            $g_number = M('student',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where(['is_del'=>0,'sex'=>'女'])->where($map_st)->count();   
            if($b_number > $bignaber){
                $bignaber = $b_number;
            }  
            if($bignaber < $g_number){
                $bignaber = $g_number;
            }
            $class_list[$key]['b_number'] = $b_number;
            $class_list[$key]['g_number'] = $g_number;             
        }          
        $bignaber = ceil($bignaber/10)*10;
        $nuber = $bignaber/10;
        if($nuber == 1){
            $nuber =2;
        }
        if ($class_list) {             
            $return['bignaber'] = $bignaber;
            $return['nuber'] = $nuber;
            $return['status'] = 1; 
            $return['list'] = $class_list;         
        } else {
            $return['status'] = 2;
            $return['info'] = null;   
        }
        $this -> ajaxReturn($return);
    }  
    
}
