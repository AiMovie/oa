<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Db;
use think\Validate;
//加密
function encrypt($data)
{
        
    $key    =   md5("esmi");
    $str = base64_encode($data);
    $res =  base64_encode($str.$key);
    return $res;
}

//解密
function decrypt($data)
{
    $key = md5("esmi");

    $data = base64_decode($data);
    
    $length = strlen($data);
    
    
    $sstr = substr($data,0,-32);
    $bkey = str_replace($sstr,'',$data);
    
    if($key != $bkey)
    {
        return false;
    }
    
    $str = base64_decode($sstr);
    return $str;   
}

/**
 * 发送短信验证码
 * @param $telphone
 * @return string
 */
function sendcode($telphone){
    $verification_code=rand(100000,999999);
    $content='【宝宝机械】您的验证码为'.$verification_code.'，请在5分钟内完成验证，若非本人操作请忽略此消息。';
    $url='https://api.smsbao.com/sms';
    $param['u']='elongtian';
    $param['p']=md5('elt20170721');
    $param['m']=$telphone;
    $param['c']=$content;
    $res=http_post($url,$param);
    if($res==0){
        $data=array(
            'telphone' => $telphone,
            'type'=>'register',
            'content'=>$content,
            'verification_code' =>$verification_code,
            'addtime'=>time()
        );
        Db::name('sms_log')->insert($data);
    }
    return $res;
}

/**
 * 将某条数据记录到日志里调试
 * @param $data
 */
function t($data){
    file_put_contents('111.log', $data. PHP_EOL, FILE_APPEND);
}

/**
 * 获取模块是否开启信息审核
 * @param $name
 * @return int
 */
function getcheck($name){
    $sys = Db::name('options')->where(array('option_name' => $name))->value("option_value");
    $sys = unserialize($sys);
    $check_open = ($sys['check_open'] == 1) ? $sys['check_open'] : 2;
    return $check_open;
}


// 应用公共文件
function p($params)
{
    echo '<pre>';
    print_r($params);
    echo "</pre>";
    exit;
}
// 防超时的file_get_contents改造函数
function wp_file_get_contents($url) {
	$context = stream_context_create ( array (
			'http' => array (
					'timeout' => 30 
			) 
	) ); // 超时时间，单位为秒
	
	return file_get_contents ( $url, 0, $context );
}


function addWeixinLog($data, $data_post = '', $wechat = false) {
    $log ['cTime'] = time();
    $log ['cTime_format'] = date ( 'Y-m-d H:i:s', $log ['cTime'] );
    $log ['data'] = is_array ( $data ) ? serialize ( $data ) : $data;
    $log ['data_post'] = is_array ( $data_post ) ? serialize ( $data_post ) : $data_post;

    \think\Db::name('weixin_log')->insert($log);
}


function get_root($domain = false)
{
    $str = dirname(request()->baseFile());
    $str = ($str == DS) ? '' : $str;
    if ($domain) {
        return request()->domain() . $str;
    } else {
        return;
    }
}

/**
 * 所有用到密码的不可逆加密方式
 * @author rainfer <81818832@qq.com>
 *
 * @param string $password
 * @param string $password_salt
 * @return string
 */
function encrypt_password($password, $password_salt)
{
    return md5(md5($password) . md5($password_salt));
}

/**
 * 递归重组节点信息为多维数组
 *
 * @param array $node
 * @param number $pid
 * @author rainfer <81818832@qq.com>
 */
function node_merge(&$node, $pid = 0, $id_name = 'id', $pid_name = 'pid', $child_name = '_child')
{
    $arr = array();

    foreach ($node as $v) {
        if ($v[$pid_name] == $pid){
            $v[$child_name] = node_merge($node, $v[$id_name], $id_name, $pid_name, $child_name);
            $arr[] = $v;
        }
    }

    return $arr;
}

/**
 * 倒推后台菜单数组
 * $str String '方法名'或'控制器名/方法名'，为空则为'当前控制器/当前方法'
 * $status int 获取的menu是否含全部状态，还是仅status=1。不为0和1时,不限制
 * $arr boolean 是否返回全部数据数组，默认假，仅返回ids
 * @author rainfer <81818832@qq.com>
 */
 function get_menus_admin($str='',$status=1,$arr=false){
    $module_name = \think\Request::instance()->module();
    $controller_name = \think\Request::instance()->controller();
    $action_name = \think\Request::instance()->action();

    $str=empty($str)? $module_name .'/'. $controller_name .'/'. $action_name :$str;
    //if(strpos($str,'/')===false){
    if(!substr_count($str,'/')){
        $str.= $module_name;
    }elseif(substr_count($str,'/')==1){
        $str.= $controller_name;
    }
    $status=empty($status)?1:$status;
    $arr=empty($arr)?false:true;
    $where['name']=$str;
    if($status==0 || $status==1){
        $where['status']=$status;
    }
    $arr_rst=array();
    $rst = \think\Db::name('auth_rule')->where($where)->order('level desc,sort')->limit(1)->select();
    
    if($rst){
        $rst=$rst[0];
        if($arr){
            $arr_rst[]=$rst;
        }else{
            $arr_rst[]=$rst['id'];
        }
        $pid=$rst['pid'];
        while(intval($pid)!=0) {
            //非顶级
            $rst = \think\Db::name('auth_rule')->where(array('id'=>$pid))->find();
            
            if($arr){
                $arr_rst[]=$rst;
            }else{
                $arr_rst[]=$rst['id'];
            }
            $pid=$rst['pid'];   
        } 
    }
    //return $arr_rst;
    return array_reverse($arr_rst);
    
}

