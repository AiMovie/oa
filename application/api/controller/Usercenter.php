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
use app\api\controller\Userbase;
use think\Db;
use think\Request;
use org\Stringnew;
use think\Cookie;
use think\Validate;

class Usercenter extends Userbase
{

    /**
     * 修改昵称
     */
    public function editName()
    {
        $id=Cookie::get('member_list_id');
        $member_list_nickname = input('nickname');
        if(!$member_list_nickname){
            $this->make_json_error('昵称不能为空', 1);
        }
        $res=DB::name('member_list')->where('member_list_id',$id)->setField('member_list_nickname', $member_list_nickname);
        if($res){
            $this->make_json_result('昵称修改成功');
        }
    }


    /**
     * 修改密码
     */
    public function editPassword()
    {
        $id=Cookie::get('member_list_id');
        $oldPassword = decrypt(input('oldPassword'));
        $newPassword = decrypt(input('newPassword'));
        if(!$oldPassword){
            $this->make_json_error('请输入原始密码', 1);
        }
        if(!$newPassword){
            $this->make_json_error('请输入新密码', 1);
        }
        if (!preg_match('/^[a-zA-Z0-9]*$/', $newPassword)) {
            $this->make_json_error('您输入的密码格式有误请重新输入！', 1);
        }
        if (strlen($newPassword) < 6 || strlen($newPassword) > 20) {
            $this->make_json_error('请将密码限制在6-20位！', 1);
        }
        $userinfo=DB::name('member_list')->where('member_list_id',$id)->find();
        $data=encrypt_password($oldPassword, $userinfo['member_list_salt']);
        if ($userinfo['member_list_pwd'] !== $data) {
            $this->make_json_error('原始密码不正确，请重新输入', 1);
        }
        $res=DB::name('member_list')->where('member_list_id',$id)->setField('member_list_pwd', encrypt_password($newPassword, $userinfo['member_list_salt']));
        if($res){
            $this->make_json_result('密码修改成功');
        }
    }

}