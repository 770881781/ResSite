<?php

/**
 * @Author: ispCMS
 * @Date:   2014-06-21 10:00:00
 * @Last Modified by:   ispCMS
 * @Last Modified time: 2016-06-21 12:39:28
 */

namespace Home\Controller;

class ExamController extends HomeCommonController {

    protected $list_type = array(
        1  =>  '单选题' ,  2  =>  '多选题'	,  
        3  =>  '判断题' ,  4  =>  '填空题'	,  
        5  =>  '排序题'	,  6  =>  '计算题'	,  
        7  =>  '简答题'	,  8  =>  '问答题'	,  
        9  =>  '作文题' );
				
    protected $chinese_num = array(
        1 => '一', 2 => '二',
        3 => '三', 4 => '四',
        5 => '五', 6 => '六',
        7 => '七', 8 => '八',     
    );
    
    //方法：index 
    public function index() {        
        $prefix = C('DB_PREFIX');        
        $user_id = $this->user_id();      
        $school_id = $this->school_id(); 
        if($user_id == '' || $school_id == ''){
            $this->error('请重新登录！','/sns');
        }  
        $type = session('std_tag');  
        $this->assign('type', $type);
        $map['_string'] = "ires_exam_form.school_id = $school_id  AND ires_exam_form.jur = 2 AND ires_exam_form.is_del = 0 OR ires_exam_form.uid = $user_id  AND ires_exam_form.jur = 1 AND ires_exam_form.is_del = 0 OR ires_exam_form.school_id = $school_id AND jur = 3";  
        $school_exam_list = M('exam_form')->where($map)->join($prefix . 'schooltype ON ' . $prefix . 'exam_form.schooltype_id = ' . $prefix . 'schooltype.id')->join($prefix . 'topic ON ' . $prefix . 'exam_form.topic_id = ' . $prefix . 'topic.id')->field('ires_exam_form.*,ires_schooltype.type_name,ires_topic.topic_name')->limit(4)->order('ires_exam_form.creattime desc')->select();
//       $a = M('exam_form')->getLastSql();
//       print_r($a);
//       exit;
        $this->assign('school_exam_list', $school_exam_list);
        //最新试卷展示       
        $new_exam_list = M("exam_form")->join($prefix . 'schooltype ON ' . $prefix . 'exam_form.schooltype_id = ' . $prefix . 'schooltype.id')->join($prefix . 'topic ON ' . $prefix . 'exam_form.topic_id = ' . $prefix . 'topic.id')->where($map)->limit(8)->order('creattime desc')->field('ires_exam_form.id,ires_exam_form.hits,ires_exam_form.name,ires_schooltype.type_name,ires_topic.topic_name')->select();
        $this->assign('new_exam_list', $new_exam_list);

        //学段列表        		
        $exam_type_list = M("schooltype")->order('sort asc')->field('id,type_name')->select();
        $this->assign('exam_type_list', $exam_type_list);

        //轮播图管理
        $map_1['aid'] = 2;
        $map_1['status'] = 0;
        $model_abc = M('abc_detail')->where($map_1)->select();
        $this->assign('model_abc', $model_abc);  

        //考试首页资讯              
        $map_art['_string'] = "school_id = $school_id AND cid = 5 AND sort = 0";//头条资讯查询 
        $map_art_three['_string'] = "school_id = $school_id AND cid = 5";//三条资讯查询        
        $art_list = M('article')->order('updatetime desc')->where($map_art)->limit(1)->find();      
        $this->assign('art_list', $art_list);
        $art_list_three = M('article')->where($map_art_three)->limit(3)->order('updatetime desc')->select();
        $this->assign('art_list_three', $art_list_three);
                
  
        //学段下科目列表               
        foreach ($exam_type_list as $value) {
            $where['schooltype_id'] = $value['id'];
            $topic_id_list[] = M('exam_form')->where($where)->field('schooltype_id,topic_id')->select();
        }
        foreach ($topic_id_list as $value) {
            $value = $this->unique_arr($value);
            foreach ($value as $key => $valuel) {
                $value_list[$valuel['schooltype_id']][] = $valuel['topic_id'];
            }
        }
        foreach ($value_list as $key => $value) {
            $value = array_unique($value);
            $value_list_exam[$key] = $value;
        }
        foreach ($value_list_exam as $key => $value_topic_id) {
            foreach ($value_topic_id as $id) {
                $map_topic['id'] = $id;
                $topic_name = M('topic')->where($map_topic)->getField('topic_name');
                $type_topic_id[$key][] = $id . '|' . $topic_name;
            }
        }
        
        $this->assign('value_list_exam', $type_topic_id);
        $this->assign('topic_name', $topic_name);
        $this->display();
    }

