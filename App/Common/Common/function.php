<?php
/******公共函数文件*******/
define('B_PIC', 1); // 图片
define('B_TOP', 2); // 头条 (置顶)
define('B_REC', 4); // 推荐
define('B_SREC', 8); // 特荐
define('B_SLIDE', 16); // 幻灯
define('B_JUMP', 32); // 跳转
define('B_OTHER', 64); // 其他

//order_status
define('OS_UNCONFIRMED', 0); // 未确认
define('OS_CONFIRMED', 1); // 已确认
define('OS_CANCELED', 2); // 已取消
define('OS_INVALID', 3); // 无效
define('OS_RETURNED', 4); // 退货

//distribution_status
define('DS_UNSHIPPED', 0); // 未发货
define('DS_SHIPPED', 1); // 已发货
define('DS_RECEIVED', 2); // 已收货
define('DS_PREPARING', 3); // 备货中

//pay_status
define('PS_UNPAYED', 0); // 未付款
define('PS_PAYING', 1); // 付款中
define('PS_PAYED', 2); // 已付款

//magic_quotes_gpc如果开启,去掉转义，不然加上TP入库时的转义，会出现两次反斜线转义
if (get_magic_quotes_gpc()) {
    function stripslashes_deep($value)
    {
        $value = is_array($value) ?
        array_map('stripslashes_deep', $value) :
        stripslashes($value); //去掉由addslashes添加的转义
        return $value;
    }
    $_POST    = array_map('stripslashes_deep', $_POST);
    $_GET     = array_map('stripslashes_deep', $_GET);
    $_COOKIE  = array_map('stripslashes_deep', $_COOKIE);
    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}

function p($array)
{

    //dump(数组参数,是否显示1/0,显示标签('<pre>'),模式[0为print_r])
    dump($array, 1, '', 0);
}

/**
 * 删除静态缓存文件
 * @param string $str 缓存路径
 * @param boolean $isdir 是否是目录
 * @param string $rules 缓存规则名
 * @return mixed
 */
function del_cache_html($str, $isdir = false, $rules = '')
{
    //为空，且不是目录
    $delflag = true;
    if (empty($str) && !$isdir) {
        return;
    }
    $str_array = array();

    //更新静态缓存
    $html_cache_rules = get_meta_value('HTML_CACHE_RULES_COMMON');
    if (get_meta_value('HOME_HTML_CACHE_ON')) {
        $str_array[] = HTML_PATH . 'Home/' . $str;
    }

    if (get_meta_value('MOBILE_HTML_CACHE_ON')) {
        $str_array[] = HTML_PATH . 'Mobile/' . $str;
    }

    if (!empty($rules) && !isset($html_cache_rules[$rules])) {
        $delflag = false; //指定规则，如不存在则不用清除
    } else {
        $delflag = true;
    }

    if ($delflag) {
        foreach ($str_array as $v) {
            if ($isdir && is_dir($v)) {
                del_dir_file($v, false);
            } else {
                $list = glob($v . '*');
                for ($i = 0; $i < count($list); $i++) {
                    if (is_file($list[$i])) {
                        unlink($list[$i]);
                    }
                }
            }

        }

    }

}

/**
 * 取出所有分类
 * @param string $status 显示部份(0|1|2)， 0显示全部(默认),1显示不隐藏的,2显示type为0(类型为内部模型非外链)全部
 * @param boolean $update 更新缓存(0|1)， 默认不更新[0]
 * @return array
 */
function get_category($status = 0, $update = 0)
{
//
    $cate_sname = 'fCategery_' . $status;
    $cate_arr   = F($cate_sname);
    if ($update || !$cate_arr) {
        if ($status == 1) {
            $cate_arr = D('CategoryView')->nofield('content')->where(array('category.status' => 1))->order('category.sort,category.id')->select();
        } else if ($status == 2) {
//后台栏目专用
            $cate_arr = D('CategoryView')->nofield('content')->where(array('category.type' => 0))->order('category.sort,category.id')->select();
        } else {
            $cate_arr = D('CategoryView')->nofield('content')->order('category.sort,category.id')->select();
        }
        if (!isset($cate_arr)) {
            $cate_arr = array();
        }

        //S(缓存名称,缓存值,缓存有效时间[秒]);
        //S($cate_sname, $cate_arr, 48 * 60 * 60);
        F($cate_sname, $cate_arr);
    }
    return $cate_arr;
}

/**
 * 获取栏目或文档网址--[Home|Mobile]
 * @param array $cate 栏目数组
 * @param integer $id 文档id
 * @param boolean $jumpflag 是否跳转
 * @param string $jumpurl 跳转网址
 * @return string
 */
function get_url($cate, $id = 0, $jumpflag = false, $jumpurl = '')
{
    $url = '';
    //如果是跳转，直接就返回跳转网址
    if ($jumpflag && !empty($jumpurl)) {
        return $jumpurl;
    }

    if (empty($cate)) {
        return $url;
    }

    //修正不能跨模块，判断当前MODULE_NAME
    if (in_array(MODULE_NAME, array('Home', 'Mobile'))) {
        $module = '';
    } else {
        $module = '/'; //'Home/';
    }

    $ename = $cate['ename'];
    if ($cate['type'] == 1) {
        $firstChar = substr($ename, 0, 1);
        if ($firstChar == '@') {
//内部
            //不存在文档id,也无路由情况
            $ename     = ucfirst(substr($ename, 1)); //
            $firstChar = substr($ename, 0, 1);
            $url       = ($firstChar != '/') ? U($module . $ename, '') : U('' . $ename, '');

        } else {
            $url = $ename; //http://
        }

    } else {
        //开启路由
        if (C('URL_ROUTER_ON') == true) {
            $url = $id > 0 ? U($module . '' . $ename . '/' . $id, '') : U('/' . $ename, '', '');
        } else {

            $url = U($module . 'List/index', array('cid' => $cate['id']));
            if ($id > 0) {
                $url = U($module . 'Show/index', array('cid' => $cate['cid'], 'id' => $cate['id']));
            }

        }

    }

    return $url;

}

