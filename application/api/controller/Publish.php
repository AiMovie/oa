<?php
// +----------------------------------------------------------------------
// | 功能：发布信息功能
// +----------------------------------------------------------------------
// | 作者：汪洋
// +----------------------------------------------------------------------
// | 日期：2017-7-26
// +----------------------------------------------------------------------

namespace app\api\controller;

// use app\api\controller\Userbase;
use app\Common\controller\Common;
use think\Db;
use think\Request;
use org\Stringnew;
use think\Cookie;
use think\Validate;

class Publish extends Common
{

    /**
     *提交新机械
     */
    public function newsmechanicsAdd()
    {
        $check_open = getcheck('newSetting');
        $rule = [
            'producing_country' => 'require|in:1,2,3',
            'mechanical_id' => 'require|number',
            'brand_id' => 'require|number',
            'mechanical_model_id' => 'require|number',
            'province_id' => 'require|number',
            'city_id' => 'require|number',
            'company_name' => 'require|chs',
            'price' => 'require|number',
            'member_list_id' => 'require',
            'is_invoice' => 'require|in:0,1',
            'telphone' => 'require|/^1[3456789]{1}\d{9}$/',
        ];
        $msg = [
            'member_list_id.require' => '用户id未传入',
            'producing_country.require' => '请选择生产国',
            'mechanical_id.require' => '请选择机械类型',
            'brand_id.require' => '请选择品牌',
            'mechanical_model_id.require' => '请选择型号',
            'province_id.require' => '请选择省份',
            'city_id.require' => '请选择市区',
            'company_name.require' => '请填写公司名称',
            'price.require' => '请填写价格',
            'telphone.require' => '请填写联系方式',
            'is_invoice.require' => '请选择是否提供发票',
            'producing_country.in' => '请选择正确的生产国',
            'mechanical_id.number' => '请选择正确的机械类型',
            'brand_id.number' => '请选择正确的品牌',
            'mechanical_model_id.number' => '请选择正确的型号',
            'province_id.number' => '请选择正确的省份',
            'city_id.number' => '请选择正确的市区',
            'company_name.chs' => '请选择正确的公司名称',
            'price.number' => '价格必须为数字',
            'is_invoice.in' => '请选择正确的发票',
            'telphone./^1[3456789]{1}\d{9}$/' => '请输入正确的手机号',
        ];
        $data = [
            'producing_country' => input('producing_country'),
            'mechanical_id' => input('mechanical_id'),
            'brand_id' => input('brand_id'),
            'mechanical_model_id' => input('mechanical_model_id'),
            'province_id' => input('province_id'),
            'city_id' => input('city_id'),
            'company_name' => input('company_name'),
            'price' => input('price'),
            'is_invoice' => input('is_invoice'),
            'telphone' => input('telphone'),
            'create_time' => time(),
            'member_list_id' => input('member_list_id'),
            'is_check' => $check_open,
            'is_effective' => 1,
            'type' => 1,
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            $this->make_json_error($validate->getError(), 1);
        }
        $res = Db::name('mechanics')->insert($data);
        //图片上传待完成
        if ($res) {
            $this->make_json_result('发布成功！');
        }
    }

    /**
     * 发布新机械
     */
    public function CreateNewsmechanics()
    {
        //生产国
        $data[]=[
            'name' => '生产国',
            'params_name' => 'producing_country',
            'is_enter' => Db::name('parameter')->where(['id'=>'159','state'=>1])->value('type'),
            'multi' => Db::name('parameter')->where(['id'=>'159','state'=>1])->value('multi'),
            'params_value' => Db::name('parameter')->where(['parent_id'=>159,'module'=>10,'state'=>1])->field("id,name")->select(),
        ];
        //机械类型与品牌
        $data[] = [
            'name' => '机械类型与品牌',
            'params_name' => 'mechanical_brand',
            'is_enter' => 1,
            'multi' => 0,
            'params_value' =>Db::name('mech_ascc')
                           ->alias('a')
                           ->join('mechanical b ','b.id= a.mechanical_id','LEFT')
                           ->join('brand c ','c.id= a.brand_id','LEFT')
                           ->order('c.create_time desc')
                           ->field('a.*,b.mechanical_name,c.brand_name')
                           ->select()
        ];
        $data[] = [
            'name' => '型号列表',
            'params_name' => 'mechanical_model_id',
            'is_enter' => 2,
            'multi' => 0,
            'params_value' => Db::name('mechanical_model')->field('model_name,mechanical_id,brand_id')->select()
        ];

        $data[] = [
            'name' => '公司名称',
            'params_name' => 'company_name',
            'is_enter' => Db::name('parameter')->where(['id'=>'183','state'=>1])->value('type'),
            'type'=>Db::name('parameter')->where(['id'=>'183','state'=>1])->value('input_type'),
            'params_value' => ''
        ];
        $data[] = [
            'name' => '联系电话',
            'params_name' => 'telphone',
            'is_enter' => Db::name('parameter')->where(['id'=>'184','state'=>1])->value('type'),
            'type'=>Db::name('parameter')->where(['id'=>'184','state'=>1])->value('input_type'),
            'params_value' => ''
        ];
        $data[] = [
            'name' => '价格',
            'params_name' => 'price',
            'is_enter' => Db::name('parameter')->where(['id'=>'185','state'=>1])->value('type'),
            'type'=>Db::name('parameter')->where(['id'=>'185','state'=>1])->value('input_type'),
            'params_value' => ''
        ];
        $this->make_json_result('新机械数据请求成功！',$data);
    }