    public function get_exam_list() {
        $prefix = C('DB_PREFIX');
        $type_topic_id = I('topic_id');
        $type_topic_id = explode('|', $type_topic_id);        
        $school_id = $this->school_id();   
        $user_id = $this->user_id();        
        //首页本校试卷展示     
        if($user_id == ''){
            $user_id = 0;
        }        
        $map['_string'] = "ires_exam_form.school_id = $school_id  AND ires_exam_form.jur = 2 AND ires_exam_form.is_del = 0 OR ires_exam_form.uid = $user_id  AND ires_exam_form.jur = 1 AND ires_exam_form.is_del = 0 OR jur = 3 AND ires_exam_form.is_del = 0 ";
        $map['schooltype_id'] = $type_topic_id[1];
        $map['topic_id'] = $type_topic_id[0];
        $model = M('exam_form');
        $exam_list = $model->where($map)->join($prefix . 'schooltype ON ' . $prefix . 'exam_form.schooltype_id = ' . $prefix . 'schooltype.id')->join($prefix . 'topic ON ' . $prefix . 'exam_form.topic_id = ' . $prefix . 'topic.id')->limit(6)->order('creattime desc')->field('ires_exam_form.id,ires_exam_form.schooltype_id,ires_exam_form.topic_id,ires_exam_form.creattime,ires_exam_form.name,ires_schooltype.type_name,ires_topic.topic_name')->select();
        if ($exam_list !== false) {// 读取成功
            $return['exam_list'] = $exam_list;
            $return['status'] = 1;
            $return['info'] = "读取成功";
        } else {
            $return['status'] = 0;
            $return['info'] = "读取错误";
        }
        $this->ajaxReturn($return);
    }

    public function get_topic_id() {
        $type_id = I('type_id');
        $where['schooltype_id'] = $type_id;
        $topic_id_list[] = M('exam_form')->where($where)->field('schooltype_id,topic_id')->select();
        foreach ($topic_id_list as $value) {
            $value = $this->unique_arr($value);
            foreach ($value as $key => $valuel) {
                $value_list[$valuel['schooltype_id']][] = $valuel['topic_id'];
            }
        }
        $value_list_exam = $value_list[$type_id];
        if ($exam_list !== false) {// 读取成功
            $return['topic_id'] = $value_list_exam;
            $return['status'] = 1;
            $return['info'] = "读取成功";
        } else {
            $return['status'] = 0;
            $return['info'] = "读取错误";
        }
        $this->ajaxReturn($return);
    }

    public function details() {
        $prefix = C('DB_PREFIX');
        $type = session('std_tag');       
        //试卷信息
        $exam_id = I('exam_id');
        $list = M('exam_form')->where(['id'=>$exam_id])->setInc('hits');        
        //print_r($list);
        $map['ires_exam_form.id'] = $exam_id;    
        $model = M('exam_form');        
        $exam_details = $model->where($map)->join($prefix . 'schooltype ON ' . $prefix . 'exam_form.schooltype_id = ' . $prefix . 'schooltype.id')->join($prefix . 'topic ON ' . $prefix . 'exam_form.topic_id = ' . $prefix . 'topic.id')->field('ires_exam_form.*,ires_schooltype.type_name,ires_topic.topic_name')->select();
        //最新试卷
        $new_exam_list = M("exam_form")->join($prefix . 'schooltype ON ' . $prefix . 'exam_form.schooltype_id = ' . $prefix . 'schooltype.id')->join($prefix . 'topic ON ' . $prefix . 'exam_form.topic_id = ' . $prefix . 'topic.id')->where(['ires_exam_form.is_del'=> 0])->limit(6)->order('creattime desc')->field('ires_exam_form.id,ires_exam_form.name,ires_exam_form.creattime,ires_schooltype.type_name,ires_topic.topic_name')->select();
        //最热试卷
        $hot_exam_list = M("exam_form")->join($prefix . 'schooltype ON ' . $prefix . 'exam_form.schooltype_id = ' . $prefix . 'schooltype.id')->join($prefix . 'topic ON ' . $prefix . 'exam_form.topic_id = ' . $prefix . 'topic.id')->where(['ires_exam_form.is_del'=> 0])->limit(6)->order('hits desc')->field('ires_exam_form.id,ires_exam_form.name,ires_exam_form.creattime,ires_schooltype.type_name,ires_topic.topic_name')->select();
        $this->assign('type', $type);
        $this->assign('new_exam_list', $new_exam_list);
        $this->assign('hot_exam_list', $hot_exam_list);
        $this->assign('exam_details', $exam_details);
        $this->display();
    }

