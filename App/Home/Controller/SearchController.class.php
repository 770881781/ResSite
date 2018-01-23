<?php
/**
 * @Author: ispCMS
 * @Date:   2014-06-21 10:00:00
 * @Last Modified by:   ispCMS
 * @Last Modified time: 2016-06-21 12:39:44
 */
namespace Home\Controller;

class SearchController extends HomeCommonController
{
    //方法：index
    public function index()
    {

        $modelid = I('modelid', 0, 'intval');
        $keyword = I('keyword', '', 'htmlspecialchars,trim'); //关键字
        if (!empty($modelid)) {
            $tablename = M('model')->where(array('id' => $modelid))->getField('tablename');
        }

        if (empty($tablename)) {
            $model = M('model')->order('id')->find();
            if ($model) {
                $modelid   = $model['id'];
                $tablename = $model['tablename'];
            }
        }

        if ($keyword == '请输入关键词') {
            $keyword = '';
        }

        if (!empty($keyword) && !empty($tablename)) {

            $where = array('title' => array('LIKE', '%' . $keyword . '%'));
            $count = D2('ArcView', $tablename)->where($where)->count();

            //设置显示的页数
            $thisPage           = new \Common\Lib\Page($count, 10);
            $thisPage->rollPage = 3;
            $thisPage->setConfig('theme', ' %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            $limit = $thisPage->firstRow . ',' . $thisPage->listRows;
            $page  = $thisPage->show();

            $vlist = D2('ArcView', $tablename)->nofield('content')->where($where)->order('id desc')->limit($limit)->select();

        } else {
            $page  = '';
            $vlist = array();
        }
        if (empty($vlist)) {
            $vlist = array();
        }
        foreach ($vlist as $k => $v) {
            if (isset($v['flag'])) {
                $_jumpflag = ($v['flag'] & B_JUMP) == B_JUMP ? true : false;
                $_jumpurl  = $v['jumpurl'];
            } else {
                $_jumpflag = false;
                $_jumpurl  = '';
            }
            $vlist[$k]['url'] = get_content_url($v['id'], $v['cid'], $v['ename'], $_jumpflag, $_jumpurl);
        }

        if (empty($keyword)) {
            $title = '搜索中心';
        } else {
            $title = $keyword . '_搜索中心';
        }

        $this->assign('title', $title);
        $this->assign('keyword', $keyword);
        $this->assign('searchurl', U('Search/index'));
        $this->assign('vlist', $vlist);
        $this->assign('page', $page);
        $this->assign('modelid', $modelid);
        $this->display();

    }

}