    /**
     * 发布二手机械
     */
    public function Createusedmechanics()
    {
        //生产国
        $data[]=[
            'name' => '生产国',
            'params_name' => 'producing_country',
            'is_enter' => Db::name('parameter')->where(['id'=>'159','state'=>1])->value('type'),
            'multi' => Db::name('parameter')->where(['id'=>'159','state'=>1])->value('multi'),
            'params_value' => Db::name('parameter')->where(['parent_id'=>159,'module'=>10,'state'=>1])->field("id,name")->select(),
        ];
        //机械类型与品牌
        $data[] = [
            'name' => '机械类型与品牌',
            'params_name' => 'mechanical_brand',
            'is_enter' => 1,
            'multi' => 0,
            'params_value' =>Db::name('mech_ascc')
                ->alias('a')
                ->join('mechanical b ','b.id= a.mechanical_id','LEFT')
                ->join('brand c ','c.id= a.brand_id','LEFT')
                ->order('c.create_time desc')
                ->field('a.*,b.mechanical_name,c.brand_name')
                ->select()
        ];
        $data[] = [
            'name' => '型号列表',
            'params_name' => 'mechanical_model_id',
            'is_enter' => 1,
            'multi' => 0,
            'params_value' => Db::name('mechanical_model')->field('model_name,mechanical_id,brand_id')->select()
        ];

        $data[] = [
            'name' => '封面图上传',
            'params_name' => 'file0',
            'type'=>Db::name('parameter')->where(['id'=>'188','state'=>1])->value('input_type'),
            'params_value' => ''
        ];

        $data[] = [
            'name' => '多图上传',
            'params_name' => 'fileurl_tmp',
            'type'=>Db::name('parameter')->where(['id'=>'189','state'=>1])->value('input_type'),
            'params_value' => ''
        ];


        $data[] = [
            'name' => '公司名称/姓名',
            'params_name' => 'company_name',
            'is_enter' => Db::name('parameter')->where(['id'=>'190','state'=>1])->value('type'),
            'type'=>Db::name('parameter')->where(['id'=>'190','state'=>1])->value('input_type'),
            'params_value' => ''
        ];

        $data[] = [
            'name' => '吨位',
            'params_name' => 'tonnage',
            'is_enter' => Db::name('parameter')->where(['id'=>'192','state'=>1])->value('type'),
            'type'=>Db::name('parameter')->where(['id'=>'192','state'=>1])->value('input_type'),
            'params_value' => ''
        ];

        $data[] = [
            'name' => '车牌',
            'params_name' => 'car_number',
            'is_enter' => Db::name('parameter')->where(['id'=>'183','state'=>1])->value('type'),
            'type'=>Db::name('parameter')->where(['id'=>'183','state'=>1])->value('input_type'),
            'params_value' => ''
        ];

        $data[] = [
            'name' => '民族',
            'params_name' => 'nation',
            'is_enter' => Db::name('parameter')->where(['id'=>'193','state'=>1])->value('type'),
            'type'=>Db::name('parameter')->where(['id'=>'193','state'=>1])->value('input_type'),
            'params_value' => ''
        ];


        $data[] = [
            'name' => '联系电话',
            'params_name' => 'telphone',
            'is_enter' => Db::name('parameter')->where(['id'=>'187','state'=>1])->value('type'),
            'type'=>Db::name('parameter')->where(['id'=>'187','state'=>1])->value('input_type'),
            'params_value' => ''
        ];
        $data[] = [
            'name' => '价格',
            'params_name' => 'price',
            'is_enter' => Db::name('parameter')->where(['id'=>'186','state'=>1])->value('type'),
            'type'=>Db::name('parameter')->where(['id'=>'186','state'=>1])->value('input_type'),
            'params_value' => ''
        ];

        $data[] = [
            'name' => '合格证',
            'params_name' => 'is_certificate',
            'is_enter' => Db::name('parameter')->where(['id'=>'195','state'=>1])->value('type'),
            'multi' => Db::name('parameter')->where(['id'=>'159','state'=>1])->value('multi'),
            'params_value' => Db::name('parameter')->where(['parent_id'=>195,'module'=>11,'state'=>1])->field("id,name")->select()
        ];

        $data[] = [
            'name' => '发票',
            'params_name' => 'is_invoice',
            'is_enter' => Db::name('parameter')->where(['id'=>'198','state'=>1])->value('type'),
            'multi' => Db::name('parameter')->where(['id'=>'198','state'=>1])->value('multi'),
            'params_value' => Db::name('parameter')->where(['parent_id'=>198,'module'=>11,'state'=>1])->field("id,name")->select()
        ];

        $this->make_json_result('二手机械数据请求成功！',$data);
    }

    /**
     * 发布司机信息
     */
    public function CreatDriver()
    {
         $data[] = [
                    'name_value'   => '司机类型',
                    'params_name'  => 'type',
                    // //'is_enum'       => 1,
                    'multi'        => Db::name('parameter')->where(['id'=>'34','state'=>1])->field('multi')->find()['multi'],//时候多选
                    'type'         =>Db::name('parameter')->where(['id'=>'34','state'=>1])->field('type')->find()['type'],
                    'input_type'   =>'0',
                    'params_value' => Db::name('parameter')->where(['parent_id'=>34,'module'=>6,'state'=>1])->field("id,name")->select()
            ];
         $data[] = [
                    'name_value'   => '证件类型',
                    'params_name'  => 'type',
                    // //'is_enum'       => 1,
                    'multi'        => Db::name('parameter')->where(['id'=>'8','state'=>1])->field('multi')->find()['multi'],//时候多选
                    'type'         =>Db::name('parameter')->where(['id'=>'8','state'=>1])->field('type')->find()['type'],
                    'input_type'   =>'0',
                    'params_value' => Db::name('parameter')->where(['parent_id'=>8,'module'=>6,'state'=>1])->field("id,name")->select()
            ];
        $data[] = [
                    'name_value'   =>'驾龄',
                    'params_name'  =>'driving_age',
                    //'is_enum'       =>1,
                    'multi'        =>Db::name('parameter')->where(['id'=>'133','state'=>1,'parent_id'=>0])->field('multi')->find()['multi'],
                    'type'         =>Db::name('parameter')->where(['id'=>'133','state'=>1])->field('type')->find()['type'],
                    'input_type'   =>Db::name('parameter')->where(['id'=>'133','state'=>1])->field('input_type')->find()['input_type'],
                    'params_value' => null,
            
                  ];

       $data[] = [
                    'name_value'   =>'姓名',
                    'params_name'  =>'name',
                    //'is_enum'       =>1,
                    'multi'        =>Db::name('parameter')->where(['id'=>'137','state'=>1,'parent_id'=>0])->field('multi')->find()['multi'],
                    'type'         =>Db::name('parameter')->where(['id'=>'137','state'=>1])->field('type')->find()['type'],
                    'input_type'   =>Db::name('parameter')->where(['id'=>'137','state'=>1])->field('input_type')->find()['input_type'],
                    'params_value' => null,
            
                  ];
         $data[] = [
                    'name_value'   =>'电话',
                    'params_name'  =>'telphone',
                    //'is_enum'       =>1,
                    'multi'        =>Db::name('parameter')->where(['id'=>'131','state'=>1,'parent_id'=>0])->field('multi')->find()['multi'],
                    'type'         =>Db::name('parameter')->where(['id'=>'131','state'=>1])->field('type')->find()['type'],
                    'input_type'   =>Db::name('parameter')->where(['id'=>'131','state'=>1])->field('input_type')->find()['input_type'],
                    'params_value' => null,
            
                  ];
         $data[] = [
                    'name_value'   =>'年龄',
                    'params_name'  =>'age',
                    //'is_enum'       =>1,
                    'multi'        =>Db::name('parameter')->where(['id'=>'132','state'=>1,'parent_id'=>0])->field('multi')->find()['multi'],
                    'type'         =>Db::name('parameter')->where(['id'=>'132','state'=>1])->field('type')->find()['type'],
                    'input_type'   =>Db::name('parameter')->where(['id'=>'132','state'=>1])->field('input_type')->find()['input_type'],
                    'params_value' => null,
                  ];
        $data[] = [
                    'name_value'   =>'性别',
                    'params_name'  =>'sex',
                    //'is_enum'       =>1,
                    'multi'        =>Db::name('parameter')->where(['id'=>'134','state'=>1,'parent_id'=>0])->field('multi')->find()['multi'],
                    'type'         =>Db::name('parameter')->where(['id'=>'134','state'=>1])->field('type')->find()['type'],
                    'input_type'   =>null,
                    'params_value' => Db::name('parameter')->where(['parent_id'=>'134','state'=>1])->field('id,name')->select(),
                  ];
        $data[] = [
                    'name_value'   =>'民族',
                    'params_name'  =>'nation',
                    //'is_enum'       =>1,
                    'multi'        =>Db::name('parameter')->where(['id'=>'130','state'=>1,'parent_id'=>0])->field('multi')->find()['multi'],
                    'type'         =>Db::name('parameter')->where(['id'=>'130','state'=>1])->field('type')->find()['type'],
                    'input_type'   =>null,
                    'params_value' => Db::name('parameter')->where(['parent_id'=>'130','state'=>1])->field('id,name')->select(),
                  ];
        $data[] = [
                    'name_value'   =>'教育程度',
                    'params_name'  =>'education_level',
                    //'is_enum'       =>1,
                    'multi'        =>Db::name('parameter')->where(['id'=>'14','state'=>1,'parent_id'=>0])->field('multi')->find()['multi'],
                    'type'         =>Db::name('parameter')->where(['id'=>'14','state'=>1])->field('type')->find()['type'],
                    'input_type'   =>null,
                    'params_value' => Db::name('parameter')->where(['parent_id'=>'14','state'=>1])->field('id,name')->select(),
                  ];
        $data[] = [
                    'name_value'   =>'工资要求',
                    'params_name'  =>'salary',
                    // //'is_enum'       =>1,
                    'multi'        =>Db::name('parameter')->where(['id'=>'23','state'=>1,'parent_id'=>0])->field('multi')->find()['multi'],
                    'type'         =>Db::name('parameter')->where(['id'=>'23','state'=>1])->field('type')->find()['type'],
                    'input_type'   =>null,
                    'params_value' => Db::name('parameter')->where(['parent_id'=>'23','state'=>1])->field('id,name')->select(),
                  ];
        
        
         $this->make_json_result('司机数据请求成功！',$data);
    }