    public function more() {
        $prefix = C('DB_PREFIX');
        $type_topic_id = I('type_topic_id');
        $this->assign('type_topic_id', $type_topic_id);
        $select = I('exam_name');       
        if (!empty($select)) {             
            $map['ires_exam_form.name'] = array('like', "%" . $select . "%");
        }   
        if ($type_topic_id == undefined) {
            $map['ires_exam_form.school_id'] = $this->school_id();
            $this->assign('school_topic_name', '本校试卷');
        } else {
            $map['ires_exam_form.schooltype_id'] = strstr($type_topic_id, '|', ture);
            $map['ires_exam_form.topic_id'] = substr($type_topic_id, strpos($type_topic_id, '|') + 1);
            //获取学段学科名
            $map_topic['ires_topic.id'] = $map['ires_exam_form.topic_id'];
            $school_topic_name = M("topic")->where($map_topic)->join($prefix . 'schooltype ON ' . $prefix . 'schooltype.id = ' . $prefix . 'topic.type_id')->field('topic_name,type_name')->select();
            $school_topic_name = $school_topic_name[0]['type_name'] . $school_topic_name[0]['topic_name'];
            $this->assign('school_topic_name', $school_topic_name);
        }
        $map['_string'] = 'ires_exam_form.schooltype_id = ires_schooltype.id AND ires_exam_form.topic_id = ires_topic.id';
        $sort = 'ires_exam_form.creattime desc';
        $nober = 13; //每页显示条数
        $model = M("exam_form");
        if (!empty($model)) {
            $exam_form = $prefix . 'exam_form';
            $schooltype = $prefix . 'schooltype';
            $topic = $prefix . 'topic';
            $this->_list_res("$exam_form ires_exam_form,$schooltype ires_schooltype,$topic ires_topic", "ires_exam_form.*,ires_schooltype.type_name as schooltype_name,ires_topic.topic_name", $map, $sort, $nober);
        }

        //最新试卷
        $new_exam_list = M("exam_form")->join($prefix . 'schooltype ON ' . $prefix . 'exam_form.schooltype_id = ' . $prefix . 'schooltype.id')->join($prefix . 'topic ON ' . $prefix . 'exam_form.topic_id = ' . $prefix . 'topic.id')->where()->limit(6)->order('creattime desc')->field('ires_exam_form.id,ires_exam_form.name,ires_exam_form.creattime,ires_schooltype.type_name,ires_topic.topic_name')->select();

        //最热试卷
        $hot_exam_list = M("exam_form")->join($prefix . 'schooltype ON ' . $prefix . 'exam_form.schooltype_id = ' . $prefix . 'schooltype.id')->join($prefix . 'topic ON ' . $prefix . 'exam_form.topic_id = ' . $prefix . 'topic.id')->where()->limit(6)->order('hits desc')->field('ires_exam_form.id,ires_exam_form.name,ires_exam_form.creattime,ires_schooltype.type_name,ires_topic.topic_name')->select();

        $this->assign('new_exam_list', $new_exam_list);
        $this->assign('hot_exam_list', $hot_exam_list);
        $this->assign('exam_details', $exam_details);
        $this->display();
    }
   
