<?php
// +----------------------------------------------------------------------
// | 功能：基础后台
// +----------------------------------------------------------------------
// | 作者：程龙飞
// +----------------------------------------------------------------------
// | 日期：2017-5-2
// +----------------------------------------------------------------------
namespace app\common\controller;

use think\Controller;

class Common extends Controller
{
	//初始化
	protected function _initialize(){
        parent::_initialize();
 
	}
     /**
     * 创建一个JSON格式的正确信息
     *
     * @access  protected
     * @param   string  $msg
     * @return  void
     */
    protected function make_json_result($message, $data=array())
    {   

    	$result = array(
    			'code' => 0,
    			'msg' => $message,
                'data' => $data,
    		);
    	$str =  json_encode($result);
        $str = str_replace('\\\\', '/', $str);
        echo $str;
        exit;
    }

    /**
     * 创建一个JSON格式的错误信息
     *
     * @access  protected
     * @param   string  $msg
     * @return  void
     */
    protected function make_json_error($message = '', $code, $data=array())
    {
    	$result = array(
    			'code' => $code,
    			'msg' => $message,
    			'data' => $data,
    		);
    	$str =  json_encode($result);
        $str = str_replace('null', '""', $str);
        $str = str_replace('\\\\', '/', $str);

        echo $str;
        exit;
    }

     /**
     * 读取网站配置信息
     * @access  protected
     * @param   string  $option_name
     * @return  array
     */
    public function get_options_value($option_name= '')
    {
        if($option_name === '')
        {
            return array();
        }

        $cache_option_value = \think\Cache::get($option_name);
        if(!$cache_option_value)
        {
            $option_value = Db::name('options')->where('option_name',$option_name)->value('option_value');

            $json_result = json_decode($option_value,true);
            //json格式数据
            if($json_result)
            {
                $cache_option_value = $json_result;
            }
            else//序列化
            {
                $cache_option_value = unserialize($option_value);
            }
            
            \think\Cache::set($option_name,$cache_option_value,0);
        }

        return $cache_option_value;
    }

    
}