    /**
     * 发布租赁信息
     */
    public function CreateLease()
    {   
       
         $data[] = [
                    'name_value'   =>'租赁类型',
                    'params_name'  =>'type',
                    //'is_enum'       =>1,
                    'multi'        =>Db::name('parameter')->where(['id'=>'57','state'=>1,'parent_id'=>0])->value('multi'),
                    'is_enter'         =>Db::name('parameter')->where(['id'=>'57','state'=>1])->field('type')->value('type'),
                    'type'   =>Db::name('parameter')->where(['id'=>'57','state'=>1])->field('input_type')->value('input_type'),
                    'params_value' =>Db::name('parameter')->where(['parent_id'=>'57','state'=>1])->field('id,name')->select(),
            
                  ];
        $data[] = [
            'name' => '机械类型与品牌',
            'params_name' => 'mechanical_brand',
            'is_enter' => 1,
            'multi' => 0,
            'params_value' =>Db::name('mech_ascc')
                           ->alias('a')
                           ->join('mechanical b ','b.id= a.mechanical_id','LEFT')
                           ->join('brand c ','c.id= a.brand_id','LEFT')
                           ->order('c.create_time desc')
                           ->field('a.*,b.mechanical_name,c.brand_name')
                           ->select()
        ];
        $data[] = [
            'name' => '机械型号列表',
            'params_name' => 'mechanical_model_id',
            'is_enter' => 2,
            'multi' => 0,
            'params_value' => Db::name('mechanical_model')->field('model_name,mechanical_id,brand_id')->select()
        ];

        $data[] = [
                    'name_value'   =>'类型',
                    'params_name'  =>'class_id',
                    //'is_enum'       =>1,
                    'multi'        =>Db::name('parameter')->where(['id'=>'57','state'=>1,'parent_id'=>0])->value('multi'),
                    'is_enter'         =>Db::name('parameter')->where(['id'=>'57','state'=>1])->field('type')->value('type'),
                    'type'   =>Db::name('parameter')->where(['id'=>'57','state'=>1])->field('input_type')->value('input_type'),
                    'params_value' =>Db::name('parameter')->where(['parent_id'=>'57','state'=>1])->field('id,name')->select(),
            
                  ];
        // 姓名

        $data[] = [
                    'name_value'   =>'姓名',
                    'params_name'  =>'name',
                    //'is_enum'       =>1,
                    'multi'        =>Db::name('parameter')->where(['id'=>'120','state'=>1,'parent_id'=>0])->value('multi'),
                    'is_enter'         =>Db::name('parameter')->where(['id'=>'120','state'=>1])->field('type')->value('type'),
                    'type'   =>Db::name('parameter')->where(['id'=>'120','state'=>1])->field('input_type')->value('input_type'),
                    'params_value' =>NULL,
            
                  ];
        // 民族
      $data[] = [
            'name_value'   =>'民族',
            'params_name'  =>'nation',
            //'is_enum'       =>1,
            'multi'        =>Db::name('parameter')->where(['id'=>'121','state'=>1,'parent_id'=>0])->value('multi'),
            'is_enter'         =>Db::name('parameter')->where(['id'=>'121','state'=>1])->field('type')->value('type'),
            'type'   =>Db::name('parameter')->where(['id'=>'121','state'=>1])->field('input_type')->value('input_type'),
            'params_value' =>NULL,
    
          ];     
        // 电话
          $data[] = [
            'name_value'   =>'电话',
            'params_name'  =>'telphone',
            //'is_enum'       =>1,
            'multi'        =>Db::name('parameter')->where(['id'=>'122','state'=>1,'parent_id'=>0])->value('multi'),
            'is_enter'         =>Db::name('parameter')->where(['id'=>'122','state'=>1])->field('type')->value('type'),
            'type'   =>Db::name('parameter')->where(['id'=>'122','state'=>1])->field('input_type')->value('input_type'),
            'params_value' =>NULL,
    
          ];
        // 车牌号
             $data[] = [
            'name_value'   =>'车牌号',
            'params_name'  =>'car_num',
            //'is_enum'       =>1,
            'multi'        =>Db::name('parameter')->where(['id'=>'123','state'=>1,'parent_id'=>0])->value('multi'),
            'is_enter'         =>Db::name('parameter')->where(['id'=>'123','state'=>1])->field('type')->value('type'),
            'type'   =>Db::name('parameter')->where(['id'=>'123','state'=>1])->field('input_type')->value('input_type'),
            'params_value' =>NULL,
    
          ];
        
        // 方数
           $data[] = [
            'name_value'   =>'方数',
            'params_name'  =>'squares',
            //'is_enum'       =>1,
            'multi'        =>Db::name('parameter')->where(['id'=>'124','state'=>1,'parent_id'=>0])->value('multi'),
            'is_enter'         =>Db::name('parameter')->where(['id'=>'124','state'=>1])->field('type')->value('type'),
            'type'   =>Db::name('parameter')->where(['id'=>'124','state'=>1])->field('input_type')->value('input_type'),
            'params_value' =>NULL,
    
          ];
        // 吨数
           $data[] = [
            'name_value'   =>'吨数',
            'params_name'  =>'tonnage',
            //'is_enum'       =>1,
            'multi'        =>Db::name('parameter')->where(['id'=>'125','state'=>1,'parent_id'=>0])->value('multi'),
            'typis_entere'         =>Db::name('parameter')->where(['id'=>'125','state'=>1])->field('type')->value('type'),
            'type'   =>Db::name('parameter')->where(['id'=>'125','state'=>1])->field('input_type')->value('input_type'),
            'params_value' =>NULL,
    
          ];
        // 小时
           $data[] = [
            'name_value'   =>'小时',
            'params_name'  =>'hours',
            //'is_enum'       =>1,
            'multi'        =>Db::name('parameter')->where(['id'=>'126','state'=>1,'parent_id'=>0])->value('multi'),
            'is_enter'         =>Db::name('parameter')->where(['id'=>'126','state'=>1])->field('type')->value('type'),
            'type'   =>Db::name('parameter')->where(['id'=>'126','state'=>1])->field('input_type')->value('input_type'),
            'params_value' =>NULL,
    
          ];
        // 生产国
          $data[] = [
            'name_value'   =>'生产国',
            'params_name'  =>'country',
            //'is_enum'       =>1,
            'multi'        =>Db::name('parameter')->where(['id'=>'139','state'=>1,'parent_id'=>0])->value('multi'),
            'is_enter'         =>Db::name('parameter')->where(['id'=>'139','state'=>1])->field('type')->value('type'),
            'type'   =>Db::name('parameter')->where(['id'=>'139','state'=>1])->field('input_type')->value('input_type'),
            'params_value' =>NULL,
    
          ];
        // 发票
          $data[] = [
            'name_value'   =>'发票',
            'params_name'  =>'invoice',
            //'is_enum'       =>1,
            'multi'        =>Db::name('parameter')->where(['id'=>'145','state'=>1,'parent_id'=>0])->value('multi'),
            'is_enter'         =>Db::name('parameter')->where(['id'=>'145','state'=>1])->field('type')->value('type'),
            'type'   =>Db::name('parameter')->where(['id'=>'145','state'=>1])->field('input_type')->value('input_type'),
            'params_value' =>NULL,
    
          ];
        // 合格证
          $data[] = [
            'name_value'   =>'合格证',
            'params_name'  =>'qualified',
            //'is_enum'       =>1,
            'multi'        =>Db::name('parameter')->where(['id'=>'146','state'=>1,'parent_id'=>0])->value('multi'),
            'is_enter'         =>Db::name('parameter')->where(['id'=>'146','state'=>1])->field('type')->value('type'),
            'type'   =>Db::name('parameter')->where(['id'=>'146','state'=>1])->field('input_type')->value('input_type'),
            'params_value' =>NULL,
    
          ];
        // 设备照片
          $data[] = [
            'name_value'   =>'设备照片',
            'params_name'  =>'pic',
            //'is_enum'       =>1,
            'multi'        =>Db::name('parameter')->where(['id'=>'147','state'=>1,'parent_id'=>0])->value('multi'),
            'is_enter'         =>Db::name('parameter')->where(['id'=>'147','state'=>1])->field('type')->value('type'),
            'type'   =>Db::name('parameter')->where(['id'=>'147','state'=>1])->field('input_type')->value('input_type'),
            'params_value' =>NULL,
    
          ];

        // ee($data);
         $this->make_json_result('租赁数据请求成功！',$data);

    }

