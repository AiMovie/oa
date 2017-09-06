<?php
// +----------------------------------------------------------------------
// | 功能：机械搜索
// +----------------------------------------------------------------------
// | 作者：张春美
// +----------------------------------------------------------------------
// | 日期：2017-8-1
// +----------------------------------------------------------------------
namespace app\api\controller;

// use app\api\controller\Userbase;
use app\Common\controller\Common;
use think\Db;
use think\Request;
use org\Stringnew;
use think\Cookie;
use think\Validate;

class Mechanical extends Common
{

   /*
   *新机械列表
    */
   public function newsMechanicsList(){
        if($_POST)
        {
            if(input('brand_name'))
            {
                $par['c.brand_name'] = input('brand_name');//品牌
            }
            if(input('province_id'))
            {
                $par['a.province_id'] = input('province_id');//区域
            }
            if(input('city_id'))
            {
                $par['a.city_id'] = input('city_id');//区域
            }
            if(input('product_country'))
            {
                $par['a.producing_country'] = input('product_country');//生产国 1国产，2合资，3进口
            }
        }
        $par['a.type'] = 1;//新机械
        $par['a.module'] = 1;//机械买卖
        $par['a.is_effective'] = 1;//有效
        $par['a.is_check']  = 2;//审核通过
        $par['a.is_delete'] = 0;//未删除
        $result = Db::name('mechanics')
                ->alias('a')
                ->join('mechanical b ','b.id= a.mechanical_id','LEFT')
                ->join('brand c ','c.id= a.brand_id','LEFT')
                ->join('mechanical_model d ','d.id= a.mechanical_model_id','LEFT')
                ->join('parameter g','g.id= a.producing_country','LEFT')
                ->join('region e ','e.id= a.province_id','LEFT')
                ->join('region f ','f.id= a.city_id','LEFT')
                ->where($par)
                ->order('a.create_time desc')
                ->field('a.id,a.photo,b.mechanical_name,c.brand_name,d.model_name,e.name as province_name,f.name as city_name,g.name as product_country ')
                ->paginate(15);

        $this->make_json_result('数据获取成功',$result);

   }

    /*
   *二手机械列表
    */
   public function oldMechanicsList(){
        if($_POST){
            if(input('brand_name'))
            {
                $par['c.brand_name'] = input('brand_name');//品牌
            }
            if(input('province_id'))
            {
                $par['a.province_id'] = input('province_id');
            }

            if(input('city_id'))
            {
                $par['a.city_id'] = input('city_id');//区域
            }

            if(input('price'))
            {
                $price = explode("-",input('price'));
                $start_price = intval($price[0]);
                $end_price = intval($price[1]);
                if($start_price && !$end_price)
                {
                    $str_price = input('price');
                    if(strpos($str_price,'以上'))
                    {
                        $par['a.price'] = array('gt',$start_price*10000);
                    }
                    else{
                        $par['a.price'] = array('lt',$start_price*10000);
                    }
                }
                elseif($start_price && $end_price)
                {
                    $par['a.price'] = array('between',array($start_price*10000,$end_price*10000));
                }
            }

            //工作小时
            if(input('work_hours'))
            {
                $work_hours = explode("-",input('work_hours'));
                $start_work_hours = intval($work_hours[0]);
                $end_work_hours = intval($work_hours[1]);

                if($start_work_hours && !$end_work_hours)
                {
                    $str_work_hours = input('work_hours'); 
                    if(strpos($str_work_hours,'以上'))
                    {
                        $par['a.work_hours'] = array('gt',$start_work_hours);

                    }
                    else
                    {
                        $par['a.work_hours'] = array('lt',$start_work_hours);
                    }
                   
                }
                elseif($start_work_hours && $end_work_hours)
                {
                    $par['a.work_hours'] = array('between',$start_work_hours,$end_work_hours);
                }
            }
            //出厂时间
            $manufacture_date = input('manufacture_date/d');
            if($manufacture_date)
            {
                $par['a.manufacture_date'] = array('between',array($manufacture_date.'-01-01',$manufacture_date.'-12-31')); 
            }
        }
        $par['a.type'] = 1;//新机械
        $par['a.module'] = 1;//机械买卖
        $par['a.is_effective'] = 1;//有效
        $par['a.is_check']  = 2;//审核通过
        $par['a.is_delete'] = 0;//未删除
        $result = Db::name('mechanics')
                ->alias('a')
                ->join('mechanical b ','b.id= a.mechanical_id','LEFT')
                ->join('brand c ','c.id= a.brand_id','LEFT')
                ->join('mechanical_model d ','d.id= a.mechanical_model_id','LEFT')
                ->join('region e ','e.id= a.province_id','LEFT')
                ->join('region f ','f.id= a.city_id','LEFT')
                ->where($par)
                ->order('a.create_time desc')
                ->field('a.id,a.work_hours,a.manufacture_date,a.price,a.photo,b.mechanical_name,c.brand_name,d.model_name,e.name as province_name,f.name as city_name')
                ->paginate(15);

        $this->make_json_result('数据获取成功',$result);

   }

