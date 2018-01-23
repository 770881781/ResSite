<?php
/**
 * @Author: ispCMS
 * @Date:   2014-06-21 10:00:00
 * @Last Modified by:   ispCMS
 * @Last Modified time: 2016-06-21 12:39:47
 */
namespace Home\Controller;

use Common\Lib\Category;

class ShowController extends HomeCommonController
{
    //方法：index
    public function index()
    {
        $id    = I('id', 0, 'intval');
        $cid   = I('cid', 0, 'intval');
        $ename = I('e', '', 'htmlspecialchars,trim');

        if ($id == 0) {
            $this->error('参数错误');
        }

        $cate = get_category(1);
        if (!empty($ename)) { //ename不为空
            $self = Category::getSelfByEName($cate, $ename); //当前栏目信息
        } else {
//$cid来判断

            $self = Category::getSelf($cate, $cid); //当前栏目信息
        }

        if (empty($self)) {
            $this->error('栏目不存在');
        }

        $cid         = $self['id']; //当使用ename获取的时候，就要重新给$cid赋值，不然0
        $_GET['cid'] = $cid; //栏目ID
        $self['url'] = get_url($self);

        //访问权限
        $groupid = intval(get_cookie('groupid'));
        $groupid = empty($groupid) ? 1 : $groupid; //1为游客
        //判断访问权限
        $access = M('categoryAccess')->where(array('catid' => $cid, 'flag' => 0, 'action' => 'visit'))->getField('roleid', true);
        //权限存在，则判断
        if (!empty($access) && !in_array($groupid, $access)) {
            $this->error('您没有访问该信息的权限！');
        }

        $patterns      = array('/' . C('TMPL_TEMPLATE_SUFFIX') . '$/');
        $replacements  = array('');
        $template_show = preg_replace($patterns, $replacements, $self['template_show']);

        if (empty($template_show)) {
            $this->error('模板不存在');
        }

        $content = M($self['tablename'])->where(array('status' => 0, 'id' => $id))->find();

        if (empty($content)) {
            $this->error('内容不存在');
        }

        //当前url
        $_jumpflag      = ($content['flag'] & B_JUMP) == B_JUMP ? true : false;
        $content['url'] = get_content_url($content['id'], $content['cid'], $self['ename'], $_jumpflag, $content['jumpurl']);

        $this->assign('cate', $self);
        $this->assign('title', $content['title']);
        $this->assign('keywords', $content['keywords']);
        $this->assign('description', empty($content['description']) ? $content['title'] : $content['description']);
        $this->assign('commentflag', $content['commentflag']); //是否允许评论,debug,以后加上个全局评价 $content['commentflag'] && CFG_Comment
        $this->assign('tablename', $self['tablename']);
        $this->assign('id', $id);

        switch ($self['tablename']) {
            case 'article':
                break;
            case 'phrase':
                break;
            case 'page':
                return;
                break;
            case 'picture':
                //把序列化过的数组恢复
                $pictureurls_arr = empty($content['pictureurls']) ? array() : explode('|||', $content['pictureurls']);

                $pictureurls = array();
                foreach ($pictureurls_arr as $v) {
                    $temp_arr = explode('$$$', $v);
                    if (!empty($temp_arr[0])) {
                        $pictureurls[] = array(
                            'url' => $temp_arr[0],
                            'alt' => $temp_arr[1],
                        );
                    }
                }
                $content['pictureurls'] = $pictureurls;
                //p($pictureurls);
                break;
            case 'product':
                //把序列化过的数组恢复
                $pictureurls_arr = empty($content['pictureurls']) ? array() : explode('|||', $content['pictureurls']);

                $pictureurls = array();
                foreach ($pictureurls_arr as $v) {
                    $temp_arr = explode('$$$', $v);
                    if (!empty($temp_arr[0])) {
                        $pictureurls[] = array(
                            'url' => $temp_arr[0],
                            'alt' => $temp_arr[1],
                        );
                    }
                }
                $content['pictureurls'] = $pictureurls;
                //p($pictureurls);
                break;

            case 'soft':
                //图片
                $pictureurls_arr = empty($content['pictureurls']) ? array() : explode('|||', $content['pictureurls']);
                $pictureurls     = array();
                foreach ($pictureurls_arr as $v) {
                    $temp_arr = explode('$$$', $v);
                    if (!empty($temp_arr[0])) {
                        $pictureurls[] = array(
                            'url' => $temp_arr[0],
                            'alt' => $temp_arr[1],
                        );
                    }
                }
                $content['pictureurls'] = $pictureurls;

                //下载地址:
                $downlink_arr = empty($content['downlink']) ? array() : explode('|||', $content['downlink']);
                $downlink     = array();
                $at           = 0; //索引
                foreach ($downlink_arr as $v) {
                    $temp_arr = explode('$$$', $v);
                    if (!empty($temp_arr[1])) {
                        $downlink[] = array(
                            //'url' => $temp_arr[1],
                            'url'   => U('Show/download', array('id' => $id, 'at' => $at)),
                            'title' => $temp_arr[0],
                        );
                        $at++;
                    }
                }
                $content['downlink'] = $downlink;

                break;
            default:
                $userOther = A(ucfirst($self['tablename']));
                $userOther->shows();
                return;
                break;
        }

        $this->assign('content', $content);
        $this->display($template_show);

    }

