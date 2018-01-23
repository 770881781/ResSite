<?php

/**
 * @Author: ispCMS
 * @Date:   2014-06-21 10:00:00
 * @Last Modified by:   ispCMS
 * @Last Modified time: 2016-10-03 10:44:56
 */
namespace Common\Lib;

class YunUpload
{
    private $thumFlag = 0; //是否是图片(带缩略图)，默认是作为文件处理（不管是否是图片）
    private $rootPath; //上传根目录
    private $subDirectory = 'file1/'; //保存子目录
    private $allowType    = array('jpg'); //允许类型

    private $fileField; //base64/远程文件 ，POST对应的字段名

    // 构造函数
    public function __construct($thum_flag = 0, $sub_path = 'file1', $file_field = '')
    {

        $this->thumFlag = intval($thum_flag);

        //上传根目录
        $this->rootPath = '../ispoa/Uploads/resource/';
        
        //带缩图的图片，生成为固定位置
        if ($this->thumFlag) {
            $this->subDirectory = 'img1/';
        } else {
            $sub_path           = trim($sub_path);           
            $sub_path           = empty($sub_path) ? 'file1/' : $sub_path;          
            $this->subDirectory = rtrim($sub_path, '/') . '/'; //上传（子）目录
        }

        //允许类型
        $this->allowType = $this->thumFlag ? explode(',', C('CFG_UPLOAD_IMG_EXT')) : explode(',', C('CFG_UPLOAD_FILE_EXT'));
        $this->fileField = $file_field; //base64/remote

    }

    /**
     * 上传图片|文件
     * @return
     */
    public function upload()
    {
        $result = array('status' => 0, 'info' => '', 'data' => array());
        //文件上传地址提交给他，并且上传完成之后返回一个信息，让其写入数据库
        if (empty($_FILES)) {
            $result['info'] = '必须选择上传文件';
            return $result;
        } else {

            $info = $this->_upload(); //获取图片信息

            if (isset($info) && is_array($info)) {
                //写入数据库的自定义c方法
                if (!$this->_saveDate($info)) {
                    //echo '上传入库失败';
                    $result['status'] = 0;
                    $result['info']   = '上传入库失败';
                    return $result;
                }

                //数组索引转为数字
                $new_info = array();
                foreach ($info as $k => $v) {

                    $v['url'] = '/ispoa/Uploads/resource/'. $v['savepath'] . $v['savename'];
                    //是否有缩略图
                    if ($this->thumFlag) {
                        //读取缩略图配置信息
                        $imgtbSize = explode(',', C('CFG_IMGTHUMB_SIZE')); //配置缩略图第一个参数
                        $imgTSize  = explode('X', $imgtbSize[0]);
                        if (!empty($imgTSize)) {
                            $v['turl'] = get_picture($v['url'], $imgTSize[0], $imgTSize[1]);
                        }
                    }
                    $new_info[] = $v;
                }

                $result['status'] = 1;
                $result['info']   = '上传成功';
                $result['data']   = $new_info;

                return $result;

            } else {
                $result['info'] = '失败:' . $info;
                return $result;
            }
        }

    }

    /**
     * uploadBase64
     * @return
     */
    public function uploadBase64()
    {
        $result = array('status' => 0, 'info' => '', 'data' => array());
        //文件上传地址提交给他，并且上传完成之后返回一个信息，让其写入数据库
        if (empty($this->fileField)) {
            $result['info'] = '必须选择上传文件';
            return $result;
        } else {

            $info = $this->_uploadBase64(); //获取图片信息

            if (isset($info) && is_array($info)) {
                //写入数据库的自定义c方法
                if (!$this->_saveDate($info)) {
                    //echo '上传入库失败';
                    $result['status'] = 0;
                    $result['info']   = '上传入库失败';
                    return $result;
                }

                //数组索引转为数字
                $new_info = array();
                foreach ($info as $k => $v) {

                    $v['url'] = get_url_path(C('CFG_UPLOAD_ROOTPATH')) . $v['savepath'] . $v['savename'];
                    //是否有缩略图
                    if ($this->thumFlag) {
                        //读取缩略图配置信息
                        $imgtbSize = explode(',', C('CFG_IMGTHUMB_SIZE')); //配置缩略图第一个参数
                        $imgTSize  = explode('X', $imgtbSize[0]);
                        if (!empty($imgTSize)) {
                            $v['turl'] = get_picture($v['url'], $imgTSize[0], $imgTSize[1]);
                        }
                    }
                    $new_info[] = $v;
                }

                $result['status'] = 1;
                $result['info']   = '上传成功';
                $result['data']   = $new_info;

                return $result;

            } else {
                $result['info'] = '失败:' . $info;
                return $result;
            }
        }

    }

