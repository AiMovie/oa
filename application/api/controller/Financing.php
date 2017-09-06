<?php
// +----------------------------------------------------------------------
// | 功能：融资按揭搜索
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

class Financing extends Common
{

   /**
    * 
   *融资按揭列表
   *
    */
   public function financingList(){
        if($_POST){
            if(input('financing_type'))
            {
                $par['a.fianceing_typeid'] = input('financing_type');
            }
           
            if(input('province_id'))
            {
                $par['a.province_id'] = input('province_id');//区域
            }

            if(input('city_id'))
            {
                $par['a.city_id'] = input('city_id');//区域城市
            }
        }
        $par['a.is_check']  = 1;//审核通过
        $par['a.is_delete'] = 0;//未删除
        $par['a.is_effective'] = 1;//有效的
        $list = Db::name('financing')
            ->alias('a')
            ->join('parameter b ', 'b.id= a.fianceing_typeid', 'LEFT')
            ->join('region c ','c.id= a.province_id','LEFT')
            ->join('region d ','d.id= a.city_id','LEFT')
            ->where($par)
            ->field('a.fianceing_id as id,a.company_name,a.createtime,a.aptitude,b.name as financing_type,c.name as province_name, d.name as city_name')
            ->order('a.createtime desc')
            ->paginate(15);

        $financing_list = array();
        $financing_list = $list->items();
        foreach ($financing_list as $key => $value) {
            $financing_list[$key]['createtime'] = date('Y-m-d',$value['createtime']);
            if($value['aptitude']== NULL){
                $financing_list[$key]['aptitude'] ="";
            }
        }
        $this->make_json_result('数据获取成功',$financing_list);

   }

   /**
   *融资按揭详情
   * 
    */
   public function financingDetail(){
        if($_POST)
        {
            $financing_id = input('financing_id');
            if(!$financing_id)
            {
                $this->make_json_error('获取数据错误',1);
            }
            $par['a.fianceing_id'] = $financing_id;
            $par['a.is_check']  = 2;//审核通过
            $par['a.is_delete'] = 0;//未删除
            $par['a.is_effective'] = 1;//有效的
            $list_detail = Db::name('financing')
                ->alias('a')
                ->join('parameter b ', 'b.id= a.fianceing_typeid', 'LEFT')
                ->join('region c ','c.id= a.province_id','LEFT')
                ->where($par)
                ->field('a.fianceing_id as id,a.company_name,a.createtime,b.name as financing_type,c.name as pro_name')
                ->find();
            if(!empty($list_detail)){
                $list_detail['createtime'] = date('Y-m-d',$list_detail['createtime']);
            }

           //店面图片列表
            $map['product_id'] = $financing_id;
            $map['type'] = 8;
            $list_detail['aptitude_img'] = Db::name('plug_files')->where($map)->field('path')->select();
            $this->make_json_result('获取数据成功',$list_detail);

        }
   }

   /**
    *获取融资种类
    * 
    */
   public function financingTypeList(){
        $financing_id = Db::name('parameter')->where("name",'融资类型')->where("module",8)->where("state",1)->value('id');
        $financing_list = Db::name('parameter')->where("parent_id",$financing_id)->where("state",1)->field('id,name')->select();
        $this->make_json_result('获取数据成功',$financing_list);
   }

   

  
}