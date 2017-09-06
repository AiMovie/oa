<?php
// +----------------------------------------------------------------------
// | 角色管理模块
// +----------------------------------------------------------------------
// | 会员列表，会员详情、禁用启用会员
// +----------------------------------------------------------------------
// | 石保帅  2017.05.06
// +----------------------------------------------------------------------
namespace app\Member\Controller;
use app\common\controller\Auth;
use think\Session;
use think\Db;
use org\Stringnew;
class Member extends Auth{
	/*
     * 会员列表
     */
	public function member_list()
	{
		if($_POST)
		{
			$key = input('key');
			$member_list = Db::name('member_list')
				->where('member_list_username|member_list_email','like',$key.'%')
				->paginate(15);
		}
		else
		{
			//查询数据列表
			$member_list = Db::name('member_list')->order('member_list_id DESC')->paginate(15);
		}
		$list = $member_list->items();
		$page = $member_list->render();
		$this->assign('page',$page);
		$this->assign('list',$list);
		return $this->fetch();
	}
	//添加会员view
	public function member_add(){
		$member_group = Db::name('member_group')->select();//会员分组
		$this->assign('member_group',$member_group);
		$province = Db::name('region')->where('pid = 1')->select();//会员分组
		$this->assign('province',$province);
		return $this->fetch();
	}
	//添加会员action
	public function member_runadd()
	{
		if(!$_POST)
		{
			$this->error('提交方式不正确',Url('member_list'),0);
		}
		else
		{
			$member_pwd_salt = Stringnew::randString(10);
			$member_db=Db::name('member_list');
			$data['member_list_groupid'] = input('member_list_groupid')?:'';//分组
			$data['member_list_username'] = input('member_list_username');//用户名---must---
			$res = $member_db->where('member_list_username',$data['member_list_username'])->find();
			if($res)
			{
				$this->error('用户名已存在',Url('member_add'),0);	
			}
			$data['member_list_open'] = input('member_list_open')?:0;
			$data['user_status'] = input('user_status')?:0;
			$data['member_list_salt'] = $member_pwd_salt;
			$data['member_list_pwd'] = encrypt_password(input('member_list_pwd'),$member_pwd_salt);//密码---must---
			$data['member_list_nickname'] = input('member_list_nickname')?:'';//昵称
			$data['member_list_province'] = input('member_list_province')?:'';//所在省份
			$data['member_list_city'] = input('member_list_city')?:'';//所在市级
			$data['member_list_town'] = input('member_list_town')?:'';//区级单位
			$data['member_list_sex'] = input('member_list_sex')?:'';//性别
			$data['member_list_tel'] = input('member_list_tel')?:'';//电话---must---
			$data['user_url'] = input('user_url')?:'';//个人网站
			$data['signature'] = input('signature')?:'';//签
			$data['member_list_email'] = input('member_list_email');//邮箱---must---
			$res = $member_db->insert($data);
			if($res)
			{
				$this->success('添加成功',Url('member_list'),1);
			}
		}
	}
	
	public function region(){
		$id = input('id');
		$region_list = Db::name('region')->where('pid',$id)->select();
		$str = '';
		foreach($region_list as $v){
			$str .= "<option value='".$v['id']."'>".$v['name']."</option>";
		}
		return $str;
	}
	//修改用户状态
	public function member_state(){
		//获取要修改的id
		$id = input("x");
		$member_db = db("member_list");
		//判断此用户状态情况
		$status=$member_db->where(array('member_list_id'=>$id))->value('member_list_open');
		if($status == 1){
			//禁止
			$statedata = array('member_list_open'=>2);
			$auth_group=$member_db->where(array('member_list_id'=>$id))->setField($statedata);
			$this->success('状态禁止',1,1);
		}else{
			//开启
			$statedata = array('member_list_open'=>1);
			$auth_group=$member_db->where(array('member_list_id'=>$id))->setField($statedata);
			$this->success('状态开启',1,1);
		}
	}
    //修改激活状态
	public function member_active(){
		//获取要修改的id
		$id = input("x");
		$member_db = db("member_list");
		//判断此用户状态情况
		$status=$member_db->where(array('member_list_id'=>$id))->value('user_status');
		if($status == 1){
			//禁止
			$statedata = array('user_status'=>2);
			$auth_group=$member_db->where(array('member_list_id'=>$id))->setField($statedata);
			$this->success('未激活',1,1);
		}else{
			//开启
			$statedata = array('user_status'=>1);
			$auth_group=$member_db->where(array('member_list_id'=>$id))->setField($statedata);
			$this->success('已激活',1,1);
		}
	}
	//修改
	public function member_edit(){
		if($_POST){
			$member_pwd_salt = Stringnew::randString(10);
			$member_db=Db::name('member_list');
			$mid = input('member_list_id');
			$data['member_list_groupid'] = input('member_list_groupid')?:'';//分组
			$data['member_list_username'] = input('member_list_username');//用户名---must---
			$data['member_list_open'] = input('member_list_open')?:0;
			$data['user_status'] = input('user_status')?:0;
			$data['member_list_salt'] = $member_pwd_salt;
			$data['member_list_pwd'] = encrypt_password(input('member_list_pwd'),$member_pwd_salt);//密码---must---
			$data['member_list_nickname'] = input('member_list_nickname')?:'';//昵称
			$data['member_list_province'] = input('member_list_province')?:'';//所在省份
			$data['member_list_city'] = input('member_list_city')?:'';//所在市级
			$data['member_list_town'] = input('member_list_town')?:'';//区级单位
			$data['member_list_sex'] = input('member_list_sex')?:'';//性别
			$data['member_list_tel'] = input('member_list_tel')?:'';//电话---must---
			$data['user_url'] = input('user_url')?:'';//个人网站
			$data['signature'] = input('signature')?:'';//签
			$data['member_list_email'] = input('member_list_email');//邮箱---must---
			$res = $member_db->where('member_list_id',$mid)->update($data);
			if($res)
			{
				$this->success('修改成功',Url('member_list'),1);
			}
		}
		else
		{

			$mid = input('member_list_id');
			$list = Db::name('member_list')->where('member_list_id',$mid)->find();
			$this->assign('member_list_edit',$list);
			$member_group = Db::name('member_group')->select();//会员分组
			$this->assign('member_group',$member_group);
			$province = Db::name('region')->where('pid',1)->select();
			$this->assign('province',$province);
			return $this->fetch();
		}
	}
	