/**
 * 获取文档内容页网址--[Home|Mobile]
 * @param integer $id 文档id
 * @param integer $cid 栏目id
 * @param string $ename 栏目英文名称
 * @param boolean $jumpflag 是否跳转
 * @param string $jumpurl 跳转网址
 * @return string
 */
function get_content_url($id, $cid, $ename, $jumpflag = false, $jumpurl = '')
{
    $url = '';
    //如果是跳转，直接就返回跳转网址
    if ($jumpflag && !empty($jumpurl)) {
        return $jumpurl;
    }
    if (empty($id) || empty($cid) || empty($ename)) {
        return $url;
    }

    //修正不能跨模块，判断当前MODULE_NAME
    if (in_array(MODULE_NAME, array('Home', 'Mobile'))) {
        $module = '';
    } else {
        $module = '/'; //'Home/';
    }

    //开启路由
    if (C('URL_ROUTER_ON') == true) {
        $url = $id > 0 ? U($module . '' . $ename . '/' . $id, '') : U('/' . $ename, '', '');
    } else {
        $url = U($module . 'Show/index', array('cid' => $cid, 'id' => $id));
    }

    return $url;
}

/**
 * 当前位置
 * @param integer $typeid 栏目id
 * @param string $sname 指定子名称
 * @param string $surl 指定子网址
 * @param boolean $ismobile 是否手机版
 * @param string $delimiter 分割符
 * @return string
 */
function get_position($typeid = 0, $sname = '', $surl = '', $ismobile = false, $delimiter = '&gt;&gt;')
{
    if ($delimiter == '') {
        $delimiter = '&gt;&gt;';
    }
    $url      = $ismobile ? U(MODULE_NAME . '/Index/index/') : C('CFG_WEBURL');
    $position = '<a href="' . $url . '">首页</a>';

    //Parents of Category
    if (!empty($typeid)) {
        $cate       = get_category(0); //ALL
        $getParents = \Common\Lib\Category::getParents($cate, $typeid);
        if (is_array($getParents)) {
            foreach ($getParents as $v) {
                $position .= $delimiter . '<a href="' . get_url($v) . '">' . $v['name'] . '</a>';
            }
        }
    }

    if (!empty($sname)) {
        if (empty($surl)) {
            $position .= $delimiter . $sname;
        } else {
            $position .= $delimiter . '<a href="' . $surl . '">' . $sname . '</a>';
        }
    }

    return $position;
}

/**
 * 获取联动(字典)项的值
 * @param string $group 联动组名
 * @param integer $value 联动值
 * @return string
 */
function get_item_value($group, $value = 0)
{
    //return $value.'--<br>';
    ${'item_' . $group} = get_item($group);
    if (isset(${'item_' . $group}[$value])) {
        return ${'item_' . $group}[$value];
    } else {
        return "保密";
    }
}

/**
 * 获取对应组的联动列表
 * @param string $group 联动组名
 * @param integer $value 联动值
 * @return array
 */
function get_item($group = 'animal', $update = 0)
{

    //S方法的缓存名都带's'
    $itme_arr = S('sItem_' . $group);
    if ($update || !$itme_arr) {
        $itme_arr = array();
        $temp     = M('iteminfo')->where(array('group' => $group))->order('sort,id')->select();
        foreach ($temp as $key => $v) {
            $itme_arr[$v['value']] = $v['name'];

        }

        //S(缓存名称,缓存值,缓存有效时间[秒]);
        S('sItem_' . $group, $itme_arr, 48 * 60 * 60);
    }
    return $itme_arr;
}

/**
 * 获取自由块内容
 * @param string $name 自由块名
 * @param boolean $update 是否更新
 * @return array
 */
function get_block($name, $update = 0)
{
    $block_sname = 'fBlock/' . md5($name);
    $_block      = F($block_sname);
    if ($update || !$_block) {

        $_block = M('block')->where(array('name' => "$name"))->find();
        if (!isset($_block)) {
            $_block = null;
            if (!$update) {
                return null;
            }

        }
        //F(缓存名称,缓存值);
        F($block_sname, $_block);
    }
    return $_block;
}

/**
 * 获取点击次数(同时点击数增加1)
 * @param integer $id 文档id
 * @param string $tablename 表名
 * @return integer
 */
function get_click($id, $tablename)
{

    $id = intval($id);
    if (empty($id) || empty($tablename)) {
        return '--';
    }
    $num = M($tablename)->where(array('id' => $id))->getField('click');
    M($tablename)->where(array('id' => $id))->setInc('click');
    return "$num";
}

/**
 * 获取上传最大值(字节数), KB转字节
 * @param integer $size 默认大小值
 * @param string $cfg 配置项值
 * @return integer
 */
function get_upload_maxsize($size = 2048, $cfg = 'CFG_UPLOAD_MAXSIZE')
{
    $maxsize = get_cfg_value($cfg);
    if (empty($maxsize)) {
        $maxsize = $size;
    }
    return $maxsize * 1024;
}

/**
 * 广告
 * @param integer $id 广告id
 * @param boolean $flag 是否js方式输出(0|1), 默认html
 * @return string
 */