    /**
     * 发布融资按揭
     */
    public function Creatfinancing()
    {
        $data[]= [
            'name'         => '融资类型',
            'params_name'  => 'fianceing_typeid',
            'is_enter'     => Db::name('parameter')->where(['id'=>'92','state'=>1])->value('type'),
            'multi'        => Db::name('parameter')->where(['id'=>'92','state'=>1])->value('multi'),
            'params_value' => Db::name('parameter')->where(['parent_id'=>'92','state'=>1])->field('id,name')->select(),
        ];
        // 公司名称
        $data[] = [
            'name'         => '公司名称',
            'params_name'  => 'company_name',
            'is_enter'     => Db::name('parameter')->where(['id'=>'97','state'=>1])->value('type'),
            'type'         => Db::name('parameter')->where(['id'=>'97','state'=>1])->field('input_type')->value('input_type'),
            'multi'        => Db::name('parameter')->where(['id'=>'97','state'=>1])->value('multi'),
            'params_value' => Db::name('parameter')->where(['parent_id'=>'97','state'=>1])->field('id,name')->select(),
                    
        ];
        // 电话号码
        $data[] = [
            'name'         => '电话',
            'params_name'  => 'company_name',
            'is_enter'     => Db::name('parameter')->where(['id'=>'98','state'=>1])->value('type'),
            'type'         => Db::name('parameter')->where(['id'=>'98','state'=>1])->field('input_type')->value('input_type'),
            'multi'        => Db::name('parameter')->where(['id'=>'98','state'=>1])->value('multi'),
            'params_value' => Db::name('parameter')->where(['parent_id'=>'98','state'=>1])->field('id,name')->select(),
                    
        ];
        // 联系人 
        $data[] = [
            'name'         => '联系人',
            'params_name'  => 'member_list_id',
            'is_enter'     => Db::name('parameter')->where(['id'=>'214','state'=>1])->value('type'),
            'type'         => Db::name('parameter')->where(['id'=>'214','state'=>1])->field('input_type')->value('input_type'),
            'multi'        => Db::name('parameter')->where(['id'=>'214','state'=>1])->value('multi'),
            'params_value' => Db::name('parameter')->where(['parent_id'=>'214','state'=>1])->field('id,name')->select(),
                    
        ];
        // 资质上传
        $data[] = [
            'name'         => '资质上传',
            'params_name'  => 'aptitude',
            'is_enter'     => Db::name('parameter')->where(['id'=>'113','state'=>1])->value('type'),
            'type'         => Db::name('parameter')->where(['id'=>'113','state'=>1])->field('input_type')->value('input_type'),
            'multi'        => Db::name('parameter')->where(['id'=>'113','state'=>1])->value('multi'),
            'params_value' => Db::name('parameter')->where(['parent_id'=>'113','state'=>1])->field('id,name')->select(),
                    
        ];
        $this->make_json_result('配件数据请求成功！',$data);
    }

