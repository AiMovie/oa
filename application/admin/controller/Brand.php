<?php
/**
 * 功能：品牌管理
 * 修改时间：2017-6-28
 * 修改人：程龙飞
 */
namespace  app\admin\controller;

use app\common\controller\Auth;
use think\Session;
use think\Db;
use org\Stringnew;
class Brand extends Auth
{
	public function index()
	{
		$key = input('key');
		if($key != '')
		{
			$map = [
				'brand_name' => ['like',"%$key%"]
			];
		}

		//查询数据列表
		$brand = Db::name('brand')->where($map)->order('sort_order ASC,id DESC')->paginate(15);
		$list = $brand->items();
		$page = $brand->render();
		$this->assign('page',$page);
		$this->assign('list',$list);
		return $this->fetch();
	} 

	//品牌添加
	public function brandAdd()
	{
		if(request()->isAjax())
		{
			//logo处理
			$file = request()->file('file0');
			if($file)
			{
			    // 移动到框架应用根目录/public/data/uploads/ 目录下
			    $info = $file->move(ROOT_PATH . 'public' . DS . 'data'. DS . 'uploads');
				if($info) {
					$img_url=substr(\think\Config::get('UPLOAD_DIR'),1).$info->getSaveName();//如果上传成功则完成路径拼接
				}else{
					$this->error($file->getError(),url('Brand/brandAdd'),0);//否则就是上传错误，显示错误原因
				}
			}
			$data = [
				'brand_name' => input('brand_name'),
				'logo' => $img_url,
				'sort_order' => input('sort_order'),
				'update_time' => time(),
				'create_time' => time(),
			];

			Db::name('brand')->insert($data);
			$this->success('添加成功',url('index'),1);

		}
		else
		{
			return $this->fetch();
		}
	}

	//品牌修改
	public function brandEdit()
	{
		$id = input('id');
		$map = [
			'id' => $id,
		];
		if(request()->isAjax())
		{
			$data = [
				'brand_name' => input('brand_name'),
				'sort_order' => input('sort_order'),
				'update_time' => time(),
			];
			//图片处理
			$checkpic=input('checkpic');
			$oldcheckpic=input('oldcheckpic');
			//如果有新上传
			if($checkpic!=$oldcheckpic)
			{
				$file = request()->file('file0');
				if($file)
				{
				    // 移动到框架应用根目录/public/data/uploads/ 目录下
				    $info = $file->move(ROOT_PATH . 'public' . DS . 'data'. DS . 'uploads');
					if($info) {
						$img_url=substr(\think\Config::get('UPLOAD_DIR'),1).$info->getSaveName();//如果上传成功则完成路径拼接
					}else{
						$this->error($file->getError(),url('Brand/index'),0);//否则就是上传错误，显示错误原因
					}
					if(is_file(ROOT_PATH . 'public' .$checkpic) && file_exists(ROOT_PATH . 'public' .$checkpic))
					{
						unlink(ROOT_PATH . 'public' .$checkpic);
					}
					$data['logo'] = $img_url;
				}
			}

			Db::name('brand')->where($map)->update($data);
			$this->success('修改成功',url('Brand/index'),1);
		}
		else
		{
			$info = Db::name('brand')->where($map)->find();
			$this->assign('info',$info);
			return $this->fetch();
		}
	}

	//品牌删除
	public function brandDelete()
	{
		$id = input('id');

		$info = Db::name("brand")->where('id',$id)->find();
		if($info)
		{
			Db::name('brand')->where('id',$id)->delete();
			$this->success('删除成功',url('index'),1);
		}

		$this->success('删除失败',url('index'),1);
	}


}