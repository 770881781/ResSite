<?php
/**
 * @Author: ispCMS
 * @Date:   2014-06-21 10:00:00
 * @Last Modified by:   ispCMS
 * @Last Modified time: 2016-06-21 12:39:28
 */

namespace Home\Controller;

class SelectController extends HomeCommonController{    
        //方法：index
    public function index(){
        $cid = I('cid');
        $select = I('select');       
        if (empty($select)) {
            $this->error('请输入搜索条件');
        }else{
            if(!(empty($cid))){
                $map['cid'] = $cid; 
            }
            $map['title'] = array('like', "%" . $select . "%"); 
            $map['is_del'] = 0;                  
        }                 
        //最新资源列表
        $model = M('detail');
        $map_new['is_del'] = 0;
        $map_new['privilege'] = 0;    
        $del_list = $model ->limit(6)->where($map_new) -> order('publishtime desc') -> field('id,title,publishtime,downlink') -> select();     
        foreach ($del_list as $key => $vo) {           
            $title = explode(".",$vo['downlink']);         
            $del_list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title[1].'.gif';     
        }          
        $count          = M('detail')->where(array('pid' => $pid))->where($map)->where($map_new)->count();
        $page           = new \Common\Lib\Page($count, 6);
        $page->rollPage = 7;
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow . ',' . $page->listRows;
        $list  = M('detail')->where($map)->order('publishtime desc')->where($map_new)->limit($limit)->select();     
        foreach ($list as $key => $vo) {           
            $title = explode(".",$vo['downlink']);         
            $list[$key]['imgs'] = '/ResSite/Public/Home/default/images/filetype/'.$title[1].'.png';     
        }     
         //栏目
        $model_list = M('category')->where('pid = 12')->field('id,name')->select();        
        $this -> assign('model_list',$model_list);  
        $this->assign('page', $page->show());
        $this->assign('vlist', $list);
        $this->assign('pid', $pid); 
        $this ->assign("select",$select);
        $this ->assign("del_list",$del_list);
        $this -> display();
    }  
     
    
}