    /**
     * 发布配件
     */
    public function CreatCertificates()
    {
        $data[] = [
            'name' => '车辆类型',
            'params_name' => 'car_type',
            'is_enter' => Db::name('parameter')->where(['id'=>'201','state'=>1])->value('type'),
            'multi' => Db::name('parameter')->where(['id'=>'201','state'=>1])->value('multi'),
            'params_value' => Db::name('parameter')->where(['parent_id'=>'201','state'=>1])->field('id,name')->select(),
        ];

        $data[] = [
            'name' => '配件种类',
            'params_name' => 'parts_tid',
            'is_enter' => Db::name('parameter')->where(['id'=>'72','state'=>1])->value('type'),
            'multi' => Db::name('parameter')->where(['id'=>'72','state'=>1])->value('multi'),
            'params_value' => Db::name('parameter')->where(['parent_id'=>'72','state'=>1])->field('id,name')->select(),
        ];

        $data[] = [
            'name' => '服务类型',
            'params_name' => 'service_tid',
            'is_enter' => Db::name('parameter')->where(['id'=>'73','state'=>1])->value('type'),
            'multi' => Db::name('parameter')->where(['id'=>'73','state'=>1])->value('multi'),
            'params_value' => Db::name('parameter')->where(['parent_id'=>'73','state'=>1])->field('id,name')->select(),
        ];

        $data[] = [
            'name' => '姓名',
            'params_name' => 'username',
            'is_enter' => Db::name('parameter')->where(['id'=>'205','state'=>1])->value('type'),
            'type'=>Db::name('parameter')->where(['id'=>'205','state'=>1])->value('input_type'),
            'params_value' => ''
        ];

        $data[] = [
            'name' => '标题',
            'params_name' => 'title',
            'is_enter' => Db::name('parameter')->where(['id'=>'215','state'=>1])->value('type'),
            'type'=>Db::name('parameter')->where(['id'=>'215','state'=>1])->value('input_type'),
            'params_value' => ''
        ];

        $data[] = [
            'name' => '描述',
            'params_name' => 'content',
            'is_enter' => Db::name('parameter')->where(['id'=>'216','state'=>1])->value('type'),
            'type'=>Db::name('parameter')->where(['id'=>'216','state'=>1])->value('input_type'),
            'params_value' => ''
        ];

        $data[] = [
            'name' => '封面图',
            'params_name' => 'file0',
            'is_enter' => Db::name('parameter')->where(['id'=>'217','state'=>1])->value('type'),
            'type'=>Db::name('parameter')->where(['id'=>'217','state'=>1])->value('input_type'),
            'params_value' => ''
        ];

        $data[] = [
            'name' => '联系方式',
            'params_name' => 'phone',
            'is_enter' => Db::name('parameter')->where(['id'=>'204','state'=>1])->value('type'),
            'type'=>Db::name('parameter')->where(['id'=>'204','state'=>1])->value('input_type'),
            'params_value' => ''
        ];
        $this->make_json_result('配件数据请求成功！',$data);
    }

    /**
     * 发布修理厂信息
     */
    public function CreateRepair()
    {
        $data[] = [
            'name' => '修理厂类型',
            'params_name' => 'repair_type',
            'is_enter' => Db::name('parameter')->where(['id'=>'210','state'=>1])->value('type'),
            'multi' => Db::name('parameter')->where(['id'=>'210','state'=>1])->value('multi'),
            'params_value' => Db::name('parameter')->where(['parent_id'=>'210','state'=>1])->field('id,name')->select(),
        ];

        $data[] = [
            'name' => '工种',
            'params_name' => 'class_id',
            'is_enter' => Db::name('parameter')->where(['id'=>'3','state'=>1])->value('type'),
            'multi' => Db::name('parameter')->where(['id'=>'3','state'=>1])->value('multi'),
            'params_value' => Db::name('parameter')->where(['parent_id'=>'3','state'=>1])->field('id,name')->select(),
        ];

        $data[] = [
            'name' => '服务类型',
            'params_name' => 'up_server',
            'is_enter' => Db::name('parameter')->where(['id'=>'127','state'=>1])->value('type'),
            'multi' => Db::name('parameter')->where(['id'=>'127','state'=>1])->value('multi'),
            'params_value' => Db::name('parameter')->where(['parent_id'=>'127','state'=>1])->field('id,name')->select(),
        ];

        $data[] = [
            'name' => '封面图',
            'params_name' => 'file0',
            'is_enter' => Db::name('parameter')->where(['id'=>'208','state'=>1])->value('type'),
            'type'=>Db::name('parameter')->where(['id'=>'205','state'=>1])->value('input_type'),
            'params_value' => ''
        ];

        $data[] = [
            'name' => '多图上传',
            'params_name' => 'fileurl_tmp',
            'is_enter' => Db::name('parameter')->where(['id'=>'209','state'=>1])->value('type'),
            'type'=>Db::name('parameter')->where(['id'=>'205','state'=>1])->value('input_type'),
            'params_value' => ''
        ];

        $data[] = [
            'name' => '姓名',
            'params_name' => 'name',
            'is_enter' => Db::name('parameter')->where(['id'=>'207','state'=>1])->value('type'),
            'type'=>Db::name('parameter')->where(['id'=>'207','state'=>1])->value('input_type'),
            'params_value' => ''
        ];

        $data[] = [
            'name' => '联系方式',
            'params_name' => 'telphone',
            'is_enter' => Db::name('parameter')->where(['id'=>'206','state'=>1])->value('type'),
            'type'=>Db::name('parameter')->where(['id'=>'206','state'=>1])->value('input_type'),
            'params_value' => ''
        ];
        $this->make_json_result('修理厂数据请求成功！',$data);
    }

