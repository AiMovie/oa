<?php
// +----------------------------------------------------------------------
// | 功能：修改发布信息功能
// +----------------------------------------------------------------------
// | 作者：张春美
// +----------------------------------------------------------------------
// | 日期：2017-8-4
// +----------------------------------------------------------------------

namespace app\api\controller;


use app\Common\controller\Common;
use think\Db;
use think\Request;
use org\Stringnew;
use think\Cookie;
use think\Validate;

class Updatepublish extends Common
{

    /**
     *修改新机械发布信息
     */
    public function getNewsMechanics()
    {
        if($_POST)
        {
            $mechanics_id = input('mechanics_id');//机械的id
            if(!$mechanics_id)
            {
                $this->make_json_error('获取数据错误,请稍后重试',1);
            }
            $map['a.id'] = $mechanics_id;
            $result = Db::name('mechanics')
                ->alias('a')
                ->join('mechanical b ','b.id= a.mechanical_id','LEFT')
                ->join('brand c ','c.id= a.brand_id','LEFT')
                ->join('mechanical_model d ','d.id= a.mechanical_model_id','LEFT')
                ->join('region e ','e.id= a.city_id','LEFT')
                ->join('region f ','f.id= a.province_id','LEFT')
                ->join('parameter g ','g.id= a.producing_country','LEFT')
                ->where($map)
                ->field('a.id,a.manufacture_date,a.is_invoice,a.is_certificate,a.company_name,a.telphone,a.linkman,a.mechanical_id,a.brand_id,a. mechanical_model_id,a.producing_country as producing_country_id,b.mechanical_name,c.brand_name,d.model_name,f.name as province_name,e.name as city_name,g.name as product_country')
                ->find();
            //是否有发票
            if($result['is_invoice'] ==1)
            {
                $result['is_invoice'] ="有";
            }
            else
            {
                $result['is_invoice'] ="无";
            }

            //是否有合格证
            if($result['is_certificate'] ==1)
            {
                $result['is_certificate'] ="有";
            }
            else
            {
                $result['is_certificate'] ="无";
            }
            //查询设备图片
            $result['img_url_list'] = Db::name('plug_files')->where(array('product_id'=>$mechanics_id,'type'=>1))->field('path')->select();

            $this->make_json_result('获取数据成功',$result);
        }
        
    }

    /**
     *提交新机械信息
     */
    public function upNewsMechanics()
    {

        $check_open = getcheck('newSetting');
        $id = input('id');
        if(!$id){
            $this->make_json_error('获取数据错误,请稍后重试',1);
        }
        $rule = [
            'producing_country_id' => 'require|in:160,161,162',
            'mechanical_id' => 'require|number',
            'brand_id' => 'require|number',
            'mechanical_model_id' => 'require|number',
            'province_id' => 'require|number',
            'city_id' => 'require|number',
            'company_name' => 'require|chs',
            'linkman' => 'require|chs',
            'telphone' => 'require|/^1[3456789]{1}\d{9}$/',
        ];
        $msg = [
            'id.require'=>'请传入机械id',
            'producing_country_id.require' => '请选择生产国',
            'mechanical_id.require' => '请选择机械类型',
            'brand_id.require' => '请选择品牌',
            'mechanical_model_id.require' => '请选择型号',
            'province_id.require' => '请选择省份',
            'city_id.require' => '请选择市区',
            'company_name.require' => '请填写公司名称',
            'linkman.require' => '请填写联系人',
            'telphone.require' => '请填写联系方式',
            'producing_country.in' => '请选择正确的生产国',
            'mechanical_id.number' => '请选择正确的机械类型',
            'brand_id.number' => '请选择正确的品牌',
            'mechanical_model_id.number' => '请选择正确的型号',
            'province_id.number' => '请选择正确的省份',
            'city_id.number' => '请选择正确的市区',
            'company_name.chs' => '请选择正确的公司名称',
            'telphone./^1[3456789]{1}\d{9}$/' => '请输入正确的手机号',
        ];
        $data = [
            'id' => $id,
            'producing_country' => input('producing_country_id'),
            'mechanical_id' => input('mechanical_id'),
            'brand_id' => input('brand_id'),
            'mechanical_model_id' => input('mechanical_model_id'),
            'province_id' => input('province_id'),
            'city_id' => input('city_id'),
            'company_name' => input('company_name'),
            'is_invoice' => input('is_invoice'),
            'is_certificate'=>input('is_certificate'),
            'telphone' => input('telphone'),
            'linkman' => input('linkman'),
            'is_check' => $check_open,
            'is_effective' => 1,
            'type' => 1,
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            $this->make_json_error($validate->getError(), 1);
        }
        $res    = Db::name('mechanics')->where('id',input('id'))->update($data);
        $img_url= Db::name('plug_files')->where(['type'=>1,'product_id'=>$id])->delete();
        $fileurl_tmp = input('fileurl_tmp/a');
        //添加商品图片
        if(!empty($fileurl_tmp)){
            foreach ($fileurl_tmp as $key => $value) {
                $data = array(
                    'uptime' => time(),
                    'path' => $value,
                    'product_id' => $id,
                    'type' => 1
                );
                Db::name('plug_files')->insert($data);
            }
        }
        $this->make_json_result('恭喜您，修改成功！');
    }


