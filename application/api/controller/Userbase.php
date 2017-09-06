<?php
// +----------------------------------------------------------------------
// | 功能：基础后台
// +----------------------------------------------------------------------
// | 作者：程龙飞
// +----------------------------------------------------------------------
// | 日期：2017-5-2
// +----------------------------------------------------------------------
namespace app\api\controller;

use app\common\controller\Common;
class Userbase extends Common
{
	public $member_list_id;
	//初始化
	public function _initialize(){
        parent::_initialize();
        $member_list_id = cookie('member_list_id');
 		if(!$member_list_id)
 		{
 			$this->make_json_error('请登录',999);
 		}
 		
 		$this->member_list_id = $member_list_id;
	}
}
