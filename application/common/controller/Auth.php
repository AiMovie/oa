<?php
// +----------------------------------------------------------------------
// | 功能：基础后台
// +----------------------------------------------------------------------
// | 作者：程龙飞
// +----------------------------------------------------------------------
// | 日期：2017-5-2
// +----------------------------------------------------------------------
namespace app\common\controller;

use app\common\controller\Common;
use think\Db;
use think\Request;
class Auth extends Common
{
	//网站配置
	public $options_value;

	//初始化
	protected function _initialize(){
        parent::_initialize();
       	//未登陆，不允许直接访问
		if(empty(session('aid'))){
			$this->error('还没有登录，正在跳转到登录页',url('Admin/Login/login'));
		}

		//系统参数
		$sysconfig = Db::name('sysconfig')->select();
		foreach($sysconfig as $v){
			$sconfig[$v['varname']] = $v['value'];
		}
		session('sysconfig',$sconfig);

		//已登录，不需要验证的权限
		$not_check = array('Admin/Sys/clear','Admin/Index/index');//不需要检测的控制器/方法

		//当前操作的请求                 模块名/方法名
		//不在不需要检测的控制器/方法时,检测
		$module_name = Request::instance()->module();
		$controller_name = Request::instance()->controller();
		$action_name = Request::instance()->action();

		if(!in_array($module_name.'/'.$controller_name.'/'.$action_name, $not_check)){
			$auth = new  \think\Auth();
			if(!$auth->check($action_name.'/'.$controller_name.'/'.$action_name,session('aid')) && session('aid')!= 1){
				$this->error('没有权限',0,0);
			}
		}
		//获取有权限的菜单tree
		$menus= \think\Cache::get('menus_admin_'.session('aid'));
		if(empty($menus)){
			$auth = new \think\Auth();
			$data = Db::name('auth_rule')->where(array('status'=>1))->order('sort')->select();
			foreach ($data as $k=>$v){
				if(!$auth->check($v['name'], session('aid')) && session('aid') != 1){
					unset($data[$k]);
				}
			}
			$menus=node_merge($data);
			\think\Cache::get('menus_admin_'.session('aid'),$menus,0);
		}
		$this->assign('menus',$menus);
		//当前方法倒推到顶级菜单数组
		$menus_curr = get_menus_admin();
		//如果$menus_curr为空,则根据'模块/控制器/方法'取status=0的menu
			// echo '<pre>';print_r($menus);exit;
		if(empty($menus_curr)){
			$rst = Db::name('auth_rule')->where(array('status'=>0,'name'=> $module_name .'/'. $controller_name .'/'. $action_name))->order('level desc,sort')->limit(1)->select();
			if($rst){
				$pid=$rst[0]['pid'];
				//再取父级
				$rst = Db::name('auth_rule')->where(array('id'=>$pid))->find();
				$menus_curr=get_menus_admin($rst['name']);
			}
		}
		$this->assign('menus_curr',$menus_curr);
		//取当前操作菜单父ID
		if(count($menus_curr)>=4){
			$pid=$menus_curr[1];
			$id_curr=$menus_curr[2];
		}elseif(count($menus_curr)>=2){
			$pid=$menus_curr[count($menus_curr)-2];
			$id_curr=end($menus_curr);
		}else{
			$pid='0';
			$id_curr=(count($menus_curr)>0)?end($menus_curr):'';
		}



		//取$pid下子菜单
		$menus_child = Db::name('auth_rule')->where(array('status'=>1,'pid'=>$pid))->order('sort')->select();

		$this->assign('menus_child',$menus_child);
		$this->assign('id_curr',$id_curr);


		//网站配置信息
        $this->options_value = \think\Cache::get('options_value');

        if(!$this->options_value)
        {
            //实例化配置信息表yf_options
            $options_db = Db::name("options");
            //查询配置信息
            $options_info = $options_db ->where("option_name='site_options'")->find();
           
            //将存储的json格式数据转换成数组，然后传递到视图
            $options_value = json_decode($options_info['option_value'],true);
            $this->options_value = $options_value;
            //缓存数据，时间24小时
            \think\Cache::set('options_value',$this->options_value,86400);
        }
        
        $this->assign('options_value',$this->options_value);
        session('admin_avatar',Db::name('admin')->where('admin_id',session('aid'))->value('admin_avatar'));
        $this->assign('admininfo',session(''));
    }
    
}
