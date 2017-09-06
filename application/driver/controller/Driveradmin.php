<?php
// +----------------------------------------------------------------------
// | 功能：司机管理后台
// +----------------------------------------------------------------------
// | 作者：程龙飞
// +----------------------------------------------------------------------
// | 日期：2017-6-28
// +----------------------------------------------------------------------
namespace app\driver\Controller;

use app\common\controller\Auth;
use think\Db;
class Driveradmin extends Auth
{

    /**
     * 信息列表
     * @return mixed
     */
    public function index()
    {
        if ($_POST) {
            if (input('province_id')) {
                $map['province_id'] = input('province_id');
                $city = Db::name('region')->where('pid', $info['province_id'])->select();
                $this->assign('city_list', $city);
            }

            if (input('city_id')) {
                $map['city_id'] = input('city_id');
            }

            if (input('salary')) {
                $map['salary'] = input('salary');
            }

            if (input('class_id')) {
                $map['class_id'] = input('class_id');
            }

            if (input('telphone')) {
                $map['telphone'] = input('telphone');
            }

            $list = Db::name('driver')
                ->alias('a')
                ->join('parameter b ', 'b.id= a.province_id', 'LEFT')
                ->join('region c ','c.id= a.province_id','LEFT')
                ->join('region d ','d.id= a.province','LEFT')
                ->join('parameter e ','e.id= a.education_level','LEFT')
                ->join('member_list f ','f.member_list_id= a.member_list_id','LEFT')
                ->join('parameter g ','g.id= a.salary','LEFT')
                ->where($map)
                ->order('create_time desc')
                ->field('a.*,b.name AS parameter_name,c.name AS home,d.name AS work,e.name AS education_level,f.member_list_tel,g.name as salary')
                ->paginate(15);
        } else {
            $list = Db::name('driver')
                ->alias('a')
                ->join('parameter b ', 'b.id= a.province_id', 'LEFT')
                ->join('region c ','c.id= a.province_id','LEFT')
                ->join('region d ','d.id= a.province','LEFT')
                ->join('parameter e ','e.id= a.education_level','LEFT')
                ->join('member_list f ','f.member_list_id= a.member_list_id','LEFT')
                ->join('parameter h ','h.id= a.type','LEFT')
                ->join('parameter i ','i.id= a.class_id','LEFT')
                ->join('parameter p ','p.id= a.sex','LEFT')
                ->order('create_time desc')
                ->field('a.*, a.salary as salary_id,b.name AS parameter_name,c.name AS home,d.name AS work,e.name AS education_level,f.member_list_tel,h.name AS diver_type,i.name as card_type')
                ->paginate(15);

        }
//        p($list);
        //查询所有的省及直辖市
        $province=Db::name('region')->where('type',1)->field('id,name')->select();
        $this->assign('province',$province);
        //薪资要求
        $salary_list =$this->_par_list('薪资要求','6');
        $salary_list_id = Db::name('parameter')->where(['parent_id'=>23,'module'=>6])->field('id')->select();

        if($salary_list_id){
            foreach ($salary_list_id  as $k => $v) {
                 $arr[] = $v['name']; 
             } 
            $salary_id_in = implode(',',$arr);
        }
        $this->assign('salary_id_in',$salary_id_in);
        $this->assign('salary_list',$salary_list);
        //分页
        $this->assign('page', $list->render());
        $this->assign('list', $list);
        // qq($list);die;
        return $this->fetch();
    }


