<?php
/**
 * 功能：角色管理
 * 创建人：张赛
 * 创建时间：2016.6.28
 */
namespace  app\rule\controller;

use app\common\controller\Auth;
use think\Session;
use think\Db;
use org\Stringnew;
class Rule extends Auth{
	//管理员列表
	public function admin_list(){
		$admin = Db::name('admin');
		$val = input('val');
		$auth = new \think\Auth();
		$this->assign('testval',$val);
		$map=array();
		if($val){
			$map['admin_username']= array('like',"%".$val."%");
		}
		
		$admin_list = $admin->where($map)->order('admin_id')->paginate(15);
		$show = $admin_list->render();// 分页显示输出
		$items = $admin_list->items();
		// echo '<pre>';print_r($items);exit();
	
		foreach ($items as $k=>$v){
			$group = $auth->getGroups($v['admin_id']);
			$items[$k]['group'] = $group[0]['title'];
		}
		$this->assign('admin_list',$items);
		$this->assign('page',$show);
		return $this->fetch();
	}
	
	public function admin_add(){
		
		$auth_group = Db::name('auth_group')->select();
		$this->assign('auth_group',$auth_group);
		return $this->fetch();
	}
	//添加管理员
	public function admin_runadd(){
		$admin = Db::name('admin');
		$check_user = $admin->where(array('admin_username'=>input('admin_username')))->find();
		if ($check_user){
			$this->error('用户已存在，请重新输入用户名',url('admin_list'),0);
		}
		$admin_pwd_salt = Stringnew::randString(10);
		$sldata=array(
				'admin_username'=>input('admin_username'),
				'admin_pwd_salt' => $admin_pwd_salt,
				'admin_pwd'=>encrypt_password(input('admin_pwd'),$admin_pwd_salt),
				'admin_email'=>input('admin_email'),
				'admin_tel'=>input('admin_tel'),
				'admin_open'=>input('admin_open'),
				'admin_realname'=>input('admin_realname'),
				'admin_ip'=>request()->ip(),
				'admin_addtime'=>time(),
				'admin_changepwd'=>time(),
		);

		$result = $admin->insert($sldata);
		$accdata = array(
					'uid'=>$admin->getLastInsID(),
					'group_id'=>input('group_id'),
				);
		$admin_access = Db::name('auth_group_access');
		$admin_access->insert($accdata);
		$this->success('管理员添加成功',url('admin_list'),1);
	}
	//编辑管理员试图
	public function admin_edit(){
		$auth_group = Db::name('auth_group')->select();
		$admin_list = Db::name('admin')->where(array('admin_id'=>input('admin_id')))->find();
		$auth_group_access = Db::name('auth_group_access')->where(array('uid'=>$admin_list['admin_id']))->value('group_id');
		$this->assign('admin_list',$admin_list);
		$this->assign('auth_group',$auth_group);
		$this->assign('auth_group_access',$auth_group_access);
		return $this->fetch();
	}
	//编辑管理员操作
	public function admin_runedit(){
		$admin_list = Db::name('admin');
		$admin_pwd = input('admin_pwd');
		$group_id = input('group_id');
		$admindata['admin_id'] = input('admin_id');
		if ($admin_pwd){
			$admin_pwd_salt = Stringnew::randString(10);
			$admindata['admin_pwd_salt']=$admin_pwd_salt;
			$admindata['admin_pwd']=encrypt_password(input('admin_pwd'),$admin_pwd_salt);
			$admindata['admin_changepwd']=time();
		}
		$admindata['admin_email'] = input('admin_email');
		$admindata['admin_tel'] = input('admin_tel');
		$admindata['admin_realname'] = input('admin_realname');
		$admindata['admin_open'] = input('admin_open',0,'intval');
		$admin_list->update($admindata);
		if($group_id){
			$rst = Db::name('auth_group_access')->where(array('uid'=>input('admin_id')))->find();
			if($rst){
				//修改
				$rst = Db::name('auth_group_access')->where(array('uid'=>input('admin_id')))->update(array('group_id'=>$group_id));
			}else{
				//增加
				$data['uid'] = input('admin_id');
				$data['group_id']=$group_id;
				$rst = Db::name('auth_group_access')->add($data);
			}
		}
		if($rst!==false){
			$this->success('管理员修改成功',url('admin_list'),1);
		}else{
			$this->error('管理员修改失败',url('admin_list'),0);
		}
	}
	//删除管理员
	public function admin_del(){
		$admin_id = input('admin_id');
		if (empty($admin_id)){
			$this->error('用户ID不存在',url('admin_list'),0);
		}
		Db::name('admin')->where(array('admin_id'=>input('admin_id')))->delete();
		$rst = Db::name('auth_group_access')->where(array('uid'=>input('admin_id')))->delete();
		if($rst!==false){
			$this->success('管理员删除成功',url('admin_list'),1);
		}else{
			$this->error('管理员删除失败',url('admin_list'),0);
		}
	}
	