    /**
     * 发布证书代办信息
     */
    public function CreatCards()
    {
        $data[] = [
            'name_value'   => '驾驶证种类',
            'params_name'  => 'certificate_typeid',
            //'is_enum'    => 1,
            'multi'        => 0,
            'type'         =>Db::name('parameter')->where(['id'=>'37'])->field('type')->find()['type'],
            'input_type'   =>'0',
            'params_value' => Db::name('parameter')->where(['parent_id'=>37,'module'=>9])->field("id,name")->select()
            ];
        $data[] =[
            'name_value'   => '驾校名称',
            'params_name'  => 'schoolname',
            //'is_enum'    => 1,
            'multi'        => 0,
            'type'         =>Db::name('parameter')->where(['id'=>'138'])->field('type')->find()['type'],
            'input_type'   =>Db::name('parameter')->where(['id'=>'138'])->field('input_type')->find()['input_type'],
            'params_value' => null,
            
            ];
            // ee($data);
        $this->make_json_result('代办证件数据请求成功！',$data);
    }


    /**
     * 提交二手机械
     * @return mixed
     */
    public function usedmechanicsAdd()
    {
        $check_open = getcheck('mechanicsSetting');
        $rule = [
            'member_list_id' => 'require',
            'mechanical_id' => 'require|number',
            'brand_id' => 'require|number',
            'mechanical_model_id' => 'require|number',
            'province_id' => 'require|number',
            'city_id' => 'require|number',
            'company_name' => 'require|chs',
            'tonnage' => 'require|number',
            'price' => 'require|number',
            'work_hours' => 'require|number',
            'manufacture_date' => 'require|dateFormat:Y-m-d',
            'car_number' => 'require|chsAlphaNum',
            'is_invoice' => 'require',
            'is_certificate' => 'require',
            'telphone' => 'require|/^1[3456789]{1}\d{9}$/',
        ];
        $msg = [
            'member_list_id.require' => '用户id未传入',
            'producing_country.require' => '请选择生产国',
            'mechanical_id.require' => '请选择机械类型',
            'brand_id.require' => '请选择品牌',
            'mechanical_model_id.require' => '请选择型号',
            'province_id.require' => '请选择省份',
            'city_id.require' => '请选择市区',
            'company_name.require' => '请填写公司名称',
            'tonnage.require' => '请填写吨位',
            'work_hours.require' => '请填写工作时长',
            'price.require' => '请填写价格',
            'car_number.require' => '请填写车牌号',
            'telphone.require' => '请填写联系方式',
            'is_invoice.require' => '请选择是否提供发票',
            'is_certificate.require' => '请选择是否有合格证',
            'manufacture_date.require' => '请选择出厂日期',
            'producing_country.in' => '请选择正确的生产国',
            'mechanical_id.number' => '请选择正确的机械类型',
            'brand_id.number' => '请选择正确的品牌',
            'mechanical_model_id.number' => '请选择正确的型号',
            'province_id.number' => '请选择正确的省份',
            'city_id.number' => '请选择正确的市区',
            'company_name.chs' => '请选择正确的公司名称',
            'tonnage.number' => '请选择正确的吨位信息',
            'work_hours.number' => '工作时长必须为数字',
            'manufacture_date.dateFormat' => '出厂日期格式不正确',
            'price.number' => '价格必须为数字',
            'car_number.chsAlphaNum' => '请选择正确的车牌号',
            'telphone./^1[3456789]{1}\d{9}$/' => '请输入正确的手机号',
        ];
        $data = [
            'producing_country' => input('producing_country'),
            'mechanical_id' => input('mechanical_id'),
            'brand_id' => input('brand_id'),
            'mechanical_model_id' => input('mechanical_model_id'),
            'province_id' => input('province_id'),
            'city_id' => input('city_id'),
            'company_name' => input('company_name'),
            'tonnage' => input('tonnage'),
            'nation' => input('nation'),
            'work_hours' => input('work_hours'),
            'manufacture_date' => input('manufacture_date'),
            'is_invoice' => input('is_invoice'),
            'is_certificate' => input('is_certificate'),
            'telphone' => input('telphone'),
            'car_number' => input('car_number'),
            'price' => input('price'),
            'member_list_id' => input('member_list_id'),
            'is_effective' => 1,
            'is_check' => $check_open,
            'create_time' => time(),
            'type' => 2
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            $this->make_json_error($validate->getError(), 1);
        }
        $res = Db::name('mechanics')->insert($data);
        //图片上传待完成
        if ($res) {
            $this->make_json_result('发布成功！');
        }
    }

    /**
     * 提交司机信息
     */
    public function driverAdd()
    {
        $check_open = getcheck('driverSetting');
        $rule = [
            'member_list_id' => 'require',
            'type'=>'require|number',
            'class_id'=>'require|number',
            'salary' => 'require|number',
            'driving_age' => 'require|number',
            'education_level' => 'require|number',
            'age' => 'require|number',
            'sex' => 'require|in:0,1',
            'province_id' => 'require|number',
            'city_id' => 'require|number',
            'province' => 'require|number',
            'city' => 'require|number',
            'name' => 'require|chs',
            'nation' => 'require|chs',
            'telphone' => 'require|/^1[3456789]{1}\d{9}$/',
        ];
        $msg = [
            'member_list_id.require' => '用户id未传入',
            'type.require'=>'请选择司机类型',
            'class_id.require'=>'请选择驾照类型',
            'province_id.require' => '请选择省份',
            'city_id.require' => '请选择市区',
            'province.require' => '请选择省份',
            'city.require' => '请选择市区',
            'salary.require' => '请选择薪资要求',
            'driving_age.require' => '请选择驾龄',
            'education_level.require' => '请选择教育背景',
            'age.require' => '请选择年龄',
            'sex.require' => '请选择性别',
            'name.require' => '请填写姓名',
            'nation.require' => '请填写民族',
            'telphone.require' => '请填写联系方式',
            'type.number' => '请选择正确的司机类型',
            'class_id.number' => '请选择正确的驾照类型',
            'salary.number' => '请选择正确的薪水区间',
            'driving_age.number' => '请选择正确的驾龄',
            'education_level.number' => '请选择正确的教育背景',
            'age.number' => '请选择正确的年龄',
            'sex.in' => '请选择正确的性别',
            'province_id.number' => '请选择正确的省份',
            'city_id.number' => '请选择正确的市区',
            'province.number' => '请选择正确的省份',
            'city.number' => '请选择正确的市区',
            'name.chs' => '请选择正确的姓名',
            'nation.chs' => '请选择正确的民族',
            'telphone./^1[3456789]{1}\d{9}$/' => '请输入正确的手机号',
        ];
        $data = [
            'type' => input('type'),
            'class_id' => input('class_id'),
            'member_list_id' => input('member_list_id'),
            'province_id' => input('province_id'),
            'city_id' => input('city_id'),
            'salary' => input('salary'),
            'driving_age' => input('driving_age'),
            'education_level' => input('education_level'),
            'age' => input('age'),
            'name' => input('name'),
            'nation' => input('nation'),
            'telphone' => input('telphone'),
            'province' => input('province'),
            'city' => input('city'),
            'sex' => input('sex'),
            'update_time' => time(),
            'create_time' => time(),
            'is_check' => $check_open,
            'is_effective' => 1,
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            $this->make_json_error($validate->getError(), 1);
        }
        $res = Db::name('driver')->insert($data);
        if ($res) {
            $this->make_json_result('发布成功！');
        }
    }