/**
 * 清除缓存
 * @author rainfer <81818832@qq.com>
 */
function clear_cache(){
    remove_dir(TEMP_PATH);
    remove_dir(CACHE_PATH);
    file_exists($file = RUNTIME_PATH . 'common~runtime.php') && @unlink($file);

    // \Think\Cache::clear();
}

/**
 * 删除文件夹
 * @author rainfer <81818832@qq.com>
 *
 */
function remove_dir($dir, $time_thres = -1)
{
    foreach (list_file($dir) as $f) {
        if ($f ['isDir']) {
            remove_dir($f ['pathname'] . '/');
        } else if ($f ['isFile'] && $f ['filename'] != 'index.html') {
            if ($time_thres == -1 || $f ['mtime'] < $time_thres) {
                @unlink($f ['pathname']);
            }
        }
    }
}

/**
 * 列出本地目录的文件
 * @author rainfer <81818832@qq.com>
 *
 * @param string $filename
 * @param string $pattern
 * @return Array
 */
function list_file($filename, $pattern = '*')
{
    if (strpos($pattern, '|') !== false) {
        $patterns = explode('|', $pattern);
    } else {
        $patterns [0] = $pattern;
    }
    $i = 0;
    $dir = array();
    if (is_dir($filename)) {
        $filename = rtrim($filename, '/') . '/';
    }
    foreach ($patterns as $pattern) {
        $list = glob($filename . $pattern);
        if ($list !== false) {
            foreach ($list as $file) {
                $dir [$i] ['filename'] = basename($file);
                $dir [$i] ['path'] = dirname($file);
                $dir [$i] ['pathname'] = realpath($file);
                $dir [$i] ['owner'] = fileowner($file);
                $dir [$i] ['perms'] = substr(base_convert(fileperms($file), 10, 8), -4);
                $dir [$i] ['atime'] = fileatime($file);
                $dir [$i] ['ctime'] = filectime($file);
                $dir [$i] ['mtime'] = filemtime($file);
                $dir [$i] ['size'] = filesize($file);
                $dir [$i] ['type'] = filetype($file);
                $dir [$i] ['ext'] = is_file($file) ? strtolower(substr(strrchr(basename($file), '.'), 1)) : '';
                $dir [$i] ['isDir'] = is_dir($file);
                $dir [$i] ['isFile'] = is_file($file);
                $dir [$i] ['isLink'] = is_link($file);
                $dir [$i] ['isReadable'] = is_readable($file);
                $dir [$i] ['isWritable'] = is_writable($file);
                $i++;
            }
        }
    }
    $cmp_func = create_function('$a,$b', '
        if( ($a["isDir"] && $b["isDir"]) || (!$a["isDir"] && !$b["isDir"]) ){
            return  $a["filename"]>$b["filename"]?1:-1;
        }else{
            if($a["isDir"]){
                return -1;
            }else if($b["isDir"]){
                return 1;
            }
            if($a["filename"]  ==  $b["filename"])  return  0;
            return  $a["filename"]>$b["filename"]?-1:1;
        }
        ');
    usort($dir, $cmp_func);
    return $dir;
}


/**
 * GET请求
 * @param $url
 * @return bool|mixed
 */
function http_get($url)
{
    $oCurl = curl_init ();
    if (stripos ( $url, "https://" ) !== FALSE) {
        curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, FALSE );
    }
    curl_setopt ( $oCurl, CURLOPT_URL, $url );
    curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
    $sContent = curl_exec ( $oCurl );
    $aStatus = curl_getinfo ( $oCurl );
    curl_close ( $oCurl );
    if (intval ( $aStatus ["http_code"] ) == 200) {
        return $sContent;
    } else {
        return false;
    }
}

/**
 * POST 请求
 *
 * @param string $url           
 * @param array $param          
 * @return string content
 */