    /**
     * 添加
     * @return mixed
     */
    public function driverAdd()
    {
        if(request()->isAjax())
        {
            $member_list_tel = input('member_list_tel');
            $member_list_id = Db::name('member_list')->where("member_list_tel",$member_list_tel)->value('member_list_id');
            if(!$member_list_id)
            {
                $this->error('不存在该用户',0,0);
            }
            $type= $_POST['type'];
            if(is_array($type))
            {
                $type = implode(',',$type);
            }
            $class_id= $_POST['class_id'];
            if(is_array($class_id))
            {
                $class_id = implode(',',$class_id);
            }

            $education_level= $_POST['education_level'];
            if(is_array($education_level))
            {
                $education_level = implode(',',$education_level);
            }

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
            //图片处理结束


            
            $data = [
                'member_list_id'  => $member_list_id,
                'type'            => $type,
                'class_id'        => $class_id,
                'province_id'     => input('province_id'),
                'city_id'         => input('city_id'),
                'salary'          => input('salary'),
                'driving_age'     => input('driving_age'),
                'education_level' => $education_level,
                'age'             => input('age'),
                'name'            => input('name'),
                'nation'          => input('nation'),
                'telphone'        => input('telphone'),
                'province'        => input('province'),
                'city'            => input('city'),
                'sex'             => input('sex'),
                'update_time'     => time(),
                'create_time'     => time(),
                'intro' => trim(input('intro')),
                'cover_img' => $shop_img_url,
                'is_check'        => input('is_check',0),
                'is_effective'    => input('is_effective',0),
            ];
            Db::name('driver')->insert($data);
            $this->success('添加成功',url('index'),1);
        }
        else
        {
            //司机类型
            $driver_id    = Db::name('parameter')->where(['state'=>1])->where("name",'司机类型')->value('id');
            $driver_multi = Db::name('parameter')->where(['state'=>1])->where("name",'司机类型')->value('multi');
            $this->assign('driver_multi',$driver_multi);
            $driver_list  = Db::name('parameter')->where(['state'=>1])->where("parent_id",$driver_id)->select();
            $this->assign('driver_list',$driver_list);
            
            //证件类型
            $card_id      = Db::name('parameter')->where(['state'=>1])->where("name",'证件类型')->value('id');
            $card_list    = Db::name('parameter')->where(['state'=>1])->where("parent_id",$card_id)->select();
            $card_multi   = Db::name('parameter')->where(['state'=>1])->where("name",'证件类型')->value('multi');
            $this->assign('card_multi',$card_multi);
            $this->assign('card_list',$card_list);
            
            //性别
            $sex_id       = Db::name('parameter')->where(['state'=>1])->where("name",'性别')->value('id');
            $sex_list     = Db::name('parameter')->where(['state'=>1])->where("parent_id",$sex_id)->select();
            $sex_multi    = Db::name('parameter')->where(['state'=>1])->where("name",'性别')->value('multi');
            $this->assign('sex_multi',$sex_multi);
            $this->assign('sex_list',$sex_list);
            
            //文化程度
            $class_id     = Db::name('parameter')->where(['state'=>1])->where("name",'文化程度')->value('id');
            $class_list   = Db::name('parameter')->where(['state'=>1])->where("parent_id",$class_id)->select();
            $class_multi  = Db::name('parameter')->where(['state'=>1])->where("name",'文化程度')->value('multi');
            $this->assign('class_multi',$class_multi);
            $this->assign('class_list',$class_list);
            //薪资要求
            $salary_id    = Db::name('parameter')->where(['state'=>1])->where("name",'薪资要求')->value('id');
            $salary_list  = Db::name('parameter')->where(['state'=>1])->where("parent_id",$salary_id)->select();
            $salary_multi = Db::name('parameter')->where(['state'=>1])->where("name",'薪资要求')->value('multi');
            $this->assign('salary_multi',$salary_multi);
            $this->assign('salary_list',$salary_list);
            //查询所有的省及直辖市
            $province=Db::name('region')->where('type',1)->field('id,name')->select();
            $this->assign('province',$province);
            // 姓名是否可用
            $name_state = $this->_state('姓名',6);
            // 电话
            $tel_state = $this->_state('电话',6);
            // 年龄
            $age_state = $this->_state('年龄',6);
            // 驾龄
            $driver_age_state = $this->_state('驾龄',6);
            //民族
            $national_state = $this->_state('民族',6);
            $this->assign('name_state',$name_state);
            $this->assign('tel_state',$tel_state);
            $this->assign('age_state',$age_state);
            $this->assign('driver_age_state',$driver_age_state);
            $this->assign('national_state',$national_state);
            return $this->fetch();
        }
    }

