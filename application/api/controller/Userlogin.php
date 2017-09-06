<?php
// +----------------------------------------------------------------------
// | 功能：登录注册绑定功能
// +----------------------------------------------------------------------
// | 作者：汪洋
// +----------------------------------------------------------------------
// | 日期：2017-7-21
// +----------------------------------------------------------------------
namespace app\api\controller;

use app\common\controller\Common;
use think\Db;
use think\Request;
use org\Stringnew;
use think\Cookie;
use think\Validate;

class Userlogin extends Common
{

    /**
     * 发送短信验证码
     */
    public function sendSMS()
    {
        $telphone = input('PhoneNumber');
        if (!$telphone) {
            $this->make_json_error('手机号不能为空！', 1);
        }
        if (!preg_match('/^1[3456789]{1}\d{9}$/', $telphone)) {
            $this->make_json_error('手机号不正确！', 1);
        }
        $verification_code = rand(100000, 999999);
        $content = '【宝宝机械】您的验证码为' . $verification_code . '，请在5分钟内完成验证，若非本人操作请忽略此消息。';
        $url = 'https://api.smsbao.com/sms';
        $param['u'] = SMSUSER;
        $param['p'] = md5(SMSPASSWORD);
        $param['m'] = $telphone;
        $param['c'] = $content;
        $res = http_post($url, $param);
        if ($res == 0) {
            $data = array(
                'telphone' => $telphone,
                'type' => 'register',
                'content' => $content,
                'verification_code' => $verification_code,
                'addtime' => time()
            );
            Db::name('sms_log')->insert($data);
            Cookie::set('verification_code', $verification_code, 300);
            $this->make_json_result('发送成功', array('verification_code' => $verification_code));
        } elseif ($res == 41) {
            $this->make_json_error('短信包余额不足', 1);
        }
    }

    /**
     * 发送验证码(测试)
     */
    public function sendSMSCode()
    {
        $telphone = input('PhoneNumber');
        if (!$telphone) {
            $this->make_json_error('手机号不能为空！', 1);
        }
        if (!preg_match('/^1[3456789]{1}\d{9}$/', $telphone)) {
            $this->make_json_error('手机号不正确！', 1);
        }
        $verification_code = rand(100000, 999999);
        $content = '【宝宝机械】您的验证码为' . $verification_code . '，请在5分钟内完成验证，若非本人操作请忽略此消息。';
        $data = array(
            'telphone' => $telphone,
            'type' => 'register',
            'content' => $content,
            'verification_code' => $verification_code,
            'addtime' => time()
        );
//        $encrypted = encrypt($verification_code);
        Db::name('sms_log')->insert($data);
        Cookie::set('verification_code', $verification_code, 300);
        $this->make_json_result('发送成功', array('verification_code' => $verification_code));
    }

    /**
     * 注册
     */
    public function UserRegister()
    {
        $telphone = input('PhoneNumber');
        $verification_code = input('validateCode');
        $password = input('PassWord');
        $password = decrypt($password);
        if (!preg_match('/^1[3456789]{1}\d{9}$/', $telphone)) {
            $this->make_json_error('手机号不正确！', 1);
        }
        $sms_log_db = Db::name('sms_log');
        $code = $sms_log_db->where("telphone='$telphone' AND type='register'")->order('addtime DESC')->limit(1)->find();
        //判断验证码是否过期
        if ((time() - $code['addtime']) > 300) {
            $this->make_json_error('验证码已过期！', 1);
        }
        //判断验证码是否正确
        if ($code['verification_code'] !== $verification_code) {
            $this->make_json_error('验证码不正确！', 1);
        }

        if (!preg_match('/^[a-zA-Z0-9]*$/', $password)) {
            $this->make_json_error('您输入的密码格式有误请重新输入！', 1);
        }
        if (strlen($password) < 6 || strlen($password) > 20) {
            $this->make_json_error('请将密码限制在6-20位！', 1);
        }

        $member_list_db = Db::name('member_list');

        $is_only = $member_list_db->where("member_list_tel='$telphone'")->count();
        if ($is_only) {
            $this->make_json_error('手机号已存在！', 1);
        }
        $sys = Db::name('options')->where(array('option_name' => 'site_options'))->value("option_value");
        $sys = json_decode($sys, true);
        $member_list_salt = Stringnew::randString(10);
        $sl_data = array(
            'member_list_tel' => $telphone,
            'member_list_salt' => $member_list_salt,
            'member_list_pwd' => encrypt_password($password, $member_list_salt),
            'member_list_open' => 1,
            'member_list_addtime' => time(),
            'member_list_headpic' => $sys['site_logo']
        );
        Db::name('member_list')->insertGetId($sl_data);
        $this->make_json_result('注册成功');
    }