function get_abc($id, $flag = 0)
{

    $id = intval($id);
    if (empty($id)) {
        return '';
    }
    $setting = '';
    $abc     = M('abc')->find($id);
    if ($abc) {
        $where = array('aid' => $id,
            'status'             => 1,
            'starttime'          => array('lt', time()),
            'endtime'            => array('gt', time()),
        );
        $detail = M('abcDetail')->where($where)->order('sort')->limit($abc['num'])->select();
        if (!$detail) {
            $detail = array();
        }

        $setting = $abc['setting'];
        $pattern = '/<loop>(.*?)<\/loop>/is';
        preg_match_all($pattern, $setting, $mat);

        if (!empty($mat[1])) {
            $rep = array();
            foreach ($mat[1] as $k => $v) {
                $rep[$k] = '';
                foreach ($detail as $k2 => $v2) {
                    $search = array('{$id}', '{$title}', '{$content}', '{$url}', '{$sort}',
                        '{$width}', '{$height}', '{$autoindex}', '{$autoindex+1}', '{$autoindex+2}');
                    $replace = array($v2['id'], $v2['title'], $v2['content'], $v2['url'], $v2['sort'],
                        $abc['width'], $abc['height'], $k2, $k2 + 1, $k2 + 2);

                    $rep[$k] .= str_replace($search, $replace, $v);
                }
            }
            $setting = str_replace($mat[0], $rep, $setting);
        }

    }

    //js输出
    if ($flag) {
        $setting = 'document.write("' . str_replace(array('"', "\r\n"), array('\"', ''), $setting) . '");';
    }
    return $setting;
}

/**
 * 取出存档分类
 * @param integer $modelid 模型id
 * @param integer $update 更新缓存(0|1|2)， 默认0不更新,1更新，2是删除
 * @return array
 */
function get_datelist($modelid = 1, $update = 0)
{
//
    $modelid = intval($modelid);
    $arr     = array();
    //为[0]或page模型[2]
    if ($modelid == 0 || $modelid == 2) {
        return $arr;
    }
    $format = '%Y-%m';
    $sname  = 'fDateList_' . $modelid;
    //删除，直抒返回
    if ($update == 2) {
        F($sname, null);
        return $arr;
    }
    $arr = F($sname);
    if ($update || !$arr) {
        $tablename = M('model')->where(array('id' => $modelid))->getField('tablename');
        if ($tablename) {
            $arr = M($tablename)->field("count(*) as arc_num, FROM_UNIXTIME(publishtime,'%Y') as arc_year, FROM_UNIXTIME(publishtime,'%m') as arc_month")->group("FROM_UNIXTIME(publishtime,'" . $format . "')")->order('publishtime desc')->select();
        } else {
            $arr = array();
        }

        if (!isset($arr)) {
            $arr = array();
        }

        F($sname, $arr);
    }
    return $arr;
}

/**
 * 生成省市联动js
 * @return boolean
 */
function get_js_city()
{

    $str = <<<str
function setcity() {
    var SelP=document.getElementsByName(arguments[0])[0];
    var SelC=document.getElementsByName(arguments[1])[0];
    var DefP=arguments[2];
    var DefC=arguments[3];
str;

    $province = M('area')->where(array('pid' => 0))->order('sort,id')->select();
    //Province
    $pcount = count($province) - 1; //$key 是从0开始的
    $str .= "var provinceOptions = new Array(";
    $str .= '"请选择省份",0';
    foreach ($province as $k => $v) {
        $str .= ',"' . $v['sname'] . '",' . $v['id'] . '';
    }
    $str .= " );\n";

    $str .= <<<str
    SelP.options.length = 0;
    for(var i = 0; i < provinceOptions.length/2; i++) {
        SelP.options[i]=new Option(provinceOptions[i*2],provinceOptions[i*2+1]);
        if(SelP.options[i].value==DefP) {
            SelP.selectedIndex = i;
        }
    }

    SelP.onchange = function(){
        switch (SelP.value) {
str;

    foreach ($province as $v) {
        $str .= 'case "' . $v['id'] . '" :' . "\n";
        //$str .= 'case "'.$v['sname'].'" :'."\n";
        $str .= "var cityOptions = new Array(";
        $city  = M('area')->where(array('pid' => $v['id']))->order('sort,id')->select();
        $count = count($city) - 1; //$key 是从0开始的
        foreach ($city as $key => $value) {
            $str .= '"' . $value['sname'] . '",' . $value['id'] . '';
            if ($key != $count) {
                $str .= ","; //不为最后一个元素，就加上","
            }
        }

        $str .= " );\n";
        $str .= " break;\n";
    }

    $str .= <<<str
        default:
            var cityOptions = new Array("");
            break;
        }

        SelC.options.length = 0;
        for(var i = 0; i < cityOptions.length/2; i++) {
            SelC.options[i]=new Option(cityOptions[i*2],cityOptions[i*2+1]);
            if (SelC.options[i].value==DefC) {
                SelC.selectedIndex = i;
            }
        }
    }

    if (DefP) {
        if(SelP.fireEvent) {
        SelP.fireEvent('onchange');
        //alert('ok');
        }else {
            SelP.onchange();
        }
    }

}
str;

    //echo $str;
    if (file_put_contents('./Data/resource/js/city.js', $str)) {
        return true;
    } else {
        return false;
    }

}

/**
 * 获取文件目录列表
 * @param string $pathname 路径
 * @param integer $fileFlag 文件列表 0所有文件列表,1只读文件夹,2是只读文件(不包含文件夹)
 * @param string $pathname 路径
 * @return array
 */
function get_file_folder_List($pathname, $fileFlag = 0, $pattern = '*')
{
    $fileArray = array();
    $pathname  = rtrim($pathname, '/') . '/';
    $list      = glob($pathname . $pattern);
    foreach ($list as $i => $file) {
        switch ($fileFlag) {
            case 0:
                $fileArray[] = basename($file);
                break;
            case 1:
                if (is_dir($file)) {
                    $fileArray[] = basename($file);
                }
                break;

            case 2:
                if (is_file($file)) {
                    $fileArray[] = basename($file);
                }
                break;

            default:
                break;
        }
    }

    if (empty($fileArray)) {
        $fileArray = null;
    }

    return $fileArray;
}

/**
 * 循环删除目录和文件函数
 * @param string $dirName 路径
 * @param boolean $fileFlag 是否删除目录
 * @return void
 */