    /**
     * 修改
     * @return mixed
     */
    public function driveredit()
    {
        if ($_POST) {
            $mid = input('id');
            $member_list_tel = input('member_list_tel');
            $member_list_id  = Db::name('member_list')->where("member_list_tel", $member_list_tel)->value('member_list_id');
            if (!$member_list_id) {
                $this->error('不存在该用户', 0, 0);
            }
            $type = input('post.type/a');
            if(is_array($type)){
                $type = implode(',',$type);
            }
            $class_id = input('post.class_id/a');
            $class_id = $this->_implode($class_id);

            $salary = input('post.salary/a');
            $salary = $this->_implode($salary);

            $education_level = input('post.education_level/a');
            $education_level = $this->_implode($education_level);

            $sex = input('post.sex/a');
            $sex = $this->_implode($sex);

            // ee($salary);
            $data = [
                'member_list_id'  => $member_list_id,
                'type'            => $type,
                'class_id'        => $class_id,
                'province_id'     => input('province_id'),
                'city_id'         => input('city_id'),
                'salary'          => $salary,
                'driving_age'     => input('driving_age'),
                'education_level' => $education_level,
                'age'             => input('age'),
                'name'            => input('name'),
                'nation'          => input('nation'),
                'telphone'        => input('telphone'),
                'province'        => input('province'),
                'city'            => input('city'),
                'sex'             => $sex,
                'update_time'     => time(),
                'create_time'     => time(),
                'is_check'        => input('is_check', 0),
                'is_effective'    => input('is_effective', 0),
                'reject_reason'   =>input('reject_reason'),
            ];
            $res = Db::name('driver')->where('id', $mid)->update($data);
            $this->success('修改成功', url('index'), 1);
        } else {
            $map['a.id'] = input('id');
            $result = Db::name('driver')
                ->alias('a')
                ->join('parameter b ', 'b.id= a.province_id', 'LEFT')
                ->join('region c ', 'c.id= a.province_id', 'LEFT')
                ->join('region d ', 'd.id= a.province', 'LEFT')
                ->join('region e ', 'e.id= a.city_id', 'LEFT')
                ->join('member_list f ', 'f.member_list_id= a.member_list_id', 'LEFT')
                ->join('region g ', 'g.id= a.city', 'LEFT')
                ->order('create_time desc')
                ->where($map)
                ->field('a.*,b.name AS parameter_name,f.member_list_tel,e.name AS homecity,g.name AS workcity')
                ->find();
            //籍贯
            //查询所有的省及直辖市
            $province = Db::name('region')->where('type', 1)->field('id,name')->select();
            $this->assign('province', $province);
            //查询当前省下的市区列表
            $province_id = Db::name('driver')->where('id', $map['a.id'])->value('province_id');
            if ($province_id) {
                $zhi = array(2, 25, 27, 32);
                if (in_array($province_id, $zhi)) {
                    $id = Db::name('region')->where('pid', $province_id)->field('id,name')->value('id');
                    $citylist = Db::name('region')->where('pid', $id)->field('id,name')->select();
                } else {
                    $citylist = Db::name('region')->where('pid', $province_id)->field('id,name')->select();
                }
            }
            $this->assign('citylist', $citylist);

            //所在区域
            //查询当前省下的市区列表
            $province = Db::name('driver')->where('id', $map['a.id'])->value('province');
            if ($province) {
                $zhi = array(2, 25, 27, 32);
                if (in_array($province, $zhi)) {
                    $id = Db::name('region')->where('pid', $province)->field('id,name')->value('id');
                    $workcitylist = Db::name('region')->where('pid', $id)->field('id,name')->select();
                } else {
                    $workcitylist = Db::name('region')->where('pid', $province)->field('id,name')->select();
                }
            }
            $this->assign('workcitylist', $workcitylist);

            //文化程度
            $driver_education_level_list  = $this->_par_list('文化程度',6);  
            $driver_education_level_multi = $this->_multi('文化程度',6); 
            $driver_education_level_state = $this->_state('文化程度',6); 
            $this->assign('driver_education_level_list',$driver_education_level_list);
            $this->assign('driver_education_level_multi',$driver_education_level_multi);
            $this->assign('driver_education_level_state',$driver_education_level_state);

            //薪资要求（到这里了 还没有套前台页面）
            $driver_salary_list  = $this->_par_list('薪资要求',6);  
            $driver_salary_multi = $this->_multi('薪资要求',6); 
            $driver_salary_state = $this->_state('薪资要求',6); 
            $this->assign('driver_salary_list',$driver_salary_list);
            $this->assign('driver_salary_multi',$driver_salary_multi);
            $this->assign('driver_salary_state',$driver_salary_state);

            //司机类型
            $driver_type_list  = $this->_par_list('司机类型',6);  
            $driver_type_multi = $this->_multi('司机类型',6); 
            $driver_type_state = $this->_state('司机类型',6); 
            $this->assign('driver_type_list',$driver_type_list);
            $this->assign('driver_type_multi',$driver_type_multi);
            $this->assign('driver_type_state',$driver_type_state);

            //证件类型
            $driver_class_list  = $this->_par_list('证件类型',6);  
            $driver_class_multi = $this->_multi('证件类型',6); 
            $driver_class_state = $this->_state('证件类型',6); 
            $this->assign('driver_class_list',$driver_class_list);
            $this->assign('driver_class_multi',$driver_class_multi);
            $this->assign('driver_class_state',$driver_class_state);

            //性别
            $driver_sex_list  = $this->_par_list('性别',6);  
            $driver_sex_multi = $this->_multi('性别',6); 
            $driver_sex_state = $this->_state('性别',6); 
            $this->assign('driver_sex_list',$driver_sex_list);
            $this->assign('driver_sex_multi',$driver_sex_multi);
            $this->assign('driver_sex_state',$driver_sex_state);

            // 姓名是否可用
            $name_state       = $this->_state('姓名',6);
            // 电话
            $tel_state        = $this->_state('电话',6);
            // 年龄
            $age_state        = $this->_state('年龄',6);
            // 驾龄
            $driver_age_state = $this->_state('驾龄',6);
            //民族
            $national_state   = $this->_state('民族',6);
            $this->assign('name_state',$name_state);
            $this->assign('tel_state',$tel_state);
            $this->assign('age_state',$age_state);
            $this->assign('driver_age_state',$driver_age_state);
            $this->assign('national_state',$national_state);
            $this->assign('salary_list', $salary_list);
            $this->assign('result', $result);
            return $this->fetch();
        }
    }

