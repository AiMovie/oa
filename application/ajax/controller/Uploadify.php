<?php
// +----------------------------------------------------------------------
// | 功能：图片上传处理
// +----------------------------------------------------------------------
// | 作者：程龙飞
// +----------------------------------------------------------------------
// | 日期：2017-5-9
// +----------------------------------------------------------------------
namespace app\ajax\controller;

use app\common\controller\Common;
use think\Db;
class Uploadify extends Common
{

	/*
	*	@上传图片
	*	@参数1 	$title	标题
	*	@参数2 	$type	上传类型
	*	缩略图上传/image/image/1/500000/uploadify/picurl/undefined
	*/	
	public function uploads()
	{
		$title = input('title','');		//标题
		$type = input('type','');		//上传类型
		$path = input('dir','');		//上传的文件夹
		$num = input('num',0);				//上传个数
		$size = input('size',0);				//最大size大小
		$frame = input('frame','');		//iframe的ID
		$input = input('input','');	//父框架保存图片地址的input的id
		$desc=$type;													//类型描述
		
		$title = urldecode($title);		
		
		$units = array(3=>'G',2=>'M',1=>'KB',0=>'B');//单位字符,可类推添加更多字符.
		$size_str='';
		foreach($units as $i => $unit){
			if($i>0){
                $n = $size / pow(1024,$i) % pow(1024,$i);			   
			}else{
                $n = $size;
			}                
			if($n!=0){
                $str.=" $n{$unit} ";
				if(!$xi)
					$size_str;
			}			
		}
		$size_str;

		$this->assign('title',$title);
		$this->assign('type',$type);
		$this->assign('uptype','*.png;*.jpg;*.gif;*.jpeg;');
		$this->assign('path',$path);
		$this->assign('num',$num);
		$this->assign('size',$size);
		$this->assign('size_str',$size_str);
		$this->assign('frame',$frame);
		$this->assign('input',$input);
		$this->assign('desc',$desc);
		$this->assign('timestamp',time());

		return $this->fetch();
	}

	public function insert()
	{

		$file = request()->file('Filedata');
		if($file)
		{
		    // 移动到框架应用根目录/public/data/uploads/ 目录下
		    $info = $file->move(ROOT_PATH . 'public/data/uploads');
			if($info) {
				$img_url=substr(\think\Config::get('UPLOAD_DIR'),1).$info->getSaveName();//如果上传成功则完成路径拼接
				$result = array(
						'code' => 0,
						'msg' => $img_url
					);
			}else{
				$result = array(
						'code' => 1,
						'msg' => $file->getError()
					);
			}			
		}
		else
		{
				$result = array(
						'code' => 2,
						'msg' => '没有上传文件'
					);
		}

		echo json_encode($result);exit;
	}

	public function delupload()
	{
		$action = input('action',''); 
		$filename = urldecode(input('filename',''));

		if($action && $filename)
		{
			$linkurl = ROOT_PATH.'public'.$filename;
			if(is_file($linkurl) && file_exists($linkurl))
			{
				unlink($linkurl);
			}
			
		}

	}
}
