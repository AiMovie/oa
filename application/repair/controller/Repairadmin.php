<?php
// +----------------------------------------------------------------------
// | 功能：机械修理厂管理后台
// +----------------------------------------------------------------------
// | 作者：程龙飞
// +----------------------------------------------------------------------
// | 时间：2017-7-14
// +----------------------------------------------------------------------
namespace  app\Repair\controller;

use app\common\controller\Auth;
use think\Db;
class Repairadmin extends Auth {

	//信息列表
	public function index()
	{

		//查询所有的省及直辖市
        $province=Db::name('region')->where('type',1)->field('id,name')->select();
        $this->assign('province',$province);

        if(input('province_id'))
        {
        	$map['province_id'] = input('province_id');
        	$city = Db::name('region')->where('pid',$info['province_id'])->select();
        	$this->assign('city_list',$city);
        }
        if(input('city_id'))
        {
        	$map['city_id'] = input('city_id');
        }
        if(input('type'))
        {
        	$map['a.type'] = input('type');
        }
        $up_server=input('up_server');
        if(!empty( $up_server))
        {
        	$map['up_server'] = input('up_server');
        }
		$result = Db::name('repair')
            ->alias('a')
            ->join('region b ','b.id= a.province_id','LEFT')
            ->join('region c ','c.id= a.city_id','LEFT')
            ->join('parameter d ','d.id= a.type','LEFT')
            ->where($map)
            ->order('create_time desc')
            ->field('a.*,b.name AS province_name,c.name AS city_name,d.name AS repair_type')
            ->paginate(15);
//p(Db::name('repair')->getlastsql());
        $list = $result->items();
        // ee($list);
        foreach($list as $key => $value)
        {
        	$list[$key]['class_list'] = Db::name('parameter')->field('name')->where(['id'=>['IN',$value['class_id']]])->select();
        	$list[$key]['service_list'] = Db::name('parameter')->field('name')->where(['id'=>['IN',$value['up_server']]])->select();
        }
		$this->assign('page',$result->render());
		$this->assign('list',$list);

		return $this->fetch();
	}

	//添加
	public function repairAdd()
	{
		if(request()->isAjax())
		{
			$member_list_tel = input('member_list_tel');

			$member_list_id = Db::name('member_list')->where("member_list_tel",$member_list_tel)->value('member_list_id');
			if(!$member_list_id)
			{
				$this->error('不存在该用户',0,0);
			}

			//图片处理
			$file = request()->file('file0');
			if($file)
			{
				// 移动到框架应用根目录/public/data/uploads/ 目录下
			    $info = $file->move(ROOT_PATH . 'public' . DS . 'data'. DS . 'uploads');
				if($info) {
					$shop_img_url=substr(\think\Config::get('UPLOAD_DIR'),1).$info->getSaveName();//如果上传成功则完成路径拼接
				}else{
					$this->error($file->getError(),url('index'),0);//否则就是上传错误，显示错误原因
				}
			} 
			//图片处理结束

			$data = [
				'member_list_id' => $member_list_id,
				'type' => input('type'),
				'class_id' => implode(',',input('class_id/a','')),
				'up_server' => implode(',',input('service_id/a','')),
				'province_id' => input('province_id'),
				'city_id' => input('city_id'),
				'name' => input('name'),
				'telphone' => input('telphone'),
				'shop_img_url' => $shop_img_url,
				'update_time' => time(),
				'create_time' => time(),
				'is_check' => input('is_check',0),
				'is_effective' => input('is_effective',0),
			];

			Db::name('repair')->insert($data);
			$id = Db::name('repair')->getLastInsID();

			$fileurl_tmp = input('fileurl_tmp/a');
			//添加商品图片
			foreach ($fileurl_tmp as $key => $value) {
				$data = array(
						'uptime' => time(),
						'path' => $value,
						'product_id' => $id,
						'type' => 4
					);
				Db::name('plug_files')->insert($data);
			}
			$this->success('添加成功',url('index'),1);

		}
		else
		{
            //修理厂类型
            $repair_id = Db::name('parameter')->where("name",'修理厂类型')->where("module",4)->where("state",1)->value('id');
            $repair_list = Db::name('parameter')->where("parent_id",$repair_id)->where("state",1)->select();
            $this->assign('repair_list',$repair_list);
		    //工种类型
			$class_id = Db::name('parameter')->where("name",'工种')->where("module",4)->where("state",1)->value('id');
			$class_list = Db::name('parameter')->where("parent_id",$class_id)->where("state",1)->select();
			$this->assign('class_list',$class_list);
            //服务类型
            $service_id = Db::name('parameter')->where("name",'服务类型')->where("module",4)->where("state",1)->value('id');
            $service_list = Db::name('parameter')->where("parent_id",$service_id)->where("state",1)->select();
            $this->assign('service_list',$service_list);
			//查询所有的省及直辖市
            $province=Db::name('region')->where('type',1)->field('id,name')->select();
            $this->assign('province',$province);
			return $this->fetch();
		}
	}

