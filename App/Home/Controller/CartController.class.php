<?php
/**
 * @Author: ispCMS
 * @Date:   2014-06-21 10:00:00
 * @Last Modified by:   ispCMS
 * @Last Modified time: 2016-06-21 12:39:15
 */
namespace Home\Controller;

class CartController extends HomeCommonController
{

    public function index()
    {

        $cart = new \Common\Lib\Cart();

        $this->assign('title', '我的购物车');
        $this->assign('cartinfo', $cart->getContent());
        $this->assign('tprice', $cart->getTotal());
        $this->display();

    }

    public function add()
    {

        $id  = I('id', 0, 'intval'); //产品id
        $num = I('num', 1, 'intval'); //数量
        if (!$id) {
            $this->error('请选择要购买的产品!');
        }
        $vo = D2('ArcView', 'product')->find($id);
        if (empty($vo)) {
            $this->error('产品不存在!');
        }
        //url
        $jumpflag  = ($vo['flag'] & B_JUMP) == B_JUMP ? true : false;
        $vo['url'] = get_content_url($vo['id'], $vo['cid'], $vo['ename'], $jumpflag, $vo['jumpurl']);

        $item = array(
            'id'          => $vo['id'],
            'num'         => $num,
            'price'       => $vo['price'],
            'name'        => $vo['title'],
            'marketprice' => $vo['marketprice'],
            'litpic'      => $vo['litpic'],
            'url'         => $vo['url'],
        );

        $cart = new \Common\Lib\Cart();
        if ($cart->insert($item)) {
            $param = array('tprice' => sprintf('%01.2f', $cart->getTotal()), 'tnum' => $cart->getTotalCount());
            if (IS_AJAX) {
                $this->success('放入购物车成功', U('Cart/index'), $param);
            } else {
                $this->redirect('Cart/index');
            }
        } else {
            $this->error('放入购物车失败，请重试');
        }

    }

    public function update()
    {

        $id  = I('id', 0, 'intval'); //产品id
        $num = I('num', 1, 'intval'); //数量
        if (!$id) {
            $this->error('请选择要购买的产品!');
        }

        $item = array('id' => $id, 'num' => $num);
        $cart = new \Common\Lib\Cart();
        if ($cart->update($item)) {
            $param = array('tprice' => sprintf('%01.2f', $cart->getTotal()), 'tnum' => $cart->getTotalCount());
            if (IS_AJAX) {
                $this->success('更新成功', U('Cart/index'), $param);
            } else {
                $this->redirect('Cart/index');
            }

        } else {
            $this->error('更新失败，请重试');
        }

    }

}
