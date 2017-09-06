<?php
// +----------------------------------------------------------------------
// | 功能：首页后台
// +----------------------------------------------------------------------
// | 作者：程龙飞
// +----------------------------------------------------------------------
// | 日期：2017-5-2
// +----------------------------------------------------------------------
namespace app\admin\Controller;

use app\common\controller\Auth;
use think\Session;
use think\Db;
class Index extends Auth
{
	//默认页面
	public function index(){


		//未登录
		if (empty(session('aid'))){
			$this->redirect('admin/login/login');
		}
		//系统信息
		$info = array(
			'PCTYPE'=>PHP_OS,
			'RUNTYPE'=>$_SERVER["SERVER_SOFTWARE"],
			'ONLOAD'=>ini_get('upload_max_filesize'),
			'ThinkPHPTYE'=>THINK_VERSION,
		);
		$this->assign('info',$info);


		$member_list_db = Db::name('member_list');
		//总会员数
        $members_count = $member_list_db->count();
        $this->assign('members_count',$members_count);

		//今日增加会员
        $tomembers_count = $member_list_db->where(array('member_list_addtime'=>array('egt',$today)))->count();
        $this->assign('tomembers_count',$tomembers_count);
        //昨日会员数
        $ztmembers_count = $member_list_db->where(array('member_list_addtime'=>array('between',array($ztday,$today))))->count();
        $this->assign('ztmembers_count',$ztmembers_count);
        $difday_m = ($ztmembers_count>0)?($tomembers_count-$ztmembers_count)/$ztmembers_count*100:0;
        $this->assign('difday_m',$difday_m);
     	
		//安全检测
		$this->system_safe = true;

        $this->danger_mode_debug = true;
        if ($this->danger_mode_debug) {
            $this->system_safe = false;
        }



        $this->weak_setting_admin_password = session('admin_weak_pwd');
        if ($this->weak_setting_admin_password) {
            $this->system_safe = false;
        }
        $this->weak_setting_admin_last_change_password = (session('admin_last_change_pwd_time') < time() - 3600 * 24 * 30);
        if ($this->weak_setting_admin_last_change_password) {
            $this->system_safe = false;
        }

        $this->assign('system_safe',$this->system_safe);
        $this->assign('weak_setting_admin_password',$this->weak_setting_admin_password);
        $this->assign('danger_mode_debug',$this->danger_mode_debug);
		//渲染模板
		return $this->fetch();
	}
}