    //删除
	public function member_del(){

		//获取要删除的ID
		$tid = input('member_list_id');
		$p = input('page');
		p($p);
		// 判断ID是否为空
		if(empty($tid)){
			$this->error('参数错误',Url('member_list',array('page'=>$p)),0);
		}
		//删除数据
		$res = Db::name('member_list')->where('member_list_id',$tid)->delete();
		// 判断是否删除成功
		if($res){
			$this->success('删除成功',Url('member_list',array('page'=>$p)),1);
		}
		else{
			$this->error('删除失败',Url('member_list',array('page'=>$p)),0);
		}
	}
	
	//会员组view
	public function member_group_list(){
		$member_group = Db::name('member_group')->paginate(8);
		$member_group_list = $member_group->items();
		$page = $member_group->render();
		$this->assign('member_group_list',$member_group_list);
//		dump($member_group_list);die;
		$this->assign('page',$page);
		return $this->fetch();
	}
	//会员组删除
	public function member_group_del(){
		$member_group_id = input('member_group_id');
		$res = Db::name('member_group')->where('member_group_id',$member_group_id)->delete();
		if($res){
			$this->success('删除成功',Url('member_group_list'),1);
		}else{
			$this->error('删除失败',Url('member_group_list'),0);
		}
	}
	//会员组添加
	public function member_group_runadd(){
		$arr = array(
			'member_group_name'=>input('member_group_name'),
			'member_group_bomlimit'=>input('member_group_bomlimit'),
			'member_group_toplimit'=>input('member_group_toplimit'),
			'member_group_order'=>input('member_group_order'),
			'member_group_open'=>input('member_group_open')?:0,
		);
		$res = Db::name('member_group')->insert($arr);
		if($res){
			$this->success('添加成功',Url('member_group_list'),1);
		}else{
			$this->error('添加失败',Url('member_group_list'),0);
		}
	}
	
	//会员组修改
	public function member_group_edit(){
		$member_group_id=input('member_group_id');
		$member_group=Db::name('member_group')->where(array('member_group_id'=>$member_group_id))->find();
		$sl_data['member_group_id']=$member_group['member_group_id'];
		$sl_data['member_group_name']=$member_group['member_group_name'];
		$sl_data['member_group_open']=$member_group['member_group_open'];
		$sl_data['member_group_toplimit']=$member_group['member_group_toplimit'];
		$sl_data['member_group_bomlimit']=$member_group['member_group_bomlimit'];
		$sl_data['member_group_order']=$member_group['member_group_order'];
		$sl_data['status']=1;
		$data = array(
				'code'=>1,
				'msg'=>'修改',
				'data'=>$sl_data,
				);
		return $data;		
	}
	
	/*
     * 修改用户组方法
     */
	public function member_group_runedit(){
		if (!$_POST){
			$this->error('提交方式不正确',U('member_group_list'),0);
		}else{
			$sl_data=array(
				'member_group_id'=>input('member_group_id'),
				'member_group_name'=>input('member_group_name'),
				'member_group_toplimit'=>input('member_group_toplimit'),
				'member_group_bomlimit'=>input('member_group_bomlimit'),
				'member_group_order'=>input('member_group_order'),

			);
			$rst=Db::name('member_group')->update($sl_data);
			if($rst!==false){
				$this->success('会员组修改成功',Url('member_group_list'),1);
			}else{
				$this->error('会员组修改失败',Url('member_group_list'),0);
			}
		}
	}
}