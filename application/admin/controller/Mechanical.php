<?php
/**
 * 功能：机械类型管理
 * 修改时间：2017-6-28
 * 修改人：程龙飞
 */
namespace  app\admin\controller;

use app\common\controller\Auth;
use think\Session;
use think\Db;
use org\Stringnew;
class Mechanical extends Auth
{
	// 机械类型列表
	public function index()
	{
		$key = input('key');
		if($key != '')
		{
			$map = [
				'mechanical_name' => ['like',"%$key%"]
			];
		}

		//查询数据列表
		$mechanical = Db::name('mechanical')->where($map)->order('sort_order ASC,id DESC')->paginate(15);
		$list = $mechanical->items();
		$page = $mechanical->render();
		$this->assign('page',$page);
		$this->assign('list',$list);
		return $this->fetch();
	} 

	//添加
	public function Add()
	{
		if(request()->isAjax())
		{

			$data = [
				'mechanical_name' => input('mechanical_name'),
				'sort_order' => input('sort_order'),
				'update_time' => time(),
				'create_time' => time(),
			];

			Db::name('mechanical')->insert($data);

			$mechanical_id = Db::name('mechanical')->getLastInsID();
			$brand_id = input('brand_id/a');
			$data = [];
			foreach($brand_id as $value)
			{
				$data[] = [
					'mechanical_id' => $mechanical_id,
					'brand_id' => $value,
				];
			}
			if($data)
			{
				Db::name('mech_ascc')->insertAll($data);
			}
			$this->success('添加成功',url('index'),1);

		}
		else
		{
			$brand = Db::name('brand')->select();
			$this->assign('brand',$brand);
			return $this->fetch();
		}
	}

	//修改
	public function Edit()
	{
		$id = input('id');
		$map = [
			'id' => $id,
		];
		if(request()->isAjax())
		{
			$data = [
				'mechanical_name' => input('mechanical_name'),
				'sort_order' => input('sort_order'),
				'update_time' => time(),
			];

			Db::name('mechanical')->where($map)->update($data);

			Db::name('mech_ascc')->where(['mechanical_id'=>$id])->delete();
			$brand_id = input('brand_id/a');
			$data = [];
			foreach($brand_id as $value)
			{
				$data[] = [
					'mechanical_id' => $id,
					'brand_id' => $value,
				];
			}
			if($data)
			{
				Db::name('mech_ascc')->insertAll($data);
			}

			$this->success('修改成功',url('index'),1);
		}
		else
		{
			$brand = Db::name('brand')->select();
			$this->assign('brand',$brand);

			$info = Db::name('mechanical')->where($map)->find();
			$this->assign('info',$info);

			$list = Db::name("mech_ascc")->field('brand_id')->where('mechanical_id',$id)->select();
			foreach($list as $value)
			{
				$brand_id[] = $value['brand_id'];
			}
			$this->assign('brand_id',$brand_id);

			return $this->fetch();
		}
	}

	//删除
	public function delete()
	{
		$id = input('id');

		$info = Db::name("mechanical")->where('id',$id)->find();
		if($info)
		{
			Db::name('mechanical')->where('id',$id)->delete();
			$this->success('删除成功',url('index'),1);
		}

		$this->success('删除失败',url('index'),1);
	}
	//型号删除
	public function mechanicalmodelDelete()
	{
		$id = input('id');

		$info = Db::name("mechanical_model")->where('id',$id)->find();
		if($info)
		{
			Db::name('mechanical_model')->where('id',$id)->delete();
			$this->success('删除成功',url('mechanicalmodel'),1);
		}

		$this->success('删除失败',url('mechanicalmodel'),1);
	}


	//型号管理
	public function mechanicalmodel()
	{
		$key = input('key');
		if($key != '')
		{
			$map = [
				'model_name' => ['like',"%$key%"]
			];
		}

		//查询数据列表
		$mechanical_model = Db::name('mechanical_model')->alias('a')->field('a.*,b.mechanical_name,c.brand_name')->join('mechanical b','a.mechanical_id=b.id','LEFT')->join('brand c','a.brand_id=c.id','LEFT')->where($map)->order('a.id DESC')->paginate(15);
		$list = $mechanical_model->items();
		$page = $mechanical_model->render();
		$this->assign('page',$page);
		$this->assign('list',$list);
		return $this->fetch();
	}

	//型号添加
	public function mechanicalmodelAdd()
	{
		if(request()->isAjax())
		{
			$data = [
				'model_name' => input('model_name'),
				'mechanical_id' => input('mechanical_id'),
				'brand_id' => input('brand_id'),
				'update_time' => time(),
				'create_time' => time(),
			];
			Db::name('mechanical_model')->insert($data);
			$this->success('添加成功',url('mechanicalmodel'),1);
		}
		else
		{
			$mechanical_list = Db::name('mechanical')->field('id,mechanical_name')->select();
			// var_dump($mechanical_list);exit;
			$this->assign('mechanical_list',$mechanical_list);
			return $this->fetch();
		}
	}


	//型号修改
	public function mechanicalmodelEdit()
	{
		$id = input('id');
		if(request()->isAjax())
		{
			$data = [
				'model_name' => input('model_name'),
				'mechanical_id' => input('mechanical_id'),
				'brand_id' => input('brand_id'),
				'update_time' => time(),
				'create_time' => time(),
			];
			Db::name('mechanical_model')->insert($data);
			$this->success('添加成功',url('mechanicalmodel'),1);
		}
		else
		{
			$info = Db::name('mechanical_model')->where('id',$id)->find();
			$mechanical_list = Db::name('mechanical')->field('id,mechanical_name')->select();
			$this->assign('mechanical_list',$mechanical_list);
			return $this->fetch();
		}
	}

	//根据机械类型获取品牌
	public function getBrand()
	{
		$mechanical_id = input('mechanical_id');
		$map = [
			'mechanical_id' => $mechanical_id
		];
		$list = Db::name("mech_ascc")->alias('a')->field('b.*')->join('brand b','a.brand_id=b.id','LEFT')->where($map)->select();

		$html = '<option value="">请选择</option>';
		foreach($list as $key => $value)
		{
			$html .= "<option value=\"{$value['id']}\">{$value['brand_name']}</option>";
		}
		echo $html;exit;

	}



}