    /**
     * 获取远程图片
     * @return
     */
    public function saveRemote()
    {
        $result = array('status' => 0, 'info' => '', 'data' => array());
        //文件上传地址提交给他，并且上传完成之后返回一个信息，让其写入数据库
        if (empty($this->fileField)) {
            $result['info'] = '远程图片不能为空';
            return $result;
        } else {

            $info = $this->_saveRemote(); //获取图片信息

            if (isset($info) && is_array($info)) {

                //写入数据库的自定义c方法
                $info_save_data = array();
                foreach ($info as $k => $v) {
                    if ($v['status']) {
                        $info_save_data[] = $v;
                    }
                }
                if (!$this->_saveDate($info_save_data)) {
                    //echo '上传入库失败';
                    $result['status'] = 0;
                    $result['info']   = '上传入库失败';
                    return $result;
                }

                //数组索引转为数字
                $new_info = array();
                foreach ($info as $k => $v) {
                    if ($v['status'] == 0) {
                        break; //失败跳过
                    }

                    $v['url'] = get_url_path(C('CFG_UPLOAD_ROOTPATH')) . $v['savepath'] . $v['savename'];
                    //是否有缩略图
                    if ($this->thumFlag) {
                        //读取缩略图配置信息
                        $imgtbSize = explode(',', C('CFG_IMGTHUMB_SIZE')); //配置缩略图第一个参数
                        $imgTSize  = explode('X', $imgtbSize[0]);
                        if (!empty($imgTSize)) {
                            $v['turl'] = get_picture($v['url'], $imgTSize[0], $imgTSize[1]);
                        }
                    }
                    $new_info[] = $v;
                }

                $result['status'] = 1;
                $result['info']   = '上传成功';
                $result['data']   = $new_info;

                return $result;

            } else {
                $result['info'] = '失败:' . $info;
                return $result;
            }
        }

    }

    //上传图片
    public function _upload()
    {
        $ext = ''; //原文件后缀
        foreach ($_FILES as $key => $v) {
            $strtemp = explode('.', $v['name']);
            $ext     = end($strtemp); //获取文件后缀，或$ext = end(explode('.', $_FILES['fileupload']['name']));
            break;
        }

        $upload = new \Think\Upload(); //new Upload($config)
        //修配置项
        $upload->autoSub  = true; //是否使用子目录保存图片
        $upload->subType  = 'date'; //子目录保存规则
        $upload->subName  = array('date', 'Ymd');
        $upload->maxSize  = 100*1024*1024; //设置上传文件大小
        $upload->exts     = $this->allowType; //设置上传文件类型
        $upload->rootPath = $this->rootPath; //上传根路径
        $upload->savePath = $this->subDirectory; //上传（子）目录
        $upload->saveName = array('uniqid', ''); //上传文件命名规则
        $upload->replace  = true; //存在同名是否覆盖
        $upload->callback = false; //检测文件是否存在回调函数，如果存在返回文件信息数组

        if ($info = $upload->upload()) {

            //是否有缩略图
            if ($this->thumFlag) {
                $this->_doThum($info);
            }
            return $info;

        } else {

            //$str = array('err' =>1 ,'msg' => $upload->getError() );
            return $upload->getError();
        }

    }

    private function _uploadBase64()
    {
        $ext        = 'png'; //原文件后缀
        $base64Data = $_POST[$this->fileField];
        $img        = base64_decode($base64Data);

        $file_size = strlen($img);
        $save_name = uniqid() . '.' . $ext;
        $file_path = $this->rootPath . $this->subDirectory . date('Ymd') . '/' . $save_name;
        $dir_name  = dirname($file_path);

        //检查文件大小是否超出限制
        if ($file_size > get_upload_maxsize()) {
            return "文件大小超出网站限制";
        }

        //创建目录失败
        if (!file_exists($dir_name) && !mkdir($dir_name, 0777, true)) {
            return "目录创建失败";
        } else if (!is_writeable($dir_name)) {
            return "目录没有写权限";
        }

        //移动文件
        if (!(file_put_contents($file_path, $img) && file_exists($file_path))) {
            //移动失败
            return "写入文件内容错误";
        } else {
            //移动成功
            $info   = array();
            $info[] = array(
                'name'      => 'scraw.png',
                'type'      => 'image/png',
                'size'      => $file_size,
                'ext'       => 'png',
                // 'md5'      => md5_file($file_path),
                // 'sha1'     => sha1_file($file_path),
                'md5'       => md5($img),
                'sha1'      => sha1($img),
                'savename'  => $save_name,
                'savepath'  => $this->subDirectory . date('Ymd') . '/',
                'haslitpic' => 0, //预设缩图为0
            );
            //是否有缩略图
            if ($this->thumFlag) {
                $this->_doThum($info);
            }

            return $info;

        }

    }