    public function download()
    {
        $id = I('id', 0, 'intval');
        $at = I('at', 0, 'intval');
        if (empty($id)) {
            $this->error('参数错误');
        }
        $downlink_tmp = M('soft')->where(array('id' => $id))->getField('downlink');
        if (empty($downlink_tmp)) {
            $this->error('文件不存在');
        }

        //下载地址:
        $downlink = array();
        foreach (explode('|||', $downlink_tmp) as $v) {
            $temp_arr = explode('$$$', $v);
            if (!empty($temp_arr[1])) {
                $downlink[] = array(
                    'url'   => $temp_arr[1],
                    'title' => $temp_arr[0],
                );
            }
        }
        if (!isset($downlink[$at]['url'])) {
            $this->error('文件不存在!');
        }
        $fileurl = trim($downlink[$at]['url']);

        $cfg_download_hide = C('CFG_DOWNLOAD_HIDE');

        //远程文件
        if (strpos($fileurl, ':/') || empty($cfg_download_hide)) {
            header("Location: $fileurl");
        } else {

            $filename = basename($fileurl);
            //处理中文文件

            $ext      = strtolower(substr(strrchr($filename, "."), 1)); //获取文件扩展名
            $filename = date('Ymd_his') . get_randomstr(3) . '.' . $ext;
            $this->downLocalFile($fileurl, $filename);

        }
    }

    /**
     * 文件下载
     * @param $filepath 文件路径
     * @param $filename 文件名称
     */
    private function downLocalFile($filepath, $filename = '')
    {
        if (!$filename) {
            $filename = basename($filepath);
        }

        $doc_path = str_ireplace(str_replace("\\", "/", $_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_FILENAME']);
        $realpath = $doc_path . $filepath;
        if (!file_exists($realpath)) {
            header('HTTP/1.1 404 Not Found');
            echo "Error: 404 Not Found.(server file path error)<!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding -->";
            exit;
        }

        $filetype = strtolower(substr(strrchr($filename, "."), 1)); //获取文件扩展名
        $filesize = sprintf("%u", filesize($realpath));
        if (ob_get_length() !== false) {
            @ob_end_clean();
        }

        header('Pragma: public');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: pre-check=0, post-check=0, max-age=0');
        header('Content-Transfer-Encoding: binary');
        header('Content-Encoding: none');
        header('Content-type: ' . $filetype);
        if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) {
            //for IE
            header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        header('Content-length: ' . $filesize);
        readfile($realpath);
        exit;
    }

}
