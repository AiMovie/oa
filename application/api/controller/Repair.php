<?php
// +----------------------------------------------------------------------
// | 功能：修理厂搜索
// +----------------------------------------------------------------------
// | 作者：张春美
// +----------------------------------------------------------------------
// | 日期：2017-8-3
// +----------------------------------------------------------------------
namespace app\api\controller;

use app\Common\controller\Common;
use think\Db;
use think\Request;
use org\Stringnew;
use think\Cookie;
use think\Validate;

class Repair extends Common
{

   /**
    * 
   *修理厂列表
   *
    */
   public function repairList(){

       
        if($_POST){
            if(input('up_server'))
            {
                $par['a.up_server'] = input('up_server');//服务类型
            }
            if(input('province_id'))
            {
                $par['a.province_id'] = input('province_id');//区域
            }

            if(input('city_id'))
            {
                $par['a.city_id'] = input('city_id');//区域
            }

            if(input('type'))
            {
                $par['a.type'] = input('type');// 211 汽车修理  212工程机械
            }
           /* $class_id = input('class_id');
            if($class_id)
            {
                $par['class_id'] = array('like','%'.$class_id.'%');
            }*/
            
        }
        $par['a.is_check']  = 2;//审核通过
        $par['a.is_delete'] = 0;//未删除
        $par['a.is_effective'] = 1;//有效的
        $result = Db::name('repair')
            ->alias('a')
            ->join('region b ','b.id= a.province_id','LEFT')
            ->where($par)
            ->order('a.create_time desc')
            ->field('a.name,a.class_id,a.up_server,a.shop_img_url,b.name AS province_name')
            ->paginate(15);

        $repair_list  = $result->items();
        foreach($repair_list as $key => $value)
        {
            $repair_list[$key]['class_list'] = Db::name('parameter')->field('name')->where(['id'=>['IN',$value['class_id']]])->select();
           // $repair_list[$key]['service_list'] = Db::name('parameter')->field('name')->where(['id'=>['IN',$value['up_server']]])->select();
            unset($repair_list[$key]['class_id']);
            unset($repair_list[$key]['up_server']);
        }
        $this->make_json_result('数据获取成功',$repair_list);

   }

   /**
   *修理厂详情
   * 
    */
   public function repairDetail(){
        if($_POST)
        {
            $repair_id = input('repair_id');
            if(!$repair_id)
            {
                $this->make_json_error('获取数据错误',1);
            }
            $map['a.id'] = $repair_id;
            $map['a.is_check']  = 2;//审核通过
            $map['a.is_delete'] = 0;//未删除
            $map['a.is_effective'] = 1;//有效的
            $repair_detail = Db::name('repair')
                    ->alias('a')
                    ->join('region b ','b.id= a.province_id','LEFT')
                    ->join('region c ','c.id= a.city_id','LEFT')
                    ->join('parameter d ','d.id= a.type','LEFT')
                    ->where($map)
                    ->field('a.id,a.name,a.create_time,a.class_id,a.up_server,b.name AS province_name,c.name AS city_name,d.name AS repair_type')
                    ->find();
            $repair_detail['repair_create_time'] = date('Y-m-d',$repair_detail['create_time']);
            $par['type'] = 4;
            $par['product_id'] = $repair_id;

            $repair_detail['shop_img_list'] = Db::name('plug_files')->field('path')->where($par)->select();
            $repair_detail['class_list'] = Db::name('parameter')->field('name')->where(['id'=>['IN',$repair_detail['class_id']]])->select();
            $repair_detail['service_list'] = Db::name('parameter')->field('name')->where(['id'=>['IN',$repair_detail['up_server']]])->select();
            
            unset($repair_detail['class_id']);
            unset($repair_detail['up_server']);
            unset($repair_detail['create_time']);
            $this->make_json_result('获取数据成功',$repair_detail);

        }
   }

   /**
    *获取工种类型
    * 
    */
   public function workTypeList(){
        $class_id = Db::name('parameter')->where("name",'工种')->where("module",4)->where("state",1)->value('id');
        $class_list = Db::name('parameter')->where("parent_id",$class_id)->where("state",1)->field('id,name')->select();
        $this->make_json_result('获取数据成功',$class_list);
   }

    /**
    *获取服务类型
    * 
    */
   public function serviceTypeList(){
        $service_id = Db::name('parameter')->where("name",'服务类型')->where("module",4)->where("state",1)->value('id');
        $service_list = Db::name('parameter')->where("parent_id",$service_id)->where("state",1)->field('id,name')->select();
        $this->make_json_result('获取数据成功',$service_list);
   }
   

  
}