    /**
     * 删除信息
     */
    public function driverDelete()
    {
        //获取要删除的ID
        $tid = input('id');
        $p = input('page');
        // 判断ID是否为空
        if(empty($tid)){
            $this->error('参数错误',Url('index',array('page'=>$p)),0);
        }
        //删除数据
        $res = Db::name('driver')->where('id',$tid)->delete();
        // 判断是否删除成功
        if($res){
            $this->success('删除成功',Url('index',array('page'=>$p)),1);
        }
        else{
            $this->error('删除失败',Url('index',array('page'=>$p)),0);
        }
    }

    /**
     * 状态激活
     */
    public function driver_active()
    {
        //获取要修改的id
        $id = input("x");
        $newsmechanics_db = db("driver");
        //判断此用户状态情况
        $status=$newsmechanics_db->where(array('id'=>$id))->value('is_effective');
        if($status == 1){
            //禁止
            $statedata = array('is_effective'=>0);
            $auth_group=$newsmechanics_db->where(array('id'=>$id))->setField($statedata);
            $this->success('未激活',1,1);
        }else{
            //开启
            $statedata = array('is_effective'=>1);
            $auth_group=$newsmechanics_db->where(array('id'=>$id))->setField($statedata);
            $this->success('已激活',1,1);
        }
    }

