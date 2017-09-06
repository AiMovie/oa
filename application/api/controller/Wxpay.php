<?php
/*
* WxpayController.class.php
* 微信支付控制器
* 创建人：程龙飞
* 创建时间：2016-10-14 09:42:06
* 最后编辑人：程龙飞
* 编辑时间：2016-10-14 09:42:06
*/
namespace app\api\controller;

use app\common\controller\Common;
use think\Db;
use think\Cookie;
class Wxpay extends Common {

    //微信支付操作
    public function payment($pay_log_info){

        $wxconfig = $this->get_options_value('wxpay');

        if($pay_log_info['state'] == 1)
        {
            $this->make_json_error("订单已支付，请勿重负支付",500);
        }

        Vendor("Pay.Wxpay.WxPayPubHelper");

        $unifiedOrder = new \UnifiedOrder_pub();
        
        $unifiedOrder->setParameter("body",$pay_log_info['body']);//商品描述
        $pay_log_info['pay_no'] = create_pay_no();
        $unifiedOrder->setParameter("out_trade_no",$pay_log_info['pay_no']);//商户订单号
        $unifiedOrder->setParameter("total_fee","".$pay_log_info['pay_money']*100);//总金额.注意单位是分
        $unifiedOrder->setParameter("notify_url",'http://'.$_SERVER['HTTP_HOST'].'/api/wxpay/notify_url');//通知地址
        $unifiedOrder->setParameter("trade_type","APP");//交易类型
        $prepay = $unifiedOrder->getPrepayId();
        $data['appid'] = $prepay['appid'];
        $data['key'] = $wxconfig['apikey'];
        $data['noncestr'] = $prepay['nonce_str'];
        $data['partnerid'] = $prepay['mch_id'];
        $data['prepayid'] = $prepay['prepay_id'];
        $data['package'] = 'Sign=WXPay';
        $data['timestamp'] = time();
         
        $sign = $unifiedOrder->getSign($data);
        $data['sign'] = $sign;
    
        $this->make_json_result('请求成功！',$data);
    }

    //微信支付异步通知方法
    public function notify_url()
    {
        /**
         * 支付完成后，微信会把相关支付和用户信息发送到商户设定的通知URL，
         * 商户接收回调信息后，根据需要设定相应的处理流程。
         *
         *
         */

        Vendor("Pay.Wxpay.WxPayPubHelper");
        //使用通用通知接口
        $notify = new \Notify_pub();
         
        //存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        // $xml = file_get_contents("php://input");

        $notify->saveData($xml);

        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if($notify->checkSign() == FALSE){
            $notify->setReturnParameter("return_code","FAIL");//返回状态码
            $notify->setReturnParameter("return_msg","签名失败");//返回信息
        }else{
            $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
        }
        $returnXml = $notify->returnXml();
        // echo $returnXml;
    
        if($notify->checkSign() == TRUE)
        {
            
            $out_trade_no = $notify->data["out_trade_no"];
            $total_fee = $notify->data["total_fee"];
            //支付流水号
            $trade_no = $notify->data['transaction_id'];
                              
            if ($notify->data["return_code"] == "SUCCESS") {

                $pay_log_info = $Db::name('pay_log')->where(array('pay_no'=>$out_trade_no))->find();
                //支付状态0未支付1已支付
                if ($pay_log_info['state'] == 1)
                {
                    $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
                }
                if ($pay_log_info['state'] == 0)
                {
                    $member_info = Db::name('member_list')->field('money')->field('pay_password,member_list_salt,money')->where(['member_list_id'=>$pay_log_info['member_list_id']])->find();
                    //此处应该更新一下订单状态，商户自行增删操作
                    $pay_log_id = $pay_log_info['id'];
                    //将支付记录支付状态改为已支付
                    $sl_data['state'] = 1;
                    $sl_data['trade_no'] = $out_trade_no;
                    $sl_data['pay_time'] = time();
                    $sl_data['pay_type'] = 'wxpay';
                    Db::name('pay_log')->where(["id"=>$pay_log_info['id']])->update($sl_data);
                               
                    Db::name('acount_log')->insert($data);


                    //调用业务操作控制器,处理相关业务
                    $Business = controller('Api/Business');
                    $Business->index($pay_log_info);
                }
                //商户自行增加处理流程,
                //例如：更新订单状态
                //例如：数据库操作
                //例如：推送支付完成信息
            }
        }       
        echo $returnXml;
        exit; 
    }
}