function http_post($url, $param) {
    $oCurl = curl_init ();
    if (stripos ( $url, "https://" ) !== FALSE) {
        curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, false );
    }
    if (is_string ( $param )) {
        $strPOST = $param;
    } else {
        $aPOST = array ();
        foreach ( $param as $key => $val ) {
            $aPOST [] = $key . "=" . urlencode ( $val );
        }
        $strPOST = join ( "&", $aPOST );
    }
    curl_setopt ( $oCurl, CURLOPT_URL, $url );
    curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt ( $oCurl, CURLOPT_POST, true );
    curl_setopt ( $oCurl, CURLOPT_POSTFIELDS, $strPOST );
    $sContent = curl_exec ( $oCurl );
    $aStatus = curl_getinfo ( $oCurl );
    curl_close ( $oCurl );
    if (intval ( $aStatus ["http_code"] ) == 200) {
        return $sContent;
    } else {
        return false;
    }
}

/**
 * 获取微信公众号的access_token
 *
 * @param string $appid           
 * @param string $appsecret          
 * @return string access_token
 */
function get_access_token($appid= '',$appsecret = '')
{

    if($appid == '' || $appsecret == '')
    {
        return '';
    }

    $access_token = \think\Cache::get($appid);
    if(!$access_token['access_token'])
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret;
        $access_token = http_get($url);
        $access_token = json_decode($access_token,true);
        \think\Cache::set($appid,$access_token,7000);
        return $access_token['access_token'];
    }
    else
    {
        return $access_token['access_token'];
    }
}

/**
 * 递归分类
 *
 * @param array $node
 * @param number $pid
 */
function node_cat($arr, $pid = 0,$lv =0,$html='├')
{
     $arr1 =array();
    foreach($arr as $v){
        if($v['parent_id'] == $pid){
            $v['leftpin'] =$lv;
            $v['lefthtml'] =$html;
            $lvz=$lv+20;
            $arr1[] = $v;
            $htmlz= $html.'─';
            $arr1 =array_merge($arr1, node_cat($arr, $v['id'],$lvz, $htmlz));
        }
       
    }
    return $arr1;
}
/**
 * 递归产品分类删除
 *
 * @param array $node
 * @param number $pid
 */
function node_cat_id($arr, $pid)
{
    $arr1 =array();
    foreach ($arr as $v){
        if ($v['parent_id'] == $pid){
            $arr1[] = $v['cat_id'];
            $arr1 =array_merge($arr1, node_cat_id($arr, $v['cat_id']));
        }
       
    }
    return $arr1;
}
/*
    微信菜单列表
*/
function wechat_category()
{
    $wechat_menu_db = \think\Db::name('wechat_menu');

    $list = $wechat_menu_db->field('id,name,parent_id')->where("parent_id=0")->select();

    foreach ($list as $key => $value) {
        $children = $wechat_menu_db->field('parent_id,name,id')->where('parent_id='.$value['id'])->select();
        $list[$key]['children'] = $children;

    }
    return $list;
}

//截取字符
function subtext($text, $length)
{
    if(mb_strlen($text, 'utf8') > $length)
        return mb_substr($text, 0, $length, 'utf8').'...';
    return $text;
}

/**
 * 将内容存到Storage中，返回转存后的文件路径
 * @author rainfer <81818832@qq.com>
 *
 * @param string $dir
 * @param string $ext
 * @param string $content
 * @return string
 */
function save_storage_content($ext = null, $content = null, $filename = '')
{
    $newfile = '';
    $path= substr(\think\Config::get('UPLOAD_DIR'),1);
    // $path=substr($path,0,2)=='./' ? substr($path,2) :$path;
    // $path=substr($path,0,1)=='/' ? substr($path,1) :$path;
    if ($ext && $content) {
        do {
            $newfile = $path.date('Y-m-d').'\\' . uniqid() . '.' . $ext;
        } while (\storage\Storage::has($newfile));

        \storage\Storage::put($newfile, $content);
    }
    return $newfile;
}
/**
 * @param string $tourism_address 省id,城市id，区县id，详细地址
 * @return string 省名称，市名称，县名称，详细地址
 */
function get_address($tourism_address)
{
    $address = explode(',', $tourism_address);

    $region = \think\Db::name('region');
    $province = $region->where('id',$address[0])->value('name');
    $city = $region->where('id',$address[1])->value('name');
    $county = $region->where('id',$address[2])->value('name');
    return $province.' '.$city.' '.$county.' '.$address[3];
}

function twoTime($time){
    $arr = explode(" - ",$time);//转换成数组
    if(count($arr)==2){
        $arrdateone=strtotime($arr[0]);
        $arrdatetwo=strtotime($arr[1].' 23:55:55');
        return array($arrdateone,$arrdatetwo);
    }else{
        return false;
    }
}

function qq($data){
	echo '<pre>';
	print_r($data);
	echo '</pre>';
}
function ee($data){
	echo '<pre>';
	print_r($data);
	echo '</pre>';
	die;
}
function q($data){
   echo '<pre>';
   echo '

.
.
.
.
.
.
.

   ';
    print_r($data);
    echo '</pre>';
}
?>