    /**
     * 根据省获取城市
     */
    public function getCity()
    {
        $province_id = input('province_id');
        $zhi=array(2,25,27,32);
        if(in_array($province_id,$zhi)){
            $id = Db::name('region')->where('pid',$province_id)->field('id,name')->value('id');
            $list = Db::name('region')->where('pid',$id)->field('id,name')->select();
        }else{
            $list = Db::name('region')->where('pid',$province_id)->field('id,name')->select();
        }
        $html = '<option value="">请选择</option>';
        foreach($list as $key => $value)
        {
            $html .= "<option value=\"{$value['id']}\">{$value['name']}</option>";
        }
        echo $html;exit;
    }


    /**
     * 配置信息
     * @return mixed
     */
    public function setting()
    {
        if(request()->isAjax())
        {
            $options = input('options/a');
            $options['check_open'] = request()->post('check_open',0,'intval');
            $options['see_open']   = request()->post('see_open',0,'intval');
            Db::name('options')->where(array('option_name'=>'driverSetting'))->setField('option_value',serialize($options));
            $this->success('修改成功',url('setting'),1);
        }
        else
        {
            $sys = Db::name('options')->where(array('option_name'=>'driverSetting'))->value("option_value");

            if($sys)
            {
                $sys=unserialize($sys);
            }
            else
            {
                $sys =  [];
            }
            $this->assign('sys',$sys);
            return $this->fetch();
        }
    }


    /**
     * 参数管理
     * @return mixed
     */
    public function paramsSetting()
    {
        $map = [
            'module' => 6, //1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
            'parent_id' => 0
        ];
        $list = Db::name('parameter')->where($map)->select();

        $this->assign('list',$list);

        return $this->fetch();
    }


    /**
     * 添加参数
     * @return mixed
     */
    public function paramsSettingAdd()
    {
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 6,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
                'name' => input('name'),
                'multi' => input('multi',0),
                'state' => input('state',0),
                'value' => '',
                'parent_id' => 0,
                'type'=>input('type'),
                'input_type'=>input('input_type'),
            ];

            Db::name('parameter')->insert($data);


