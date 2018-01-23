<?php
/*---------------------------------------------------------------------------
  Copyright (c) 2016 iSP All rights reserved.
  Author:  iSP
 -------------------------------------------------------------------------*/
namespace Home\Controller;
use Think\Controller;

class IsoapiController extends Controller {	
	/*
	同步登录通知接口
	*/
	public function login() {
		$return = [
			'code'		=> 	-1, 
			'message'	=>	'用户名或者密码错误！'
		];
		$data = I('data');
		$callback = I('callback');
		$response = ispoacurl(C('ISPOA_URL_AUTHOR'), 'data=' .urlencode($data));
		//var_dump($response);
		if($response){
			$result = json_decode($response,true);		
			if(!empty($result)){				
				$code = $result['code'];
				$usrinfo = $result['userlist'];                              
				//var_dump($usrinfo['dlzh']);
				if(0 == $code && !empty($usrinfo['dlzh'])){
					$xm	= $usrinfo['xm'];
					$ghxh 	= $usrinfo['ghxh'];
					$dlzh 	= $usrinfo['dlzh'];
					$yhlx 	= $usrinfo['yhlx'];
					$jsmc 	= $usrinfo['jsmc'];
					$id     = $usrinfo['id'];
                    $stut_id= $usrinfo['stdent_id'];
					$bjmc	= $usrinfo['bjmc'];
					$bjdm	= $usrinfo['bjdm'];
					$ssxxmc	= $usrinfo['ssxxmc'];
					$ssxxdm	= $usrinfo['ssxxdm'];
					
					//设置登录信息
					session('std_tag',      $yhlx=='老师'?0:1);
					session('user_id',	$id);
	                if($yhlx == '老师'){
	                    session('user_id',	$id);
	                }else{
	                    session('user_id',	$stut_id);
	                }
	                session('usst_id',	$stut_id);
					session('user_emp',	$dlzh);
					session('user_name',	$xm);
					session('school_id',	$ssxxdm);
							
					$return['code'] 	= 	0;
					$return['message']	= 	'登录成功！';
				}
			}
		}
		$this -> ajaxReturn($return, $callback);
	}
	
	/*
	同步退出通知接口
	*/
	public function logout() {
		$return = [
			'code'		=> 	-1, 
			'message'	=>	'退出失败！'
		];
		$data = I('data');
		$callback = I('callback');
		$response = ispoacurl(C('ISPOA_URL_VERITY'),"data=" .urlencode($data));
		//var_dump($response);
		if($response){
			$result = json_decode($response,true);		
			if(!empty($result)){				
				$code = $result['code'];
				$status = $result['status'];
				if(0 == $code){
					//状态为0，说明成功					
					if('true'==$status){
						session_unset();
						session_destroy();
					}
					
					$return['code'] 	= 	0;
					$return['message']	= 	'退出成功！';
					
				}
			}
		}
		$this -> ajaxReturn($return, $callback);
	}
	
	/**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $callback JSONP的回调函数
     * @return void
     */
    protected function ajaxReturn($data,$callback='') {
        if(empty($callback)){
			// 返回JSON数据格式到客户端 包含状态信息
			header('Content-Type:application/json; charset=utf-8');
			exit(json_encode($data));
		}
		else{
			// 返回JSON数据格式到客户端 包含状态信息
			header('Content-Type:application/json; charset=utf-8');
			exit($callback.'('.json_encode($data,$json_option).');');  
		}
    }
}
?>