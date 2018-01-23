<?php
// .-----------------------------------------------------------------------------------
// | Desc: 			shopping cart classes
// | LastModified: 	2014-11-13
// | Site: 			http://ispCMS
// |-----------------------------------------------------------------------------------
// | Author: ispCMS 
// | Copyright (c) 2014-2014, http://ispCMS. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Common\Lib;

class Cart {

	//购物车
	private $contents = array();//购物车内容
	private $total_price = 0; //商品总金额	
	private $total_count = 0;//商品总数量
	private $total_item = 0;//商品种类数量

	/**
	* 构造函数
	*/
	public function __construct() {
		//初次
		if (isset($_SESSION['cart_contents'])) {
			$this->contents = $_SESSION['cart_contents'];
			//重新计算
			$this->_recount();
		}
		
	}

	/**
	*将物品加入购物车
	* @access  public
  	* @param array 一维数组, array(id -> 物品ID, num -> 数量, price -> 单价, name -> 名称)
  	* @return  bool
	*/
	public function insert($item = array()) {
		//检测
		if (!is_array($item) || empty($item)) {
			return false;
		}
		if (!isset($item['id']) || !isset($item['num']) || !isset($item['price']) || !isset($item['name'])) {
			return false;
		}

		//数量、单价非数字
		if (!is_numeric($item['num']) || !is_numeric($item['price']) || $item['num'] == 0) {
			return false;
		}
		$id = $item['id'];

		//已经存在的物品，直接相加数量
		if (isset($this->contents[$id])) {
			$this->contents[$id]['num'] += $item['num'];
		} else {
			$this->contents[$id] = $item;
		}

		//更新到session
		$this->_save();
		
		return true;		
		
	}




	/**
	*更新购物车物品信息
	* @access  public
  	* @param array 一维数组, array(id -> 物品ID, num -> 数量, price -> 单价, name -> 名称)
  	* @return  bool
	*/
	public function update($item = array()) {
		//检测
		if (!is_array($item) || empty($item)) {
			return false;
		}
		if (!isset($item['id']) || !isset($item['num'])) {
			return false;
		}

		$id = $item['id'];
		//购物车不存在此物品，直接返回
		if (!isset($this->contents[$id])) {
			return false;
		}
		

		//数量、单价非数字
		if (!is_numeric($item['num'])) {
			return false;
		}

		//数量为0，表示删除些物品
		if ($item['num'] == 0) {
			unset($this->contents[$id]);
		} else {
			$this->contents[$id]['num'] = $item['num'];
		}		

		//更新到session
		$this->_save();
		
		return true;		
		
	}

	/**
	* 清空购物车
	* @access  public
  	* @return  bool
	*/
	public function clear($item = array()) {

		$this->contents = array();	
		$this->total_price = 0;
		$this->total_count = 0;
		$this->total_item = 0;

		//更新到session
		unset($_SESSION['cart_contents']);
		
		return true;		
		
	}

	/**
	* 获取购物车总金额
	* @access  public
  	* @return  int
	*/
	public function getTotal() {

		return $this->total_price;		
		
	}

	/**
	* 获取购物车品种数量
	* @access  public
  	* @return  int
	*/
	public function getTotalItem() {

		return $this->total_item;		
		
	}

	/**
	* 获取购物车商品总数量
	* @access  public
  	* @return  int
	*/
	public function getTotalCount() {

		return $this->total_count;		
		
	}


	/**
	* 获取购物车
	* @access  public
  	* @return  int
	*/
	public function getContent($item = array()) {

		return $this->contents;		
		
	}


	/**
	* 保存购物车数据到session
	* @access  private
  	* @return  void
	*/
	private function _recount() {
		//重新计算购物车总物品种类及总金额
		$total_item =0;		
		$total_price = 0;
		$total_count =0;
		foreach ($this->contents as $key => $val) {
			
			if (!isset($val['price']) || !isset($val['num'])) {
				continue;
			}

			$total_item ++;
			$total_price += $val['price'] * $val['num'];
			$total_count += $val['num'];

			//每种物品的总金额
			$this->contents[$key]['subtotal'] = $val['price'] * $val['num'];

		}

		$this->total_item = $total_item;
		$this->total_price = $total_price;
		$this->total_count = $total_count;
		

	}



	/**
	* 保存购物车数据到session
	* @access  private
  	* @return  bool
	*/
	private function _save() {
		//重新计算购物车总物品种类及总金额
		$this->_recount();

		//当物品数量为0时，清除对应session
		if ($this->total_item == 0) {
			unset($_SESSION['cart_contents']);
			return false;
		}

		$_SESSION['cart_contents'] = $this->contents;
		return true;

	}





}





?>