    /**
     * 拉取远程图片
     * @return mixed
     */
    private function _saveRemote()
    {
        if (isset($_POST[$this->fileField])) {
            $source = $_POST[$this->fileField];
        } else {
            $source = $_GET[$this->fileField];
        }
        if (empty($source) && !is_array($source)) {
            return "图片地址为空";
        }
        $source = array_unique($source);//去重

        $info = array();
        foreach ($source as $imgUrl) {
            $imgUrl = htmlspecialchars($imgUrl);
            $imgUrl = str_replace("&amp;", "&", $imgUrl);

            $new_file = array(
                'status' => 0, //状态
                'info'   => '', //状态信息
                'size'   => 0,
                'source' => $imgUrl,
            );

            //http开头验证
            if (stripos($imgUrl, "http") !== 0) {
                $new_file['info'] = "链接不是http链接";
                break;
            }

            preg_match('/(^https*:\/\/[^:\/]+)/', $imgUrl, $matches);
            $host_with_protocol = count($matches) > 1 ? $matches[1] : '';

            // 判断是否是合法 url
            if (!filter_var($host_with_protocol, FILTER_VALIDATE_URL)) {
                $new_file['info'] = "非法 URL";
                break;
            }

            preg_match('/^https*:\/\/(.+)/', $host_with_protocol, $matches);
            $host_without_protocol = count($matches) > 1 ? $matches[1] : '';

            // 此时提取出来的可能是 ip 也有可能是域名，先获取 ip
            $ip = gethostbyname($host_without_protocol);
            // 判断是否是私有 ip
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
                $new_file['info'] = "非法 IP";
                break;
            }

            //获取请求头并检测死链
            $heads = get_headers($imgUrl, 1);
            if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
                $new_file['info'] = "链接不可用";
                break;
            }
            //格式验证(扩展名验证和Content-Type验证)
            $ext = strtolower(ltrim(strrchr($imgUrl, '.'), '.'));
            if (!in_array($ext, $this->allowType) || !isset($heads['Content-Type']) || !stristr($heads['Content-Type'], "image")) {
                $new_file['info'] = "链接contentType不正确";
                break;
            }
            $file_type = $heads['Content-Type'];