function del_dir_file($dirName, $bFlag = false)
{
    if ($handle = opendir("$dirName")) {
        while (false !== ($item = readdir($handle))) {
            if ($item != "." && $item != "..") {
                if (is_dir("$dirName/$item")) {
                    del_dir_file("$dirName/$item", $bFlag);
                } else {
                    unlink("$dirName/$item");
                }
            }
        }
        closedir($handle);
        if ($bFlag) {
            rmdir($dirName);
        }

    }
}

/**
 * 计算年龄
 * @param string $birth 日期 如1981-1-1
 * @return integer
 */
function birthday2age($birth)
{
    list($byear, $bmonth, $bday) = explode('-', $birth);
    $age                         = date('Y') - $byear - 1;
    $tmonth                      = date('n');
    $tday                        = date('j');
    if ($tmonth > $bmonth || $tmonth == $bmonth && $tday > $bday) {
        $age++;
    }

    return $age;
}

/**
 * 替换字符串为指定的字符
 * @param string $str 字符串
 * @param integer $num 替换个数
 * @param string $sp 替换后的字符
 * @return string
 */
function str2symbol($str, $num = 1, $sp = '*')
{
    if ($str == '' || $num <= 0) {
        return $str;
    }
    $num    = mb_strlen($str, 'utf-8') > $num ? $num : mb_strlen($str, 'utf-8');
    $newstr = '';
    for ($i = 0; $i < $num; $i++) {
        $newstr .= '*';
    }
    $newstr .= mb_substr($str, $num, mb_strlen($str, 'utf-8') - $num, 'utf-8'); //substr中国会乱码

    return $newstr;

}

/**
 * 截取指定长度的字符串
 * @param string $str 字符串
 * @param integer $num 截取长度
 * @param boolean $flag 是否显示省略符
 * @param string $sp 省略符
 * @return string
 */
function str2sub($str, $num, $flag = 0, $sp = '...')
{
    if ($str == '' || $num <= 0) {
        return $str;
    }
    $strlen = mb_strlen($str, 'utf-8');
    $newstr = '';
    $newstr .= mb_substr($str, 0, $num, 'utf-8'); //substr中国会乱码
    if ($num < $strlen && $flag) {
        $newstr .= $sp;
    }

    return $newstr;
}

/**
 * 字符串过滤
 * @param string $str 字符串
 * @param string $delimiter 分割符
 * @param boolean $flag 是否检测成员为数字
 * @return string
 */
function string2filter($str, $delimiter = ',', $flag = false)
{
    if (empty($str)) {
        return '';
    }

    $tmp_arr  = array_filter(explode($delimiter, $str)); //去除空数组'',0,再使用sort()重建索引
    $tmp_arr2 = array();

    //检验是不是数字
    if ($flag) {
        foreach ($tmp_arr as $v) {
            if (is_numeric($v)) {
                $tmp_arr2[] = $v;
            }
        }
    } else {
        $tmp_arr2 = $tmp_arr;
    }

    return implode($delimiter, $tmp_arr2);

}

//flag相加,返回数值，用于查询
function flag2sum($str, $delimiter = ',')
{
    if (empty($str)) {
        return 0;
    }
    $tmp_arr = array_filter(explode($delimiter, $str)); //去除空数组'',0,再使用sort()重建索引
    if (empty($tmp_arr)) {
        return 0;
    }

    $arr = array('a' => B_PIC, 'b' => B_TOP, 'c' => B_REC, 'd' => B_SREC, 'e' => B_SLIDE, 'f' => B_JUMP, 'g' => B_OTHER);
    $sum = 0;
    foreach ($arr as $k => $v) {
        if (in_array($k, $tmp_arr)) {
            $sum += $v;
        }
    }

    return $sum;

}

function check_badword($content)
{
    //定义处理违法关键字的方法
    $badword = C('CFG_BADWORD'); //定义敏感词

    if (empty($badword)) {
        return false;
    }
    $keyword = explode('|', $badword);
    $m       = 0;
    for ($i = 0; $i < count($keyword); $i++) {
        //根据数组元素数量执行for循环
        //应用substr_count检测文章的标题和内容中是否包含敏感词
        if (substr_count($content, $keyword[$i]) > 0) {
            //$m ++;
            return true;
        }
    }
    //return $m;              //返回变量值，根据变量值判断是否存在敏感词
    return false;
}

/**
 * 对用户的密码进行加密
 * @param $password
 * @param $encrypt //传入加密串，在修改密码时做认证
 * @return array/password
 */
function get_password($password, $encrypt = '')
{
    $pwd             = array();
    $pwd['encrypt']  = $encrypt ? $encrypt : get_randomstr();
    $pwd['password'] = md5(md5(trim($password)) . $pwd['encrypt']);
    return $encrypt ? $pwd['password'] : $pwd;
}

/**
 * 对用户的已经加密的密码进行二次加密--new
 * @param $password_md5 已经加密的密码
 * @param $encrypt //传入加密串，在修改密码时做认证
 * @return array/password
 */
function get_password_md5($password_md5, $encrypt = '') {
    $pwd             = array();
    $pwd['encrypt']  = $encrypt ? $encrypt : get_randomstr();
    $pwd['password'] = md5($password_md5 . $pwd['encrypt']);
    return $encrypt ? $pwd['password'] : $pwd;
}

/**
 * 生成随机字符串
 * @param string $lenth 长度
 * @return string 字符串
 */
function get_randomstr($lenth = 6)
{
    return get_random($lenth, '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ');
}

/**
 * 产生随机字符串
 *
 * @param    int        $length  输出长度
 * @param    string     $chars   可选的 ，默认为 0123456789
 * @return   string     字符串
 */