	//更改状态
	public function admin_state(){
		$id = input('x');
		if (empty($id)){
			$this->error('用户ID不存在',url('admin_list'),0);
		}
		$status = Db::name('admin')->where(array('admin_id'=>$id))->value('admin_open');//判断当前状态情况
		if($status==1){
			$statedata = array('admin_open'=>0);
			Db::name('admin')->where(array('admin_id'=>$id))->update($statedata);
			$this->success('状态禁止',1,1);
		}else{
			$statedata = array('admin_open'=>1);
			Db::name('admin')->where(array('admin_id'=>$id))->update($statedata);
			$this->success('状态开启',1,1);
		}
	
	}
	
	//用户组管理
	public function admin_group_list(){
		$auth_group = Db::name('auth_group')->select();
		$this->assign('auth_group',$auth_group);
		return $this->fetch();
	}
	//用户组管理
	public function admin_group_add(){
		return $this->fetch();
	}
	public function admin_group_runadd(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('admin_group_list'),0);
		}else{
			$sldata=array(
					'title' => input('title'),
					'status' => input('status'),
					'addtime' => time(),
			);
			Db::name('auth_group')->insert($sldata);
			$this->success('用户组添加成功',url('admin_group_list'),1);
		}
	}
	
	public function admin_group_del(){
		$rst = Db::name('auth_group')->where(array('id'=>input('id')))->delete();
		if($rst!==false){
			$this->success('用户组删除成功',url('admin_group_list'),1);
		}else{
			$this->error('用户组删除失败',url('admin_group_list'),0);
		}
	}
	
	public function admin_group_edit(){
		
		$group = Db::name('auth_group')->where(array('id'=>input('id')))->find();
		$this->assign('group',$group);
		return $this->fetch();
	}
	
	public function admin_group_runedit(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('admin_group_list'),0);
		}else{
			$sldata=array(
					'id'=>input('id'),
					'title'=>input('title'),
					'status'=>input('status'),
			);
			Db::name('auth_group')->update($sldata);
			$this->success('用户组修改成功',url('admin_group_list'),1);
		}
	}
	
	public function admin_group_state()
	{
		$id = input('x');
		$status = Db::name('auth_group')->where(array('id'=>$id))->value('status');//判断当前状态情况
		if($status==1){
			$statedata = array('status'=>0);
			$auth_group=Db::name('auth_group')->where(array('id'=>$id))->update($statedata);
			$this->success('状态禁止',1,1);
		}else{
			$statedata = array('status'=>1);
			$auth_group=Db::name('auth_group')->where(array('id'=>$id))->update($statedata);
			$this->success('状态开启',1,1);
		}
	}
	//四重权限配置
	public function admin_group_access()
	{
		$admin_group = Db::name('auth_group')->where(array('id'=>input('id')))->find();
		$m = Db::name('auth_rule');
		$data = $m->field('id,name,title')->where('pid=0')->select();
		foreach ($data as $k=>$v){
			$data[$k]['sub'] = $m->field('id,name,title')->where('pid='.$v['id'])->select();
			foreach ($data[$k]['sub'] as $kk=>$vv){
				$data[$k]['sub'][$kk]['sub'] = $m->field('id,name,title')->where('pid='.$vv['id'])->select();
				foreach ($data[$k]['sub'][$kk]['sub'] as $kkk=>$vvv){
					$data[$k]['sub'][$kk]['sub'][$kkk]['sub'] = $m->field('id,name,title')->where('pid='.$vvv['id'])->select();
				}
			}
		}
		$this->assign('admin_group',$admin_group);	// 顶级
		$this->assign('datab',$data);	// 顶级
		return $this->fetch();
	}
	//四重权限配置操作
	public function admin_group_runaccess(){
		$m = Db::name('auth_group');
		$new_rules = input('new_rules/a');

		$imp_rules = empty($new_rules) ? '' : implode(',', $new_rules).',';
		$sldata=array(
				'id'=>input('id'),
				'rules'=>$imp_rules,
		);
		if($m->update($sldata)!==false){
			clear_cache();
			exit;
			$this->success('权限配置成功',url('admin_group_list'),1);
		}else{
			$this->error('权限配置失败',url('admin_group_list'),0);
		}
	}
	//权限列表
	public function admin_rule_list(){
		// echo APP_PATH;exit;

		$admin_rule = Db::name('auth_rule')->order('sort')->select();
		$arr = \org\Leftnav::rule($admin_rule);
		$this->assign('admin_rule',$arr);//权限列表
		return $this->fetch();
	}
	//权限添加
	public function admin_rule_runadd(){
		if(!request()->isAjax()){
			$this->error('提交方式不正确',url('admin_rule_list'),0);
		}else{
			$admin_rule = Db::name('auth_rule');
			$pid=$admin_rule->where(array('id'=>input('pid')))->field('level')->find();
			$level=$pid['level']+1;
			//是否存在控制器/方法
			$arr=explode('/',input('name'));
			//检测name是否有效
			if($level==1)
			{
				//是否存在控制器
				$class = 'app\\'.$arr[0].'\controller\\'.$arr[1];
				if(!class_exists($class))
				{
					$this->error('不存在 '.$arr[1].' 的控制器',url('admin_rule_list'),0);
				}
			}
			elseif($level==2)
			{
				//不检测
			}
			else
			{
				if(count($arr)==3)
				{
					$class = 'app\\'.$arr[0].'\controller\\'.$arr[1];
					if(!class_exists($class))
					{
						$this->error('不存在 '.$arr[1].' 的控制器',url('admin_rule_list'),0);
					}
					if(!method_exists($class, $arr[2]))
					{
						$this->error('控制器'.$arr[1].'不存在方法'.$arr[2],url('admin_rule_list'),0);
					}
				}else{
					$this->error('提交名称不规范',url('admin_rule_list'),0);
				}
			}
			$sldata=array(
					'name'=>input('name'),
					'title'=>input('title'),
					'status'=>input('status',0),
					'sort'=>input('sort'),
					'addtime'=>time(),
					'pid'=>input('pid'),
					'css'=>input('css'),
					'level'=>$level,
			);
			$admin_rule->insert($sldata);
			clear_cache();
			$this->success('权限添加成功',url('admin_rule_list'),1);
		}
	}
	
	//修改状态
	public function admin_rule_state(){
		$id=input('x');
		$statusone = Db::name('auth_rule')->where(array('id'=>$id))->value('status');//判断当前状态情况
		if($statusone==1){
			$statedata = array('status'=>0);
			$auth_group = Db::name('auth_rule')->where(array('id'=>$id))->update($statedata);
			clear_cache();
			$this->success('状态禁止',1,1);
		}else{
			$statedata = array('status'=>1);
			$auth_group = Db::name('auth_rule')->where(array('id'=>$id))->update($statedata);
			clear_cache();
			$this->success('状态开启',1,1);
		}
	
	}
	//排序
	public function admin_rule_order(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('admin_rule_list'),0);
		}else{
			$auth_rule = Db::name('auth_rule');
			foreach ($_POST as $id => $sort){
				$auth_rule->where(array('id' => $id ))->update(array('sort'=>$sort));
			}
			clear_cache();
			$this->success('排序更新成功',url('admin_rule_list'),1);
		}
	}
	//修改权限
	public function admin_rule_edit(){
		//全部规则
		$admin_rule_all=Db::name('auth_rule')->order('sort')->select();
		$arr = \org\Leftnav::rule($admin_rule_all);
		$this->assign('admin_rule',$arr);
		//待编辑规则
		$admin_rule=Db::name('auth_rule')->where(array('id'=>input('id')))->find();
		$this->assign('rule',$admin_rule);
		return $this->fetch();
	}
	//复制权限
	public function admin_rule_copy(){
		//全部规则
		$admin_rule_all = Db::name('auth_rule')->order('sort')->select();
		$arr = \org\Leftnav::rule($admin_rule_all);
		$this->assign('admin_rule',$arr);
		//待编辑规则
		$admin_rule = Db::name('auth_rule')->where(array('id'=>input('id')))->find();
		$this->assign('rule',$admin_rule);
		return $this->fetch();
	}
	//修改权限操作
	public function admin_rule_runedit(){
		if(!request()->isAjax()){
			$this->error('提交方式不正确',url('admin_rule_list'),0);
		}else{
			$admin_rule=Db::name('auth_rule');
			$pid=$admin_rule->where(array('id'=>input('pid')))->field('level')->find();
			$level=$pid['level']+1;
			$sldata=array(
					'id'=>input('id',1,'intval'),
					'name'=>input('name'),
					'title'=>input('title'),
					'status'=>input('status'),
					'pid'=>input('pid',0,'intval'),
					'css'=>input('css'),
					'sort'=>input('sort'),
					'level'=>$level,
			);
			$rst=$admin_rule->update($sldata);
			if($rst!==false){
				clear_cache();
				$this->success('权限修改成功',url('admin_rule_list'),1);
			}else{
				$this->error('权限修改失败',url('admin_rule_list'),0);
			}
		}
	}
	//删除权限
	public function admin_rule_del(){
		//TODO 自动删除子权限
		$rst = Db::name('auth_rule')->where(array('id'=>input('id')))->delete();
		if($rst!==false){
			clear_cache();
			$this->success('权限删除成功',url('admin_rule_list'),1);
		}else{
			$this->error('权限删除失败',url('admin_rule_list'),0);
		}
	}
}