    public function myresnew() {
        $type = session('std_tag');  
        $this->assign('type', $type);
        $user_id = $this->user_id();
        $keyword = I('keyword');
        if (!empty($keyword)) {
            $map['ires_exam_form.name'] = array('like', "%" . $keyword . "%");
        }
        $date_first = strtotime(I('date_first'));
        $date_last = strtotime(I('date_last'));     
        $prefix = C('DB_PREFIX');
        $nober = 25; //每页显示条数
        //学生试卷
        if($type == 1){  
            if (!empty($date_first)) {
                $map['ires_exam_student.posttime'] = array('between', array($date_first, $date_last));
            } 
            $map['ires_exam_student.student_uid'] = $user_id;  //获取当前登录信息
            $map['_string'] = 'ires_exam_student.form_id = ires_exam_form.id AND ires_exam_form.schooltype_id = ires_schooltype.id AND ires_exam_form.topic_id = ires_topic.id';
            $sort = 'ires_exam_student.posttime desc';           
            $model = M('exam_student');
            if (!empty($model)) {
                $exam_form = $prefix . 'exam_form';
                $exam_student = $prefix . 'exam_student';
                $schooltype = $prefix . 'schooltype';
                $topic = $prefix . 'topic';
                $this->_list_res("$exam_form ires_exam_form,$exam_student ires_exam_student,$schooltype ires_schooltype,$topic ires_topic", "ires_exam_student.*,ires_exam_form.id,ires_exam_form.name,ires_schooltype.type_name as type_name,ires_topic.topic_name", $map, $sort, $nober);
            }
        }else{
        //老师试卷
            if (!empty($date_first)) {
                $map['ires_exam_form.creattime'] = array('between', array($date_first, $date_last));
            } 
            $map['_string'] = 'ires_exam_form.schooltype_id = ires_schooltype.id AND ires_exam_form.topic_id = ires_topic.id';
            $map['ires_exam_form.uid'] = $user_id;  //获取当前登录信息
            $sort = 'ires_exam_form.creattime desc';           
            $model = M('exam_form');
            if (!empty($model)) {
                $exam_form = $prefix . 'exam_form';               
                $schooltype = $prefix . 'schooltype';
                $topic = $prefix . 'topic';
                $this->_list_res("$exam_form ires_exam_form,$schooltype ires_schooltype,$topic ires_topic", "ires_exam_form.*,ires_topic.topic_name,ires_schooltype.type_name", $map, $sort, $nober);
            }
        }
        $this->display();
    }

    public function get_exam_form_list() {
        $map['id'] = I('exam_id');
        //题目分数分值类型标题
        $exam_list = M('exam_form')->where($map)->find();
        $config = @unserialize($exam_list['config']);
        unset($vo['config']);
        foreach ($config[numdb] as $key => $value) {
            $vo['numdb' . '_' . $key . '|' . $this->list_type[$key]] = $value . '|' . $config[fendb][$key];
        }
        //具体题目
        $map_element['form_id'] = $map['id'];
        $element_list = M('exam_form_element')->where($map_element)->field('form_id,title_id')->select();
        if ($element_list) {
            $model = M('exam_title');
            foreach ($element_list as $value) {
                $map_title['id'] = $value['title_id'];
                $title_list[] = $model->where($map_title)->find();
            }
        }
        if ($exam_list !== false) {// 读取成功
            $return['title'] = $title_list;
            $return['config'] = $vo;
            $return['status'] = 1;
            $return['info'] = "读取成功";
        } else {
            $return['status'] = 0;
            $return['info'] = "读取错误";
        }
        $this->ajaxReturn($return);
    }

    public function form() {
        $exam_id = I('exam_id', "intval");        
        $exam_id = explode('|',  $exam_id);
        $map['id'] = $exam_id[0];
        $is_exam = $exam_id[1];
        //题目分数分值类型标题
        $exam_form = M('exam_form')->where($map)->find();
        $config = @unserialize($exam_form['config']);
        unset($vo['config']);
        foreach ($config[numdb] as $key => $value) {
            //具体题目
            $prefix = C('DB_PREFIX');
            $exam_form_element = $prefix . 'exam_form_element';
            $exam_title = $prefix . 'exam_title';

            $map_element['exam_form_element.form_id'] = $exam_form['id'];
            $map_element['exam_title.type'] = $key;
            $map_element['_string'] = 'exam_form_element.title_id = exam_title.id';
            $element_list = M()->table("$exam_form_element exam_form_element,$exam_title exam_title")->where($map_element)->select();            
            foreach ($element_list as $_key_ => &$element) {
                $element['_id_'] = $_key_ + 1;
            }
            $fenz = $config[fendb][$key]*$value;
            $vo_list[] = [
                'title_id' => $element_list['title_id'],
                'exam_id' => $map['id'],
                'id' => $key,
                'chinese_num' => $this->chinese_num[$key],
                'name' => $this->list_type[$key],
                'num' => $value,
                'fen' => $config[fendb][$key],
                'fenz' => $fenz,
                'list' => $element_list,
                'is_exam'=>$is_exam,
            ];
            unset($element_list);
        }               
        //print_r($vo_list);
        $this->assign('type_list', $vo_list);
        $this->display();
    }   
    