    /**
     * 提交租赁信息
     */
    public function LeaseAdd()
    {
        $check_open = getcheck('LeaseSetting');
        $rule = [
            'member_list_id' => 'require',
            'type'=>'require|number',
            'class_id'=>'require|number',
            'mechanical_id' => 'number',
            'brand_id' => 'number',
            'mechanical_model_id' => 'number',
            'squares' => 'number',
            'tonnage' => 'number',
            'hours' => 'number',
            'manufacture_date' => 'dateFormat:Y-m-d',
            'province_id' => 'require|number',
            'city_id' => 'require|number',
            'name' => 'require|chs',
            'nation' => 'require|chs',
            'telphone' => 'require|/^1[3456789]{1}\d{9}$/',
        ];
        $msg = [
            'member_list_id.require' => '用户id未传入',
            'type.require'=>'请选择租赁类型',
            'class_id.require'=>'请选择类型',
            'mechanical_id.number' => '请选择正确的机械类型',
            'brand_id.number' => '请选择正确的品牌',
            'mechanical_model_id.number' => '请选择正确的型号',
            'province_id.require' => '请选择省份',
            'city_id.require' => '请选择市区',
            'manufacture_date.dateFormat' => '请选择正确的出厂日期',
            'name.require' => '请填写姓名',
            'nation.require' => '请填写民族',
            'telphone.require' => '请填写联系方式',
            'type.number' => '请选择正确的租赁类型',
            'class_id.number' => '请选择正确的类型',
            'squares.number' => '请填写正确的方数',
            'tonnage.number' => '请填写正确的吨数',
            'hours.number' => '请选择正确的工作时长',
            'province_id.number' => '请选择正确的省份',
            'city_id.number' => '请选择正确的市区',
            'name.chs' => '请选择正确的姓名',
            'nation.chs' => '请选择正确的民族',
            'telphone./^1[3456789]{1}\d{9}$/' => '请输入正确的手机号',
        ];
        $data = [
            'member_list_id' => input('member_list_id'),
            'type' => input('type'),
            'mechanical_id' => input('mechanical_id'),
            'brand_id' => input('brand_id'),
            'mechanical_model_id' => input('mechanical_model_id'),
            'class_id' => input('class_id'),
            'province_id' => input('province_id'),
            'city_id' => input('city_id'),
            'squares' => input('squares'),
            'tonnage' => input('tonnage'),
            'manufacture_date' => input('manufacture_date'),
            'hours' => input('hours'),
            'car_num' => input('car_num'),
            'name' => input('name'),
            'nation' => input('nation'),
            'telphone' => input('telphone'),
            'update_time' => time(),
            'create_time' => time(),
            'is_check' => $check_open,
            'is_effective' => 1,
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            $this->make_json_error($validate->getError(), 1);
        }
        $res = Db::name('lease')->insert($data);
        if ($res) {
            $this->make_json_result('发布成功！');
        }
    }

    /**
     * 提交融资按揭信息
     */
    public function financingadd()
    {
        $check_open = getcheck('FinancingSetting');
        $rule = [
            'member_list_id' => 'require',
            'fianceing_typeid'=>'require|number',
            'company_name'=>'require|chs',
            'phone' => 'require|/^1[3456789]{1}\d{9}$/',
            'province_id' => 'require|number',
            'city_id' => 'require|number',
        ];
        $msg = [
            'member_list_id.require' => '用户id未传入',
            'fianceing_typeid.require' => '类型未选择',
            'fianceing_typeid.number' => '请选择正确的类型',
            'company_name.require' => '请填写姓名',
            'company_name.chs' => '请选择正确的姓名',
            'phone.require' => '联系方式未填写',
            'phone./^1[3456789]{1}\d{9}$/' => '请输入正确的联系方式',
            'province_id.require' => '请选择省份',
            'city_id.require' => '请选择市区',
            'province_id.number' => '请选择正确的省份',
            'city_id.number' => '请选择正确的市区',
        ];
        $data=[
            'fianceing_typeid' => input('type'),
            'member_list_id' => input('member_list_id'),
            'company_name'=>input('company_name'),
            'phone'=>input('telphone'),
            'province_id'=>input('province_id'),
            'city_id'=>input('city_id'),
            'is_check'=>$check_open,
            'is_effective'=>1,
            'createtime'=>time(),
            'updatetime'=>time(),
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            $this->make_json_error($validate->getError(), 1);
        }
        //图片上传待处理
        $res=Db::name('financing')->insert($data);
        if ($res) {
            $this->make_json_result('发布成功！');
        }
    }

