<?php
// +----------------------------------------------------------------------
// | 功能：微信公众号入口
// +----------------------------------------------------------------------
// | 作者：程龙飞
// +----------------------------------------------------------------------
// | 日期：2017-5-2
// +----------------------------------------------------------------------
namespace app\wechat\controller;

use think\Controller;
use think\Db;
class Weixin extends Controller
{
	/*
	 * 微信入口
	 *
	 */
    public function index()
    {
    	$Weixin = new \wechat\Weixin();
		$msg_type = $Weixin->getType();
		//事件消息
		if($msg_type == 'event')
		{
			//事件类型
			$event = $Weixin->getEvent();
			//如果是关注事件
			if($event == 'subscribe')
			{
				$weixin_info = Db::name('weixin')->where("id=1")->find();
				$appid = $weixin_info['appid'];
				$appsecret = $weixin_info['appsecret'];
		        $access_token = get_access_token($appid,$appsecret);
				$openid = $Weixin->getFromUserName();
				$url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
				$result = http_get($url);
				if($result)
				{
					$wechat_info = json_decode($result,true);
					if (isset($wechat_info['errcode']))
					{
						$this->errCode = $wechat_info['errcode'];
						$this->errMsg = $wechat_info['errmsg'];
						return false;
					}
					$user = Db::name('oauth_user')->where(array('openid'=>$openid))->find();
					if($user)
					{
						$data['oauth_from'] = 'wechat';
						$data['name'] = $wechat_info['nickname'];
						$data['head_img'] = $wechat_info['headimgurl'];
						$data['openid'] = $openid;
						$data['last_login_time'] = date("Y-m-d H:i:s");
						$data['last_login_ip'] = request()->ip();
						$data['create_time'] = date("Y-m-d H:i:s");
						$data['city'] = $wechat_info['city'];
						$data['province'] = $wechat_info['province'];
						$data['country'] = $wechat_info['country'];
						$data['subscribe_time'] = $wechat_info['subscribe_time'];
						Db::name('oauth_user')->where(array('openid'=>$openid))->update($data);
					}
					else
					{
						$data['oauth_from'] = 'wechat';
						$data['name'] = $wechat_info['nickname'];
						$data['head_img'] = $wechat_info['headimgurl'];
						$data['openid'] = $openid;
						$data['last_login_time'] = date("Y-m-d H:i:s");
						$data['last_login_ip'] = request()->ip();
						$data['create_time'] = date("Y-m-d H:i:s");
						$data['city'] = $wechat_info['city'];
						$data['province'] = $wechat_info['province'];
						$data['country'] = $wechat_info['country'];
						$data['subscribe_time'] = $wechat_info['subscribe_time'];
						Db::name('oauth_user')->insert($data);
					}
				}
				$Weixin->makeText('欢迎关注哦O(∩_∩)O~！');	
			}
			//取消关注时间
			elseif($event == 'unsubscribe')
			{
				// $map = array(
				// 		'openid' => $openid,
				// 		'oauth_from' => 'wechat'
				// 	);
				// M('oauth_user')->where($map)->save(array('subscribe_time' => 0));

			}
			elseif($event == '')
			{
				
			}
		}
		//文本消息
		elseif($msg_type == 'text')
		{
			$Weixin->makeText('谢谢关注O(∩_∩)O~！');
		}
		//图片消息
		elseif($msg_type == 'image')
		{

		}
    }

    public function test()
	{
				
	}
}