    public function form_item() {
        $exam_id = I('exam_id', "intval");        
        $exam_id = explode('|',  $exam_id);
        $map['id'] = $exam_id[0];
        $is_exam = $exam_id[1];
        //题目分数分值类型标题
        $exam_form = M('exam_form')->where($map)->find();
        $config = @unserialize($exam_form['config']);
        unset($vo['config']);
        foreach ($config[numdb] as $key => $value) {
            //具体题目
            $prefix = C('DB_PREFIX');
            $exam_form_element = $prefix . 'exam_form_element';
            $exam_title = $prefix . 'exam_title';

            $map_element['exam_form_element.form_id'] = $exam_form['id'];
            $map_element['exam_title.type'] = $key;
            $map_element['_string'] = 'exam_form_element.title_id = exam_title.id';
            $element_list = M()->table("$exam_form_element exam_form_element,$exam_title exam_title")->where($map_element)->select();            
            foreach ($element_list as $_key_ => &$element) {
                $element['_id_'] = $_key_ + 1;
            }
            $fenz = $config[fendb][$key]*$value;
            $vo_list[] = [
                'title_id' => $element_list['title_id'],
                'exam_id' => $map['id'],
                'id' => $key,
                'chinese_num' => $this->chinese_num[$key],
                'name' => $this->list_type[$key],
                'num' => $value,
                'fen' => $config[fendb][$key],
                'fenz' => $fenz,
                'list' => $element_list,
                'is_exam'=>$is_exam,
            ];
            unset($element_list);
        }               
        //print_r($vo_list);
        $this->assign('type_list', $vo_list);
        $this->display();
    }    
    
    public function examstar(){      
        $exam_id = I('exam_id');
        $type = session('std_tag');       
        $map['id'] =  $exam_id;
        $list = M('exam_form')->where($map)->find();      
        $time = time();
        $this->assign('list',$list);
        $this->assign('time',$time); 
        $this->assign('type',$type);
        $this->assign('exam_id',$exam_id);
        $this->display();
    } 
    
    public function is_exam(){
        $exam_data = I('post.');    
        if (!empty($exam_data)) {
            $this->success('违规操作',U('index'),5);
        }       
        $user_id = $this->user_id();       
        if($user_id){
            $where['ioa_student.id'] = $user_id;
            $prefix = C('DB_OA.DB_PREFIX'); 
            $stud = M('student',C('DB_OA.DB_PREFIX'),C('DB_OA'))
            ->join($prefix . 'grade ON ' . $prefix . 'student.grade_id = ' . $prefix . 'grade.id')  
            ->join($prefix . 'class ON ' . $prefix . 'student.class_id = ' . $prefix . 'class.id')    
            ->field('ioa_grade.grade_name,ioa_class.class_name,ioa_class.id as class_id')  
            ->where($where)
            ->find();            
        }      
       //考过的试卷入库
        $map['form_id'] = $exam_data['exam_id'];		
        $map['student_uid'] = $user_id;	               
        $list = M("exam_student") -> where($map) -> find(); 
        if(!($list)){
            $data_stu['form_id']  = $exam_data['exam_id'];
            $data_stu['posttime'] = time();
            $data_stu['student_uid']   = $user_id;
            $data_stu['student_name']  = $this->user_name();
            $data_stu['school_id']  = $this->school_id();   
            $data_stu['aclass']  = $stud['class_id'];
            $data_add = M('exam_student')->add($data_stu);      
        }        
        //提交的答案数据入库
        $data['form_id'] = $exam_data['exam_id'];    //试卷id
        $exam_data_pop = array_pop($exam_data);        
        $data['student_id'] = $this->user_id();    //模拟用户    
        $data['class_id'] = $stud['class_id'];    //
        $data['school_id'] = $this->school_id();    //  
        foreach ($exam_data as $key => $value) {           
            $key_q = strstr($key, '|', ture);            
            $key_h = substr($key,strpos($key,'|')+1);  
            $data['title_id'] = $key_h;
            if($key_q == 1 || $key_q == 3|| $key_q == 4|| $key_q == 5|| $key_q == 6|| $key_q == 7|| $key_q == 8){                
                $data['answer'] = $value;
                $is_exam = M('exam_student_title')->add($data);
            }else if($key_q == 2){                  
                $value_2 = implode(',',$value);
                $data['answer'] = $value_2;
                $is_exam_2 = M('exam_student_title')->add($data);                
            }        
            if($is_exam){                
                $this->success('试卷提交成功，待管理员评分后可在我的试卷查看',U('index'),3);
            }else{
                $this->success('违规操作',U('index'),3);
            }            
        }
    }
    