    /**
     * 提交配件信息
     */
    public function certificatesAdd()
    {
        $check_open = getcheck('partsSetting');
        $rule = [
            'member_list_id' => 'require',
            'car_tid'=>'require|number',
            'username'=>'require|chs',
            'phone' => 'require|/^1[3456789]{1}\d{9}$/',
            'province_id' => 'require|number',
            'city_id' => 'require|number',
            'parts_tid' => 'require|number',
            'service_tid' => 'require|number',
            'title'  => 'require',
            'content'  => 'require',
        ];
        $msg = [
            'member_list_id.require' => '用户id未传入',
            'car_tid.require' => '配件类型未选择',
            'car_tid.number' => '请选择正确的配件类型',
            'parts_tid.require' => '配件种类未选择',
            'phone.require' => '联系方式未填写',
            'title.require' => '标题未填写',
            'content.require' => '配件描述未填写',
            'parts_tid.number' => '请选择正确的配件种类',
            'service_tid.require' => '服务类型未选择',
            'service_tid.number' => '请选择正确的服务类型',
            'username.require' => '请填写公司名称',
            'username.chs' => '请选择正确的公司名称',
            'phone./^1[3456789]{1}\d{9}$/' => '请输入正确的手机号',
            'province_id.require' => '请选择省份',
            'city_id.require' => '请选择市区',
            'province_id.number' => '请选择正确的省份',
            'city_id.number' => '请选择正确的市区',
        ];
        $data=[
            'member_list_id' => input('member_list_id'),
            'car_tid' =>input('car_type'),
            'parts_tid' => input('parts_tid'),
            'service_tid' => input('service_tid'),
            'province_id' => input('province_id'),
            'city_id' => input('city_id'),
            'username'=> input('username'),
            'phone' => input('phone'),
            'title' => trim(input('title')),
            'content' => trim(input('content')),
            'updatetime' => time(),
            'createtime' => time(),
            'is_check' => $check_open,
            'is_effective' => 1,
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            $this->make_json_error($validate->getError(), 1);
        }
        //图片上传待处理
        $res=Db::name('parts')->insert($data);
        if ($res) {
            $this->make_json_result('发布成功！');
        }
    }

    /**
     * 提交修理厂信息
     */
    public function repairAdd()
    {
        $check_open = getcheck('repairSetting');

        //图片处理
        $file = request()->file('file0');
        if($file)
        {
            // 移动到框架应用根目录/public/data/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'data'. DS . 'uploads');
            if($info) {
                $shop_img_url=substr(\think\Config::get('UPLOAD_DIR'),1).$info->getSaveName();//如果上传成功则完成路径拼接
            }else{
                $this->error($file->getError(),url('index'),0);//否则就是上传错误，显示错误原因
            }
        }
        $rule = [
            'member_list_id' => 'require',
            'name'=>'require|chs',
            'telphone' => 'require|/^1[3456789]{1}\d{9}$/',
            'province_id' => 'require|number',
            'city_id' => 'require|number',
            'type' => 'require|number',
            'up_server' => 'require',
            'class_id' => 'require',
        ];
        $msg = [
            'member_list_id.require' => '用户id未传入',
            'type_tid.require' => '修理厂种类未选择',
            'class_id.require' => '工种未选择',
            'telphone.require' => '联系方式未填写',
            'type.number' => '请选择正确的修理厂种类',
            'up_server.require' => '服务类型未选择',
            'name.require' => '请填写公司名称',
            'name.chs' => '请选择正确的公司名称',
            'phone./^1[3456789]{1}\d{9}$/' => '请输入正确的手机号',
            'province_id.require' => '请选择省份',
            'city_id.require' => '请选择市区',
            'province_id.number' => '请选择正确的省份',
            'city_id.number' => '请选择正确的市区',
        ];
        $data = [
            'member_list_id' => input('member_list_id'),
            'type' => input('type'),
            'class_id' => implode(',',input('class_id/a','')),
            'up_server' => implode(',',input('service_id/a','')),
            'province_id' => input('province_id'),
            'city_id' => input('city_id'),
            'name' => input('name'),
            'telphone' => input('telphone'),
            'shop_img_url' => $shop_img_url,
            'update_time' => time(),
            'create_time' => time(),
            'is_check' => $check_open ,
            'is_effective' => 1,
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            $this->make_json_error($validate->getError(), 1);
        }
        $id=Db::name('repair')->insertGetId($data);
        $fileurl_tmp = input('fileurl_tmp/a');
        //多图上传
        foreach ($fileurl_tmp as $key => $value) {
            $data = array(
                'uptime' => time(),
                'path' => $value,
                'product_id' => $id,
                'type' => 4
            );
            $res=Db::name('plug_files')->insert($data);
        }
        if ($res) {
            $this->make_json_result('发布成功！');
        }
    }


    /**
     * 提交证书代办信息
     */
    public function cardsAdd()
    {
        $check_open = getcheck('certificatesSetting');
        //店铺主图片处理
        $file = request()->file('file0');
        if($file)
        {
            // 移动到框架应用根目录/public/data/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'data'. DS . 'uploads');
            if($info) {
                $shop_img_url=substr(\think\Config::get('UPLOAD_DIR'),1).$info->getSaveName();//如果上传成功则完成路径拼接
            }else{
                $this->error($file->getError(),url('index'),0);//否则就是上传错误，显示错误原因
            }
        }
        $rule = [
            'member_list_id' => 'require',
            'schoolname'=>'require|chs',
            'phone' => 'require|/^1[3456789]{1}\d{9}$/',
            'province_id' => 'require|number',
            'city_id' => 'require|number',
            'certificate_typeid' => 'require|number',
            'certificate_level' => 'require',
        ];
        $msg = [
            'member_list_id.require' => '用户id未传入',
            'certificate_typeid.require' => '机构种类未选择',
            'certificate_level.require' => '证书未选择',
            'telphone.require' => '联系方式未填写',
            'certificate_typeid.number' => '请选择正确的机构种类',
            'schoolname.require' => '请填写公司名称',
            'schoolname.chs' => '请选择正确的公司名称',
            'phone./^1[3456789]{1}\d{9}$/' => '请输入正确的手机号',
            'province_id.require' => '请选择省份',
            'city_id.require' => '请选择市区',
            'province_id.number' => '请选择正确的省份',
            'city_id.number' => '请选择正确的市区',
        ];
        $data = [
            'shop_img_url' => $shop_img_url,
            'member_list_id' => input('member_list_id'),
            'certificate_typeid' => input('type'),
            'schoolname' => input('school'),
            'province_id' => input('province_id'),
            'city_id' => input('city_id'),
            'phone'=>input('phone'),
            'certificate_level'=>implode(',',input('certificate_level/a','')),
            'updatetime' => time(),
            'createtime' => time(),
            'is_check' => $check_open ,
            'is_effective' => 1,
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            $this->make_json_error($validate->getError(), 1);
        }
        $id=Db::name('certificate')->insertGetId($data);
        //多图上传
        $fileurl_tmp = input('fileurl_tmp/a');
        foreach ($fileurl_tmp as $key => $value) {
            $data = array(
                'uptime' => time(),
                'path' => $value,
                'product_id' => $id,
                'type' => 4
            );
            $res=Db::name('plug_files')->insert($data);
        }
        if ($res) {
            $this->make_json_result('发布成功！');
        }
    }
}