    /*
   *机械租赁列表---工程翻斗车
    */
   public function rentTrunckList(){
        if($_POST){
            if(input('province_id'))
            {
                $map['a.province_id'] = input('province_id');
            }
            if(input('city_id'))
            {
                $map['a.city_id'] = input('city_id');//区域
            }

            if(input('squares'))
            {
                //方位
                $squares = explode("-",input('squares'));
                $start_squares = intval($squares[0]);
                $end_squares = intval($squares[1]);
                if($start_squares && !$end_squares)
                {
                    $str_squares = input('squares');
                    if(strpos($str_squares,'以上'))
                    {
                        $map['a.squares'] = array('gt',$start_squares);
                    }
                    else
                    {
                        $map['a.squares'] = array('lt',$start_squares);
                    }
                    
                }
                elseif($start_squares && $end_squares)
                {
                    $map['a.squares'] = array('between',$start_squares,$end_squares);
                }
            }
           
            if(input('mechanical_id')){
                $map['a.mechanical_id'] = input('mechanical_id');
            }
        }
        $map['a.is_delete'] =0;//未删除
      //  $map['e.type'] =2;//1工程机械，2工程翻斗车，3机械拖板车
        $map['a.is_check']  = 2;//审核通过
        $map['a.is_effective'] = 1;//有效的
        $result = Db::name('lease')
                ->alias('a')
                ->join('region c ','c.id= a.province_id','LEFT')
                ->join('region d ','d.id= a.city_id','LEFT')
                ->join('mechanical e ','e.id= a.mechanical_id','LEFT')
                ->join('brand g ','g.id= a.brand_id','LEFT')
                ->join('mechanical_model h ','h.id= a.mechanical_model_id','LEFT')
                ->where($map)
                ->order('a.create_time desc')
                ->field('a.id,a.squares,c.name AS province_name,d.name AS city_name,e.mechanical_name,g.brand_name,h.model_name')
                ->paginate(15);
        
        $this->make_json_result('数据获取成功',$result);

   }

   /*
   *机械租赁列表---机械拖板车
    */
   public function rentMechanicalList(){
        if($_POST){
            if(input('province_id'))
            {
                $map['a.province_id'] = input('province_id');
            }
            if(input('city_id'))
            {
                $map['a.city_id'] = input('city_id');//区域
            }

            if(input('tonnage'))
            {
                $tonnage = explode("-",input('tonnage'));
                $start_tonnage = intval($tonnage[0]);
                $end_tonnage = intval($tonnage[1]);

                if($start_tonnage && !$end_tonnage)
                {
                    $str_tonnage = input('tonnage'); 
                    if(strpos($str_tonnage,'吨以上'))
                    {
                        $map['a.tonnage'] = array('gt',$start_tonnage);

                    }
                    else
                    {
                        $map['a.tonnage'] = array('lt',$start_tonnage);
                    }
                   
                }
                elseif($start_tonnage && $end_tonnage)
                {
                    $map['a.tonnage'] = array('between',$start_tonnage,$end_tonnage);
                }
            }
            if(input('mechanical_id'))
            {
                $map['a.mechanical_id'] = input('mechanical_id');
            }
        }
        $map['a.is_delete'] =0;//未删除
        $map['a.is_check']  = 2;//审核通过
        $map['a.is_effective'] = 1;//有效的
        //$map['e.type'] =3;//1工程机械，2工程翻斗车，3机械拖板车
        $result = Db::name('lease')
                ->alias('a')
                ->join('region c ','c.id= a.province_id','LEFT')
                ->join('region d ','d.id= a.city_id','LEFT')
                ->join('mechanical e ','e.id= a.mechanical_id','LEFT')
                ->join('brand g ','g.id= a.brand_id','LEFT')
                ->join('mechanical_model h ','h.id= a.mechanical_model_id','LEFT')
                ->where($map)
                ->order('a.create_time desc')
                ->field('a.id,a.tonnage,c.name AS province_name,d.name AS city_name,e.mechanical_name,g.brand_name,h.model_name')
                ->paginate(15);
        
        $this->make_json_result('数据获取成功',$result);

   }