    public function is_user_exam(){
        $map['form_id'] = I('exam_id');		
        $map['student_uid'] = $this->user_id();	               
        $list = M("exam_student") -> where($map) -> find();                                
        if ($list) {
            $return['status'] = 2;
            $return['info'] = '您已经参加过该场考试，不能够重复参加！';           
        } else {
            $return['status'] = 1;
            $return['info'] = null;   
        }
        $this -> ajaxReturn($return);
    }
    //我的错题
    public function myresold() {
        $keyword = I('keyword');
        if (!empty($keyword)) {
            $map['ires_exam_form.name'] = array('like', "%" . $keyword . "%");
        }
        $date_first = strtotime(I('date_first'));
        $date_last = strtotime(I('date_last'));
        if (!empty($date_first)) {
            $map['ires_exam_student.posttime'] = array('between', array($date_first, $date_last));
        }
        $prefix = C('DB_PREFIX');
        $map['ires_exam_student.student_uid'] = $this->user_id();  //获取当前登录信息
        $map['_string'] = 'ires_exam_student.form_id = ires_exam_form.id AND ires_exam_form.schooltype_id = ires_schooltype.id AND ires_exam_form.topic_id = ires_topic.id';
        $sort = 'ires_exam_student.posttime desc';
        $nober = 25; //每页显示条数
        $model = M('exam_student');
        if (!empty($model)) {
            $exam_form = $prefix . 'exam_form';
            $exam_student = $prefix . 'exam_student';
            $schooltype = $prefix . 'schooltype';
            $topic = $prefix . 'topic';
            $this->_list_res("$exam_form ires_exam_form,$exam_student ires_exam_student,$schooltype ires_schooltype,$topic ires_topic", "ires_exam_student.*,ires_exam_form.id,ires_exam_form.name,ires_schooltype.type_name as type_name,ires_topic.topic_name", $map, $sort, $nober);
        }
        $this->display();
    }
    
     public function form_old() {
        $exam_id = I('exam_id', "intval");        
        $exam_id = explode('|',  $exam_id);
        $map['id'] = $exam_id[0];
        $is_exam = $exam_id[1];       
        //题目分数分值类型标题
        $exam_form = M('exam_form')->where($map)->find();
        $config = @unserialize($exam_form['config']);
        unset($vo['config']);
        foreach ($config[numdb] as $key => $value) {
            //具体题目
            $prefix = C('DB_PREFIX');
            $exam_form_element = $prefix . 'exam_form_element';
            $exam_student_title = $prefix . 'exam_student_title'; 
            $exam_title = $prefix . 'exam_title';
            $map_element['exam_student_title.is_old'] = 1;
            $map_element['exam_form_element.form_id'] = $exam_form['id'];
            $map_element['exam_title.type'] = $key;
            $map_element['_string'] = 'exam_form_element.title_id = exam_title.id AND exam_student_title.title_id = exam_title.id';
            $element_list = M()->table("$exam_form_element exam_form_element,$exam_title exam_title,$exam_student_title exam_student_title")->where($map_element)->field('exam_title.*,exam_student_title.answer as title_answer,exam_student_title.is_old')->select();            
            foreach ($element_list as $_key_ => &$element) {
                $element['_id_'] = $_key_ + 1;
            }
            $fenz = $config[fendb][$key]*$value;
            $vo_list[] = [
                'title_id' => $element_list['title_id'],
                'exam_id' => $map['id'],
                'id' => $key,
                'chinese_num' => $this->chinese_num[$key],
                'name' => $this->list_type[$key],
                'num' => $value,
                'fen' => $config[fendb][$key],
                'fenz' => $fenz,
                'list' => $element_list,
                'is_exam'=>$is_exam,                
            ];
            unset($element_list);
        }               
        //print_r($vo_list);
        $this->assign('type_list', $vo_list);
        $this->display('form');
    }    
    // 试卷分析
    public function analysis(){
        $prefix = C('DB_PREFIX');
        $nober = 8;
        $user_id = $this->user_id();
        $school_id = $this->school_id();
        $user_type = session('user_type');
        if($user_type == 0){
            $map['ires_exam_form.uid'] = $user_id;  //获取当前登录信息   
        }else if($user_type == 88){
            $map['ires_exam_form.school_id'] = $school_id;
        }        
        $this->assign('school_id', $school_id);
        $map['_string'] = 'ires_exam_form.schooltype_id = ires_schooltype.id AND ires_exam_form.topic_id = ires_topic.id';           
        $sort = 'ires_exam_form.creattime desc';           
        $model = M('exam_form');
        if (!empty($model)) {
            $exam_form = $prefix . 'exam_form';               
            $schooltype = $prefix . 'schooltype';
            $topic = $prefix . 'topic';
            $this->_list_res("$exam_form ires_exam_form,$schooltype ires_schooltype,$topic ires_topic", "ires_exam_form.*,ires_topic.topic_name,ires_schooltype.type_name", $map, $sort, $nober);
        }        
        $this->display();
    }
    
