<?php

namespace Home\Widget;

use Think\Controller;

class ExamTitleWidget extends Controller {

    public function view($data) {
        $type = $data['id'];
        $letterDB = array("a", "b", "c", "d", "e", "f", "g", "h");
        $this->assign('letterDB', $letterDB);
        if ($type == 1 || $type == 2 || $type == 3 || $type == 5) {
            foreach ($data['list'] as $key => $value) {
                $data['list'][$key]['detail'] = explode(",", $value['config']);
            }
        } else if ($type == 6 || $type == 4) {
            foreach ($data['list'] as $key => $value) {
                $data['list'][$key]['question'] = explode(",", $value['question']);
            }
        }
        $this->assign('data', $data);
        switch ($type) {
            case '1' :
                $content = $this->display('Widget:ExamTitleType/1');
                break;
            case '2' :
                $content = $this->display('Widget:ExamTitleType/2');
                break;
            case '3' :
                $content = $this->display('Widget:ExamTitleType/3');
                break;
            case '4' :
                $content = $this->display('Widget:ExamTitleType/4');
                break;
            case '5' :
                $content = $this->display('Widget:ExamTitleType/5');
                break;
            case '6' :
                $content = $this->display('Widget:ExamTitleType/6');
                break;
            case '7' :
                $content = $this->display('Widget:ExamTitleType/7');
                break;
            case '8' :
                $content = $this->display('Widget:ExamTitleType/8');
                break;
            case '9' :
                $content = $this->display('Widget:ExamTitleType/9');
                break;
            default :
                $content = $this->display('Widget:ExamTitleType/1');
                break;
        }
    }   
    
    public function view_item($data) {
        $type = $data['id'];
        $letterDB = array("a", "b", "c", "d", "e", "f", "g", "h");
        $this->assign('letterDB', $letterDB);
        if ($type == 1 || $type == 2 || $type == 3 || $type == 5) {
            foreach ($data['list'] as $key => $value) {
                $data['list'][$key]['detail'] = explode(",", $value['config']);
            }
        } else if ($type == 6 || $type == 4) {
            foreach ($data['list'] as $key => $value) {
                $data['list'][$key]['question'] = explode(",", $value['question']);
            }
        }
        $this->assign('data', $data);
        switch ($type) {
            case '1' :
                $content = $this->display('Widget:ExamTitleType/a');
                break;
            case '2' :
                $content = $this->display('Widget:ExamTitleType/b');
                break;
            case '3' :
                $content = $this->display('Widget:ExamTitleType/c');
                break;
            case '4' :
                $content = $this->display('Widget:ExamTitleType/d');
                break;
            case '5' :
                $content = $this->display('Widget:ExamTitleType/e');
                break;
            case '6' :
                $content = $this->display('Widget:ExamTitleType/f');
                break;
            case '7' :
                $content = $this->display('Widget:ExamTitleType/g');
                break;
            case '8' :
                $content = $this->display('Widget:ExamTitleType/h');
                break;
            case '9' :
                $content = $this->display('Widget:ExamTitleType/i');
                break;
            default :
                $content = $this->display('Widget:ExamTitleType/a');
                break;
        }
    }

    function conv_data($val) {
        $new = array();
        if (strpos($val, "|") !== false) {
            $arr_tmp = explode(",", $val);
            foreach ($arr_tmp as $item) {
                $tmp = explode("|", $item);
                $new[$tmp[0]] = $tmp[1];
            }
        } else {
            $new = $val;
        }
        return $new;
    }

    //1 题目数 2 分数
    function nober($a,$val) {        
        $map['id'] = $val;
        $exam = M('exam_form')->where($map)->getField('config');
        $config = @unserialize($exam);
        $num_1 = 0;
        $num_2 = 0;
        if($a == 1){
            foreach ($config['numdb'] as $key => $value) {
                $num_1 += $value;
            }     
            $this->assign('val',$num_1);
            $content = $this->display('Widget:ExamTitleType/nober');
        }else if($a == 2){
            foreach ($config['numdb'] as $key => $value) {
                $num_2 += $config['numdb'][$key] * $config['fendb'][$key];
            } 
            $this->assign('val',$num_2);
            $content = $this->display('Widget:ExamTitleType/nober');                    
        }      
        return $content;
    } 
    //考试人数
    function popnober($val,$school_id) {        
        $map['form_id'] = $val;
        $map['school_id'] = $school_id;
        $exam = M('exam_student')->where($map)->count();       
        $this->assign('val',$exam);
        $content = $this->display('Widget:ExamTitleType/nober');         
        return $content;
    } 
    
    function name($uid) {        
        $map['id'] = $uid;
        $name = M('user',C('DB_OA.DB_PREFIX'),C('DB_OA'))->where($map)->getField('name');          
        $this->assign('name',$name);
        $content = $this->display('Widget:ExamTitleType/name');               
        return $content;
    }
}

?>