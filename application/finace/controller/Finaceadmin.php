<?php
/*
* FinaceAdminController.class.php
* 后台财务管理控制器，管理资金账号，资金记录，充值设置等操作
* 创建人：程龙飞
* 创建时间：2017-5-9
* 最后编辑人：程龙飞
* 编辑时间：2017-5-9 09:49:26
*/
namespace  app\finace\controller;
use app\common\controller\Auth;
use think\Db;
class Finaceadmin extends Auth {

	//资金账户列表
    public function index(){
		if(I('keyword'))
        {
            $map['member_list_username'] = array('like','%'.I('keyword').'%');
            $map['member_list_nickname'] = array('like','%'.I('keyword').'%');
            $map['member_list_realname'] = array('like','%'.I('keyword').'%');
            $map['member_list_tel'] = array('like','%'.I('keyword').'%');
            $map['_logic'] = 'or';
        }

        $member_lit_db = M("member_list");
        $count= $member_lit_db->where($map)->count();
        $Page= new \Think\Page($count,C('DB_PAGENUM'));
        $list = $member_lit_db->field('member_list_id,member_list_username,member_list_tel,money,frozen_money,is_service,member_type,member_list_realname')->where($map)->order('member_list_id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        $show = $Page->show();
        $this->assign('member_list',$list);
        $this->assign('page',$show);
        $this->assign('keyword',I('keyword'));

		$this->display();
    }

    //资金增减
    public function moneyEdit()
    {
        $member_list_id = I("member_list_id");

        $member_list_db = M("member_list");
        $member_info = $member_list_db->where("`member_list_id`='$member_list_id'")->find();
        if(!$member_info)
        {
            $this->error("用户不存在！",U("index"),1);
        }
        $this->assign('info',$member_info);
        $this->display();
    }

    //资金编辑操作
    public function moneyEditrun()
    {
        if(IS_AJAX)
        {
            $member_list_id = I("member_list_id");
            $edit_money = I('edit_money',0,'intval');
            if($edit_money === 0)
            {
                $this->error("变动金额不能为0");
            }
            $type = I('type');
            $member_list_db = M("member_list");

            $member_info = $member_list_db->where("`member_list_id`='$member_list_id'")->find();
            switch ($type) {
                case 1:
                    $data['member_list_id'] = $member_list_id;
                    $data['income'] = $edit_money;
                    $data['expenditure'] = 0;
                    $data['frozen_inc'] = 0;
                    $data['frozen_dec'] = 0;
                    $data['fund_change_type'] = 'station_recharge';
                    $data['fund_type'] = 'station_balance';
                    $data['remark'] = I("remark");

                    $capital = new CapitalController();

                    $resutl = $capital->addCapitalLog($data);
                    if($resutl)
                    {
                        $this->success('操作成功',U('index'),1);
                    }
                break;
                case 2:
                    $member_list_info = $member_list_db->field('money,frozen_money')->where("member_list_id='$member_list_id'")->find();
                    if(!member_info)
                    {
                        $this->errpr('用户不存在！');
                    }
                    if ($edit_money > $member_info['money']) {
                        $this->error('用户余额不足！');
                    }
                    $data['member_list_id'] = $member_list_id;
                    $data['income'] = 0;
                    $data['expenditure'] = $edit_money;
                    $data['frozen_inc'] = 0;
                    $data['frozen_dec'] = 0;
                    $data['fund_change_type'] = 'station_recharge';
                    $data['fund_type'] = 'station_balance';
                    $data['remark'] = I("remark");

                    $capital = new CapitalController();

                    $resutl = $capital->addCapitalLog($data);
                    if($resutl)
                    {
                        $this->success('操作成功',U('index'),1);
                    }
                break;                
                case 3:
                    $member_list_info = $member_list_db->field('money,frozen_money')->where("member_list_id='$member_list_id'")->find();
                    if(!member_info)
                    {
                        $this->errpr('用户不存在！');
                    }
                    if ($edit_money > $member_info['money']) {
                        $this->error('用户余额不足！');
                    }
                    $data['member_list_id'] = $member_list_id;
                    $data['income'] = 0;
                    $data['expenditure'] = $edit_money;
                    $data['frozen_inc'] = $edit_money;
                    $data['frozen_dec'] = 0;
                    $data['fund_change_type'] = 'station_recharge';
                    $data['fund_type'] = 'station_balance';
                    $data['remark'] = I("remark");

                    $capital = new CapitalController();

                    $resutl = $capital->addCapitalLog($data);
                    if($resutl)
                    {
                        $this->success('操作成功',U('index'),1);
                    }
                break;                
                case 4:
                    $member_list_info = $member_list_db->field('money,frozen_money')->where("member_list_id='$member_list_id'")->find();
                    if(!member_info)
                    {
                        $this->errpr('用户不存在！');
                    }
                    if ($edit_money > $member_info['frozen_money']) {
                        $this->error('冻结金额不足！');
                    }
                    $data['member_list_id'] = $member_list_id;
                    $data['income'] = 0;
                    $data['expenditure'] = 0;
                    $data['frozen_inc'] = 0;
                    $data['frozen_dec'] = $edit_money;
                    $data['fund_change_type'] = 'station_recharge';
                    $data['fund_type'] = 'station_balance';
                    $data['remark'] = I("remark");

                    $capital = new CapitalController();

                    $resutl = $capital->addCapitalLog($data);
                    if($resutl)
                    {
                        $this->success('操作成功',U('index'),1);
                    }
                break;                
                
                default:
                    # code...
                    break;
            }

        }
    }
    //资金记录
    public function priceLog(){
		
        if(I('keyword'))
        {
            $map['b.member_list_username|b.member_list_tel'] = array('like','%'.I('keyword').'%');
        }
        if(I('member_list_id'))
        {
            $map['a.member_list_id'] = I('member_list_id');
        }
        $acount_log_db = M("acount_log");
        $count= $acount_log_db->join(" AS a LEFT JOIN __MEMBER_LIST__ AS b ON a.member_list_id = b.member_list_id")->join(" LEFT JOIN __OPTIONS__ AS c ON a.fund_type = c.option_name")->join(" LEFT JOIN __OPTIONS__ AS d ON a.fund_change_type = d.option_name")->where($map)->count();

        $Page= new \Think\Page($count,C('DB_PAGENUM'));

        $list = $acount_log_db->field('a.*,b.member_list_username,c.option_value as fund_type_name,d.option_value as fund_change_type_name')->join(" AS a LEFT JOIN __MEMBER_LIST__ AS b ON a.member_list_id = b.member_list_id")->join(" LEFT JOIN __OPTIONS__ AS c ON a.fund_type = c.option_name")->join(" LEFT JOIN __OPTIONS__ AS d ON a.fund_change_type = d.option_name")->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        $show = $Page->show();
        // echo '<pre>';print_r($list);exit;
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->assign('keyword',I('keyword'));

        $this->display();

    }


    //充值记录
    public function rechargeLog(){

        $recharg_log_db = M('recharg_log');
        
        $map = array(
                'state' => 1
            );
        $count = $recharg_log_db->join('AS a LEFT JOIN __MEMBER_LIST__ AS b ON a.member_list_id=b.member_list_id')->where($map)->count();

        $Page = new \Think\Page($count,C('DB_PAGENUM'));

        $recharg_log_list = $recharg_log_db->field('a.id,a.member_list_id,a.recharge_member_list_id,a.money,a.type,a.recharge_type,a.state,b.member_list_tel')->where($map)->join('AS a LEFT JOIN __MEMBER_LIST__ AS b ON a.member_list_id=b.member_list_id')->limit($Page->firstRow.','.$Page->listRows)->order('id DESC')->select();
        foreach ($recharg_log_list as $key => $value) {
            $recharg_log_list[$key]['tel']= M('member_list')->where(array('member_list_id'=>$value['recharge_member_list_id']))->getfield('member_list_tel');
        }
        
        foreach ($recharg_log_list as $key => $value)
        {
            if($value['type'] == 1)
            {
                 $recharg_log_list[$key]['type']='卡券充值';
            }
            else
            {
                 $recharg_log_list[$key]['type']='自定义金额';
            }

            if($value['recharge_type'] == 'alipay')
            {
                $recharg_log_list[$key]['recharge_type']='银联';
            }
            if($value['recharge_type'] == 'wxpay')
            {
                $recharg_log_list[$key]['recharge_type']='微信';
            }
             if($value['recharge_type'] == 'unionpay')
            {
                $recharg_log_list[$key]['recharge_type']='支付宝';
            }
            else{             
            }
        }
        $show = $Page->show();
        $this->assign('recharg_log_list',$recharg_log_list);
        $this->assign('page',$show);	
		$this->display();
    }
    //编辑充值记录
    public function rechargeEdit()
    {
        if(IS_POST)
        {
            $id=I('id');
            $map['state'] = I('state');
            $list = M('recharg_log')->where(array('id'=>$id))->find();

            if($map['state'] == 1)
            {
                $data['member_list_id'] = $list['member_list_id'];;//用户
                $data['income'] =  $list['money'];//收入
                $data['expenditure'] =0;//支出
                $data['frozen_inc'] = 0;//增加冻结金额
                $data['frozen_dec'] = 0;//减少冻结金额
                $data['fund_change_type'] = 'online_recharge';//资金变动类型
                $data['fund_type'] = 'station_balance';//资金类型
                $data['remark'] = I("remark");  //备注说明            
                $capital = new CapitalController();
                $resutl = $capital->addCapitalLog($data);
            }

                 M('recharg_log')->where("id=$id")->save($map);
                $this->success('修改成功',U('rechargeLog'),1);                      
        }else{
            $id=I('id');
            $this->assign('id',$id);
            $this->display();  
        }
    }

    //提现记录
    public function cashPostalLog(){
        if(I('keyword'))
        {
            $map['b.member_list_username|b.member_list_tel'] = array('like','%'.I('keyword').'%');
        }
        $withd_db = M("withd");
        $count= $withd_db->join(" AS a LEFT JOIN __MEMBER_LIST__ AS b ON a.member_list_id = b.member_list_id")->join(" LEFT JOIN __OPTIONS__ AS c ON a.bank_id = c.option_id")->where($map)->count();

        $Page= new \Think\Page($count,C('DB_PAGENUM'));

        $list = $withd_db->field('a.*,b.member_list_username,c.option_value as bank_name')->join(" AS a LEFT JOIN __MEMBER_LIST__ AS b ON a.member_list_id = b.member_list_id")->join(" LEFT JOIN __OPTIONS__ AS c ON a.bank_id = c.option_id")->where($map)->order('member_list_id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $show = $Page->show();
        // echo '<pre>';print_r($list);exit;
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->assign('keyword',I('keyword'));

        $this->display();
    }

    //提现记录审核
    public function withdraw(){
        if(IS_AJAX){
            $withd_id = I('withd_id');
            $map['state'] = I('state');
            $list = M('withd')->where(array('id'=>$withd_id))->find();
            if($map['state'] == 2){
                $data['member_list_id'] = $list['member_list_id'];
                $data['income'] = 0;
                $data['expenditure'] = 0;
                $data['frozen_inc'] = 0;
                $data['frozen_dec'] = $list['money'];
                $data['fund_change_type'] = 'withdraw';
                $data['fund_type'] = 'station_balance';
                $data['remark'] = I("remark",'') !=''?I('remark'):'提现审核通过';
                $capital = new CapitalController();
                $result = $capital->addCapitalLog($data);
                if($result){
                    $map['remark'] = I("remark",'') !=''?I('remark'):'提现审核通过';
                    $withd=M('withd');          
                    M('withd')->where(array('id'=>$withd_id))->save($map);
                    $this->success('审核通过',U('Finace/FinaceAdmin/cashPostalLog'),1);
                } 
            }elseif($map['state'] == 3){
                $data['member_list_id'] = $list['member_list_id'];
                $data['income'] = $list['money'];
                $data['expenditure'] = 0;
                $data['frozen_inc'] = 0;
                $data['frozen_dec'] = $list['money'];
                $data['fund_change_type'] = 'withdraw';
                $data['fund_type'] = 'station_balance';
                $data['remark'] = I("remark",'') !=''?I('remark'):'提现审核未通过';
                $capital = new CapitalController();
                $result = $capital->addCapitalLog($data);
                if($result){
                    $map['remark'] = I("remark",'') !=''?I('remark'):'提现审核未通过';
                    $withd=M('withd');
                    M('withd')->where(array('id'=>$withd_id))->save($map);
                    $this->success('审核未通过',U('Finace/FinaceAdmin/cashPostalLog'),1);
                } 
            }elseif($map['state'] == 4){
                    $map['remark'] = I("remark");
                    $withd=M('withd');          
                    M('withd')->where(array('id'=>$withd_id))->save($map);
                    $this->success('审核取消',U('Finace/FinaceAdmin/cashPostalLog'),1);
            }

        }else{
            $id = I('id');
            $this->assign('id',$id);
            $this->display();  
        }
        
    }
    //删除提现记录
    public function withdraw_delete(){
        $id = I('id');
        $withd=M('withd');          
        $result = M('withd')->where(array('id'=>$id))->delete();
        if($result){
            $this->success('删除成功',U('Finace/FinaceAdmin/cashPostalLog'),1);
        }else{
            $this->success('删除失败',U('Finace/FinaceAdmin/cashPostalLog'),0);
        }
        
    }
    //充值设置
    public function rechargeSet()
    {

        $options_db = M('options');
        $map['group'] = 'recharge';
        $recharge_list = $options_db->field('option_id,option_value,autoload,sort_order')->where($map)->select();

        foreach($recharge_list as $key => $value)
        {
            $recharge_list[$key]['option_value'] = json_decode($value['option_value'],true);
        }
		$this->assign('recharge_list',$recharge_list);
		$this->display();
    }
    //充值卡添加
    public function rechargeAdd()
    {
        $coupon_db = M('coupon');
        $coupon_list = $coupon_db->field('id,coupon_name,money')->where(array('coupon_type_id'=>3))->select();
        $this->assign('coupon_list',$coupon_list);
        $this->display();
    }
    //充值卡添加操作
    public function rechargeAddRun()
    {
        if(!IS_AJAX)
        {
            $this->error("提交方式错误",U('rechargeSet'),1);
        }
        else
        {
            $options_db = M('options');
            $coupon_ids = implode(',',I('coupon_ids'));
            if($coupon_ids){
                $coupon_total = M('coupon')->where("id IN($coupon_ids)")->sum('money');
            }
            $data['option_name'] = 'recharge_money_'.I('money');
            $data['group'] = 'recharge';
            $data['sort_order'] = I('sort_order',100,'intval');
            $data['autoload'] = I('autoload',0,'intval');

            $option_value = array(
                    'money' => I('money','0','intval'),
                    'coupon_total' => intval($coupon_total),
                    'coupon_ids' => $coupon_ids
                );
            $data['option_value'] = json_encode($option_value);
            $options_db->add($data);
            $this->success('充值卡添加成功',U('rechargeSet'),1);
        }
    }
    //充值卡是用状态修改
    public function rechargestate()
    {
        $id=I('x');
        if (empty($id)){
            $this->error('ID不存在',U('rechargeSet'),0);
        }
        $status=M('options')->where(array('option_id'=>$id))->getField('autoload');//判断当前状态情况
        if($status==1){
            $statedata = array('autoload'=>0);
            M('options')->where(array('option_id'=>$id))->setField($statedata);
            $this->success('状态禁止',1,1);
        }else{
            $statedata = array('autoload'=>1);
            M('options')->where(array('option_id'=>$id))->setField($statedata);
            $this->success('状态开启',1,1);
        }
    }

    //删除充值卡
    public function rechargeDel()
    {
        $option_id = I("option_id");

        $options_db = M("options");
        $options_db->where("option_id='$option_id'")->delete();

        $this->success("删除成功");
    }
    //会员卡设置
    public function memberCardSet(){
        $options_db = M('options');

        $map['group'] = 'member_card';
        $list = $options_db->where($map)->order("sort_order ASC")->select();

        foreach ($list as $key => $value) {
            $list[$key]['option_value'] = json_decode($value['option_value'],true);
        }
        // var_dump($list);exit;
        $this->assign('list',$list);

		$this->display();
    }

    //添加会员卡
    public function memberCardAdd()
    {
        if(IS_AJAX)
        {
            $option_value['term'] = intval(I('term'));
            $option_value['price'] = I('price');
            $option_value['discount_price'] = I('discount_price');
            $option_value['discount_name'] = I('discount_name');

            $data['option_name'] = 'member_card_'.I('price',0,'intval');
            $data['option_value'] = json_encode($option_value);
            $data['autoload'] = I('autoload',0,'intval');
            $data['sort_order'] = I('sort_order');
            $data['remark'] = '卖家会员卡';
            $data['group'] = 'member_card';

            $options_db = M('options');
            $option_id = I("post.option_id");
            if($option_id)
            {
                $options_db->where("option_id='$option_id'")->save($data);
                 $this->success("修改成功",U("memberCardSet"),0);
            }
            else
            {
                $options_db->add($data);

                $this->success("添加成功",U("memberCardSet"),1);
            }
        }
        else
        {
            $option_id = I("option_id");
            $options_db = M('options');
            if($option_id)
            {
                $info = $options_db->where("option_id='$option_id'")->find();
                $info['option_value'] = json_decode($info['option_value'],true);
                $this->assign('info',$info);
            }
            
            $this->display();
        }
    }



    
}