            //打开输出缓冲区并获取远程图片
            ob_start();
            $context = stream_context_create(
                array('http' => array(
                    'follow_location' => false, // don't follow redirects
                ))
            );
            readfile($imgUrl, false, $context);
            $img = ob_get_contents();
            ob_end_clean();
            preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $imgUrl, $m);

            $name      = $m ? $m[1] : "";
            $file_size = strlen($img);
            $save_name = uniqid() . '.' . $ext;
            $file_path = $this->rootPath . $this->subDirectory . date('Ymd') . '/' . $save_name;
            $dir_name  = dirname($file_path);

            //检查文件大小是否超出限制
            if ($file_size > get_upload_maxsize()) {
                $new_file['info'] = "文件大小超出网站限制";
                break;
            }

            //创建目录失败
            if (!file_exists($dir_name) && !mkdir($dir_name, 0777, true)) {
                $new_file['info'] = "目录创建失败";
                break;
            } else if (!is_writeable($dir_name)) {
                $new_file['info'] = "目录没有写权限";
                break;
            }

            //移动文件
            if (!(file_put_contents($file_path, $img) && file_exists($file_path))) {
                //移动失败
                $new_file['info'] = "写入文件内容错误";
                break;
            } else {
                //移动成功
                $new_file['status'] = 1;
                $new_file['info']   = '保存远程图片成功';

                $new_file['name'] = $name;
                $new_file['type'] = $file_type;
                $new_file['size'] = $file_size;
                $new_file['ext']  = $ext;

                // 'md5'] = md5_file($file_path);
                // 'sha1'] = sha1_file($file_path);
                $new_file['md5']       = md5($img);
                $new_file['sha1']      = sha1($img);
                $new_file['savename']  = $save_name;
                $new_file['savepath']  = $this->subDirectory . date('Ymd') . '/';
                $new_file['haslitpic'] = 0; //预设缩图为0
                $info[]                = $new_file;

            }
        }

        //是否有缩略图
        if ($this->thumFlag) {
            $this->_doThum($info);
        }

        return $info;

    }

    public function _doThum(&$info)
    {
        if (empty($info)) {
            return;
        }

        $ext_dest = 'jpg'; //针对图片--生成缩略图格式
        /*缩略图设置*/
        //设置需要生成缩略图,仅对图像文件有效
        //读取配置文件中的设置
        $imgtbSize  = explode(',', C('CFG_IMGTHUMB_SIZE'));
        $imgtbArray = array();
        foreach ($imgtbSize as $v) {
            $t_size = explode('X', $v);

            if (empty($t_size) || empty($t_size[0]) || empty($t_size[1])) {
                continue;
            }
            $imgtbArray[] = array('w' => intval($t_size[0]), 'h' => intval($t_size[1]));
        }
        //$info = current($info);    //第一张图片信息

        //读取配置文件固定宽等比缩略
        $imgtbFixWidth = explode(',', C('CFG_IMGTHUMB_WIDTH'));
        $imgtbFixArray = array();
        foreach ($imgtbFixWidth as $v) {
            if (empty($v) || intval($v) == 0) {
                continue;
            }
            $imgtbFixArray[] = array('w' => intval($v), 'h' => intval($v * 100));
        }

        if (!empty($imgtbFixArray) || !empty($imgtbArray)) {
            //默认使用GD
            $think_img = new \Think\Image();
            $thumbType = C('CFG_IMGTHUMB_TYPE') ? 3 : 1; //配置大小

            foreach ($info as $k => $file) {
                if (empty($file['savename'])) {
                    break; //无文件名的跳过--比如远程图片，失败的直接跳过
                }

                $real_path = $this->rootPath . $file['savepath'] . $file['savename'];
                //$think_img->open($real_path);//$think_img->open($real_path)->thumb(xx,xx,xx)->save(xx,xx);

                //生成缩略图,固定大小
                foreach ($imgtbArray as $i => $v) {
                    $strSuffix = '!' . $v['w'] . 'X' . $v['h'];
                    $think_img->open($real_path)->thumb($v['w'], $v['h'], $thumbType)->save($real_path . $strSuffix . '.' . $ext_dest, $ext_dest);

                }
                //生成缩略图，不放大，等宽，高度不限
                foreach ($imgtbFixArray as $v) {
                    $strSuffix = '!' . $v['w'] . 'X';
                    $think_img->open($real_path)->thumb($v['w'], $v['h'], 1)->save($real_path . $strSuffix . '.' . $ext_dest, $ext_dest);
                }

                $info[$k]['haslitpic'] = 1; //设置有缩略图
            }

        }
    }

    /**
     *图片(上传后)数组入库
     *filearr:图片数组
     **/
    public function _saveDate($filearr)
    {
        if (!is_array($filearr)) {
            return false;
        }

        $db  = M('attachment');
        $num = 0;

        foreach ($filearr as $k => $file) {

            $data['filepath']  = $file['savepath'] . $file['savename'];
            $data['title']     = $file['name'];
            $data['haslitpic'] = empty($file['haslitpic']) ? 0 : 1;
            $filetype          = 1;
            //后缀
            switch ($file['ext']) {
                case 'gif':
                    $filetype = 1;
                    break;
                case 'jpg':
                    $filetype = 1;
                    break;
                case 'png':
                    $filetype = 1;
                    break;
                case 'bmp':
                    $filetype = 1;
                    break;
                case 'swf': //flash
                    $filetype = 2;
                    break;
                case 'mp3': //音乐
                    $filetype = 3;
                    break;
                case 'wav':
                    $filetype = 3;
                    break;
                case 'rm': //电影
                    $filetype = 4;
                    break;

                case 'doc': //
                    $filetype = 5;
                    break;
                case 'docx': //
                    $filetype = 5;
                    break;
                case 'xls': //
                    $filetype = 5;
                    break;
                case 'ppt': //
                    $filetype = 5;
                    break;
                case 'zip': //
                    $filetype = 6;
                    break;
                case 'rar': //
                    $filetype = 6;
                    break;
                case '7z': //
                    $filetype = 6;
                    break;

                default: //其他
                    $filetype = 0;
                    break;
            }
            $data['filetype']   = $filetype;
            $data['filesize']   = $file['size'];
            $data['uploadtime'] = time();
            $data['aid']        = session(C('USER_AUTH_KEY')); //管理员ID
            if ($db->add($data)) {
                ++$num;
            }
        }

        if ($num == count($filearr)) {
            return true;
        } else {
            return false;
        }

    }

}
