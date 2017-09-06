<?php
// +----------------------------------------------------------------------
// | 功能：登陆退出后台
// +----------------------------------------------------------------------
// | 作者：程龙飞
// +----------------------------------------------------------------------
// | 日期：2017-5-2
// +----------------------------------------------------------------------
namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;
class Login extends Controller {
	//登入页面
	public function login(){
		//已登录,跳转到首页
		if(session('aid')){
			$this->redirect('Admin/Index/index');
		}

		return $this->fetch();
	}

	//登陆验证
	public function runlogin(){

		if (!request()->isAjax()){
			$this->error("提交方式错误！",url('Admin/Login/login'),0);
		}else{
			$admin_username = input('admin_username');
			$password = input('admin_pwd');

            // 验证验证码
            $verify = new \org\Verify([]);
            if (!$verify->check(input('verify'), 1)) {
                // $this->error('验证码错误！', url('Admin/Login/login'), 0);
            }


			$admin = DB::name('admin')->where(array('admin_username'=>$admin_username))->find();
 
			if (!$admin||encrypt_password($password,$admin['admin_pwd_salt'])!==$admin['admin_pwd']){
				$this->error('用户名或者密码错误，重新输入',url('Login/login'),0);
			}elseif($admin['admin_open'] != 1){
				$this->error('该账号已禁用!',url('Login/login'),0);
			}else{
				//检查是否弱密码
				session('admin_weak_pwd', false);
				$weak_pwd_reg = array(
					'/^[0-9]{0,6}$/',
					'/^[a-z]{0,6}$/',
					'/^[A-Z]{0,6}$/'
				);
				foreach ($weak_pwd_reg as $reg) {
					if (preg_match($reg, $password)) {
						session('admin_weak_pwd', true);
						break;
					}
				}
				//登录后更新数据库，登录IP，登录次数,登录时间
				$data=array(
                    'admin_last_ip'=>$admin['admin_ip'],
                    'admin_last_time'=>$admin['admin_time'],
					'admin_ip'=>request()->ip(),
                    'admin_time'=>time(),
				);

				DB::name('admin')->where(array('admin_username'=>$admin_username))->setInc('admin_hits',1);
				DB::name('admin')->where(array('admin_username'=>$admin_username))->update($data);
				
				session('aid',$admin['admin_id']);
				session('admin_username',$admin['admin_username']);
				session('admin_realname',$admin['admin_realname']);
				session('admin_avatar',$admin['admin_avatar']);
				session('admin_last_change_pwd_time', $admin ['admin_changepwd']);
				$this->success('恭喜您，登陆成功',url('Admin/Index/index'),1);
			}
		}
	}
	//验证码
	public function verify()
    {
        if (session('aid')) {
            header('Location: ' . U('Admin/Index/index'));
            return;
        }
        $id = input('id', 1, 'intval');
        $verify = new \org\Verify([]);
        $verify->entry($id);
    }
	/*
     * 退出登录
     */
	public function logout(){
		session('aid',null);
		session('admin_username',null);
		session('admin_realname',null);
		session('admin_avatar',null);
		session('admin_last_change_pwd_time', null);
		$this->redirect('Admin/Login/login');
	}
}