    public function popup_student(){
        $exam_id = I('exam_id');
        $map['form_id'] = $exam_id; 
        $map['school_id'] = $this->school_id();
        $model = M('exam_student');
        $list = $model->where($map)->order('yz asc,posttime desc')->select();
        if($list){           
            foreach($list as $key => $value){                                  
                $res_usr_list[$value['student_uid']] = $value['student_uid']; 
            } 
        }      
        if(!empty($res_usr_list)){	
            $prefix = C('DB_OA.DB_PREFIX'); 
            $where['ioa_student.id'] = array('in', $res_usr_list);
            $stud = M('student',C('DB_OA.DB_PREFIX'),C('DB_OA'))
                ->join($prefix . 'grade ON ' . $prefix . 'student.grade_id = ' . $prefix . 'grade.id')  
                ->join($prefix . 'class ON ' . $prefix . 'student.class_id = ' . $prefix . 'class.id')    
                ->field('ioa_student.*,ioa_grade.grade_name,ioa_class.class_name')  
                ->where($where)
                ->select();                 
            foreach ($stud as $key => $vo) {
                $stud_is[$vo['id']] = $vo;           
            }
            unset($stud);
            foreach ($list as $ke => $ve) {
                $list[$ke]['student'] = $stud_is[$ve['student_uid']];
            }    
            unset($stud_is);
        }    
        $this->assign('list', $list); 
        $this->display();
    } 
    
    public function popup_item(){
        $title_id = I('title_id');        
        if($title_id){   
            $school_id = $this->school_id();
            $user_id = $this->user_id();
            if($user_id){
                $prefix = C('DB_OA.DB_PREFIX'); 
                $where['ioa_classteacher.teacher_id'] = $user_id;
                $classname = M('classteacher',C('DB_OA.DB_PREFIX'),C('DB_OA'))
                ->join($prefix . 'grade ON ' . $prefix . 'classteacher.grade_id = ' . $prefix . 'grade.id')  
                ->join($prefix . 'class ON ' . $prefix . 'classteacher.class_id = ' . $prefix . 'class.id')    
                ->field('ioa_grade.grade_name,ioa_class.class_name,ioa_class.id as class_id')  
                ->where($where)
                ->select();  
                $this->assign('classname', $classname);
            }  
            $this->assign('title_id', $title_id); 
        }
        $this->display();
    } 
    public function get_data_item(){
        $title_id = I('title_id');         
        $class_id = I('class_id');  
        $school_id = $this->school_id();        
        $old_is = array('错误','正确');    
        $map_form['title_id'] = $title_id;
        $map_form['school_id'] = $school_id;
        if(!($class_id == undefined)){
            $map_form['class_id'] = $class_id;  
        } 
        foreach ($old_is as $key => $value) {      
            $map_form['is_old'] = $key;
            $number = M('exam_student_title')->where(['yz'=>1])->where($map_form)->count();
            $yz_data[$key]['value'] = $number;
            $yz_data[$key]['name'] = $old_is[$key];             
        }                              
        if ($yz_data) {  
            $return['status'] = 1; 
            $return['list'] = $yz_data;         
        } else {
            $return['status'] = 2;
            $return['info'] = null;   
        }
        $this -> ajaxReturn($return);
    }  
    