	//修改
	public function repairedit()
	{
		$id = input('id');
		if(request()->isAjax())
		{
			$checkpic=input('checkpic');
			$oldcheckpic=input('oldcheckpic');
			$options=input('options/a');
			//如果有新上传
			if($checkpic!=$oldcheckpic)
			{
				
				$file = request()->file('file0');

				if($file)
				{
			    	// 移动到框架应用根目录/public/data/uploads/ 目录下
			   	 	$info = $file->move(ROOT_PATH . 'public' . DS . 'data'. DS . 'uploads');
					if($info)
					{
						$img_url=substr(\think\Config::get('UPLOAD_DIR'),1).$info->getSaveName();//如果上传成功则完成路径拼接
					}else{
						$this->error($file->getError(),url('repairedit',['id'=>$id]),0);//否则就是上传错误，显示错误原因
					}
				}
				$shop_img_url = $img_url;
			}
			else
			{
				//原有图片
				$shop_img_url = input('oldcheckpicname');
			}

			$data = [
				'type' => input('type'),
				'class_id' => implode(',',input('class_id/a','')),
                'up_server' => implode(',',input('service_id/a','')),
                'province_id' => input('province_id'),
				'city_id' => input('city_id'),
				'name' => input('name'),
				'telphone' => input('telphone'),
				'shop_img_url' => $shop_img_url,
				'update_time' => time(),
				'create_time' => time(),
				'is_check' => input('is_check',0),
				'is_effective' => input('is_effective'),
			];
			Db::name('repair')->where('id',$id)->update($data);

			Db::name('plug_files')->where(['type'=>4,'product_id'=>$id])->delete();

			$fileurl_tmp = input('fileurl_tmp/a');
			//添加商品图片
			foreach ($fileurl_tmp as $key => $value) {
				$data = array(
						'uptime' => time(),
						'path' => $value,
						'product_id' => $id,
						'type' => 4
					);
				Db::name('plug_files')->insert($data);
			}

			$this->success('修改成功',url('index',['p'=>input('p')]),1);
		}
		else
		{
            //修理厂类型
            $repair_id = Db::name('parameter')->where("name",'修理厂类型')->where("module",4)->where("state",1)->value('id');
            $repair_list = Db::name('parameter')->where("parent_id",$repair_id)->where("state",1)->select();
            $this->assign('repair_list',$repair_list);
            //工种类型
            $class_id = Db::name('parameter')->where("name",'工种')->where("module",4)->where("state",1)->value('id');
            $class_list = Db::name('parameter')->where("parent_id",$class_id)->where("state",1)->select();
            $this->assign('class_list',$class_list);
            //服务类型
            $service_id = Db::name('parameter')->where("name",'服务类型')->where("module",4)->where("state",1)->value('id');
            $service_list = Db::name('parameter')->where("parent_id",$service_id)->where("state",1)->select();
            $this->assign('service_list',$service_list);

			//查询所有的省及直辖市
            $province=Db::name('region')->where('type',1)->field('id,name')->select();
            $this->assign('province',$province);

            $info = Db::name('repair')->where('id',$id)->find();
            if($info['province_id'])
            {
            	$city = Db::name('region')->where('pid',$info['province_id'])->select();
            	$this->assign('city_list',$city);
            }
//            p($info);
            $this->assign('info',$info);
            $classids = explode(',',$info['class_id']);
            foreach($class_list as $key => $value)
            {
            	if(in_array($value['id'],$classids))
            	{
            		$class_list[$key]['checked'] = 'checked';
            	}
            	else
            	{
            		$class_list[$key]['checked'] = '';
            	}
            }
			$this->assign('class_list',$class_list);

            //多图列表
            $img_list = Db::name('plug_files')->where(['type'=>4,'product_id'=>$id])->order('id ASC')->select();
            $this->assign('img_list',$img_list);
            $this->assign('p',input('p',1));
			return $this->fetch();
		}
	}