            $this->success('添加成功',url('paramsSetting'),1);
        }
        //没有参数提交显示页面
        else
        {
            return $this->fetch();
        }
    }


    /**
     * 修改参数
     * @return mixed
     */
    public function paramsSettingEdit()
    {
        $id = input('id');
        if(!id)
        {
            $this->redirect(url('paramsSetting'));
        }
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 6,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
                'name' => input('name'),
                'multi' => input('multi',0),
                'state' => input('state',0),
                'value' => '',
                'parent_id' => 0,
                'type'=>input('type'),
                'input_type'=>input('input_type'),  
            ];

            Db::name('parameter')->where(['id'=>$id])->update($data);


            $this->success('修改成功',url('paramsSetting'),1);
        }
        //没有参数提交显示页面
        else
        {
            $info = Db::name('parameter')->where('id',$id)->find();
            $this->assign('info',$info);
            $this->assign('id',$id);
            return $this->fetch();
        }
    }


    /**
     * 删除参数
     */
    public function paramsSettingDelete()
    {
        $id = input('id');
        if (empty($id)){
            $this->error('数据不存在',url('paramsSetting'),0);
        }
        Db::name('parameter')->where(array('id'=>input('id')))->delete();

        if($rst!==false){
            $this->success('删除成功',url('paramsSetting'),1);
        }else{
            $this->error('删除失败',url('paramsSetting'),0);
        }
    }

    /**
     * 查看参数值
     * @return mixed
     */
    public function paramsSettingValue()
    {
        $pid = input('pid');
        if(empty($pid))
        {
            $this->redirect(url('paramsSetting'));
        }
        $name = Db::name('parameter')->where('id',$pid)->value('name');
        $this->assign('name',$name);
        $this->assign('pid',$pid);

        $map = [
            'parent_id' => $pid,
        ];
        $list = Db::name('parameter')->where($map)->select();
        $this->assign('list',$list);

        return $this->fetch();
    }


    /***
     * 添加参数值
     * @return mixed
     */
    public function paramsSettingValueAdd()
    {
        $pid = input('pid');
        if(empty($pid))
        {
            $this->redirect(url('paramsSetting'));
        }
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 6,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
                'name' => input('name'),
                'multi' => 1,
                'state' => input('state',0),
                'value' => input('name'),
                'parent_id' => $pid
            ];

            Db::name('parameter')->insert($data);


            $this->success('添加成功',url('paramsSettingValue',array('pid'=>$pid)),1);
        }
        //没有参数提交显示页面
        else
        {
            $name = Db::name('parameter')->where('id',$pid)->value('name');
            $this->assign('name',$name);
            $this->assign('pid',$pid);
            return $this->fetch();
        }
    }

    /**
     * 修改参数值
     * @return mixed
     */
    public function paramssettingvalueedit()
    {
        $id = input('id');
        if(!id)
        {
            $this->redirect(url('paramsSettingValue',array('pid'=>input('pid'))));
        }

        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 6,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
                'name' => input('name'),
                'multi' => 1,
                'state' => input('state',0),
                'value' => input('name'),
                'parent_id' => input('pid'),
            ];

            Db::name('parameter')->where(['id'=>$id])->update($data);


            $this->success('修改成功',url('paramsSetting'),1);
        }
        //没有参数提交显示页面
        else
        {
            $info = Db::name('parameter')->where('id',$id)->find();
            $this->assign('info',$info);
            $this->assign('id',$id);
            $name = Db::name('parameter')->where('id',input('pid'))->value('name');
            $this->assign('name',$name);
            return $this->fetch();
        }
    }


    /**
     * 修改参数状态（开启关闭）
     */
    public function paramsSettingState()
    {
        $id = input('x');
        $status = Db::name('parameter')->where(array('id'=>$id))->value('state');//判断当前状态情况
        if($status==1){
            $statedata = array('state'=>0);
            $auth_group=Db::name('parameter')->where(array('id'=>$id))->setField($statedata);
            $this->success('状态禁止',1,1);
        }else{
            $statedata = array('state'=>1);
            $auth_group=Db::name('parameter')->where(array('id'=>$id))->setField($statedata);
            $this->success('状态开启',1,1);
        }
    }

    /**
     * 删除参数
     */
    public function paramsSettingValueDelete()
    {
        $id = input('id');
        if (empty($id)){
            $this->error('数据不存在',url('paramsSetting'),0);
        }
        Db::name('parameter')->where(array('id'=>input('id')))->delete();

        if($rst!==false){
            $this->success('删除成功',url('paramsSettingValue',array('pid'=>input('pid'))),1);
        }else{
            $this->error('删除失败',url('paramsSettingValue',array('pid'=>input('pid'))),0);
        }
    }

    /**
     * 检测参数是否可用 
     * $value string 名称 
     * $modle int 模块代号
     * @ return bool
     */
    public function _state($value,$model='')
    {
        $bool  = Db::name('parameter')->where('module',$model)->where("name",$value)->value('state');
        return $bool;
    }

    /**
     * 检测参数是否多选 
     * $value string 名称 
     * $modle int 模块代号
     * @ return bool
     */
    public function _multi($value,$model='')
    {
        $bool  = Db::name('parameter')->where('module',$model)->where("name",$value)->value('multi');
        return $bool;
    }

     /**
     * 查找参数值 
     * $value string 名称 
     * $modle int 模块代号
     * @ return array 
     */
    public function _par_list($value,$model='')
    {
        $id = Db::name('parameter')->where("name", $value)->value('id');
        $par_list = Db::name('parameter')->where('module',$model)->where("parent_id", $id )->select();
        return $par_list;
    }

    public function _implode($data)
    {   
        if(is_array($data))
        {
            $data = implode(',',$data);
        }
        else
        {
            $data =$data;
        }
        return $data;
    }

    public function adsp_group($arr, $key){
        
        $grouped = [];

        foreach ($arr as $value) {
            $grouped[$value[$key]][] = $value;
        }

        if (func_num_args() > 2) {

            $args = func_get_args();
            foreach ($grouped as $key => $value) {
                $parms = array_merge([$value], array_slice($args, 2, func_num_args()));
                $grouped[$key] = call_user_func_array('array_group_by', $parms);
            }
        }

        return $grouped;
        
    }  
}