    /*
   *机械租赁列表---工程机械车
    */
   public function rentMachineryList(){
        if($_POST)
        {
            if(input('province_id'))
            {
                $map['a.province_id'] = input('province_id');
            }
            if(input('city_id'))
            {
                $map['a.city_id'] = input('city_id');//区域
            }
            //吨位
            if(input('tonnage'))
            {
                $tonnage = explode("-",input('tonnage'));
                $start_tonnage = intval($tonnage[0]);
                $end_tonnage = intval($tonnage[1]);

                if($start_tonnage && !$end_tonnage)
                {
                    $str_tonnage = input('tonnage'); 
                    if(strpos($str_tonnage,'吨以上'))
                    {
                        $map['a.tonnage'] = array('gt',$start_tonnage);

                    }
                    else
                    {
                        $map['a.tonnage'] = array('lt',$start_tonnage);
                    }
                   
                }
                elseif($start_tonnage && $end_tonnage)
                {
                    $map['a.tonnage'] = array('between',$start_tonnage,$end_tonnage);
                }
            }

         /*   if(input('mechanical_id'))
            {
                $map['a.mechanical_id'] = input('mechanical_id');
            }*/
            if(input('brand_id'))
            {
                $map['a.brand_id'] = input('brand_id');
            }
            if(input('mechanical_model_id'))
            {//机型
                $map['a.mechanical_model_id'] = input('mechanical_model_id');
            }
            if(input('work_hours')){
                //小时
                $work_hours = explode("-",input('work_hours'));
                $start_hours = intval($work_hours[0]);
                $end_hours = intval($work_hours[1]);
                if($start_hours && !$end_hours)
                {
                    $str_work_hours = input('work_hours');
                    //如果是以上
                    if(strpos($str_work_hours,'以上'))
                    {
                        $map['a.hours'] = array('gt',$start_hours);
                    }
                    else
                    {
                        $map['a.hours'] = array('lt',$start_hours);
                    }
                }
                elseif($start_hours && $end_hours)
                {
                    $map['a.hours'] = array('between',$start_hours,$end_hours);
                } 
            }
            
            $manufacture_date = input('manufacture_date/d'); 
             if($manufacture_date)
             {//出厂日期
                 $map['a.manufacture_date'] = array('between',$manufacture_date.'-01-01',$manufacture_date.'-12-31');
            }
             /*if(input('mechanical_id'))
             {//机械名称
                 $map['a.mechanical_id'] = input('mechanical_id');
            }*/
        }
        $map['a.is_delete'] =0;//未删除
        $map['a.is_check']  = 2;//审核通过
        $map['a.is_effective'] = 1;//有效的
      //  $map['e.type'] =1;//1工程机械，2工程翻斗车，3机械拖板车
        $result = Db::name('lease')
                ->alias('a')
                ->join('region c ','c.id= a.province_id','LEFT')
                ->join('region d ','d.id= a.city_id','LEFT')
                ->join('mechanical e ','e.id= a.mechanical_id','LEFT')
                ->join('brand g ','g.id= a.brand_id','LEFT')
                ->join('mechanical_model h ','h.id= a.mechanical_model_id','LEFT')
               // ->where($map)
                ->order('a.create_time desc')
                ->field('a.id,a.hours,a.manufacture_date,c.name AS province_name,d.name AS city_name,e.mechanical_name,g.brand_name,h.model_name')
                ->paginate(15);
        $this->make_json_result('数据获取成功',$result);

   }

   
   /*
   *机械详情页
    */
    public function getMechanicsDetail(){
        if($_POST){
            $mechanical_id = input('mechanical_id');
            if(!$mechanical_id)
            {
                $this->make_json_error('获取数据错误',1);
            }
            $par['a.id'] = $mechanical_id;
             //获取机械的详情
            $data =Db::name('mechanics')
                    ->alias('a')
                    ->join('mechanical b ','b.id= a.mechanical_id','LEFT')
                    ->join('brand c ','c.id= a.brand_id','LEFT')
                    ->join('mechanical_model d ','d.id= a.mechanical_model_id','LEFT')
                    ->join('region e ','e.id= a.province_id','LEFT')
                    ->where($par)
                    ->field('a.id,a.work_hours,a.manufacture_date,a.price,a.tonnage,a.is_certificate,a.is_check,a.photo,b.mechanical_name,c.brand_name,d.model_name,e.name')
                    ->find(); 
            //猜你喜欢
            $param['b.mechanical_name'] = array('like','%'.$data['mechanical_name'].'%');
            $data['mechanical_list'] = Db::name('mechanics')
                    ->alias('a')
                    ->join('mechanical b ','b.id= a.mechanical_id','LEFT')
                    ->join('brand c ','c.id= a.brand_id','LEFT')
                    ->join('mechanical_model d ','d.id= a.mechanical_model_id','LEFT')
                    ->join('region e ','e.id= a.province_id','LEFT')
                    ->where($param)
                    ->order('a.create_time desc')
                    ->field('a.id,a.work_hours,a.manufacture_date,a.price,a.photo,b.mechanical_name,c.brand_name,d.model_name,e.name')
                    ->limit(4)
                    ->select();
            $this->make_json_result('数据获取成功',$data);
        }
   }


