<?php
namespace app\api\controller;
use app\common\controller\Common;
use think\Db;
use think\Request;
use org\Stringnew;
use think\Cookie;
use app\index\model\Weixin;
class Ads extends Common
{
	 // $this->make_json_error('手机号已存在！',1);
	// $this->make_json_result('绑定成功',array('userId'=>$userinfo['member_list_id']));
	// 首页banner
	public function  banner()
	{	
		$type =  input('type'); 

		switch ($type) {
			//首页banner
			case '1':
				// $this->make_json_result('asd',array('data'=>$type));
				$where = [
							'plug_ad_adtypeid'=>1,
						 ];
				$ads_info = Db::name('plug_ad')
							->where($where)
							->where('plug_ad_starttime','<', time())
							->where('plug_ad_endtime','>', time())
							->field('plug_ad_name, plug_ad_checkid, plug_ad_pic, plug_ad_content, plug_ad_object_id')
							->select();
							 // $this->make_json_error('手机号已存在！',1);
							
				if($ads_info){
					$this->make_json_result('首页banner',$ads_info);
				}else{
					 $this->make_json_error('没有数据',1);
				}
				break;
				// 中部第一个广告位
			case '2':
				$where = [
							'plug_ad_adtypeid'=>2,
						 ];
				$ads_info = Db::name('plug_ad')
							->where($where)
							->where('plug_ad_starttime','<', time())
							->where('plug_ad_endtime','>', time())
							->field('plug_ad_name, plug_ad_checkid, plug_ad_pic, plug_ad_content, plug_ad_object_id')
							->select();
				if($ads_info){
					$this->make_json_result(' 中部第一个广告位',$ads_info);
				}else{
					 $this->make_json_error('没有数据',1);
				}
				break;
			// 中部第二个广告位
			case '3':
				$where = [
							'plug_ad_adtypeid'=>3,
						 ];
				$ads_info = Db::name('plug_ad')
							->where($where)
							->where('plug_ad_starttime','<', time())
							->where('plug_ad_endtime','>', time())
							->field('plug_ad_name, plug_ad_checkid, plug_ad_pic, plug_ad_content, plug_ad_object_id')
							->select();
				if($ads_info){
					$this->make_json_result('中部第二个广告位',$ads_info);
				}else{
					 $this->make_json_error('没有数据',1);
				}
			break;
			// 中部第三个广告位
			case '4':
				$where = [
							'plug_ad_adtypeid'=>4,
						 ];
				$ads_info = Db::name('plug_ad')
							->where($where)
							->where('plug_ad_starttime','<', time())
							->where('plug_ad_endtime','>', time())
							->field('plug_ad_name, plug_ad_checkid, plug_ad_pic, plug_ad_content, plug_ad_object_id')
							->select();
				if($ads_info){
					$this->make_json_result('中部第三个广告位',$ads_info);
				}else{
					 $this->make_json_error('没有数据',1);
				}
			break;
			// 中部第4个广告位
			case '5':
				$where = [
							'plug_ad_adtypeid'=>5,
						 ];
				$ads_info = Db::name('plug_ad')
							->where($where)
							->where('plug_ad_starttime','<', time())
							->where('plug_ad_endtime','>', time())
							->field('plug_ad_name, plug_ad_checkid, plug_ad_pic, plug_ad_content, plug_ad_object_id')
							->select();
				if($ads_info){
					$this->make_json_result('中部第4个广告位',$ads_info);
				}else{
					 $this->make_json_error('没有数据',1);
				}
			break;
			default:
				# code...
				break;
		}
		die;
	
	}
    
	public function _ads_type($type,$tablename){
		$where = [
					'plug_ad_adtypeid'=>2,
				];
		$ads_info = Db::name('plug_ad')
					->where($where)
					->where('plug_ad_starttime','<', time())
					->where('plug_ad_endtime','>', time())
					->select();
		
	}   
}
