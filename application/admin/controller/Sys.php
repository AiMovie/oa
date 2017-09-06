<?php
/**
 * 功能：系统设置
 * 修改时间：2017-5-3
 * 修改人：程龙飞
 */
namespace  app\admin\controller;

use app\common\controller\Auth;
use think\Session;
use think\Db;
use org\Stringnew;
class Sys extends Auth
{
	public function sysnotedel()
	{
		$id = input('id');
		$p = input('p');
		$re = Db::name('sysnote')->where('id',$id)->delete();
		if($re){
			$this->success('删除成功',url('sysnotelist',array('p'=>$p)),1);
		}else{
			$this->error('删除失败',url('sysnotelist',array('p'=>$p)),0);
		}
	}
	//系统通知列表
	public function sysnotelist()
	{
		if($_POST){
			$key = input('key');
			$object = Db::name('sysnote')
					->where('title','like','%'.$key.'%')
					->whereOr('content','like','%'.$key.'%')
					->order('id DESC')
					->paginate(15);
		}else{
			$object = Db::name('sysnote')->order('id DESC')->paginate(15);
		}
		$note_list = $object->items();
		$page = $object->render();
		// dump($note_list);die;
		$this->assign('note_list',$note_list);
		$this->assign('page',$page);
		return $this->fetch();
		return $this->fetch();
	}
	//app启动页设置
	public function appStartSetting()
	{
		if($_POST){
			$checkpic=input('checkpic');
			$oldcheckpic=input('oldcheckpic');
			$options=input('options/a');
			//如果有新上传
			if($checkpic!=$oldcheckpic)
			{
				
				$file = request()->file('file0');
			    // 移动到框架应用根目录/public/data/uploads/ 目录下
			    $info = $file->move(ROOT_PATH . 'public' . DS . 'data'. DS . 'uploads');
				if($info) {
					$img_url=substr(\think\Config::get('UPLOAD_DIR'),1).$info->getSaveName();//如果上传成功则完成路径拼接
				}else{
					$this->error($file->getError(),url('Sys/appStartSetting'),0);//否则就是上传错误，显示错误原因
				}

				$options['start_img']=$img_url;
			}
			else
			{
				//原有图片
				$options['start_img']=input('oldcheckpicname');
			}

			$data = [
				'option_value' => serialize($options),
			];
			Db::name('options')->where(array('option_name'=>'app_start_options'))->update($data);
			$this->success('修改成功',url('Sys/appStartSetting'),1);
		}else{
			$sys=Db::name('options')->where(array('option_name'=>'app_start_options'))->find();		
			if(empty($sys)){
				$data['option_name']='email_options';
				$data['option_value']='';
				$data['autoload']=1;
				Db::name('options')->insert($data);
			}
			$sys=unserialize($sys['option_value']);
			// var_dump($sys);exit;
			$this->assign('sys',$sys);
			return $this->fetch();
		}
	}
	//意见回馈删除操作
	public function plug_sug_del()
	{
		$id = input('plugid');
		$p = input('p');
		$re = Db::name('plug_sug')->where('plug_sug_id',$id)->delete();
		if($re){
			$this->success('删除成功',url('feedback',array('p'=>$p)),1);
		}else{
			$this->error('删除失败',url('feedback',array('p'=>$p)),0);
		}
	}
	//举报信息管理
	public function feedback()
	{
		if($_POST){
			$key = input('key');
			$object = Db::name('plug_sug')
					->where('plug_sug_phone','like','%'.$key.'%')
					->whereOr('plug_sug_email','like','%'.$key.'%')
					->order('plug_sug_id DESC')
					->paginate(15);
		}else{
			$object = Db::name('plug_sug')->order('plug_sug_id DESC')->paginate(15);
		}
		$plug_list = $object->items();
		$page = $object->render();

		$this->assign('plug_list',$plug_list);
		$this->assign('page',$page);
		return $this->fetch();
	}
	//站点设置显示
	public function sys(){
		$sys = Db::name('options')->where(array('option_name'=>'site_options'))->value("option_value");
		$sys=json_decode($sys,true);
		$this->assign('sys',$sys);
		return $this->fetch();
	}
	//保存站点设置
	public function runsys(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('Sys/sys'),0);
		}else{
			$checkpic=input('checkpic');
			$oldcheckpic=input('oldcheckpic');
			$options=input('options/a');
			//如果有新上传
			if($checkpic!=$oldcheckpic)
			{
				
				$file = request()->file('file0');
			    // 移动到框架应用根目录/public/data/uploads/ 目录下
			    $info = $file->move(ROOT_PATH . 'public' . DS . 'data'. DS . 'uploads');
				if($info) {
					$img_url=substr(\think\Config::get('UPLOAD_DIR'),1).$info->getSaveName();//如果上传成功则完成路径拼接
					//写入文件记录数据库
					$data['uptime']=time();
					$data['filesize']=$info->getInfo()['size'];
					$data['path']=$img_url;
					Db::name('plug_files')->insert($data);
				}else{
					$this->error($file->getError(),url('Sys/sys'),0);//否则就是上传错误，显示错误原因
				}

				$options['site_logo']=$img_url;
			}
			else
			{
				//原有图片
				$options['site_logo']=input('oldcheckpicname');
			}
			$rst=Db::name('options')->where(array('option_name'=>'site_options'))->setField('option_value',json_encode($options));
			if($rst!==false)
			{
				\think\Cache::set("site_options", $options,0);
				$this->success('站点设置保存成功',url('Sys/sys'),1);
			}
			else
			{
				$this->error('提交参数不正确',url('Sys/sys'),0);
			}
		}
	}

	//微信设置显示
	public function wesys(){
		$sys=Db::name('options')->where(array('option_name'=>'weixin_options'))->find();
		if(empty($sys)){
			$data['option_name']='weixin_options';
			$data['option_value']='';
			$data['autoload']=1;
			Db::name('options')->insert($data);
		}else{
			$sys=json_decode($sys['option_value'],true);
		}
		$this->assign('sys',$sys)->fetch();
	}

	//保存微信设置
	public function runwesys(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('wesys'),0);
		}else{
			$rst=Db::name('options')->where(array('option_name'=>'weixin_options'))->setField('option_value',json_encode(input('options')));
			if($rst!==false){
				$this->success('微信设置保存成功',url('wesys'),1);
			}else{
				$this->error('提交参数不正确',url('wesys'),0);
			}
		}
	}
	//第三方登录设置显示
	public function oauthsys(){
		$oauth_qq=sys_config_get('THINK_SDK_QQ');
		$oauth_sina=sys_config_get('THINK_SDK_SINA');
		$this->assign('oauth_qq',$oauth_qq);
		$this->assign('oauth_sina',$oauth_sina);
		$this->fetch();
	}
	//保存第三方登录设置
	public function runoauthsys(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('oauthsys'),0);
		}else{
			$host=get_host();
			$call_back = $host.__ROOT__.'/index.php?m=Home&c=oauth&a=callback&type=';
			$data = array(
				'THINK_SDK_QQ' => array(
						'APP_KEY'    => input('qq_appid'),
						'APP_SECRET' => input('qq_appkey'),
						'CALLBACK'   => $call_back . 'qq',
				),
				'THINK_SDK_SINA' => array(
						'APP_KEY'    => input('sina_appid'),
						'APP_SECRET' => input('sina_appkey'),
						'CALLBACK'   => $call_back . 'sina',
				),
			    'THINK_SDK_WEIXIN' => array(
			        'APP_KEY'    => input('weixin_appid'),
			        'APP_SECRET' => input('weixin_appkey'),
			        'CALLBACK'   => $call_back . 'weixin',
			    ),
			);
			$rst=sys_config_setbyarr($data);
			if($rst){
				clear_cache();
				$this->success('设置保存成功',url('oauthsys'),1);
			}else{
				$this->error('设置保存失败',url('oauthsys'),0);
			}
		}
	}
	//发送邮件设置
	public function emailsys(){
		$sys=Db::name('options')->where(array('option_name'=>'email_options'))->find();		
		if(empty($sys)){
			$data['option_name']='email_options';
			$data['option_value']='';
			$data['autoload']=1;
			Db::name('options')->insert($data);
		}
		$sys=json_decode($sys['option_value'],true);
		$this->assign('sys',$sys);
		return $this->fetch();
	}

	//保存邮箱设置
	public function runemail(){
		
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('emailsys'),0);
		}else{
			
			$info = input();
			$option_value = $info['options'];

			$rst=Db::name('options')->where(array('option_name'=>'email_options'))->setField('option_value',json_encode($option_value));
			if($rst!==false){
				Session::clear("email_options");
				$this->success('邮箱设置保存成功',url('emailsys'),1);
			}else{
				$this->error('提交参数不正确',url('emailsys'),0);
			}
		}
	}

	//帐号激活设置
	public function activesys(){
		$sys=Db::name('options')->where(array('option_name'=>'active_options'))->find();
		if(empty($sys)){
			$data['option_name']='active_options';
			$data['option_value']='';
			$data['autoload']=1;
			Db::name('options')->insert($data);
		}
		$sys=json_decode($sys['option_value'],true);
		$this->assign('sys',$sys)->fetch();
	}

	//保存帐号激活设置
	public function runactive(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('Sys/activesys'),0);
		}else{
			//htmlspecialchars_decode(
			$options=input('options');
			$options['email_tpl']=htmlspecialchars_decode($options['email_tpl']);
			$rst=Db::name('options')->where(array('option_name'=>'active_options'))->setField('option_value',json_encode($options));
			if($rst!==false){
				F("active_options",null);
				$this->success('帐号激活设置保存成功',url('Sys/activesys'),1);
			}else{
				$this->error('提交参数不正确',url('Sys/activesys'),0);
			}
		}
	}


	public function clear(){
		clear_cache();
		$this->success('清理缓存成功',1,1);
	}
  
	public function profile(){
        $admin=array();
        if(session('aid')){
            $rs=Db::name('admin');
            $admin=$rs->alias("a")->join('auth_group_access b','a.admin_id = b.uid')->join('auth_group c','b.group_id = c.id')->where(array('a.admin_id'=>session('aid')))->find();
        }
		
        $this->assign('admin', $admin);
		return $this->fetch();
	}
    public function avatar(){

        $imgurl=input('post.imgurl');
        //去'/'
        $imgurl=str_replace('/','',$imgurl);
        $admin=Db::name('admin')->where(array('admin_id'=>session('aid')))->find();
        $old_img=$admin['admin_avatar'];
        $da['admin_avatar']=substr(\think\Config::get('UPLOAD_DIR'),1).'avatar/'.$imgurl;;

        $rst=Db::name('admin')->where(array('admin_id'=>session('aid')))->update($da);
        if($rst!==false){
            session('admin_avatar',$imgurl);
			$url=substr(\think\Config::get('UPLOAD_DIR'),1).'avatar/'.$imgurl;
			$this->success('头像更新成功',url('profile'),1);
        }else{
            $this->error('头像更新失败',url('profile'),0);
        }
    }
	public function admin_runedit(){
		$admin_list=Db::name('admin');
		$admin_pwd=input('admin_pwd');
		$group_id=input('group_id');
		$admindata['admin_id']=input('admin_id');
		if ($admin_pwd){
			$admin_pwd_salt=Stringnew::randString(10);
			$admindata['admin_pwd_salt']=$admin_pwd_salt;
			$admindata['admin_pwd']=encrypt_password(input('admin_pwd'),$admin_pwd_salt);
            $admindata['admin_changepwd']=time();
		}
		$admindata['admin_email']=input('admin_email');
		$admindata['admin_tel']=input('admin_tel');
		$admindata['admin_realname']=input('admin_realname');
		$admindata['admin_open']=input('admin_open',0,'intval');
		$admin_list->update($admindata);
        if($group_id){
			$rst=Db::name('auth_group_access')->where(array('uid'=>input('admin_id')))->find();
			if($rst){
				//修改
				$rst=Db::name('auth_group_access')->where(array('uid'=>input('admin_id')))->setField('group_id',$group_id);
			}else{
				//增加
				$data['uid']=input('admin_id');
				$data['group_id']=$group_id;
				$rst=Db::name('auth_group_access')->insert($data);
			}
        }
        if($rst!==false){
            $this->success('管理员修改成功',url('profile'),1);
        }else{
            $this->error('管理员修改失败',url('profile'),0);
        }
	}
    //登陆注册配置
    public function regist(){
    	$this->fetch();
    }

    //版本设置
    public function version()
    {

    }

    //版本添加
    public function versionAdd()
    {

    }

}