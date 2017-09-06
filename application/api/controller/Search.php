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

class Search extends Common
{
    /**
     * 搜索配件列表
     */
    public function SearchPart()
    {
        if($_POST){
            if(input('car_tid'))
            {
                $par['car_tid'] = input('car_tid');
            }
            if(input('province_id'))
            {
                $par['province_id'] = input('province_id');
            }
            if(input('city_id'))
            {
                $par['city_id'] = input('city_id');
            }
            if(input('parts_tid'))
            {
                $par['parts_tid'] = input('parts_tid');
            }
            if(input('service_tid'))
            {
                $par['service_tid'] = input('service_tid');
            }
        }
        $par['is_check'] = 2;
        $par['is_delete'] = 0;
        $par['is_effective'] = 1;
        $result = Db::name('parts')
            ->where($par)
            ->order('createtime desc')
            ->field('cover_image,title,price,city_id')
            ->paginate(15);
        if($result){
            $this->make_json_result('配件数据获取成功',$result);
        }else{
            $this->make_json_error('数据获取失败', 1);
        }

    }


    /**
     * 获取车辆种类
     */
    public function GetCarList()
    {
        $res=Db::name('parameter')->where(['parent_id'=>'201','state'=>1])->field('id,name')->select();
        if($res){
            $this->make_json_result('车辆种类数据获取成功',$res);
        }else{
            $this->make_json_error('数据获取失败', 1);
        }
    }

    /**
     * 服务类型
     */
    public function GetServiceList()
    {
        $res=Db::name('parameter')->where(['parent_id'=>'73','state'=>1])->field('id,name')->select();
        if($res){
            $this->make_json_result('服务类型数据获取成功',$res);
        }else{
            $this->make_json_error('数据获取失败', 1);
        }
    }

    /**
     * 配件种类
     */
    public function GetPartsList()
    {
        $res=Db::name('parameter')->where(['parent_id'=>'72','state'=>1])->field('id,name')->select();
        if($res){
            $this->make_json_result('配件种类数据获取成功',$res);
        }else{
            $this->make_json_error('数据获取失败', 1);
        }
    }

    /**
     * 配件详情
     */
    public function GetPart()
    {
        if(input('parts_id'))
        {
            $par['parts_id']= input('parts_id');
        }
        $par['is_check'] = 2;
        $par['is_delete']= 0;
        $par['is_effective'] = 1;
        $res=Db::name('parts')->where('parts_id',$par['parts_id'])->where($par)->find();
        if($res){
            $this->make_json_result('配件详情数据获取成功',$res);
        }else{
            $this->make_json_error('数据获取失败', 1);
        }
    }

    /**
     * 司机列表
     * （未完成）
     */
    public function DriverList()
    {
        if($_POST){
            //司机类型
            if(input('type'))
            {
                $par['type'] = input('type');
            }
            //驾照类型
            if(input('class_id'))
            {
                $par['class_id'] = input('class_id');
            }
            //工作区域
            if(input('city_id'))
            {
                $par['city_id'] = input('city_id');
            }
            //工作省份
            if(input('province_id'))
            {
                $par['province_id'] = input('province_id');
            }
            //籍贯区域
            if(input('city'))
            {
                $par['city'] = input('city');
            }
            //籍贯省份
            if(input('province'))
            {
                $par['province'] = input('province');
            }
            //驾龄
            if(input('driving_age'))
            {
                $driving_age = explode("-",input('driving_age'));
                $start_driving_age = intval($driving_age[0]);
                $end_driving_age = intval($driving_age[1]);

                if($start_driving_age && !$end_driving_age)
                {
                    $str_driving_age = input('driving_age');
                    if(strpos($str_driving_age,'以上'))
                    {
                        $par['a.driving_age'] = array('gt',$start_driving_age);

                    }
                    else
                    {
                        $par['a.driving_age'] = array('lt',$start_driving_age);
                    }

                }
                elseif($start_driving_age && $end_driving_age)
                {
                    $par['a.driving_age'] = array('between',$start_driving_age,$end_driving_age);
                }
            }
            //年龄
            if(input('age'))
            {
                $age = explode("-",input('age'));
                $start_age = intval($age[0]);
                $end_age = intval($age[1]);
                if($start_age && !$end_age)
                {
                    $str_age = input('age');
                    if(strpos($str_age,'以上'))
                    {
                        $par['a.age'] = array('gt',$start_age);
                    }
                    else
                    {
                        $par['a.age'] = array('lt',$start_age);
                    }
                }
                elseif($start_age && $end_age)
                {
                    $par['a.age'] = array('between',$start_age,$end_age);
                }
            }
            //民族
            if(input('nation'))
            {
                $nation=input('nation');
                $name=Db::name('driver')->where('id',$nation)->value('name');
                $par['nation'] = input($name);
            }

            //教育经历
            if(input('education_level'))
            {
                $par['education_level'] = input('education_level');
            }
            //工资要求
            if(input('salary'))
            {
                $salary = explode("-",input('salary'));
                $start_salary = intval($salary[0]);
                $end_salary = intval($salary[1]);
                if($start_salary && !$end_salary)
                {
                    $str_salary = input('salary');
                    if(strpos($str_salary,'以上'))
                    {
                        $par['a.salary'] = array('gt',$salary);
                    }
                    else
                    {
                        $par['a.salary'] = array('lt',$salary);
                    }
                }
                elseif($start_salary && $end_salary)
                {
                    $par['a.salary'] = array('between',$start_salary,$end_salary);
                }

            }
            //性别
            if(input('sex'))
            {
                $par['sex'] = input('sex');
            }
        }
        $par['is_check'] = 2;
        $par['is_delete'] = 0;
        $par['is_effective'] = 1;
        $result = Db::name('driver')
            ->where($par)
            ->order('create_time desc')
            ->field('cover_image,class_id,driving_age,name,city_id')
            ->paginate(15);
        if($result){
            $this->make_json_result('配件数据获取成功',$result);
        }else{
            $this->make_json_error('数据获取失败', 1);
        }

    }