    /**
     * 登陆
     */
    public function UserLogin()
    {
        $telphone = input('PhoneNumber');
        $password = input('PassWord');
        $password = decrypt($password);
        if (!$telphone) {
            $this->make_json_error('手机号不能为空！', 1);
        }
        if (!$password) {
            $this->make_json_error('密码不能为空！', 1);
        }
        $userinfo = Db::name('member_list')->where(['member_list_tel' => $telphone])->find();
        if ($userinfo['user_status'] == 0) {
            $this->make_json_error('账户已被冻结禁止登陆', 1);
        }
        if (!$userinfo || $userinfo['member_list_pwd'] !== encrypt_password($password, $userinfo['member_list_salt'])) {
            $this->make_json_error('账号或密码错误，请重新输入', 2);
        }
//        $encrypted=encrypt($userinfo['member_list_id']);
        cookie('member_list_id', $userinfo['member_list_id'], 365 * 86400);
        $this->make_json_result('登陆成功', array('cookie' => $userinfo['member_list_id']));
    }



    /**
     * 密码重置
     */
    public function FindPassword()
    {
        $telphone = input('PhoneNumber');
        $verification_code = input('validateCode');
//        $password = input('Password');
        $password = decrypt(input('PassWord'));
        t($password);
        if (!preg_match('/^1[3456789]{1}\d{9}$/', $telphone)) {
            $this->make_json_error('手机号不正确！', 1);
        }
        $is_only = Db::name("member_list")->where("member_list_tel='$telphone'")->find();
        if (!$is_only) {
            $this->make_json_error('账户不存在！', 1);
        }
        if (!$password) {
            $this->make_json_error('密码不能为空！', 1);
        }
        $sms_log_db = Db::name('sms_log');

        $code = $sms_log_db->where('telphone',$telphone)->order('addtime DESC')->limit(1)->find();

        //判断验证码是否过期
        if ((time() - $code['addtime']) > 300) {
            $this->make_json_error('验证码已过期！', 1);
        }
        //判断验证码是否正确
        if ($code['verification_code'] !== $verification_code) {
            $this->make_json_error('验证码不正确！', 1);
        }

        if (!preg_match('/^[a-zA-Z0-9]*$/', $password)) {
            $this->make_json_error('您输入的密码格式有误请重新输入！', 1);
        }
//        t($password);
        if (strlen($password) < 6 || strlen($password) > 20) {
            $this->make_json_error('请将密码限制在6-20位！', 1);
        }
        $data = array(
            'member_list_pwd' => encrypt_password($password, $is_only['member_list_salt'])
        );
        Db::name('member_list')->where('member_list_id', $is_only['member_list_id'])->update($data);

        $this->make_json_result('重置密码成功', []);
    }