function get_random($length, $chars = '0123456789')
{
    $hash = '';
    $max  = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

/**
 * 得到指定cookie的值
 *
 * @param string $name
 */
//function get_cookie($name, $key = '@^%$y5fbl') {
function get_cookie($name, $key = '')
{

    if (!isset($_COOKIE[$name])) {
        return null;
    }
    $key = empty($key) ? C('CFG_COOKIE_ENCODE') : $key;

    $value = $_COOKIE[$name];
    $key   = md5($key);
    $sc    = new \Common\Lib\SysCrypt($key);
    $value = $sc->php_decrypt($value);
    return unserialize($value);
}

/**
 * 设置cookie
 *
 * @param array $args
 * @return boolean
 */
//使用时修改密钥$key 涉及金额结算请重新设计cookie存储格式
//function set_cookie($args , $key = '@^%$y5fbl') {
function set_cookie($args, $key = '')
{
    $key = empty($key) ? C('CFG_COOKIE_ENCODE') : $key;

    $name   = $args['name'];
    $expire = isset($args['expire']) ? $args['expire'] : null;
    $path   = isset($args['path']) ? $args['path'] : '/';
    $domain = isset($args['domain']) ? $args['domain'] : null;
    $secure = isset($args['secure']) ? $args['secure'] : 0;
    $value  = serialize($args['value']);

    $key   = md5($key);
    $sc    = new \Common\Lib\SysCrypt($key);
    $value = $sc->php_encrypt($value);
    //setcookie($cookieName ,$cookie, time()+3600,'/','',false);
    return setcookie($name, $value, $expire, $path, $domain, $secure); //失效时间   0关闭浏览器即失效
}

/**
 * 删除cookie
 * @param array $args
 * @return boolean
 */
function del_cookie($args)
{
    $name   = $args['name'];
    $domain = isset($args['domain']) ? $args['domain'] : null;
    return isset($_COOKIE[$name]) ? setcookie($name, '', time() - 86400, '/', $domain) : true;
}

/**
 * 获取指定大小的头像[必须系统有的]
 * @param string $str 头像地址
 * @param integer $size 尺寸长宽
 * @param boolean $rnd 是否显示随机码
 * @return string
 */
function get_avatar($str, $size = 160, $rnd = false)
{

    $ext = 'jpg';
    if (!empty($str)) {
        $ext = explode('.', $str);
        $ext = end($ext);
    }

    if (empty($ext) || !in_array(strtolower($ext), array('jpg', 'gif', 'png', 'jpeg'))) {
        $str = '';
    }
    if (empty($str)) {
        $str = __ROOT__ . '/avatar/system/0.jpg';
        $ext = 'jpg';
        if ($size > 160 || $size < 30) {
            $size = 160;
        }
    }

    if ($size == 0) {
        return $str;
    }
    $rndstr = $rnd ? '?random=' . time() : '';
    return $str . '!' . $size . 'X' . $size . '.' . $ext . $rndstr;
}

/**
 * 获取指定长宽的图片[尺寸见后台设置]
 * @param string $str 图片地址
 * @param integer $width 长度
 * @param integer $height 高度
 * @param boolean $rnd 是否显示随机码
 * @return string
 */
function get_picture($str, $width = 0, $height = 0, $rnd = false)
{

    //$ext = end(explode('.', $str));
    $ext      = 'jpg'; //原文件后缀
    $ext_dest = 'jpg'; //生成缩略图格式
    $height   = $height == 0 ? '' : $height;
    if (!empty($str)) {
        $str = preg_replace('/!(\d+)X(\d+)\.' . $ext_dest . '$/i', '', $str); //清除缩略图的!200X200.jpg后缀

        $ext = explode('.', $str);
        $ext = end($ext);
    }
    if (empty($ext) || !in_array(strtolower($ext), array('jpg', 'gif', 'png', 'jpeg'))) {
        $str = '';
    }
    if (empty($str)) {
        $str      = __ROOT__ . '/uploads/system/nopic.png';
        $ext      = 'png';
        $ext_dest = 'png';
        $width    = 0;
    }
    if ($width == 0) {
        return $str;
    }

    $rndstr = $rnd ? '?random=' . time() : '';
    return $str . '!' . $width . 'X' . $height . '.' . $ext_dest . $rndstr;
}

/**
 * 功能：计算文件大小
 * @param int $bytes
 * @return string 转换后的字符串
 */
function get_byte($bytes)
{
    if (empty($bytes)) {
        return '--';
    }
    $sizetext = array(" B", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
    return round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2) . $sizetext[$i];
}

/**
 * 获取拼音信息
 * @param     string  $str  字符串
 * @param     int  $ishead  是否为首字母
 * @param     int  $isclose  解析后是否释放资源
 * @param     int  $lang  语言
 * @return    string
 * 用法：$data['EnglishName'] = $this->get_pinyin(iconv('utf-8','gbk//ignore',$utfstr),0);
 */
function get_pinyin($str, $ishead = 0, $isclose = 1, $lang = 'zh-cn')
{
    //global $pinyins;
    $pinyins = array();
    $restr   = '';
    $str     = trim($str);
    $slen    = strlen($str);
    //$str=iconv("UTF-8","gb2312",$str);
    //echo $str;
    if ($slen < 2) {
        return $str;
    }
    $file = './Data/pinyin-' . $lang . '.dat';
    if (!file_exists($file)) {
        $file = './Data/pinyin-zh-cn.dat';
    }
    if (count($pinyins) == 0) {
        $fp = fopen($file, 'r');
        if (false == $fp) {
            return '';
        }
        while (!feof($fp)) {
            $line                         = trim(fgets($fp));
            $pinyins[$line[0] . $line[1]] = substr($line, 3, strlen($line) - 3);
        }
        fclose($fp);
    }

    for ($i = 0; $i < $slen; $i++) {
        if (ord($str[$i]) > 0x80) {
            $c = $str[$i] . $str[$i + 1];
            $i++;
            if (isset($pinyins[$c])) {
                if ($ishead == 0) {
                    $restr .= $pinyins[$c];
                } else {
                    $restr .= $pinyins[$c][0];
                }
            } else {
                $restr .= "x"; //$restr .= "_";
            }
        } else if (preg_match("/[a-z0-9]/i", $str[$i])) {
            $restr .= $str[$i];
        } else {
            $restr .= "x"; //$restr .= "_";
        }
    }
    if ($isclose == 0) {
        unset($pinyins);
    }
    return $restr;
}

/*
 * 获取模板地址
 * @param string $tpl 模板文件名
 * @param string $style 样式
 * @return string
 */
function get_tpl($tpl = '', $style = '')
{
    $tplPath = './Public/' . MODULE_NAME . '/';
    $tplPath .= empty($style) ? C('CFG_THEMESTYLE') . '/' : $style . '/';
    if (trim($tpl) == '') {
        $tplPath .= CONTROLLER_NAME . C('TMPL_FILE_DEPR') . ACTION_NAME . C('TMPL_TEMPLATE_SUFFIX');
    } elseif (strpos($tpl, '.') > 0) {
        $tplPath .= $tpl;
    } else {
        $tplPath .= $tpl . C('TMPL_TEMPLATE_SUFFIX');
    }
    return $tplPath;
}

/**
 * 返回数据元值表(Key-Value)
 * @param string|integer $key 标识名
 * @return mixed
 */
function get_meta_value($key)
{
    $sname = 'config/meta';
    if ($key == '') {
        return '';
    }
    $array = F($sname);
    if (!$array) {
        $data  = M('meta')->field('name,value')->select();
        $array = array();
        if ($data) {
            foreach ($data as $value) {
                $array[$value['name']] = $value['value'];
            }
        }

        //静态缓存规则
        $html_cache_rules = array();
        if (isset($array['HTML_CACHE_INDEX_ON']) && $array['HTML_CACHE_INDEX_ON'] == 1) {
            $html_cache_rules['index:index'] = array('{:module}/Index_{:action}', intval($array['HTML_CACHE_INDEX_TIME']));
        }
        if (isset($array['HTML_CACHE_LIST_ON']) && $array['HTML_CACHE_LIST_ON'] == 1) {
            $html_cache_rules['list:index'] = array('{:module}/List/{:action}_{e}{cid|intval}_{p|intval}', intval($array['HTML_CACHE_LIST_TIME']));
        }
        if (isset($array['HTML_CACHE_SHOW_ON']) && $array['HTML_CACHE_SHOW_ON'] == 1) {
            $html_cache_rules['show:index'] = array('{:module}/Show/{:action}_{e}{cid|intval}_{id|intval}', intval($array['HTML_CACHE_SHOW_TIME']));
        }
        if (isset($array['HTML_CACHE_SPECIAL_ON']) && $array['HTML_CACHE_SPECIAL_ON'] == 1) {
            $html_cache_rules['special:index'] = array('{:module}/Special/{:action}_{cid|intval}_{p|intval}', intval($array['HTML_CACHE_SPECIAL_TIME'])); //首页
            $html_cache_rules['special:shows'] = array('{:module}/Special/{:action}_{id|intval}', intval($array['HTML_CACHE_SPECIAL_TIME'])); //页面
        }
        $array['HTML_CACHE_RULES_COMMON'] = $html_cache_rules; //公共静态缓存规则

        //路由
        $tmp = explode("\n", str_replace(array("\r\n", "\t"), array("\n", ""), trim($array['HOME_URL_ROUTE_RULES'], "\r\n")));

        $url_route_rules = array();
        foreach ($tmp as $v) {
            $temparr = explode('=>', $v);
            if (empty($temparr[0]) || empty($temparr[1])) {
                continue;
            }
            $url_route_rules[$temparr[0]] = $temparr[1];
        }
        $array['HOME_URL_ROUTE_RULES'] = $url_route_rules; //公共静态缓存规则

        F($sname, $array);
    }

    $value = isset($array[$key]) ? $array[$key] : '';
    return $value;

}

/**
 * 返回配置项数组或对应值(快速缓存)
 * @param string|integer $key 标识名,标识为空则返回所有配置项数组
 * @param string|integer $name 缓存名称
 * @return mixed
 */
function get_cfg_value($key = '', $name = 'site')
{
    if (empty($name)) {
        return array();
    }
    $sname = 'config/' . $name;
    $array = F($sname);
    if (!$array) {
        $data  = M('config')->field('name,value,typeid')->select();
        $array = array();
        if ($data) {
            foreach ($data as $value) {
                $array[$value['name']] = $value['value'];
            }
        }
        F($sname, $array);
    }
    if ($key == '') {
        return $array;
    } else {
        $value = isset($array[$key]) ? $array[$key] : '';
        return $value;
    }

}

/**
 * 获取文件storage访问地址(SAE)
 * @param string $domain 域名名称
 * @param string $filename 文件名称(路径)
 * @return string
 */
function get_sae_storage_url($domain = 'uploads', $filename = '')
{
    if (empty($domain)) {
        return '';
    }

    static $_storage = array();
    $name            = $domain . ':' . $filename;
    if (isset($_storage[$name])) {
        return $_storage[$name];
    }
    $storage         = new \SaeStorage();
    $file_url        = $storage->getUrl($domain, $filename);
    $_storage[$name] = $filename;
    return $file_url;
}

/**
 * 返回从根目录开始的地址
 * @param string $path 子目录地址
 * @param boolean $domain 是否显示域名
 * @param string $path_root 网站根目录地址
 * @return mixed
 */
function get_url_path($path, $domain = false, $path_root = __ROOT__)
{

    $baseurl = ''; //域名地址
    if ($domain) {
        if (!empty($_SERVER['HTTP_HOST'])) {
            $baseurl = 'http://' . $_SERVER['HTTP_HOST'];
        } else {
            $baseurl = rtrim("http://" . $_SERVER['SERVER_NAME'], '/');
        }

    }

    $path_sub = explode('/', $path);

    if ($path_sub[0] == '') {
        $baseurl = $baseurl . implode('/', $path_sub);
    } elseif (empty($path_root)) {
        foreach ($path_sub as $k => $v) {
            if ($v == '.' || $v == '..') {
                unset($path_sub[$k]);
            }
        }
        $baseurl .= '/' . implode('/', $path_sub);
    } else {
        $path_root_tmp   = explode('/', $path_root);
        $path_root_count = count($path_root_tmp);
        foreach ($path_sub as $k => $v) {
            if ($v == '.') {
                unset($path_sub[$k]);
            }
            if ($v == '..') {
                if ($path_root_count > 0) {
                    unset($path_root_tmp[$path_root_count - 1]);
                    --$path_root_count;
                }
                unset($path_sub[$k]);
            }
        }
        $baseurl .= implode('/', $path_root_tmp) . '/' . implode('/', $path_sub);
    }
    $baseurl = rtrim($baseurl, '/') . '/';
    return $baseurl;
}

/**
 * 图片字符串 转图片数组
 */
function get_picture_array($str_pictureurls)
{
    $pictureurls_arr = empty($str_pictureurls) ? array() : explode('|||', $str_pictureurls);

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
    return $pictureurls;
}

/**
 * 检测验证码
 */
function check_verify($code, $id = 1)
{
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

/**goto mobile*/
function go_mobile()
{
    $mobileAuto = C('CFG_MOBILE_AUTO');
    if ($mobileAuto == 1) {
        $wap2web = I('wap2web', 0, 'intval'); //手机访问电脑版
        $agent   = $_SERVER['HTTP_USER_AGENT'];
        if ($wap2web != 1) {
            if (strpos($agent, "comFront") || strpos($agent, "iPhone") || strpos($agent, "MIDP-2.0") || strpos($agent, "Opera Mini") || strpos($agent, "UCWEB") || strpos($agent, "Android") || strpos($agent, "Windows Phone") || strpos($agent, "Windows CE") || strpos($agent, "SymbianOS")) {
                header('Location:' . U('Mobile/Index/index') . '');
            }
        }
    }

}

/**
 * 转换网址
 * @param string $weburl 网址或者U方法的参数
 * @param boolean $rnd 是否添加随机数
 * @param boolean $flag 是否转换index.php
 * @return string
 */
function go_link($weburl = 'http://ispCMS/', $rnd = 0, $flag = 1)
{
    if (strpos($weburl, 'http://') === 0 || strpos($weburl, 'https://') === 0 || strpos($weburl, 'ftp://') === 0) {
        $weburl = U(C('DEFAULT_MODULE') . '/Go/link', array('url' => base64_encode($weburl)));
    } else {
        $weburl = U($weburl);
    }
    if ($flag) {
        $search  = $_SERVER['SCRIPT_NAME']; //$_SERVER['PHP_SELF'];
        $replace = rtrim(dirname($search), "\\/") . '/index.php';
        $weburl  = str_replace($search, $replace, $weburl);
    }
    //随机数
    if ($rnd) {
        $weburl .= '#' . rand(1000, time());
    }

    return $weburl;
}

/**
 * D2是D方法的扩展20140919
 * D2函数用于实例化Model 格式 项目://分组/模块
 * @param string $name Model资源地址
 * @param string $tableName 数据表名
 * @param string $layer 业务层名称
 * @return Model
 */
function D2($name = '', $tableName = '', $layer = '')
{
    if (empty($name)) {
        return new \Think\Model;
    }

    static $_model = array();
    $layer         = $layer ?: C('DEFAULT_M_LAYER');
    if (isset($_model[$name . $layer . '\\' . $tableName])) {
        return $_model[$name . $layer . '\\' . $tableName];
    }

    $class = parse_res_name($name, $layer);
    if (class_exists($class)) {
        //$model      =   new $class(basename($name));
        $model = empty($tableName) ? new $class(basename($name)) : new $class(basename($tableName), $tableName);
    } elseif (false === strpos($name, '/')) {
        // 自动加载公共模块下面的模型
        if (!C('APP_USE_NAMESPACE')) {
            import('Common/' . $layer . '/' . $class);
        } else {
            $class = '\\Common\\' . $layer . '\\' . $name . $layer;
        }
        $model = class_exists($class) ? (empty($tableName) ? new $class(basename($name)) : new $class(basename($tableName), $tableName)) : new Think\Model($name);
    } else {
        Think\Log::record('D方法实例化没找到模型类' . $class, Think\Log::NOTICE);
        $model = new \Think\Model(basename($name));
    }
    $_model[$name . $layer . '\\' . $tableName] = $model;
    return $model;
}

/**
 * 提示信息
 * @param string $msg 提示内容
 * @param string $title 标题
 * @return void
 */
function exit_msg($msg = '', $title = '提示')
{
    $msg = nl2br($msg);
    $str = <<<str
<!DOCTYPE html><html><head><meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no'/>
<title>{$title}</title>
<style type="text/css">
body{background:#fff;font-family: 'Microsoft YaHei'; color: #333;}
.info{width:90%;font-size:100%; line-height:150%; margin:20px auto; padding:10px;border:solid 1px #ccc;}
</style>
</head>
<body>
<div class="info">{$msg}</div>
</body>
</html>
str;
    echo $str;
    exit();
}

/**
 *发送邮件
 * @param    string   $address       地址
 * @param    string    $title 标题
 * @param    string    $message 邮件内容
 * @param    string $attachment 附件列表
 * @return   boolean
 */
function send_mail($address, $title, $message, $attachment = null)
{
    Vendor('PHPMailer.class#phpmailer');

    $mail = new PHPMailer;
    //$mail->Priority = 3;
    // 设置PHPMailer使用SMTP服务器发送Email
    $mail->IsSMTP();
    // 设置邮件的字符编码，若不指定，则为'UTF-8'
    $mail->CharSet   = 'UTF-8';
    $mail->SMTPDebug = 0; // 关闭SMTP调试功能
    $mail->SMTPAuth  = true; // 启用 SMTP 验证功能
    // $mail->SMTPSecure = 'ssl';  // 使用安全协议
    $mail->IsHTML(true); //body is html

    // 设置SMTP服务器。
    $mail->Host = C('CFG_EMAIL_HOST');
    $mail->Port = C('CFG_EMAIL_PORT') ? C('CFG_EMAIL_PORT') : 25; // SMTP服务器的端口号

    // 设置用户名和密码。
    $mail->Username = C('CFG_EMAIL_LOGINNAME');
    $mail->Password = C('CFG_EMAIL_PASSWORD');

    // 设置邮件头的From字段
    $mail->From = C('CFG_EMAIL_FROM');
    // 设置发件人名字
    $mail->FromName = C('CFG_EMAIL_FROM_NAME');

    // 设置邮件标题
    $mail->Subject = $title;
    // 添加收件人地址，可以多次使用来添加多个收件人
    $mail->AddAddress($address);
    // 设置邮件正文
    $mail->Body = $message;
    // 添加附件
    if (is_array($attachment)) {
        foreach ($attachment as $file) {
            is_file($file) && $mail->AddAttachment($file);
        }
    }

    // 发送邮件。
    //return($mail->Send());
    return $mail->Send() ? true : $mail->ErrorInfo;
}

function check_date($str, $format = "Y-m-d")
{
    $unixTime_1 = strtotime($str); //strtotime 成功则返回时间戳，否则返回 FALSE。在 PHP 5.1.0 之前本函数在失败时返回 -1
    if (!is_numeric($unixTime_1) || $unixTime_1 == -1) {
        return false;
    }

    $checkDate  = date($format, $unixTime_1);
    $unixTime_2 = strtotime($checkDate);
    if ($unixTime_1 == $unixTime_2) {
        return true;
    } else {
        return false;
    }
}

/**
 *将字符串转换为数组
 *@param string $data 字符串
 */
function string2array($data)
{
    if ($data == '') {
        return array();
    }

    @eval("\$array = $data;");
    return $array;
}

/**
 *将数组转换为字符串
 *@param    array   $data       数组
 *@param    bool    $isformdata 如果为0，则不使用new_stripslashes处理，可选参数，默
 */
function array2string($data, $isformdata = 1)
{
    if ($data == '') {
        return '';
    }

    if ($isformdata) {
        $data = new_stripslashes($data);
    }

    return addslashes(var_export($data, true));
}

function new_stripslashes($string)
{
    if (!is_array($string)) {
        return stripslashes($string);
    }

    foreach ($string as $key => $val) {
        $string[$key] = new_stripslashes($val);
    }

    return $string;

}

/**
 * 请求接口返回内容
 * @param  string $url [请求的URL地址]
 * @param  string $params [请求的参数]
 * @param  int $ipost [是否采用POST形式]
 * @return  string
 */
function ispoacurl($url, $params=false, $ispost=false){
	$_headers = array();
	$httpInfo = array();	
	$ch = curl_init();
	$timestamp = time();
	$headers = [
		"ispoa-apikey" 		=> C('ISPOA_APIKEY'),
		"ispoa-timestamp" 	=> $timestamp,
		"ispoa-sign" 		=> md5(C('ISPOA_APIKEY') . $timestamp . C('ISPOA_SECRETKEY')),
	];
	if (!is_null($headers) && is_array($headers)){
		foreach($headers as $k => $v) {
			array_push($_headers, "{$k}: {$v}");
		}
	}
	curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
	curl_setopt( $ch, CURLOPT_USERAGENT , 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22' );
	curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 30 );
	curl_setopt( $ch, CURLOPT_TIMEOUT , 30);
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $_headers);
	//curl_setopt( $ch, CURLOPT_PROXY,'127.0.0.1:8888');	//设置代理服务器
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER,0);		//若PHP编译时不带openssl则需要此行
	if( $ispost ) {
		curl_setopt( $ch , CURLOPT_POST , true );
		curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
		curl_setopt( $ch , CURLOPT_URL , $url );
	}
	else
	{
		if($params){
			curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
		}else{
			curl_setopt( $ch , CURLOPT_URL , $url);
		}
	}
	$response = curl_exec( $ch );
	if ($response === FALSE) {
		//echo "cURL Error: " . curl_error($ch);
		return false;
	}
	$httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
	$httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
	
	curl_close( $ch );
	//var_dump($httpInfo);
	
	return $response;
}


/**
 * 检测是否使用手机访问
 * @return bool
 */
function isMobile()
{
	if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")) {
		return true;
	} elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtoupper($_SERVER['HTTP_ACCEPT']), "VND.WAP.WML")) {
		return true;
	} elseif (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
		return true;
	} elseif (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipad|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])) {
		return true;
	} else {
		return false;
	}
}
/**
 * 获取设备类型，ios，android或者其他
 * @return  string
 */
function get_device_type(){  
	$agent = strtolower($_SERVER['HTTP_USER_AGENT']);  
	$type = 'other';  
	if(strpos($agent, 'iphone') || strpos($agent, 'ipad') || strpos($agent, 'ipod') ){  
		$type = 'ios';  
	}  
	if(strpos($agent, 'android')){  
		$type = 'android';  
	}  
	return $type;  
}

/**
 * 改变文件名中的后缀名
 * @param  string $filename [需要转换的文件名]
 * @param  string $toExt [转换的目的后缀名]
 * @return  string
 */
function changeFileExt($filename, $toExt = 'mp4')
{ 
	$pos = strripos($filename,'.');					//获取到文件名的位置
	$ext = substr($filename, $pos+1);				//获取后缀格式
	if (!empty($ext) && strtolower($ext)!=$toExt) 
	{
		$filename = substr($filename,0,$pos);		//获取文件名
		$filename .= '.' . $toExt;
		return $filename;
	}
	return $filename;
} 

/**
 * 检测用户是否是学生
 */
function is_student()
{
    if (empty(session('std_tag'))) {
        return false;
    }

    return intval(session('std_tag')) == 1;
}