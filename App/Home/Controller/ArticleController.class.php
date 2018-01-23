<?php
/**
 * @Author: ispCMS
 * @Date:   2014-06-21 10:00:00
 * @Last Modified by:   ispCMS
 * @Last Modified time: 2016-06-21 12:39:28
 */

namespace Home\Controller;

class ArticleController extends HomeCommonController{
    //方法：index    
    public function index(){   
        $user_id = $this->user_id();      
        $school_id = $this->school_id(); 
        if($user_id == '' || $school_id == ''){
            $this->error('请重新登录！','/sns');
        }  
        //推荐文章列表
        $art_list = M('Article')->limit(2)->where(['jumpurl'=>1,'status'=>0]) -> order('publishtime desc') -> select();       
        $num = count($art_list);
        if($num <2){
            $art_list = M('Article')->limit(2)->where(['status'=>0]) -> order('publishtime desc') -> select();       
        }        
        $this->assign('art_list', $art_list);          
        //获取文章栏目下级目录 默认文章在栏目表中id排序第一 不可更改  否则pid应相应变化        
        $list = M('Category') ->where('pid = 1')->select();        
        $this -> assign('list',$list);  
        //轮播图管理
//        $map_1['aid'] = 3;
//        $map_1['status'] = 0;
//        $model_abc = M('abc_detail')->where($map_1)->order('sort asc')->select();
//        $this->assign('model_abc', $model_abc);          
        //文章栏目搜索        
        $model_list = M('category')->where('pid = 1')->field('id,name')->select();        
        $this -> assign('model_list',$model_list);          
       
        $this -> display();
    } 
    
    public function oldindex(){   
        $user_id = $this->user_id();      
        $school_id = $this->school_id(); 
        if($user_id == '' || $school_id == ''){
            $this->error('请重新登录！','/sns');
        }  
        //最新文章列表
        $art_list = M('Article')->limit(5) -> order('publishtime desc') -> field('id,title,publishtime') -> select();       
        $this->assign('art_list', $art_list);          
        //获取文章栏目下级目录 默认文章在栏目表中id排序第一 不可更改  否则pid应相应变化        
        $list = M('Category') ->where('pid = 1')->select();        
        $this -> assign('list',$list);  
        //轮播图管理
        $map_1['aid'] = 3;
        $map_1['status'] = 0;
        $model_abc = M('abc_detail')->where($map_1)->order('sort asc')->select();
        $this->assign('model_abc', $model_abc);          
        //文章栏目搜索		
        $model_list = M('category')->where('pid = 1')->field('id,name')->select();        
        $this -> assign('model_list',$model_list);  		
       
        $this -> display();
    } 
    
    
    public function olddetails(){   
        $id = I('id');       
        $map['id'] = $id;
        $user_name = $this->user_name();                 
        $list = M('article')->where($map)->find();     
        $list['username'] = $user_name;
        
        //下一条
        $user_id = $this->user_id();
        if($user_id == ''){
            $user_id = 0;            
        }        
        $school_id = $this->school_id();
        if($school_id == ''){
            $school_id = 1000;            
        }                  
        $map['_string'] = "userid = $user_id AND jur = 1 OR school_id = $school_id AND jur = 2 OR jur = 3";
        $next = M('article')->where($map)->where(array('id' => array('gt',$id)))->field('id,title')->find();        
        //上一条
        $pro = M('article')->where($map)->where(array('id' => array('lt',$id)))->field('id,title')->find();
       
        $this->assign('list', $list);  
        $this->assign('next', $next);  
        $this->assign('pro', $pro);                       
        $this -> display();
    }   

    public function are_list() {      
        $user_id = $this->user_id();
        if($user_id == ''){
            $user_id = 0;            
        }        
        $school_id = $this->school_id();
        if($school_id == ''){
            $school_id = 1000;            
        }    
        $aArray = I('aArray');        
        $page = I('page');
        $pageSize = I('pageSize');
        $offset = ($page - 1) * $pageSize;      
        $prefix = C('DB_PREFIX');        
        if(!($aArray['cid'] == '') && !($aArray['cid'] == 0)){            
            $map['ires_article.cid'] = $aArray['cid'];         
        }
        if(!($aArray['title'] == '')){            
            $map['ires_article.title'] = array('like', "%" . $aArray['title'] . "%");    ;    
        }  
        $map['ststus'] = 0;
        $map['_string'] = "userid = $user_id AND jur = 1 OR school_id = $school_id AND jur = 2 OR jur = 3";  
        $res_list = M('article')->where($map)->order('publishtime desc')->limit($offset,$pageSize)->select();          
        $total = M('article')->where($map)->count();        
        $rows = $res_list;                  
        $this->ajaxReturn(compact('total','rows'),'JSON');        
    } 	

    


    public function details(){   
        $id = I('id');
        $inc_ = M('article')->where(['id'=>$id])->setInc('click');
        $map['id'] = $id;
        $user_name = $this->user_name();                 
        $list = M('article')->where($map)->find();     
        $list['username'] = $user_name;
        //最新文章
        $art_list = M('Article')->limit(5) -> where(['status'=> 0])->order('publishtime desc') -> field('id,title,publishtime') -> select();       
        $this->assign('art_list', $art_list); 
          //最热文章
        $art_relist = M('Article')->limit(5) -> where(['status'=> 0])->order('click desc,publishtime desc') -> field('id,title,publishtime') -> select();       
        $this->assign('art_relist', $art_relist); 
        
        
        //下一条
        $user_id = $this->user_id();
        if($user_id == ''){
            $user_id = 0;            
        }        
        $school_id = $this->school_id();
        if($school_id == ''){
            $school_id = 1000;            
        }     
        $map['status'] = 0;
        $map['_string'] = "userid = $user_id AND jur = 1 OR school_id = $school_id AND jur = 2 OR jur = 3";
        $next = M('article')->where($map)->where(array('id' => array('gt',$id)))->field('id,title')->find();        
        //上一条
        $pro = M('article')->where($map)->where(array('id' => array('lt',$id)))->field('id,title')->find();
       
        $this->assign('list', $list);  
        $this->assign('next', $next);  
        $this->assign('pro', $pro);                       
        $this -> display();
    }   
}