	//删除信息
	public function repairDelete()
	{
		$id = input('id');

		$info = Db::name('repair')->where(['id' => $id])->find();
		if($info)
		{
			if(file_exists(ROOT_PATH.$info['shop_img_url']) && is_file(ROOT_PATH.$info['shop_img_url']))
			{
				unlink(ROOT_PATH.$info['shop_img_url']);
			}

            $img_list = Db::name('plug_files')->where(['type'=>4,'product_id'=>$id])->order('id ASC')->select();
            if($img_list)
            {
	            foreach($img_list as $value)
	            {
					if(file_exists(ROOT_PATH.$value['path']) && is_file(ROOT_PATH.$value['path']))
					{
						unlink(ROOT_PATH.$value['path']);
					}
	            }
	        }
            Db::name('repair')->where(['id' => $id])->delete();
            Db::name('plug_files')->where(['type'=>4,'product_id'=>$id])->delete();
            $this->success('删除成功',url('index',['p'=>input('p',1)]),1);
		}
		else
		{
			$this->success('删除失败',url('index',['p'=>input('p',1)]),0);
		}
	}

	//状态激活
	public function repair_active()
	{
        //获取要修改的id
        $id = input("x");
        $newsmechanics_db = db("repair");
        //判断此用户状态情况
        $status=$newsmechanics_db->where(array('id'=>$id))->value('is_effective');
        if($status == 1){
            //禁止
            $statedata = array('is_effective'=>0);
            $auth_group=$newsmechanics_db->where(array('id'=>$id))->setField($statedata);
            $this->success('未激活',1,1);
        }else{
            //开启
            $statedata = array('is_effective'=>1);
            $auth_group=$newsmechanics_db->where(array('id'=>$id))->setField($statedata);
            $this->success('已激活',1,1);
        }
	}
    /**
     * 根据省获取城市
     */
    public function getCity()
    {
        $province_id = input('province_id');
        $zhi=array(2,25,27,32);
        if(in_array($province_id,$zhi)){
            $id = Db::name('region')->where('pid',$province_id)->field('id,name')->value('id');
            $list = Db::name('region')->where('pid',$id)->field('id,name')->select();
        }else{
            $list = Db::name('region')->where('pid',$province_id)->field('id,name')->select();
        }
        $html = '<option value="">请选择</option>';
        foreach($list as $key => $value)
        {
            $html .= "<option value=\"{$value['id']}\">{$value['name']}</option>";
        }
        echo $html;exit;
    }

	//管理设置
	public function setting()
	{
		if(request()->isAjax())
		{
			$options = input('options/a');
			$options['check_open'] = request()->post('check_open',0,'intval');
			$options['see_open'] = request()->post('see_open',0,'intval');
			Db::name('options')->where(array('option_name'=>'repairSetting'))->setField('option_value',serialize($options));
			$this->success('修改成功',url('setting'),1);
		}
		else
		{
			$sys = Db::name('options')->where(array('option_name'=>'repairSetting'))->value("option_value");
			if($sys)
			{
				$sys=unserialize($sys);
			}
			else
			{
				$sys =  [];
			}
			$this->assign('sys',$sys);
			return $this->fetch();
		}
	}

	//参数管理
	public function paramsSetting()
	{
		$map = [
			'module' => 4, //1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
			'parent_id' => 0
		];
		$list = Db::name('parameter')->where($map)->select();

		$this->assign('list',$list);

		return $this->fetch();
	}

	//添加参数
	public function paramsSettingAdd()
	{
		//有参数提交
		if(request()->isAjax())
		{
			$data = [
				'module' => 4,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
				'name' => input('name'),
				'multi' => input('multi',0),
				'state' => input('state',0),
				'value' => '',
				'parent_id' => 0,
				'type'=>input('type'),
				'input_type'=>input('input_type'),
			];

			Db::name('parameter')->insert($data);


			$this->success('添加成功',url('paramsSetting'),1);
		}
		//没有参数提交显示页面
		else
		{
			return $this->fetch();
		}
	}