    //绑定未注册手机号
    public function BangThreePhone()
    {
        $telphone = input('PhoneNumber');
        $password = input('Password');
        $verification_code = input('validateCode');
        $OauthUserId = input('OauthUserId');
        $member_db = Db::name('member_list');
        if (!preg_match('/^1[3456789]{1}\d{9}$/', $telphone)) {
            $this->make_json_error('手机号不正确！', 1);
        }
        $sms_log_db = Db::name('sms_log');
        $code = $sms_log_db->where(array('telphone' => $telphone, 'type' => 'register'))->order('addtime DESC')->limit(1)->find();
        //判断验证码是否过期
        if ((time() - $code['addtime']) > 9000) {
            $this->make_json_error('验证码已过期！', 1);
        }
        //判断验证码是否正确
        if ($code['verification_code'] !== $verification_code) {
            $this->make_json_error('验证码不正确', 1);
        }
        //判断该手机是否在平台上注册过
        $re = Db::name('member_list')->where('member_list_tel', $telphone)->find();
        if ($re) {
            $this->make_json_error('该手机已注册', 1);
        }
        //判断密码
        if (strlen($password) < 6 || strlen($password) > 20) {
            $this->make_json_error('请将密码限制在6-20位！', 1);
        }
        //判断邀请码
        $invitation_code = input('appointmentCode');//邀请码
        if ($invitation_code) {
            $parentid = Db('member_list')->where('invitation_code', $invitation_code)->value('member_list_id');
            if (!$parentid) {
                $this->make_json_error('邀请码不存在', 1);
            }
        }

        //数据写入
        $sys = Db::name('options')->where(array('option_name' => 'site_options'))->value("option_value");
        $sys = json_decode($sys, true);
        $member_list_salt = Stringnew::randString(10);

        $sl_data = array(
            'parent_id' => $invitation_code ? $parentid : 0,
            'invitation_code' => $this->InvitationCode(),
            'member_list_tel' => $telphone,
            'member_list_salt' => $member_list_salt,
            'member_list_pwd' => encrypt_password($password, $member_list_salt),
            'member_list_open' => 1,
            'member_list_addtime' => time(),
            'member_list_headpic' => $sys['site_logo']
        );
        Db::name('member_list')->insert($sl_data);
        $member_list_id = Db::name('member_list')->getLastInsID();
        $res = Db::name('oauth_user')->where('id', $OauthUserId)->update(array('uid' => $member_list_id));
        cookie('member_list_id', $member_list_id, 365 * 86400);
        $info = Db::name('member_list')->field('member_list_id,member_list_headpic')->where('member_list_id', $member_list_id)->find();
        if ($res) {
            cookie('member_list_id', $info['member_list_id'], 365 * 86400);
            $this->make_json_result('绑定成功');
        } else {
            $this->make_json_error('绑定失败！联系管理员。', 1);
        }

    }

    //绑定已有账号
    public function bangThreeFinshRester()
    {
        $telphone = input('PhoneNumber');
        $password = input('Password');
        $OauthUserId = input('OauthUserId');
        if (!$telphone) {
            $this->make_json_error('手机号不能为空！', 2);
        }
        if (!$password) {
            $this->make_json_error('密码不能为空！', 1);
        }
        $userinfo = Db::name('member_list')->where(['member_list_tel' => $telphone])->find();

        if (!$userinfo || $userinfo['member_list_pwd'] !== encrypt_password($password, $userinfo['member_list_salt'])) {
            $this->make_json_error('账号或密码错误，请重新输入', 2);
        }
        cookie('member_list_id', $userinfo['member_list_id'], 365 * 86400);
        //$info = Db::name('member_list')->field('member_list_id AS userId')->where('member_list_id',$userinfo['member_list_id'])->find();
        $re = DB::name('oauth_user')->where('uid', $userinfo['member_list_id'])->find();
        if ($re) {
            $this->make_json_error('该手机号已经在本站绑定', 1);
        }
        $res = Db::name('oauth_user')->where('id', $OauthUserId)->update(array('uid' => $userinfo['member_list_id']));
        if ($res) {
            $this->make_json_result('绑定成功', array('userId' => $userinfo['member_list_id']));
        } else {
            $this->make_json_error('绑定失败联系管理员', 1);
        }
    }