    public function popup_anal(){
        $exam_id['id'] = I('exam_id');
        $school_id = $this->school_id();
        $user_id = $this->user_id();
        if($user_id){
            $prefix = C('DB_OA.DB_PREFIX'); 
            $where['ioa_classteacher.teacher_id'] = $user_id;
            $classname = M('classteacher',C('DB_OA.DB_PREFIX'),C('DB_OA'))
            ->join($prefix . 'grade ON ' . $prefix . 'classteacher.grade_id = ' . $prefix . 'grade.id')  
            ->join($prefix . 'class ON ' . $prefix . 'classteacher.class_id = ' . $prefix . 'class.id')    
            ->field('ioa_grade.grade_name,ioa_class.class_name,ioa_class.id as class_id')  
            ->where($where)
            ->select();  
            $this->assign('classname', $classname);
        }  
        
        $exam = M('exam_form')->where($exam_id)->getField('config');
        $config = @unserialize($exam);
        foreach ($config['numdb'] as $key => $value) {
            $zfen += $config['numdb'][$key] * $config['fendb'][$key];
        }    
        //$zfen = 150;
        $y = floor($zfen/10);
        if($y>6){if($y>10){$y=$y-8;}else{$y=3;}}else{$y=$y-2;}        
        for ($x=0; $x<=$y; $x++) {                
            $fen[$x] = ($zfen-9).'分'.'->'.$zfen.'分'; 
            $fen_is[$x] = ($zfen-9).'|'.$zfen;
            $zfen = $zfen-10;
        }        
        $fen[] = (0).'分'.'->'.($zfen).'分';      
        $fen_is[] = (0).'|'.($zfen);       
        foreach ($fen_is as $key => $value) {
            $small = strstr($value, '|', ture);
            $bag = substr($value,strpos($value,'|')+1);
            $map['ires_exam_student.total_fen'] = array('between', array($small, $bag));
            $map['ires_exam_student.form_id'] = $exam_id['id'];            
            $number = M('exam_student')->where(['yz'=>1])->where($map)->count();
            $fen_data[$key]['value'] = $number;
            $fen_data[$key]['name'] = $fen[$key];             
        }      
        $fen = json_encode($fen);  
        $fen_data = json_encode($fen_data);          
        $this->assign('fen', $fen);
        $this->assign('exam_id',$exam_id['id'] );  
        $this->assign('fen_data', $fen_data);    
        $this->display();
    } 
    
    
    public function get_data(){
        $map['id'] = I('exam_id'); 
        $class_id = I('class_id');  
        $school_id = $this->school_id();        
        $map_form['form_id'] = $map['id'];	        
        $exam = M('exam_form')->where($map)->getField('config');
        $config = @unserialize($exam);
        foreach ($config['numdb'] as $key => $value) {
            $zfen += $config['numdb'][$key] * $config['fendb'][$key];
        }      
        //$zfen = 120;
        $y = floor($zfen/10);
        if($y>6){if($y>10){$y=$y-8;}else{$y=3;}}else{$y=$y-2;}        
        for ($x=0; $x<=$y; $x++) {                
            $fen[$x] = ($zfen-9).'分'.'->'.$zfen.'分'; 
            $fen_is[$x] = ($zfen-9).'|'.$zfen;
            $zfen = $zfen-10;
        }        
        $fen[] = (0).'分'.'->'.($zfen).'分';      
        $fen_is[] = (0).'|'.($zfen);       
        if(!($class_id == undefined)){
            $map_form['ires_exam_student.aclass'] = $class_id;  
        } 
        $map_form['ires_exam_student.school_id'] = $school_id;
        foreach ($fen_is as $key => $value) {            
            $small = strstr($value, '|', ture);
            $bag = substr($value,strpos($value,'|')+1);
            $map_form['ires_exam_student.total_fen'] = array('between', array($small, $bag));
            $map_form['ires_exam_student.form_id'] = $map['id'];            
            $number = M('exam_student')->where(['yz'=>1])->where($map_form)->count();
            $fen_data[$key]['value'] = $number;
            $fen_data[$key]['name'] = $fen[$key];             
        }                              
        if ($fen) {  
            $return['status'] = 1; 
            $return['list'] = $fen_data;         
        } else {
            $return['status'] = 2;
            $return['info'] = null;   
        }
        $this -> ajaxReturn($return);
    }  
  
    public function itemanalysis(){
        $prefix = C('DB_PREFIX');
        $map['_string'] = 'ires_exam_form.schooltype_id = ires_schooltype.id AND ires_exam_form.topic_id = ires_topic.id';
        $map['ires_exam_form.id'] = I('id');  //获取当前登录信息
        $sort = '';           
        $model = M('exam_form');
        if (!empty($model)) {
            $exam_form = $prefix . 'exam_form';               
            $schooltype = $prefix . 'schooltype';
            $topic = $prefix . 'topic';
            $this->_list_res("$exam_form ires_exam_form,$schooltype ires_schooltype,$topic ires_topic", "ires_exam_form.*,ires_topic.topic_name,ires_schooltype.type_name", $map, $sort, $nober);
        }                
        $this->display();
    }
}