	//修改参数
	public function paramsSettingEdit()
	{
		$id = input('id');
		if(!id)
		{
			$this->redirect(url('paramsSetting'));
		}
		//有参数提交
		if(request()->isAjax())
		{
			$data = [
				'module' => 4,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
				'name' => input('name'),
				'multi' => input('multi',0),
				'state' => input('state',0),
				'value' => '',
				'parent_id' => 0,
				'type'=>input('type'),
				'input_type'=>input('input_type')
			];

			Db::name('parameter')->where(['id'=>$id])->update($data);


			$this->success('修改成功',url('paramsSetting'),1);
		}
		//没有参数提交显示页面
		else
		{
			$info = Db::name('parameter')->where('id',$id)->find();
			$this->assign('info',$info);
			$this->assign('id',$id);
			return $this->fetch();
		}
	}

	//删除参数
	public function paramsSettingDelete()
	{
		$id = input('id');
		if (empty($id)){
			$this->error('数据不存在',url('paramsSetting'),0);
		}
		Db::name('parameter')->where(array('id'=>input('id')))->delete();

		if($rst!==false){
			$this->success('删除成功',url('paramsSetting'),1);
		}else{
			$this->error('删除失败',url('paramsSetting'),0);
		}
	}
	//查看参数值
	public function paramsSettingValue()
	{
		$pid = input('pid');
		if(empty($pid))
		{
			$this->redirect(url('paramsSetting'));
		}
		$name = Db::name('parameter')->where('id',$pid)->value('name');
		$this->assign('name',$name);
		$this->assign('pid',$pid);

		$map = [
			'parent_id' => $pid,
		];
		$list = Db::name('parameter')->where($map)->select();
		$this->assign('list',$list);

		return $this->fetch();
	}

	//添加参数值
	public function paramsSettingValueAdd()
	{
		$pid = input('pid');
		if(empty($pid))
		{
			$this->redirect(url('paramsSetting'));
		}		
		//有参数提交
		if(request()->isAjax())
		{
			$data = [
				'module' => 4,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
				'name' => input('name'),
				'multi' => 1,
				'state' => input('state',0),
				'value' => input('name'),
				'parent_id' => $pid
			];

			Db::name('parameter')->insert($data);


			$this->success('添加成功',url('paramsSettingValue',array('pid'=>$pid)),1);
		}
		//没有参数提交显示页面
		else
		{
			$name = Db::name('parameter')->where('id',$pid)->value('name');
			$this->assign('name',$name);
			$this->assign('pid',$pid);
			return $this->fetch();
		}
	}

    //修改参数值
	public function paramssettingvalueedit()
	{
		$id = input('id');
		if(!id)
		{
			$this->redirect(url('paramsSettingValue',array('pid'=>input('pid'))));
		}

		//有参数提交
		if(request()->isAjax())
		{
			$data = [
				'module' => 4,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
				'name' => input('name'),
				'multi' => 1,
				'state' => input('state',0),
				'value' => input('name'),
				'parent_id' => input('pid'),
			];

			Db::name('parameter')->where(['id'=>$id])->update($data);


			$this->success('修改成功',url('paramsSetting'),1);
		}
		//没有参数提交显示页面
		else
		{
			$info = Db::name('parameter')->where('id',$id)->find();
			$this->assign('info',$info);
			$this->assign('id',$id);
			$name = Db::name('parameter')->where('id',input('pid'))->value('name');
			$this->assign('name',$name);
			return $this->fetch();
		}
	}


	//修改参数状态（开启关闭）
	public function paramsSettingState()
	{
		$id = input('x');
		$status = Db::name('parameter')->where(array('id'=>$id))->value('state');//判断当前状态情况
		if($status==1){
			$statedata = array('state'=>0);
			$auth_group=Db::name('parameter')->where(array('id'=>$id))->setField($statedata);
			$this->success('状态禁止',1,1);
		}else{
			$statedata = array('state'=>1);
			$auth_group=Db::name('parameter')->where(array('id'=>$id))->setField($statedata);
			$this->success('状态开启',1,1);
		}
	}

	//删除参数
	public function paramsSettingValueDelete()
	{
		$id = input('id');
		if (empty($id)){
			$this->error('数据不存在',url('paramsSetting'),0);
		}
		Db::name('parameter')->where(array('id'=>input('id')))->delete();

		if($rst!==false){
			$this->success('删除成功',url('paramsSettingValue',array('pid'=>input('pid'))),1);
		}else{
			$this->error('删除失败',url('paramsSettingValue',array('pid'=>input('pid'))),0);
		}
	}
}
?>