    /**
     * 民族列表
     */
    public function NationList()
    {
        $res=Db::name('parameter')->where(['parent_id'=>'130','state'=>1])->field('id,name')->select();
        if($res){
            $this->make_json_result('民族数据获取成功',$res);
        }else{
            $this->make_json_error('数据获取失败', 1);
        }
    }

    /**
     * 薪资列表
     */
    public function SalaryList()
    {
        $res=Db::name('parameter')->where(['parent_id'=>'23','state'=>1])->field('id,name')->select();
        if($res){
            $this->make_json_result('薪资列表获取成功',$res);
        }else{
            $this->make_json_error('数据获取失败', 1);
        }
    }

    /**
     * 驾龄列表
     */
    public function DrivingAgeList()
    {
        $res=Db::name('parameter')->where(['parent_id'=>'222','state'=>1])->field('id,name')->select();
        if($res){
            $this->make_json_result('驾龄列表获取成功',$res);
        }else{
            $this->make_json_error('数据获取失败', 1);
        }
    }

    /**
     * 性别列表
     */
    public function SexList()
    {
        $res=Db::name('parameter')->where(['parent_id'=>'134','state'=>1])->field('id,name')->select();
        if($res){
            $this->make_json_result('性别列表获取成功',$res);
        }else{
            $this->make_json_error('数据获取失败', 1);
        }
    }

    /**
     * 司机类型列表
     */
    public function DriverTypeList()
    {
        $res=Db::name('parameter')->where(['parent_id'=>'34','state'=>1])->field('id,name')->select();
        if($res){
            $this->make_json_result('司机类型列表获取成功',$res);
        }else{
            $this->make_json_error('数据获取失败', 1);
        }
    }

    /**
     * 证件类型列表
     */
    public function DriverClassList()
    {
        $res=Db::name('parameter')->where(['parent_id'=>'8','state'=>1])->field('id,name')->select();
        if($res){
            $this->make_json_result('证件类型列表获取成功',$res);
        }else{
            $this->make_json_error('数据获取失败', 1);
        }
    }

    /**
     * 学历列表
     */
    public function EducationLevelList()
    {
        $res=Db::name('parameter')->where(['parent_id'=>'14','state'=>1])->field('id,name')->select();
        if($res){
            $this->make_json_result('民族数据获取成功',$res);
        }else{
            $this->make_json_error('数据获取失败', 1);
        }
    }

    /**
     * 年龄列表
     */
    public function AgeList()
    {
        $res=Db::name('parameter')->where(['parent_id'=>'283','state'=>1])->field('id,name')->select();
        if($res){
            $this->make_json_result('年龄数据获取成功',$res);
        }else{
            $this->make_json_error('数据获取失败', 1);
        }
    }


    /**
     * 司机详情
     */
    public function Driver()
    {
        if(input('id'))
        {
            $par['a.id']= input('id');
        }
        $par['a.is_check'] = 2;
        $par['a.is_delete'] = 0;
        $par['a.is_effective'] = 1;
        $res=Db::name('driver')
            ->alias('a')
            ->join('parameter b ', 'b.id= a.type', 'LEFT')
            ->join('parameter c ', 'c.id= a.class_id', 'LEFT')
            ->join('region d ','d.id= a.city_id','LEFT')
            ->join('region e ','e.id= a.city','LEFT')
            ->join('parameter f ','f.id= a.education_level','LEFT')
            ->join('parameter g ','g.id= a.driving_age','LEFT')
            ->join('parameter h ','h.id= a.age','LEFT')
            ->join('parameter i ','i.id= a.sex','LEFT')
            ->join('parameter j ','j.id= a.nation','LEFT')
            ->where($par)
            ->field('a.id,b.name AS type,c.name AS class_id,d.name AS city_id,a.driving_age,a.age,i.name AS sex,e.name AS city,a.nation,f.name AS education_level,a.salary,a.intro,a.cover_img')
            ->find();
        if($res){
            $this->make_json_result('司机详情数据获取成功',$res);
        }else{
            $this->make_json_error('数据获取失败', 1);
        }
    }

}