    /**
     *
     * 修改二手机械信息
     */
    public function getOldMechanical(){
        if($_POST)
        {
            $mechanics_id = input('mechanics_id');
            if(!$mechanics_id)
            {
                $this->make_json_error('获取数据错误,请稍后重试',1);
            }
            $map['a.id'] = $mechanics_id;
            $result = array();
            $result = Db::name('mechanics')
                ->alias('a')
                ->join('mechanical b ','b.id= a.mechanical_id','LEFT')
                ->join('brand c ','c.id= a.brand_id','LEFT')
                ->join('mechanical_model d ','d.id= a.mechanical_model_id','LEFT')
                ->join('region e ','e.id= a.city_id','LEFT')
                ->join('region f ','f.id= a.province_id','LEFT')
                ->join('parameter g ','g.id= a.producing_country','LEFT')
                ->where($map)
                ->field('a.id,a.mechanical_id,a.brand_id,a.mechanical_model_id,a.work_hours,a.manufacture_date,a.price,a.linkman,a.telphone,a.brand_id,a.is_invoice,a.is_certificate,a.producing_country as producing_country_id,b.mechanical_name,c.brand_name,d.model_name,e.name as city_name,f.name as province_name,g.name as product_country')
                ->find();
            if(!empty($result))
            {
                //是否有发票
               if($result['is_invoice'] ==1)
                {
                    $result['is_invoice'] ="有";
                }
                else
                {
                    $result['is_invoice'] ="无";
                }

                //是否有合格证
                if($result['is_certificate'] ==1)
                {
                    $result['is_certificate'] ="有";
                }
                else
                {
                    $result['is_certificate'] ="无";
                }
                //查询设备图片
                $result['img_url_list'] = Db::name('plug_files')->where(array('product_id'=>$mechanics_id,'type'=>1))->field('path')->select(); 
            }
                
            $this->make_json_result('获取数据成功',$result);

        }
    }


    /**
     * 修改二手机械信息提交
     * 
     */
    public function upOldMechanics()
    {
        $check_open = getcheck('mechanicsSetting');
        if(!input('id'))
        {
            $this->make_json_error('获取数据错误,请稍后重试',1);
        }
        $rule = [
            'id' => 'require',
            'mechanical_id' => 'require|number',
            'brand_id' => 'require|number',
            'mechanical_model_id' => 'require|number',
            'province_id' => 'require|number',
            'city_id' => 'require|number',
            'company_name' => 'require|chs',
            'price' => 'require|number',
            'work_hours' => 'require|number',
            'manufacture_date' => 'require|dateFormat:Y-m-d',
            'is_invoice' => 'require',
            'is_certificate' => 'require',
            'linkman' =>'require|chs',
            'telphone' => 'require|/^1[3456789]{1}\d{9}$/',
        ];
        $msg = [
            'id.require' => '机械id未传入',
            'producing_country.require' => '请选择生产国',
            'mechanical_id.require' => '请选择机械类型',
            'brand_id.require' => '请选择品牌',
            'mechanical_model_id.require' => '请选择型号',
            'province_id.require' => '请选择省份',
            'city_id.require' => '请选择市区',
            'company_name.require' => '请填写公司名称',
            'work_hours.require' => '请填写工作时长',
            'price.require' => '请填写价格',
            'linkman.require' => '请填写联系人',
            'telphone.require' => '请填写联系方式',
            'is_invoice.require' => '请选择是否提供发票',
            'is_certificate.require' => '请选择是否有合格证',
            'manufacture_date.require' => '请选择出厂日期',
            'mechanical_id.number' => '请选择正确的机械类型',
            'brand_id.number' => '请选择正确的品牌',
            'mechanical_model_id.number' => '请选择正确的型号',
            'province_id.number' => '请选择正确的省份',
            'city_id.number' => '请选择正确的市区',
            'company_name.chs' => '请选择正确的公司名称',
            'work_hours.number' => '工作时长必须为数字',
            'manufacture_date.dateFormat' => '出厂日期格式不正确',
            'price.number' => '价格必须为数字',
            'telphone./^1[3456789]{1}\d{9}$/' => '请输入正确的手机号',
        ];
        $data = [
            'id'=>input('id'),
            'producing_country' => input('producing_country_id'),
            'mechanical_id' => input('mechanical_id'),
            'brand_id' => input('brand_id'),
            'mechanical_model_id' => input('mechanical_model_id'),
            'province_id' => input('province_id'),
            'city_id' => input('city_id'),
            'company_name' => input('company_name'),
            'work_hours' => input('work_hours'),
            'manufacture_date' => input('manufacture_date'),
            'is_invoice' => input('is_invoice'),
            'is_certificate' => input('is_certificate'),
            'telphone' => input('telphone'),
            'linkman'=>input('linkman'),
            'price' => input('price'),
            'is_effective' => 1,
            'is_check' => $check_open,
            'type' => 2
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        $res    = Db::name('mechanics')->where('id',input('id'))->update($data);
        $img_url= Db::name('plug_files')->where(['type'=>1,'product_id'=>input('id')])->delete();
        $fileurl_tmp = input('fileurl_tmp/a');
        //添加商品图片
        if(!empty($fileurl_tmp)){
            foreach ($fileurl_tmp as $key => $value) {
                $data = array(
                    'uptime' => time(),
                    'path' => $value,
                    'product_id' => input('id'),
                    'type' => 1
                );
                Db::name('plug_files')->insert($data);
            }
        }
        $this->make_json_result('恭喜您，修改成功！');
    }

   
}