   /*
   *获取车辆品牌
    */
   public function getCarBrand(){
        $brand_list = Db::name('brand')->field('id,brand_name,logo')->order('sort_order desc')->select();

        $this->make_json_result('数据获取成功',$brand_list);
   }

   /*
   *获取区域省份列表
    */
   public function getProvince(){
        $province_list = Db::name('region')->where('type',1)->field('id,name')->select();

        $this->make_json_result('数据获取成功',$province_list);
   }

   /*
   *获取区域城市列表
    */
   public function getCity(){
        if(input('parent_id'))
        {
            $map['pid']  = input('parent_id');
            $map['type'] = 2;
            $city_list = Db::name('region')->where($map)->field('id,name')->select();

            $this->make_json_result('数据获取成功',$city_list);
        }
        else
        {
            $this->make_json_error('获取数据错误',1);
        }
        
   }

   /*
   *获取价格配置列表
    */
   public function getPriceList(){
        $type = input('type');
        if($type ==1){
            $par['module'] = 10;
        }else{
            $par['module'] = 11;
        }
        $par['name'] = '价格';
        $price_list = array();
        $parent_list = Db::name('parameter')->where($par)->find();
        if(!empty($parent_list)){
            $price_list = Db::name('parameter')->where('parent_id',$parent_list['id'])->field('id,name')->select();
        }
        $this->make_json_result('获取数据成功',$price_list);
   }

   /*
   *获取吨位配置列表
    */
   public function getTonnageList(){
        $type = input('type');
        if($type ==1){
            $par['module'] = 10;
        }else{
            $par['module'] = 11;
        }
        $par['name'] = '吨位';
        $list = Db::name('parameter')->where($par)->find();
        $tonnage_list = array();
        if(!empty($list)){
            $tonnage_list = Db::name('parameter')->where('parent_id',$list['id'])->field('id,name')->select();
        }
        $this->make_json_result('获取数据成功',$tonnage_list);
    }

    /*
   *获取方数配置列表
    */
   public function getSquaresList(){
        $squares_list = array();
        if(!empty($list)){
            $squares_list = Db::name('parameter')->where('parent_id',124)->field('id,name')->select();
        }
        $this->make_json_result('获取数据成功',$squares_list);
    }

    /*
    *获取机械类型列表
     */
    public function getModelsList(){
        $type = input('type');
        if(!$type){
            $this->make_json_error('获取数据错误',1);
        }
        $map['type'] = $type;
        $model_list = Db::name('mechanical')->where($map)->field('id,mechanical_name')->select();
        $this->make_json_result('获取数据成功',$model_list);
    }

    /*
    *获取机型列表
     */
    public function getModelTypeList(){
        $mechanical_id = input('mechanical_id');
        $brand_id = input('brand_id');
        if(!$type || !$brand_id){
            $this->make_json_error('获取数据错误',1);
        }
        $map['mechanical_id'] = $mechanical_id;
        $map['brand_id'] = $brand_id;
        $model_list = Db::name('mechanical_model')->where($map)->field('id,model_name')->select();
        $this->make_json_result('获取数据成功',$model_list);
    }

    /**
     * 获取生产国
     */
    public function getProductCountry(){

        $country_id = Db::name('parameter')->where("name",'生产国')->where("module",10)->where("state",1)->value('id');
        $product_country = Db::name('parameter')->where("parent_id",$country_id)->where("state",1)->field('id,name')->select();
        $this->make_json_result('获取数据成功',$product_country);
    }

    /*
    * 根据id获取配置表面的数据
     */
    private function _getConfig($id){
        return Db::name('parameter')->where('id',$id)->field('id,value')->find();
    }
}