    //发送短信获取验证码
    public function GetVerificationCode()
    {
        $telphone = input("telphone");
        $type = input('type');
        $member_list_db = Db::name('member_list');
        if ($type == 1 || $type == 3) {
            $is_only = $member_list_db->where("member_list_tel='$telphone'")->count();
            if ($is_only) {
                $this->make_json_error('手机已注册', 111);
            }

            $verification_code = rand(1000, 9999);
            $mobile = $telphone;
            $content = '您的验证码是：' . $verification_code . ',15分钟内有效。';

            // $res = $this->smsSend($mobile,$content,$type,$sendTime = '');
            // $res = \org\Aliyun::reg_sendsms($mobile,$verification_code);
            $res['errcode'] = 0;

            if ($res['errcode'] === 0) {
                $sms_log = Db::name('sms_log');
                $data['telphone'] = $mobile;
                $data['type'] = $type;
                $data['content'] = $content;
                $data['verification_code'] = $verification_code;
                $data['addtime'] = time();
                $sms_log->insert($data);
                $this->make_json_result('发送成功', array('yzcode' => $verification_code));
            } else {
                $this->make_json_error('发送失败', 101);
            }
        } elseif ($type == 2 || $type == 4) {

            if (!preg_match('/^1[3456789]{1}\d{9}$/', $telphone)) {
                $this->make_json_error('手机号不正确！', 1);
            }

            $is_only = $member_list_db->where("member_list_tel='$telphone'")->count();
            if (!$is_only) {
                $this->make_json_error('账户不存在！', 1);
            }

            $verification_code = rand(1000, 9999);
            $mobile = $telphone;
            $content = '您的验证码是：' . $verification_code . ',15分钟内有效。';

            // $res = $this->smsSend($mobile,$content,$type,$sendTime = '');
            // $res = \org\Aliyun::reg_sendsms($mobile,$verification_code);
            $res['errcode'] = 0;

            if ($res['errcode'] === 0) {
                $sms_log = Db::name('sms_log');
                $data['telphone'] = $mobile;
                $data['type'] = $type;
                $data['content'] = $content;
                $data['verification_code'] = $verification_code;
                $data['addtime'] = time();
                $sms_log->insert($data);
                $this->make_json_result('发送成功', array('code' => $verification_code));
            } else {
                $this->make_json_error('发送失败', 101);
            }
        }

        $this->make_json_error('发送失败', 100);
    }


//邀请码生成方法
    public function InvitationCode()
    {
        $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $rand = $code[rand(0, 25)]

            . strtoupper(dechex(date('m')))

            . date('d') . substr(time(), -5)

            . substr(microtime(), 2, 5)

            . sprintf('%02d', rand(0, 99));

        for (

            $a = md5($rand, true),

            $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',

            $d = '',

            $f = 0;

            $f < 8;

            $g = ord($a[$f]),

            $d .= $s[($g ^ ord($a[$f + 8])) - $g & 0x1F],

            $f++

        ) ;

        return $d;
    }

    //第三方登录
    public function TripartiteUserLogin()
    {
        $openid = input('openId');    //用户唯一标识
        $type = input('type') == 1 ? 'qq' : 'wechat';        //1QQ,2微信
        $head = input('head');        //用户头像
        $nickname = input('nickname');    //用户昵称
        $sex = input('sex');        //用户性别
        //查询用户数据
        $oauth = Db::name('oauth_user')->where(array('openid' => $openid, 'oauth_from' => $type))->find();
        if ($oauth) {
            if ($oauth['uid']) {
                //设置cookie
                cookie('userId', $oauth['uid'], 365 * 86400);
            }
            $result = [
                'userid' => $oauth['uid'],
                'oauth_user_id' => $oauth['id']
            ];
        } else {
            $data = [];
            $data['oauth_from'] = $type;
            $data['name'] = $nickname;
            $data['head_img'] = $head;
            $data['create_time'] = date("Y-m-d H:i:s");
            $data['openid'] = $openid;
            Db::name('oauth_user')->insert($data);
            $result = [
                'userid' => 0,
                'OauthUserId' => Db::name('oauth_user')->getLastInsId()
            ];
        }

        $this->make_json_result('登录结果获取成功', $result);
    }


}